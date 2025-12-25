<?php
require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;

echo "\n📱 MI TERMINAL - Información Real\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$ch = curl_init("https://api.mercadopago.com/terminals/v1/list");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    if (isset($data['data']['terminals']) && count($data['data']['terminals']) > 0) {
        foreach ($data['data']['terminals'] as $terminal) {
            echo "╔═══════════════════════════════════════════════════════════════╗\n";
            echo "║                    TU TERMINAL REAL                           ║\n";
            echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
            
            echo "🆔 TERMINAL ID:\n";
            echo "   " . $terminal['id'] . "\n\n";
            
            echo "🏪 STORE ID:\n";
            echo "   " . $terminal['store_id'] . "\n\n";
            
            echo "🖥️  POS ID:\n";
            echo "   " . $terminal['pos_id'] . "\n\n";
            
            echo "📋 EXTERNAL POS ID:\n";
            echo "   " . ($terminal['external_pos_id'] ?: '(vacío)') . "\n\n";
            
            echo "⚙️  MODO DE OPERACIÓN:\n";
            echo "   " . $terminal['operating_mode'] . "\n\n";
            
            echo "───────────────────────────────────────────────────────────────\n\n";
        }
        
        echo "📊 TOTAL DE TERMINALES: " . count($data['data']['terminals']) . "\n\n";
        
        // Mostrar también el JSON completo
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "JSON COMPLETO:\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
    } else {
        echo "❌ No se encontraron terminales en tu cuenta\n\n";
        echo "Respuesta completa:\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
} else {
    echo "❌ Error al consultar terminales\n";
    echo "Respuesta: {$response}\n\n";
}
