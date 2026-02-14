<?php
/**
 * Cancelar orden con PUT
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$orderId = $argv[1] ?? null;

if (!$orderId) {
    die("Uso: php cancelar-orden.php [ORDER_ID]\n");
}

echo "\n🚫 CANCELAR ORDEN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Order ID: {$orderId}\n\n";

// Intentar con PUT
$url = "https://api.mercadopago.com/point/integration-api/orders/{$orderId}";

$data = json_encode([
    'status' => 'cancelled'
]);

echo "📡 Enviando solicitud de cancelación...\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 200 || $httpCode === 204) {
    echo "✅ ¡Orden cancelada exitosamente!\n\n";
} else {
    echo "❌ Error al cancelar\n\n";
    echo "Respuesta:\n";
    echo $response . "\n\n";
    
    // Intentar con el endpoint de devices
    echo "🔄 Intentando con endpoint alternativo...\n\n";
    
    $deviceId = MP_DEVICE_ID;
    $url2 = "https://api.mercadopago.com/point/integration-api/devices/{$deviceId}/payment-intents/{$orderId}";
    
    $ch2 = curl_init($url2);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    
    $response2 = curl_exec($ch2);
    $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    
    echo "HTTP Code: {$httpCode2}\n\n";
    
    if ($httpCode2 === 200 || $httpCode2 === 204) {
        echo "✅ ¡Orden cancelada exitosamente con endpoint alternativo!\n\n";
    } else {
        echo "❌ Error con endpoint alternativo\n\n";
        echo "Respuesta:\n";
        echo $response2 . "\n\n";
    }
}
