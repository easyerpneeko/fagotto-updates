<?php
/**
 * Cambiar modo de operación del terminal
 * STANDALONE → PDV
 * 
 * Uso: php cambiar-modo-terminal.php
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;

echo "\n🔧 CAMBIAR MODO DE TERMINAL\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Terminal: {$deviceId}\n";
echo "Cambiando a modo: PDV\n\n";

// Endpoint para cambiar el modo (varios intentos)
$endpoints = [
    "https://api.mercadopago.com/point/integration-api/devices/{$deviceId}",
    "https://api.mercadopago.com/devices/{$deviceId}",
    "https://api.mercadopago.com/point/services/integrations/v1/devices/{$deviceId}"
];

$data = [
    "operating_mode" => "PDV"
];

foreach ($endpoints as $index => $url) {
    echo "Intento " . ($index + 1) . ": {$url}\n";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: {$httpCode}\n";
    
    if ($httpCode === 200 || $httpCode === 204) {
        echo "✅ ÉXITO con este endpoint!\n\n";
        break;
    } else {
        echo "Error: {$response}\n\n";
    }
}

echo "\n";

if ($httpCode === 200 || $httpCode === 204) {
    echo "✅ ÉXITO: Terminal cambiado a modo PDV\n\n";
    echo "🔄 Verificando cambio...\n\n";
    
    // Verificar el cambio
    sleep(2);
    $verifyUrl = "https://api.mercadopago.com/point/integration-api/devices/{$deviceId}";
    $ch = curl_init($verifyUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    
    $verifyResponse = curl_exec($ch);
    $verifyData = json_decode($verifyResponse, true);
    curl_close($ch);
    
    if (isset($verifyData['operating_mode'])) {
        echo "📱 Modo actual: " . $verifyData['operating_mode'] . "\n\n";
    }
    
    echo json_encode($verifyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
} else {
    echo "❌ ERROR al cambiar modo\n\n";
    echo "Respuesta:\n";
    echo $response . "\n\n";
    
    $error = json_decode($response, true);
    if ($error) {
        echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "💡 TIP: Si el cambio fue exitoso, el terminal ahora puede\n";
echo "   recibir órdenes de pago desde tu sistema.\n\n";
