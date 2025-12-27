<?php
/**
 * API: Obtener lista de empleados para check-in
 * GET /api/empleados.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    $pdo = getDB();
    
    $stmt = $pdo->query("
        SELECT id, nombre, rut, cargo, face_indexed 
        FROM asistencias_employees 
        WHERE face_indexed = 1
        ORDER BY nombre ASC
    ");
    
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($empleados);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al cargar empleados: ' . $e->getMessage()
    ]);
}
