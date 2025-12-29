<?php
/**
 * API: Actualizar configuración de local
 */
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$data = json_decode(file_get_contents('php://input'), true);

$appId = $data['app_id'] ?? null;
$latitud = $data['latitud'] ?? null;
$longitud = $data['longitud'] ?? null;
$radio = $data['radio'] ?? null;

if (!$appId) {
    echo json_encode(['success' => false, 'message' => 'APP ID requerido']);
    exit;
}

try {
    $pdo = getDB();
    
    // Construir query dinámicamente según los datos enviados
    $updates = [];
    $params = [];
    
    if ($latitud !== null) {
        $updates[] = "latitud = ?";
        $params[] = $latitud;
    }
    
    if ($longitud !== null) {
        $updates[] = "longitud = ?";
        $params[] = $longitud;
    }
    
    if ($radio !== null) {
        $updates[] = "radio_metros = ?";
        $params[] = $radio;
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No hay datos para actualizar']);
        exit;
    }
    
    $params[] = $appId;
    
    $sql = "UPDATE asistencias_locales SET " . implode(', ', $updates) . " WHERE app_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode([
        'success' => true,
        'message' => 'Local actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
