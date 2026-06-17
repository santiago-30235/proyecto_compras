<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\OrdenCompra;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource with dynamic pagination and search.
     */
    public function index(Request $request)
{
    // 1. Construir la consulta base con JOIN para ordenar por nombre del proveedor
    $query = Pago::with(['ordenCompra', 'ordenCompra.proveedor', 'metodoPago'])
        ->join('ordencompras', 'pagos.ordencompra_id', '=', 'ordencompras.id')
        ->join('proveedores', 'ordencompras.proveedor_id', '=', 'proveedores.id')
        ->select('pagos.*');
    
    // 2. Aplicar búsqueda si existe
    $search = $request->input('search');
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('pagos.id', 'LIKE', "%{$search}%")
              ->orWhere('pagos.fechapago', 'LIKE', "%{$search}%")
              ->orWhere('pagos.monto', 'LIKE', "%{$search}%")
              ->orWhere('pagos.registradopor', 'LIKE', "%{$search}%")
              ->orWhere('ordencompras.id', 'LIKE', "%{$search}%")
              ->orWhere('ordencompras.total', 'LIKE', "%{$search}%")
              ->orWhere('proveedores.nombre', 'LIKE', "%{$search}%");
        });
    }
    
    // 3. ORDENAR ALFABÉTICAMENTE POR NOMBRE DEL PROVEEDOR
    // 4. get() en lugar de paginate() para que DataTables maneje la paginación
    $pagos = $query->orderBy('proveedores.nombre', 'asc')->get();
    
    // 5. Retornar la vista
    return view('pagos.index', compact('pagos'));
}
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ordenes = OrdenCompra::where('saldopendiente', '>', 0)->get();
        $metodos = MetodoPago::where('estado', '1')->get();
        return view('pagos.create', compact('ordenes', 'metodos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'metodopago_id'  => 'required|exists:metodopagos,id',
            'monto'          => 'required|numeric|min:0.01',
        ]);

        $orden = OrdenCompra::findOrFail($request->ordencompra_id);

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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pago = Pago::with(['ordenCompra.proveedor', 'metodoPago'])->findOrFail($id);
        return view('pagos.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pago = Pago::findOrFail($id);
        $ordenes = OrdenCompra::all();
        $metodos = MetodoPago::where('estado', '1')->get();
        return view('pagos.edit', compact('pago', 'ordenes', 'metodos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $request->validate([
            'ordencompra_id' => 'required|exists:ordencompras,id',
            'metodopago_id'  => 'required|exists:metodopagos,id',
            'monto'          => 'required|numeric|min:0.01',
        ]);

        $orden = $pago->ordenCompra;
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $pago = Pago::find($id);

    if (!$pago) {
        return redirect()->route('pagos.index')
            ->with('error', 'Pago no encontrado.');
    }

    //  LOS PAGOS NO SE PUEDEN ELIMINAR
    return redirect()->route('pagos.index')
        ->with('error', 'No se puede eliminar el pago #' . $pago->id . '. Los pagos no se eliminan por seguridad.');
}
    /**
     * Cambia el estado de un pago (Activo/Inactivo)
     */
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