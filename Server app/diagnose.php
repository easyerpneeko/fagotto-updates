<?php
// Prueba simple de conexión a base de datos y query directa
echo "🔍 DIAGNÓSTICO DEL EXCEL EXPORT\n";
echo "================================\n\n";

// 1. Verificar configuración de base de datos
echo "1. 📊 Verificando configuración de base de datos...\n";

// Intentar conectar usando las credenciales típicas de XAMPP
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión a MySQL exitosa\n";
    
    // Listar bases de datos
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📂 Bases de datos disponibles:\n";
    foreach ($databases as $db) {
        echo "   - $db\n";
    }
    
    // Buscar base de datos que pueda contener las ventas
    $possibleDbs = array_filter($databases, function($db) {
        return stripos($db, 'fagotto') !== false || 
               stripos($db, 'erp') !== false || 
               stripos($db, 'pos') !== false ||
               stripos($db, 'venta') !== false;
    });
    
    if (!empty($possibleDbs)) {
        echo "\n🎯 Bases de datos candidatas:\n";
        foreach ($possibleDbs as $db) {
            echo "   - $db\n";
            
            // Conectar a esta base de datos
            $pdo_db = new PDO("mysql:host=$host;dbname=$db", $username, $password);
            $pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar si existe tabla 'sells'
            $stmt = $pdo_db->query("SHOW TABLES LIKE 'sells'");
            if ($stmt->rowCount() > 0) {
                echo "   ✅ Tabla 'sells' encontrada en $db\n";
                
                // Contar registros
                $stmt = $pdo_db->query("SELECT COUNT(*) as total FROM sells WHERE trash = 0");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "   📊 Total de ventas activas: {$count['total']}\n";
                
                // Mostrar algunas ventas como ejemplo
                if ($count['total'] > 0) {
                    $stmt = $pdo_db->query("SELECT id, total, created_at FROM sells WHERE trash = 0 ORDER BY created_at DESC LIMIT 3");
                    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "   📋 Últimas ventas:\n";
                    foreach ($samples as $sale) {
                        echo "      ID: {$sale['id']}, Total: \${$sale['total']}, Fecha: {$sale['created_at']}\n";
                    }
                }
            }
        }
    } else {
        echo "⚠️ No se encontraron bases de datos candidatas\n";
        echo "💡 Intenta conectar manualmente a la base de datos correcta\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "💡 Verifica que XAMPP esté ejecutándose y que MySQL esté activo\n";
}

echo "\n2. 📁 Verificando archivos del proyecto...\n";

// Verificar archivos clave
$files_to_check = [
    'app/Http/Controllers/Controllers_local/ReportsController.php',
    'app/Exports/CustomReportExport.php',
    'app/Helpers/PaymentMethodHelper.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file - existe\n";
    } else {
        echo "❌ $file - NO EXISTE\n";
    }
}

echo "\n3. 📊 Verificando logs de Laravel...\n";

$log_file = 'storage/logs/laravel.log';
if (file_exists($log_file)) {
    echo "✅ Log file encontrado\n";
    
    // Leer las últimas líneas del log
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $last_lines = array_slice($lines, -10);
    
    echo "📝 Últimas entradas del log:\n";
    foreach ($last_lines as $line) {
        echo "   $line\n";
    }
} else {
    echo "❌ Log file no encontrado\n";
}

echo "\n🏁 DIAGNÓSTICO COMPLETADO\n";
