<?php
/**
 * Script de prueba para generar logs de ejemplo
 */

require_once 'config.php';

// Definir LOG_FILE antes de usar las funciones
define('LOG_FILE', __DIR__ . '/registro_empleados.log');
ini_set('error_log', LOG_FILE);

// Función helper para escribir logs estructurados
function writeLog($level, $message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message";
    if ($data !== null) {
        $logEntry .= " | " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    error_log($logEntry);
}

echo "<h1>🧪 Generador de Logs de Prueba</h1>";
echo "<p>Generando logs de ejemplo en: <b>" . LOG_FILE . "</b></p>";
echo "<hr>";

// Generar logs de prueba
writeLog('INFO', '=== INICIO DE PRUEBA DEL SISTEMA DE LOGS ===');
writeLog('INFO', 'Sistema iniciado correctamente');
writeLog('INFO', 'Verificando configuración', [
    'db_host' => DB_HOST,
    'db_name' => DB_NAME,
    'app_id' => APP_ID
]);

writeLog('INFO', 'Simulando registro de empleado', [
    'nombre' => 'Juan Pérez Test',
    'rut' => '12345678-9',
    'cargo' => 'Cajero'
]);

writeLog('INFO', 'Conectando a base de datos...');
writeLog('INFO', 'Conexión a BD exitosa');

writeLog('INFO', 'Validando sesión', ['session_id' => 'REG-TEST-12345']);
writeLog('INFO', 'Sesión validada correctamente', [
    'session_id' => 'REG-TEST-12345',
    'app_id' => 'AGU001',
    'expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
]);

writeLog('INFO', 'Verificando RUT duplicado', ['rut' => '12345678-9']);
writeLog('INFO', 'RUT disponible');

writeLog('INFO', 'Verificando email duplicado', ['email' => 'test@ejemplo.com']);
writeLog('INFO', 'Email disponible');

writeLog('INFO', 'Generando ID de empleado...');
writeLog('INFO', 'ID generado', ['new_id' => 'EMP999']);

writeLog('INFO', 'Iniciando indexación de rostro en AWS', ['employee_id' => 'EMP999']);
writeLog('WARNING', 'Clase AWSRekognition no disponible, usando face_id mock');

writeLog('INFO', 'Rostro indexado correctamente', ['face_id' => 'FACE-EMP999']);

writeLog('INFO', 'Insertando empleado en base de datos', [
    'id' => 'EMP999',
    'nombre' => 'Juan Pérez Test',
    'rut' => '12345678-9',
    'cargo' => 'Cajero',
    'email' => 'test@ejemplo.com',
    'face_id' => 'FACE-EMP999'
]);

writeLog('INFO', 'Empleado insertado en BD correctamente', ['employee_id' => 'EMP999']);

writeLog('INFO', 'Marcando sesión como usada', ['session_id' => 'REG-TEST-12345']);

writeLog('SUCCESS', '✅ REGISTRO COMPLETADO EXITOSAMENTE', [
    'employee_id' => 'EMP999',
    'nombre' => 'Juan Pérez Test',
    'face_id' => 'FACE-EMP999'
]);

// Simular algunos errores comunes
echo "<br><br><b>Generando logs de errores comunes...</b><br><br>";

writeLog('INFO', '=== SIMULANDO ERRORES COMUNES ===');

writeLog('ERROR', 'Datos incompletos en la petición', [
    'sessionId' => 'OK',
    'appId' => '❌ FALTA',
    'nombre' => 'OK',
    'rut' => 'OK',
    'cargo' => '❌ FALTA',
    'email' => 'OK',
    'foto' => 'OK'
]);

writeLog('ERROR', 'Sesión inválida o expirada', ['session_id' => 'REG-EXPIRED-123']);

writeLog('ERROR', 'RUT duplicado encontrado', [
    'rut' => '98765432-1',
    'existing_id' => 'EMP001',
    'existing_nombre' => 'María González'
]);

writeLog('ERROR', 'Email duplicado encontrado', [
    'email' => 'duplicado@ejemplo.com',
    'existing_id' => 'EMP002',
    'existing_nombre' => 'Pedro Silva'
]);

writeLog('ERROR', 'Error PDO (Base de datos)', [
    'message' => 'SQLSTATE[42S02]: Base table or view not found',
    'code' => '42S02',
    'file' => 'registrar-rostro.php',
    'line' => 145
]);

writeLog('WARNING', 'Error al usar AWS Rekognition, usando face_id mock', [
    'error' => 'InvalidSignatureException: Signature expired',
    'employee_id' => 'EMP888'
]);

writeLog('ERROR', 'JSON inválido recibido', ['json_error' => 'Syntax error']);

echo "✅ Logs generados exitosamente<br><br>";
echo "📊 <a href='ver-logs.html' target='_blank'><b>Ver logs en tiempo real</b></a><br><br>";
echo "📄 Archivo de log: " . LOG_FILE . "<br>";
echo "📏 Tamaño: " . filesize(LOG_FILE) . " bytes<br>";
echo "📝 Líneas: " . count(file(LOG_FILE)) . "<br>";
