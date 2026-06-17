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
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdenCompraExport;
use App\Exports\OrdenesExport;

class OrdenCompraController extends Controller
{
    public function exportarExcel()
    {
        return Excel::download(new OrdenesExport(), 'ordenes-compras.xlsx');
    }

    /**
     * Muestra el listado de órdenes de compra con paginación dinámica y búsqueda
     */
    public function index(Request $request)
    {
        // 1. Obtener el número de registros por página (por defecto 10)
        $perPage = $request->input('per_page', 10);
        
        // 2. Obtener el término de búsqueda
        $search = $request->input('search');
        
        // 3. Construir la consulta base
        $query = OrdenCompra::with('proveedor');
        
        // 4. Aplicar búsqueda si existe
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('fecha', 'LIKE', "%{$search}%")
                  ->orWhere('total', 'LIKE', "%{$search}%")
                  ->orWhere('tipopago', 'LIKE', "%{$search}%")
                  ->orWhere('estado', 'LIKE', "%{$search}%")
                  ->orWhere('saldopendiente', 'LIKE', "%{$search}%")
                  ->orWhere('registradopor', 'LIKE', "%{$search}%")
                  ->orWhereHas('proveedor', function($q2) use ($search) {
                      $q2->where('nombre', 'LIKE', "%{$search}%")
                         ->orWhere('razonsocial', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%")
                         ->orWhere('telefono', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // 5. Ejecutar la consulta con paginación
        $ordencompras = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // 6. Mantener los parámetros en los links de paginación
        $ordencompras->appends($request->all());
        
        // 7. Retornar la vista
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
        $request->validate([
            'proveedor_id'         => 'required|exists:proveedores,id',
            'fecha'                => 'required|date',
            'tipopago'             => 'required|in:contado,credito',
            'productos'            => 'required|array|min:1',
            'productos.*.id'       => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

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

        DB::beginTransaction();

        try {
            $usuarioResponsable = auth()->user()->name ?? 'Sistema';
            $total = 0;

            $orden = OrdenCompra::create([
                'fecha'              => $request->fecha,
                'proveedor_id'       => $request->proveedor_id,
                'total'              => 0,
                'tipopago'           => $request->tipopago,
                'saldopendiente'     => 0,
                'estado'             => '1',
                'registradopor'      => $usuarioResponsable,
            ]);

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

                if ($abonoInicial < 0 || $abonoInicial > $total) {
                    DB::rollback();
                    return back()->withErrors('El abono inicial no puede ser negativo ni mayor al total.')->withInput();
                }

                $nuevoSaldo = $total - $abonoInicial;
                $orden->update(['total' => $total, 'saldopendiente' => $nuevoSaldo]);

                if ($abonoInicial > 0) {
                    Pago::create([
                        'ordencompra_id' => $orden->id,
                        'fechapago'      => now(),
                        'monto'          => $abonoInicial,
                        'metodopago_id'  => $request->metodopago_id ?? 1,
                        'registradopor'  => $usuarioResponsable,
                    ]);
                }

                $mensaje = 'Orden de compra a crédito registrada. Saldo pendiente: $' . number_format($nuevoSaldo, 2);
            }

            DB::commit();
            return redirect()->route('ordencompras.index')->with('success', $mensaje);

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al registrar orden: ' . $e->getMessage());
            return redirect()->route('ordencompras.index')
                ->with('error', 'Ocurrió un error al registrar la orden.');
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
        $orden = OrdenCompra::findOrFail($id);

        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha'        => 'required|date',
            'tipopago'     => 'required|in:contado,credito',
        ]);

        try {
            $orden->update([
                'fecha'        => $request->fecha,
                'proveedor_id' => $request->proveedor_id,
                'tipopago'     => $request->tipopago,
            ]);

            return redirect()->route('ordencompras.index')
                ->with('success', 'Orden actualizada correctamente.');

        } catch (Exception $e) {
            Log::error('Error al actualizar orden: ' . $e->getMessage());
            return redirect()->route('ordencompras.index')
                ->with('error', 'Ocurrió un error al actualizar la orden.');
        }
    }

    public function destroy($id)
    {
        $orden = OrdenCompra::find($id);

        if (!$orden) {
            return redirect()->route('ordencompras.index')
                ->with('error', 'Orden no encontrada.');
        }

        if ($orden->saldopendiente > 0) {
            return redirect()->route('ordencompras.index')
                ->with('error', 'No se puede eliminar la orden porque tiene saldo pendiente.');
        }

        DB::beginTransaction();

        try {
            foreach ($orden->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto) {
                    $producto->stock -= $detalle->cantidad;
                    if ($producto->stock < 0) $producto->stock = 0;
                    $producto->save();
                }
            }

            $orden->detalles()->delete();
            $orden->pagos()->delete();
            $orden->delete();

            DB::commit();
            return redirect()->route('ordencompras.index')
                ->with('success', 'Orden eliminada correctamente y stock revertido.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar orden: ' . $e->getMessage());
            return redirect()->route('ordencompras.index')
                ->with('error', 'Ocurrió un error al eliminar la orden.');
        }
    }

    public function cambioestado(Request $request)
    {
        $orden = OrdenCompra::find($request->id);

        if (!$orden) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
        }

        $orden->estado = $request->estado;
        $orden->save();

        return response()->json(['success' => true]);
    }

    public function generarPDF($id)
    {
        $orden = OrdenCompra::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        $data = ['orden' => $orden, 'fecha' => now()->format('d/m/Y H:i')];
        $pdf = Pdf::loadView('ordencompras.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('orden-compra-' . $orden->id . '.pdf');
    }

    public function generarExcel($id)
    {
        $orden = OrdenCompra::with(['proveedor', 'detalles.producto'])->findOrFail($id);
        return Excel::download(new OrdenCompraExport($orden), 'orden-compra-' . $id . '.xlsx');
    }

    public function generarExcelGeneral()
    {
        return Excel::download(new OrdenesExport(), 'ordenes-compras.xlsx');
    }
}