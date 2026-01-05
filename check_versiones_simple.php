<?php
// Script simple para ver versiones de cada negocio vs última versión de GitHub

// Obtener última versión de GitHub
function getLatestGithubVersion() {
    $url = 'https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest';
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP',
                'Accept: application/vnd.github.v3+json'
            ]
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        return str_replace('v', '', $data['tag_name'] ?? 'N/A');
    }
    return 'N/A';
}

$latestGithub = getLatestGithubVersion();

// Conectar a DB
try {
    $db = new PDO('mysql:host=localhost;dbname=fagotto_central', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->query("
        SELECT 
            app_id,
            app_name,
            current_version,
            last_ping,
            TIMESTAMPDIFF(MINUTE, last_ping, NOW()) as minutos_offline
        FROM app_version_tracking
        ORDER BY app_name ASC
    ");
    
    $negocios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error DB: " . $e->getMessage());
}

// Mostrar tabla simple
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Check Versiones</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h1 { color: #4ec9b0; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #3e3e42; padding: 8px; text-align: left; }
        th { background: #252526; color: #4ec9b0; }
        tr:nth-child(even) { background: #252526; }
        .ok { color: #4ec9b0; }
        .warning { color: #dcdcaa; }
        .error { color: #f48771; }
        .offline { color: #858585; }
        pre { background: #252526; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>

<h1>📊 Check Versiones - Fagotto ERP</h1>

<pre>Última versión en GitHub: <span class="ok"><?= $latestGithub ?></span></pre>
<pre>Total negocios: <?= count($negocios) ?></pre>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Negocio</th>
            <th>Versión GitHub</th>
            <th>Versión que usa</th>
            <th>Estado</th>
            <th>Última conexión</th>
            <th>Offline</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($negocios as $n): 
            $actualizado = ($n['current_version'] === $latestGithub);
            $offline = ($n['minutos_offline'] > 60);
            
            $estado = $offline ? '⚫ Offline' : ($actualizado ? '✅ OK' : '⚠️ Desactualizado');
            $estadoClass = $offline ? 'offline' : ($actualizado ? 'ok' : 'warning');
        ?>
        <tr>
            <td><?= $n['app_id'] ?></td>
            <td><strong><?= htmlspecialchars($n['app_name']) ?></strong></td>
            <td class="ok"><?= $latestGithub ?></td>
            <td class="<?= $actualizado ? 'ok' : 'warning' ?>"><?= $n['current_version'] ?></td>
            <td class="<?= $estadoClass ?>"><?= $estado ?></td>
            <td><?= date('d/m/Y H:i', strtotime($n['last_ping'])) ?></td>
            <td><?= $n['minutos_offline'] ?> min</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<pre>
Leyenda:
  ✅ OK            - Actualizado a la última versión
  ⚠️ Desactualizado - Usando versión antigua
  ⚫ Offline       - Más de 1 hora sin reportar
</pre>

<script>
// Auto-refresh cada 30 segundos
setTimeout(() => location.reload(), 30000);
</script>

</body>
</html>
