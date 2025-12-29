<?php
/**
 * Configuración de Base de Datos
 * 
 * INSTRUCCIONES:
 * 1. Crea una base de datos en tu servidor MySQL/PostgreSQL
 * 2. Ejecuta el archivo: database/schema_asistencia.sql
 * 3. Completa los datos de conexión abajo
 */

// ✅ CONFIGURACIÓN BASE DE DATOS MAESTRA - REMOTA
// TODOS LOS LOCALES SE CONECTAN A ESTA DB CENTRALIZADA
define('DB_HOST', 'localhost'); // Cambiar a IP/dominio del servidor: 'fagottoerp.cl' o '192.168.1.100'
define('DB_NAME', 'asistencias');
define('DB_USER', 'root');
define('DB_PASS', 'Fagotto2025!');
define('DB_CHARSET', 'utf8mb4');

/**
 * Crear conexión PDO
 * @return PDO
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error
        error_log("Database Connection Error: " . $e->getMessage());
        
        // Respuesta JSON para APIs
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error de conexión a base de datos'
            ]);
            exit;
        }
        
        // HTML para páginas
        die('❌ Error de conexión a base de datos. Verifica config/database.php');
    }
}

/**
 * EJEMPLO DE CONFIGURACIÓN:
 * 
 * // Servidor local
 * define('DB_HOST', 'localhost');
 * define('DB_NAME', 'fagotto_asistencia');
 * define('DB_USER', 'root');
 * define('DB_PASS', '');
 * 
 * // Servidor producción
 * define('DB_HOST', 'db.fagotto.cl');
 * define('DB_NAME', 'fagotto_prod');
 * define('DB_USER', 'fagotto_user');
 * define('DB_PASS', 'tu_password_seguro');
 */

// Verificar que la configuración está completa
if (DB_HOST === 'localhost' && DB_NAME === 'fagotto_asistencia' && DB_PASS === '') {
    // Estás usando valores por defecto - probablemente desarrollo local
    // No mostrar error aquí, pero advertir en logs
    error_log("⚠️ ADVERTENCIA: Usando configuración de base de datos por defecto");
}
