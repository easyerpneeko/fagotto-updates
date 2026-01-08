<?php
/**
 * Debug Request - Para ver exactamente qué datos está enviando el frontend
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Capturar todo el input
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

// Preparar respuesta de debug
$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'no-content-type',
    'raw_input_length' => strlen($rawInput),
    'raw_input_preview' => substr($rawInput, 0, 500),
    'json_decoded' => $jsonData,
    'json_error' => json_last_error_msg(),
    'post_data' => $_POST,
    'get_data' => $_GET,
    'headers' => getallheaders(),
    'validation' => [
        'sessionId' => isset($jsonData['sessionId']) ? [
            'exists' => true,
            'value' => $jsonData['sessionId'],
            'length' => strlen($jsonData['sessionId']),
            'type' => gettype($jsonData['sessionId'])
        ] : ['exists' => false],
        'nombre' => isset($jsonData['nombre']) ? [
            'exists' => true,
            'value' => $jsonData['nombre'],
            'length' => strlen($jsonData['nombre']),
            'type' => gettype($jsonData['nombre'])
        ] : ['exists' => false],
        'rut' => isset($jsonData['rut']) ? [
            'exists' => true,
            'value' => $jsonData['rut'],
            'length' => strlen($jsonData['rut']),
            'type' => gettype($jsonData['rut'])
        ] : ['exists' => false],
        'cargo' => isset($jsonData['cargo']) ? [
            'exists' => true,
            'value' => $jsonData['cargo'],
            'length' => strlen($jsonData['cargo']),
            'type' => gettype($jsonData['cargo'])
        ] : ['exists' => false],
        'email' => isset($jsonData['email']) ? [
            'exists' => true,
            'value' => $jsonData['email'],
            'length' => strlen($jsonData['email']),
            'type' => gettype($jsonData['email'])
        ] : ['exists' => false],
        'foto' => isset($jsonData['foto']) ? [
            'exists' => true,
            'value' => substr($jsonData['foto'], 0, 50) . '...',
            'length' => strlen($jsonData['foto']),
            'type' => gettype($jsonData['foto'])
        ] : ['exists' => false]
    ]
];

// Guardar en archivo de log también
$logFile = __DIR__ . '/../debug_requests.log';
file_put_contents($logFile, 
    "\n\n=== DEBUG REQUEST " . date('Y-m-d H:i:s') . " ===\n" . 
    json_encode($debug, JSON_PRETTY_PRINT) . "\n", 
    FILE_APPEND
);

echo json_encode($debug, JSON_PRETTY_PRINT);
