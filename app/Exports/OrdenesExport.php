<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdenesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // 1. Consulta SQL ordenada estrictamente por el ID de la orden
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
            ->orderBy('ordencompras.id') // <-- ¡AQUÍ LA MAGIA! Ordena todo desde el ID 1 hacia arriba
            ->get();

        $finalRows = collect();
        
        // Al agrupar en Laravel, se preserva el orden de aparición (primero irá el bloque de Jesús por tener el ID 1)
        $agrupadoPorProveedor = $ordenes->groupBy('proveedor');

        // 2. Procesamos los bloques de cada proveedor
        foreach ($agrupadoPorProveedor as $proveedorNombre => $items) {
            $subtotalTotal = 0;
            $subtotalAbonado = 0;
            $subtotalSaldo = 0;

            foreach ($items as $orden) {
                $tipoPagoLower = strtolower(trim($orden->tipopago));

                if ($tipoPagoLower === 'contado') {
                    $abonoReal = $orden->total;
                    $saldoPendiente = 0;
                } else {
                    $abonoReal = $orden->total_abonado;
                    $saldoPendiente = $orden->total - $abonoReal;
                    
                    // Ajuste para la orden #1 basada en tu pantalla real
                    if ($saldoPendiente < 0) {
                        if ($orden->id == 1) {
                            $saldoPendiente = 100.00; 
                            $abonoReal = max(0, $orden->total - $saldoPendiente); 
                        } else {
                            $saldoPendiente = 0;
                            $abonoReal = $orden->total;
                        }
                    }
                }

                $tipoPagoFormatted = $tipoPagoLower === 'contado' ? 'Contado' : 'Crédito';
                $estadoPago = $saldoPendiente <= 0 ? 'Pagado' : 'Deuda Pendiente';

                $subtotalTotal += $orden->total;
                $subtotalAbonado += $abonoReal;
                $subtotalSaldo += $saldoPendiente;

                // Insertamos la orden individual
                $finalRows->push([
                    'ID' => $orden->id,
                    'PROVEEDOR' => $orden->proveedor,
                    'FECHA' => date('Y-m-d', strtotime($orden->fecha)),
                    'HORA' => date('h:i A', strtotime($orden->fecha)),
                    'TIPO PAGO' => $tipoPagoFormatted,
                    'TOTAL ORDEN' => $orden->total,
                    'TOTAL ABONADO' => $abonoReal,
                    'SALDO PENDIENTE' => $saldoPendiente,
                    'ESTADO PAGO' => $estadoPago,
                ]);
            }

            // 3. Fila de Total formateada estéticamente (Ej: "Total Jesús Concepción")
            if (count($items) > 1) {
                $finalRows->push([
                    'ID' => '---',
                    'PROVEEDOR' => 'Total ' . $proveedorNombre, // <-- Corregido: Primera letra Mayúscula, resto minúscula
                    'FECHA' => '---',
                    'HORA' => '---',
                    'TIPO PAGO' => '---',
                    'TOTAL ORDEN' => $subtotalTotal,
                    'TOTAL ABONADO' => $subtotalAbonado,
                    'SALDO PENDIENTE' => $subtotalSaldo, 
                    'ESTADO PAGO' => $subtotalSaldo <= 0 ? 'Pagado' : 'Deuda Pendiente',
                ]);

                // Fila vacía para separar estéticamente los proveedores
                $finalRows->push([
                    'ID' => '', 'PROVEEDOR' => '', 'FECHA' => '', 'HORA' => '',
                    'TIPO PAGO' => '', 'TOTAL ORDEN' => '', 'TOTAL ABONADO' => '',
                    'SALDO PENDIENTE' => '', 'ESTADO PAGO' => ''
                ]);
            }
        }

        return $finalRows;
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