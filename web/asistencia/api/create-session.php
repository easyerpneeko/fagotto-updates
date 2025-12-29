<?php
/**
 * API: Crear Sesión de Check-in
 */

require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // DEBUG: Log de lo que recibe el servidor
    error_log('=== CREATE SESSION DEBUG ===');
    error_log('Raw input: ' . file_get_contents('php://input'));
    error_log('Decoded data: ' . print_r($data, true));
    
    $sessionId = $data['session_id'] ?? null;
    $negocioNombre = $data['negocio_nombre'] ?? null;
    $expiresAt = $data['expires_at'] ?? null;
    $gpsLat = $data['gps_lat'] ?? null;
    $gpsLng = $data['gps_lng'] ?? null;
    
    error_log('session_id: ' . ($sessionId ?? 'NULL'));
    error_log('negocio_nombre: ' . ($negocioNombre ?? 'NULL'));
    error_log('expires_at: ' . ($expiresAt ?? 'NULL'));
    error_log('gps_lat: ' . ($gpsLat ?? 'NULL'));
    error_log('gps_lng: ' . ($gpsLng ?? 'NULL'));
    
    if (!$sessionId || !$negocioNombre || !$expiresAt) {
        error_log('❌ Datos incompletos detectados');
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }
    
    try {
        $pdo = getDB();
        
        // Buscar coordenadas del local en la base de datos
        $stmtCoords = $pdo->prepare("
            SELECT gps_lat, gps_lng 
            FROM asistencias_locales 
            WHERE nombre = ?
        ");
        $stmtCoords->execute([$negocioNombre]);
        $local = $stmtCoords->fetch();
        
        // Si el local ya existe en BD, usar sus coordenadas
        if ($local && $local['gps_lat'] && $local['gps_lng']) {
            $gpsLat = $local['gps_lat'];
            $gpsLng = $local['gps_lng'];
            error_log("✅ Coordenadas encontradas en BD: $gpsLat, $gpsLng");
        } else {
            // Si no hay coordenadas en BD, usar las del parámetro (si las enviaron)
            $gpsLat = $gpsLat ?? null;
            $gpsLng = $gpsLng ?? null;
            error_log("⚠️ Local sin coordenadas en BD - usando parámetros o NULL");
        }
        
        // Insertar o actualizar el local
        if ($gpsLat && $gpsLng) {
            $stmtLocal = $pdo->prepare("
                INSERT INTO asistencias_locales (nombre, gps_lat, gps_lng, radio_metros, active)
                VALUES (?, ?, ?, 30, 1)
                ON DUPLICATE KEY UPDATE 
                    gps_lat = VALUES(gps_lat),
                    gps_lng = VALUES(gps_lng)
            ");
            $stmtLocal->execute([$negocioNombre, $gpsLat, $gpsLng]);
        } else {
            $stmtLocal = $pdo->prepare("
                INSERT INTO asistencias_locales (nombre, active)
                VALUES (?, 1)
                ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
            ");
            $stmtLocal->execute([$negocioNombre]);
        }
        
        // Crear la sesión CON coordenadas del local
        $stmt = $pdo->prepare("
            INSERT INTO asistencias_sessions (session_id, negocio_nombre, expires_at, gps_lat, gps_lng, used)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([$sessionId, $negocioNombre, $expiresAt, $gpsLat, $gpsLng]);
        
        echo json_encode([
            'success' => true,
            'session_id' => $sessionId,
            'negocio_nombre' => $negocioNombre,
            'gps_validacion' => $gpsLat && $gpsLng ? 'activa' : 'desactivada',
            'coordenadas' => $gpsLat && $gpsLng ? "$gpsLat, $gpsLng" : null
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
