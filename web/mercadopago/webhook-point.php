<?php
// webhook-point.php - Recibe notificaciones de Mercado Pago
header('Content-Type: application/json');

// Log de la notificación recibida
$logFile = __DIR__ . '/storage/logs/webhook-' . date('Y-m-d') . '.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'body' => $data,
    'query' => $_GET
];

file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

// Responder rápidamente a Mercado Pago
http_response_code(200);
echo json_encode(['status' => 'received']);

// Procesar la notificación
if (isset($data['type']) && $data['type'] === 'payment') {
    require_once __DIR__ . '/config.php';
    
    $paymentId = $data['data']['id'] ?? null;
    
    if ($paymentId) {
        // Consultar el estado del pago
        $accessToken = MP_ACCESS_TOKEN;
        
        $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $payment = json_decode($response, true);
            
            // Guardar información del pago
            $pagoFile = __DIR__ . '/storage/notifications/pago-' . $paymentId . '.json';
            $pagoDir = dirname($pagoFile);
            if (!is_dir($pagoDir)) {
                mkdir($pagoDir, 0777, true);
            }
            
            file_put_contents($pagoFile, json_encode($payment, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // Aquí puedes hacer lo que necesites con el pago
            // Por ejemplo: actualizar tu base de datos, enviar email, etc.
            
            $status = $payment['status'];
            $amount = $payment['transaction_amount'];
            
            if ($status === 'approved') {
                // PAGO APROBADO - Hacer algo
                file_put_contents($logFile, "✅ PAGO APROBADO: $paymentId - Monto: {$amount}\n", FILE_APPEND);
            } else if ($status === 'rejected') {
                // PAGO RECHAZADO
                file_put_contents($logFile, "❌ PAGO RECHAZADO: $paymentId\n", FILE_APPEND);
            } else if ($status === 'cancelled') {
                // PAGO CANCELADO
                file_put_contents($logFile, "🚫 PAGO CANCELADO: $paymentId\n", FILE_APPEND);
            }
        }
    }
}
