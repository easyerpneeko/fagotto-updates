<?php
/**
 * Panel de Administración - Sistema de Asistencias
 * CON TABS SEPARADOS: Usuarios Registrados | Registros de Asistencia
 */
require_once 'config.php';

// Autenticación
session_start();
$password = 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['admin_logged'] = true;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-nuevo.php');
    exit;
}

if (!isset($_SESSION['admin_logged'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Admin Login</title>
        <style>
            body { font-family: system-ui; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-box { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 300px; }
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
    
    // Estadísticas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_employees WHERE active = 1");
    $totalEmpleados = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_records WHERE DATE(fecha_hora) = CURDATE()");
    $registrosHoy = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM asistencias_records WHERE YEARWEEK(fecha_hora) = YEARWEEK(NOW())");
    $registrosSemana = $stmt->fetch()['total'];
    
    // Obtener locales únicos de registros
    $stmt = $pdo->query("SELECT DISTINCT nombre FROM asistencias_locales ORDER BY nombre");
    $locales = $stmt->fetchAll();
    
    // Obtener empleados activos
    $stmt = $pdo->query("SELECT * FROM asistencias_employees WHERE active = 1 ORDER BY nombre");
    $empleados = $stmt->fetchAll();
    
    // FILTROS PARA ASISTENCIA
    $filtroNegocio = $_GET['negocio'] ?? '';
    $filtroEmployeeId = $_GET['employee_id'] ?? '';
    $filtroFechaDesde = $_GET['fecha_desde'] ?? date('Y-m-d', strtotime('-7 days'));
    $filtroFechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
    
    // Obtener registros de asistencia
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
    
    $sqlRegistros .= " ORDER BY r.fecha_hora DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sqlRegistros);
    $stmt->execute($params);
    $registros = $stmt->fetchAll();
    
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
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
        body { font-family: system-ui, -apple-system, sans-serif; background: #f1f5f9; color: #1e293b; }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .navbar h1 { font-size: 24px; }
        .navbar a { color: white; text-decoration: none; padding: 10px 20px; background: rgba(255,255,255,0.2); border-radius: 8px; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
        
        /* TABS */
        .tabs-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .tabs {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
        }
        .tab-btn {
            flex: 1;
            padding: 20px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s;
        }
        .tab-btn:hover { background: #f8fafc; color: #667eea; }
        .tab-btn.active { color: #667eea; border-bottom-color: #667eea; background: #f8fafc; }
        
        .tab-content { display: none; padding: 30px; }
        .tab-content.active { display: block; }
        
        /* FILTROS */
        .filtros {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            align-items: flex-end;
        }
        .filtro-group { display: flex; flex-direction: column; gap: 5px; }
        .filtro-group label { font-weight: 600; font-size: 13px; color: #475569; }
        .filtros select, .filtros input { padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; min-width: 180px; }
        
        .btn { padding: 10px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-secondary { background: #64748b; color: white; }
        .btn-secondary:hover { background: #475569; }
        
        /* TABLA */
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 15px; text-align: left; font-weight: 600; color: #475569; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #e2e8f0; }
        tr:hover { background: #f8fafc; }
        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge.success { background: #d1fae5; color: #059669; }
        .badge.danger { background: #fee2e2; color: #dc2626; }
        .badge.info { background: #dbeafe; color: #2563eb; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }

        /* FOTO REGISTRO */
        .foto-registro {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid #e2e8f0;
        }
        .foto-registro:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* MODAL FOTO */
        .foto-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            padding-top: 50px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.95);
            align-items: center;
            justify-content: center;
        }
        .foto-modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 80vh;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: zoom 0.3s;
        }
        @keyframes zoom {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .foto-modal-close {
            position: absolute;
            top: 30px;
            right: 50px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .foto-modal-close:hover,
        .foto-modal-close:focus {
            color: #bbb;
            transform: rotate(90deg);
        }
        .foto-modal-caption {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            text-align: center;
            color: white;
            padding: 20px 0;
            font-size: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-clipboard-check"></i> Sistema de Asistencias</h1>
        <a href="?logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>
    
    <div class="container">
        <!-- Estadísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?= $totalEmpleados ?></h3>
                    <p>Empleados Registrados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-info">
                    <h3><?= $registrosHoy ?></h3>
                    <p>Registros Hoy</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-calendar-week"></i></div>
                <div class="stat-info">
                    <h3><?= $registrosSemana ?></h3>
                    <p>Registros Esta Semana</p>
                </div>
            </div>
        </div>
        
        <!-- TABS -->
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('usuarios')">
                    <i class="fas fa-users"></i> Usuarios Registrados
                </button>
                <button class="tab-btn" onclick="switchTab('asistencia')">
                    <i class="fas fa-clipboard-list"></i> Registros de Asistencia
                </button>
            </div>
            
            <!-- TAB 1: USUARIOS REGISTRADOS -->
            <div id="tab-usuarios" class="tab-content active">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-users"></i> Usuarios Registrados (<?= count($empleados) ?>)</h2>
                
                <?php if (empty($empleados)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h3>No hay empleados registrados</h3>
                        <p>Los empleados se registran escaneando el QR de registro desde la app.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>RUT</th>
                                <th>Cargo</th>
                                <th>Email</th>
                                <th>Rostro</th>
                                <th>Registro</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleados as $emp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($emp['id']) ?></strong></td>
                                <td><?= htmlspecialchars($emp['nombre']) ?></td>
                                <td><?= htmlspecialchars($emp['rut']) ?></td>
                                <td><?= htmlspecialchars($emp['cargo']) ?></td>
                                <td><?= htmlspecialchars($emp['email']) ?></td>
                                <td>
                                    <?php if ($emp['face_indexed']): ?>
                                        <span class="badge success"><i class="fas fa-check"></i> Indexado</span>
                                    <?php else: ?>
                                        <span class="badge danger"><i class="fas fa-times"></i> No</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($emp['created_at'])) ?></td>
                                <td>
                                    <?php if ($emp['active']): ?>
                                        <span class="badge success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- TAB 2: REGISTROS DE ASISTENCIA -->
            <div id="tab-asistencia" class="tab-content">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-clipboard-list"></i> Registros de Asistencia</h2>
                
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
                            <option value="">Todos</option>
                            <?php foreach ($empleados as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= $filtroEmployeeId == $emp['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['nombre']) ?>
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
                    
                    <button class="btn btn-primary" onclick="aplicarFiltros()"><i class="fas fa-filter"></i> Filtrar</button>
                    <button class="btn btn-secondary" onclick="limpiarFiltros()"><i class="fas fa-eraser"></i> Limpiar</button>
                    <button class="btn btn-success" onclick="exportarExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                </div>
                
                <?php if (empty($registros)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard"></i>
                        <h3>No hay registros de asistencia</h3>
                        <p>Los registros aparecerán aquí cuando los empleados marquen entrada/salida.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Local</th>
                                <th>Empleado</th>
                                <th>Cargo</th>
                                <th>Tipo</th>
                                <th>Coincidencia</th>
                                <th>Foto</th>
                                <th>Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $reg): ?>
                            <tr>
                                <td><strong><?= date('d/m/Y H:i:s', strtotime($reg['fecha_hora'])) ?></strong></td>
                                <td><span class="badge info"><?= htmlspecialchars($reg['negocio_nombre']) ?></span></td>
                                <td><?= htmlspecialchars($reg['nombre']) ?></td>
                                <td><?= htmlspecialchars($reg['cargo']) ?></td>
                                <td><?= ucfirst($reg['tipo_marcacion']) ?></td>
                                <td><?= $reg['coincidencia_facial'] ?>%</td>
                                <td>
                                    <?php if ($reg['foto_capturada']): ?>
                                        <img 
                                            src="<?= htmlspecialchars($reg['foto_capturada']) ?>" 
                                            class="foto-registro" 
                                            onclick="abrirFotoModal('<?= htmlspecialchars($reg['foto_capturada']) ?>', '<?= htmlspecialchars($reg['nombre']) ?>')"
                                            title="Click para agrandar"
                                        />
                                    <?php else: ?>
                                        <span style="color: #999;">Sin foto</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reg['gps_lat'] && $reg['gps_lng']): ?>
                                        <?= number_format($reg['gps_lat'], 4) ?>, <?= number_format($reg['gps_lng'], 4) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para foto ampliada -->
    <div id="fotoModal" class="foto-modal">
        <span class="foto-modal-close" onclick="cerrarFotoModal()">&times;</span>
        <img id="fotoModalImg" class="foto-modal-content">
        <div id="fotoModalCaption" class="foto-modal-caption"></div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Mostrar el tab seleccionado
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        
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

        function abrirFotoModal(src, nombre) {
            const modal = document.getElementById('fotoModal');
            const img = document.getElementById('fotoModalImg');
            const caption = document.getElementById('fotoModalCaption');
            
            modal.style.display = 'flex';
            img.src = src;
            caption.textContent = nombre;
        }

        function cerrarFotoModal() {
            document.getElementById('fotoModal').style.display = 'none';
        }

        // Cerrar al hacer click fuera de la imagen
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('fotoModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        cerrarFotoModal();
                    }
                });
            }
        });
    </script>
</body>
</html>
