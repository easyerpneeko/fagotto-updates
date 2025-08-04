-- Script para restaurar configuraciones del SII que se desactivaron
-- Este script reactiva el submódulo SII 0.1 para todas las aplicaciones

-- Primero verificamos qué aplicaciones tienen el módulo de ventas instalado
SELECT 
    a.id as app_id,
    a.name as app_name,
    ma.id as modulo_app_id,
    m.name as modulo_name
FROM aplications a
JOIN modulos_aplicaciones ma ON a.id = ma.aplication_id
JOIN modulos m ON ma.modulo_id = m.id
WHERE m.name = 'ventas'
AND a.deleted_at IS NULL
AND ma.deleted_at IS NULL;

-- Verificamos qué aplicaciones tienen el submódulo SII
SELECT 
    a.id as app_id,
    a.name as app_name,
    sma.id as submodulo_app_id,
    sm.name as submodulo_name,
    sma.version
FROM aplications a
JOIN modulos_aplicaciones ma ON a.id = ma.aplication_id
JOIN modulos m ON ma.modulo_id = m.id
JOIN submodulos_aplicaciones sma ON ma.id = sma.modulo_aplicacion_id
JOIN submodulos sm ON sma.submodulo_id = sm.id
WHERE m.name = 'ventas'
AND sm.name = 'sii'
AND a.deleted_at IS NULL
AND ma.deleted_at IS NULL
AND sma.deleted_at IS NULL;

-- Reactivar el submódulo SII (cambiar status a 1) para todas las aplicaciones
UPDATE submodulos_aplicaciones sma
JOIN modulos_aplicaciones ma ON sma.modulo_aplicacion_id = ma.id
JOIN modulos m ON ma.modulo_id = m.id
JOIN submodulos sm ON sma.submodulo_id = sm.id
JOIN aplications a ON ma.aplication_id = a.id
SET sma.status = 1
WHERE m.name = 'ventas'
AND sm.name = 'sii'
AND a.deleted_at IS NULL
AND ma.deleted_at IS NULL
AND sma.deleted_at IS NULL;

-- Verificar que se actualizaron correctamente
SELECT 
    a.id as app_id,
    a.name as app_name,
    sma.status,
    sma.version,
    sm.name as submodulo_name
FROM aplications a
JOIN modulos_aplicaciones ma ON a.id = ma.aplication_id
JOIN modulos m ON ma.modulo_id = m.id
JOIN submodulos_aplicaciones sma ON ma.id = sma.modulo_aplicacion_id
JOIN submodulos sm ON sma.submodulo_id = sm.id
WHERE m.name = 'ventas'
AND sm.name = 'sii'
AND a.deleted_at IS NULL
AND ma.deleted_at IS NULL
AND sma.deleted_at IS NULL;

-- También verificamos y reactivamos las configuraciones de ajustes del SII
-- Primero vemos cuáles están desactivadas
SELECT 
    a.id as app_id,
    a.name as app_name,
    ss.id as setting_id,
    ss.key_name,
    ss.value,
    ss.status
FROM aplications a
JOIN modulos_aplicaciones ma ON a.id = ma.aplication_id
JOIN modulos m ON ma.modulo_id = m.id
JOIN submodulos_aplicaciones sma ON ma.id = sma.modulo_aplicacion_id
JOIN submodulos sm ON sma.submodulo_id = sm.id
JOIN submodulos_settings ss ON sma.id = ss.submodulo_aplicacion_id
WHERE m.name = 'ventas'
AND sm.name = 'sii'
AND ss.key_name LIKE '%sii%'
AND a.deleted_at IS NULL
AND ma.deleted_at IS NULL
AND sma.deleted_at IS NULL;

-- Reactivar configuraciones básicas del SII que suelen estar en "1"
UPDATE submodulos_settings ss
JOIN submodulos_aplicaciones sma ON ss.submodulo_aplicacion_id = sma.id
JOIN modulos_aplicaciones ma ON sma.modulo_aplicacion_id = ma.id
JOIN modulos m ON ma.modulo_id = m.id
JOIN submodulos sm ON sma.submodulo_id = sm.id
JOIN aplications a ON ma.aplication_id = a.id
SET ss.value = '1', ss.status = 1
WHERE m.name = 'ventas'
AND sm.name = 'sii'
AND ss.key_name IN (
    'boleta',
    'boleta_local', 
    'factura',
    'nota_de_credito',
    'debito',
    'transferencia',
    'efectivo',
    'banco',
    'credito',
    'cheque'
)
AND a.deleted_at IS NULL
AND ma.deleted_at IS NULL
AND sma.deleted_at IS NULL;

-- Verificación final de las configuraciones restauradas
SELECT 
    a.id as app_id,
    a.name as app_name,
    ss.key_name,
    ss.value,
    ss.status
FROM aplications a
JOIN modulos_aplicaciones ma ON a.id = ma.aplication_id
JOIN modulos m ON ma.modulo_id = m.id
JOIN submodulos_aplicaciones sma ON ma.id = sma.modulo_aplicacion_id
JOIN submodulos sm ON sma.submodulo_id = sm.id
JOIN submodulos_settings ss ON sma.id = ss.submodulo_aplicacion_id
WHERE m.name = 'ventas'
AND sm.name = 'sii'
AND a.deleted_at IS NULL
AND ma.deleted_at IS NULL
AND sma.deleted_at IS NULL
ORDER BY a.name, ss.key_name;
