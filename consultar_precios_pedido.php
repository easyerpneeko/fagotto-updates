<?php
/**
 * API para consultar precios del sistema Pedido Final
 * Consulta la tabla pedidofinal_precios en la DB maestra (easyerp)
 * 
 * Endpoint: https://asistencia.fagottoerp.cl/api/consultar_precios_pedido.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuración de la base de datos maestra
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ajustar según tu configuración
define('DB_PASS', ''); // Ajustar según tu configuración
define('DB_NAME', 'easyerp');
define('DB_CHARSET', 'utf8mb4');

/**
 * Conectar a la base de datos maestra
 */
function conectarDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Error de conexión DB: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtener todos los precios
 */
function obtenerPrecios() {
    $pdo = conectarDB();
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Error de conexión a la base de datos'
        ];
    }
    
    try {
        $sql = "SELECT 
                    id,
                    producto,
                    unidad_venta,
                    unidad_medida,
                    precio_por_unidad,
                    categoria,
                    activo,
                    fecha_actualizacion
                FROM pedidofinal_precios
                ORDER BY categoria, producto";
        
        $stmt = $pdo->query($sql);
        $precios = $stmt->fetchAll();
        
        return [
            'success' => true,
            'precios' => $precios,
            'total' => count($precios),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener precios: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al consultar precios: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtener precios por categoría
 */
function obtenerPreciosPorCategoria($categoria) {
    $pdo = conectarDB();
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Error de conexión a la base de datos'
        ];
    }
    
    try {
        $sql = "SELECT 
                    id,
                    producto,
                    unidad_venta,
                    unidad_medida,
                    precio_por_unidad,
                    categoria,
                    activo,
                    fecha_actualizacion
                FROM pedidofinal_precios
                WHERE categoria = :categoria
                ORDER BY producto";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['categoria' => $categoria]);
        $precios = $stmt->fetchAll();
        
        return [
            'success' => true,
            'precios' => $precios,
            'total' => count($precios),
            'categoria' => $categoria,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener precios por categoría: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al consultar precios: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtener un producto específico
 */
function obtenerProducto($id) {
    $pdo = conectarDB();
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Error de conexión a la base de datos'
        ];
    }
    
    try {
        $sql = "SELECT 
                    id,
                    producto,
                    unidad_venta,
                    unidad_medida,
                    precio_por_unidad,
                    categoria,
                    activo,
                    fecha_actualizacion
                FROM pedidofinal_precios
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $producto = $stmt->fetch();
        
        if ($producto) {
            return [
                'success' => true,
                'producto' => $producto,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Producto no encontrado'
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error al obtener producto: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al consultar producto: ' . $e->getMessage()
        ];
    }
}

/**
 * Actualizar precio de un producto
 */
function actualizarPrecio($id, $precio) {
    $pdo = conectarDB();
    
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Error de conexión a la base de datos'
        ];
    }
    
    try {
        $sql = "UPDATE pedidofinal_precios 
                SET precio_por_unidad = :precio
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([
            'precio' => $precio,
            'id' => $id
        ]);
        
        if ($resultado) {
            return [
                'success' => true,
                'message' => 'Precio actualizado correctamente',
                'id' => $id,
                'nuevo_precio' => $precio,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se pudo actualizar el precio'
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error al actualizar precio: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al actualizar precio: ' . $e->getMessage()
        ];
    }
}

// Procesar la solicitud
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'obtener_precios':
        $response = obtenerPrecios();
        break;
        
    case 'obtener_por_categoria':
        $categoria = $input['categoria'] ?? '';
        if (empty($categoria)) {
            $response = [
                'success' => false,
                'message' => 'Categoría no especificada'
            ];
        } else {
            $response = obtenerPreciosPorCategoria($categoria);
        }
        break;
        
    case 'obtener_producto':
        $id = $input['id'] ?? 0;
        if (empty($id)) {
            $response = [
                'success' => false,
                'message' => 'ID no especificado'
            ];
        } else {
            $response = obtenerProducto($id);
        }
        break;
        
    case 'actualizar_precio':
        $id = $input['id'] ?? 0;
        $precio = $input['precio'] ?? 0;
        
        if (empty($id) || $precio < 0) {
            $response = [
                'success' => false,
                'message' => 'Datos inválidos'
            ];
        } else {
            $response = actualizarPrecio($id, $precio);
        }
        break;
        
    default:
        $response = [
            'success' => false,
            'message' => 'Acción no válida',
            'acciones_disponibles' => [
                'obtener_precios',
                'obtener_por_categoria',
                'obtener_producto',
                'actualizar_precio'
            ]
        ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
