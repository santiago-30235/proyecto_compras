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
                'ordencompras.created_at as created_at',
                'ordencompras.total',
                'ordencompras.tipopago',
                DB::raw('COALESCE(SUM(pagos.monto), 0) as total_abonado')
            )
            ->groupBy(
                'ordencompras.id',
                'proveedores.nombre',
                'ordencompras.fecha',
                'ordencompras.created_at',
                'ordencompras.total',
                'ordencompras.tipopago'
            )
            ->orderBy('ordencompras.id')
            ->get();

        return $ordenes->map(function ($orden) {
            $totalAbonado = $orden->tipopago === 'contado'
                ? $orden->total
                : $orden->total_abonado;

            $saldoPendiente = $orden->tipopago === 'contado'
                ? 0
                : max(0, $orden->total - $orden->total_abonado);

            $estadoPago = $orden->tipopago === 'contado'
                ? 'PAGADO TOTALMENTE'
                : ($saldoPendiente > 0 ? 'CON DEUDA PENDIENTE' : 'CRÉDITO LIBERADO');

            return [
                'ID' => $orden->id,
                'PROVEEDOR' => $orden->proveedor,
                'FECHA' => date('Y-m-d', strtotime($orden->fecha)),
                'HORA' => date('h:i A', strtotime($orden->fecha)),
                'FECHA Y HORA DE REGISTRO' => date('Y-m-d h:i A', strtotime($orden->created_at)),
                'TIPO PAGO' => strtoupper($orden->tipopago),
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
            'FECHA Y HORA DE REGISTRO',
            'TIPO PAGO',
            'TOTAL ORDEN',
            'TOTAL ABONADO',
            'SALDO PENDIENTE',
            'ESTADO PAGO'
        ];
    }
}