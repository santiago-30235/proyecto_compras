<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\OrdenCompra;
use App\Models\MetodoPago;
use App\Http\Requests\PagoRequest;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class PagoController extends Controller
{
    public function index()
{
    $pagos = Pago::with(['ordenCompra', 'metodoPago'])->get(); // ← get(), no paginate()
    return view('pagos.index', compact('pagos'));
}

    public function create()
    {
        $ordenes = OrdenCompra::where('saldopendiente', '>', 0)->get();
        $metodos = MetodoPago::where('estado', '1')->get();
        return view('pagos.create', compact('ordenes', 'metodos'));
    }

    public function store(PagoRequest $request)
    {
        $orden = OrdenCompra::findOrFail($request->ordencompra_id);

        // Validar que no se pague más del saldo pendiente
        if ($request->monto > $orden->saldopendiente) {
            return back()->withErrors('El monto no puede superar el saldo pendiente de $' . number_format($orden->saldopendiente, 2));
        }

        // Crear pago
        Pago::create([
            'ordencompra_id' => $request->ordencompra_id,
            'fechapago' => now(),
            'monto' => $request->monto,
            'metodopago_id' => $request->metodopago_id,
            'registradopor' => auth()->user()->name,
        ]);

        // Actualizar saldo pendiente
        $nuevoSaldo = $orden->saldopendiente - $request->monto;
        $orden->update([
            'saldopendiente' => $nuevoSaldo,
            'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'pendiente',
        ]);

        return redirect()->route('pagos.index')->with('successMsg', 'Pago registrado exitosamente');
    }

    public function show($id)
    {
        $pago = Pago::with(['ordenCompra.proveedor', 'metodoPago'])->findOrFail($id);
        return view('pagos.show', compact('pago'));
    }

    public function edit($id)
    {
        $pago = Pago::findOrFail($id);
        $ordenes = OrdenCompra::all();
        $metodos = MetodoPago::where('estado', '1')->get();
        return view('pagos.edit', compact('pago', 'ordenes', 'metodos'));
    }

    public function update(PagoRequest $request, $id)
    {
        $pago = Pago::findOrFail($id);
        $ordenAntes = $pago->ordenCompra;

        // Si cambia el monto, ajustar saldo pendiente
        if ($request->monto != $pago->monto) {
            $diferencia = $request->monto - $pago->monto;
            $nuevoSaldo = $ordenAntes->saldopendiente - $diferencia;
            
            if ($nuevoSaldo < 0) {
                return back()->withErrors('El nuevo monto excede el saldo pendiente');
            }
            
            $ordenAntes->update([
                'saldopendiente' => $nuevoSaldo,
                'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'pendiente',
            ]);
        }

        $pago->update([
            'ordencompra_id' => $request->ordencompra_id,
            'monto' => $request->monto,
            'metodopago_id' => $request->metodopago_id,
            'registradopor' => auth()->user()->name,
        ]);

        return redirect()->route('pagos.index')->with('successMsg', 'Pago actualizado exitosamente');
    }

    public function destroy($id)
{
    // 1. Iniciamos una transacción para que la contabilidad no se descuadre si algo falla
    \DB::beginTransaction();

    try {
        $pago = Pago::findOrFail($id);
        
        // Buscamos la orden usando la relación (con plan B por si acaso)
        $orden = $pago->ordenCompra ?? $pago->orden_compra ?? null;

        if ($orden) {
            // Restaurar saldo pendiente sumando el dinero del pago que se va a eliminar
            $nuevoSaldo = $orden->saldopendiente + $pago->monto;
            
            $orden->update([
                'saldopendiente' => $nuevoSaldo,
                'estado' => 'pendiente', // Mantiene el estado que ya tenías configurado
            ]);
        }

        // Eliminar físicamente el registro del pago
        $pago->delete();

        // Si todo el proceso fue exitoso, guardamos los cambios en la Base de Datos
        \DB::commit();

        return redirect()->route('pagos.index')
            ->with('successMsg', 'Pago eliminado correctamente.');

    } catch (\Exception $e) {
        // Si algo falla en la mitad, deshacemos los cambios del saldo para proteger tus cuentas
        \DB::rollback();
        
        \Log::error('Error al eliminar el pago: ' . $e->getMessage());

        return redirect()->route('pagos.index')
            ->withErrors('Ocurrió un error al intentar eliminar el pago.');
    }
}

    public function cambioestado(Request $request)
    {
        $pago = Pago::find($request->id);
        if ($pago) {
            $pago->estado = $request->estado;
            $pago->save();
        }
        return response()->json(['success' => true]);
    }
}