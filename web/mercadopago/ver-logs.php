<?php
// Ver últimas 100 líneas del log de hoy
$logFile = __DIR__ . '/storage/logs/api-' . date('Y-m-d') . '.log';

if (!file_exists($logFile)) {
    die("El archivo de log no existe: $logFile\n");
}

$lines = file($logFile);
$total = count($lines);
$start = max(0, $total - 100); // Últimas 100 líneas

echo "=== ÚLTIMAS 100 LÍNEAS DEL LOG ===\n\n";
for ($i = $start; $i < $total; $i++) {
    echo $lines[$i];
}
