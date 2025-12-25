<?php
/**
 * Script para limpiar el cache de Laravel sin SSH
 * Sube este archivo a la raíz del servidor y accede desde el navegador
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Limpieza de Cache Laravel</h1>";
echo "<pre>";

// Cambiar al directorio del servidor
$serverPath = __DIR__ . '/Server app';
if (is_dir($serverPath)) {
    chdir($serverPath);
    echo "✅ Directorio cambiado a: " . getcwd() . "\n\n";
} else {
    echo "❌ No se encontró el directorio 'Server app'\n";
    echo "Directorio actual: " . __DIR__ . "\n";
    exit;
}

// Función para ejecutar comandos
function runCommand($command, $description) {
    echo "🔧 $description\n";
    echo "Ejecutando: $command\n";
    
    $output = [];
    $returnVar = 0;
    exec($command . ' 2>&1', $output, $returnVar);
    
    foreach ($output as $line) {
        echo "   $line\n";
    }
    
    if ($returnVar === 0) {
        echo "✅ Completado exitosamente\n\n";
    } else {
        echo "⚠️ Código de salida: $returnVar\n\n";
    }
    
    return $returnVar === 0;
}

// Limpiar archivos de cache manualmente
echo "🗑️ Limpiando archivos de cache manualmente...\n";

$cacheDirectories = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
];

foreach ($cacheDirectories as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "   ✅ Eliminado: $file\n";
        } else {
            echo "   ❌ No se pudo eliminar: $file\n";
        }
    }
}
echo "\n";

// Limpiar cache de storage
$storageCache = 'storage/framework/cache/data';
if (is_dir($storageCache)) {
    echo "🗑️ Limpiando cache en storage...\n";
    $files = glob($storageCache . '/*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    echo "   ✅ Eliminados $count archivos de cache\n\n";
}

// Ejecutar composer dump-autoload
runCommand('composer dump-autoload -o', 'Regenerando autoload de Composer');

// Ejecutar comandos artisan
runCommand('php artisan cache:clear', 'Limpiando cache de aplicación');
runCommand('php artisan config:clear', 'Limpiando cache de configuración');
runCommand('php artisan route:clear', 'Limpiando cache de rutas');
runCommand('php artisan view:clear', 'Limpiando cache de vistas');

// Cache de rutas y config (opcional, solo si estás en producción)
echo "📦 Regenerando cache optimizado...\n";
runCommand('php artisan config:cache', 'Cacheando configuración');
runCommand('php artisan route:cache', 'Cacheando rutas');

echo "\n";
echo "======================================\n";
echo "✅ PROCESO COMPLETADO\n";
echo "======================================\n";
echo "\n";
echo "Ahora prueba la URL: https://posfagotto.cl/api/cupones/validar\n";
echo "\n";
echo "⚠️ IMPORTANTE: Elimina este archivo después de usarlo por seguridad\n";

echo "</pre>";
?>
