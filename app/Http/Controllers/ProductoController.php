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
            'nombre'       => 'required',
            'preciocompra' => 'required|numeric|min:0',
            'stockmaximo'  => 'required|integer|min:0',
            'imagen'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            $productoExistente = Producto::where('nombre', $request->nombre)->first();

            // Si el producto ya existe, se actualizan topes y precios de forma segura
            if ($productoExistente) {
                $productoExistente->stockmaximo += $request->stockmaximo;
                $productoExistente->preciocompra = $request->preciocompra;
                $productoExistente->registradopor = auth()->check() ? auth()->user()->name : 'Sistema';
                $productoExistente->save();
                
                return redirect()->route('productos.index')
                    ->with('successMsg', 'Stock máximo actualizado correctamente.');
            }

            // Si es un producto nuevo, se procesa la imagen
            $rutaImagen = null;
            if ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $nombre = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/productos'), $nombre);
                $rutaImagen = 'images/productos/' . $nombre;
            }

            Producto::create([
                'nombre'        => $request->nombre,
                'preciocompra'  => $request->preciocompra,
                'descripcion'   => $request->descripcion,
                'stockmaximo'   => $request->stockmaximo,
                'stock'         => 0,  // Nuevo producto arranca en ceros obligatoriamente
                'imagen'        => $rutaImagen,
                'estado'        => '1',
                'registradopor' => auth()->check() ? auth()->user()->name : 'Sistema',
            ]);

            return redirect()->route('productos.index')
                ->with('successMsg', 'Producto registrado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al registrar producto: ' . $e->getMessage());
            return redirect()->route('productos.index')
                ->withErrors('Ocurrió un error al intentar registrar el producto.');
        }
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
            'nombre'       => 'required|unique:productos,nombre,' . $id,
            'preciocompra' => 'required|numeric|min:0',
            'stockmaximo'  => 'required|integer|min:0',
            'imagen'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            $rutaImagen = $producto->imagen;
            if ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $nombre = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/productos'), $nombre);
                $rutaImagen = 'images/productos/' . $nombre;
            }

            $producto->update([
                'nombre'        => $request->nombre,
                'preciocompra'  => $request->preciocompra,
                'descripcion'   => $request->descripcion,
                'stockmaximo'   => $request->stockmaximo,
                'imagen'        => $rutaImagen,
                'registradopor' => auth()->check() ? auth()->user()->name : 'Sistema',
            ]);

            return redirect()->route('productos.index')
                ->with('successMsg', 'Producto actualizado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return redirect()->route('productos.index')
                ->withErrors('Ocurrió un error al intentar actualizar el producto.');
        }
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->delete();
            
            return redirect()->route('productos.index')
                ->with('successMsg', 'Producto eliminado correctamente.');

        } catch (QueryException $e) {
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
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}