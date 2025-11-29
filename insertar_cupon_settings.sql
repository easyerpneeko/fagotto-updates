-- ====================================================================
-- Query para insertar el setting de Cupón en settings_modules
-- Ejecutar después de eliminar los duplicados
-- ====================================================================

-- Insertar el registro de cupón en settings_modules
INSERT INTO settings_modules (name, keyname, module_id) 
VALUES (
    'Cupón', 
    'cupon', 
    (SELECT id FROM modules WHERE keyname = 'cafeteria' LIMIT 1)
);

-- Verificar que se insertó correctamente
SELECT * FROM settings_modules WHERE keyname = 'cupon';
