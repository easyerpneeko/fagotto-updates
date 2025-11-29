-- ====================================================================
-- Query para insertar el setting de Gelateria en settings_modules
-- ====================================================================

-- Insertar el registro de gelateria en settings_modules
INSERT INTO settings_modules (name, keyname, module_id) 
VALUES (
    'Gelateria', 
    'gelateria', 
    (SELECT id FROM modules WHERE keyname = 'cafeteria' LIMIT 1)
);

-- Verificar que se insertó correctamente
SELECT * FROM settings_modules WHERE keyname = 'gelateria';
