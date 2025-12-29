<?php
/**
 * Test simple de registro - Debug
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['test' => 'inicio', 'steps' => []];

try {
    $response['steps'][] = 'Iniciando test';
    
    // 1. Test de conexión a DB
    $response['steps'][] = 'Cargando config';
    require_once '../config.php';
    $response['steps'][] = 'Config cargado';
    
    $response['steps'][] = 'Conectando a DB';
    $pdo = getDB();
    $response['steps'][] = 'DB conectado';
    
    // 2. Verificar tabla
    $response['steps'][] = 'Verificando tabla asistencias_employees';
    $stmt = $pdo->query("SHOW TABLES LIKE 'asistencias_employees'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        $response['error'] = 'La tabla asistencias_employees no existe';
        echo json_encode($response);
        exit;
    }
    $response['steps'][] = 'Tabla existe';
    
    // 3. Ver estructura de la tabla
    $response['steps'][] = 'Obteniendo estructura';
    $stmt = $pdo->query("DESCRIBE asistencias_employees");
    $response['table_structure'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Contar empleados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_employees");
    $count = $stmt->fetch();
    $response['total_employees'] = $count['total'];
    
    // 5. Test de datos recibidos
    $data = json_decode(file_get_contents('php://input'), true);
    $response['received_data'] = [
        'sessionId' => isset($data['sessionId']) ? 'OK' : 'FALTA',
        'appId' => isset($data['appId']) ? 'OK' : 'FALTA',
        'nombre' => isset($data['nombre']) ? 'OK' : 'FALTA',
        'rut' => isset($data['rut']) ? 'OK' : 'FALTA',
        'cargo' => isset($data['cargo']) ? 'OK' : 'FALTA',
        'email' => isset($data['email']) ? 'OK' : 'FALTA',
        'foto' => isset($data['foto']) ? strlen($data['foto']) . ' bytes' : 'FALTA'
    ];
    
    $response['success'] = true;
    $response['message'] = 'Test completado exitosamente';
    
} catch (PDOException $e) {
    $response['success'] = false;
    $response['error'] = 'Error DB: ' . $e->getMessage();
    $response['error_code'] = $e->getCode();
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = 'Error: ' . $e->getMessage();
    $response['error_type'] = get_class($e);
}

echo json_encode($response, JSON_PRETTY_PRINT);
