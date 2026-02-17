<?php
// Cargar configuración (esto carga el .env)
require_once __DIR__ . '/config.php';

echo "=== VARIABLES ESPECÍFICAS POR APP_ID ===\n";
echo "MP_ACCESS_TOKEN_116: " . (getenv('MP_ACCESS_TOKEN_116') ?: 'NO EXISTE') . "\n";
echo "MP_DEVICE_ID_116: " . (getenv('MP_DEVICE_ID_116') ?: 'NO EXISTE') . "\n\n";
echo "MP_ACCESS_TOKEN_58: " . (getenv('MP_ACCESS_TOKEN_58') ?: 'NO EXISTE') . "\n";
echo "MP_DEVICE_ID_58: " . (getenv('MP_DEVICE_ID_58') ?: 'NO EXISTE') . "\n\n";

echo "=== VARIABLES BASE (FALLBACK) ===\n";
echo "MP_ACCESS_TOKEN: " . (getenv('MP_ACCESS_TOKEN') ?: 'NO EXISTE') . "\n";
echo "MP_DEVICE_ID: " . (getenv('MP_DEVICE_ID') ?: 'NO EXISTE') . "\n";
