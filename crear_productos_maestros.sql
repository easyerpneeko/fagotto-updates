-- ================================================
-- TABLA MAESTRA PARA PROMO INTERACTIVA
-- Base de datos: easyerp
-- Solo Pastas y Salsas - Simple y directo
-- ================================================

-- 1. Tabla de tipos de pasta
CREATE TABLE IF NOT EXISTS `promo_pasta_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `order_display` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de tipos de salsa
CREATE TABLE IF NOT EXISTS `promo_salsa_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `order_display` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- INSERTAR TIPOS DE PASTA
-- ================================================

INSERT INTO `promo_pasta_types` (`name`, `description`, `order_display`) VALUES
('Bigoli', 'Pasta gruesa estilo italiano', 1),
('Fetuccini', 'Pasta plana tipo fettuccine', 2);

-- ================================================
-- INSERTAR TIPOS DE SALSA
-- ================================================

INSERT INTO `promo_salsa_types` (`name`, `description`, `order_display`) VALUES
('Alfredo', 'Salsa blanca cremosa con queso parmesano', 1),
('Camarón', 'Salsa de mariscos con camarones frescos', 2),
('Champiñón', 'Salsa cremosa con champiñones', 3),
('Pollo Mostaza', 'Salsa de pollo con toque de mostaza', 4),
('Boloñesa', 'Salsa tradicional de carne con tomate', 5),
('Pesto', 'Salsa verde de albahaca con aceite de oliva', 6);

-- ================================================
-- CONSULTAS PARA EL FRONTEND
-- ================================================

-- Obtener todos los tipos de pasta activos
-- SELECT * FROM promo_pasta_types WHERE active = 1 ORDER BY order_display;

-- Obtener todos los tipos de salsa activos  
-- SELECT * FROM promo_salsa_types WHERE active = 1 ORDER BY order_display;

-- ================================================
-- NOTAS DE USO
-- ================================================
-- 1. El wizard primero muestra: promo_pasta_types (Bigoli o Fetuccini)
-- 2. Luego muestra: promo_salsa_types (las 6 salsas)
-- 3. Después consulta la DB del negocio para:
--    - Bebidas (categoría 3)
--    - Extras (categoría 7, etc)
