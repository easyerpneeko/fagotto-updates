<?php
/**
 * CAMBIAR TERMINAL A MODO PDV (Point of Sale)
 * Habilita el terminal para recibir pagos por API
 */

// Cargar configuraciones
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("❌ No se encontró el archivo .env\n");
}

$envContent = file_get_contents($envFile);
$envLines = explode("\n", $envContent);
$env = [];
foreach ($envLines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

$accessToken = $env['MP_ACCESS_TOKEN'] ?? null;
$deviceId = $env['MP_DEVICE_ID'] ?? null;

if (!$accessToken || !$deviceId) {
    die("❌ Faltan credenciales en .env\n");
}

echo "🔄 CAMBIAR TERMINAL A MODO PDV\n";
echo str_repeat("═", 70) . "\n\n";
echo "🖥️  Terminal: $deviceId\n\n";

// Cambiar a PDV
echo "📡 Cambiando a modo PDV (Point of Sale)...\n";

$data = [
    'terminals' => [
        [
            'id' => $deviceId,
            'operating_mode' => 'PDV'
        ]
    ]
];

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
    echo "✅ ¡Terminal cambiado a PDV exitosamente!\n\n";
    $result = json_decode($response, true);
    
    if (isset($result['data']['terminals'][0])) {
        $terminal = $result['data']['terminals'][0];
        echo "🖥️  Terminal: " . $terminal['id'] . "\n";
        echo "🔧 Modo: " . $terminal['operating_mode'] . "\n";
        echo "🏪 Store: " . $terminal['store_id'] . "\n\n";
    } else {
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    }
    
    echo "💡 El terminal debe sincronizar en 2-3 minutos\n";
    echo "   Ya puedes enviar pagos: php enviar-pago.php 500\n\n";
} else {
    echo "❌ Error al cambiar el modo\n\n";
    echo "Respuesta:\n";
    echo $response . "\n\n";
}

echo str_repeat("═", 70) . "\n";
