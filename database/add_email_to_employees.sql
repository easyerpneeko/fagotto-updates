-- Agregar columna email a la tabla asistencias_employees
USE easyerp;

ALTER TABLE asistencias_employees 
ADD COLUMN email VARCHAR(100) NULL AFTER cargo;

-- Verificar la estructura
DESCRIBE asistencias_employees;

SELECT '✅ Columna email agregada exitosamente' as resultado;
