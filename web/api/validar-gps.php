<?php
/**
 * API: Validar GPS del empleado contra ubicación del local
 * POST /api/validar-gps.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

$input = json_decode(file_get_contents('php://input'), true);

$sessionId = $input['sessionId'] ?? null;
$lat = $input['lat'] ?? null;
$lng = $input['lng'] ?? null;

if (!$sessionId || !$lat || !$lng) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros incompletos']);
    exit;
}

try {
    $pdo = getDB();
    
    // Obtener APP_ID de la sesión
    $stmt = $pdo->prepare("SELECT app_id FROM asistencias_sessions WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        http_response_code(404);
        echo json_encode(['error' => 'Sesión no encontrada']);
        exit;
    }
    
    // Obtener coordenadas del local y su nombre
    $stmt = $pdo->prepare("
        SELECT app_id, nombre, latitud, longitud, radio_metros 
        FROM asistencias_locales 
        WHERE app_id = ?
    ");
    $stmt->execute([$session['app_id']]);
    $local = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$local) {
        http_response_code(404);
        echo json_encode(['error' => 'Local no configurado']);
        exit;
    }
    
    // Calcular distancia al local de la sesión (fórmula Haversine)
    $R = 6371000; // Radio de la Tierra en metros
    $dLat = deg2rad($local['latitud'] - $lat);
    $dLng = deg2rad($local['longitud'] - $lng);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat)) * cos(deg2rad($local['latitud'])) *
         sin($dLng/2) * sin($dLng/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $R * $c;
    
    // Validar si está dentro del radio permitido
    $valid = $distance <= $local['radio_metros'];
    
    // 🔍 DETECTAR LOCAL MÁS CERCANO (para diagnóstico)
    $stmt = $pdo->prepare("
        SELECT app_id, nombre, latitud, longitud, radio_metros
        FROM asistencias_locales
        WHERE latitud != 0 AND longitud != 0
    ");
    $stmt->execute();
    $todosLocales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $localMasCercano = null;
    $distanciaMenor = PHP_FLOAT_MAX;
    
    foreach ($todosLocales as $otroLocal) {
        $dLat2 = deg2rad($otroLocal['latitud'] - $lat);
        $dLng2 = deg2rad($otroLocal['longitud'] - $lng);
        
        $a2 = sin($dLat2/2) * sin($dLat2/2) +
              cos(deg2rad($lat)) * cos(deg2rad($otroLocal['latitud'])) *
              sin($dLng2/2) * sin($dLng2/2);
        
        $c2 = 2 * atan2(sqrt($a2), sqrt(1-$a2));
        $dist2 = $R * $c2;
        
        if ($dist2 < $distanciaMenor) {
            $distanciaMenor = $dist2;
            $localMasCercano = $otroLocal;
        }
    }
    
    // Respuesta con información detallada
    $response = [
        'valid' => $valid,
        'distance' => round($distance, 2),
        'max_distance' => $local['radio_metros'],
        'local_esperado' => [
            'app_id' => $local['app_id'],
            'nombre' => $local['nombre']
        ],
        'local_mas_cercano' => null
    ];
    
    // Si no está en el local correcto, informar cuál es el más cercano
    if (!$valid && $localMasCercano) {
        $dentroDeCercano = $distanciaMenor <= $localMasCercano['radio_metros'];
        $response['local_mas_cercano'] = [
            'app_id' => $localMasCercano['app_id'],
            'nombre' => $localMasCercano['nombre'],
            'distancia' => round($distanciaMenor, 2),
            'dentro_rango' => $dentroDeCercano
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al validar GPS: ' . $e->getMessage()
    ]);
}
