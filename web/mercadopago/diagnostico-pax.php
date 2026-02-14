<?php
/**
 * DIAGNÓSTICO COMPLETO - PAX A910
 * Verifica todo el estado del terminal para resolver problemas de sincronización
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

echo "🔍 DIAGNÓSTICO COMPLETO DEL PAX A910\n";
echo str_repeat("═", 70) . "\n\n";
echo "🖥️  Terminal: $deviceId\n\n";

// 1. Estado del terminal en la API
echo "📡 1. ESTADO DEL TERMINAL EN API\n";
echo str_repeat("-", 70) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/terminals/v1/terminals?devices=$deviceId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $result = json_decode($response, true);
    if (isset($result['data']['terminals'][0])) {
        $terminal = $result['data']['terminals'][0];
        echo "✅ Terminal encontrado\n";
        echo "   ID: " . $terminal['id'] . "\n";
        echo "   Modo: " . $terminal['operating_mode'] . "\n";
        echo "   Store: " . $terminal['store_id'] . "\n";
        echo "   POS: " . $terminal['pos_id'] . "\n\n";
    }
} else {
    echo "⚠️  No se pudo obtener info del terminal (HTTP $httpCode)\n\n";
}

// 2. Verificar órdenes pendientes
echo "📡 2. ÓRDENES PENDIENTES QUE PUEDEN BLOQUEAR\n";
echo str_repeat("-", 70) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/orders");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode == 200) {
    $orders = json_decode($response, true);
    $pendingCount = 0;
    
    if (isset($orders['results'])) {
        foreach ($orders['results'] as $order) {
            if (in_array($order['status'], ['created', 'at_terminal', 'processing'])) {
                $pendingCount++;
                echo "⚠️  Orden pendiente: " . $order['id'] . "\n";
                echo "   Estado: " . $order['status'] . "\n";
                echo "   Monto: $" . $order['total_amount'] . " CLP\n";
                echo "   Creada: " . $order['date_created'] . "\n\n";
            }
        }
    }
    
    if ($pendingCount == 0) {
        echo "✅ No hay órdenes pendientes\n\n";
    } else {
        echo "💡 HAY $pendingCount ORDEN(ES) BLOQUEANDO EL TERMINAL\n";
        echo "   SOLUCIÓN: Cancela físicamente en el terminal (botón rojo)\n\n";
    }
} else {
    echo "⚠️  No se pudo verificar órdenes (HTTP $httpCode)\n\n";
}

// 3. Info del POS
echo "📡 3. INFORMACIÓN DEL POS\n";
echo str_repeat("-", 70) . "\n";

// Primero obtener el POS ID del terminal
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/terminals/v1/terminals?devices=$deviceId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken"
]);

$response = curl_exec($ch);
$result = json_decode($response, true);
$posId = null;

if (isset($result['data']['terminals'][0]['pos_id'])) {
    $posId = $result['data']['terminals'][0]['pos_id'];
}

if ($posId) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/pos/$posId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $pos = json_decode($response, true);
        echo "✅ POS ID: " . $pos['id'] . "\n";
        echo "   Nombre: " . $pos['name'] . "\n";
        echo "   Estado: " . $pos['status'] . "\n";
        echo "   Store: " . $pos['store_id'] . "\n";
        echo "   Última actualización: " . $pos['date_last_updated'] . "\n\n";
    }
} else {
    echo "⚠️  No se pudo obtener POS ID\n\n";
}

// 4. Resumen y soluciones
echo str_repeat("═", 70) . "\n";
echo "🏁 SOLUCIONES PARA PAX A910 QUE NO ACTUALIZA\n";
echo str_repeat("═", 70) . "\n\n";

echo "🔧 PASO 1: LIMPIAR SESIÓN DEL TERMINAL\n";
echo "   1. En el PAX A910, abre la app MercadoPago\n";
echo "   2. Ve a Settings/Configuración\n";
echo "   3. Cierra sesión (Log out)\n";
echo "   4. Espera 30 segundos\n\n";

echo "🔧 PASO 2: VOLVER A INICIAR SESIÓN\n";
echo "   1. Abre MercadoPago en el terminal\n";
echo "   2. Inicia sesión con las credenciales de la cuenta\n";
echo "   3. Espera que sincronice (puede tardar 3-5 minutos)\n";
echo "   4. Verifica que aparezca el logo/pantalla de MercadoPago\n\n";

echo "🔧 PASO 3: FORZAR ACTUALIZACIÓN POR API\n";
echo "   Ejecuta: php sincronizar-terminal.php\n";
echo "   Esto envía señal de actualización desde nuestro servidor\n\n";

echo "🔧 PASO 4: SI NADA FUNCIONA - RESET COMPLETO\n";
echo "   1. Apaga el terminal completamente (botón de encendido)\n";
echo "   2. Espera 30 segundos\n";
echo "   3. Enciéndelo de nuevo\n";
echo "   4. Vuelve a iniciar sesión en MercadoPago\n\n";

echo "⚠️  PROBLEMA CONOCIDO CON PAX A910:\n";
echo "   - Son terminales más viejos que pueden tener problemas de sync\n";
echo "   - A veces demoran más en recibir actualizaciones (hasta 10 min)\n";
echo "   - Si persiste, puede ser problema de conectividad del terminal\n\n";

echo "💡 DESPUÉS DE HACER LOS PASOS:\n";
echo "   php enviar-pago.php 500\n\n";

echo str_repeat("═", 70) . "\n";
