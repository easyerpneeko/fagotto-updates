<?php
/**
 * API: Obtener logs de registro en tiempo real
 * GET /api/get-logs.php?lines=50
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, must-revalidate');

$logFile = __DIR__ . '/../registro_empleados.log';
$lines = isset($_GET['lines']) ? intval($_GET['lines']) : 100;
$clear = isset($_GET['clear']) && $_GET['clear'] === 'true';

// Limpiar log si se solicita
if ($clear) {
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
        echo json_encode([
            'success' => true,
            'message' => 'Log limpiado',
            'logs' => []
        ]);
        exit;
    }
}

// Verificar si existe el archivo de log
if (!file_exists($logFile)) {
    echo json_encode([
        'success' => true,
        'message' => 'No hay logs disponibles aún',
        'logs' => [],
        'file_exists' => false
    ]);
    exit;
}

// Leer últimas líneas del log
$logContent = file($logFile);
$totalLines = count($logContent);
$lastLines = array_slice($logContent, -$lines);

// Parsear logs para facilitar la lectura
$parsedLogs = [];
foreach ($lastLines as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    // Intentar parsear formato: [timestamp] [level] message | data
    if (preg_match('/^\[(.*?)\] \[(.*?)\] (.*)$/', $line, $matches)) {
        $timestamp = $matches[1];
        $level = $matches[2];
        $rest = $matches[3];
        
        // Separar mensaje y data si existe
        $parts = explode(' | ', $rest, 2);
        $message = $parts[0];
        $data = isset($parts[1]) ? json_decode($parts[1], true) : null;
        
        $parsedLogs[] = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'data' => $data,
            'raw' => $line
        ];
    } else {
        // Línea que no sigue el formato, agregar como está
        $parsedLogs[] = [
            'timestamp' => null,
            'level' => 'RAW',
            'message' => $line,
            'data' => null,
            'raw' => $line
        ];
    }
}

echo json_encode([
    'success' => true,
    'logs' => $parsedLogs,
    'total_lines' => $totalLines,
    'showing_lines' => count($parsedLogs),
    'file_size' => filesize($logFile),
    'last_modified' => date('Y-m-d H:i:s', filemtime($logFile))
]);
