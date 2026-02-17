<?php
/**
 * Diagnóstico completo del sistema MercadoPago
 * Subir a: https://fagottoerp.cl/mercadopago/diagnostico-completo.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico MercadoPago - fagottoerp.cl</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .ok { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .section { margin: 20px 0; padding: 15px; background: #252526; border-left: 3px solid #007acc; }
        h2 { color: #569cd6; }
        pre { background: #1e1e1e; padding: 10px; overflow-x: auto; border: 1px solid #3c3c3c; }
    </style>
</head>
<body>
<h1>🔍 Diagnóstico Completo MercadoPago</h1>
<p><strong>Servidor:</strong> fagottoerp.cl</p>
<p><strong>Fecha:</strong> <?= date('Y-m-d H:i:s') ?></p>

<div class="section">
    <h2>1. Directorio de Trabajo</h2>
    <pre><?php
    echo "📂 __DIR__: " . __DIR__ . "\n";
    echo "📂 Absolute path: " . realpath(__DIR__) . "\n";
    ?></pre>
</div>

<div class="section">
    <h2>2. Archivos Críticos</h2>
    <pre><?php
    $archivos = [
        'config.php',
        '.env',
        'api-enviar-pago.php',
        'storage/logs',
        'maquinas-config.php'
    ];
    
    foreach ($archivos as $archivo) {
        $path = __DIR__ . '/' . $archivo;
        $existe = file_exists($path);
        $tipo = is_dir($path) ? 'DIR' : 'FILE';
        $permisos = $existe ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
        $size = $existe && is_file($path) ? filesize($path) . ' bytes' : 'N/A';
        
        $status = $existe ? '✅' : '❌';
        echo "$status $archivo ($tipo) - Permisos: $permisos - Size: $size\n";
        
        if (!$existe && $tipo === 'DIR') {
            // Intentar crear directorio
            echo "   ⚠️ Intentando crear directorio...\n";
            if (@mkdir($path, 0755, true)) {
                echo "   ✅ Directorio creado exitosamente\n";
            } else {
                echo "   ❌ No se pudo crear el directorio\n";
            }
        }
    }
    ?></pre>
</div>

<div class="section">
    <h2>3. Contenido del .env</h2>
    <pre><?php
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        echo "✅ Archivo .env existe\n\n";
        $content = file_get_contents($envPath);
        echo "📄 Contenido (primeras 500 caracteres):\n";
        echo htmlspecialchars(substr($content, 0, 500)) . "\n";
        echo "\n📊 Tamaño total: " . strlen($content) . " bytes";
    } else {
        echo "❌ Archivo .env NO EXISTE";
    }
    ?></pre>
</div>

<div class="section">
    <h2>4. Carga de config.php</h2>
    <pre><?php
    $configPath = __DIR__ . '/config.php';
    if (file_exists($configPath)) {
        echo "✅ config.php existe, intentando cargar...\n\n";
        try {
            require_once $configPath;
            echo "✅ config.php cargado exitosamente\n";
        } catch (Exception $e) {
            echo "❌ Error al cargar config.php: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ config.php NO EXISTE";
    }
    ?></pre>
</div>

<div class="section">
    <h2>5. Variables de Entorno</h2>
    <pre><?php
    $vars = [
        'MP_ACCESS_TOKEN_116',
        'MP_DEVICE_ID_116',
        'MP_ACCESS_TOKEN_58',
        'MP_DEVICE_ID_58',
        'MP_ACCESS_TOKEN',
        'MP_DEVICE_ID'
    ];
    
    foreach ($vars as $var) {
        $value = getenv($var);
        if ($value !== false && !empty($value)) {
            $masked = substr($value, 0, 20) . '...' . substr($value, -10);
            echo "✅ $var = $masked\n";
        } else {
            echo "❌ $var = NO EXISTE\n";
        }
    }
    ?></pre>
</div>

<div class="section">
    <h2>6. Simulación de Request POST</h2>
    <pre><?php
    echo "Simulando request como lo hace MercadoPagoController...\n\n";
    
    $testPayload = [
        'monto' => 200,
        'descripcion' => 'Test desde diagnóstico',
        'referencia' => 'TEST-' . time(),
        'productos' => json_encode([]),
        'app_id' => 116,
        'payment_type' => 'debit'
    ];
    
    echo "📤 Payload de prueba:\n";
    echo json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";
    
    // Simular llamada interna
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [];
    $inputMock = json_encode($testPayload);
    
    echo "🔍 Probando parseo JSON...\n";
    $parsed = json_decode($inputMock, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON válido\n";
        echo "✅ monto: " . ($parsed['monto'] ?? 'NO EXISTE') . "\n";
        echo "✅ app_id: " . ($parsed['app_id'] ?? 'NO EXISTE') . "\n";
    } else {
        echo "❌ Error al parsear JSON: " . json_last_error_msg() . "\n";
    }
    
    // Verificar credenciales para app_id 116
    echo "\n🔑 Verificando credenciales para app_id 116:\n";
    $appId = 116;
    $accessToken = getenv('MP_ACCESS_TOKEN_116');
    $deviceId = getenv('MP_DEVICE_ID_116');
    
    if (!empty($accessToken) && !empty($deviceId)) {
        echo "✅ Credenciales encontradas\n";
        echo "   Token: " . substr($accessToken, 0, 20) . "...\n";
        echo "   Device: $deviceId\n";
    } else {
        echo "❌ Credenciales faltantes\n";
        echo "   Token: " . ($accessToken ?: 'VACÍO') . "\n";
        echo "   Device: " . ($deviceId ?: 'VACÍO') . "\n";
    }
    ?></pre>
</div>

<div class="section">
    <h2>7. Permisos de Escritura</h2>
    <pre><?php
    $testLogPath = __DIR__ . '/storage/logs/test-' . date('Y-m-d') . '.log';
    echo "Intentando escribir en: $testLogPath\n\n";
    
    $testContent = "Test de escritura - " . date('Y-m-d H:i:s');
    $result = @file_put_contents($testLogPath, $testContent);
    
    if ($result !== false) {
        echo "✅ Escritura exitosa ($result bytes)\n";
        echo "✅ Archivo creado: $testLogPath\n";
        
        // Verificar lectura
        $read = file_get_contents($testLogPath);
        if ($read === $testContent) {
            echo "✅ Lectura verificada correctamente\n";
        } else {
            echo "⚠️ El contenido leído no coincide\n";
        }
        
        // Limpiar
        @unlink($testLogPath);
    } else {
        echo "❌ NO se pudo escribir el archivo\n";
        echo "❌ Posible problema de permisos\n";
    }
    ?></pre>
</div>

<div class="section">
    <h2>8. Test de Conectividad MercadoPago API</h2>
    <pre><?php
    echo "Probando conexión a api.mercadopago.com...\n\n";
    
    $testUrl = "https://api.mercadopago.com";
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        echo "❌ Error de cURL: $curlError\n";
    } else {
        echo "✅ Conexión exitosa\n";
        echo "📊 HTTP Code: $httpCode\n";
    }
    ?></pre>
</div>

<div class="section">
    <h2>9. Información del Servidor</h2>
    <pre><?php
    echo "PHP Version: " . phpversion() . "\n";
    echo "OS: " . PHP_OS . "\n";
    echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
    echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
    echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
    echo "\nExtensiones cURL: " . (extension_loaded('curl') ? '✅ Habilitada' : '❌ No disponible') . "\n";
    echo "Extensiones JSON: " . (extension_loaded('json') ? '✅ Habilitada' : '❌ No disponible') . "\n";
    ?></pre>
</div>

<div class="section">
    <h2>10. Últimos Logs (si existen)</h2>
    <pre><?php
    $logsDir = __DIR__ . '/storage/logs';
    if (is_dir($logsDir)) {
        $files = glob($logsDir . '/api-*.log');
        if (!empty($files)) {
            // Obtener el más reciente
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $latestLog = $files[0];
            echo "📄 Log más reciente: " . basename($latestLog) . "\n";
            echo "📅 Fecha: " . date('Y-m-d H:i:s', filemtime($latestLog)) . "\n\n";
            
            $content = file_get_contents($latestLog);
            $lines = explode("\n", $content);
            $lastLines = array_slice($lines, -50); // Últimas 50 líneas
            
            echo "📋 Últimas líneas:\n";
            echo htmlspecialchars(implode("\n", $lastLines));
        } else {
            echo "⚠️ No hay archivos de log todavía\n";
            echo "Esto significa que api-enviar-pago.php nunca se ha ejecutado o no puede escribir logs";
        }
    } else {
        echo "❌ Directorio de logs no existe: $logsDir";
    }
    ?></pre>
</div>

<hr>
<p style="color: #569cd6;"><strong>📝 Instrucciones:</strong></p>
<ol>
    <li>Sube este archivo a <code>fagottoerp.cl/mercadopago/diagnostico-completo.php</code></li>
    <li>Accede desde el navegador: <code>https://fagottoerp.cl/mercadopago/diagnostico-completo.php</code></li>
    <li>Comparte el resultado completo para identificar el problema</li>
</ol>
</body>
</html>
