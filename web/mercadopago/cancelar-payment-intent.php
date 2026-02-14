<?php
/**
 * Cancelar payment intent usando el endpoint correcto
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;
$paymentIntentId = $argv[1] ?? null;

if (!$paymentIntentId) {
    die("\nUso: php cancelar-payment-intent.php [ORDER_ID]\n\n");
}

echo "\n🚫 CANCELAR PAYMENT INTENT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Device ID: {$deviceId}\n";
echo "Payment Intent ID: {$paymentIntentId}\n\n";

// Endpoint correcto de MercadoPago
$url = "https://api.mercadopago.com/point/integration-api/devices/{$deviceId}/payment-intents/{$paymentIntentId}";

echo "📡 Enviando solicitud de cancelación...\n";
echo "URL: {$url}\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";

if ($curlError) {
    echo "CURL Error: {$curlError}\n\n";
}

if ($httpCode === 200 || $httpCode === 204) {
    echo "\n✅ ¡Payment Intent cancelado exitosamente!\n\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✨ El terminal está libre, podés enviar un nuevo pago\n\n";
    
} else if ($httpCode === 404) {
    echo "\nℹ️  Payment Intent no encontrado o ya fue procesado\n\n";
    echo "✅ El terminal debería estar libre\n\n";
    
} else {
    echo "\n❌ Error al cancelar\n\n";
    echo "Respuesta:\n";
    echo $response . "\n\n";
    
    $error = json_decode($response, true);
    if ($error) {
        echo "JSON decodificado:\n";
        echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
}
