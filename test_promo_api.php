<?php
// Script de prueba para verificar la conexión a realmdfka

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Conexión a DB Maestra realmdfka</h1>";

// Configuración de la base de datos
$host = '127.0.0.1';
$port = '3306';
$database = 'realmdfka';
$username = 'root'; // Ajusta según tu configuración
$password = ''; // Ajusta según tu configuración

echo "<h2>1. Intentando conectar a la base de datos...</h2>";
echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>Database:</strong> $database</p>";
echo "<p><strong>Username:</strong> $username</p>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p style='color: green;'>✅ <strong>Conexión exitosa a la base de datos!</strong></p>";
    
    // Test 1: Verificar si existen las tablas
    echo "<h2>2. Verificando si existen las tablas...</h2>";
    
    $tables = ['promo_pasta_types', 'promo_salsa_types'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<p style='color: green;'>✅ Tabla <strong>$table</strong> existe</p>";
            
            // Contar registros
            $count = $pdo->query("SELECT COUNT(*) as total FROM $table")->fetch();
            echo "<p style='margin-left: 20px;'>📊 Registros: {$count['total']}</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla <strong>$table</strong> NO existe</p>";
        }
    }
    
    // Test 2: Obtener pastas
    echo "<h2>3. Obteniendo datos de promo_pasta_types...</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM promo_pasta_types WHERE active = 1 ORDER BY order_display");
        $pastas = $stmt->fetchAll();
        
        echo "<pre>";
        print_r($pastas);
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al consultar pastas: " . $e->getMessage() . "</p>";
    }
    
    // Test 3: Obtener salsas
    echo "<h2>4. Obteniendo datos de promo_salsa_types...</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM promo_salsa_types WHERE active = 1 ORDER BY order_display");
        $salsas = $stmt->fetchAll();
        
        echo "<pre>";
        print_r($salsas);
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al consultar salsas: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Posibles soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verifica que MySQL esté corriendo</li>";
    echo "<li>Verifica el usuario y contraseña</li>";
    echo "<li>Verifica que la base de datos 'realmdfka' exista</li>";
    echo "<li>Ejecuta el SQL: crear_productos_maestros.sql</li>";
    echo "</ul>";
}
?>
