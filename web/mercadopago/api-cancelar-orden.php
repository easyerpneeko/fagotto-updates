<?php
/**
 * API para cancelar pagos pendientes en MercadoPago Point
 * 
 * Este archivo permite cancelar automáticamente órdenes en estado "pending"
 * Subir a: fagottoerp.cl/mercadopago/api-cancelar-orden.php
 */

header('Content-Type: application/json');

// Cargar configuración
require_once __DIR__ . '/config.php';

// Verificar método de petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Método no permitido',
        'message' => 'Solo se permiten peticiones POST'
    ]);
    exit;
}

// Obtener datos del body
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

// Log de la petición
$logFile = __DIR__ . '/storage/logs/api-cancelar-' . date('Y-m-d') . '.log';
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'cancel_order_request',
    'data' => $data
];
file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Validar app_id
$appId = $data['app_id'] ?? null;
if (!$appId || !in_array($appId, [58, 116])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'app_id inválido',
        'message' => 'El app_id debe ser 58 (Agustinas) o 116 (Las Condes)'
    ]);
    exit;
}

// Obtener credenciales según app_id
if ($appId == 58) {
    $accessToken = getenv('MP_ACCESS_TOKEN_58');
    $deviceId = getenv('MP_DEVICE_ID_58');
} elseif ($appId == 116) {
    $accessToken = getenv('MP_ACCESS_TOKEN_116');
    $deviceId = getenv('MP_DEVICE_ID_116');
}

if (empty($accessToken) || empty($deviceId)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuración incompleta',
        'message' => 'Faltan credenciales de MercadoPago para app_id: ' . $appId
    ]);
    exit;
}

try {
    // Consultar órdenes pendientes en el terminal
    $searchUrl = "https://api.mercadopago.com/v1/orders/search";
    
    $ch = curl_init($searchUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception('Error al consultar órdenes: ' . $curlError);
    }
    
    $orders = json_decode($response, true);
    
    // Log de las órdenes encontradas
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => 'search_orders_response',
        'http_code' => $httpCode,
        'orders_found' => $orders
    ];
    file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    
    if ($httpCode !== 200) {
        throw new Exception('Error al buscar órdenes pendientes (HTTP ' . $httpCode . ')');
    }
    
    // Buscar órdenes pendientes en este device_id
    $pendingOrders = [];
    if (isset($orders['results']) && is_array($orders['results'])) {
        foreach ($orders['results'] as $order) {
            // Verificar si la orden está pendiente y es de este terminal
            $orderDeviceId = $order['config']['point']['terminal_id'] ?? null;
            $orderStatus = $order['status'] ?? null;
            
            if ($orderDeviceId === $deviceId && $orderStatus === 'pending') {
                $pendingOrders[] = $order;
            }
        }
    }
    
    if (empty($pendingOrders)) {
        echo json_encode([
            'success' => true,
            'message' => 'No hay órdenes pendientes en este terminal',
            'cancelled_count' => 0
        ]);
        exit;
    }
    
    // Cancelar cada orden pendiente
    $cancelledCount = 0;
    $errors = [];
    
    foreach ($pendingOrders as $order) {
        $orderId = $order['id'];
        
        // Llamar al endpoint de cancelación de MercadoPago
        $cancelUrl = "https://api.mercadopago.com/v1/orders/$orderId";
        
        $ch = curl_init($cancelUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $cancelResponse = curl_exec($ch);
        $cancelHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Log de la cancelación
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'cancel_order',
            'order_id' => $orderId,
            'http_code' => $cancelHttpCode,
            'response' => $cancelResponse
        ];
        file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        if ($cancelHttpCode === 200 || $cancelHttpCode === 204) {
            $cancelledCount++;
        } else {
            $errors[] = "Error al cancelar orden $orderId (HTTP $cancelHttpCode)";
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Se cancelaron $cancelledCount órdenes pendientes",
        'cancelled_count' => $cancelledCount,
        'total_found' => count($pendingOrders),
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al cancelar órdenes',
        'message' => $e->getMessage()
    ]);
}
