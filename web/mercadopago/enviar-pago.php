<?php
/**
 * Enviar pago rápido desde terminal
 * Uso: php enviar-pago.php [MONTO]
 */

require_once __DIR__ . '/config.php';

$monto = $argv[1] ?? 1000;
$monto = intval($monto);

if ($monto < 100) {
    die("❌ El monto mínimo es 100 CLP\n\n");
}

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;
$externalReference = "TEST-" . time();

echo "\n💳 ENVIANDO PAGO AL TERMINAL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Terminal: {$deviceId}\n";
echo "Monto: \${$monto} CLP\n";
echo "Referencia: {$externalReference}\n\n";

$payload = [
    "type" => "point",
    "external_reference" => $externalReference,
    "description" => "Test - $" . number_format($monto, 0, ',', '.'),
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
    ]
];

$idempotencyKey = uniqid('test_', true);

echo "📡 Enviando a MercadoPago...\n\n";

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

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 201) {
    echo "✅ ¡PAGO ENVIADO EXITOSAMENTE!\n\n";
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                    ORDEN CREADA                              ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
    
    echo "🆔 Order ID: " . $responseData['id'] . "\n";
    echo "💰 Monto: \${$monto} CLP\n";
    echo "📋 Referencia: {$externalReference}\n";
    echo "📊 Estado: " . $responseData['status'] . "\n\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "🖨️  El cobro YA DEBE ESTAR EN EL TERMINAL\n";
    echo "📱 Esperando que pases la tarjeta...\n\n";
    
    echo "💡 Para ver el estado del pago:\n";
    echo "   php validar-pago.php {$responseData['id']}\n\n";
    
} else if ($httpCode === 409) {
    echo "⚠️  HAY UN PAGO PENDIENTE EN EL TERMINAL\n\n";
    echo "Opciones:\n";
    echo "1. Cancela el pago en el terminal físicamente\n";
    echo "2. Espera 5-10 minutos a que expire\n";
    echo "3. Ejecuta: php limpiar-terminal.php\n\n";
    
} else {
    echo "❌ ERROR AL CREAR EL PAGO\n\n";
    echo "Respuesta:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}
