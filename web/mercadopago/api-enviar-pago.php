<?php
/**
 * API para integración de MercadoPago Point con Backend Laravel
 * 
 * Este archivo recibe peticiones del backend Laravel para enviar pagos
 * al terminal MercadoPago Point configurado en el .env
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
$data = [];

// Intentar parsear como JSON
$jsonData = json_decode($inputData, true);
if (json_last_error() === JSON_ERROR_NONE && $jsonData) {
    $data = $jsonData;
} else {
    // Si no es JSON, intentar parsear como POST data
    parse_str($inputData, $data);
}

// Si todavía no hay datos, intentar $_POST
if (empty($data)) {
    $data = $_POST;
}

// Log de la petición recibida
$logFile = __DIR__ . '/storage/logs/api-' . date('Y-m-d') . '.log';
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'input_raw' => $inputData,
    'data_parsed' => $data,
    'headers' => getallheaders()
];
file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

// Validar datos requeridos
if (!isset($data['monto']) || empty($data['monto'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Datos inválidos',
        'message' => 'El campo "monto" es requerido'
    ]);
    exit;
}

$monto = intval($data['monto']);

if ($monto < 100) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Monto inválido',
        'message' => 'El monto mínimo es $100 CLP'
    ]);
    exit;
}

// Obtener app_id del payload para seleccionar credenciales correctas
$appId = $data['app_id'] ?? null;

// Seleccionar credenciales según app_id
if ($appId == 58) {
    // Agustinas - Cuenta 4
    $accessToken = getenv('MP_ACCESS_TOKEN_58');
    $deviceId = getenv('MP_DEVICE_ID_58');
} elseif ($appId == 116) {
    // Las Condes - Cuenta 1
    $accessToken = getenv('MP_ACCESS_TOKEN_116');
    $deviceId = getenv('MP_DEVICE_ID_116');
} else {
    // Fallback: credenciales del payload o error
    $accessToken = $data['access_token'] ?? null;
    $deviceId = $data['device_id'] ?? null;
}

if (empty($accessToken) || empty($deviceId)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuración incompleta',
        'message' => 'Faltan credenciales de MercadoPago para app_id: ' . $appId
    ]);
    exit;
}

// Preparar datos para MercadoPago
$descripcion = $data['descripcion'] ?? "Venta Fagotto Las Condes - $" . number_format($monto, 0, ',', '.');
$externalReference = $data['referencia'] ?? "REF-" . time();

// Determinar tipo de pago: 'credit' o 'debit'
$paymentType = $data['payment_type'] ?? 'debit';
$mercadoPagoPaymentType = ($paymentType === 'credit') ? 'credit_card' : 'debit_card';

// Determinar external_store_id según el terminal (para evitar errores de site_id)
$externalStoreId = null;
if (strpos($deviceId, 'N950NCC302980807') !== false) {
    // Terminal 1 NEWLAND - Cuenta 2
    $externalStoreId = "75998370";
} elseif (strpos($deviceId, 'SMARTPOS1495485450') !== false) {
    // Terminal PAX A910 - Cuenta 2/3
    $externalStoreId = "73565262";
} elseif (strpos($deviceId, 'N950NCC302980808') !== false) {
    // Terminal NEWLAND - Cuenta 1 Las Condes
    $externalStoreId = "76216860";
} elseif (strpos($deviceId, 'N950NCC804178629') !== false) {
    // Terminal NEWLAND - Cuenta 4 Agustinas
    $externalStoreId = "77440880";
}

$payload = [
    "type" => "point",
    "external_reference" => $externalReference,
    "description" => $descripcion,
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
            "default_type" => $mercadoPagoPaymentType // Dinámico: credit_card o debit_card
        ]
    ],
    "taxes" => [
        [
            "payer_condition" => "payment_taxable_iva"
        ]
    ]
];

// Agregar external_store_id si está disponible (ayuda a evitar errores de site_id)
if ($externalStoreId !== null) {
    $payload['external_store_id'] = $externalStoreId;
}

// Generar idempotency key único
$idempotencyKey = uniqid('mppoint_api_', true);

// Log del payload que se enviará
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'sending_to_mercadopago',
    'payment_type_received' => $paymentType,
    'mercadopago_payment_type' => $mercadoPagoPaymentType,
    'payload' => $payload,
    'idempotency_key' => $idempotencyKey
];
file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

// Enviar petición a MercadoPago
try {
    $ch = curl_init("https://api.mercadopago.com/v1/orders");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'X-Idempotency-Key: ' . $idempotencyKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Log de la respuesta
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => 'response_from_mercadopago',
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
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 201) {
        // Pago creado exitosamente
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'order_id' => $responseData['id'],
            'status' => $responseData['status'],
            'external_reference' => $externalReference,
            'amount' => $monto,
            'message' => 'Pago enviado al terminal exitosamente',
            'response_data' => $responseData
        ]);
        
    } else if ($httpCode === 409) {
        // Ya hay un pago pendiente
        http_response_code(409);
        echo json_encode([
            'error' => 'Pago pendiente',
            'message' => 'Ya hay un pago pendiente en el terminal. Cancela o completa el pago actual.',
            'response_data' => $responseData
        ]);
        
    } else {
        // Otro error
        http_response_code($httpCode);
        echo json_encode([
            'error' => 'Error al crear pago',
            'message' => 'Error al crear el pago en MercadoPago',
            'http_code' => $httpCode,
            'response_data' => $responseData
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Excepción',
        'message' => 'Error inesperado: ' . $e->getMessage()
    ]);
}
