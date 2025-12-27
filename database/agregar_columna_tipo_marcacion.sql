-- Agregar columna tipo de marcación
USE easyerp;

ALTER TABLE asistencias_records 
ADD COLUMN tipo_marcacion ENUM('entrada', 'salida_colacion', 'regreso_colacion', 'salida') 
NOT NULL DEFAULT 'entrada'
AFTER fecha_hora;

-- Ver estructura actualizada
DESCRIBE asistencias_records;
