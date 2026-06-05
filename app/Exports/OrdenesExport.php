<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdenesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('ordencompras')
            ->join('proveedores', 'ordencompras.proveedor_id', '=', 'proveedores.id')
            ->select(
                'ordencompras.id',
                'proveedores.nombre as proveedor',
                'ordencompras.fecha',
                'ordencompras.total',
                'ordencompras.tipopago',
                'ordencompras.saldopendiente'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Proveedor',
            'Fecha',
            'Total',
            'Tipo Pago',
            'Saldo Pendiente'
        ];
    }
}