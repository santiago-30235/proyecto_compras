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
        $productos = Producto::orderBy('nombre')->paginate(10);
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255|unique:productos,nombre',
            'preciocompra' => 'required|numeric|min:0',
            'stockmaximo'  => 'required|integer|min:0',
            'imagen'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            // Verificar si el producto ya existe
            $productoExistente = Producto::where('nombre', $request->nombre)->first();

            if ($productoExistente) {
                $productoExistente->stockmaximo += $request->stockmaximo;
                $productoExistente->preciocompra = $request->preciocompra;
                $productoExistente->save();

                return redirect()->route('productos.index')
                    ->with('success', 'Stock máximo actualizado correctamente.');
            }

            // Procesar imagen
            $rutaImagen = 'sin-imagen.png';
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
                'stock'         => 0, // Producto nuevo inicia sin stock
                'imagen'        => $rutaImagen,
                'estado'        => 1,
                'registradopor' => auth()->user()->name ?? 'Sistema',
            ]);

            return redirect()->route('productos.index')
                ->with('success', 'Producto registrado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al registrar producto: ' . $e->getMessage());
            return redirect()->route('productos.index')
                ->with('error', 'Ocurrió un error al intentar registrar el producto.');
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
            'nombre'       => 'required|string|max:255|unique:productos,nombre,' . $id,
            'preciocompra' => 'required|numeric|min:0',
            'stockmaximo'  => 'required|integer|min:0',
            'imagen'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            $rutaImagen = $producto->imagen ?? 'sin-imagen.png';

            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si no es la por defecto
                if ($producto->imagen && $producto->imagen !== 'sin-imagen.png' && file_exists(public_path($producto->imagen))) {
                    unlink(public_path($producto->imagen));
                }

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
                // 'stock' no se actualiza aquí (solo se actualiza con compras)
                // 'estado' no se actualiza aquí (se maneja con toggle)
                // 'registradopor' no se actualiza aquí (es histórico)
            ]);

            return redirect()->route('productos.index')
                ->with('success', 'Producto actualizado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return redirect()->route('productos.index')
                ->with('error', 'Ocurrió un error al intentar actualizar el producto.');
        }
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return redirect()->route('productos.index')
                ->with('error', 'Producto no encontrado.');
        }

        // Verificar si tiene detalles de compra asociados
        if ($producto->detallescompras()->exists()) {
            return redirect()->route('productos.index')
                ->with('error', 'No se puede eliminar este producto porque está asociado a una orden de compra.');
        }

        try {
            // Eliminar imagen si no es la por defecto
            if ($producto->imagen && $producto->imagen !== 'sin-imagen.png' && file_exists(public_path($producto->imagen))) {
                unlink(public_path($producto->imagen));
            }

            $producto->delete();

            return redirect()->route('productos.index')
                ->with('success', 'Producto eliminado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al eliminar producto: ' . $e->getMessage());
            return redirect()->route('productos.index')
                ->with('error', 'Ocurrió un error al intentar eliminar el producto.');
        }
    }

    public function cambioestado(Request $request)
    {
        $producto = Producto::find($request->id);

        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
        }

        $producto->estado = $request->estado;
        $producto->save();

        return response()->json(['success' => true]);
    }
}