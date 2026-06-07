<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra #{{ $orden->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            padding: 24px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
        }

        .header-title {
            font-size: 24px;
            letter-spacing: 0.04em;
            margin: 0 0 8px;
        }

        .header-subtitle {
            color: #4b5563;
            font-size: 12px;
            margin: 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .info-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            background: #f8fafc;
        }

        .info-box p {
            margin: 8px 0;
            line-height: 1.5;
            font-size: 12px;
        }

        .section-label {
            display: inline-block;
            margin-bottom: 12px;
            color: #111827;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .table th,
        .table td {
            border: 1px solid #e2e8f0;
            padding: 12px 14px;
            vertical-align: top;
            font-size: 12px;
        }

        .table th {
            background: #f1f5f9;
            font-weight: 700;
            color: #111827;
            text-align: left;
        }

        .table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            align-items: end;
        }

        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            background: #f8fafc;
        }

        .summary-box p {
            margin: 8px 0;
            font-size: 12px;
            line-height: 1.5;
        }

        .summary-total {
            font-size: 14px;
            font-weight: 700;
            text-align: right;
            margin-top: 12px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card">
            <div class="header">
                <div>
                    <h1 class="header-title">Orden de Compra</h1>
                    <p class="header-subtitle">Documento #{{ $orden->id }} · Fecha {{ date('d/m/Y', strtotime($orden->fecha)) }}</p>
                </div>
                <div class="text-right">
                    <p class="header-subtitle">Generado: {{ $fecha }}</p>
                    <p class="header-subtitle">Por: {{ auth()->user()->name ?? 'Sistema' }}</p>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-box">
                    <span class="section-label">Datos de la orden</span>
                    <p><strong>Tipo de Pago:</strong> {{ ucfirst($orden->tipopago) }}</p>
                    <p><strong>Total Orden:</strong> ${{ number_format($orden->total, 2) }}</p>
                    <p><strong>Saldo Pendiente:</strong> ${{ $orden->tipopago === 'credito' ? number_format($orden->saldopendiente, 2) : '0.00' }}</p>
                    <p><strong>Estado de Pago:</strong>
                        {{ $orden->tipopago === 'contado' ? 'Pagado totalmente' : ($orden->saldopendiente > 0 ? 'Con deuda pendiente' : 'Crédito liberado') }}
                    </p>
                </div>

                <div class="info-box">
                    <span class="section-label">Datos del proveedor</span>
                    <p><strong>Proveedor:</strong> {{ ucfirst($orden->proveedor->nombre ?? 'N/A') }}</p>
                    <p><strong>Documento:</strong> {{ $orden->proveedor->documento ?? 'N/A' }}</p>
                    <p><strong>Dirección:</strong> {{ $orden->proveedor->direccion ?? 'N/A' }}</p>
                    <p><strong>Teléfono:</strong> {{ $orden->proveedor->telefono ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $orden->proveedor->email ?? 'N/A' }}</p>
                </div>
            </div>

            <div>
                <span class="section-label">Detalle de productos</span>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orden->detalles as $detalle)
                        <tr>
                            <td>
                                <strong>{{ $detalle->producto->nombre }}</strong>
                                @if(!empty($detalle->producto->descripcion))
                                    <div>{{ $detalle->producto->descripcion }}</div>
                                @endif
                            </td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>$ {{ number_format($detalle->producto->preciocompra, 2) }}</td>
                            <td>$ {{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total orden</strong></td>
                            <td>$ {{ number_format($orden->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="summary">
                <div></div>
                <div class="summary-box">
                    <p><strong>Total abonado:</strong>
                        ${{ $orden->tipopago === 'credito' ? number_format($orden->total - $orden->saldopendiente, 2) : number_format($orden->total, 2) }}</p>
                    <p><strong>Saldo pendiente:</strong> ${{ $orden->tipopago === 'credito' ? number_format($orden->saldopendiente, 2) : '0.00' }}</p>
                    <p class="summary-total">Total a pagar: ${{ number_format($orden->total, 2) }}</p>
                </div>
            </div>

            <div class="footer">
                <p>Documento generado automáticamente para la gestión de compras.</p>
            </div>
        </div>
    </div>
</body>
</html>