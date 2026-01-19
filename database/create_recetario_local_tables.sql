-- ============================================
-- Script de creación de tablas para RECETARIO
-- PARTE 2: BASE DE DATOS LOCAL (CADA NEGOCIO)
-- ============================================
-- Este script crea las tablas de ASOCIACIÓN de recetas con productos en cada negocio
--
-- ⚠️ IMPORTANTE: Este script se ejecuta en cada BASE DE DATOS LOCAL de cada negocio/sucursal
-- NO en la base de datos maestra
--
-- Arquitectura:
-- ✅ BD MAESTRA → Plantillas de recetas (create_recetario_tables.sql)
-- ✅ BD LOCAL → Asociación de recetas con productos (ESTE ARCHIVO)
--
-- Flujo:
-- 1. Este negocio VE las plantillas de recetas de la BD Maestra
-- 2. Selecciona una receta (ej: "Brownie de Chocolate")
-- 3. La ASOCIA a uno de sus productos locales (tabla product_recetas)
-- 4. Al vender ese producto, se descuenta del stock local (tabla receta_descuentos_stock)

-- Descomentar y cambiar por el nombre de tu base de datos LOCAL:
-- USE `nombre_negocio_local`;

-- ============================================
-- Tabla: product_recetas
-- ============================================
-- Asocia PLANTILLAS de recetas globales con PRODUCTOS locales de este negocio
CREATE TABLE IF NOT EXISTS `product_recetas` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del producto LOCAL de este negocio',
  `receta_global_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la receta GLOBAL (de BD Maestra)',
  `multiplicador` DECIMAL(10,3) NOT NULL DEFAULT 1.000 COMMENT 'Multiplicador de cantidad (ej: si vende 2 porciones usar 2.000)',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Asociación activa (descuenta stock), 0=Inactiva',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_receta_unique` (`product_id`, `receta_global_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_receta_global` (`receta_global_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Asociación de productos locales con recetas globales';

-- ============================================
-- Tabla: receta_descuentos_stock
-- ============================================
-- Log de descuentos de stock local cuando se vende un producto con receta
-- Permite auditar y rastrear todos los descuentos automáticos
CREATE TABLE IF NOT EXISTS `receta_descuentos_stock` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_id` INT(11) UNSIGNED NULL COMMENT 'ID de la venta que generó el descuento',
  `product_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del producto vendido',
  `receta_global_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la receta global aplicada',
  `ingredient_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del ingrediente LOCAL descontado',
  `ingrediente_nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre del ingrediente (para histórico)',
  `cantidad_descontada` DECIMAL(10,3) NOT NULL COMMENT 'Cantidad descontada del stock',
  `unidad` VARCHAR(50) NOT NULL COMMENT 'Unidad de medida',
  `stock_anterior` DECIMAL(10,3) NULL COMMENT 'Stock antes del descuento',
  `stock_nuevo` DECIMAL(10,3) NULL COMMENT 'Stock después del descuento',
  `multiplicador_aplicado` DECIMAL(10,3) NOT NULL DEFAULT 1.000 COMMENT 'Multiplicador usado en la venta',
  `user_id` INT(11) UNSIGNED NULL COMMENT 'Usuario que realizó la venta',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_venta` (`venta_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_receta_global` (`receta_global_id`),
  KEY `idx_ingrediente` (`ingredient_id`),
  KEY `idx_fecha` (`created_at`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Log de descuentos automáticos de stock por recetas';

-- ============================================
-- Tabla: receta_ingredientes_mapping (OPCIONAL)
-- ============================================
-- Mapeo entre ingredientes de la plantilla global y los ingredientes locales
-- Útil si los nombres no coinciden exactamente
CREATE TABLE IF NOT EXISTS `receta_ingredientes_mapping` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `receta_global_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la receta global',
  `ingrediente_nombre_global` VARCHAR(255) NOT NULL COMMENT 'Nombre del ingrediente en la plantilla',
  `ingredient_id_local` INT(11) UNSIGNED NOT NULL COMMENT 'ID del ingrediente LOCAL equivalente',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Mapeo activo, 0=Inactivo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mapping_unique` (`receta_global_id`, `ingrediente_nombre_global`),
  KEY `idx_receta_global` (`receta_global_id`),
  KEY `idx_ingredient_local` (`ingredient_id_local`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Mapeo de ingredientes globales a locales (OPCIONAL)';

-- ============================================
-- Foreign Keys (opcional)
-- ============================================
/*
ALTER TABLE `product_recetas`
  ADD CONSTRAINT `fk_product_recetas_product` 
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `receta_descuentos_stock`
  ADD CONSTRAINT `fk_descuentos_product` 
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_descuentos_ingredient` 
    FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `receta_ingredientes_mapping`
  ADD CONSTRAINT `fk_mapping_ingredient` 
    FOREIGN KEY (`ingredient_id_local`) REFERENCES `ingredients` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;
*/

-- ============================================
-- Datos de ejemplo (opcional - comentados)
-- ============================================
/*
-- Supongamos que:
-- - Este negocio tiene un producto con id=15 llamado "Brownie de la casa"
-- - En la BD Maestra existe la receta_global_id=1 "Brownie de Chocolate"

-- Asociar el producto local con la receta global
INSERT INTO `product_recetas` (`product_id`, `receta_global_id`, `multiplicador`, `activo`) VALUES
(15, 1, 1.000, 1);  -- 1 porción por venta

-- Si los nombres de ingredientes no coinciden exactamente, crear mapeo:
INSERT INTO `receta_ingredientes_mapping` 
  (`receta_global_id`, `ingrediente_nombre_global`, `ingredient_id_local`, `activo`) 
VALUES
  (1, 'Harina', 5, 1),  -- La "Harina" de la receta global se mapea al ingredient_id=5 local
  (1, 'Azúcar', 8, 1),
  (1, 'Huevos', 12, 1),
  (1, 'Mantequilla', 20, 1),
  (1, 'Chocolate', 25, 1);
*/

-- ============================================
-- Vista auxiliar: productos_con_recetas
-- ============================================
-- Vista que facilita ver qué productos tienen recetas asociadas
CREATE OR REPLACE VIEW `productos_con_recetas` AS
SELECT 
  p.id AS product_id,
  p.name AS producto_nombre,
  pr.receta_global_id,
  pr.multiplicador,
  pr.activo AS receta_activa,
  pr.created_at AS asociado_el
FROM products p
INNER JOIN product_recetas pr ON p.id = pr.product_id
WHERE pr.activo = 1;

-- ============================================
-- Fin del script - PARTE 2 (BD LOCAL)
-- ============================================
-- ✅ Tablas locales creadas exitosamente
-- 
-- RESUMEN DE LA ARQUITECTURA:
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- BD MAESTRA (fagotto-erd-aplicacion):
--   ├── recetas_global (plantillas)
--   ├── receta_ingredientes_global (ingredientes de plantillas)
--   └── receta_history (historial de cambios)
--
-- BD LOCAL (cada negocio):
--   ├── product_recetas (asocia recetas globales con productos locales)
--   ├── receta_descuentos_stock (log de descuentos)
--   └── receta_ingredientes_mapping (mapeo opcional)
--
-- FLUJO COMPLETO:
-- 1. Admin crea receta en BD MAESTRA
-- 2. Negocio VE recetas disponibles
-- 3. Negocio ASOCIA receta a su producto (product_recetas)
-- 4. Al VENDER, se descuenta stock local automáticamente
-- 5. Se registra en receta_descuentos_stock
