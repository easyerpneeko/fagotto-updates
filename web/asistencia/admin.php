<?php
/**
 * Panel de Administración - Sistema de Asistencias
 */
require_once 'config.php';

// Autenticación simple (cambiar en producción)
session_start();
$password = 'admin123'; // Cambiar esto

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['admin_logged'] = true;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['admin_logged'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .login-box {
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                width: 300px;
            }
            h2 { text-align: center; color: #667eea; margin-bottom: 30px; }
            input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px; }
            button { width: 100%; padding: 14px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; }
            button:hover { background: #5568d3; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🔐 Admin Login</h2>
            <form method="POST">
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Ingresar</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

try {
    $pdo = getDB();
    
    // Obtener estadísticas
    $stats = [];
    
    // Total empleados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_employees WHERE active = 1");
    $stats['empleados'] = $stmt->fetch()['total'];
    
    // Total registros hoy
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_records WHERE DATE(fecha_hora) = CURDATE()");
    $stats['hoy'] = $stmt->fetch()['total'];
    
    // Total registros esta semana
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_records WHERE YEARWEEK(fecha_hora) = YEARWEEK(NOW())");
    $stats['semana'] = $stmt->fetch()['total'];
    
    // Obtener locales
    $stmt = $pdo->query("SELECT * FROM asistencias_locales ORDER BY nombre");
    $locales = $stmt->fetchAll();
    
    // Obtener empleados para filtros
    $stmt = $pdo->query("SELECT id, nombre, cargo FROM asistencias_employees WHERE active = 1 ORDER BY nombre");
    $empleados = $stmt->fetchAll();
    
    // Procesar filtros
    $filtroNegocio = $_GET['negocio'] ?? '';
    $filtroEmployeeId = $_GET['employee_id'] ?? '';
    $filtroFechaDesde = $_GET['fecha_desde'] ?? date('Y-m-d', strtotime('-7 days'));
    $filtroFechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
    
    // Obtener registros con filtros
    $sqlRegistros = "
        SELECT r.*, e.nombre as empleado_nombre, e.cargo
        FROM asistencias_records r
        LEFT JOIN asistencias_employees e ON r.employee_id = e.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($filtroNegocio) {
        $sqlRegistros .= " AND r.negocio_nombre = ?";
        $params[] = $filtroNegocio;
    }
    
    if ($filtroEmployeeId) {
        $sqlRegistros .= " AND r.employee_id = ?";
        $params[] = $filtroEmployeeId;
    }
    
    if ($filtroFechaDesde) {
        $sqlRegistros .= " AND DATE(r.fecha_hora) >= ?";
        $params[] = $filtroFechaDesde;
    }
    
    if ($filtroFechaHasta) {
        $sqlRegistros .= " AND DATE(r.fecha_hora) <= ?";
        $params[] = $filtroFechaHasta;
    }
    
    $sqlRegistros .= " ORDER BY r.fecha_hora DESC LIMIT 200";
    
    $stmt = $pdo->prepare($sqlRegistros);
    $stmt->execute($params);
    $registros = $stmt->fetchAll();
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sistema de Asistencias</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .navbar h1 { font-size: 24px; }
        .navbar a { color: white; text-decoration: none; padding: 10px 20px; background: rgba(255, 255, 255, 0.2); border-radius: 8px; }
        .navbar a:hover { background: rgba(255, 255, 255, 0.3); }
        
        /* Tabs */
        .tabs {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .tabs-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
        }
        .tab-btn {
            padding: 18px 30px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s;
        }
        .tab-btn:hover { color: #667eea; background: #f8fafc; }
        .tab-btn.active { color: #667eea; border-bottom-color: #667eea; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Filtros */
        .filtros {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filtro-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filtro-group label {
            font-weight: 600;
            font-size: 13px;
            color: #475569;
        }
        .filtros select, .filtros input[type="date"] {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            min-width: 180px;
        }
        .btn-filtrar {
            padding: 10px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            height: 42px;
        }
        .btn-filtrar:hover { background: #5568d3; }
        .btn-export {
            padding: 10px 20px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 42px;
        }
        .btn-export:hover { background: #059669; }
        .btn-limpiar {
            padding: 10px 20px;
            background: #64748b;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            height: 42px;
        }
        .btn-limpiar:hover { background: #475569; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 40px 20px; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        .stat-icon.blue { background: #e0e7ff; color: #667eea; }
        .stat-icon.green { background: #d1fae5; color: #10b981; }
        .stat-icon.purple { background: #ede9fe; color: #8b5cf6; }
        .stat-info h3 { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
        .stat-info p { color: #64748b; font-size: 14px; }
        
        .section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .section h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8fafc;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:hover {
            background: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.success { background: #d1fae5; color: #065f46; }
        .badge.warning { background: #fef3c7; color: #92400e; }
        .badge.error { background: #fee2e2; color: #991b1b; }
        .badge.default { background: #e2e8f0; color: #475569; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .foto-mini {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        input[type="number"] {
            width: 100px;
            padding: 8px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-shield-alt"></i> Sistema de Asistencias</h1>
        <a href="?logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs">
        <div class="tabs-container">
            <button class="tab-btn active" onclick="switchTab('registros')">
                <i class="fas fa-clipboard-list"></i> Registro de Asistencias
            </button>
            <button class="tab-btn" onclick="switchTab('locales')">
                <i class="fas fa-map-marker-alt"></i> Configuración Locales
            </button>
        </div>
    </div>

    <div class="container">
        <!-- TAB: Registros -->
        <div id="tab-registros" class="tab-content active">
        
        <!-- Filtros -->
        <div class="filtros">
            <div class="filtro-group">
                <label><i class="fas fa-store"></i> Local</label>
                <select id="filtro_negocio">
                    <option value="">Todos los locales</option>
                    <?php foreach ($locales as $local): ?>
                    <option value="<?= htmlspecialchars($local['nombre']) ?>" <?= $filtroNegocio == $local['nombre'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($local['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filtro-group">
                <label><i class="fas fa-user"></i> Empleado</label>
                <select id="filtro_employee_id">
                    <option value="">Todos los empleados</option>
                    <?php foreach ($empleados as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= $filtroEmployeeId == $emp['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($emp['nombre']) ?> - <?= htmlspecialchars($emp['cargo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filtro-group">
                <label><i class="fas fa-calendar"></i> Desde</label>
                <input type="date" id="filtro_fecha_desde" value="<?= $filtroFechaDesde ?>">
            </div>
            
            <div class="filtro-group">
                <label><i class="fas fa-calendar"></i> Hasta</label>
                <input type="date" id="filtro_fecha_hasta" value="<?= $filtroFechaHasta ?>">
            </div>
            
            <button class="btn-filtrar" onclick="aplicarFiltros()">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            
            <button class="btn-limpiar" onclick="limpiarFiltros()">
                <i class="fas fa-times"></i> Limpiar
            </button>
            
            <a href="#" class="btn-export" onclick="exportarExcel(); return false;">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-clipboard-list"></i> Registros Filtrados (<?= count($registros) ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Empleado</th>
                        <th>Cargo</th>
                        <th>Local</th>
                        <th>Tipo Marcación</th>
                        <th>Fecha y Hora</th>
                        <th>Coincidencia</th>
                        <th>GPS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $reg): ?>
                    <tr>
                        <td>
                            <?php if ($reg['foto_capturada']): ?>
                            <img src="<?= htmlspecialchars($reg['foto_capturada']) ?>" class="foto-mini" alt="Foto">
                            <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size: 40px; color: #cbd5e1;"></i>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($reg['empleado_nombre'] ?? 'N/A') ?></strong></td>
                        <td><?= htmlspecialchars($reg['cargo'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge success"><?= htmlspecialchars($reg['local_nombre']) ?></span>
                        </td>
                        <td>
                            <?php
                            $tipos = [
                                'entrada' => ['icon' => '🟢', 'text' => 'Entrada', 'class' => 'success'],
                                'salida_colacion' => ['icon' => '🍽️', 'text' => 'Salida Colación', 'class' => 'warning'],
                                'regreso_colacion' => ['icon' => '🟢', 'text' => 'Regreso Colación', 'class' => 'success'],
                                'salida' => ['icon' => '🔴', 'text' => 'Salida', 'class' => 'error']
                            ];
                            $tipo = $tipos[$reg['tipo_marcacion']] ?? ['icon' => '❓', 'text' => 'Desconocido', 'class' => 'default'];
                            ?>
                            <span class="badge <?= $tipo['class'] ?>">
                                <?= $tipo['icon'] ?> <?= $tipo['text'] ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i:s', strtotime($reg['fecha_hora'])) ?></td>
                        <td>
                            <span class="badge <?= $reg['coincidencia_facial'] >= 90 ? 'success' : 'warning' ?>">
                                <?= $reg['coincidencia_facial'] ?>%
                            </span>
                        </td>
                        <td><?= number_format($reg['gps_lat'], 4) ?>, <?= number_format($reg['gps_lng'], 4) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
        
        <!-- TAB: Locales -->
        <div id="tab-locales" class="tab-content">
        <div class="section">
            <h2><i class="fas fa-map-marker-alt"></i> Configuración de Locales</h2>
            <p style="margin-bottom: 20px; color: #64748b;">
                <i class="fas fa-info-circle"></i> 
                Puedes obtener las coordenadas desde <a href="https://www.google.com/maps" target="_blank">Google Maps</a> 
                (clic derecho → copiar coordenadas)
            </p>
            <table>
                <thead>
                    <tr>
                        <th>APP ID</th>
                        <th>Nombre</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Radio GPS (metros)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locales as $local): ?>
                    <tr id="local-<?= $local['app_id'] ?>">
                        <td><strong><?= htmlspecialchars($local['app_id']) ?></strong></td>
                        <td><?= htmlspecialchars($local['nombre']) ?></td>
                        <td>
                            <input type="number" step="0.000001" value="<?= $local['latitud'] ?>" 
                                   id="lat-<?= $local['app_id'] ?>" style="width: 120px;">
                        </td>
                        <td>
                            <input type="number" step="0.000001" value="<?= $local['longitud'] ?>" 
                                   id="lng-<?= $local['app_id'] ?>" style="width: 120px;">
                        </td>
                        <td>
                            <input type="number" value="<?= $local['radio_metros'] ?>" 
                                   id="radio-<?= $local['app_id'] ?>">
                        </td>
                        <td>
                            <span class="badge success">
                                <i class="fas fa-check-circle"></i> Activo
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary" onclick="updateLocal('<?= $local['app_id'] ?>')">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>

    <script>
        function updateLocal(appId) {
            const lat = document.getElementById(`lat-${appId}`).value;
            const lng = document.getElementById(`lng-${appId}`).value;
            const radio = document.getElementById(`radio-${appId}`).value;
            
            if (confirm(`¿Guardar cambios para ${appId}?`)) {
                fetch('/api/update-local.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        app_id: appId, 
                        latitud: parseFloat(lat),
                        longitud: parseFloat(lng),
                        radio: parseInt(radio)
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Local actualizado correctamente');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => {
                    alert('❌ Error de conexión: ' + err.message);
                });
            }
        }
        
        // Funciones para tabs
        function switchTab(tabName) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar tab seleccionado
            document.getElementById(`tab-${tabName}`).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Funciones para filtros
        function aplicarFiltros() {
            const negocio = document.getElementById('filtro_negocio').value;
            const employeeId = document.getElementById('filtro_employee_id').value;
            const fechaDesde = document.getElementById('filtro_fecha_desde').value;
            const fechaHasta = document.getElementById('filtro_fecha_hasta').value;
            
            let url = '?';
            if (negocio) url += `negocio=${encodeURIComponent(negocio)}&`;
            if (employeeId) url += `employee_id=${employeeId}&`;
            if (fechaDesde) url += `fecha_desde=${fechaDesde}&`;
            if (fechaHasta) url += `fecha_hasta=${fechaHasta}`;
            
            window.location.href = url;
        }
        
        function limpiarFiltros() {
            window.location.href = '?';
        }
        
        function exportarExcel() {
            const negocio = document.getElementById('filtro_negocio').value;
            const employeeId = document.getElementById('filtro_employee_id').value;
            const fechaDesde = document.getElementById('filtro_fecha_desde').value;
            const fechaHasta = document.getElementById('filtro_fecha_hasta').value;
            
            let url = 'api/exportar-excel.php?';
            if (negocio) url += `negocio=${encodeURIComponent(negocio)}&`;
            if (employeeId) url += `employee_id=${employeeId}&`;
            if (fechaDesde) url += `fecha_desde=${fechaDesde}&`;
            if (fechaHasta) url += `fecha_hasta=${fechaHasta}`;
            
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
