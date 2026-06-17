<?php

namespace App\Http\Controllers;

use App\Models\Metodopago;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class MetodopagoController extends Controller
{
    public function index()
    {
        $metodopagos = Metodopago::orderBy('nombre')->paginate(10);
        return view('metodopagos.index', compact('metodopagos'));
    }

    public function create()
    {
        return view('metodopagos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:metodopagos,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {
            Metodopago::create([
                'nombre'        => $request->nombre,
                'descripcion'   => $request->descripcion,
                'estado'        => 1,
                'registradopor' => auth()->user()->name ?? 'Sistema',
            ]);

            return redirect()->route('metodopagos.index')
                ->with('success', 'Método de pago registrado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al registrar método de pago: ' . $e->getMessage());
            return redirect()->route('metodopagos.index')
                ->with('error', 'Ocurrió un error al intentar registrar el método de pago.');
        }
    }

    public function show($id)
    {
        $metodopago = Metodopago::findOrFail($id);
        return view('metodopagos.show', compact('metodopago'));
    }

    public function edit($id)
    {
        $metodopago = Metodopago::findOrFail($id);
        return view('metodopagos.edit', compact('metodopago'));
    }

    public function update(Request $request, $id)
    {
        $metodopago = Metodopago::findOrFail($id);

        $request->validate([
            'nombre'      => 'required|string|max:255|unique:metodopagos,nombre,' . $id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {
            $metodopago->update([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion,
                // 'estado' no se actualiza aquí (se maneja con toggle)
                // 'registradopor' no se actualiza aquí (es histórico)
            ]);

            return redirect()->route('metodopagos.index')
                ->with('success', 'Método de pago actualizado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al actualizar método de pago: ' . $e->getMessage());
            return redirect()->route('metodopagos.index')
                ->with('error', 'Ocurrió un error al intentar actualizar el método de pago.');
        }
    }

    public function destroy($id)
    {
        $metodopago = Metodopago::find($id);

        if (!$metodopago) {
            return redirect()->route('metodopagos.index')
                ->with('error', 'Método de pago no encontrado.');
        }

        // Verificar si tiene pagos asociados
        if ($metodopago->pagos()->exists()) {
            return redirect()->route('metodopagos.index')
                ->with('error', 'No se puede eliminar este método de pago porque está asociado a uno o más pagos.');
        }

        try {
            $metodopago->delete();
            return redirect()->route('metodopagos.index')
                ->with('success', 'Método de pago eliminado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al eliminar método de pago: ' . $e->getMessage());
            return redirect()->route('metodopagos.index')
                ->with('error', 'Ocurrió un error al intentar eliminar el método de pago.');
        }
    }

    public function cambioestado(Request $request)
    {
        $metodopago = Metodopago::find($request->id);

        if (!$metodopago) {
            return response()->json(['success' => false, 'message' => 'Método de pago no encontrado'], 404);
        }

        $metodopago->estado = $request->estado;
        $metodopago->save();

        return response()->json(['success' => true]);
    }
}