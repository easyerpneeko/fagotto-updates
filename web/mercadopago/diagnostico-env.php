<?php
/**
 * Diagnóstico de configuración de MercadoPago
 * Para verificar que las variables del .env se están leyendo correctamente
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$diagnostico = [
    'timestamp' => date('Y-m-d H:i:s'),
    'env_file_exists' => file_exists(__DIR__ . '/.env'),
    'config_loaded' => defined('MP_ENVIRONMENT'),
    'credenciales' => [
        'cuenta_116' => [
            'access_token' => getenv('MP_ACCESS_TOKEN_116') ? '✅ Configurado (' . substr(getenv('MP_ACCESS_TOKEN_116'), 0, 20) . '...)' : '❌ NO ENCONTRADO',
            'device_id' => getenv('MP_DEVICE_ID_116') ?: '❌ NO ENCONTRADO'
        ],
        'cuenta_58' => [
            'access_token' => getenv('MP_ACCESS_TOKEN_58') ? '✅ Configurado (' . substr(getenv('MP_ACCESS_TOKEN_58'), 0, 20) . '...)' : '❌ NO ENCONTRADO',
            'device_id' => getenv('MP_DEVICE_ID_58') ?: '❌ NO ENCONTRADO'
        ]
    ],
    'prueba_app_id_58' => null,
    'prueba_app_id_116' => null
];

// Probar selección por app_id 58
$appId = 58;
if ($appId == 58) {
    $token = getenv('MP_ACCESS_TOKEN_58');
    $device = getenv('MP_DEVICE_ID_58');
} else {
    $token = null;
    $device = null;
}

$diagnostico['prueba_app_id_58'] = [
    'token_encontrado' => !empty($token),
    'device_encontrado' => !empty($device),
    'token' => $token ? substr($token, 0, 30) . '...' : 'NO ENCONTRADO',
    'device' => $device ?: 'NO ENCONTRADO'
];

// Probar selección por app_id 116
$appId = 116;
if ($appId == 116) {
    $token = getenv('MP_ACCESS_TOKEN_116');
    $device = getenv('MP_DEVICE_ID_116');
} else {
    $token = null;
    $device = null;
}

$diagnostico['prueba_app_id_116'] = [
    'token_encontrado' => !empty($token),
    'device_encontrado' => !empty($device),
    'token' => $token ? substr($token, 0, 30) . '...' : 'NO ENCONTRADO',
    'device' => $device ?: 'NO ENCONTRADO'
];

// Variables de entorno disponibles
$diagnostico['todas_las_env_mp'] = [];
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'MP_') === 0) {
        $diagnostico['todas_las_env_mp'][$key] = substr($value, 0, 30) . '...';
    }
}

echo json_encode($diagnostico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
