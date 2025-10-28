-- Tabla para gestionar descuentos de productos por sucursal
-- Ejecutar en la BASE DE DATOS PRINCIPAL (fagottodb)

CREATE TABLE `product_discounts_by_branch` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `application_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID de la sucursal/negocio',
  `product_id` BIGINT UNSIGNED NULL COMMENT 'ID del producto específico (NULL = todos)',
  `product_category` VARCHAR(255) NULL COMMENT 'Categoría de productos (ej: salsas)',
  `product_name_pattern` VARCHAR(255) NULL COMMENT 'Patrón de nombre (ej: %salsa%)',
  `discount_percentage` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de descuento (10.00 = 10%)',
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Monto fijo de descuento',
  `active` TINYINT(1) DEFAULT 1 COMMENT 'Si está activo o no',
  `start_date` DATE NULL COMMENT 'Fecha inicio de vigencia',
  `end_date` DATE NULL COMMENT 'Fecha fin de vigencia',
  `description` TEXT NULL COMMENT 'Descripción del descuento',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  INDEX `idx_application_id` (`application_id`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_active` (`active`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ejemplo de uso: Descuento del 10% en todas las salsas para la sucursal con ID 5
-- INSERT INTO product_discounts_by_branch 
-- (application_id, product_name_pattern, discount_percentage, active, description)
-- VALUES 
-- (5, '%salsa%', 10.00, 1, 'Descuento 10% en todas las salsas para Villa Alemana');
