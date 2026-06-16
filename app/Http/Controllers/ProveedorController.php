<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

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
        $request->validate([
            'nombre'    => 'required|unique:proveedores,nombre',
            'documento' => 'required|unique:proveedores,documento',
            'telefono'  => 'required',
            'email'     => 'required|email|unique:proveedores,email',
        ]);

        Proveedor::create([
            'nombre'       => $request->nombre,
            'documento'    => $request->documento,
            'direccion'    => $request->direccion,
            'telefono'     => $request->telefono,
            'email'        => $request->email,
            'estado'       => 1,
            'registradopor'=> auth()->user()->name,
        ]);

        return redirect()->route('proveedores.index')->with('successMsg', 'El registro se guardó exitosamente');
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
            'nombre'    => 'required|unique:proveedores,nombre,' . $id,
            'documento' => 'required|unique:proveedores,documento,' . $id,
            'telefono'  => 'required',
            'email'     => 'required|email|unique:proveedores,email,' . $id,
        ]);

        $proveedor = Proveedor::findOrFail($id);

        $proveedor->update([
            'nombre'       => $request->nombre,
            'documento'    => $request->documento,
            'direccion'    => $request->direccion,
            'telefono'     => $request->telefono,
            'email'        => $request->email,
            'registradopor'=> auth()->user()->name,
        ]);

        return redirect()->route('proveedores.index')->with('successMsg', 'El registro se actualizó exitosamente');
    }

   public function destroy($id)
{
    // 1. Buscar el proveedor o lanzar un 404 si no existe
    $proveedor = Proveedor::findOrFail($id);

    // 2. Validar si tiene órdenes de compra asociadas para proteger la base de datos
    $tieneOrdenes = false;
    if (method_exists($proveedor, 'ordencompras')) {
        $tieneOrdenes = $proveedor->ordencompras()->exists();
    } elseif (method_exists($proveedor, 'ordenes')) {
        $tieneOrdenes = $proveedor->ordenes()->exists();
    } else {
        // Plan B: Consulta directa para asegurar que corra perfecto en Render
        $tieneOrdenes = \DB::table('ordencompras')->where('proveedor_id', $id)->exists() 
                     || \DB::table('orden_compras')->where('proveedor_id', $id)->exists();
    }

    //  Si tiene historial, mensaje corto y directo
    if ($tieneOrdenes) {
        return redirect()->route('proveedores.index')
            ->withErrors('No se puede eliminar este proveedor porque tiene órdenes de compra asociadas.');
    }

    //  Si está libre de registros, eliminación normal
    try {
        $proveedor->delete();
        return redirect()->route('proveedores.index')
            ->with('successMsg', 'Proveedor eliminado correctamente.');
    } catch (\Exception $e) {
        return redirect()->route('proveedores.index')
            ->withErrors('Ocurrió un error al intentar eliminar el proveedor.');
    }
}

    public function cambioestado(Request $request)
    {
        $proveedor = Proveedor::find($request->id);
        $proveedor->estado = $request->estado;
        $proveedor->save();
        return response()->json(['success' => true]);
    }
}