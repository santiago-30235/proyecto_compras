<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdenesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Volvemos a la consulta original que SÍ funciona y no tumba el sistema
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
            $tipoPagoLower = strtolower(trim($orden->tipopago));

            // Conservamos tu cálculo matemático original para evitar conflictos
            $totalAbonado = $tipoPagoLower === 'contado'
                ? $orden->total
                : $orden->total_abonado;

            $saldoPendiente = $tipoPagoLower === 'contado'
                ? 0
                : max(0, $orden->total - $orden->total_abonado);

            // 1. Limpieza de texto (Formato título bien elegante)
            $tipoPagoFormatted = $tipoPagoLower === 'contado' ? 'Contado' : 'Crédito';

            // 2. Reducción estricta a solo los DOS estados que me pediste
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