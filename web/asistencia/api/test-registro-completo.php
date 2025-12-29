<?php
/**
 * Test completo de registro con datos reales
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

$response = ['steps' => []];

try {
    $pdo = getDB();
    $response['steps'][] = '✓ DB conectado';
    
    // Datos de prueba
    $rut = '99999999-9';
    $email = 'test-' . time() . '@test.com';
    $nombre = 'Test Usuario ' . time();
    
    $response['test_data'] = [
        'rut' => $rut,
        'email' => $email,
        'nombre' => $nombre
    ];
    
    // 1. Verificar si el RUT ya existe
    $response['steps'][] = 'Verificando RUT: ' . $rut;
    $stmt = $pdo->prepare("SELECT id, nombre FROM asistencias_employees WHERE rut = ?");
    $stmt->execute([$rut]);
    $existingEmp = $stmt->fetch();
    
    if ($existingEmp) {
        $response['steps'][] = '⚠ RUT ya existe: ' . json_encode($existingEmp);
        
        // Borrar para poder probar
        $response['steps'][] = 'Borrando empleado existente para test...';
        $stmt = $pdo->prepare("DELETE FROM asistencias_employees WHERE rut = ?");
        $stmt->execute([$rut]);
        $response['steps'][] = '✓ Empleado borrado';
    } else {
        $response['steps'][] = '✓ RUT no existe, se puede registrar';
    }
    
    // 2. Generar ID
    $response['steps'][] = 'Generando nuevo ID...';
    $stmt = $pdo->query("SELECT id FROM asistencias_employees ORDER BY id DESC LIMIT 1");
    $lastEmp = $stmt->fetch();
    
    if ($lastEmp) {
        $lastNum = intval(substr($lastEmp['id'], 3));
        $newId = 'EMP' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newId = 'EMP001';
    }
    $response['new_id'] = $newId;
    $response['steps'][] = '✓ Nuevo ID: ' . $newId;
    
    // 3. Insertar empleado
    $response['steps'][] = 'Insertando empleado en DB...';
    $stmt = $pdo->prepare("
        INSERT INTO asistencias_employees 
        (id, nombre, rut, cargo, email, face_indexed, face_id, foto_registro, active)
        VALUES (?, ?, ?, ?, ?, 1, ?, ?, 1)
    ");
    
    $faceId = 'FACE-' . $newId;
    $foto = 'data:image/jpeg;base64,test';
    
    $stmt->execute([
        $newId,
        $nombre,
        $rut,
        'Cajero Test',
        $email,
        $faceId,
        $foto
    ]);
    
    $response['steps'][] = '✓ Empleado insertado exitosamente';
    
    // 4. Verificar inserción
    $stmt = $pdo->prepare("SELECT * FROM asistencias_employees WHERE id = ?");
    $stmt->execute([$newId]);
    $inserted = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($inserted) {
        $response['steps'][] = '✓ Verificado en DB';
        $response['inserted_employee'] = [
            'id' => $inserted['id'],
            'nombre' => $inserted['nombre'],
            'rut' => $inserted['rut'],
            'email' => $inserted['email']
        ];
    }
    
    $response['success'] = true;
    $response['message'] = '✅ TEST COMPLETO EXITOSO - El registro funciona!';
    
} catch (PDOException $e) {
    $response['success'] = false;
    $response['error'] = 'Error DB: ' . $e->getMessage();
    $response['error_code'] = $e->getCode();
    $response['sql_state'] = $e->errorInfo[0] ?? 'N/A';
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
