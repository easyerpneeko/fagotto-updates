<?php
/**
 * CONFIGURACIÓN SIMPLE - Carga todo desde .env
 */

// Zona horaria de Chile
date_default_timezone_set('America/Santiago');

// Cargar .env
function loadEnv($file = __DIR__ . '/.env') {
    if (!file_exists($file)) return;
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

loadEnv();

// Database
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_NAME', $_ENV['DB_NAME'] ?? 'asistencias');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// AWS
define('AWS_ACCESS_KEY', $_ENV['AWS_ACCESS_KEY'] ?? '');
define('AWS_SECRET_KEY', $_ENV['AWS_SECRET_KEY'] ?? '');
define('AWS_REGION', $_ENV['AWS_REGION'] ?? 'us-east-1');

// Local
define('APP_ID', $_ENV['APP_ID'] ?? 'AGU001');
define('LOCAL_NOMBRE', $_ENV['LOCAL_NOMBRE'] ?? 'Local');
define('LOCAL_LAT', $_ENV['LOCAL_LAT'] ?? 0);
define('LOCAL_LNG', $_ENV['LOCAL_LNG'] ?? 0);
define('LOCAL_RADIUS', $_ENV['LOCAL_RADIUS'] ?? 50);

// Conexión PDO
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '-03:00'" // Chile
            ]);
        } catch (PDOException $e) {
            die('❌ Error DB: ' . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Calcular distancia entre dos puntos GPS (fórmula Haversine)
 * @param float $lat1 Latitud punto 1
 * @param float $lon1 Longitud punto 1
 * @param float $lat2 Latitud punto 2
 * @param float $lon2 Longitud punto 2
 * @return float Distancia en metros
 */
function calcularDistanciaGPS($lat1, $lon1, $lat2, $lon2) {
    $radioTierra = 6371000; // Radio de la Tierra en metros
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distancia = $radioTierra * $c;
    
    return round($distancia, 2); // Metros con 2 decimales
}

