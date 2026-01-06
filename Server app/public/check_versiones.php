<?php
/**
 * Script simple para ver versiones de cada negocio
 * Ubicación: Server app/public/check_versiones.php
 * URL: https://posfagotto.cl/check_versiones.php
 */

// Ruta al bootstrap de Laravel para usar las configuraciones de DB
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

/**
 * Obtener la versión oficial del sistema (igual que el Controller)
 * Prioridad: 1) Base de datos, 2) GitHub, 3) Fallback
 */
function getOfficialVersion() {
    // 1️⃣ Intentar desde DB (system_config)
    try {
        $config = DB::table('system_config')
            ->where('config_key', 'app_official_version')
            ->first();
        
        if ($config && !empty($config->config_value)) {
            return [
                'version' => $config->config_value,
                'source' => '🗄️ Base de Datos'
            ];
        }
    } catch (Exception $e) {
        // DB no disponible, continuar
    }

    // 2️⃣ Intentar desde GitHub
    try {
        $url = 'https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest';
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Fagotto-Check-Script',
                    'Accept: application/vnd.github.v3+json'
                ],
                'timeout' => 5
            ]
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);
        
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['tag_name'])) {
                return [
                    'version' => str_replace('v', '', $data['tag_name']),
                    'source' => '🐙 GitHub API'
                ];
            }
        }
    } catch (Exception $e) {
        // GitHub no disponible, continuar
    }

    // 3️⃣ Fallback hardcoded
    return [
        'version' => '1.11.50',
        'source' => '⚠️ Fallback Hardcoded'
    ];
}

$versionData = getOfficialVersion();
$latestGithub = $versionData['version'];
$versionSource = $versionData['source'];

// Obtener datos de la DB usando Laravel
try {
    $negocios = DB::table('app_version_tracking')
        ->select(
            'app_id',
            'app_name',
            'current_version',
            'last_ping',
            DB::raw('TIMESTAMPDIFF(MINUTE, last_ping, NOW()) as minutos_offline')
        )
        ->orderBy('app_name', 'ASC')
        ->get();
    
} catch (Exception $e) {
    die("Error DB: " . $e->getMessage());
}

// Calcular estadísticas
$total = count($negocios);
$actualizados = 0;
$desactualizados = 0;
$offline = 0;

foreach ($negocios as $n) {
    if ($n->minutos_offline > 60) {
        $offline++;
    } elseif ($n->current_version === $latestGithub) {
        $actualizados++;
    } else {
        $desactualizados++;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Versiones - Fagotto ERP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace; 
            padding: 20px; 
            background: #1e1e1e; 
            color: #d4d4d4; 
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #4ec9b0; margin-bottom: 20px; font-size: 24px; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: #252526;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #4ec9b0;
        }
        .stat-box.warning { border-left-color: #dcdcaa; }
        .stat-box.error { border-left-color: #f48771; }
        .stat-box.offline { border-left-color: #858585; }
        
        .stat-label { color: #858585; font-size: 12px; margin-bottom: 5px; }
        .stat-value { font-size: 32px; font-weight: bold; }
        
        table { 
            border-collapse: collapse; 
            width: 100%; 
            background: #252526;
            border-radius: 5px;
            overflow: hidden;
        }
        th, td { 
            border: 1px solid #3e3e42; 
            padding: 12px 8px; 
            text-align: left; 
        }
        th { 
            background: #1e1e1e; 
            color: #4ec9b0; 
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        tr:hover { background: #2d2d30; }
        
        .ok { color: #4ec9b0; font-weight: bold; }
        .warning { color: #dcdcaa; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .offline { color: #858585; }
        
        .info-box {
            background: #252526;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            border-left: 4px solid #569cd6;
        }
        
        @media (max-width: 768px) {
            table { font-size: 12px; }
            th, td { padding: 8px 4px; }
        }
    </style>
</head>
<body>
<div class="container">

<h1>📊 Check Versiones - Fagotto ERP</h1>

<div class="stats">
    <div class="stat-box">
        <div class="stat-label">VERSIÓN OFICIAL</div>
        <div class="stat-value ok"><?= $latestGithub ?></div>
        <div style="font-size: 11px; color: #858585; margin-top: 5px;"><?= $versionSource ?></div>
    </div>
    <div class="stat-box">
        <div class="stat-label">TOTAL NEGOCIOS</div>
        <div class="stat-value"><?= $total ?></div>
    </div>
    <div class="stat-box">
        <div class="stat-label">ACTUALIZADOS</div>
        <div class="stat-value ok"><?= $actualizados ?></div>
    </div>
    <div class="stat-box warning">
        <div class="stat-label">DESACTUALIZADOS</div>
        <div class="stat-value warning"><?= $desactualizados ?></div>
    </div>
    <div class="stat-box offline">
        <div class="stat-label">OFFLINE (&gt;1h)</div>
        <div class="stat-value offline"><?= $offline ?></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Negocio</th>
            <th>GitHub</th>
            <th>Usando</th>
            <th>Estado</th>
            <th>Última Conexión</th>
            <th>Offline</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($negocios as $n): 
            $actualizado = ($n->current_version === $latestGithub);
            $isOffline = ($n->minutos_offline > 60);
            
            if ($isOffline) {
                $estado = '⚫ Offline';
                $estadoClass = 'offline';
            } elseif ($actualizado) {
                $estado = '✅ OK';
                $estadoClass = 'ok';
            } else {
                $estado = '⚠️ Desactualizado';
                $estadoClass = 'warning';
            }
        ?>
        <tr>
            <td><?= $n->app_id ?></td>
            <td><strong><?= htmlspecialchars($n->app_name) ?></strong></td>
            <td class="ok"><?= $latestGithub ?></td>
            <td class="<?= $actualizado ? 'ok' : 'warning' ?>"><?= $n->current_version ?></td>
            <td class="<?= $estadoClass ?>"><?= $estado ?></td>
            <td><?= date('d/m/Y H:i', strtotime($n->last_ping)) ?></td>
            <td class="<?= $isOffline ? 'offline' : '' ?>">
                <?= $n->minutos_offline ?> min
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="info-box">
    <strong>Leyenda:</strong><br>
    ✅ OK - Actualizado a la última versión<br>
    ⚠️ Desactualizado - Usando versión antigua pero conectado<br>
    ⚫ Offline - Más de 1 hora sin reportar<br>
    <br>
    <strong>Auto-refresh:</strong> Esta página se actualiza cada 30 segundos automáticamente<br>
    <strong>Última actualización:</strong> <?= date('d/m/Y H:i:s') ?>
</div>

</div>

<script>
// Auto-refresh cada 30 segundos
setTimeout(() => location.reload(), 30000);
</script>

</body>
</html>
