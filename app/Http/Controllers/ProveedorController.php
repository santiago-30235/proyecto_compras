<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre')->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255|unique:proveedores,nombre',
            'documento' => 'required|string|max:50|unique:proveedores,documento',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'required|string|max:20',
            'email'     => 'required|email|max:255|unique:proveedores,email',
        ]);

        try {
            Proveedor::create([
                'nombre'        => $request->nombre,
                'documento'     => $request->documento,
                'direccion'     => $request->direccion,
                'telefono'      => $request->telefono,
                'email'         => $request->email,
                'estado'        => 1,
                'registradopor' => auth()->check() ? auth()->user()->name : 'Sistema',
            ]);

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor registrado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al guardar proveedor: ' . $e->getMessage());
            return redirect()->route('proveedores.index')
                ->with('error', 'Ocurrió un error al intentar guardar el proveedor.');
        }
    }

    public function show($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255|unique:proveedores,nombre,' . $id,
            'documento' => 'required|string|max:50|unique:proveedores,documento,' . $id,
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'required|string|max:20',
            'email'     => 'required|email|max:255|unique:proveedores,email,' . $id,
        ]);

        try {
            $proveedor = Proveedor::findOrFail($id);
            $proveedor->update([
                'nombre'    => $request->nombre,
                'documento' => $request->documento,
                'direccion' => $request->direccion,
                'telefono'  => $request->telefono,
                'email'     => $request->email,
                // 'estado' no se actualiza aquí (se maneja con toggle)
                // 'registradopor' no se actualiza aquí (es histórico)
            ]);

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor actualizado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al actualizar proveedor: ' . $e->getMessage());
            return redirect()->route('proveedores.index')
                ->with('error', 'Ocurrió un error al intentar actualizar el proveedor.');
        }
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            session()->flash('error', 'Proveedor no encontrado.');
            return redirect()->route('proveedores.index');
        }

        $ordenesCompras = $proveedor->ordenCompras()->count();
        $ordenCompraIds = $proveedor->ordenCompras()->pluck('id');
        $productosAsociados = 0;

        if ($ordenCompraIds->isNotEmpty()) {
            $productosAsociados = DetalleCompra::whereIn('ordencompra_id', $ordenCompraIds)
                ->distinct()
                ->count('producto_id');
        }

        if ($ordenesCompras > 0 || $productosAsociados > 0) {
            session()->flash('error', 'No se puede eliminar este proveedor porque tiene ' . $ordenesCompras . ' órdenes de compra asociadas y ' . $productosAsociados . ' productos asociados.');
            return redirect()->route('proveedores.index');
        }

        try {
            $proveedor->delete();
            session()->flash('success', 'Proveedor eliminado correctamente.');
            return redirect()->route('proveedores.index');
        } catch (Exception $e) {
            Log::error('Error al eliminar proveedor: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al intentar eliminar el proveedor.');
            return redirect()->route('proveedores.index');
        }
    }

    public function cambioestado(Request $request)
    {
        $proveedor = Proveedor::find($request->id);

        if (!$proveedor) {
            return response()->json(['success' => false, 'message' => 'Proveedor no encontrado'], 404);
        }

        $proveedor->estado = $request->estado;
        $proveedor->save();

        return response()->json(['success' => true]);
    }
}