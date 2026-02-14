<?php
/**
 * Diagnóstico completo del terminal
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$deviceId = MP_DEVICE_ID;

echo "\n🔍 DIAGNÓSTICO COMPLETO - TERMINAL PAX\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📋 CONFIGURACIÓN ACTUAL:\n";
echo "   Device ID: {$deviceId}\n";
echo "   Access Token: " . substr($accessToken, 0, 20) . "...\n\n";

// 1. Verificar terminal
echo "1️⃣ Verificando información del terminal...\n\n";
$url1 = "https://api.mercadopago.com/point/integration-api/devices/{$deviceId}";

$ch1 = curl_init($url1);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response1 = curl_exec($ch1);
$httpCode1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
curl_close($ch1);

echo "   HTTP Code: {$httpCode1}\n";

if ($httpCode1 === 200) {
    $data1 = json_decode($response1, true);
    echo "   ✅ Terminal encontrado\n";
    echo "   Store ID: " . ($data1['store_id'] ?? 'N/A') . "\n";
    echo "   POS ID: " . ($data1['pos_id'] ?? 'N/A') . "\n";
    echo "   Operating Mode: " . ($data1['operating_mode'] ?? 'N/A') . "\n\n";
    
    if (isset($data1['operating_mode']) && $data1['operating_mode'] !== 'PDV') {
        echo "   ⚠️ PROBLEMA: El terminal NO está en modo PDV\n";
        echo "   📝 Ejecuta: php cambiar-modo-pdv.php\n\n";
    }
} else {
    echo "   ❌ Error al obtener información del terminal\n";
    echo "   Respuesta: {$response1}\n\n";
}

// 2. Verificar si el terminal está online
echo "2️⃣ Verificando conectividad del terminal...\n\n";

$url2 = "https://api.mercadopago.com/point/integration-api/devices";

$ch2 = curl_init($url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "   HTTP Code: {$httpCode2}\n";

if ($httpCode2 === 200) {
    $data2 = json_decode($response2, true);
    
    if (isset($data2['devices'])) {
        $found = false;
        foreach ($data2['devices'] as $device) {
            if ($device['id'] === $deviceId) {
                $found = true;
                echo "   ✅ Terminal encontrado en la lista de dispositivos\n";
                echo "   Status: " . ($device['status'] ?? 'N/A') . "\n\n";
                break;
            }
        }
        
        if (!$found) {
            echo "   ⚠️ Terminal NO encontrado en la lista\n";
            echo "   Posibles causas:\n";
            echo "   - Terminal no vinculado a esta cuenta\n";
            echo "   - Device ID incorrecto\n\n";
        }
    }
}

// 3. Verificar órdenes pendientes
echo "3️⃣ Verificando órdenes pendientes...\n\n";

$url3 = "https://api.mercadopago.com/point/integration-api/devices/{$deviceId}/payment-intents";

$ch3 = curl_init($url3);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response3 = curl_exec($ch3);
$httpCode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
curl_close($ch3);

echo "   HTTP Code: {$httpCode3}\n";

if ($httpCode3 === 200) {
    $data3 = json_decode($response3, true);
    echo "   ✅ Consulta exitosa\n";
    
    if (isset($data3['payment_intents']) && count($data3['payment_intents']) > 0) {
        echo "   ⚠️ Hay " . count($data3['payment_intents']) . " payment intent(s) pendiente(s)\n\n";
    } else {
        echo "   ✅ No hay órdenes pendientes\n\n";
    }
} else {
    echo "   Respuesta: {$response3}\n\n";
}

// 4. Verificar permisos del token
echo "4️⃣ Verificando permisos del Access Token...\n\n";

$url4 = "https://api.mercadopago.com/users/me";

$ch4 = curl_init($url4);
curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch4, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response4 = curl_exec($ch4);
$httpCode4 = curl_getinfo($ch4, CURLINFO_HTTP_CODE);
curl_close($ch4);

echo "   HTTP Code: {$httpCode4}\n";

if ($httpCode4 === 200) {
    $data4 = json_decode($response4, true);
    echo "   ✅ Token válido\n";
    echo "   User ID: " . ($data4['id'] ?? 'N/A') . "\n";
    echo "   Email: " . ($data4['email'] ?? 'N/A') . "\n";
    echo "   Nickname: " . ($data4['nickname'] ?? 'N/A') . "\n\n";
} else {
    echo "   ❌ Token inválido o sin permisos\n";
    echo "   Respuesta: {$response4}\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "📝 RESUMEN:\n\n";
echo "Si todo está ✅ en la API pero el terminal muestra error:\n";
echo "1. El terminal físico NO está logueado en MercadoPago\n";
echo "2. El terminal perdió conexión a Internet\n";
echo "3. El terminal necesita reiniciarse\n\n";
echo "SOLUCIÓN:\n";
echo "- Ve al terminal PAX → Apps → MercadoPago\n";
echo "- Verifica que esté logueado con la cuenta correcta\n";
echo "- Si no, ingresa con las credenciales de la Cuenta 2\n\n";
