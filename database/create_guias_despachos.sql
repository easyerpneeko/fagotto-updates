-- =========================================
-- SISTEMA DE GUIAS DE DESPACHO
-- =========================================
-- Este archivo crea todas las tablas necesarias para el módulo de Guías de Despacho
-- Incluye: guias_despachos y products_guia_despacho
-- =========================================

-- Tabla principal: guias_despachos
CREATE TABLE IF NOT EXISTS `guias_despachos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `gananciaTotal` decimal(16,2) DEFAULT NULL,
  `user` int(11) NOT NULL,
  `translado` int(11) DEFAULT NULL,
  `despacho` int(11) DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client` int(11) DEFAULT NULL,
  `guia_folio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancel` tinyint(1) DEFAULT '0',
  `trash` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guias_despachos_id_index` (`id`),
  KEY `guias_despachos_user_index` (`user`),
  KEY `guias_despachos_translado_index` (`translado`),
  KEY `guias_despachos_despacho_index` (`despacho`),
  KEY `guias_despachos_client_index` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de productos de guías de despacho: products_guia_despacho
CREATE TABLE IF NOT EXISTS `products_guia_despacho` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `price` decimal(16,2) NOT NULL,
  `gananciaTotal` decimal(16,2) DEFAULT NULL,
  `quantity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unitary_price` decimal(16,2) NOT NULL,
  `product` bigint(20) unsigned NOT NULL,
  `guia` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_guia_despacho_id_index` (`id`),
  KEY `products_guia_despacho_product_index` (`product`),
  KEY `products_guia_despacho_guia_index` (`guia`),
  CONSTRAINT `products_guia_despacho_product_foreign` FOREIGN KEY (`product`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_guia_despacho_guia_foreign` FOREIGN KEY (`guia`) REFERENCES `guias_despachos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- FIN SISTEMA DE GUIAS DE DESPACHO
-- =========================================
