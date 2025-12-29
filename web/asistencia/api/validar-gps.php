<?php
/**
 * API: Validar GPS - Verifica que el empleado esté dentro de 30 metros del local
 */

// Habilitar reporting de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla
ini_set('log_errors', 1);

require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_log("=== VALIDAR GPS API ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $raw = file_get_contents('php://input');
        error_log("Raw input: " . $raw);
        
        $data = json_decode($raw, true);
        error_log("Data decoded: " . print_r($data, true));
        
        $sessionId = $data['sessionId'] ?? null;
        $userLat = $data['lat'] ?? null;
        $userLng = $data['lng'] ?? null;
        
        error_log("sessionId: " . ($sessionId ?? 'NULL'));
        error_log("userLat: " . ($userLat ?? 'NULL'));
        error_log("userLng: " . ($userLng ?? 'NULL'));
        
        if (!$sessionId || !$userLat || !$userLng) {
            error_log("❌ Parámetros incompletos");
            echo json_encode(['valid' => false, 'error' => 'Parámetros incompletos']);
            exit;
        }
        
        $pdo = getDB();
        error_log("✅ Conexión DB establecida");
        
        // Obtener coordenadas del local desde la sesión
        $stmt = $pdo->prepare("
            SELECT gps_lat, gps_lng, negocio_nombre
            FROM asistencias_sessions
            WHERE session_id = ?
        ");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();
        
        error_log("Session query result: " . print_r($session, true));
        
        if (!$session) {
            error_log("❌ Sesión no encontrada: " . $sessionId);
            echo json_encode(['valid' => false, 'error' => 'Sesión no encontrada']);
            exit;
        }
        
        $localLat = $session['gps_lat'];
        $localLng = $session['gps_lng'];
        
        error_log("Local coords: $localLat, $localLng");
        
        // Si no hay coordenadas del local, permitir acceso (para testing)
        if (!$localLat || !$localLng) {
            error_log("⚠️ Sesión sin GPS - Permitiendo acceso para testing");
            echo json_encode([
                'valid' => true,
                'distance' => 0,
                'mensaje' => 'GPS no configurado - modo desarrollo'
            ]);
            exit;
        }
        
        // Calcular distancia usando función Haversine
        $distancia = calcularDistanciaGPS($localLat, $localLng, $userLat, $userLng);
        
        // Radio máximo permitido: 30 metros
        $radioPermitido = 30;
        
        error_log("📍 Validación GPS:");
        error_log("   Local ({$session['negocio_nombre']}): $localLat, $localLng");
        error_log("   Usuario: $userLat, $userLng");
        error_log("   Distancia: {$distancia}m");
        error_log("   Radio permitido: {$radioPermitido}m");
        
        if ($distancia <= $radioPermitido) {
            error_log("✅ VALIDACIÓN EXITOSA");
            echo json_encode([
                'valid' => true,
                'distance' => round($distancia, 1),
                'mensaje' => "Dentro del rango ({$distancia}m)"
            ]);
        } else {
            error_log("❌ FUERA DE RANGO");
            echo json_encode([
                'valid' => false,
                'distance' => round($distancia, 1),
                'mensaje' => "Estás a {$distancia}m del local. Máximo permitido: {$radioPermitido}m"
            ]);
        }
        
    } catch (Exception $e) {
        error_log("❌ EXCEPTION: " . $e->getMessage());
        error_log("   Stack: " . $e->getTraceAsString());
        echo json_encode(['valid' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['valid' => false, 'error' => 'Método no permitido']);
}
