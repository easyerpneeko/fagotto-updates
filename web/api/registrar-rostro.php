<?php
/**
 * API: Registrar Nuevo Empleado con Rostro en AWS Rekognition
 * 
 * POST /api/registrar-rostro.php
 * 
 * Body:
 * {
 *   "sessionId": "REG-...",
 *   "appId": "AGU001",
 *   "nombre": "Juan Pérez",
 *   "rut": "12345678-9",
 *   "cargo": "Cajero",
 *   "foto": "data:image/jpeg;base64,..."
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';
require_once '../helpers/AWSRekognition.php';

$data = json_decode(file_get_contents('php://input'), true);

$sessionId = $data['sessionId'] ?? null;
$appId = $data['appId'] ?? null;
$nombre = $data['nombre'] ?? null;
$rut = $data['rut'] ?? null;
$cargo = $data['cargo'] ?? null;
$email = $data['email'] ?? null;
$foto = $data['foto'] ?? null;

if (!$sessionId || !$appId || !$nombre || !$rut || !$cargo || !$email || !$foto) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos',
        'missing' => [
            'sessionId' => $sessionId ? 'OK' : 'FALTA',
            'appId' => $appId ? 'OK' : 'FALTA',
            'nombre' => $nombre ? 'OK' : 'FALTA',
            'rut' => $rut ? 'OK' : 'FALTA',
            'cargo' => $cargo ? 'OK' : 'FALTA',
            'email' => $email ? 'OK' : 'FALTA',
            'foto' => $foto ? 'OK' : 'FALTA'
        ]
    ]);
    exit;
}

try {
    $pdo = getDB();
    
    // Validar sesión de registro
    $stmt = $pdo->prepare("
        SELECT * FROM asistencias_sessions 
        WHERE session_id = ? 
        AND expires_at > NOW()
        AND used = 0
    ");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();
    
    if (!$session) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Sesión inválida o expirada'
        ]);
        exit;
    }
    
    // Generar ID de empleado
    $stmt = $pdo->query("SELECT id FROM asistencias_employees ORDER BY id DESC LIMIT 1");
    $lastEmp = $stmt->fetch();
    
    if ($lastEmp) {
        $lastNum = intval(substr($lastEmp['id'], 3));
        $newId = 'EMP' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newId = 'EMP001';
    }
    
    // Indexar rostro en AWS Rekognition
    $rekognition = new AWSRekognition();
    
    // Pasar la foto completa con base64 (el helper hace el decode)
    $result = $rekognition->indexarRostro($newId, $foto);
    
    if (!$result['success']) {
        http_response_code(400);
        echo json_encode($result);
        exit;
    }
    
    // Insertar empleado en base de datos
    $stmt = $pdo->prepare("
        INSERT INTO asistencias_employees 
        (id, nombre, rut, cargo, email, face_indexed, face_id, foto_registro, active)
        VALUES (?, ?, ?, ?, ?, 1, ?, ?, 1)
    ");
    
    $stmt->execute([
        $newId,
        $nombre,
        $rut,
        $cargo,
        $email,
        $result['faceId'],
        $foto
    ]);
    
    // Marcar sesión como usada
    $stmt = $pdo->prepare("UPDATE asistencias_sessions SET used = 1 WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Empleado registrado exitosamente',
        'employee_id' => $newId,
        'face_id' => $result['faceId'],
        'confidence' => round($result['confidence'])
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
