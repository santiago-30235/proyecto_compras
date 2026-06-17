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
        // 1. Obtener el número de registros por página (por defecto 10)
        $perPage = $request->input('per_page', 10);
        
        // 2. Obtener el término de búsqueda
        $search = $request->input('search');
        
        // 3. Construir la consulta base
        $query = Pago::with(['ordenCompra', 'metodoPago']);
        
        // 4. Aplicar búsqueda si existe
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('fechapago', 'LIKE', "%{$search}%")
                  ->orWhere('monto', 'LIKE', "%{$search}%")
                  ->orWhere('registradopor', 'LIKE', "%{$search}%")
                  ->orWhereHas('ordenCompra', function($q2) use ($search) {
                      $q2->where('id', 'LIKE', "%{$search}%")
                         ->orWhere('total', 'LIKE', "%{$search}%")
                         ->orWhere('tipopago', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('metodoPago', function($q3) use ($search) {
                      $q3->where('nombre', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // 5. Ejecutar la consulta con paginación
        $pagos = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // 6. Mantener los parámetros en los links de paginación
        $pagos->appends($request->all());
        
        // 7. Retornar la vista
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
            session()->flash('error', 'Pago no encontrado.');
            return redirect()->route('pagos.index');
        }

        $ordenesAsociadas = $pago->ordenCompra ? 1 : 0;

        if ($ordenesAsociadas > 0) {
            session()->flash('error', 'No se puede eliminar este pago porque tiene ' . $ordenesAsociadas . ' órdenes de compra asociadas.');
            return redirect()->route('pagos.index');
        }

        DB::beginTransaction();

        try {
            $pago->delete();

            DB::commit();
            session()->flash('success', 'Pago eliminado correctamente.');
            return redirect()->route('pagos.index');

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar pago: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al intentar eliminar el pago.');
            return redirect()->route('pagos.index');
        }
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