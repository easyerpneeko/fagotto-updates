<?php
/**
 * Configuración de Mercado Pago Point Smart
 * 
 * Carga las variables de entorno y configura la conexión con MP
 */

// Cargar variables de entorno desde .env
if (!function_exists('loadEnv')) {
    function loadEnv($file = '.env') {
        if (!file_exists($file)) {
            die("ERROR: Archivo {$file} no encontrado. Copia .env.example a .env y configura tus credenciales.\n");
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }
    }
}

loadEnv(__DIR__ . '/.env');

// Configuración general
define('MP_ACCESS_TOKEN', $_ENV['MP_ACCESS_TOKEN'] ?? '');
define('MP_DEVICE_ID', $_ENV['MP_DEVICE_ID'] ?? '');
define('WEBHOOK_URL', $_ENV['WEBHOOK_URL'] ?? '');
define('MP_ENVIRONMENT', $_ENV['MP_ENVIRONMENT'] ?? 'development');

// Directorio para almacenar tokens
define('TOKEN_DIR', __DIR__ . '/storage/tokens');
define('LOG_DIR', __DIR__ . '/storage/logs');

// Crear directorios si no existen
if (!file_exists(TOKEN_DIR)) {
    mkdir(TOKEN_DIR, 0755, true);
}

if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

// No validar credenciales aquí, se validan en cada archivo según necesidad

return [
    'access_token' => MP_ACCESS_TOKEN,
    'device_id' => MP_DEVICE_ID,
    'webhook_url' => WEBHOOK_URL,
    'environment' => MP_ENVIRONMENT
];
