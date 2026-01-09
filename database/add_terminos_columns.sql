-- Agregar columnas para registro de aceptación de términos
-- Fecha: 2026-01-08
-- Propósito: Guardar información de cuándo el empleado aceptó los términos y dónde están los archivos

-- Verificar si las columnas ya existen
SET @dbname = DATABASE();
SET @tablename = 'asistencias_employees';

-- Agregar columnas solo si no existen
SET @query1 = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'terminos_aceptados') = 0,
    'ALTER TABLE asistencias_employees ADD COLUMN terminos_aceptados TINYINT(1) DEFAULT 0 COMMENT "Si el empleado aceptó los términos (0=No, 1=Sí)"',
    'SELECT "Column terminos_aceptados already exists"'
));
PREPARE stmt1 FROM @query1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @query2 = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'terminos_fecha') = 0,
    'ALTER TABLE asistencias_employees ADD COLUMN terminos_fecha TIMESTAMP NULL COMMENT "Fecha y hora en que aceptó los términos"',
    'SELECT "Column terminos_fecha already exists"'
));
PREPARE stmt2 FROM @query2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @query3 = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'terminos_archivo') = 0,
    'ALTER TABLE asistencias_employees ADD COLUMN terminos_archivo VARCHAR(255) NULL COMMENT "Nombre del archivo HTML con los términos firmados"',
    'SELECT "Column terminos_archivo already exists"'
));
PREPARE stmt3 FROM @query3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SET @query4 = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'firma_archivo') = 0,
    'ALTER TABLE asistencias_employees ADD COLUMN firma_archivo VARCHAR(255) NULL COMMENT "Nombre del archivo PNG con la firma digital"',
    'SELECT "Column firma_archivo already exists"'
));
PREPARE stmt4 FROM @query4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

-- Verificar
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT, 
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'asistencias_employees' 
    AND TABLE_SCHEMA = DATABASE()
    AND COLUMN_NAME IN ('terminos_aceptados', 'terminos_fecha', 'terminos_archivo', 'firma_archivo')
ORDER BY ORDINAL_POSITION;
