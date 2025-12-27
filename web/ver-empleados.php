<?php
/**
 * Ver Empleados Existentes
 */

require_once 'config.php';

echo "<h1>Empleados Registrados</h1>";

try {
    $pdo = getDB();
    
    $stmt = $pdo->query("SELECT * FROM asistencias_employees ORDER BY id");
    $empleados = $stmt->fetchAll();
    
    if (empty($empleados)) {
        echo "❌ No hay empleados registrados<br><br>";
        echo "<a href='insertar-empleados.php'>Insertar empleados de prueba</a>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th>ID</th><th>Nombre</th><th>RUT</th><th>Cargo</th><th>Face Indexed</th><th>Face ID</th>";
        echo "</tr>";
        
        foreach ($empleados as $emp) {
            echo "<tr>";
            echo "<td>{$emp['id']}</td>";
            echo "<td><b>{$emp['nombre']}</b></td>";
            echo "<td>{$emp['rut']}</td>";
            echo "<td>{$emp['cargo']}</td>";
            echo "<td>" . ($emp['face_indexed'] ? '✅' : '❌') . "</td>";
            echo "<td>{$emp['face_id']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><p><b>Total: " . count($empleados) . " empleados</b></p>";
        echo "<br><a href='test-session.php'>Crear nueva sesión y probar</a>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
