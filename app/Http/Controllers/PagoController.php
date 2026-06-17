<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Ordencompra;
use App\Models\Metodopago;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function index()
    {
        $pagos = Pago::with(['ordencompra', 'metodopago'])
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('pagos.index', compact('pagos'));
    }

    public function create()
    {
        $ordenes = Ordencompra::where('saldopendiente', '>', 0)->get();
        $metodos = Metodopago::where('estado', '1')->get();
        return view('pagos.create', compact('ordenes', 'metodos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'metodopago_id'  => 'required|exists:metodopagos,id',
            'monto'          => 'required|numeric|min:0.01',
        ]);

        $orden = Ordencompra::findOrFail($request->ordencompra_id);

        if ($request->monto > $orden->saldopendiente) {
            return back()->withErrors('El monto no puede superar el saldo pendiente de $' . number_format($orden->saldopendiente, 2));
        }

        DB::beginTransaction();

        try {
            Pago::create([
                'ordencompra_id' => $request->ordencompra_id,
                'fechapago'      => now(),
                'monto'          => $request->monto,
                'metodopago_id'  => $request->metodopago_id,
                'registradopor'  => auth()->user()->name ?? 'Sistema',
            ]);

            $nuevoSaldo = $orden->saldopendiente - $request->monto;
            $orden->update([
                'saldopendiente' => $nuevoSaldo,
                'estado'         => $nuevoSaldo <= 0 ? '1' : '1',
            ]);

            DB::commit();
            return redirect()->route('pagos.index')
                ->with('success', 'Pago registrado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al registrar pago: ' . $e->getMessage());
            return redirect()->route('pagos.index')
                ->with('error', 'Ocurrió un error al registrar el pago.');
        }
    }

    public function show($id)
    {
        $pago = Pago::with(['ordencompra.proveedor', 'metodopago'])->findOrFail($id);
        return view('pagos.show', compact('pago'));
    }

    public function edit($id)
    {
        $pago = Pago::findOrFail($id);
        $ordenes = Ordencompra::all();
        $metodos = Metodopago::where('estado', '1')->get();
        return view('pagos.edit', compact('pago', 'ordenes', 'metodos'));
    }

    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'metodopago_id'  => 'required|exists:metodopagos,id',
            'monto'          => 'required|numeric|min:0.01',
        ]);

        $orden = $pago->ordencompra;
        $diferencia = $request->monto - $pago->monto;
        $nuevoSaldo = $orden->saldopendiente - $diferencia;

        if ($nuevoSaldo < 0) {
            return back()->withErrors('El nuevo monto excede el saldo pendiente.');
        }

        DB::beginTransaction();

        try {
            $pago->update([
                'ordencompra_id' => $request->ordencompra_id,
                'monto'          => $request->monto,
                'metodopago_id'  => $request->metodopago_id,
                'registradopor'  => auth()->user()->name ?? 'Sistema',
            ]);

            $orden->update([
                'saldopendiente' => $nuevoSaldo,
                'estado'         => $nuevoSaldo <= 0 ? '1' : '1',
            ]);

            DB::commit();
            return redirect()->route('pagos.index')
                ->with('success', 'Pago actualizado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar pago: ' . $e->getMessage());
            return redirect()->route('pagos.index')
                ->with('error', 'Ocurrió un error al actualizar el pago.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $pago = Pago::findOrFail($id);
            $orden = $pago->ordencompra;

            if ($orden) {
                $nuevoSaldo = $orden->saldopendiente + $pago->monto;
                $orden->update([
                    'saldopendiente' => $nuevoSaldo,
                    'estado'         => '1',
                ]);
            }

            $pago->delete();

            DB::commit();
            return redirect()->route('pagos.index')
                ->with('success', 'Pago eliminado correctamente.');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar pago: ' . $e->getMessage());
            return redirect()->route('pagos.index')
                ->with('error', 'Ocurrió un error al eliminar el pago.');
        }
    }

    public function cambioestado(Request $request)
    {
        $pago = Pago::find($request->id);

        if (!$pago) {
            return response()->json(['success' => false, 'message' => 'Pago no encontrado'], 404);
        }

        $pago->estado = $request->estado;
        $pago->save();

        return response()->json(['success' => true]);
    }
}