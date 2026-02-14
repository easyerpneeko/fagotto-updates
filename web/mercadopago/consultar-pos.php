<?php
/**
 * Consultar información del POS
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;

echo "\n📊 INFORMACIÓN DEL POS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Obtener POS ID del terminal actual
echo "1️⃣ Obteniendo información del terminal...\n\n";
$terminalUrl = "https://api.mercadopago.com/point/integration-api/devices";

$ch1 = curl_init($terminalUrl);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$terminalResponse = curl_exec($ch1);
$httpCode1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
curl_close($ch1);

if ($httpCode1 === 200) {
    $terminalData = json_decode($terminalResponse, true);
    
    if (isset($terminalData['devices']) && count($terminalData['devices']) > 0) {
        $device = $terminalData['devices'][0];
        $posId = $device['pos_id'] ?? null;
        $storeId = $device['store_id'] ?? null;
        
        echo "   Terminal ID: " . ($device['id'] ?? 'N/A') . "\n";
        echo "   POS ID: {$posId}\n";
        echo "   Store ID: {$storeId}\n\n";
        
        if ($posId) {
            echo "2️⃣ Consultando información del POS...\n\n";
            
            $posUrl = "https://api.mercadopago.com/pos/{$posId}";
            
            $ch2 = curl_init($posUrl);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken
            ]);
            
            $posResponse = curl_exec($ch2);
            $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            curl_close($ch2);
            
            echo "   HTTP Code: {$httpCode2}\n\n";
            
            if ($httpCode2 === 200) {
                $posData = json_decode($posResponse, true);
                
                echo "╔═══════════════════════════════════════════════════════════════╗\n";
                echo "║                    INFORMACIÓN DEL POS                        ║\n";
                echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
                
                echo "🆔 POS ID: " . ($posData['id'] ?? 'N/A') . "\n";
                echo "📝 Nombre: " . ($posData['name'] ?? 'N/A') . "\n";
                echo "🏪 Store ID: " . ($posData['store_id'] ?? 'N/A') . "\n";
                echo "📍 External Store ID: " . ($posData['external_store_id'] ?? 'N/A') . "\n";
                echo "🔧 External ID: " . ($posData['external_id'] ?? 'N/A') . "\n";
                echo "📊 Status: " . ($posData['status'] ?? 'N/A') . "\n";
                echo "📅 Fecha creación: " . ($posData['date_created'] ?? 'N/A') . "\n";
                echo "🔄 Última actualización: " . ($posData['date_last_updated'] ?? 'N/A') . "\n\n";
                
                echo "JSON Completo:\n";
                echo json_encode($posData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
                
                echo "═══════════════════════════════════════════════════════════════\n";
                echo "💡 USOS DEL ENDPOINT /pos/:posId:\n\n";
                echo "GET    - Obtener información del POS\n";
                echo "PUT    - Actualizar nombre, external_id, status\n";
                echo "DELETE - Eliminar el POS (desvincula terminales)\n\n";
                
            } else {
                echo "   ❌ Error al consultar POS\n";
                echo "   Respuesta: {$posResponse}\n\n";
            }
        }
    }
} else {
    echo "❌ Error al obtener información del terminal\n";
    echo "Respuesta: {$terminalResponse}\n\n";
}
