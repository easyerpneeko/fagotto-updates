<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Conectar a la base de datos centralizada
    $db = new PDO('mysql:host=localhost;dbname=fagotto_central', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener todos los registros con información de última conexión
    $stmt = $db->query("
        SELECT 
            app_id,
            app_name,
            current_version,
            last_ping,
            system_info,
            TIMESTAMPDIFF(MINUTE, last_ping, NOW()) as minutes_since_ping,
            DATE_FORMAT(last_ping, '%d/%m/%Y %H:%i:%s') as last_ping_formatted,
            created_at,
            updated_at
        FROM app_version_tracking
        ORDER BY last_ping DESC
    ");
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener la versión más reciente
    $stmtLatest = $db->query("
        SELECT current_version, COUNT(*) as count
        FROM app_version_tracking
        GROUP BY current_version
        ORDER BY current_version DESC
        LIMIT 1
    ");
    $latestVersionRow = $stmtLatest->fetch(PDO::FETCH_ASSOC);
    $latestVersion = $latestVersionRow ? $latestVersionRow['current_version'] : '0.0.0';

    // Calcular estadísticas
    $total = count($data);
    $updated = 0;
    $outdated = 0;
    $offline = 0;

    foreach ($data as $row) {
        if ($row['minutes_since_ping'] > 60) {
            $offline++;
        } elseif ($row['current_version'] === $latestVersion) {
            $updated++;
        } else {
            $outdated++;
        }
    }

    // Parsear system_info
    foreach ($data as &$row) {
        if ($row['system_info']) {
            $sysInfo = json_decode($row['system_info'], true);
            if ($sysInfo) {
                $row['system_info'] = sprintf(
                    "%s %s",
                    ucfirst($sysInfo['platform'] ?? 'N/A'),
                    $sysInfo['arch'] ?? ''
                );
            }
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'latest_version' => $latestVersion,
        'stats' => [
            'total' => $total,
            'updated' => $updated,
            'outdated' => $outdated,
            'offline' => $offline
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos',
        'message' => $e->getMessage()
    ]);
}
