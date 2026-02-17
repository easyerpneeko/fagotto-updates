<?php
/**
 * Test rápido de configuración después del cambio a terminal 808
 * Subir a: fagottoerp.cl/mercadopago/test-terminal-808.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';

$result = [
    'test' => 'Verificación de cambio a Terminal 808',
    'timestamp' => date('Y-m-d H:i:s'),
];

// Verificar variables de entorno para app_id 116 (Las Condes)
$token116 = getenv('MP_ACCESS_TOKEN_116');
$device116 = getenv('MP_DEVICE_ID_116');

$result['app_id_116_las_condes'] = [
    'access_token' => $token116 ? substr($token116, 0, 20) . '...' : '❌ NO ENCONTRADO',
    'device_id' => $device116,
    'esperado_device_id' => 'NEWLAND_N950__N950NCC302980808',
    'esperado_token_inicio' => 'APP_USR-628335597394',
    'token_es_correcto' => strpos($token116, 'APP_USR-6283355973944660') === 0 ? '✅ Cuenta 1 (correcto)' : '❌ Token incorrecto',
    'configuracion_correcta' => ($device116 === 'NEWLAND_N950__N950NCC302980808' && strpos($token116, 'APP_USR-6283355973944660') === 0) ? '✅ SÍ' : '❌ NO'
];

// Simular payload de Las Condes
$testPayload = [
    'monto' => 200,
    'descripcion' => 'Test Terminal 808',
    'referencia' => 'TEST-808-' . time(),
    'productos' => json_encode([]),
    'app_id' => 116,
    'payment_type' => 'debit'
];

$result['payload_prueba'] = $testPayload;

// Obtener credenciales según app_id (como lo hace api-enviar-pago.php)
$appId = 116;
if ($appId == 116) {
    $accessToken = getenv('MP_ACCESS_TOKEN_116');
    $deviceId = getenv('MP_DEVICE_ID_116');
}

$result['credenciales_que_se_usaran'] = [
    'access_token' => $accessToken ? substr($accessToken, 0, 20) . '...' . substr($accessToken, -10) : '❌ VACÍO',
    'device_id' => $deviceId ?: '❌ VACÍO',
    'es_terminal_808' => strpos($deviceId, '808') !== false ? '✅ SÍ' : '❌ NO'
];

// Preparar payload para MercadoPago (como lo hace api-enviar-pago.php)
$monto = intval($testPayload['monto']);
$descripcion = $testPayload['descripcion'];
$externalReference = $testPayload['referencia'];
$paymentType = 'debit';
$mercadoPagoPaymentType = 'debit_card';

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
            "default_type" => $mercadoPagoPaymentType
        ]
    ],
    "taxes" => [
        [
            "payer_condition" => "payment_taxable_iva"
        ]
    ]
];

$result['payload_mercadopago'] = $payload;
$result['terminal_id_en_payload'] = $payload['config']['point']['terminal_id'];
$result['confirmacion'] = $payload['config']['point']['terminal_id'] === 'NEWLAND_N950__N950NCC302980808' 
    ? '✅ CORRECTO - Usará terminal 808' 
    : '❌ ERROR - No usará terminal 808';

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
