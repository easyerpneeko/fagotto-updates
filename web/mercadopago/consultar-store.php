<?php
/**
 * Consultar Store en MercadoPago
 * GET https://api.mercadopago.com/point/integration-api/stores/{store_id}
 */

require_once __DIR__ . '/config.php';

// Store ID del PAX A910
$storeId = "73565262";

// Access token de cuenta 2
$accessToken = "APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034";

$url = "https://api.mercadopago.com/point/integration-api/stores/{$storeId}";

echo "🔍 Consultando Store ID: {$storeId}\n";
echo "URL: {$url}\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$accessToken}",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "✅ Store encontrado:\n";
    echo "   ID: " . ($data['id'] ?? 'N/A') . "\n";
    echo "   Name: " . ($data['name'] ?? 'N/A') . "\n";
    echo "   External ID: " . ($data['external_id'] ?? 'N/A') . "\n";
    echo "   Location: " . json_encode($data['location'] ?? []) . "\n";
} else {
    echo "❌ Error consultando store\n";
}
