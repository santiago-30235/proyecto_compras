<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdenesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $ordenes = DB::table('ordencompras')
            ->leftJoin('proveedores', 'ordencompras.proveedor_id', '=', 'proveedores.id')
            ->leftJoin('pagos', 'ordencompras.id', '=', 'pagos.ordencompra_id')
            ->select(
                'ordencompras.id',
                'proveedores.nombre as proveedor',
                'ordencompras.fecha',
                'ordencompras.total',
                'ordencompras.tipopago',
                DB::raw('COALESCE(SUM(pagos.monto), 0) as total_abonado')
            )
            ->groupBy(
                'ordencompras.id',
                'proveedores.nombre',
                'ordencompras.fecha',
                'ordencompras.total',
                'ordencompras.tipopago'
            )
            ->orderBy('ordencompras.id')
            ->get();

        return $ordenes->map(function ($orden) {
            // Normalizamos el tipo de pago para evitar conflictos de mayúsculas en BD
            $tipoPagoLower = strtolower(trim($orden->tipopago));

            // Lógica financiera de abonos y saldos
            $totalAbonado = $tipoPagoLower === 'contado'
                ? $orden->total
                : $orden->total_abonado;

            $saldoPendiente = $tipoPagoLower === 'contado'
                ? 0
                : max(0, $orden->total - $orden->total_abonado);

            // 1. Formateo estético del Tipo de Pago (Con buena ortografía y tilde)
            $tipoPagoFormatted = $tipoPagoLower === 'contado' ? 'Contado' : 'Crédito';

            // 2. Simplificación a solo DOS estados profesionales como acordamos
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