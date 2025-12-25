<?php
// Headers CORS para permitir peticiones desde la app Electron
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

require_once __DIR__ . '/config.php';

// Obtener datos del POST
$monto = isset($_POST['monto']) ? intval($_POST['monto']) : 0;

if ($monto < 100) {
    echo json_encode([
        'success' => false,
        'message' => 'El monto mínimo es $100 CLP'
    ]);
    exit();
}

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;
$externalReference = "REF-" . time();

$payload = [
    "type" => "point",
    "external_reference" => $externalReference,
    "description" => "Venta Totem - $" . number_format($monto, 0, ',', '.'),
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
    echo json_encode([
        'success' => true,
        'orderId' => $responseData['id'],
        'monto' => $monto,
        'reference' => $externalReference,
        'status' => $responseData['status'],
        'message' => 'Pago enviado exitosamente al terminal Point Smart'
    ]);
} else if ($httpCode === 409) {
    echo json_encode([
        'success' => false,
        'message' => 'Ya hay un pago pendiente en el terminal. Completa o cancela el pago actual primero.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el pago en Mercado Pago',
        'error' => $responseData,
        'httpCode' => $httpCode
    ]);
}
