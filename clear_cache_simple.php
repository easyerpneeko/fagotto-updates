<?php
/**
 * Script simple para limpiar cache - Solo elimina archivos
 * Úsalo si el otro script no funciona
 */

echo "<h1>Limpieza Simple de Cache</h1>";
echo "<pre>";

$baseDir = __DIR__ . '/Server app';

if (!is_dir($baseDir)) {
    $baseDir = __DIR__;
}

echo "Directorio base: $baseDir\n\n";

$filesToDelete = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes.php', 
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/compiled.php',
];

$deletedCount = 0;

foreach ($filesToDelete as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        if (@unlink($fullPath)) {
            echo "✅ Eliminado: $file\n";
            $deletedCount++;
        } else {
            echo "❌ Error al eliminar: $file\n";
        }
    } else {
        echo "⚠️ No existe: $file\n";
    }
}

// Limpiar storage/framework/cache
$cacheDirs = [
    'storage/framework/cache/data',
    'storage/framework/views',
];

foreach ($cacheDirs as $dir) {
    $fullDir = $baseDir . '/' . $dir;
    if (is_dir($fullDir)) {
        $files = glob($fullDir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                @unlink($file);
                $deletedCount++;
            }
        }
        echo "✅ Limpiado directorio: $dir\n";
    }
}

echo "\n======================================\n";
echo "Total archivos eliminados: $deletedCount\n";
echo "======================================\n\n";
echo "Ahora prueba tu aplicación\n";
echo "⚠️ Elimina este archivo después\n";
echo "</pre>";
?>
