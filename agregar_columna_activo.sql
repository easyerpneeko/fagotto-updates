-- Agregar columna 'activo' a la tabla cupones
-- Esta columna es requerida por el backend para validar si un cupón está habilitado

ALTER TABLE cupones 
ADD COLUMN activo TINYINT(1) DEFAULT 1 NOT NULL AFTER valor_descuento;

-- Actualizar todos los cupones existentes para que estén activos
UPDATE cupones SET activo = 1;

-- Crear índice para búsquedas más rápidas
CREATE INDEX idx_activo ON cupones(activo);

-- Verificar
SELECT COUNT(*) as total_cupones, 
       SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
       SUM(CASE WHEN usado = 1 THEN 1 ELSE 0 END) as usados
FROM cupones;
