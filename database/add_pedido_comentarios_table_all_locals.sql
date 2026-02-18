-- ============================================
-- Agregar tabla pedido_comentarios para historial de comentarios
-- Autor: Sistema Fagotto
-- Fecha: 2026-02-18
-- Descripción: Tabla para guardar historial completo de comentarios por pedido
-- ============================================

-- DB 1: faggotoo_agustina
USE erd_app_faggotoo_agustina_65a757ec101ac;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 2: faggotoo_plaza
USE erd_app_faggotoo_plaza_65a7ddfb3acd5;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_2` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 3: almacen_agustina
USE erd_app_almacen_agustina_65a92c854aae2;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_3` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 4: negocio_prueba
USE erd_app_negocio_prueba_65ae866348b4d;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_4` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 5: fagotto_merced
USE erd_app_fagotto_merced_65eaf8c2b8c16;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_5` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 6: kokoro_spa
USE erd_app_kokoro_spa_661ed74ec4469;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_6` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 7: facturacion_fafotto
USE erd_app_facturacion_fafotto_66420ee700c12;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_7` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 8: fagotto_ahumada
USE erd_app_fagotto_ahumada_6666af8b73317;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_8` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 9: firenze_spa
USE erd_app_firenze_spa_66aa50b6d3e63;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_9` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 10: fagotto_suecia
USE erd_app_fagotto_suecia_66abda54b5b14;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_10` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 11: fagotto_rosario_norte
USE erd_app_fagotto_rosario_norte_66ba7e785b9ae;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_11` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 12: fagotto_manuel_mont
USE erd_app_fagotto_manuel_mont_66c8afbb815f9;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_12` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 13: fagotto_independencia
USE erd_app_fagotto_independencia_66f5c521cd0ba;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_13` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 14: fagotto_turbus
USE erd_app_fagotto_turbus_671cdb235d0d1;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_14` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 15: rosario_norte_gelato
USE erd_app_rosario_norte_gelato_6789337030f12;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_15` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 16: fagotto_bulnes
USE erd_app_fagotto_bulnes_678a64c729636;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_16` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 17: fagotto_puente_alto
USE erd_app_fagotto_puente_alto_679a2099af4fd;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_17` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 18: trai_i_pasti
USE erd_app_trai_i_pasti_67b35bfe97f28;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_18` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 19: fagotto_quilin
USE erd_app_fagotto_quilin_67c6df873105a;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_19` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 20: fagotto_mall_imperio
USE erd_app_fagotto_mall_imperio_67dd6d487b0e2;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_20` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 21: fagotto_moneda
USE erd_app_fagotto_moneda_68091fd4e40a7;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_21` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 22: fagotto_vergara
USE erd_app_fagotto_vergara_682ccb8cb0ee6;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_22` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 23: fagotto_amunategi
USE erd_app_fagotto_amunategi_687aad4b29200;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_23` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 24: fagotto_italian
USE erd_app_fagotto_italian_68a79fde57307;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_24` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 25: fagotto_las_condes
USE erd_app_fagotto_las_condes_68b5ff81287c2;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_25` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 26: fagotto_rancagua
USE erd_app_fagotto_rancagua_68d4feced7dca;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_26` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 27: fagotto_san_francisco
USE erd_app_fagotto_san_francisco_68d4fedcac0ed;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_27` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 28: fagotto_food_truck
USE erd_app_fagotto_food_truck_6928b5232f4dd;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_28` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 29: espacio_causino
USE erd_app_espacio_causino_692dbcf185154;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_29` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 30: mustang
USE erd_app_mustang_692dce62ecf6f;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_30` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DB 31: zis_spa
USE erd_app_zis_spa_69500cc4b494b;
CREATE TABLE IF NOT EXISTS `pedido_comentarios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) unsigned NOT NULL,
  `user_name` varchar(255) DEFAULT NULL COMMENT 'Nombre del usuario que comentó',
  `user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID del usuario si está logueado',
  `comentario` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_comentarios_request_id_foreign` (`request_id`),
  CONSTRAINT `pedido_comentarios_request_id_foreign_31` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- RESUMEN DE MIGRACIÓN
-- ============================================
-- Total de bases de datos: 31
-- Tabla creada: pedido_comentarios
-- Campos:
--   - id (PK, auto_increment)
--   - request_id (FK a requests.id, CASCADE on delete)
--   - user_name (nombre del usuario que comentó)
--   - user_id (ID del usuario si está logueado)
--   - comentario (texto del comentario)
--   - created_at, updated_at (timestamps)
-- ============================================
-- FIN DE MIGRACIÓN
-- ============================================
