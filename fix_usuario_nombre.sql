-- Agregar la columna usuario_nombre que falta
ALTER TABLE `TurnosCaja` ADD COLUMN `usuario_nombre` varchar(255) NOT NULL AFTER `usuario_id`;