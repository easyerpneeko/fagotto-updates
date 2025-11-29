-- Agregar columnas para almacenar nombres (no solo IDs)
ALTER TABLE cupones 
ADD COLUMN sucursal_nombre VARCHAR(255) NULL AFTER sucursal_id,
ADD COLUMN usuario_nombre VARCHAR(255) NULL AFTER usuario_id;

-- Verificar estructura
DESCRIBE cupones;
