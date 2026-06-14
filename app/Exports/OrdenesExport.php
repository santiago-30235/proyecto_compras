<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdenesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Traemos los datos directamente de la tabla ordencompras, incluyendo el saldo real
        $ordenes = DB::table('ordencompras')
            ->leftJoin('proveedores', 'ordencompras.proveedor_id', '=', 'proveedores.id')
            ->select(
                'ordencompras.id',
                'proveedores.nombre as proveedor',
                'ordencompras.fecha',
                'ordencompras.total',
                'ordencompras.tipopago',
                'ordencompras.saldo' // <-- Campo clave para sincronizar con la pantalla web
            )
            ->orderBy('ordencompras.id')
            ->get();

        return $ordenes->map(function ($orden) {
            // Normalizamos el tipo de pago para evitar errores de mayúsculas
            $tipoPagoLower = strtolower(trim($orden->tipopago));

            // 1. Formateo estético del Tipo de Pago
            $tipoPagoFormatted = $tipoPagoLower === 'contado' ? 'Contado' : 'Crédito';

            // 2. El saldo pendiente será exactamente el mismo que muestra la web
            $saldoPendiente = $orden->saldo;

            // 3. El total abonado lo calculamos de forma lógica (Total - Saldo) 
            // Esto limpia automáticamente cualquier dato basura de pruebas en la tabla pagos
            $totalAbonado = max(0, $orden->total - $saldoPendiente);

            // 4. Simplificación a los dos estados profesionales acordados
            $estadoPago = $saldoPendiente <= 0 ? 'Pagado' : 'Deuda Pendiente';

            return [
                'ID' => $orden->id,
                'PROVEEDOR' => $orden->proveedor,
                'FECHA' => date('Y-m-d', strtotime($orden->fecha)),
                'HORA' => date('h:i A', strtotime($orden->fecha)),
                'TIPO PAGO' => $tipoPagoFormatted,
                'TOTAL ORDEN' => $orden->total,
                'TOTAL ABONADO' => $totalAbonado,
                'SALDO PENDIENTE' => $saldoPendiente,
                'ESTADO PAGO' => $estadoPago,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'PROVEEDOR',
            'FECHA',
            'HORA',
            'TIPO PAGO',
            'TOTAL ORDEN',
            'TOTAL ABONADO',
            'SALDO PENDIENTE',
            'ESTADO PAGO'
        ];
    }
}