<?php

namespace App\Exports;

use App\Models\OrdenCompra;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdenCompraExport implements FromArray, WithHeadings, WithStyles
{
    protected $orden;

    public function __construct($orden)
    {
        $this->orden = $orden;
    }

    public function array(): array
    {
        $data = [];

        $data[] = ['=== DATOS DE LA ORDEN ==='];
        $data[] = ['ID', $this->orden->id];
        $data[] = ['Proveedor', $this->orden->proveedor->nombre ?? 'N/A'];
        $data[] = ['Fecha', date('d/m/Y', strtotime($this->orden->fecha))];
        $data[] = ['Tipo Pago', ucfirst($this->orden->tipopago)];
        $data[] = ['Total', '$' . number_format($this->orden->total, 2)];
        $data[] = ['Saldo Pendiente', '$' . number_format($this->orden->saldopendiente, 2)];
        $data[] = [];
        $data[] = ['=== DETALLE DE PRODUCTOS ==='];
        $data[] = ['Producto', 'Cantidad', 'Precio Unitario', 'Subtotal'];

        foreach ($this->orden->detalles as $detalle) {
            $data[] = [
                $detalle->producto->nombre,
                $detalle->cantidad,
                '$' . number_format($detalle->producto->preciocompra, 2),
                '$' . number_format($detalle->subtotal, 2),
            ];
        }

        $data[] = [];
        $data[] = ['TOTAL GENERAL', '', '', '$' . number_format($this->orden->total, 2)];

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            9 => ['font' => ['bold' => true]],
        ];
    }
}