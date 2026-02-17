<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Pedido Final</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header .pedido-num {
            font-size: 18px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .info-section {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 140px;
        }
        .info-value {
            color: #333;
        }
        .products-section {
            margin: 30px 0;
        }
        .products-section h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table thead {
            background: #667eea;
            color: white;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        table tbody tr:hover {
            background: #f8f9fa;
        }
        .totals {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 16px;
        }
        .total-row.final {
            border-top: 2px solid #667eea;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }
        .footer {
            background: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        .emoji {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✅ NUEVO PEDIDO RECIBIDO</h1>
            <div class="pedido-num">Pedido #{{ $pedido->id }}</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Información General -->
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">📍 Local:</div>
                    <div class="info-value"><strong>{{ $local->Name ?? $local->name ?? $local->nombre ?? 'N/A' }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">📅 Fecha:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">👤 Solicitante:</div>
                    <div class="info-value">{{ $pedido->contact_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">📱 WhatsApp:</div>
                    <div class="info-value">{{ $pedido->contact_phone }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">💳 Método Pago:</div>
                    <div class="info-value">
                        <span class="badge badge-info">{{ $pedido->paymode }}</span>
                    </div>
                </div>
                @if($pedido->comment)
                <div class="info-row">
                    <div class="info-label">💬 Comentario:</div>
                    <div class="info-value">{{ $pedido->comment }}</div>
                </div>
                @endif
            </div>

            <!-- Productos -->
            <div class="products-section">
                <h2>📦 PRODUCTOS SOLICITADOS</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">Cant.</th>
                            <th style="width: 50%;">Producto</th>
                            <th style="width: 20%;">Precio Unit.</th>
                            <th style="width: 20%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $producto)
                        <tr>
                            <td style="text-align: center;">
                                <strong>{{ $producto['quantity'] ?? $producto['cantidad'] ?? 0 }}</strong>
                                {{ $producto['unidad_medida'] ?? 'un' }}
                            </td>
                            <td>{{ $producto['name'] ?? $producto['producto'] ?? 'Producto' }}</td>
                            <td>${{ number_format($producto['price'] ?? $producto['precio_por_unidad'] ?? 0, 0, ',', '.') }}</td>
                            <td><strong>${{ number_format(($producto['quantity'] ?? $producto['cantidad'] ?? 0) * ($producto['price'] ?? $producto['precio_por_unidad'] ?? 0), 0, ',', '.') }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totales -->
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal ({{ count($productos) }} productos):</span>
                    <span>${{ number_format($pedido->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span>IVA (19%):</span>
                    <span>${{ number_format($pedido->iva, 0, ',', '.') }}</span>
                </div>
                <div class="total-row final">
                    <span>TOTAL A PAGAR:</span>
                    <span>${{ number_format($pedido->price, 0, ',', '.') }} CLP</span>
                </div>
            </div>

            @if($pedido->emergency > 0)
            <div class="info-section" style="border-left-color: #ff9800;">
                <div class="info-row">
                    <div class="info-label">⚠️ EMERGENCIA:</div>
                    <div class="info-value">
                        <span class="badge" style="background: #ff9800; color: white;">
                            Cargo adicional: ${{ number_format($pedido->emergency, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Sistema Fagotto ERP</strong></p>
            <p>Pedidos Centralizados - Notificación Automática</p>
            <p style="opacity: 0.8; font-size: 12px; margin-top: 10px;">
                Este correo fue generado automáticamente. Por favor no responder.
            </p>
        </div>
    </div>
</body>
</html>
