-- Agregar columna id_local_original a tabla existente
USE easyerp;

ALTER TABLE asistencias_locales 
ADD COLUMN id_local_original INT NULL COMMENT 'ID del local en el sistema ERP original' 
AFTER app_id;
