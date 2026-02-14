<?php
/**
 * Listar todas las órdenes recientes
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;

echo "\n📋 LISTAR ÓRDENES RECIENTES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$url = "https://api.mercadopago.com/point/integration-api/orders";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    
    if (isset($data['orders']) && count($data['orders']) > 0) {
        echo "Total de órdenes: " . count($data['orders']) . "\n\n";
        
        foreach ($data['orders'] as $order) {
            $amount = $order['total_amount'] ?? 'N/A';
            $status = $order['status'] ?? 'N/A';
            $orderId = $order['id'] ?? 'N/A';
            $created = $order['created_on'] ?? 'N/A';
            
            echo "╔═══════════════════════════════════════════════════════════════╗\n";
            echo "🆔 Order ID: {$orderId}\n";
            echo "💰 Monto: \${$amount}\n";
            echo "📊 Estado: {$status}\n";
            echo "📅 Creado: {$created}\n";
            echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
        }
    } else {
        echo "ℹ️ No hay órdenes recientes\n\n";
    }
    
    echo "Respuesta completa:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
} else {
    echo "❌ Error al listar órdenes\n\n";
    echo $response . "\n\n";
}
