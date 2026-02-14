<?php
require_once __DIR__ . '/config.php';

$orderId = $argv[1] ?? null;

if (!$orderId) {
    die("Uso: php consultar-estado-cli.php [ORDER_ID]\n");
}

$accessToken = MP_ACCESS_TOKEN;
$url = "https://api.mercadopago.com/v1/orders/{$orderId}";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

echo "\n📊 Estado del Pago: {$orderId}\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "HTTP Code: {$httpCode}\n";
echo "Estado: " . ($data['status'] ?? 'error') . "\n\n";

if (isset($data['transactions']['payments'][0])) {
    $payment = $data['transactions']['payments'][0];
    echo "💳 Información de Pago:\n";
    echo "   Payment ID: " . ($payment['id'] ?? 'N/A') . "\n";
    echo "   Estado: " . ($payment['status'] ?? 'N/A') . "\n";
    echo "   Detalle: " . ($payment['status_detail'] ?? 'N/A') . "\n";
    echo "   Monto: $" . ($payment['amount'] ?? 'N/A') . "\n\n";
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
