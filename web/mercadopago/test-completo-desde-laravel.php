<?php
/**
 * Simular exactamente lo que hace MercadoPagoController
 * Subir a fagottoerp.cl/mercadopago/test-completo-desde-laravel.php
 */

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'test' => 'Prueba de conexión desde Laravel simulado',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'php_version' => phpversion(),
    'curl_enabled' => extension_loaded('curl')
], JSON_PRETTY_PRINT);

echo "\n\n";

// Simular payload de MercadoPagoController
$postData = [
    'monto' => 200,
    'descripcion' => 'Test desde Laravel simulado',
    'referencia' => 'TEST-' . time(),
    'productos' => json_encode([]),
    'app_id' => 116,
    'payment_type' => 'debit'
];

$jsonPayload = json_encode($postData);

echo "📤 Payload a enviar:\n";
echo json_encode($postData, JSON_PRETTY_PRINT);
echo "\n\n";

// URL exacta que usa MercadoPagoController
$url = 'https://fagottoerp.cl/mercadopago/api-enviar-pago.php';

echo "🌐 URL destino: $url\n\n";

// Hacer el mismo curl que MercadoPagoController
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonPayload)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

echo "⏳ Ejecutando curl...\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

echo "📊 RESULTADO:\n";
echo "═══════════════════════════════════════\n\n";

if ($curlError) {
    echo "❌ Error de cURL: $curlError\n\n";
} else {
    echo "✅ cURL ejecutado sin errores\n\n";
}

echo "HTTP Code: $httpCode\n";
echo "Content Type: " . ($curlInfo['content_type'] ?? 'N/A') . "\n";
echo "Total Time: " . ($curlInfo['total_time'] ?? 'N/A') . " segundos\n";
echo "Size Downloaded: " . ($curlInfo['size_download'] ?? 'N/A') . " bytes\n\n";

echo "📄 Respuesta del servidor:\n";
echo "───────────────────────────────────────\n";
echo $response;
echo "\n───────────────────────────────────────\n\n";

// Intentar parsear como JSON
$responseData = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ Respuesta es JSON válido:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n\n";
    
    if (isset($responseData['error'])) {
        echo "❌ ERROR en respuesta:\n";
        echo "   Tipo: " . $responseData['error'] . "\n";
        echo "   Mensaje: " . ($responseData['message'] ?? 'Sin mensaje') . "\n";
    }
} else {
    echo "⚠️ Respuesta NO es JSON válido\n";
    echo "Error JSON: " . json_last_error_msg() . "\n";
}

echo "\n═══════════════════════════════════════\n\n";

// Información de cURL completa
echo "🔍 Información completa de cURL:\n";
echo json_encode($curlInfo, JSON_PRETTY_PRINT);
