<?php
/**
 * Verificar información del token y usuario
 * GET https://api.mercadopago.com/users/me
 */

require_once __DIR__ . '/config.php';

// Access token de cuenta 2
$accessToken = "APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034";

$url = "https://api.mercadopago.com/users/me";

echo "🔍 Verificando información del token\n";
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
    echo "✅ Usuario:\n";
    echo "   ID: " . ($data['id'] ?? 'N/A') . "\n";
    echo "   Site ID: " . ($data['site_id'] ?? 'N/A') . "\n";
    echo "   Country: " . ($data['country_id'] ?? 'N/A') . "\n";
    echo "   Email: " . ($data['email'] ?? 'N/A') . "\n";
    echo "   Type: " . ($data['user_type'] ?? 'N/A') . "\n";
} else {
    echo "❌ Error verificando token\n";
}
