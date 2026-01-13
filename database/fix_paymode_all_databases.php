<?php
/**
 * OPCIÓN 2: Script PHP para ejecutar fix en todas las bases de datos
 * Ejecutar desde Server app o directamente en el servidor
 * 
 * Uso: php fix_paymode_all_databases.php
 */

// Configuración de conexión (ajustar según tu servidor)
$host = 'localhost';
$username = 'root';
$password = 'tu_password_aqui';
$port = 3306;

// Conectar al servidor MySQL (sin seleccionar base de datos)
$conn = new mysqli($host, $username, $password, '', $port);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "🔌 Conectado al servidor MySQL\n\n";

// Obtener todas las bases de datos que tienen tabla 'requests' con columna 'paymode'
$query = "
    SELECT DISTINCT TABLE_SCHEMA 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'requests' 
        AND COLUMN_NAME = 'paymode'
        AND TABLE_SCHEMA NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')
    ORDER BY TABLE_SCHEMA
";

$result = $conn->query($query);

if (!$result) {
    die("❌ Error obteniendo bases de datos: " . $conn->error);
}

$databases = [];
while ($row = $result->fetch_assoc()) {
    $databases[] = $row['TABLE_SCHEMA'];
}

if (empty($databases)) {
    die("⚠️ No se encontraron bases de datos con tabla 'requests'\n");
}

echo "📊 Se encontraron " . count($databases) . " bases de datos:\n";
foreach ($databases as $db) {
    echo "   - $db\n";
}
echo "\n";

// Preguntar confirmación
echo "⚠️ ¿Deseas aplicar el fix en TODAS estas bases de datos? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim(strtolower($line)) != 'y') {
    echo "❌ Operación cancelada\n";
    exit(0);
}
fclose($handle);

echo "\n🚀 Iniciando fix...\n\n";

$success_count = 0;
$error_count = 0;

foreach ($databases as $db) {
    echo "🔧 Procesando: $db ... ";
    
    // Verificar tipo actual
    $check_query = "
        SELECT COLUMN_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '$db' 
            AND TABLE_NAME = 'requests' 
            AND COLUMN_NAME = 'paymode'
    ";
    
    $check_result = $conn->query($check_query);
    $current_type = '';
    if ($check_result && $row = $check_result->fetch_assoc()) {
        $current_type = $row['COLUMN_TYPE'];
    }
    
    // Aplicar ALTER TABLE
    $alter_query = "
        ALTER TABLE `$db`.`requests` 
        MODIFY COLUMN paymode ENUM('Efectivo', 'Transferencia', 'Tarjeta') 
        DEFAULT 'Efectivo'
    ";
    
    if ($conn->query($alter_query)) {
        echo "✅ OK (antes: $current_type)\n";
        $success_count++;
    } else {
        echo "❌ ERROR: " . $conn->error . "\n";
        $error_count++;
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 RESUMEN:\n";
echo "   ✅ Exitosos: $success_count\n";
echo "   ❌ Errores:  $error_count\n";
echo "   📊 Total:    " . count($databases) . "\n";
echo str_repeat("=", 60) . "\n\n";

// Verificación final
echo "🔍 VERIFICACIÓN FINAL:\n\n";
$verify_query = "
    SELECT 
        TABLE_SCHEMA AS base_datos,
        COLUMN_TYPE AS tipo_actual
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'requests' 
        AND COLUMN_NAME = 'paymode'
        AND TABLE_SCHEMA NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')
    ORDER BY TABLE_SCHEMA
";

$verify_result = $conn->query($verify_query);
while ($row = $verify_result->fetch_assoc()) {
    $icon = (strpos($row['tipo_actual'], 'Tarjeta') !== false) ? '✅' : '⚠️';
    echo "$icon {$row['base_datos']}: {$row['tipo_actual']}\n";
}

$conn->close();
echo "\n✨ Proceso completado\n";
?>
