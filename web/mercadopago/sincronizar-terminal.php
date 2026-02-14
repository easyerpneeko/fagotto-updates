<?php
/**
 * SINCRONIZAR TERMINAL - Forzar actualización del dispositivo PAX/NEWLAND
 * Este script intenta forzar una sincronización del terminal con MercadoPago
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
    die("❌ Faltan credenciales en .env (MP_ACCESS_TOKEN, MP_DEVICE_ID)\n");
}

echo "🔄 SINCRONIZAR TERMINAL CON MERCADOPAGO\n";
echo str_repeat("═", 70) . "\n\n";
echo "🖥️  Device ID: $deviceId\n\n";

// 1. Forzar actualización del terminal via PATCH /terminals/v1/setup
echo "📡 1. Forzando sincronización del terminal (PATCH)...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/terminals/v1/setup");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'terminals' => [
        [
            'id' => $deviceId
        ]
    ]
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";

if ($httpCode == 201 || $httpCode == 200) {
    echo "   ✅ Sincronización forzada exitosamente\n";
    $result = json_decode($response, true);
    echo "   📋 Respuesta:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    // Mostrar info de los terminales actualizados
    if (isset($result['terminals'])) {
        foreach ($result['terminals'] as $term) {
            echo "   🖥️  Terminal: " . ($term['id'] ?? 'N/A') . "\n";
            echo "   🔧 Modo: " . ($term['operating_mode'] ?? 'N/A') . "\n";
            echo "   🏪 Store: " . ($term['store_id'] ?? 'N/A') . "\n";
            if (isset($term['pos'][0])) {
                echo "   🆔 POS: " . $term['pos'][0]['id'] . " - " . $term['pos'][0]['name'] . "\n";
            }
        }
    }
    echo "\n";
} else {
    echo "   ⚠️  Error al sincronizar\n";
    echo "   Respuesta: $response\n\n";
}

// 2. Intentar cambio a modo PDV (puede ayudar a refrescar)
echo "📡 2. Verificando modo PDV del terminal...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/terminals/v1/setup");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'terminals' => [
        [
            'id' => $deviceId,
            'operating_mode' => 'PDV'
        ]
    ]
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";

if ($httpCode == 201 || $httpCode == 200) {
    echo "   ✅ Modo PDV confirmado\n\n";
} else {
    echo "   ⚠️  Respuesta: $response\n\n";
}

// 3. Verificar si hay órdenes pendientes que puedan estar bloqueando
echo "📡 3. Verificando órdenes pendientes...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $orders = json_decode($response, true);
    $pending = 0;
    if (isset($orders['results'])) {
        foreach ($orders['results'] as $order) {
            if (in_array($order['status'], ['created', 'at_terminal', 'processing'])) {
                $pending++;
            }
        }
    }
    
    if ($pending > 0) {
        echo "   ⚠️  Hay $pending orden(es) pendiente(s) que pueden estar bloqueando\n";
        echo "   💡 Cancela en el terminal físicamente (botón rojo/atrás)\n\n";
    } else {
        echo "   ✅ No hay órdenes pendientes\n\n";
    }
} else {
    echo "   ⚠️  No se pudo verificar órdenes (HTTP $httpCode)\n\n";
}

// 4. Estado final
echo str_repeat("═", 70) . "\n";
echo "🏁 PROCESO COMPLETADO\n\n";
echo "📝 Próximos pasos:\n";
echo "   1. Espera 2-3 minutos para que el terminal sincronice\n";
echo "   2. Revisa que el terminal muestre el logo de MercadoPago\n";
echo "   3. Si está en \"algo salió mal\", presiona el botón atrás (←)\n";
echo "   4. Intenta enviar un nuevo pago: php enviar-pago.php 1000\n\n";
echo "💡 Si el terminal sigue bloqueado:\n";
echo "   - Cierra sesión en la app MercadoPago del terminal\n";
echo "   - Vuelve a iniciar sesión\n";
echo "   - Espera la sincronización automática (3-5 min)\n\n";
