<?php
/**
 * Cancelar el último pago pendiente del terminal
 * Cancela cualquier orden abierta en el terminal
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;

echo "\n🚫 CANCELAR PAGO PENDIENTE DEL TERMINAL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Terminal: {$deviceId}\n\n";

// Intentar cancelar usando el endpoint de órdenes Point
$cancelUrl = "https://api.mercadopago.com/point/integration-api/devices/{$deviceId}/orders/current";

echo "🔄 Intentando cancelar orden actual del terminal...\n\n";

$ch = curl_init($cancelUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 200 || $httpCode === 204) {
    echo "✅ ¡Pago cancelado exitosamente!\n\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✨ El terminal está libre, podés enviar un nuevo pago\n\n";
    
} else if ($httpCode === 404) {
    echo "ℹ️  No hay ningún pago pendiente en el terminal\n\n";
    echo "✅ El terminal está libre para recibir pagos\n\n";
    
} else {
    echo "❌ Error al cancelar\n\n";
    echo "Respuesta:\n";
    echo $response . "\n\n";
    
    $error = json_decode($response, true);
    if ($error) {
        echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "💡 TIP: Si el pago ya expiró, el terminal se liberará solo\n";
    echo "   O puedes reiniciar el terminal físicamente\n\n";
}
