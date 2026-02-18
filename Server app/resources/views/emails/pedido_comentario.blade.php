<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Comentario en Pedido</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f5f5f5; padding: 20px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">
                                💬 Nuevo Comentario en Pedido
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #e3f2fd; font-size: 16px;">
                                Se ha registrado un comentario sobre un pedido
                            </p>
                        </td>
                    </tr>

                    <!-- Pedido Info -->
                    <tr>
                        <td style="padding: 30px;">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; text-align: center; margin-bottom: 20px;">
                                        <h2 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700;">
                                            Pedido #{{ $pedido->id }}
                                        </h2>
                                    </td>
                                </tr>
                            </table>

                            <!-- Local y Fecha -->
                            <table cellpadding="8" cellspacing="0" border="0" width="100%" style="margin-top: 20px;">
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                                        <strong style="color: #667eea;">🏢 Local:</strong>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                                        {{ $local->Name ?? $local->name ?? $local->nombre ?? 'Local' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                                        <strong style="color: #667eea;">📅 Fecha del Pedido:</strong>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                                        {{ date('d/m/Y H:i', strtotime($pedido->created_at)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                                        <strong style="color: #667eea;">👤 Solicitante:</strong>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                                        {{ $pedido->contact_name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                                        <strong style="color: #667eea;">💰 Total:</strong>
                                    </td>
                                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; text-align: right; font-weight: 700; font-size: 18px; color: #667eea;">
                                        ${{ number_format($pedido->price, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px;">
                                        <strong style="color: #667eea;">📊 Estado:</strong>
                                    </td>
                                    <td style="padding: 12px; text-align: right;">
                                        <span style="background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 20px; font-weight: 600; text-transform: uppercase; font-size: 12px;">
                                            {{ strtoupper($pedido->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Comentario -->
                            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #17a2b8; border-radius: 8px;">
                                <h3 style="margin: 0 0 15px 0; color: #17a2b8; font-size: 18px;">
                                    💬 Comentario:
                                </h3>
                                <p style="margin: 0; color: #333; font-size: 16px; line-height: 1.6; white-space: pre-wrap;">{{ $comentario }}</p>
                                <p style="margin: 15px 0 0 0; color: #6c757d; font-size: 14px; font-style: italic;">
                                    ⏰ Registrado: {{ date('d/m/Y H:i:s') }}
                                </p>
                            </div>

                            <!-- Productos del Pedido -->
                            <div style="margin-top: 30px;">
                                <h3 style="margin: 0 0 15px 0; color: #333; font-size: 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea;">
                                    📦 Productos del Pedido
                                </h3>
                                <table cellpadding="12" cellspacing="0" border="0" width="100%" style="margin-top: 15px;">
                                    <thead>
                                        <tr style="background: #667eea; color: #ffffff;">
                                            <th style="padding: 12px; text-align: center; border-radius: 8px 0 0 0;">Cant.</th>
                                            <th style="padding: 12px; text-align: left;">Producto</th>
                                            <th style="padding: 12px; text-align: right;">Precio Unit.</th>
                                            <th style="padding: 12px; text-align: right; border-radius: 0 8px 0 0;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($productos as $producto)
                                        <tr style="border-bottom: 1px solid #e0e0e0;">
                                            <td style="padding: 12px; text-align: center; font-weight: 700; color: #667eea;">
                                                {{ $producto['quantity'] ?? $producto['cantidad'] ?? 0 }}
                                                <small style="color: #6c757d;">{{ $producto['unidad_medida'] ?? 'un' }}</small>
                                            </td>
                                            <td style="padding: 12px; color: #333;">
                                                {{ $producto['name'] ?? $producto['producto'] ?? 'Producto' }}
                                            </td>
                                            <td style="padding: 12px; text-align: right; color: #666;">
                                                ${{ number_format($producto['price'] ?? $producto['precio_por_unidad'] ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td style="padding: 12px; text-align: right; font-weight: 700; color: #667eea;">
                                                ${{ number_format(($producto['quantity'] ?? $producto['cantidad'] ?? 0) * ($producto['price'] ?? $producto['precio_por_unidad'] ?? 0), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                                <!-- Totales -->
                                <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                                    <table cellpadding="8" cellspacing="0" border="0" width="100%">
                                        <tr>
                                            <td style="padding: 8px; color: #666;">Subtotal ({{ count($productos) }} productos):</td>
                                            <td style="padding: 8px; text-align: right; font-weight: 600;">${{ number_format($pedido->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; color: #666;">IVA (19%):</td>
                                            <td style="padding: 8px; text-align: right; font-weight: 600;">${{ number_format($pedido->iva, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr style="border-top: 2px solid #667eea;">
                                            <td style="padding: 15px 8px 8px 8px; color: #667eea; font-size: 18px; font-weight: 700;">TOTAL A PAGAR:</td>
                                            <td style="padding: 15px 8px 8px 8px; text-align: right; color: #667eea; font-size: 20px; font-weight: 700;">${{ number_format($pedido->price, 0, ',', '.') }} CLP</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Información Original del Pedido -->
                            @if($pedido->comment)
                            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                                <h4 style="margin: 0 0 10px 0; color: #856404; font-size: 14px;">
                                    📝 Comentario Original del Pedido:
                                </h4>
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">{{ $pedido->comment }}</p>
                            </div>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #2c3e50; padding: 20px; text-align: center;">
                            <p style="margin: 0; color: #95a5a6; font-size: 14px;">
                                🔔 Este es un mensaje automático del Sistema Fagotto ERP
                            </p>
                            <p style="margin: 10px 0 0 0; color: #7f8c8d; font-size: 12px;">
                                Para revisar el pedido completo, ingresa al sistema
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
