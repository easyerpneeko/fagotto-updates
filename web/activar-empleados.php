<?php
/**
 * Activar empleados para reconocimiento facial
 */

require_once 'config.php';

echo "<h1>Activar Empleados para Reconocimiento Facial</h1>";

try {
    $pdo = getDB();
    
    // Obtener empleados sin face_id
    $stmt = $pdo->query("SELECT * FROM asistencias_employees WHERE face_indexed = 0 OR face_id IS NULL");
    $empleados = $stmt->fetchAll();
    
    if (empty($empleados)) {
        echo "✅ Todos los empleados ya están activados<br><br>";
        echo "<a href='ver-empleados.php'>Ver empleados</a>";
        exit;
    }
    
    // Actualizar cada empleado
    $stmt = $pdo->prepare("
        UPDATE asistencias_employees 
        SET face_indexed = 1, face_id = ?
        WHERE id = ?
    ");
    
    foreach ($empleados as $emp) {
        $faceId = 'FACE-' . strtoupper(substr(md5($emp['rut']), 0, 8));
        $stmt->execute([$faceId, $emp['id']]);
        echo "✅ Empleado activado: <b>{$emp['nombre']}</b> → Face ID: {$faceId}<br>";
    }
    
    echo "<br><h2>✅ ¡Listo! Empleados activados</h2>";
    echo "<br><a href='ver-empleados.php'>Ver empleados</a>";
    echo "<br><a href='test-session.php'>Probar QR</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
