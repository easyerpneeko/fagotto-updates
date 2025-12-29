<?php
/**
 * TEST DE CONEXIÓN - Probar que todo funciona
 */

require_once 'config.php';

echo "<h1>🧪 Test de Conexión</h1>";

// Test 1: Variables de entorno
echo "<h2>1. Variables .env</h2>";
echo "✅ DB_HOST: " . DB_HOST . "<br>";
echo "✅ DB_NAME: " . DB_NAME . "<br>";
echo "✅ DB_USER: " . DB_USER . "<br>";
echo "✅ APP_ID: " . APP_ID . "<br>";
echo "✅ LOCAL_NOMBRE: " . LOCAL_NOMBRE . "<br>";
echo "✅ AWS_ACCESS_KEY: " . substr(AWS_ACCESS_KEY, 0, 10) . "...<br>";

// Test 2: Conexión a base de datos
echo "<h2>2. Conexión Base de Datos</h2>";
try {
    $pdo = getDB();
    echo "✅ Conexión exitosa<br>";
    
    // Test 3: Contar tablas
    echo "<h2>3. Tablas Creadas</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tablas as $tabla) {
        echo "✅ Tabla: {$tabla}<br>";
    }
    
    // Test 4: Contar registros
    echo "<h2>4. Datos Insertados</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM locales");
    $count = $stmt->fetchColumn();
    echo "✅ Locales: {$count}<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    $count = $stmt->fetchColumn();
    echo "✅ Empleados: {$count}<br>";
    
    // Test 5: Listar locales
    echo "<h2>5. Locales Disponibles</h2>";
    $stmt = $pdo->query("SELECT * FROM locales");
    $locales = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>APP_ID</th><th>Nombre</th><th>GPS</th><th>Estado</th></tr>";
    foreach ($locales as $local) {
        echo "<tr>";
        echo "<td>{$local['app_id']}</td>";
        echo "<td>{$local['nombre']}</td>";
        echo "<td>{$local['latitud']}, {$local['longitud']}</td>";
        echo "<td>" . ($local['active'] ? '🟢 Activo' : '🔴 Inactivo') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 6: Listar empleados
    echo "<h2>6. Empleados Registrados</h2>";
    $stmt = $pdo->query("SELECT * FROM employees");
    $empleados = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>RUT</th><th>Cargo</th><th>Rostro</th></tr>";
    foreach ($empleados as $emp) {
        echo "<tr>";
        echo "<td>{$emp['id']}</td>";
        echo "<td>{$emp['nombre']}</td>";
        echo "<td>{$emp['rut']}</td>";
        echo "<td>{$emp['cargo']}</td>";
        echo "<td>" . ($emp['face_indexed'] ? '✅ Registrado' : '❌ Pendiente') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>✅ TODO FUNCIONA CORRECTAMENTE</h2>";
    echo "<p>Siguiente paso: Actualizar archivos PHP para usar nuevo config</p>";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
