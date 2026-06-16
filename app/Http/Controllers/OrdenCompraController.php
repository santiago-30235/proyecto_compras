<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        // 1. Validación estricta de entrada
        $request->validate([
            'proveedor_id'         => 'required|exists:proveedores,id',
            'fecha'                => 'required|date',
            'tipopago'             => 'required|in:contado,credito',
            'productos'            => 'required|array|min:1',
            'productos.*.id'       => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'numero_comprobante'   => 'nullable|string|max:150',
            'observaciones'        => 'nullable|string|max:1000',
        ]);

        // 2. Validación preventiva de Stock Máximo antes de tocar la BD
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

        // 3. Activamos Transacción Atómica: O se guarda TODO o no se guarda NADA
        DB::beginTransaction();

        try {
            $usuarioResponsable = auth()->check() ? auth()->user()->name : 'Sistema';
            $total = 0;

            // Crear cabecera de la orden
            $orden = OrdenCompra::create([
                'fecha'              => $request->fecha,
                'proveedor_id'       => $request->proveedor_id,
                'total'              => 0,
                'tipopago'           => $request->tipopago,
                'saldopendiente'     => 0,
                'estado'             => '1',
                'registradopor'      => $usuarioResponsable,
                'numero_comprobante' => $request->numero_comprobante ?? null,
                'observaciones'      => $request->observaciones ?? null,
            ]);

            // Registrar detalles y actualizar inventario
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['id']);
                $subtotal = $producto->preciocompra * $item['cantidad'];
                $total += $subtotal;

                DetalleCompra::create([
                    'ordencompra_id' => $orden->id,
                    'producto_id'    => $item['id'],
                    'cantidad'       => $item['cantidad'],
                    'subtotal'       => $subtotal,
                    'registradopor'  => $usuarioResponsable,
                ]);

                $producto->stock += $item['cantidad'];
                $producto->save();
            }

            // Procesamiento financiero según el tipo de pago
            if ($request->tipopago == 'contado') {
                Pago::create([
                    'ordencompra_id' => $orden->id,
                    'fechapago'      => now(),
                    'monto'          => $total,
                    'metodopago_id'  => $request->metodopago_id ?? 1,
                    'registradopor'  => $usuarioResponsable,
                ]);
                
                $orden->update(['total' => $total, 'saldopendiente' => 0]);
                $mensaje = 'Orden de compra creada y pagada correctamente.';
                
            } else {
                $abonoInicial = $request->abono_inicial ?? 0;
                
                if ($abonoInicial < 0) {
                    DB::rollback();
                    return back()->withErrors('El abono inicial no puede ser negativo.')->withInput();
                }
                if ($abonoInicial > $total) {
                    DB::rollback();
                    return back()->withErrors('El abono inicial no puede ser mayor al total de la orden.')->withInput();
                }
                
                $nuevoSaldo = $total - $abonoInicial;
                
                $orden->update([
                    'total'          => $total,
                    'saldopendiente' => $nuevoSaldo,
                ]);
                
                if ($abonoInicial > 0) {
                    Pago::create([
                        'ordencompra_id' => $orden->id,
                        'fechapago'      => now(),
                        'monto'          => $abonoInicial,
                        'metodopago_id'  => $request->metodopago_id ?? 1,
                        'registradopor'  => $usuarioResponsable,
                    ]);
                }
                
                $mensaje = 'Orden de compra a crédito registrada correctamente. Saldo pendiente: $' . number_format($nuevoSaldo, 2);
            }

            // Si todo salió perfecto, consolidamos los cambios en PostgreSQL
            DB::commit();
            return redirect()->route('ordencompras.index')->with('successMsg', $mensaje);

        } catch (Exception $e) {
            // Si algo falló en Render, deshacemos todo para evitar inconsistencias
            DB::rollback();
            Log::error('Error crítico al procesar orden de compra: ' . $e->getMessage());
            return redirect()->route('ordencompras.index')
                ->withErrors('Ocurrió un error interno al intentar registrar la orden de compra.');
        }
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
            'proveedor_id'       => 'required|exists:proveedores,id',
            'fecha'              => 'required|date',
            'tipopago'           => 'required|in:contado,credito',
            'metodopago_id'      => 'nullable|exists:metodopagos,id',
            'numero_comprobante' => 'nullable|string|max:150',
            'observaciones'      => 'nullable|string|max:1000',
        ]);

        try {
            $orden->update([
                'fecha'              => $request->fecha,
                'proveedor_id'       => $request->proveedor_id,
                'tipopago'           => $request->tipopago,
                'numero_comprobante' => $request->numero_comprobante ?? null,
                'observaciones'      => $request->observaciones ?? null,
                'registradopor'      => auth()->check() ? auth()->user()->name : 'Sistema',
            ]);

            if ($request->filled('metodopago_id')) {
                $primerPago = $orden->pagos()->orderBy('id')->first();
                if ($primerPago) {
                    $primerPago->update(['metodopago_id' => $request->metodopago_id]);
                }
            }

            return redirect()->route('ordencompras.index')
                ->with('successMsg', 'Orden de compra actualizada correctamente.');

        } catch (Exception $e) {
            Log::error('Error al actualizar orden de compra: ' . $e->getMessage());
            return redirect()->route('ordencompras.index')
                ->withErrors('Ocurrió un error al intentar actualizar la orden de compra.');
        }
    }

    public function destroy($id)
    {
        $orden = OrdenCompra::findOrFail($id);

        // Control de deudas antes de permitir la eliminación
        $totalPagado = DB::table('pagos')->where('ordencompra_id', $id)->sum('monto');
        $saldoPendiente = $orden->total - $totalPagado;

        if ($saldoPendiente > 0) {
            return redirect()->route('ordencompras.index')
                ->withErrors('Esta orden de compra no se puede eliminar porque presenta un saldo activo pendiente con el proveedor.');
        }

        DB::beginTransaction();

        try {
            // Reversión del stock adquirido
            foreach ($orden->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto) {
                    $producto->stock -= $detalle->cantidad;
                    if ($producto->stock < 0) {
                        $producto->stock = 0;
                    }
                    $producto->save();
                }
            }

            // Limpieza segura en cascada para PostgreSQL
            $orden->detalles()->delete();

            if (method_exists($orden, 'pagos')) {
                $orden->pagos()->delete();
            } else {
                DB::table('pagos')->where('ordencompra_id', $id)->delete();
            }

            $orden->delete();

            DB::commit(); 
            return redirect()->route('ordencompras.index')
                ->with('successMsg', 'Orden de compra eliminada correctamente y stock revertido.');

        } catch (Exception $e) {
            DB::rollback(); 
            Log::error('Error al eliminar orden de compra: ' . $e->getMessage());
            return redirect()->route('ordencompras.index')
                ->withErrors('Ocurrió un error inesperado al intentar eliminar la orden de compra.');
        }
    }

    public function cambioestado(Request $request)
    {
        $orden = OrdenCompra::find($request->id);
        if ($orden) {
            $orden->estado = $request->estado;
            $orden->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function generarPDF($id)
    {
        $orden = OrdenCompra::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        $data = ['orden' => $orden, 'fecha' => now()->format('d/m/Y H:i')];
        $pdf = PDF::loadView('ordencompras.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('orden-compra-' . $orden->id . '.pdf');
    }

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