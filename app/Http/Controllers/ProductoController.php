<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::all();
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create');
    }

public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required',
        'preciocompra' => 'required|numeric|min:0',
        'stockmaximo' => 'required|integer|min:0',
        'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    $productoExistente = Producto::where('nombre', $request->nombre)->first();

    if ($productoExistente) {
        // SOLO SUMAR AL STOCK MÁXIMO, NO AL STOCK NORMAL
        $productoExistente->stockmaximo += $request->stockmaximo;
        $productoExistente->preciocompra = $request->preciocompra;
        $productoExistente->save();
        return redirect()->route('productos.index')->with('successMsg', 'Stock máximo actualizado');
    }

    // Nuevo producto: stock normal = 0, stockmaximo = lo que ponga
    $rutaImagen = null;
    if ($request->hasFile('imagen')) {
        $file = $request->file('imagen');
        $nombre = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/productos'), $nombre);
        $rutaImagen = 'images/productos/' . $nombre;
    }

    Producto::create([
        'nombre' => $request->nombre,
        'preciocompra' => $request->preciocompra,
        'descripcion' => $request->descripcion,
        'stockmaximo' => $request->stockmaximo,
        'stock' => 0,  // ← NUEVO PRODUCTO EMPIEZA CON STOCK 0
        'imagen' => $rutaImagen,
        'estado' => '1',
        'registradopor' => auth()->user()->name,
    ]);

    return redirect()->route('productos.index')->with('successMsg', 'Producto registrado exitosamente');
}

    public function show($id)
    {
        $producto = Producto::findOrFail($id);
        return view('productos.show', compact('producto'));
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        return view('productos.edit', compact('producto'));
    }

public function update(Request $request, $id)
{
    $producto = Producto::findOrFail($id);

    $request->validate([
        'nombre' => 'required|unique:productos,nombre,' . $id,
        'preciocompra' => 'required|numeric|min:0',
        'stockmaximo' => 'required|integer|min:0',
        'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    $rutaImagen = $producto->imagen;
    if ($request->hasFile('imagen')) {
        $file = $request->file('imagen');
        $nombre = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/productos'), $nombre);
        $rutaImagen = 'images/productos/' . $nombre;
    }

    $producto->update([
        'nombre' => $request->nombre,
        'preciocompra' => $request->preciocompra,
        'descripcion' => $request->descripcion,
        'stockmaximo' => $request->stockmaximo,
        // stock NO se modifica aquí
        'imagen' => $rutaImagen,
        'registradopor' => auth()->user()->name,
    ]);

    return redirect()->route('productos.index')->with('successMsg', 'Producto actualizado exitosamente');
}

   public function destroy($id)
{
    try {
        $producto = Producto::findOrFail($id);
        $producto->delete();
        
        return redirect()->route('productos.index')
            ->with('successMsg', 'Producto eliminado correctamente.');

    } catch (QueryException $e) {
        // Log interno para ti, pero el usuario ve un mensaje corto y limpio
        Log::error('Error al eliminar el producto: ' . $e->getMessage());
        
        return redirect()->route('productos.index')
            ->withErrors('No se puede eliminar este producto porque está asociado a una orden de compra.');

    } catch (Exception $e) {
        Log::error('Error inesperado: ' . $e->getMessage());
        
        return redirect()->route('productos.index')
            ->withErrors('Ocurrió un error al intentar eliminar el producto.');
    }
}

    public function cambioestado(Request $request)
    {
        $producto = Producto::find($request->id);
        if ($producto) {
            $producto->estado = $request->estado;
            $producto->save();
        }
        return response()->json(['success' => true]);
    }
}