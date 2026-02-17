<?php
/**
 * Verificar versión de api-enviar-pago.php
 * Subir a: fagottoerp.cl/mercadopago/check-version.php
 */

header('Content-Type: application/json');

$apiFile = __DIR__ . '/api-enviar-pago.php';

if (!file_exists($apiFile)) {
    echo json_encode([
        'error' => 'api-enviar-pago.php no encontrado',
        'path' => $apiFile
    ], JSON_PRETTY_PRINT);
    exit;
}

$content = file_get_contents($apiFile);

// Buscar si contiene la línea del external_store_id (versión antigua)
$hasExternalStoreId = strpos($content, "external_store_id") !== false;

// Buscar línea que confirma la corrección
$hasCorrectionNote = strpos($content, "NO está soportado por MercadoPago Point API") !== false;

echo json_encode([
    'file' => 'api-enviar-pago.php',
    'path' => $apiFile,
    'size' => filesize($apiFile) . ' bytes',
    'last_modified' => date('Y-m-d H:i:s', filemtime($apiFile)),
    'has_external_store_id_reference' => $hasExternalStoreId ? 'SÍ (puede ser solo comentarios)' : 'NO',
    'has_correction_note' => $hasCorrectionNote ? 'SÍ ✅ (versión corregida)' : 'NO ❌ (versión antigua)',
    'version_status' => $hasCorrectionNote ? 'ACTUALIZADO ✅' : 'DESACTUALIZADO ⚠️',
    'action_needed' => $hasCorrectionNote ? 'Ninguna - archivo correcto' : 'SUBIR api-enviar-pago.php corregido',
    'first_100_chars' => substr($content, 0, 200) . '...'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
