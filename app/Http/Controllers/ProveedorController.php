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
    try {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado.'
            ], 404);
        }

        // Verificar todas las dependencias
        $errores = [];

        // 1. Verificar órdenes de compra
        if ($proveedor->ordenCompras()->exists()) {
            $count = $proveedor->ordenCompras()->count();
            $errores[] = "{$count} órdenes de compra asociadas";
        }

        // 2. Verificar productos asociados
        if ($proveedor->productos()->exists()) {
            $count = $proveedor->productos()->count();
            $errores[] = "{$count} productos asociados";
        }

        // 3. Verificar facturas (si tienes relación)
        if (method_exists($proveedor, 'facturas') && $proveedor->facturas()->exists()) {
            $count = $proveedor->facturas()->count();
            $errores[] = "{$count} facturas asociadas";
        }

        // 4. Verificar contratos (si tienes relación)
        if (method_exists($proveedor, 'contratos') && $proveedor->contratos()->exists()) {
            $count = $proveedor->contratos()->count();
            $errores[] = "{$count} contratos asociados";
        }

        // 5. Verificar compras (si tienes relación)
        if (method_exists($proveedor, 'compras') && $proveedor->compras()->exists()) {
            $count = $proveedor->compras()->count();
            $errores[] = "{$count} compras asociadas";
        }

        // Si hay errores, no se puede eliminar
        if (!empty($errores)) {
            $mensaje = 'No se puede eliminar este proveedor porque tiene: ' . implode(', ', $errores) . '.';
            
            return response()->json([
                'success' => false,
                'message' => $mensaje,
                'detalles' => $errores
            ], 400);
        }

        // Si no tiene dependencias, eliminar
        DB::beginTransaction();
        try {
            $proveedor->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado correctamente.'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar proveedor: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar eliminar el proveedor.'
            ], 500);
        }

    } catch (Exception $e) {
        Log::error('Error en destroy proveedor: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error al procesar la solicitud.'
        ], 500);
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