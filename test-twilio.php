<?php
/**
 * Archivo de prueba para verificar instalación de Twilio
 * Sube este archivo al servidor y accede a: https://posfagotto.cl/test-twilio.php
 */

echo "=== TEST TWILIO ===\n";
echo "PHP Version: " . phpversion() . "\n\n";

// Test 1: Verificar autoload de Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Autoload de Composer encontrado\n";
    require __DIR__ . '/vendor/autoload.php';
} else {
    echo "❌ Autoload de Composer NO encontrado\n";
    echo "Buscar en: " . __DIR__ . "/vendor/autoload.php\n";
    die();
}

// Test 2: Verificar clase Twilio
if (class_exists('Twilio\Rest\Client')) {
    echo "✅ Clase Twilio\Rest\Client existe\n\n";
    
    // Test 3: Intentar crear instancia
    try {
        $sid = 'AC6bc40244fd40581717f54f24b32c7f74';
        $token = '5d704d4eba111718bc4c501a858d948b';
        
        $client = new \Twilio\Rest\Client($sid, $token);
        echo "✅ Cliente Twilio creado correctamente\n";
        echo "Account SID: " . $sid . "\n\n";
        
        // Test 4: Verificar método messages
        if (method_exists($client, 'messages')) {
            echo "✅ Método messages existe\n";
        } else {
            echo "❌ Método messages NO existe\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error creando cliente Twilio:\n";
        echo $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
    
} else {
    echo "❌ Clase Twilio\Rest\Client NO existe\n";
    echo "Ejecuta: composer require twilio/sdk\n\n";
    
    // Verificar qué paquetes están instalados
    $composerLock = __DIR__ . '/composer.lock';
    if (file_exists($composerLock)) {
        echo "Buscando en composer.lock...\n";
        $lockContent = file_get_contents($composerLock);
        if (strpos($lockContent, 'twilio/sdk') !== false) {
            echo "✅ twilio/sdk está en composer.lock\n";
        } else {
            echo "❌ twilio/sdk NO está en composer.lock\n";
        }
    }
}

echo "\n=== FIN TEST ===\n";
