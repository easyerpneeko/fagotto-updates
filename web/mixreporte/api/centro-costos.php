<?php
/**
 * =============================================
 * API: CENTRO DE COSTOS
 * =============================================
 */

// Configuración de la base de datos CT
define('CT_DB_HOST', 'localhost');
define('CT_DB_NAME', 'ct');
define('CT_DB_USER', 'root');
define('CT_DB_PASS', 'Fagotto2025!');
define('CT_DB_CHARSET', 'utf8mb4');

// Configuración de uploads
define('UPLOAD_DIR', '/var/www/html/uploads/centro-costos/');
define('UPLOAD_URL', 'https://fagottoerp.cl/uploads/centro-costos/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Conexión a base de datos CT
 */
function getCTDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . CT_DB_HOST . ";dbname=" . CT_DB_NAME . ";charset=" . CT_DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $pdo = new PDO($dsn, CT_DB_USER, CT_DB_PASS, $options);
        } catch(PDOException $e) {
            jsonError('Error de conexión: ' . $e->getMessage());
        }
    }
    return $pdo;
}

function jsonSuccess($data = [], $message = 'OK') {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit();
}

// Obtener acción
$jsonData = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $_POST['action'] ?? ($jsonData['action'] ?? null);

if (!$action) {
    jsonError('Acción no especificada');
}

$db = getCTDB();

