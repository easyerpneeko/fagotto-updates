-- Agregar columna 'dia' a la tabla metas_locales para metas por día
ALTER TABLE `easyerp`.`metas_locales` 
ADD COLUMN `dia` INT NULL DEFAULT NULL AFTER `anio`,
ADD INDEX `idx_dia` (`dia`);

-- Comentario: Ahora cada registro puede tener un día específico del mes
-- Esto permite configurar metas diferentes para cada día
-- dia = 1 a 31 (según el mes)
