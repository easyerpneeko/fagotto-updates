<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, App-Key');

// Permitir OPTIONS para CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/app/Aplication.php';

try {
    // Obtener el serial desde el header App-Key (igual que merchise y arqueo)
    $headers = getallheaders();
    $serial = isset($headers['App-Key']) ? $headers['App-Key'] : null;
    
    if (!$serial) {
        http_response_code(401);
        echo json_encode(['error' => 'App-Key header no encontrado']);
        exit;
    }

    // Obtener datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $version = isset($data['version']) ? $data['version'] : null;
    $systemInfo = isset($data['system_info']) ? $data['system_info'] : null;
    
    if (!$version) {
        http_response_code(400);
        echo json_encode(['error' => 'Versión no proporcionada']);
        exit;
    }

    // Buscar la aplicación por serial (igual que AppSecurity middleware)
    $app = Aplication::where('serial', $serial)->first();
    
    if (!$app) {
        http_response_code(404);
        echo json_encode(['error' => 'Serial no encontrado']);
        exit;
    }

    // Conectar a la base de datos centralizada
    $db = new PDO('mysql:host=localhost;dbname=fagotto_central', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si ya existe un registro para este app_id
    $stmt = $db->prepare("SELECT id, current_version FROM app_version_tracking WHERE app_id = :app_id");
    $stmt->execute(['app_id' => $app->id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Actualizar registro existente
        $oldVersion = $existing['current_version'];
        
        $stmt = $db->prepare("
            UPDATE app_version_tracking 
            SET current_version = :version,
                app_name = :app_name,
                last_ping = NOW(),
                system_info = :system_info,
                updated_at = NOW()
            WHERE app_id = :app_id
        ");
        
        $stmt->execute([
            'app_id' => $app->id,
            'version' => $version,
            'app_name' => $app->name,
            'system_info' => $systemInfo
        ]);

        // Si cambió la versión, registrar en historial
        if ($oldVersion !== $version) {
            $stmtHistory = $db->prepare("
                INSERT INTO app_version_history (app_id, from_version, to_version, updated_at)
                VALUES (:app_id, :from_version, :to_version, NOW())
            ");
            $stmtHistory->execute([
                'app_id' => $app->id,
                'from_version' => $oldVersion,
                'to_version' => $version
            ]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Versión actualizada',
            'app_id' => $app->id,
            'app_name' => $app->name,
            'version' => $version,
            'updated' => $oldVersion !== $version
        ]);
    } else {
        // Insertar nuevo registro
        $stmt = $db->prepare("
            INSERT INTO app_version_tracking 
            (app_id, app_name, current_version, last_ping, system_info, created_at, updated_at)
            VALUES (:app_id, :app_name, :version, NOW(), :system_info, NOW(), NOW())
        ");
        
        $stmt->execute([
            'app_id' => $app->id,
            'app_name' => $app->name,
            'version' => $version,
            'system_info' => $systemInfo
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Primera versión registrada',
            'app_id' => $app->id,
            'app_name' => $app->name,
            'version' => $version
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor',
        'message' => $e->getMessage()
    ]);
}