try {
    switch ($action) {
        
        // ========== CATEGORÍAS ==========
        case 'listar_categorias':
            $stmt = $db->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
            $categorias = $stmt->fetchAll();
            jsonSuccess($categorias);
            break;
            
        case 'crear_categoria':
            $data = $jsonData ?? json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['nombre'])) {
                jsonError('El nombre de la categoría es requerido');
            }
            
            $stmt = $db->prepare("
                INSERT INTO categorias (nombre, icono, color)
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['icono'] ?? '📋',
                $data['color'] ?? 'otros'
            ]);
            
            jsonSuccess(['id' => $db->lastInsertId()], 'Categoría creada exitosamente');
            break;
            
        case 'editar_categoria':
            $data = $jsonData ?? json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id']) || empty($data['nombre'])) {
                jsonError('Faltan datos requeridos');
            }
            
            $stmt = $db->prepare("
                UPDATE categorias SET nombre = ?, icono = ?, color = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['icono'] ?? '📋',
                $data['color'] ?? 'otros',
                $data['id']
            ]);
            
            jsonSuccess([], 'Categoría actualizada exitosamente');
            break;
            
        case 'eliminar_categoria':
            $data = $jsonData ?? json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? $_POST['id'] ?? null;
            
            if (!$id) {
                jsonError('ID no especificado');
            }
            
            // Verificar si tiene costos asociados
            $stmt = $db->prepare("SELECT COUNT(*) FROM centro_costos WHERE categoria = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                jsonError("No se puede eliminar. Hay {$count} registro(s) asociado(s)");
            }
            
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonSuccess([], 'Categoría eliminada exitosamente');
            break;
        
        // ========== PROVEEDORES ==========
        case 'listar_proveedores':
            $stmt = $db->query("SELECT * FROM proveedores WHERE activo = 1 ORDER BY nombre");
            $proveedores = $stmt->fetchAll();
            jsonSuccess($proveedores);
            break;
            
        case 'crear_proveedor':
            $data = $jsonData ?? json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['nombre'])) {
                jsonError('El nombre del proveedor es requerido');
            }
            
            $stmt = $db->prepare("
                INSERT INTO proveedores (nombre, rut, telefono, email, direccion)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['rut'] ?? null,
                $data['telefono'] ?? null,
                $data['email'] ?? null,
                $data['direccion'] ?? null
            ]);
            
            jsonSuccess(['id' => $db->lastInsertId()], 'Proveedor creado exitosamente');
            break;
            
        case 'editar_proveedor':
            $data = $jsonData ?? json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id']) || empty($data['nombre'])) {
                jsonError('Faltan datos requeridos');
            }
            
            $stmt = $db->prepare("
                UPDATE proveedores SET nombre = ?, rut = ?, telefono = ?, email = ?, direccion = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['rut'] ?? '',
                $data['telefono'] ?? '',
                $data['email'] ?? '',
                $data['direccion'] ?? '',
                $data['id']
            ]);
            
            jsonSuccess([], 'Proveedor actualizado exitosamente');
            break;
            
        case 'eliminar_proveedor':
            $data = $jsonData ?? json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? $_POST['id'] ?? null;
            
            if (!$id) {
                jsonError('ID no especificado');
            }
            
            // Verificar si tiene costos asociados
            $stmt = $db->prepare("SELECT COUNT(*) FROM centro_costos WHERE proveedor_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                // Desactivar en lugar de eliminar
                $stmt = $db->prepare("UPDATE proveedores SET activo = 0 WHERE id = ?");
                $stmt->execute([$id]);
                jsonSuccess([], 'Proveedor desactivado (tiene registros asociados)');
            } else {
                $stmt = $db->prepare("DELETE FROM proveedores WHERE id = ?");
                $stmt->execute([$id]);
                jsonSuccess([], 'Proveedor eliminado exitosamente');
            }
            break;
        
        // ========== COSTOS ==========
        case 'listar_costos':
            $categoria = $_GET['categoria'] ?? null;
            $fechaDesde = $_GET['fecha_desde'] ?? null;
            $fechaHasta = $_GET['fecha_hasta'] ?? null;
            $buscar = $_GET['buscar'] ?? null;
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 50);
            $offset = ($page - 1) * $limit;
            
            $sql = "
                SELECT 
                    c.*,
                    p.nombre as proveedor_nombre
                FROM centro_costos c
                LEFT JOIN proveedores p ON c.proveedor_id = p.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($categoria) {
                $sql .= " AND c.categoria = ?";
                $params[] = $categoria;
            }
            
            if ($fechaDesde) {
                $sql .= " AND c.fecha >= ?";
                $params[] = $fechaDesde;
            }
            
            if ($fechaHasta) {
                $sql .= " AND c.fecha <= ?";
                $params[] = $fechaHasta;
            }
            
            if ($buscar) {
                $sql .= " AND (c.descripcion LIKE ? OR p.nombre LIKE ? OR c.notas LIKE ?)";
                $searchTerm = "%{$buscar}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Contar total
            $sqlCount = preg_replace('/SELECT .* FROM/', 'SELECT COUNT(*) FROM', $sql);
            $stmtCount = $db->prepare($sqlCount);
            $stmtCount->execute($params);
            $total = intval($stmtCount->fetchColumn());
            
            $sql .= " ORDER BY c.fecha DESC, c.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $costos = $stmt->fetchAll();
            
            jsonSuccess([
                'costos' => $costos,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
            break;
            
        case 'crear_costo':
            // Validar datos requeridos
            if (empty($_POST['fecha']) || empty($_POST['categoria']) || 
                empty($_POST['descripcion']) || empty($_POST['monto'])) {
                jsonError('Faltan datos requeridos');
            }
            
            // Debug: Ver qué llega en $_FILES
            error_log('DEBUG $_FILES: ' . print_r($_FILES, true));
            error_log('DEBUG $_POST: ' . print_r($_POST, true));
            
            // Validar archivo
            if (!isset($_FILES['documento'])) {
                jsonError('No se recibió el archivo. $_FILES está vacío.');
            }
            
            if ($_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
                $error_message = 'Error al subir archivo. Código: ' . $_FILES['documento']['error'];
                switch ($_FILES['documento']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_message = 'El archivo excede el tamaño máximo permitido';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = 'El archivo se subió parcialmente';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_message = 'No se subió ningún archivo';
                        break;
                }
                jsonError($error_message);
            }
            
            // Obtener usuario_id de la sesión o usar 1 por defecto
            $usuario_id = $_POST['usuario_id'] ?? 1;
            
            $file = $_FILES['documento'];
            
            // Validar tamaño
            if ($file['size'] > MAX_FILE_SIZE) {
                jsonError('El archivo no debe superar 10MB');
            }
            
            // Validar tipo
            $tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $tipoArchivo = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($tipoArchivo, $tiposPermitidos)) {
                jsonError('Tipo de archivo no permitido. Solo PDF, JPG o PNG');
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
            $rutaArchivo = UPLOAD_DIR . $nombreArchivo;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $rutaArchivo)) {
                jsonError('Error al subir el archivo');
            }
            
            // Guardar en base de datos
            $stmt = $db->prepare("
                INSERT INTO centro_costos 
                (fecha, categoria, descripcion, monto, proveedor_id, notas, 
                 documento_nombre, documento_ruta, documento_tipo, documento_tamano, usuario_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['fecha'],
                $_POST['categoria'],
                $_POST['descripcion'],
                $_POST['monto'],
                !empty($_POST['proveedor']) ? $_POST['proveedor'] : null,
                $_POST['notas'] ?? null,
                $file['name'],
                $nombreArchivo,
                $tipoArchivo,
                $file['size'],
                $usuario_id
            ]);
            
            $id = $db->lastInsertId();
            
            // Obtener el registro completo
            $stmt = $db->prepare("
                SELECT 
                    c.*,
                    p.nombre as proveedor_nombre
                FROM centro_costos c
                LEFT JOIN proveedores p ON c.proveedor_id = p.id
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            $costo = $stmt->fetch();
            
            jsonSuccess($costo, 'Registro creado exitosamente');
            break;
            
        case 'eliminar_costo':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                jsonError('ID no especificado');
            }
            
            // Obtener información del documento
            $stmt = $db->prepare("SELECT documento_ruta FROM centro_costos WHERE id = ?");
            $stmt->execute([$id]);
            $costo = $stmt->fetch();
            
            if ($costo && $costo['documento_ruta']) {
                $rutaCompleta = UPLOAD_DIR . $costo['documento_ruta'];
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }
            
            // Eliminar registro
            $stmt = $db->prepare("DELETE FROM centro_costos WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonSuccess([], 'Registro eliminado exitosamente');
            break;
            
        case 'editar_costo':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                jsonError('ID no especificado');
            }
            
            // Validar datos básicos
            if (empty($_POST['fecha']) || empty($_POST['categoria']) || 
                empty($_POST['descripcion']) || empty($_POST['monto'])) {
                jsonError('Faltan datos requeridos');
            }
            
            // Si hay nuevo documento, procesarlo
            $documentoData = [];
            if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['documento'];
                
                // Validar tamaño
                if ($file['size'] > MAX_FILE_SIZE) {
                    jsonError('El archivo no debe superar 10MB');
                }
                
                // Validar tipo
                $tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $tipoArchivo = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($tipoArchivo, $tiposPermitidos)) {
                    jsonError('Tipo de archivo no permitido. Solo PDF, JPG o PNG');
                }
                
                // Eliminar documento anterior
                $stmt = $db->prepare("SELECT documento_ruta FROM centro_costos WHERE id = ?");
                $stmt->execute([$id]);
                $costoAnterior = $stmt->fetch();
                
                if ($costoAnterior && $costoAnterior['documento_ruta']) {
                    $rutaAnterior = UPLOAD_DIR . $costoAnterior['documento_ruta'];
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                
                // Subir nuevo documento
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;
                $rutaArchivo = UPLOAD_DIR . $nombreArchivo;
                
                if (!move_uploaded_file($file['tmp_name'], $rutaArchivo)) {
                    jsonError('Error al subir el archivo');
                }
                
                $documentoData = [
                    'documento_nombre' => $file['name'],
                    'documento_ruta' => $nombreArchivo,
                    'documento_tipo' => $tipoArchivo,
                    'documento_tamano' => $file['size']
                ];
            }
            
            // Actualizar registro
            $sql = "UPDATE centro_costos SET 
                fecha = ?, categoria = ?, descripcion = ?, monto = ?, 
                proveedor_id = ?, notas = ?";
            
            $params = [
                $_POST['fecha'],
                $_POST['categoria'],
                $_POST['descripcion'],
                $_POST['monto'],
                !empty($_POST['proveedor']) ? $_POST['proveedor'] : null,
                $_POST['notas'] ?? ''
            ];
            
            if (!empty($documentoData)) {
                $sql .= ", documento_nombre = ?, documento_ruta = ?, documento_tipo = ?, documento_tamano = ?";
                $params = array_merge($params, array_values($documentoData));
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            jsonSuccess(['id' => $id], 'Registro actualizado exitosamente');
            break;
            
        case 'estadisticas':
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            
            // Total de costos
            $stmt = $db->prepare("
                SELECT 
                    SUM(monto) as total,
                    COUNT(*) as registros,
                    COUNT(DISTINCT categoria) as categorias
                FROM centro_costos
                WHERE fecha BETWEEN ? AND ?
            ");
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $stats = $stmt->fetch();
            
            // Por categoría
            $stmt = $db->prepare("
                SELECT categoria, SUM(monto) as total, COUNT(*) as cantidad
                FROM centro_costos
                WHERE fecha BETWEEN ? AND ?
                GROUP BY categoria
            ");
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $porCategoria = $stmt->fetchAll();
            
            jsonSuccess([
                'total' => floatval($stats['total'] ?? 0),
                'registros' => intval($stats['registros'] ?? 0),
                'categorias' => intval($stats['categorias'] ?? 0),
                'por_categoria' => $porCategoria
            ]);
            break;
            
        case 'exportar_excel':
            $categoria = $_GET['categoria'] ?? null;
            $fechaDesde = $_GET['fecha_desde'] ?? null;
            $fechaHasta = $_GET['fecha_hasta'] ?? null;
            $buscar = $_GET['buscar'] ?? null;
            
            $sql = "
                SELECT 
                    c.fecha,
                    cat.nombre as categoria,
                    c.descripcion,
                    c.monto,
                    p.nombre as proveedor,
                    c.notas,
                    c.documento_nombre
                FROM centro_costos c
                LEFT JOIN proveedores p ON c.proveedor_id = p.id
                LEFT JOIN categorias cat ON c.categoria = cat.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($categoria) {
                $sql .= " AND c.categoria = ?";
                $params[] = $categoria;
            }
            
            if ($fechaDesde) {
                $sql .= " AND c.fecha >= ?";
                $params[] = $fechaDesde;
            }
            
            if ($fechaHasta) {
                $sql .= " AND c.fecha <= ?";
                $params[] = $fechaHasta;
            }
            
            if ($buscar) {
                $sql .= " AND (c.descripcion LIKE ? OR p.nombre LIKE ? OR c.notas LIKE ?)";
                $searchTerm = "%{$buscar}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY c.fecha DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $costos = $stmt->fetchAll();
            
            // Generar CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="centro_costos_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Cabeceras
            fputcsv($output, ['Fecha', 'Categoría', 'Descripción', 'Monto', 'Proveedor', 'Notas', 'Documento']);
            
            // Datos
            foreach ($costos as $costo) {
                fputcsv($output, [
                    $costo['fecha'],
                    $costo['categoria'] ?? '',
                    $costo['descripcion'],
                    number_format($costo['monto'], 0, ',', '.'),
                    $costo['proveedor'] ?? '',
                    $costo['notas'] ?? '',
                    $costo['documento_nombre'] ?? ''
                ]);
            }
            
            fclose($output);
            exit();
            break;
            
        case 'grafica_temporal':
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            
            // Obtener costos agrupados por fecha
            $stmt = $db->prepare("
                SELECT fecha, SUM(monto) as total
                FROM centro_costos
                WHERE fecha BETWEEN ? AND ?
                GROUP BY fecha
                ORDER BY fecha ASC
            ");
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $datos = $stmt->fetchAll();
            
            jsonSuccess($datos);
            break;
            
        case 'detalle_diario_facturacion':
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            
            // Obtener costos por día
            $stmt = $db->prepare("
                SELECT 
                    fecha,
                    SUM(monto) as total_compras,
                    COUNT(*) as registros
                FROM centro_costos
                WHERE fecha BETWEEN ? AND ?
                GROUP BY fecha
                ORDER BY fecha ASC
            ");
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $comprasPorDia = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener headers de autenticación
            $headers = getallheaders();
            $appKey = $headers['app-key'] ?? $_SERVER['HTTP_APP_KEY'] ?? '';
            $authorization = $headers['authorization'] ?? $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            
            // Obtener facturación día por día (hacer request por cada día)
            $facturacionPorDia = [];
            $fechaActual = new DateTime($fechaDesde);
            $fechaFin = new DateTime($fechaHasta);
            
            error_log("[DETALLE_DIARIO] Iniciando análisis desde {$fechaDesde} hasta {$fechaHasta}");
            
            while ($fechaActual <= $fechaFin) {
                $fechaStr = $fechaActual->format('Y-m-d');
                
                // Request para este día específico
                $url = "https://posfagotto.cl/api/getAppFacturacion?startDate={$fechaStr}&endDate={$fechaStr}";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "app-key: {$appKey}",
                    "authorization: {$authorization}"
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                error_log("[DETALLE_DIARIO] Día {$fechaStr}: HTTP {$httpCode}, Response: " . substr($response, 0, 200));
                
                if ($httpCode === 200 && $response) {
                    $facturas = json_decode($response, true) ?: [];
                    $totalDia = 0;
                    foreach ($facturas as $factura) {
                        $totalDia += floatval($factura['MntTotal'] ?? 0);
                    }
                    
                    if (count($facturas) > 0) {
                        error_log("[DETALLE_DIARIO] Día {$fechaStr}: {$totalDia} en " . count($facturas) . " facturas");
                        $facturacionPorDia[$fechaStr] = [
                            'total' => $totalDia,
                            'cantidad' => count($facturas)
                        ];
                    }
                }
                
                $fechaActual->modify('+1 day');
            }
            
            error_log("[DETALLE_DIARIO] Total días con facturación: " . count($facturacionPorDia));
            
            // Combinar compras con facturación
            $todasLasFechas = array_unique(array_merge(
                array_column($comprasPorDia, 'fecha'),
                array_keys($facturacionPorDia)
            ));
            sort($todasLasFechas);
            
            $resultado = [];
            foreach ($todasLasFechas as $fecha) {
                $compras = 0;
                $registrosCompras = 0;
                foreach ($comprasPorDia as $dia) {
                    if ($dia['fecha'] === $fecha) {
                        $compras = floatval($dia['total_compras']);
                        $registrosCompras = intval($dia['registros']);
                        break;
                    }
                }
                
                $facturacion = floatval($facturacionPorDia[$fecha]['total'] ?? 0);
                $cantidadFacturas = intval($facturacionPorDia[$fecha]['cantidad'] ?? 0);
                $porcentaje = $facturacion > 0 ? ($compras / $facturacion) * 100 : 0;
                
                $resultado[] = [
                    'fecha' => $fecha,
                    'total_compras' => $compras,
                    'total_facturacion' => $facturacion,
                    'total_facturas' => $cantidadFacturas,
                    'registros_compras' => $registrosCompras,
                    'porcentaje' => round($porcentaje, 2)
                ];
            }
            
            jsonSuccess($resultado);
            break;
            
        case 'comparacion_facturacion':
            $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
            $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
            
            // Obtener total de costos (compras)
            $stmt = $db->prepare("
                SELECT SUM(monto) as total_costos, COUNT(*) as registros_costos
                FROM centro_costos
                WHERE fecha BETWEEN ? AND ?
            ");
            $stmt->execute([$fechaDesde, $fechaHasta]);
            $costos = $stmt->fetch();
            
            // Obtener headers de autenticación del request
            $headers = getallheaders();
            $appKey = $headers['app-key'] ?? $_SERVER['HTTP_APP_KEY'] ?? '';
            $authorization = $headers['authorization'] ?? $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            
            // Debug: verificar qué headers llegan
            error_log("APP-KEY recibido: " . $appKey);
            error_log("AUTHORIZATION recibido: " . $authorization);
            
            // Obtener facturación desde API externa (igual que dashboard)
            $url = "https://posfagotto.cl/api/getAppFacturacion?startDate={$fechaDesde}&endDate={$fechaHasta}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "app-key: {$appKey}",
                "authorization: {$authorization}"
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            error_log("HTTP Code respuesta: " . $httpCode);
            error_log("Response length: " . strlen($response));
            
            $facturas = [];
            if ($httpCode === 200 && $response) {
                // Guardar respuesta para debug
                file_put_contents('/tmp/facturas_debug.json', $response);
                
                $facturas = json_decode($response, true) ?: [];
                error_log("Facturas decodificadas: " . count($facturas));
                
                // Debug: ver estructura de la primera factura
                if (count($facturas) > 0) {
                    error_log("Primera factura keys: " . implode(', ', array_keys($facturas[0])));
                    if (isset($facturas[0]['MntTotal'])) {
                        error_log("MntTotal primera factura: " . $facturas[0]['MntTotal']);
                    }
                }
            }
            
            // Calcular totales (IGUAL QUE DASHBOARD.JS)
            $totalFacturacion = 0;
            $totalFacturas = count($facturas);
            $facturacionPorDia = [];
            
            error_log("Total facturas encontradas: " . $totalFacturas);
            
            foreach ($facturas as $factura) {
                // COPIAR EXACTAMENTE COMO DASHBOARD: parseFloat(factura.MntTotal)
                $monto = floatval($factura['MntTotal'] ?? 0);
                $totalFacturacion += $monto;
                
                error_log("Factura MntTotal: " . ($factura['MntTotal'] ?? 'NULL') . " -> parsed: " . $monto);
                
                // Agrupar por día usando FchEmis (igual que dashboard)
                if (!empty($factura['FchEmis'])) {
                    $fecha = date('Y-m-d', strtotime($factura['FchEmis']));
                    if (!isset($facturacionPorDia[$fecha])) {
                        $facturacionPorDia[$fecha] = [
                            'fecha' => $fecha,
                            'total_facturacion' => 0,
                            'total_facturas' => 0
                        ];
                    }
                    $facturacionPorDia[$fecha]['total_facturacion'] += $monto;
                    $facturacionPorDia[$fecha]['total_facturas']++;
                }
            }
            
            error_log("TOTAL FACTURACION CALCULADO: " . $totalFacturacion);
            
            $totalCostos = floatval($costos['total_costos'] ?? 0);
            
            // Calcular porcentaje: (compras / facturación) * 100
            $porcentajeCostos = $totalFacturacion > 0 ? ($totalCostos / $totalFacturacion) * 100 : 0;
            
            // Calcular utilidad bruta (facturación - costos)
            $utilidadBruta = $totalFacturacion - $totalCostos;
            $margenUtilidad = $totalFacturacion > 0 ? ($utilidadBruta / $totalFacturacion) * 100 : 0;
            
            // Estado del margen (ideal < 20%)
            $estado = 'excelente';
            if ($porcentajeCostos > 30) $estado = 'critico';
            else if ($porcentajeCostos > 25) $estado = 'alto';
            else if ($porcentajeCostos > 20) $estado = 'aceptable';
            
            jsonSuccess([
                'total_facturacion' => $totalFacturacion,
                'total_costos' => $totalCostos,
                'total_facturas' => $totalFacturas,
                'utilidad_bruta' => $utilidadBruta,
                'porcentaje_costos' => round($porcentajeCostos, 2),
                'margen_utilidad' => round($margenUtilidad, 2),
                'registros_costos' => intval($costos['registros_costos'] ?? 0),
                'estado' => $estado,
                'mensaje' => $porcentajeCostos <= 20 ? '✅ Costos bajo control' : '⚠️ Costos sobre el objetivo',
                'por_dia' => array_values($facturacionPorDia)
            ]);
            break;
            
        default:
            jsonError('Acción no válida');
    }
    
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
?>
