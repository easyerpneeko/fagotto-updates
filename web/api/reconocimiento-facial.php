<?php
/**
 * API: Reconocimiento Facial para Check-in
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';
require_once '../helpers/AWSRekognition.php';

$data = json_decode(file_get_contents('php://input'), true);

$sessionId = $data['sessionId'] ?? null;
$employeeId = $data['employeeId'] ?? null;
$foto = $data['foto'] ?? null;
$ubicacion = $data['ubicacion'] ?? '';
$tipoMarcacion = $data['tipoMarcacion'] ?? 'entrada';

if (!$sessionId || !$employeeId || !$foto) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

try {
    $pdo = getDB();
    
    // Validar sesión
    $stmt = $pdo->prepare("
        SELECT * FROM asistencias_sessions 
        WHERE session_id = ? AND expires_at > NOW() AND used = 0
    ");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Sesión inválida o expirada'
        ]);
        exit;
    }
    
    // Obtener datos del empleado
    $stmt = $pdo->prepare("SELECT * FROM asistencias_employees WHERE id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Empleado no encontrado'
        ]);
        exit;
    }
    
    // Buscar rostro en AWS Rekognition
    $rekognition = new AWSRekognition();
    $result = $rekognition->buscarRostro($foto, 75); // 75% de similitud mínima
    
    if (!$result['success']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
        exit;
    }
    
    // Verificar que el rostro encontrado corresponda al empleado seleccionado
    if ($result['employeeId'] !== $employeeId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'El rostro no coincide con el empleado seleccionado'
        ]);
        exit;
    }
    
    // Registrar asistencia
    list($lat, $lng) = array_map('trim', explode(',', $ubicacion));
    
    $stmt = $pdo->prepare("
        INSERT INTO asistencias_records 
        (app_id, employee_id, fecha_hora, tipo_marcacion, coincidencia_facial, foto_capturada, gps_lat, gps_lng)
        VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $session['app_id'],
        $employeeId,
        $tipoMarcacion,
        round($result['similarity']),
        $foto,
        (float)$lat,
        (float)$lng
    ]);
    
    // Marcar sesión como usada
    $stmt = $pdo->prepare("UPDATE asistencias_sessions SET used = 1 WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'similarity' => round($result['similarity']),
        'confidence' => round($result['confidence'] ?? 90),
        'timestamp' => date('H:i:s'),
        'message' => '¡Entrada registrada exitosamente!'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
