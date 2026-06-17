<?php

namespace App\Http\Controllers;

use App\Models\Detallecompra;
use App\Models\Ordencompra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DetalleCompraController extends Controller
{
    public function index()
    {
        $detalles = Detallecompra::with(['ordencompra', 'producto'])
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('detallecompras.index', compact('detalles'));
    }

    public function create()
    {
        $ordenes = Ordencompra::where('estado', '1')->get();
        $productos = Producto::where('estado', '1')->get();
        return view('detallecompras.create', compact('ordenes', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'producto_id'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'subtotal'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($request->producto_id);
            $orden = Ordencompra::findOrFail($request->ordencompra_id);
            $usuario = auth()->user()->name ?? 'Sistema';

            $nuevoStock = $producto->stock + $request->cantidad;
            if ($nuevoStock > $producto->stockmaximo) {
                DB::rollback();
                return redirect()->back()
                    ->withErrors("No se puede agregar esta cantidad. El stock máximo disponible para '{$producto->nombre}' es de {$producto->stockmaximo} unidades.")
                    ->withInput();
            }

            $producto->stock = $nuevoStock;
            $producto->save();

            Detallecompra::create([
                'ordencompra_id' => $request->ordencompra_id,
                'producto_id'    => $request->producto_id,
                'cantidad'       => $request->cantidad,
                'subtotal'       => $request->subtotal,
                'registradopor'  => $usuario,
            ]);

            $orden->total += $request->subtotal;
            $orden->saldopendiente += $request->subtotal;
            $orden->save();

            DB::commit();
            return redirect()->route('detallecompras.index')
                ->with('success', 'Detalle de compra registrado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al registrar detalle: ' . $e->getMessage());
            return redirect()->route('detallecompras.index')
                ->with('error', 'Ocurrió un error al registrar el detalle.');
        }
    }

    public function edit($id)
    {
        $detalle = Detallecompra::findOrFail($id);
        $ordenes = Ordencompra::where('estado', '1')->get();
        $productos = Producto::where('estado', '1')->get();
        return view('detallecompras.edit', compact('detalle', 'ordenes', 'productos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'producto_id'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'subtotal'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $detalle = Detallecompra::findOrFail($id);
            $producto = Producto::findOrFail($request->producto_id);
            $orden = Ordencompra::findOrFail($detalle->ordencompra_id);
            $usuario = auth()->user()->name ?? 'Sistema';

            $producto->stock -= $detalle->cantidad;
            $nuevoStock = $producto->stock + $request->cantidad;

            if ($nuevoStock > $producto->stockmaximo) {
                DB::rollback();
                return redirect()->back()
                    ->withErrors("La actualización supera el límite de stock máximo para '{$producto->nombre}'.")
                    ->withInput();
            }

            $producto->stock = $nuevoStock;
            $producto->save();

            $orden->total -= $detalle->subtotal;
            $orden->total += $request->subtotal;
            $orden->saldopendiente = $orden->total - $orden->pagos()->sum('monto');
            if ($orden->saldopendiente < 0) {
                $orden->saldopendiente = 0;
            }
            $orden->save();

            $detalle->update([
                'ordencompra_id' => $request->ordencompra_id,
                'producto_id'    => $request->producto_id,
                'cantidad'       => $request->cantidad,
                'subtotal'       => $request->subtotal,
                'registradopor'  => $usuario,
            ]);

            DB::commit();
            return redirect()->route('detallecompras.index')
                ->with('success', 'Detalle actualizado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar detalle: ' . $e->getMessage());
            return redirect()->route('detallecompras.index')
                ->with('error', 'Ocurrió un error al actualizar el detalle.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $detalle = Detallecompra::findOrFail($id);
            $producto = Producto::findOrFail($detalle->producto_id);
            $orden = Ordencompra::findOrFail($detalle->ordencompra_id);

            $producto->stock -= $detalle->cantidad;
            if ($producto->stock < 0) {
                $producto->stock = 0;
            }
            $producto->save();

            $orden->total -= $detalle->subtotal;
            $orden->saldopendiente = $orden->total - $orden->pagos()->sum('monto');
            if ($orden->saldopendiente < 0) {
                $orden->saldopendiente = 0;
            }
            $orden->save();

            $detalle->delete();

            DB::commit();
            return redirect()->route('detallecompras.index')
                ->with('success', 'Detalle eliminado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar detalle: ' . $e->getMessage());
            return redirect()->route('detallecompras.index')
                ->with('error', 'Ocurrió un error al eliminar el detalle.');
        }
    }
}