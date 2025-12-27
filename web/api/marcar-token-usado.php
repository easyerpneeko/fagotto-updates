<?php
/**
 * Marcar Token como Usado
 * Marca un token de registro como utilizado para evitar reutilización
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos del request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $token = $data['token'] ?? null;
    $employeeId = $data['employeeId'] ?? null;
    
    if (!$token || !$employeeId) {
        throw new Exception('Token y Employee ID son requeridos');
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Marcar token en tabla registro_tokens
    $stmt = $pdo->prepare("
        UPDATE registro_tokens 
        SET usado = 1,
            usado_fecha = NOW(),
            usado_ip = ?,
            usado_user_agent = ?
        WHERE token = ? AND employee_id = ?
    ");
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt->execute([$ip, $userAgent, $token, $employeeId]);
    
    // Marcar token_usado en tabla employees
    $stmt2 = $pdo->prepare("
        UPDATE employees 
        SET token_usado = 1,
            token_fecha_uso = NOW()
        WHERE id = ? AND registro_token = ?
    ");
    
    $stmt2->execute([$employeeId, $token]);
    
    // Confirmar transacción
    $pdo->commit();
    
    // Log del evento
    error_log(sprintf(
        "[REGISTRO] Token usado - Employee: %s, Token: %s, IP: %s, Fecha: %s",
        $employeeId,
        substr($token, 0, 10) . '...',
        $ip,
        date('Y-m-d H:i:s')
    ));
    
    echo json_encode([
        'success' => true,
        'message' => 'Token marcado como usado exitosamente',
        'data' => [
            'employeeId' => $employeeId,
            'fecha_uso' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("[REGISTRO ERROR] Marcar token usado: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al marcar token: ' . $e->getMessage()
    ]);
}
?>
