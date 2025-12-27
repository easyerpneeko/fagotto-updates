<?php
/**
 * API: Crear Sesión de Check-in
 */

require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sessionId = $data['session_id'] ?? null;
    $appId = $data['app_id'] ?? null;
    $expiresAt = $data['expires_at'] ?? null;
    
    if (!$sessionId || !$appId || !$expiresAt) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }
    
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO asistencias_sessions (session_id, app_id, expires_at, used)
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute([$sessionId, $appId, $expiresAt]);
        
        echo json_encode([
            'success' => true,
            'session_id' => $sessionId,
            'app_id' => $appId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
