-- ============================================
-- Agregar tabla pedido_comentarios para historial de comentarios
-- Autor: Sistema Fagotto
-- Fecha: 2026-02-17
-- Descripción: Tabla para guardar historial completo de comentarios por pedido
-- ============================================

-- NEGOCIO 1
USE erd_app_agustinas_31ksF4;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 2
USE erd_app_antonio_v_31Gsa9;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 3
USE erd_app_arauco_31P73k;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 4
USE erd_app_barrio_u_31K2Nh;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 5
USE erd_app_bellavista_31A4Ky;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 6
USE erd_app_biobio_31Y8Qj;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 7
USE erd_app_bustamante_31Lm2p;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 8
USE erd_app_conce_31T9Pm;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 9
USE erd_app_coquimbo_31B5Wn;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 10
USE erd_app_costanera_31D8Xw;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 11
USE erd_app_estacion_c_31V7Zq;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 12
USE erd_app_huerfanos_31M2Cp;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 13
USE erd_app_iquique_31Q5Rs;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 14
USE erd_app_la_serena_31H9Jt;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 15
USE erd_app_las_condes_31N4Km;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 16
USE erd_app_linares_31W6Tv;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 17
USE erd_app_mall_plaza_a_31S8Zx;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 18
USE erd_app_mall_tottus_pu_31F3Lw;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 19
USE erd_app_osorno_31G7Yp;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 20
USE erd_app_parque_a_31J9Ks;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 21
USE erd_app_paseo_a_31P4Nm;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 22
USE erd_app_portal_t_31R6Hq;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 23
USE erd_app_providencia_31X2Bt;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 24
USE erd_app_puerto_m_31Z5Dv;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 25
USE erd_app_puente_alto_31L8Fw;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 26
USE erd_app_quinta_31C4Gx;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 27
USE erd_app_rancagua_31U9Jp;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 28
USE erd_app_san_bernardo_31E7Kz;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 29
USE erd_app_tobalaba_31I6Lm;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 30
USE erd_app_valdivia_31O3Mw;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEGOCIO 31
USE erd_app_vina_del_m_31A1Nx;
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
  CONSTRAINT `pedido_comentarios_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FIN DE MIGRACIÓN
-- ============================================
