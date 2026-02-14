<?php
/**
 * Actualizar configuración del POS
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;

// Obtener POS ID del argumento o del terminal actual
$posId = $argv[1] ?? null;

if (!$posId) {
    echo "Obteniendo POS ID del terminal actual...\n";
    // Aquí podríamos obtenerlo automáticamente, pero por ahora pedimos que lo pasen
    die("\nUso: php actualizar-pos.php [POS_ID]\n\n");
}

echo "\n🔧 ACTUALIZAR CONFIGURACIÓN DEL POS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "POS ID: {$posId}\n\n";

$url = "https://api.mercadopago.com/pos/{$posId}";

// Datos para actualizar (solo nombre, external_id debe ser alfanumérico)
$data = [
    "name" => "POS Fagotto Cuenta 4"
];

echo "📡 Enviando actualización...\n";
echo "URL: {$url}\n";
echo "Data: " . json_encode($data) . "\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 200) {
    echo "✅ ¡POS actualizado exitosamente!\n\n";
    
    $result = json_decode($response, true);
    
    if ($result) {
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║                    POS ACTUALIZADO                            ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
        
        echo "🆔 POS ID: " . ($result['id'] ?? 'N/A') . "\n";
        echo "📝 Nombre: " . ($result['name'] ?? 'N/A') . "\n";
        echo "📊 Status: " . ($result['status'] ?? 'N/A') . "\n";
        echo "🏪 Store ID: " . ($result['store_id'] ?? 'N/A') . "\n\n";
        
        echo "JSON Completo:\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    
} else {
    echo "❌ Error al actualizar\n\n";
    echo "Respuesta:\n";
    echo $response . "\n\n";
    
    $error = json_decode($response, true);
    if ($error) {
        echo "JSON decodificado:\n";
        echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
}
