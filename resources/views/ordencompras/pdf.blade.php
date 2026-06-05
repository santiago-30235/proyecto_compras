<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra #{{ $orden->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .info {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #e0e0e0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORDEN DE COMPRA</h1>
        <p>N° {{ $orden->id }}</p>
    </div>

    <div class="info">
        <p><strong>Proveedor:</strong> {{ $orden->proveedor->nombre ?? 'N/A' }}</p>
        <p><strong>Documento:</strong> {{ $orden->proveedor->documento ?? 'N/A' }}</p>
        <p><strong>Fecha:</strong> {{ date('d/m/Y', strtotime($orden->fecha)) }}</p>
        <p><strong>Tipo de Pago:</strong> {{ ucfirst($orden->tipopago) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->nombre }}<br><small>{{ $detalle->producto->descripcion ?? '' }}</small></td>
                <td>{{ $detalle->cantidad }} uds<br><small>Compra</small></td>
                <td>${{ number_format($detalle->producto->preciocompra, 2) }}<br><small>Unitario</small></td>
                <td>${{ number_format($detalle->subtotal, 2) }}<br><small>Total</small></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="text-right">
                <th colspan="3" class="text-right">TOTAL:</th>
                <th>${{ number_format($orden->total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="total">
        <p>Fecha de emisión: {{ $fecha }}</p>
        <p>Generado por: {{ auth()->user()->name ?? 'Sistema' }}</p>
    </div>

    <div class="footer">
        <p>Este documento es una representación de la orden de compra.</p>
    </div>
</body>
</html>