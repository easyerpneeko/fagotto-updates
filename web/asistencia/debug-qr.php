<?php
/**
 * 🐛 DEBUG MODE - Generador de Links QR
 * 
 * Página de desarrollo para probar links de registro y check-in
 * sin necesidad de escanear QR codes con el celular
 * 
 * ⚠️ SOLO PARA DESARROLLO - ELIMINAR EN PRODUCCIÓN
 */

require_once 'config/database.php';

// Configuración
$baseUrl = 'http://localhost/web'; // Cambiar según tu entorno
// $baseUrl = 'https://fagotto.cl'; // Para producción

// Obtener todos los empleados
$stmt = $pdo->query("
    SELECT 
        e.*,
        rt.token as token_registro,
        rt.usado as token_usado,
        rt.usado_fecha
    FROM employees e
    LEFT JOIN registro_tokens rt ON rt.employee_id = e.id AND rt.tipo_uso = 'registro'
    ORDER BY e.nombre ASC
");
$empleados = $stmt->fetchAll();

// Función para generar nuevo token
function generarToken($pdo, $employeeId) {
    $token = bin2hex(random_bytes(32));
    
    $stmt = $pdo->prepare("
        INSERT INTO registro_tokens (token, employee_id, tipo_uso, fecha_creacion)
        VALUES (?, ?, 'registro', NOW())
    ");
    $stmt->execute([$token, $employeeId]);
    
    $stmt2 = $pdo->prepare("
        UPDATE employees 
        SET registro_token = ?
        WHERE id = ?
    ");
    $stmt2->execute([$token, $employeeId]);
    
    return $token;
}

// Manejar generación de nuevo token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_token'])) {
    $employeeId = $_POST['employee_id'];
    $nuevoToken = generarToken($pdo, $employeeId);
    header("Location: debug-qr.php?success=token_generado&emp=" . $employeeId);
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐛 Debug QR - Links de Registro y Check-in</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            min-height: 100vh;
            padding: 20px;
            color: #fff;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 3px solid #fca5a5;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .warning-box {
            background: rgba(254, 226, 226, 0.1);
            border: 2px solid #fca5a5;
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .warning-box i {
            font-size: 24px;
            color: #fef2f2;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(10px);
        }

        .stat-card h3 {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #60a5fa;
        }

        .empleados-grid {
            display: grid;
            gap: 20px;
        }

        .empleado-card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .empleado-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .empleado-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .empleado-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .empleado-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        .empleado-datos h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .empleado-datos p {
            font-size: 14px;
            opacity: 0.7;
        }

        .estado-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .estado-registrado {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .estado-pendiente {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .links-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .link-box {
            background: rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .link-box h3 {
            font-size: 14px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.9;
        }

        .link-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .link-input {
            flex: 1;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            justify-content: center;
        }

        .btn-copy {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-copy:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-open {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            width: 100%;
        }

        .btn-open:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-generate {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.4);
        }

        .token-info {
            background: rgba(254, 226, 226, 0.1);
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
        }

        .token-info.success {
            background: rgba(209, 250, 229, 0.1);
            border-color: #86efac;
        }

        .copied-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: none;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            animation: slideIn 0.3s ease;
            z-index: 9999;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .filter-bar {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        @media (max-width: 768px) {
            .links-container {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .link-input-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-bug"></i>
                Debug Mode - Generador de Links QR
            </h1>
            <p>Página de desarrollo para probar registro biométrico y check-in sin necesidad de escanear QR</p>
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>⚠️ SOLO PARA DESARROLLO</strong> - Esta página debe eliminarse en producción
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Total Empleados</h3>
                <div class="number"><?php echo count($empleados); ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-check-circle"></i> Registrados</h3>
                <div class="number"><?php echo count(array_filter($empleados, fn($e) => $e['face_indexed'])); ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-clock"></i> Pendientes</h3>
                <div class="number"><?php echo count(array_filter($empleados, fn($e) => !$e['face_indexed'])); ?></div>
            </div>
        </div>

        <div class="filter-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Buscar empleado por nombre, RUT o cargo...">
        </div>

        <div class="empleados-grid" id="empleadosGrid">
            <?php foreach ($empleados as $empleado): ?>
            <div class="empleado-card" data-search="<?php echo strtolower($empleado['nombre'] . ' ' . $empleado['rut'] . ' ' . $empleado['cargo']); ?>">
                <div class="empleado-header">
                    <div class="empleado-info">
                        <div class="empleado-avatar">
                            <?php echo strtoupper(substr($empleado['nombre'], 0, 2)); ?>
                        </div>
                        <div class="empleado-datos">
                            <h2><?php echo htmlspecialchars($empleado['nombre']); ?></h2>
                            <p>
                                <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($empleado['rut'] ?? 'Sin RUT'); ?> • 
                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($empleado['cargo'] ?? 'Sin cargo'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="estado-badge <?php echo $empleado['face_indexed'] ? 'estado-registrado' : 'estado-pendiente'; ?>">
                        <?php echo $empleado['face_indexed'] ? '✓ Registrado' : '⏳ Pendiente'; ?>
                    </div>
                </div>

                <div class="links-container">
                    <!-- Link de Registro -->
                    <div class="link-box">
                        <h3><i class="fas fa-user-plus"></i> Link de Registro</h3>
                        <?php if ($empleado['token_registro'] && !$empleado['token_usado']): ?>
                            <div class="link-input-group">
                                <input type="text" class="link-input" readonly 
                                    value="<?php echo $baseUrl; ?>/qrregister.php?token=<?php echo $empleado['token_registro']; ?>"
                                    id="registro-<?php echo $empleado['id']; ?>">
                                <button class="btn btn-copy" onclick="copyLink('registro-<?php echo $empleado['id']; ?>')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <a href="<?php echo $baseUrl; ?>/qrregister.php?token=<?php echo $empleado['token_registro']; ?>" 
                               target="_blank" class="btn btn-open">
                                <i class="fas fa-external-link-alt"></i> Abrir Link de Registro
                            </a>
                            <div class="token-info success">
                                <i class="fas fa-check-circle"></i> Token activo y listo para usar
                            </div>
                        <?php elseif ($empleado['token_usado']): ?>
                            <div class="token-info">
                                <i class="fas fa-times-circle"></i> Token usado el <?php echo date('d/m/Y H:i', strtotime($empleado['usado_fecha'])); ?>
                            </div>
                            <form method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="employee_id" value="<?php echo $empleado['id']; ?>">
                                <button type="submit" name="generar_token" class="btn btn-generate" style="width: 100%;">
                                    <i class="fas fa-redo"></i> Generar Nuevo Token
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="employee_id" value="<?php echo $empleado['id']; ?>">
                                <button type="submit" name="generar_token" class="btn btn-generate" style="width: 100%;">
                                    <i class="fas fa-plus-circle"></i> Generar Token
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Link de Check-in -->
                    <div class="link-box">
                        <h3><i class="fas fa-clock"></i> Link de Check-in</h3>
                        <div class="link-input-group">
                            <input type="text" class="link-input" readonly 
                                value="<?php echo $baseUrl; ?>/qrcheck.php?employee=<?php echo $empleado['id']; ?>"
                                id="checkin-<?php echo $empleado['id']; ?>">
                            <button class="btn btn-copy" onclick="copyLink('checkin-<?php echo $empleado['id']; ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <a href="<?php echo $baseUrl; ?>/qrcheck.php?employee=<?php echo $empleado['id']; ?>" 
                           target="_blank" class="btn btn-open">
                            <i class="fas fa-external-link-alt"></i> Abrir Link de Check-in
                        </a>
                        <?php if (!$empleado['face_indexed']): ?>
                        <div class="token-info">
                            <i class="fas fa-info-circle"></i> Requiere completar registro primero
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="copied-toast" id="copiedToast">
        <i class="fas fa-check-circle"></i>
        <span>Link copiado al portapapeles!</span>
    </div>

    <script>
        function copyLink(inputId) {
            const input = document.getElementById(inputId);
            input.select();
            input.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(input.value).then(() => {
                showToast();
            });
        }

        function showToast() {
            const toast = document.getElementById('copiedToast');
            toast.style.display = 'flex';
            
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.empleado-card');
            
            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
