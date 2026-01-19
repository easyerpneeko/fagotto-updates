-- ============================================
-- Script de creación de tablas para RECETARIO
-- BASE DE DATOS MAESTRA (PLANTILLAS GLOBALES + ASOCIACIONES POR NEGOCIO)
-- ============================================
-- Este script crea TODAS las tablas del recetario en la BD MAESTRA
-- Las recetas son globales pero cada negocio las asocia a sus productos usando app_id
--
-- ⚠️ IMPORTANTE: Este script se ejecuta SOLO en la BASE DE DATOS MAESTRA
-- Archivo: create_recetario_tables.sql
--
-- Arquitectura:
-- ✅ recetas_global → Plantillas de recetas (compartidas entre todos)
-- ✅ receta_ingredientes_global → Ingredientes de cada receta
-- ✅ product_recetas → Asociación de recetas a productos (filtrado por app_id)
-- ✅ receta_descuentos_stock → Log de descuentos (filtrado por app_id)
--
-- Flujo:
-- 1. Admin crea PLANTILLA de receta aquí (ej: "Ciabatta Salame")
-- 2. Admin define ingredientes y cantidades
-- 3. Cada negocio VE estas plantillas y las ASOCIA a sus productos (usando app_id)
-- 4. Al vender, se usa la receta global pero se descuenta del stock del negocio

USE `fagotto-erd-aplicacion`;

-- ============================================
-- Tabla: recetas_global
-- ============================================
-- Almacena las PLANTILLAS de recetas disponibles para todos los negocios
CREATE TABLE IF NOT EXISTS `recetas_global` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre de la receta',
  `descripcion` TEXT NULL COMMENT 'Descripción detallada de la receta',
  `codigo` VARCHAR(50) NULL COMMENT 'Código único de la receta (opcional)',
  `observaciones` TEXT NULL COMMENT 'Notas adicionales',
  `activa` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Activa, 0=Inactiva',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_unique` (`codigo`),
  KEY `idx_activa` (`activa`),
  KEY `idx_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PLANTILLAS de recetas globales (BD Maestra)';

-- ============================================
-- Tabla: receta_ingredientes_global
-- ============================================
-- Ingredientes y cantidades de cada PLANTILLA de receta
-- Nota: ingrediente_nombre es el nombre genérico del ingrediente
-- Cada negocio tendrá su propio ingredient_id local
CREATE TABLE IF NOT EXISTS `receta_ingredientes_global` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `receta_global_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la receta global',
  `ingrediente_nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre genérico del ingrediente (ej: Harina, Azúcar)',
  `cantidad` DECIMAL(10,3) NOT NULL COMMENT 'Cantidad del ingrediente requerida',
  `unidad` VARCHAR(50) NOT NULL DEFAULT 'g' COMMENT 'Unidad de medida (g, kg, ml, litro, unidad, etc)',
  `orden` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de presentación',
  `opcional` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Ingrediente opcional, 0=Obligatorio',
  `notas` TEXT NULL COMMENT 'Notas específicas para este ingrediente',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_receta_global` (`receta_global_id`),
  KEY `idx_ingrediente_nombre` (`ingrediente_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ingredientes de plantillas de recetas globales';

-- ============================================
-- Tabla: receta_history
-- ============================================
-- Historial de cambios en plantillas de recetas globales
CREATE TABLE IF NOT EXISTS `receta_history` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `receta_global_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la receta global modificada',
  `user_id` INT(11) UNSIGNED NULL COMMENT 'ID del usuario que realizó el cambio',
  `accion` ENUM('creada', 'editada', 'eliminada', 'activada', 'desactivada', 'ingrediente_agregado', 'ingrediente_removido', 'producto_asociado', 'producto_desasociado') NOT NULL COMMENT 'Tipo de acción realizada',
  `descripcion` TEXT NULL COMMENT 'Descripción del cambio',
  `datos_anteriores` JSON NULL COMMENT 'Datos antes del cambio (JSON)',
  `datos_nuevos` JSON NULL COMMENT 'Datos después del cambio (JSON)',
  `ip_address` VARCHAR(45) NULL COMMENT 'IP desde donde se realizó el cambio',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_receta_global` (`receta_global_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_fecha` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de cambios en plantillas de recetas globales';

/*
ALTER TABLE `receta_ingredientes_global`
  ADD CONSTRAINT `fk_receta_ingredientes_global` 
    FOREIGN KEY (`receta_global_id`) REFERENCES `recetas_global` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `receta_history`
  ADD CONSTRAINT `fk_receta_history_global` 
    FOREIGN KEY (`receta_global_id`) REFERENCES `recetas_global` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;
    
ALTER TABLE `product_recetas`
  ADD CONSTRAINT `fk_product_recetas_product` 
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_product_recetas_receta`
    FOREIGN KEY (`receta_global_id`) REFERENCES `recetas_global` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;
*/

-- ============================================
-- Tabla: product_recetas
-- ============================================
-- Asocia recetas globales con productos de cada negocio (filtrado por app_id)
CREATE TABLE IF NOT EXISTS `product_recetas` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del negocio (igual que en products)',
  `product_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del producto global',
  `receta_global_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la receta global',
  `multiplicador` DECIMAL(10,3) NOT NULL DEFAULT 1.000 COMMENT 'Multiplicador de cantidad',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Activa, 0=Inactiva',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_receta_unique` (`app_id`, `product_id`, `receta_global_id`),
  KEY `idx_app` (`app_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_receta_global` (`receta_global_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Asociación de productos con recetas (por negocio)';

-- ============================================
-- Tabla: receta_descuentos_stock
-- ============================================
-- Log de descuentos automáticos de stock por recetas (por negocio)
CREATE TABLE IF NOT EXISTS `receta_descuentos_stock` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del negocio',
  `venta_id` INT(11) UNSIGNED NULL COMMENT 'ID de la venta',
  `product_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del producto vendido',
  `receta_global_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la receta aplicada',
  `ingredient_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del ingrediente descontado',
  `ingrediente_nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre del ingrediente',
  `cantidad_descontada` DECIMAL(10,3) NOT NULL COMMENT 'Cantidad descontada',
  `unidad` VARCHAR(50) NOT NULL COMMENT 'Unidad de medida',
  `stock_anterior` DECIMAL(10,3) NULL COMMENT 'Stock antes del descuento',
  `stock_nuevo` DECIMAL(10,3) NULL COMMENT 'Stock después del descuento',
  `multiplicador_aplicado` DECIMAL(10,3) NOT NULL DEFAULT 1.000,
  `user_id` INT(11) UNSIGNED NULL COMMENT 'Usuario que realizó la venta',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_app` (`app_id`),
  KEY `idx_venta` (`venta_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_receta_global` (`receta_global_id`),
  KEY `idx_ingrediente` (`ingredient_id`),
  KEY `idx_fecha` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Log de descuentos automáticos de stock por recetas';

-- ============================================
-- Fin del script
-- ============================================
-- ✅ Todas las tablas creadas en BD Maestra
-- ⚠️ IMPORTANTE: Ahora ejecuta insert_recetas_base.sql para las 18 recetas
