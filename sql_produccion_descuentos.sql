-- ============================================
-- Script SQL para Base de Datos de PRODUCCIÓN
-- Sistema de Descuentos por Sucursal
-- ============================================

-- 1. CREAR LA TABLA product_discounts_by_branch
CREATE TABLE IF NOT EXISTS `product_discounts_by_branch` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID de la sucursal',
  `product_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID del producto',
  `discount_percentage` decimal(5,2) NOT NULL COMMENT 'Porcentaje de descuento (ej: 10.00 para 10%)',
  `start_date` date NOT NULL COMMENT 'Fecha de inicio del descuento',
  `end_date` date NOT NULL COMMENT 'Fecha de fin del descuento',
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `description` varchar(255) DEFAULT NULL COMMENT 'Descripción del descuento',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_discounts_by_branch_application_id_foreign` (`application_id`),
  KEY `product_discounts_by_branch_product_id_foreign` (`product_id`),
  KEY `idx_active_dates` (`active`, `start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. INSERTAR LOS 27 DESCUENTOS (Productos 18, 17, 19 en 9 sucursales)
-- Descuento: 10% Halloween - Válido hasta 31 de octubre 2025

-- Sucursal 77 (Fagotto Merced)
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(77, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(77, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(77, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 95
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(95, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(95, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(95, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 108
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(108, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(108, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(108, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 113
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(113, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(113, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(113, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 98
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(98, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(98, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(98, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 114
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(114, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(114, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(114, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 102
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(102, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(102, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(102, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 78
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(78, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(78, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(78, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- Sucursal 96
INSERT INTO `product_discounts_by_branch` (`application_id`, `product_id`, `discount_percentage`, `start_date`, `end_date`, `active`, `description`, `created_at`, `updated_at`) VALUES
(96, 18, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(96, 17, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW()),
(96, 19, 10.00, '2025-10-01', '2025-10-31', 1, 'Descuento Halloween 10%', NOW(), NOW());

-- 3. VERIFICAR LOS DATOS INSERTADOS
SELECT 
    COUNT(*) as total_descuentos,
    COUNT(DISTINCT application_id) as total_sucursales,
    COUNT(DISTINCT product_id) as total_productos
FROM product_discounts_by_branch
WHERE active = 1 
  AND start_date <= CURDATE() 
  AND end_date >= CURDATE();

-- 4. VERIFICAR DESCUENTOS PARA LA SUCURSAL MERCED (ID 77)
SELECT 
    pdb.id,
    pdb.application_id,
    a.Name as sucursal,
    pdb.product_id,
    p.Name as producto,
    pdb.discount_percentage as descuento,
    pdb.start_date as inicio,
    pdb.end_date as fin,
    pdb.active as activo
FROM product_discounts_by_branch pdb
LEFT JOIN aplications a ON pdb.application_id = a.Id
LEFT JOIN products p ON pdb.product_id = p.Id
WHERE pdb.application_id = 77
  AND pdb.active = 1
ORDER BY pdb.product_id;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
