-- Agregar campos ticket_promedio y semana a la tabla metas_locales
-- ticket_promedio: almacena el ticket promedio semanal ingresado manualmente por los administradores
-- semana: identifica la semana (1-5) del mes al que pertenece el ticket promedio

ALTER TABLE `metas_locales` 
ADD COLUMN `semana` INT NULL COMMENT 'Número de semana del mes (1-5)' AFTER `dia`,
ADD COLUMN `ticket_promedio` DECIMAL(10,2) DEFAULT NULL COMMENT 'Ticket promedio semanal ingresado manualmente' AFTER `meta_diaria`;

-- Nota: Los registros con ticket_promedio tienen 'semana' y 'dia' = NULL
-- Los registros con meta_diaria tienen 'dia' y 'semana' puede estar presente
