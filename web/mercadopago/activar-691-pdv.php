<?php
/**
 * ACTIVAR TERMINAL 691 (AHUMADA) A MODO PDV - CUENTA 3
 * Serial: N950NCC804178691
 */

$accessToken = "APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034";
$deviceId = "NEWLAND_N950__N950NCC804178691";

echo "===============================================\n";
echo "  ACTIVAR TERMINAL AHUMADA 691 A MODO PDV\n";
echo "===============================================\n\n";
echo "🖥️  Terminal: $deviceId\n\n";

$data = [
    'terminals' => [
        [
            'id' => $deviceId,
            'operating_mode' => 'PDV'
        ]
    ]
];

echo "📡 Enviando request a MercadoPago...\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/terminals/v1/setup");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n\n";

if ($httpCode == 200 || $httpCode == 201) {
    echo "✅ ¡ÉXITO! Terminal activado en modo PDV\n\n";
    
    $result = json_decode($response, true);
    
    if (isset($result['data']['terminals'][0])) {
        $terminal = $result['data']['terminals'][0];
        echo "🖥️  Terminal ID: " . $terminal['id'] . "\n";
        echo "🔧 Modo: " . $terminal['operating_mode'] . "\n";
        echo "🏪 Store ID: " . $terminal['store_id'] . "\n";
        echo "📍 POS ID: " . ($terminal['pos_id'] ?? 'N/A') . "\n";
    } else {
        echo "Respuesta completa:\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
    
    echo "\n💡 El terminal debe sincronizar en 2-3 minutos\n";
    echo "   Ya puedes enviar pagos desde Ahumada\n\n";
    
} else {
    echo "❌ ERROR $httpCode\n\n";
    
    $result = json_decode($response, true);
    
    if ($result) {
        echo "Detalles del error:\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo "Respuesta:\n$response\n";
    }
    
    echo "\n";
}

echo "===============================================\n";
