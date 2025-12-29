<?php
/**
 * Insertar Empleados de Prueba
 */

require_once 'config.php';

echo "<h1>Insertar Empleados de Prueba</h1>";

try {
    $pdo = getDB();
    
    $empleados = [
        ['id' => 'EMP001', 'nombre' => 'Juan Pérez', 'rut' => '12345678-9', 'cargo' => 'Cajero'],
        ['id' => 'EMP002', 'nombre' => 'María González', 'rut' => '98765432-1', 'cargo' => 'Mesera'],
        ['id' => 'EMP003', 'nombre' => 'Pedro Silva', 'rut' => '11223344-5', 'cargo' => 'Chef']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO asistencias_employees (id, nombre, rut, cargo, face_indexed, face_id)
        VALUES (?, ?, ?, ?, 1, ?)
    ");
    
    foreach ($empleados as $emp) {
        $faceId = 'FACE-' . strtoupper(substr(md5($emp['rut']), 0, 8));
        $stmt->execute([$emp['id'], $emp['nombre'], $emp['rut'], $emp['cargo'], $faceId]);
        echo "✅ Empleado creado: <b>{$emp['nombre']}</b> - {$emp['cargo']}<br>";
    }
    
    echo "<br><h2>Lista de Empleados:</h2>";
    $stmt = $pdo->query("SELECT * FROM asistencias_employees");
    $empleados = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>RUT</th><th>Cargo</th><th>Face ID</th></tr>";
    foreach ($empleados as $emp) {
        echo "<tr>";
        echo "<td>{$emp['id']}</td>";
        echo "<td>{$emp['nombre']}</td>";
        echo "<td>{$emp['rut']}</td>";
        echo "<td>{$emp['cargo']}</td>";
        echo "<td>{$emp['face_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><br><a href='test-session.php'>Probar con sesión nueva</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
