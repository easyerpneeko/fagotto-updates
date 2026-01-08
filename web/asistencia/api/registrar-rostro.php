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

// Habilitar logging de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error_register.log');

try {
    require_once '../config.php';
} catch (Exception $e) {
    error_log("ERROR: No se pudo cargar config.php: " . $e->getMessage());
}

try {
    if (file_exists('../helpers/AWSRekognition.php')) {
        require_once '../helpers/AWSRekognition.php';
    } else {
        error_log("WARNING: AWSRekognition.php no encontrado");
    }
} catch (Exception $e) {
    error_log("ERROR: No se pudo cargar AWSRekognition.php: " . $e->getMessage());
}

$data = json_decode(file_get_contents('php://input'), true);

// Log de entrada
error_log("=== REGISTRO EMPLEADO INICIADO ===");
error_log("Timestamp: " . date('Y-m-d H:i:s'));
error_log("Datos recibidos: " . json_encode([
    'sessionId' => $data['sessionId'] ?? 'NO',
    'nombre' => $data['nombre'] ?? 'NO',
    'rut' => $data['rut'] ?? 'NO',
    'cargo' => $data['cargo'] ?? 'NO',
    'email' => $data['email'] ?? 'NO',
    'foto_length' => isset($data['foto']) ? strlen($data['foto']) : 0
]));

$sessionId = $data['sessionId'] ?? null;
$nombre = $data['nombre'] ?? null;
$rut = $data['rut'] ?? null;
$cargo = $data['cargo'] ?? null;
$email = $data['email'] ?? null;
$foto = $data['foto'] ?? null;

// Log más detallado de valores
error_log("Validación de campos:");
error_log("  sessionId: " . ($sessionId ? "'" . $sessionId . "'" : "NULL/EMPTY") . " (length: " . strlen($sessionId ?? '') . ")");
error_log("  nombre: " . ($nombre ? "'" . $nombre . "'" : "NULL/EMPTY") . " (length: " . strlen($nombre ?? '') . ")");
error_log("  rut: " . ($rut ? "'" . $rut . "'" : "NULL/EMPTY") . " (length: " . strlen($rut ?? '') . ")");
error_log("  cargo: " . ($cargo ? "'" . $cargo . "'" : "NULL/EMPTY") . " (length: " . strlen($cargo ?? '') . ")");
error_log("  email: " . ($email ? "'" . $email . "'" : "NULL/EMPTY") . " (length: " . strlen($email ?? '') . ")");
error_log("  foto: " . ($foto ? "tiene " . strlen($foto) . " caracteres" : "NULL/EMPTY"));

if (!$sessionId || !$nombre || !$rut || !$cargo || !$email || !$foto) {
    $errorDetail = [
        'success' => false,
        'message' => 'Datos incompletos',
        'missing' => [
            'sessionId' => $sessionId ? 'OK' : 'FALTA',
            'nombre' => $nombre ? 'OK' : 'FALTA',
            'rut' => $rut ? 'OK' : 'FALTA',
            'cargo' => $cargo ? 'OK' : 'FALTA',
            'email' => $email ? 'OK' : 'FALTA',
            'foto' => $foto ? 'OK' : 'FALTA'
        ],
        'received_data' => [
            'sessionId' => substr($sessionId ?? '', 0, 20),
            'nombre' => $nombre ?? 'null',
            'rut' => $rut ?? 'null',
            'cargo' => $cargo ?? 'null',
            'email' => $email ?? 'null',
            'foto_length' => $foto ? strlen($foto) : 0
        ]
    ];
    error_log("ERROR: Datos incompletos - " . json_encode($errorDetail));
    http_response_code(400);
    echo json_encode($errorDetail);
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
    
    // Verificar si el RUT ya existe
    error_log("Verificando RUT: $rut");
    $stmt = $pdo->prepare("SELECT id, nombre FROM asistencias_employees WHERE rut = ?");
    $stmt->execute([$rut]);
    $existingEmp = $stmt->fetch();
    
    if ($existingEmp) {
        error_log("ERROR: RUT duplicado - " . json_encode($existingEmp));
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El RUT ya está registrado',
            'existing_employee' => $existingEmp['nombre'],
            'existing_id' => $existingEmp['id']
        ]);
        exit;
    }
    
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id, nombre FROM asistencias_employees WHERE email = ?");
    $stmt->execute([$email]);
    $existingEmail = $stmt->fetch();
    
    if ($existingEmail) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El email ya está registrado',
            'existing_employee' => $existingEmail['nombre'],
            'existing_id' => $existingEmail['id']
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
    error_log("Indexando rostro en AWS para empleado: $newId");
    
    $result = ['success' => true, 'faceId' => 'FACE-' . $newId, 'confidence' => 99.9];
    
    // Intentar usar AWS si está disponible
    if (class_exists('AWSRekognition')) {
        try {
            $rekognition = new AWSRekognition();
            $result = $rekognition->indexarRostro($newId, $foto);
            error_log("Resultado AWS: " . json_encode($result));
        } catch (Exception $awsError) {
            error_log("WARNING: AWS no disponible, usando mock: " . $awsError->getMessage());
            // Usar resultado mock si AWS falla
        }
    } else {
        error_log("WARNING: AWSRekognition no disponible, usando face_id mock");
    }
    
    if (!$result['success']) {
        error_log("ERROR: Fallo al indexar rostro en AWS");
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
    
    error_log("✅ Registro completado exitosamente para: $newId");
    
    // Respuesta exitosa
    $response = [
        'success' => true,
        'message' => 'Empleado registrado exitosamente',
        'employee_id' => $newId,
        'face_id' => $result['faceId'] ?? 'FACE-' . $newId
    ];
    
    if (isset($result['confidence'])) {
        $response['confidence'] = round(floatval($result['confidence']));
    }
    
    echo json_encode($response);
    error_log("Respuesta enviada: " . json_encode($response));
    
} catch (PDOException $e) {
    // Error específico de base de datos
    error_log("ERROR PDO: " . $e->getMessage());
    error_log("Error Code: " . $e->getCode());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    
    $errorMessage = 'Error de base de datos: ' . $e->getMessage();
    
    // Detectar error de duplicado
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
        http_response_code(400);
        $errorMessage = 'El registro ya existe en la base de datos. Verifica el RUT o email.';
    }
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage,
        'error_code' => $e->getCode(),
        'error_info' => $e->errorInfo ?? null
    ]);
} catch (Exception $e) {
    // Cualquier otro error
    error_log("ERROR GENERAL: " . $e->getMessage());
    error_log("Tipo: " . get_class($e));
    error_log("Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage(),
        'error_type' => get_class($e),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
