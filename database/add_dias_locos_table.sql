-- Tabla para almacenar los detalles de productos "Días Locos"
-- Similar a merchise y colaciones, pero para el módulo de Días Locos

CREATE TABLE IF NOT EXISTS `dias_locos_products` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sell_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID de la venta',
  `product_sell_id` BIGINT UNSIGNED NULL COMMENT 'ID en products_sells',
  `base_product_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del producto base (categoría Emergencia)',
  `base_product_name` VARCHAR(255) NOT NULL COMMENT 'Nombre del producto base',
  `pasta_type` VARCHAR(50) NOT NULL COMMENT 'Tipo de pasta: fettuccine o bigoli',
  `pasta_name` VARCHAR(100) NOT NULL COMMENT 'Nombre de la pasta',
  `original_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Precio original del producto',
  `final_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Precio final cobrado',
  `app_id` INT UNSIGNED NOT NULL COMMENT 'ID de la aplicación/negocio',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_sell_id` (`sell_id`),
  INDEX `idx_base_product_id` (`base_product_id`),
  INDEX `idx_app_id` (`app_id`),
  
  FOREIGN KEY (`base_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Almacena los detalles de productos del módulo Días Locos';

-- Verificar creación
SELECT 'Tabla dias_locos_products creada correctamente' AS message;
