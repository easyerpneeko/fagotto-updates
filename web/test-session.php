<?php
/**
 * Test: Crear sesión de prueba
 */

require_once 'config.php';

echo "<h1>Test Conexión y Creación de Sesión</h1>";

try {
    $pdo = getDB();
    echo "✅ Conexión DB exitosa<br><br>";
    
    // Crear sesión de prueba
    $sessionId = 'TEST-' . time();
    $appId = 'AGU001';
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    $stmt = $pdo->prepare("
        INSERT INTO asistencias_sessions (session_id, app_id, expires_at, used)
        VALUES (?, ?, ?, 0)
    ");
    $stmt->execute([$sessionId, $appId, $expiresAt]);
    
    echo "✅ Sesión creada: <b>$sessionId</b><br>";
    echo "App ID: $appId<br>";
    echo "Expira: $expiresAt<br><br>";
    
    // Verificar sesión
    $stmt = $pdo->prepare("
        SELECT * FROM asistencias_sessions 
        WHERE session_id = ? 
        AND expires_at > NOW()
        AND used = 0
    ");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();
    
    if ($session) {
        echo "✅ Sesión encontrada y válida<br>";
        echo "<pre>" . print_r($session, true) . "</pre>";
        
        echo "<br><a href='qrcheck.php?session=$sessionId'>Probar QR Check</a>";
    } else {
        echo "❌ Sesión NO encontrada";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
