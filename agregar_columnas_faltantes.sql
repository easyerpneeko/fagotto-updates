-- Agregar columnas faltantes a la tabla TurnosCaja
ALTER TABLE `TurnosCaja` ADD COLUMN `app_id` int(11) NOT NULL AFTER `id`;
ALTER TABLE `TurnosCaja` ADD COLUMN `app_nombre` varchar(255) NOT NULL AFTER `usuario_id`;
ALTER TABLE `TurnosCaja` ADD COLUMN `fecha_inicio` datetime NOT NULL AFTER `app_nombre`;
ALTER TABLE `TurnosCaja` ADD COLUMN `fecha_termino` datetime NULL AFTER `fecha_inicio`;
ALTER TABLE `TurnosCaja` ADD COLUMN `total_sistema` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `monto_final`;
ALTER TABLE `TurnosCaja` ADD COLUMN `diferencia` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `total_general`;
ALTER TABLE `TurnosCaja` ADD COLUMN `estado` enum('abierto','cerrado','perfecto','sobrante','faltante') NOT NULL DEFAULT 'abierto' AFTER `diferencia_general`;
ALTER TABLE `TurnosCaja` ADD COLUMN `detalle_efectivo` longtext NULL AFTER `observaciones`;
ALTER TABLE `TurnosCaja` ADD COLUMN `detalle_medios_pago` longtext NULL AFTER `detalle_efectivo`;
ALTER TABLE `TurnosCaja` ADD COLUMN `numero_transacciones` int(11) NOT NULL DEFAULT '0' AFTER `detalle_medios_pago`;
ALTER TABLE `TurnosCaja` ADD COLUMN `puede_hacer_arqueo` tinyint(1) NOT NULL DEFAULT '1' AFTER `turno_cerrado_en`;