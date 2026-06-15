<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Http\Request;
use PDF;
use Excel;
use App\Exports\OrdenCompraExport;
use App\Exports\OrdenesComprasExport;
use App\Exports\OrdenesExport;

class OrdenCompraController extends Controller
{
    public function exportarExcel()
    {
        return Excel::download(new OrdenesExport(), 'ordenes-compras.xlsx');
    }

    public function index()
    {
        $ordencompras = OrdenCompra::with('proveedor')->get();
        return view('ordencompras.index', compact('ordencompras'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('estado', '1')->get();
        $productos = Producto::where('estado', '1')->get();
        $metodosPago = MetodoPago::where('estado', '1')->get();
        return view('ordencompras.create', compact('proveedores', 'productos', 'metodosPago'));
    }

    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'tipopago' => 'required|in:contado,credito',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'numero_comprobante' => 'nullable|string|max:150',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // VALIDACIÓN DE STOCK MÁXIMO
        foreach ($request->productos as $item) {
            $producto = Producto::find($item['id']);
            $nuevoStock = $producto->stock + $item['cantidad'];
            
            if ($nuevoStock > $producto->stockmaximo) {
                $disponible = $producto->stockmaximo - $producto->stock;
                return redirect()->back()
                    ->withErrors(["No se puede comprar {$item['cantidad']} unidades de '{$producto->nombre}'. Solo puedes comprar hasta {$disponible} unidades."])
                    ->withInput();
            }
        }

        $total = 0;

        // Crear orden
        $orden = OrdenCompra::create([
            'fecha' => $request->fecha,
            'proveedor_id' => $request->proveedor_id,
            'total' => 0,
            'tipopago' => $request->tipopago,
            'saldopendiente' => 0,
            'estado' => '1',
            'registradopor' => auth()->user()->name,
            'numero_comprobante' => $request->numero_comprobante ?? null,
            'observaciones' => $request->observaciones ?? null,
        ]);

        // Crear detalles y calcular total
        foreach ($request->productos as $item) {
            $producto = Producto::find($item['id']);
            $subtotal = $producto->preciocompra * $item['cantidad'];
            $total += $subtotal;

            DetalleCompra::create([
                'ordencompra_id' => $orden->id,
                'producto_id' => $item['id'],
                'cantidad' => $item['cantidad'],
                'subtotal' => $subtotal,
                'registradopor' => auth()->user()->name,
            ]);

            // Aumentar stock
            $producto->stock += $item['cantidad'];
            $producto->save();
        }

        // LÓGICA SEGÚN TIPO DE PAGO
        if ($request->tipopago == 'contado') {
            // CONTADO: pago automático por el total
            Pago::create([
                'ordencompra_id' => $orden->id,
                'fechapago' => now(),
                'monto' => $total,
                'metodopago_id' => $request->metodopago_id ?? 1,
                'registradopor' => auth()->user()->name,
            ]);
            $orden->update(['total' => $total, 'saldopendiente' => 0]);
            $mensaje = 'Orden de compra creada y PAGADA exitosamente';
            
        } else {
            // CRÉDITO: con abono inicial
            $abonoInicial = $request->abono_inicial ?? 0;
            
            if ($abonoInicial < 0) {
                return back()->withErrors('El abono inicial no puede ser negativo')->withInput();
            }
            if ($abonoInicial > $total) {
                return back()->withErrors('El abono inicial no puede ser mayor al total de la orden')->withInput();
            }
            
            $nuevoSaldo = $total - $abonoInicial;
            
            $orden->update([
                'total' => $total,
                'saldopendiente' => $nuevoSaldo,
            ]);
            
            // Si hay abono inicial, crear el pago
            if ($abonoInicial > 0) {
                Pago::create([
                    'ordencompra_id' => $orden->id,
                    'fechapago' => now(),
                    'monto' => $abonoInicial,
                    'metodopago_id' => $request->metodopago_id ?? 1,
                    'registradopor' => auth()->user()->name,
                ]);
            }
            
            $mensaje = 'Orden de compra creada. ';
            if ($abonoInicial > 0) {
                $mensaje .= 'Abono inicial: $' . number_format($abonoInicial, 2) . '. ';
            }
            $mensaje .= 'Saldo pendiente: $' . number_format($nuevoSaldo, 2);
        }

        return redirect()->route('ordencompras.index')->with('successMsg', $mensaje);
    }

    public function show($id)
    {
        $orden = OrdenCompra::with(['proveedor', 'detalles.producto', 'pagos'])->findOrFail($id);
        return view('ordencompras.show', compact('orden'));
    }

    public function edit($id)
    {
        $orden = OrdenCompra::findOrFail($id);
        $proveedores = Proveedor::where('estado', '1')->get();
        $productos = Producto::where('estado', '1')->get();
        $metodosPago = MetodoPago::where('estado', '1')->get();
        return view('ordencompras.edit', compact('orden', 'proveedores', 'productos', 'metodosPago'));
    }

    public function update(Request $request, $id)
    {
        $orden = OrdenCompra::with('pagos')->findOrFail($id);

        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha' => 'required|date',
            'tipopago' => 'required|in:contado,credito',
            'metodopago_id' => 'nullable|exists:metodopagos,id',
            'numero_comprobante' => 'nullable|string|max:150',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $orden->update([
            'fecha' => $request->fecha,
            'proveedor_id' => $request->proveedor_id,
            'tipopago' => $request->tipopago,
            'numero_comprobante' => $request->numero_comprobante ?? null,
            'observaciones' => $request->observaciones ?? null,
            'registradopor' => auth()->user()->name,
        ]);

        if ($request->filled('metodopago_id')) {
            $primerPago = $orden->pagos()->orderBy('id')->first();
            if ($primerPago) {
                $primerPago->update(['metodopago_id' => $request->metodopago_id]);
            }
        }

        return redirect()->route('ordencompras.index')->with('successMsg', 'Orden actualizada exitosamente');
    }

    public function destroy($id)
{
    // 1. Buscamos la orden de compra
    $orden = OrdenCompra::findOrFail($id);

    // 2. Calculamos cuánto se ha pagado sumando los abonos en la tabla pagos
    $totalPagado = \DB::table('pagos')->where('ordencompra_id', $id)->sum('monto');

    // 3. Calculamos si todavía debe dinero
    $saldoPendiente = $orden->total - $totalPagado;

    // --- REGLA DEL PROFESOR 1: SI DEBE PLATA, COMPLETAMENTE BLOQUEADO ---
    // Si el saldo pendiente es mayor a cero, detenemos el proceso inmediatamente
    if ($saldoPendiente > 0) {
        return redirect()->route('ordencompras.index')
            ->withErrors("Restricción de Integridad: No se puede eliminar la orden #{$id} porque el proveedor '{$orden->proveedor->nombre}' tiene esta orden con saldo pendiente (\${$saldoPendiente}). Primero debe liquidar la deuda.");
    }

    // --- REGLA DEL PROFESOR 2: SI ESTÁ PAGO, SE BORRA TODO DE LA MANO EN CASCADA ---
    // Iniciamos una transacción para que si algo falla, no se altere nada en la BD
    \DB::beginTransaction();

    try {
        // A. REVERTIR EL STOCK: Devolvemos la mercancía comprada (pasa de 42 a 40)
        foreach ($orden->detalles as $detalle) {
            $producto = $detalle->producto;
            if ($producto) {
                $producto->stock -= $detalle->cantidad;
                if ($producto->stock < 0) $producto->stock = 0;
                $producto->save();
            }
        }

        // B. ELIMINAR ASOCIACIONES: Borramos los pagos vinculados para que MySQL no se queje
        \DB::table('pagos')->where('ordencompra_id', $id)->delete();

        // C. ELIMINAR DETALLES: Limpiamos la tabla pivote de productos de esta orden
        \DB::table('detalle_ordencompras')->where('ordencompra_id', $id)->delete();

        // D. ELIMINAR ORDEN: Finalmente borramos el registro principal de la orden de compra
        $orden->delete();

        // Guardamos todos los cambios en la base de datos de manera definitiva
        \DB::commit();

        return redirect()->route('ordencompras.index')
            ->with('successMsg', "Orden #{$id} eliminada con éxito. Como estaba totalmente pagada, se eliminaron sus relaciones y el stock se redujo correctamente.");

    } catch (\Exception $e) {
        // Si ocurre cualquier error imprevisto, deshacemos todo para no descuadrar el inventario
        \DB::rollback();

        return redirect()->route('ordencompras.index')
            ->withErrors("Error fatal: No se pudo eliminar la orden debido a dependencias con el proveedor o el sistema. " . $e->getMessage());
    }
}

    public function cambioestado(Request $request)
    {
        $orden = OrdenCompra::find($request->id);
        if ($orden) {
            $orden->estado = $request->estado;
            $orden->save();
        }
        return response()->json(['success' => true]);
    }

    // PDF
    public function generarPDF($id)
    {
        $orden = OrdenCompra::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        $data = ['orden' => $orden, 'fecha' => now()->format('d/m/Y H:i')];
        $pdf = PDF::loadView('ordencompras.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('orden-compra-' . $orden->id . '.pdf');
    }

    // EXCEL
    public function generarExcel($id)
    {
        $orden = OrdenCompra::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        return Excel::download(new OrdenCompraExport($orden), 'orden-compra-' . $id . '.xlsx');
    }

    public function generarExcelGeneral()
    {
        return Excel::download(new OrdenesComprasExport(), 'ordenes-compras.xlsx');
    }
}