<?php
/**
 * Listar todos los Stores de la cuenta
 * GET https://api.mercadopago.com/point/integration-api/stores
 */

require_once __DIR__ . '/config.php';

// Access token de cuenta 2
$accessToken = "APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034";

$url = "https://api.mercadopago.com/point/integration-api/stores/search";

echo "🔍 Listando todos los Stores de la cuenta\n";
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
    echo "✅ Stores encontrados: " . count($data['results'] ?? []) . "\n\n";
    
    foreach (($data['results'] ?? []) as $store) {
        echo "📍 Store ID: " . ($store['id'] ?? 'N/A') . "\n";
        echo "   Name: " . ($store['name'] ?? 'N/A') . "\n";
        echo "   External ID: " . ($store['external_id'] ?? 'N/A') . "\n";
        echo "   Status: " . ($store['status'] ?? 'N/A') . "\n\n";
    }
} else {
    echo "❌ Error listando stores\n";
}
