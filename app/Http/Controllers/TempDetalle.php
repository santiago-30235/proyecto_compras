<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Kardex;
use Illuminate\Http\Request;

class DetalleCompraController extends Controller
{
    // =========================
    // LISTAR
    // =========================
    public function index()
    {
        // ðŸ”¥ cargamos orden y producto para poder mostrar datos relacionados
        $detalles = DetalleCompra::with(['ordenCompra', 'producto'])
            ->paginate(10);

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
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        // ðŸ’¡ aumenta stock
        $producto->stockmaximo += $request->cantidad;
        $producto->save();

        // ðŸ’¡ crear detalle
        $detalle = DetalleCompra::create([
            'ordencompra_id' => $request->ordencompra_id,
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad,
            'subtotal' => $request->subtotal,
            'registradopor' => auth()->user()->name
        ]);

        // ðŸ’¡ actualizar orden
        $orden = OrdenCompra::findOrFail($request->ordencompra_id);
        $orden->total += $request->subtotal;
        $orden->saldopendiente = $orden->total;
        $orden->save();

        // ðŸ’¡ kardex entrada
        Kardex::create([
            'producto_id' => $producto->id,
            'tipo' => 'entrada',
            'cantidad' => $request->cantidad,
            'referencia' => 'Compra #' . $orden->id,
            'registradopor' => auth()->user()->name
        ]);

        return redirect()->route('detallecompras.index')
            ->with('success', 'Compra registrada correctamente');
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
        $detalle = DetalleCompra::findOrFail($id);

        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        // revertir stock anterior
        $producto->stockmaximo -= $detalle->cantidad;

        // aplicar nuevo stock
        $producto->stockmaximo += $request->cantidad;

        $producto->save();

        $detalle->update([
            'ordencompra_id' => $request->ordencompra_id,
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad,
            'subtotal' => $request->subtotal,
            'registradopor' => auth()->user()->name
        ]);

        Kardex::create([
            'producto_id' => $producto->id,
            'tipo' => 'ajuste',
            'cantidad' => $request->cantidad,
            'referencia' => 'EdiciÃ³n detalle #' . $detalle->id,
            'registradopor' => auth()->user()->name
        ]);

        return redirect()->route('detallecompras.index')
            ->with('success', 'Actualizado correctamente');
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $detalle = DetalleCompra::findOrFail($id);

        $producto = Producto::findOrFail($detalle->producto_id);

        $producto->stockmaximo -= $detalle->cantidad;
        $producto->save();

        Kardex::create([
            'producto_id' => $producto->id,
            'tipo' => 'salida',
            'cantidad' => $detalle->cantidad,
            'referencia' => 'EliminaciÃ³n detalle #' . $detalle->id,
            'registradopor' => auth()->user()->name
        ]);

        $detalle->delete();

        return redirect()->route('detallecompras.index')
            ->with('success', 'Eliminado correctamente');
    }
}



