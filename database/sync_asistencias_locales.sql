-- Sincronizar todos los locales de la tabla principal a asistencias_locales
-- Esto evita el error de foreign key constraint

USE easyerp;

-- Insertar locales faltantes desde la tabla locales/sucursales principal
-- Ajusta el nombre de la tabla según tu sistema (puede ser 'locales', 'sucursales', 'branches', etc.)

-- Si la tabla se llama 'locales':
INSERT IGNORE INTO asistencias_locales (app_id, id_local_original, nombre, latitud, longitud, radio_metros, active)
SELECT 
    Serial as app_id,
    Id as id_local_original,
    Name as nombre,
    COALESCE(latitud, -33.4372) as latitud,  -- Default Santiago centro
    COALESCE(longitud, -70.6506) as longitud,
    50 as radio_metros,
    1 as active
FROM locales
WHERE Serial IS NOT NULL;

-- Ver los locales insertados
SELECT 
    app_id,
    nombre,
    CONCAT(latitud, ', ', longitud) as coordenadas,
    radio_metros
FROM asistencias_locales
ORDER BY nombre;

SELECT CONCAT('✅ ', COUNT(*), ' locales sincronizados') as resultado
FROM asistencias_locales;
