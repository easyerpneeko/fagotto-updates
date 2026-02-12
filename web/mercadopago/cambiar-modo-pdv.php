<?php
/**
 * Cambiar modo de operación del terminal
 * STANDALONE → PDV (Punto de Venta integrado)
 * 
 * IMPORTANTE: Solo funciona con NEWLAND_N950 y PAX_A910
 * Endpoint oficial: https://api.mercadopago.com/terminals/v1/setup
 * 
 * Uso: php cambiar-modo-pdv.php
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;

echo "\n🔧 CAMBIAR MODO DE TERMINAL A PDV\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Terminal: {$deviceId}\n";
echo "Modo objetivo: PDV (Punto de Venta integrado)\n\n";

// Endpoint oficial de MercadoPago
$url = "https://api.mercadopago.com/terminals/v1/setup";

$data = [
    "terminals" => [
        [
            "id" => $deviceId,
            "operating_mode" => "PDV"
        ]
    ]
];

echo "📡 Enviando solicitud a MercadoPago...\n\n";

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

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 200) {
    echo "✅ ¡ÉXITO! Terminal cambiado a modo PDV\n\n";
    
    $result = json_decode($response, true);
    
    if (isset($result['terminals']) && count($result['terminals']) > 0) {
        foreach ($result['terminals'] as $terminal) {
            echo "╔═══════════════════════════════════════════════════════════════╗\n";
            echo "║                 CONFIGURACIÓN ACTUALIZADA                     ║\n";
            echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
            
            echo "🆔 Terminal ID: " . $terminal['id'] . "\n";
            echo "⚙️  Modo: " . $terminal['operating_mode'] . "\n\n";
        }
    }
    
    echo "Respuesta completa:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ El terminal ahora puede recibir órdenes de pago desde tu API\n\n";
    
} else {
    echo "❌ ERROR al cambiar modo\n\n";
    
    $error = json_decode($response, true);
    if ($error) {
        echo "Mensaje: " . ($error['message'] ?? 'Sin mensaje') . "\n";
        echo "Error: " . ($error['error'] ?? 'Sin código') . "\n";
        echo "Status: " . ($error['status'] ?? $httpCode) . "\n\n";
        
        if (isset($error['cause']) && count($error['cause']) > 0) {
            echo "Causas:\n";
            foreach ($error['cause'] as $cause) {
                echo "  - " . json_encode($cause);
                echo "\n";
            }
            echo "\n";
        }
        
        echo "Respuesta completa:\n";
        echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    } else {
        echo "Respuesta:\n{$response}\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "💡 TIPS para solucionar:\n";
    echo "  - Verifica que el ACCESS_TOKEN sea de producción\n";
    echo "  - Solo funciona con NEWLAND_N950 y PAX_A910\n";
    echo "  - El terminal debe estar vinculado a una Store y POS\n";
    echo "  - Solo puede haber 1 terminal en PDV por POS\n\n";
}

echo "🔄 Verifica el cambio ejecutando: php ver-mi-terminal.php\n\n";
