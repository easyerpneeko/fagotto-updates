<?php
/**
 * API: Crear Sesión de Registro de Empleado
 * POST /api/create-registration-session.php
 */

require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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
        
        // Crear sesión de registro (tipo especial)
        $stmt = $pdo->prepare("
            INSERT INTO asistencias_sessions (session_id, app_id, expires_at, used)
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute(['REG-' . $sessionId, $appId, $expiresAt]);
        
        echo json_encode([
            'success' => true,
            'session_id' => 'REG-' . $sessionId,
            'app_id' => $appId
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
