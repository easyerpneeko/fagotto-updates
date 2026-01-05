-- Consulta SQL simple para ver versiones de cada negocio
-- Ejecutar directamente en tu cliente MySQL

-- Ver todos los negocios y sus versiones
SELECT 
    app_id as 'ID',
    app_name as 'Negocio',
    current_version as 'Versión',
    last_ping as 'Última Conexión',
    TIMESTAMPDIFF(MINUTE, last_ping, NOW()) as 'Minutos Offline'
FROM app_version_tracking
ORDER BY app_name ASC;

-- Ver solo los que NO están en la última versión (cambiar 1.11.42 por tu versión actual)
SELECT 
    app_id as 'ID',
    app_name as 'Negocio',
    current_version as 'Versión',
    '1.11.42' as 'Versión Esperada',
    last_ping as 'Última Conexión'
FROM app_version_tracking
WHERE current_version != '1.11.42'
ORDER BY app_name ASC;

-- Ver cuántos negocios hay por versión
SELECT 
    current_version as 'Versión',
    COUNT(*) as 'Cantidad Negocios'
FROM app_version_tracking
GROUP BY current_version
ORDER BY current_version DESC;

-- Ver los que llevan más de 1 hora offline
SELECT 
    app_id as 'ID',
    app_name as 'Negocio',
    current_version as 'Versión',
    last_ping as 'Última Conexión',
    TIMESTAMPDIFF(HOUR, last_ping, NOW()) as 'Horas Offline'
FROM app_version_tracking
WHERE TIMESTAMPDIFF(MINUTE, last_ping, NOW()) > 60
ORDER BY last_ping ASC;

-- Resumen rápido
SELECT 
    COUNT(*) as 'Total Negocios',
    SUM(CASE WHEN current_version = '1.11.42' THEN 1 ELSE 0 END) as 'Actualizados',
    SUM(CASE WHEN current_version != '1.11.42' AND TIMESTAMPDIFF(MINUTE, last_ping, NOW()) <= 60 THEN 1 ELSE 0 END) as 'Desactualizados',
    SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, last_ping, NOW()) > 60 THEN 1 ELSE 0 END) as 'Offline'
FROM app_version_tracking;
