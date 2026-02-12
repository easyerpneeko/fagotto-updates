<?php
/**
 * API para consultar estado de pago en MercadoPago Point
 * 
 * Este archivo recibe peticiones del backend Laravel para consultar
 * el estado de un pago previamente enviado al terminal
 */

header('Content-Type: application/json');

// Cargar configuración
require_once __DIR__ . '/config.php';

// Verificar método de petición
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Método no permitido',
        'message' => 'Solo se permiten peticiones GET'
    ]);
    exit;
}

// Obtener order_id de la query string
$orderId = $_GET['order_id'] ?? null;

if (empty($orderId)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Datos inválidos',
        'message' => 'El parámetro "order_id" es requerido'
    ]);
    exit;
}

// Log de la petición
$logFile = __DIR__ . '/storage/logs/api-status-' . date('Y-m-d') . '.log';
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'consultar_estado',
    'order_id' => $orderId
];
file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

// Obtener credenciales
$accessToken = MP_ACCESS_TOKEN;

if (empty($accessToken)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuración incompleta',
        'message' => 'Falta el Access Token de MercadoPago'
    ]);
    exit;
}

// Consultar estado del pago
try {
    $url = "https://api.mercadopago.com/v1/orders/{$orderId}";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Log de la respuesta
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => 'response_status',
        'order_id' => $orderId,
        'http_code' => $httpCode,
        'response' => $response,
        'curl_error' => $curlError
    ];
    file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);
    
    if ($curlError) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Error de conexión',
            'message' => 'Error al conectar con MercadoPago: ' . $curlError
        ]);
        exit;
    }
    
    if ($httpCode !== 200) {
        http_response_code($httpCode);
        echo json_encode([
            'error' => 'Error al consultar',
            'message' => 'Error al consultar el estado del pago',
            'http_code' => $httpCode
        ]);
        exit;
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData || !isset($responseData['status'])) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Respuesta inválida',
            'message' => 'La respuesta de MercadoPago no tiene el formato esperado'
        ]);
        exit;
    }
    
    // Determinar el estado del pago
    $status = $responseData['status'];
    $paymentData = null;
    
    // Si el pago tiene transactions, extraer la información
    if (isset($responseData['transactions']) && isset($responseData['transactions']['payments'])) {
        $payments = $responseData['transactions']['payments'];
        if (!empty($payments)) {
            $payment = $payments[0];
            $paymentData = [
                'payment_id' => $payment['id'] ?? null,
                'status' => $payment['status'] ?? null,
                'status_detail' => $payment['status_detail'] ?? null,
                'amount' => $payment['amount'] ?? null,
                'payment_method' => $payment['payment_method_id'] ?? null,
                'card_last_digits' => $payment['card']['last_four_digits'] ?? null,
                'approved_at' => $payment['date_approved'] ?? null
            ];
        }
    }
    
    // Responder con el estado
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'status' => $status,
        'payment_data' => $paymentData,
        'full_response' => $responseData
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Excepción',
        'message' => 'Error inespePerado: ' . $e->getMessage()
    ]);
}
