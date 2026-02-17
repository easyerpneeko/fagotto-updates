<?php
// Ver logs de Laravel del día de hoy

$possiblePaths = [
    '/var/www/html/storage/logs/laravel-' . date('Y-m-d') . '.log',
    '../storage/logs/laravel-' . date('Y-m-d') . '.log',
    '../../storage/logs/laravel-' . date('Y-m-d') . '.log',
    '../../../storage/logs/laravel-' . date('Y-m-d') . '.log',
    '../../../../storage/logs/laravel-' . date('Y-m-d') . '.log',
    '../../../../../storage/logs/laravel-' . date('Y-m-d') . '.log',
];

echo "=== BUSCANDO LOGS DE LARAVEL ===\n\n";

foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        echo "✅ ENCONTRADO: $path\n\n";
        echo "=== ÚLTIMAS 150 LÍNEAS ===\n\n";
        
        $lines = file($path);
        $total = count($lines);
        $start = max(0, $total - 150);
        
        for ($i = $start; $i < $total; $i++) {
            echo $lines[$i];
        }
        exit;
    } else {
        echo "❌ No existe: $path\n";
    }
}

echo "\n❌ No se encontró ningún archivo de log de Laravel";
