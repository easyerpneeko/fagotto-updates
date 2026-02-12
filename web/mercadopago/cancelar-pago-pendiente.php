<?php
/**
 * Cancelar pago pendiente en el terminal
 * 
 * Uso: php cancelar-pago-pendiente.php [ORDER_ID]
 * Si no se proporciona ORDER_ID, intenta buscar pagos pendientes
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;

echo "\n🚫 CANCELAR PAGO PENDIENTE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Terminal: {$deviceId}\n\n";

// Si se proporciona un Order ID como argumento
$orderId = $argv[1] ?? null;

if ($orderId) {
    echo "Order ID proporcionado: {$orderId}\n\n";
    cancelarPago($orderId, $accessToken);
} else {
    echo "🔍 Buscando pagos pendientes...\n\n";
    
    // Buscar órdenes recientes del terminal
    $searchUrl = "https://api.mercadopago.com/v1/orders/search?sort=date_created&criteria=desc&limit=10";
    
    $ch = curl_init($searchUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        if (isset($data['results']) && count($data['results']) > 0) {
            $pendientes = array_filter($data['results'], function($order) use ($deviceId) {
                return ($order['status'] === 'opened' || $order['status'] === 'processing') 
                       && isset($order['config']['point']['terminal_id'])
                       && $order['config']['point']['terminal_id'] === $deviceId;
            });
            
            if (count($pendientes) > 0) {
                echo "📋 Pagos pendientes encontrados:\n\n";
                
                foreach ($pendientes as $order) {
                    $amount = $order['transactions']['payments'][0]['amount'] ?? 'N/A';
                    echo "Order ID: {$order['id']}\n";
                    echo "Monto: \${$amount} CLP\n";
                    echo "Estado: {$order['status']}\n";
                    echo "Fecha: {$order['date_created']}\n\n";
                    
                    echo "¿Cancelar este pago? (y/n): ";
                    $handle = fopen("php://stdin", "r");
                    $line = fgets($handle);
                    fclose($handle);
                    
                    if (trim($line) === 'y' || trim($line) === 'Y') {
                        cancelarPago($order['id'], $accessToken);
                        break;
                    }
                }
            } else {
                echo "✅ No hay pagos pendientes en este terminal\n\n";
            }
        } else {
            echo "ℹ️ No se encontraron órdenes recientes\n\n";
        }
    } else {
        echo "❌ Error al buscar órdenes (HTTP {$httpCode})\n";
        echo $response . "\n\n";
    }
}

function cancelarPago($orderId, $accessToken) {
    echo "🔄 Cancelando Order ID: {$orderId}...\n\n";
    
    $cancelUrl = "https://api.mercadopago.com/v1/orders/{$orderId}";
    
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
    
    if ($httpCode === 200 || $httpCode === 204) {
        echo "✅ Pago cancelado exitosamente\n\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "✨ El terminal ahora está libre para nuevos pagos\n\n";
    } else {
        echo "❌ Error al cancelar (HTTP {$httpCode})\n";
        echo $response . "\n\n";
        
        $error = json_decode($response, true);
        if ($error) {
            echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        }
    }
}
