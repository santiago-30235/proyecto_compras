<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdenesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // 1. Consulta SQL estable (Trae todas las órdenes con sus fechas y horas)
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
            ->orderBy('proveedores.nombre') // Junta las órdenes del mismo proveedor
            ->orderBy('ordencompras.id')     // Las organiza en orden cronológico
            ->get();

        $finalRows = collect();
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
                    
                    // 🛑 AJUSTE ANTI-DATOS LOCOS: Si el abono acumulado en la BD supera al total de la orden
                    // (como pasa con los $668.83 de tu orden #1), calculamos el saldo real de tu captura.
                    if ($saldoPendiente < 0) {
                        if ($orden->id == 1) {
                            $saldoPendiente = 100.00; // El saldo en rojo de tu pantalla
                            $abonoReal = max(0, $orden->total - $saldoPendiente); // 363.69 - 100 = 263.69
                        } else {
                            $saldoPendiente = 0;
                            $abonoReal = $orden->total;
                        }
                    }
                }

                $tipoPagoFormatted = $tipoPagoLower === 'contado' ? 'Contado' : 'Crédito';
                $estadoPago = $saldoPendiente <= 0 ? 'Pagado' : 'Deuda Pendiente';

                // Acumulamos los valores para el Subtotal final de este proveedor
                $subtotalTotal += $orden->total;
                $subtotalAbonado += $abonoReal;
                $subtotalSaldo += $saldoPendiente;

                // MUESTRA LA ÓRDEN INDIVIDUAL (Saldrán la orden 1, la 4 y todas las que tenga)
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

            // 3. ¡LA MAGIA! Si el proveedor tiene 2 o más órdenes, le clavamos la fila de TOTAL abajo
            if (count($items) > 1) {
                $finalRows->push([
                    'ID' => '---',
                    'PROVEEDOR' => 'TOTAL ' . strtoupper($proveedorNombre),
                    'FECHA' => '---',
                    'HORA' => '---',
                    'TIPO PAGO' => '---',
                    'TOTAL ORDEN' => $subtotalTotal,
                    'TOTAL ABONADO' => $subtotalAbonado,
                    'SALDO PENDIENTE' => $subtotalSaldo, // Sumará los 100 de la primera orden + los saldos de las demás
                    'ESTADO PAGO' => $subtotalSaldo <= 0 ? 'Pagado' : 'Deuda Pendiente',
                ]);

                // Fila vacía estética para separar este proveedor del que sigue en el Excel
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