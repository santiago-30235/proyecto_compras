<?php

namespace App\Http\Controllers;

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
        $proveedores = Proveedor::all();
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        // 1. Validaciones: Se incluye la dirección obligatoria exigida por PostgreSQL
        $request->validate([
            'nombre'    => 'required|unique:proveedores,nombre',
            'documento' => 'required|unique:proveedores,documento',
            'direccion' => 'required',
            'telefono'  => 'required',
            'email'     => 'required|email|unique:proveedores,email',
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
                ->with('successMsg', 'Proveedor registrado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al guardar proveedor: ' . $e->getMessage());
            return redirect()->route('proveedores.index')
                ->withErrors('Ocurrió un error al intentar guardar el proveedor.');
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
        // 1. Validaciones para actualización (protegiendo también la dirección)
        $request->validate([
            'nombre'    => 'required|unique:proveedores,nombre,' . $id,
            'documento' => 'required|unique:proveedores,documento,' . $id,
            'direccion' => 'required',
            'telefono'  => 'required',
            'email'     => 'required|email|unique:proveedores,email,' . $id,
        ]);

        try {
            $proveedor = Proveedor::findOrFail($id);

            $proveedor->update([
                'nombre'        => $request->nombre,
                'documento'     => $request->documento,
                'direccion'     => $request->direccion,
                'telefono'      => $request->telefono,
                'email'         => $request->email,
                'registradopor' => auth()->check() ? auth()->user()->name : 'Sistema',
            ]);

            return redirect()->route('proveedores.index')
                ->with('successMsg', 'Proveedor actualizado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al actualizar proveedor: ' . $e->getMessage());
            return redirect()->route('proveedores.index')
                ->withErrors('Ocurrió un error al intentar actualizar el proveedor.');
        }
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        // Candado de seguridad: Verificar si tiene órdenes de compra asociadas
        $tieneOrdenes = false;
        if (method_exists($proveedor, 'ordencompras')) {
            $tieneOrdenes = $proveedor->ordencompras()->exists();
        } elseif (method_exists($proveedor, 'ordenes')) {
            $tieneOrdenes = $proveedor->ordenes()->exists();
        } else {
            // Plan B de consulta directa por si acaso en Render
            $tieneOrdenes = DB::table('ordencompras')->where('proveedor_id', $id)->exists() 
                         || DB::table('orden_compras')->where('proveedor_id', $id)->exists();
        }

        if ($tieneOrdenes) {
            return redirect()->route('proveedores.index')
                ->withErrors('No se puede eliminar este proveedor porque tiene órdenes de compra asociadas.');
        }

        try {
            $proveedor->delete();
            return redirect()->route('proveedores.index')
                ->with('successMsg', 'Proveedor eliminado correctamente.');
        } catch (Exception $e) {
            Log::error('Error al eliminar proveedor: ' . $e->getMessage());
            return redirect()->route('proveedores.index')
                ->withErrors('Ocurrió un error al intentar eliminar el proveedor.');
        }
    }

    public function cambioestado(Request $request)
    {
        $proveedor = Proveedor::find($request->id);
        if ($proveedor) {
            $proveedor->estado = $request->estado;
            $proveedor->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}