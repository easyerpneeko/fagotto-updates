<?php
/**
 * Test: Verificar que la tabla asistencias_locales existe y tiene datos
 */
require_once 'config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST TABLA ASISTENCIAS_LOCALES ===\n\n";

try {
    $pdo = getDB();
    echo "✅ Conexión a DB establecida\n\n";
    
    // 1. Verificar si la tabla existe
    echo "1. ¿Existe la tabla?\n";
    $result = $pdo->query("SHOW TABLES LIKE 'asistencias_locales'");
    if ($result->rowCount() > 0) {
        echo "   ✅ Sí existe\n\n";
    } else {
        echo "   ❌ NO EXISTE\n";
        echo "   Crear con el SQL preparado\n";
        exit;
    }
    
    // 2. Contar registros
    echo "2. Cantidad de registros:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_locales");
    $count = $stmt->fetch()['total'];
    echo "   Total: $count\n\n";
    
    // 3. Ver registros con GPS
    echo "3. Locales con GPS configurado:\n";
    $stmt = $pdo->query("
        SELECT nombre, gps_lat, gps_lng, radio_metros
        FROM asistencias_locales
        WHERE gps_lat IS NOT NULL
        ORDER BY nombre
    ");
    $locales = $stmt->fetchAll();
    
    foreach ($locales as $local) {
        echo sprintf("   %-25s  Lat: %s  Lng: %s  Radio: %dm\n",
            $local['nombre'],
            $local['gps_lat'],
            $local['gps_lng'],
            $local['radio_metros'] ?? 30
        );
    }
    
    echo "\n4. Locales SIN GPS:\n";
    $stmt = $pdo->query("
        SELECT nombre
        FROM asistencias_locales
        WHERE gps_lat IS NULL
    ");
    $sinGPS = $stmt->fetchAll();
    
    if (count($sinGPS) > 0) {
        foreach ($sinGPS as $local) {
            echo "   ❌ " . $local['nombre'] . "\n";
        }
    } else {
        echo "   ✅ Todos tienen GPS\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
