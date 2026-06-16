<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Kardex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DetalleCompraController extends Controller
{
    // =========================
    // LISTAR
    // =========================
    public function index()
    {
        $detalles = DetalleCompra::with(['ordenCompra', 'producto'])->paginate(10);
        return view('detallecompras.index', compact('detalles'));
    }

    // =========================
    // CREATE
    // =========================
    public function create()
    {
        $ordenes = OrdenCompra::where('estado', 1)->get();
        $productos = Producto::where('estado', 1)->get();
        return view('detallecompras.create', compact('ordenes', 'productos'));
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'producto_id'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'subtotal'       => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($request->producto_id);
            $orden = OrdenCompra::findOrFail($request->ordencompra_id);
            $usuarioResponsable = auth()->check() ? auth()->user()->name : 'Sistema';

            // Validar que el nuevo stock real no supere el stock máximo permitido
            $nuevoStock = $producto->stock + $request->cantidad;
            if ($nuevoStock > $producto->stockmaximo) {
                $disponible = $producto->stockmaximo - $producto->stock;
                DB::rollback();
                return redirect()->back()
                    ->withErrors(["No se puede agregar esta cantidad. El espacio disponible en stock máximo para '{$producto->nombre}' es de {$disponible} unidades."])
                    ->withInput();
            }

            // Aumentar stock REAL de inventario
            $producto->stock += $request->cantidad;
            $producto->save();

            // Crear el detalle de compra
            $detalle = DetalleCompra::create([
                'ordencompra_id' => $request->ordencompra_id,
                'producto_id'    => $request->producto_id,
                'cantidad'       => $request->cantidad,
                'subtotal'       => $request->subtotal,
                'registradopor'  => $usuarioResponsable
            ]);

            // Actualizar la orden de compra financieramente
            $orden->total += $request->subtotal;
            $orden->saldopendiente = $orden->total - DB::table('pagos')->where('ordencompra_id', $orden->id)->sum('monto');
            if ($orden->saldopendiente < 0) {
                $orden->saldopendiente = 0;
            }
            $orden->save();

            // Registrar movimiento en el Kardex
            Kardex::create([
                'producto_id'    => $producto->id,
                'tipo'           => 'entrada',
                'cantidad'       => $request->cantidad,
                'referencia'     => 'Compra #' . $orden->id,
                'registradopor'  => $usuarioResponsable
            ]);

            DB::commit();
            return redirect()->route('detallecompras.index')
                ->with('successMsg', 'Detalle de compra registrado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al registrar detalle de compra: ' . $e->getMessage());
            return redirect()->route('detallecompras.index')
                ->withErrors('Ocurrió un error al intentar registrar el detalle de la compra.');
        }
    }

    // =========================
    // EDITAR
    // =========================
    public function edit($id)
    {
        $detalle = DetalleCompra::findOrFail($id);
        $ordenes = OrdenCompra::where('estado', 1)->get();
        $productos = Producto::where('estado', 1)->get();
        return view('detallecompras.edit', compact('detalle', 'ordenes', 'productos'));
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'producto_id'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'subtotal'       => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $detalle = DetalleCompra::findOrFail($id);
            $producto = Producto::findOrFail($request->producto_id);
            $ordenAnterior = OrdenCompra::findOrFail($detalle->ordencompra_id);
            $ordenNueva = OrdenCompra::findOrFail($request->ordencompra_id);
            $usuarioResponsable = auth()->check() ? auth()->user()->name : 'Sistema';

            // 1. Revertir cambios en el stock REAL del producto
            $producto->stock -= $detalle->cantidad;

            // Validar que el nuevo stock modificado no supere el stock máximo
            $nuevoStock = $producto->stock + $request->cantidad;
            if ($nuevoStock > $producto->stockmaximo) {
                DB::rollback();
                return redirect()->back()
                    ->withErrors(["La actualización supera el límite de stock máximo para '{$producto->nombre}'."])
                    ->withInput();
            }

            // Aplicar el nuevo stock real
            $producto->stock += $request->cantidad;
            if ($producto->stock < 0) {
                $producto->stock = 0;
            }
            $producto->save();

            // 2. Revertir montos de la orden anterior
            $ordenAnterior->total -= $detalle->subtotal;
            $ordenAnterior->saldopendiente = $ordenAnterior->total - DB::table('pagos')->where('ordencompra_id', $ordenAnterior->id)->sum('monto');
            if ($ordenAnterior->saldopendiente < 0) {
                $ordenAnterior->saldopendiente = 0;
            }
            $ordenAnterior->save();

            // 3. Actualizar el registro del detalle
            $detalle->update([
                'ordencompra_id' => $request->ordencompra_id,
                'producto_id'    => $request->producto_id,
                'cantidad'       => $request->cantidad,
                'subtotal'       => $request->subtotal,
                'registradopor'  => $usuarioResponsable
            ]);

            // 4. Aplicar montos a la orden seleccionada (puede ser la misma o una nueva)
            $ordenNueva->total += $request->subtotal;
            $ordenNueva->saldopendiente = $ordenNueva->total - DB::table('pagos')->where('ordencompra_id', $ordenNueva->id)->sum('monto');
            if ($ordenNueva->saldopendiente < 0) {
                $ordenNueva->saldopendiente = 0;
            }
            $ordenNueva->save();

            // 5. Registrar ajuste en Kardex
            Kardex::create([
                'producto_id'    => $producto->id,
                'tipo'           => 'ajuste',
                'cantidad'       => $request->cantidad,
                'referencia'     => 'Edición detalle #' . $detalle->id,
                'registradopor'  => $usuarioResponsable
            ]);

            DB::commit();
            return redirect()->route('detallecompras.index')
                ->with('successMsg', 'Detalle de compra actualizado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar detalle de compra: ' . $e->getMessage());
            return redirect()->route('detallecompras.index')
                ->withErrors('Ocurrió un error al intentar actualizar el detalle de la compra.');
        }
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $detalle = DetalleCompra::findOrFail($id);
            $producto = Producto::findOrFail($detalle->producto_id);
            $orden = OrdenCompra::findOrFail($detalle->ordencompra_id);
            $usuarioResponsable = auth()->check() ? auth()->user()->name : 'Sistema';

            // Descontar del stock REAL el inventario eliminado
            $producto->stock -= $detalle->cantidad;
            if ($producto->stock < 0) {
                $producto->stock = 0;
            }
            $producto->save();

            // Revertir el dinero de la orden de compra
            $orden->total -= $detalle->subtotal;
            $orden->saldopendiente = $orden->total - DB::table('pagos')->where('ordencompra_id', $orden->id)->sum('monto');
            if ($orden->saldopendiente < 0) {
                $orden->saldopendiente = 0;
            }
            $orden->save();

            // Kardex de salida por eliminación
            Kardex::create([
                'producto_id'    => $producto->id,
                'tipo'           => 'salida',
                'cantidad'       => $detalle->cantidad,
                'referencia'     => 'Eliminación detalle #' . $detalle->id,
                'registradopor'  => $usuarioResponsable
            ]);

            $detalle->delete();

            DB::commit();
            return redirect()->route('detallecompras.index')
                ->with('successMsg', 'Detalle de compra eliminado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar detalle de compra: ' . $e->getMessage());
            return redirect()->route('detallecompras.index')
                ->withErrors('Ocurrió un error al intentar eliminar el detalle de la compra.');
        }
    }
}