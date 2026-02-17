<?php
/**
 * Verificar configuración completa de ambos locales
 * Subir a: fagottoerp.cl/mercadopago/test-ambos-locales.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';

$result = [
    'test' => 'Verificación de configuración multi-local',
    'timestamp' => date('Y-m-d H:i:s'),
    'locales' => []
];

// ========== LAS CONDES (app_id 116) ==========
$result['locales']['116_las_condes'] = [
    'app_id' => 116,
    'nombre' => 'Fagotto Las Condes',
    'cuenta' => 'Cuenta 2',
    'access_token' => getenv('MP_ACCESS_TOKEN_116') ? substr(getenv('MP_ACCESS_TOKEN_116'), 0, 20) . '...' . substr(getenv('MP_ACCESS_TOKEN_116'), -10) : '❌ NO ENCONTRADO',
    'device_id' => getenv('MP_DEVICE_ID_116') ?: '❌ NO ENCONTRADO',
    'client_id' => getenv('MP_CLIENT_ID_116') ?: '❌ NO ENCONTRADO',
    'client_secret' => getenv('MP_CLIENT_SECRET_116') ? substr(getenv('MP_CLIENT_SECRET_116'), 0, 8) . '...' : '❌ NO ENCONTRADO',
    'configuracion_completa' => (
        getenv('MP_ACCESS_TOKEN_116') && 
        getenv('MP_DEVICE_ID_116') && 
        getenv('MP_CLIENT_ID_116') && 
        getenv('MP_CLIENT_SECRET_116')
    ) ? '✅ COMPLETA' : '❌ INCOMPLETA',
    'terminal_esperado' => 'NEWLAND_N950__N950NCC302980807'
];

// ========== AGUSTINAS (app_id 58) ==========
$result['locales']['58_agustinas'] = [
    'app_id' => 58,
    'nombre' => 'Fagotto Agustinas',
    'cuenta' => 'Cuenta 4',
    'access_token' => getenv('MP_ACCESS_TOKEN_58') ? substr(getenv('MP_ACCESS_TOKEN_58'), 0, 20) . '...' . substr(getenv('MP_ACCESS_TOKEN_58'), -10) : '❌ NO ENCONTRADO',
    'device_id' => getenv('MP_DEVICE_ID_58') ?: '❌ NO ENCONTRADO',
    'client_id' => getenv('MP_CLIENT_ID_58') ?: '❌ NO ENCONTRADO',
    'client_secret' => getenv('MP_CLIENT_SECRET_58') ? substr(getenv('MP_CLIENT_SECRET_58'), 0, 8) . '...' : '❌ NO ENCONTRADO',
    'configuracion_completa' => (
        getenv('MP_ACCESS_TOKEN_58') && 
        getenv('MP_DEVICE_ID_58') && 
        getenv('MP_CLIENT_ID_58') && 
        getenv('MP_CLIENT_SECRET_58')
    ) ? '✅ COMPLETA' : '❌ INCOMPLETA',
    'terminal_esperado' => 'NEWLAND_N950__N950NCC804178629'
];

// Resumen general
$result['resumen'] = [
    'total_locales' => 2,
    'locales_configurados_completamente' => (
        $result['locales']['116_las_condes']['configuracion_completa'] === '✅ COMPLETA' &&
        $result['locales']['58_agustinas']['configuracion_completa'] === '✅ COMPLETA'
    ) ? 2 : 'INCOMPLETO',
    'estado_general' => (
        $result['locales']['116_las_condes']['configuracion_completa'] === '✅ COMPLETA' &&
        $result['locales']['58_agustinas']['configuracion_completa'] === '✅ COMPLETA'
    ) ? '✅ TODO CONFIGURADO CORRECTAMENTE' : '⚠️ REVISAR CONFIGURACIÓN'
];

// Test de payload para cada local
$result['test_payloads'] = [];

// Test Las Condes
$appId = 116;
$tokenLC = getenv('MP_ACCESS_TOKEN_116');
$deviceLC = getenv('MP_DEVICE_ID_116');
$result['test_payloads']['las_condes'] = [
    'app_id' => $appId,
    'access_token_presente' => !empty($tokenLC) ? '✅' : '❌',
    'device_id_presente' => !empty($deviceLC) ? '✅' : '❌',
    'device_id' => $deviceLC,
    'puede_procesar_pagos' => (!empty($tokenLC) && !empty($deviceLC)) ? '✅ SÍ' : '❌ NO'
];

// Test Agustinas
$appId = 58;
$tokenAG = getenv('MP_ACCESS_TOKEN_58');
$deviceAG = getenv('MP_DEVICE_ID_58');
$result['test_payloads']['agustinas'] = [
    'app_id' => $appId,
    'access_token_presente' => !empty($tokenAG) ? '✅' : '❌',
    'device_id_presente' => !empty($deviceAG) ? '✅' : '❌',
    'device_id' => $deviceAG,
    'puede_procesar_pagos' => (!empty($tokenAG) && !empty($deviceAG)) ? '✅ SÍ' : '❌ NO'
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
