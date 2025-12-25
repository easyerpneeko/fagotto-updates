<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Pago - Mercado Pago Point</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input[type="number"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            transition: all 0.3s;
        }
        input[type="number"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn:active {
            transform: translateY(0);
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .info-box strong {
            color: #333;
        }
        .terminal-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .terminal-info strong {
            color: #1976d2;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ðŸ’³ Enviar Pago</h1>
        <p class="subtitle">Mercado Pago Point Smart</p>

        <div class="terminal-info">
            <strong>ðŸ–¥ï¸ Terminal:</strong> NEWLAND_N950__N950NCC302980808<br>
            <strong>ðŸ“ Modo:</strong> PDV
        </div>

        <?php
// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if (<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Pago - Mercado Pago Point</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input[type="number"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            transition: all 0.3s;
        }
        input[type="number"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn:active {
            transform: translateY(0);
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .info-box strong {
            color: #333;
        }
        .terminal-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .terminal-info strong {
            color: #1976d2;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ðŸ’³ Enviar Pago</h1>
        <p class="subtitle">Mercado Pago Point Smart</p>

        <div class="terminal-info">
            <strong>ðŸ–¥ï¸ Terminal:</strong> NEWLAND_N950__N950NCC302980808<br>
            <strong>ðŸ“ Modo:</strong> PDV
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config.php';
            
            $monto = intval($_POST['monto']);
            
            if ($monto < 100) {
                echo '<div class="alert alert-error">âŒ El monto mÃ­nimo es $100 CLP</div>';
            } else {
                $accessToken = MP_ACCESS_TOKEN;
                $deviceId = MP_DEVICE_ID;
                $externalReference = "REF-" . time();
                
                $payload = [
                    "type" => "point",
                    "external_reference" => $externalReference,
                    "description" => "Venta web - $" . number_format($monto, 0, ',', '.'),
                    "transactions" => [
                        "payments" => [
                            [
                                "amount" => (string)$monto
                            ]
                        ]
                    ],
                    "config" => [
                        "point" => [
                            "terminal_id" => $deviceId,
                            "print_on_terminal" => "seller_ticket"
                        ],
                        "payment_method" => [
                            "default_type" => "credit_card"
                        ]
                    ],
                    "taxes" => [
                        [
                            "payer_condition" => "payment_taxable_iva"
                        ]
                    ]
                ];
                
                $idempotencyKey = uniqid('mppoint_', true);
                
                $ch = curl_init("https://api.mercadopago.com/v1/orders");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                    'X-Idempotency-Key: ' . $idempotencyKey
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $responseData = json_decode($response, true);
                
                if ($httpCode === 201) {
                    echo '<div class="alert alert-success">';
                    echo 'âœ… <strong>Â¡Pago enviado exitosamente!</strong><br><br>';
                    echo '<strong>Order ID:</strong> ' . $responseData['id'] . '<br>';
                    echo '<strong>Monto:</strong> $' . number_format($monto, 0, ',', '.') . ' CLP<br>';
                    echo '<strong>Referencia:</strong> ' . $externalReference . '<br>';
                    echo '<strong>Estado:</strong> ' . $responseData['status'] . '<br><br>';
                    echo 'ðŸ–¨ï¸ El pago ya estÃ¡ en el Point Smart, esperando tarjeta...';
                    echo '</div>';
                } else if ($httpCode === 409) {
                    echo '<div class="alert alert-warning">';
                    echo 'âš ï¸ <strong>Ya hay un pago pendiente en el terminal</strong><br><br>';
                    echo 'CancelÃ¡ o completÃ¡ el pago actual antes de enviar uno nuevo.';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-error">';
                    echo 'âŒ <strong>Error al crear el pago</strong><br><br>';
                    echo '<strong>HTTP Code:</strong> ' . $httpCode . '<br>';
                    echo '<pre>' . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                    echo '</div>';
                }
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="monto">ðŸ’° Monto en CLP</label>
                <input 
                    type="number" 
                    id="monto" 
                    name="monto" 
                    placeholder="1000" 
                    min="100" 
                    step="1" 
                    required
                    value="<?php echo isset($_POST['monto']) ? $_POST['monto'] : '1000'; ?>"
                >
            </div>

            <button type="submit" class="btn">
                ðŸ“¤ Enviar Pago al Terminal
            </button>
        </form>

        <div class="info-box">
            <strong>â„¹ï¸ InformaciÃ³n:</strong><br>
            â€¢ El monto mÃ­nimo es $100 CLP<br>
            â€¢ No uses decimales (Ej: 1000, no 1000.00)<br>
            â€¢ El pago aparecerÃ¡ inmediatamente en el Point Smart<br>
            â€¢ Solo se puede tener 1 pago pendiente a la vez
        </div>
    </div>
</body>
</html>
SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config.php';
            
            $monto = intval($_POST['monto']);
            
            if ($monto < 100) {
                echo '<div class="alert alert-error">âŒ El monto mÃ­nimo es $100 CLP</div>';
            } else {
                $accessToken = MP_ACCESS_TOKEN;
                $deviceId = MP_DEVICE_ID;
                $externalReference = "REF-" . time();
                
                $payload = [
                    "type" => "point",
                    "external_reference" => $externalReference,
                    "description" => "Venta web - $" . number_format($monto, 0, ',', '.'),
                    "transactions" => [
                        "payments" => [
                            [
                                "amount" => (string)$monto
                            ]
                        ]
                    ],
                    "config" => [
                        "point" => [
                            "terminal_id" => $deviceId,
                            "print_on_terminal" => "seller_ticket"
                        ],
                        "payment_method" => [
                            "default_type" => "credit_card"
                        ]
                    ],
                    "taxes" => [
                        [
                            "payer_condition" => "payment_taxable_iva"
                        ]
                    ]
                ];
                
                $idempotencyKey = uniqid('mppoint_', true);
                
                $ch = curl_init("https://api.mercadopago.com/v1/orders");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                    'X-Idempotency-Key: ' . $idempotencyKey
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $responseData = json_decode($response, true);
                
                if ($httpCode === 201) {
                    echo '<div class="alert alert-success">';
                    echo 'âœ… <strong>Â¡Pago enviado exitosamente!</strong><br><br>';
                    echo '<strong>Order ID:</strong> ' . $responseData['id'] . '<br>';
                    echo '<strong>Monto:</strong> $' . number_format($monto, 0, ',', '.') . ' CLP<br>';
                    echo '<strong>Referencia:</strong> ' . $externalReference . '<br>';
                    echo '<strong>Estado:</strong> ' . $responseData['status'] . '<br><br>';
                    echo 'ðŸ–¨ï¸ El pago ya estÃ¡ en el Point Smart, esperando tarjeta...';
                    echo '</div>';
                } else if ($httpCode === 409) {
                    echo '<div class="alert alert-warning">';
                    echo 'âš ï¸ <strong>Ya hay un pago pendiente en el terminal</strong><br><br>';
                    echo 'CancelÃ¡ o completÃ¡ el pago actual antes de enviar uno nuevo.';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-error">';
                    echo 'âŒ <strong>Error al crear el pago</strong><br><br>';
                    echo '<strong>HTTP Code:</strong> ' . $httpCode . '<br>';
                    echo '<pre>' . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                    echo '</div>';
                }
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="monto">ðŸ’° Monto en CLP</label>
                <input 
                    type="number" 
                    id="monto" 
                    name="monto" 
                    placeholder="1000" 
                    min="100" 
                    step="1" 
                    required
                    value="<?php
// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if (<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Pago - Mercado Pago Point</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input[type="number"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            transition: all 0.3s;
        }
        input[type="number"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn:active {
            transform: translateY(0);
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .info-box strong {
            color: #333;
        }
        .terminal-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .terminal-info strong {
            color: #1976d2;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ðŸ’³ Enviar Pago</h1>
        <p class="subtitle">Mercado Pago Point Smart</p>

        <div class="terminal-info">
            <strong>ðŸ–¥ï¸ Terminal:</strong> NEWLAND_N950__N950NCC302980808<br>
            <strong>ðŸ“ Modo:</strong> PDV
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/config.php';
            
            $monto = intval($_POST['monto']);
            
            if ($monto < 100) {
                echo '<div class="alert alert-error">âŒ El monto mÃ­nimo es $100 CLP</div>';
            } else {
                $accessToken = MP_ACCESS_TOKEN;
                $deviceId = MP_DEVICE_ID;
                $externalReference = "REF-" . time();
                
                $payload = [
                    "type" => "point",
                    "external_reference" => $externalReference,
                    "description" => "Venta web - $" . number_format($monto, 0, ',', '.'),
                    "transactions" => [
                        "payments" => [
                            [
                                "amount" => (string)$monto
                            ]
                        ]
                    ],
                    "config" => [
                        "point" => [
                            "terminal_id" => $deviceId,
                            "print_on_terminal" => "seller_ticket"
                        ],
                        "payment_method" => [
                            "default_type" => "credit_card"
                        ]
                    ],
                    "taxes" => [
                        [
                            "payer_condition" => "payment_taxable_iva"
                        ]
                    ]
                ];
                
                $idempotencyKey = uniqid('mppoint_', true);
                
                $ch = curl_init("https://api.mercadopago.com/v1/orders");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                    'X-Idempotency-Key: ' . $idempotencyKey
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $responseData = json_decode($response, true);
                
                if ($httpCode === 201) {
                    echo '<div class="alert alert-success">';
                    echo 'âœ… <strong>Â¡Pago enviado exitosamente!</strong><br><br>';
                    echo '<strong>Order ID:</strong> ' . $responseData['id'] . '<br>';
                    echo '<strong>Monto:</strong> $' . number_format($monto, 0, ',', '.') . ' CLP<br>';
                    echo '<strong>Referencia:</strong> ' . $externalReference . '<br>';
                    echo '<strong>Estado:</strong> ' . $responseData['status'] . '<br><br>';
                    echo 'ðŸ–¨ï¸ El pago ya estÃ¡ en el Point Smart, esperando tarjeta...';
                    echo '</div>';
                } else if ($httpCode === 409) {
                    echo '<div class="alert alert-warning">';
                    echo 'âš ï¸ <strong>Ya hay un pago pendiente en el terminal</strong><br><br>';
                    echo 'CancelÃ¡ o completÃ¡ el pago actual antes de enviar uno nuevo.';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-error">';
                    echo 'âŒ <strong>Error al crear el pago</strong><br><br>';
                    echo '<strong>HTTP Code:</strong> ' . $httpCode . '<br>';
                    echo '<pre>' . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                    echo '</div>';
                }
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="monto">ðŸ’° Monto en CLP</label>
                <input 
                    type="number" 
                    id="monto" 
                    name="monto" 
                    placeholder="1000" 
                    min="100" 
                    step="1" 
                    required
                    value="<?php echo isset($_POST['monto']) ? $_POST['monto'] : '1000'; ?>"
                >
            </div>

            <button type="submit" class="btn">
                ðŸ“¤ Enviar Pago al Terminal
            </button>
        </form>

        <div class="info-box">
            <strong>â„¹ï¸ InformaciÃ³n:</strong><br>
            â€¢ El monto mÃ­nimo es $100 CLP<br>
            â€¢ No uses decimales (Ej: 1000, no 1000.00)<br>
            â€¢ El pago aparecerÃ¡ inmediatamente en el Point Smart<br>
            â€¢ Solo se puede tener 1 pago pendiente a la vez
        </div>
    </div>
</body>
</html>
SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
 echo isset($_POST['monto']) ? $_POST['monto'] : '1000'; ?>"
                >
            </div>

            <button type="submit" class="btn">
                ðŸ“¤ Enviar Pago al Terminal
            </button>
        </form>

        <div class="info-box">
            <strong>â„¹ï¸ InformaciÃ³n:</strong><br>
            â€¢ El monto mÃ­nimo es $100 CLP<br>
            â€¢ No uses decimales (Ej: 1000, no 1000.00)<br>
            â€¢ El pago aparecerÃ¡ inmediatamente en el Point Smart<br>
            â€¢ Solo se puede tener 1 pago pendiente a la vez
        </div>
    </div>
</body>
</html>

