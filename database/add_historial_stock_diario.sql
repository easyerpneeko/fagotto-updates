-- CREAR TABLA HISTORIAL DE STOCK POR DÍA
-- Guarda cada registro de stock reportado por negocio

USE easyerp;

CREATE TABLE IF NOT EXISTS `historial_stock_diario` (
  `id`                  INT          NOT NULL AUTO_INCREMENT,
  `id_producto`         INT          NOT NULL COMMENT 'FK a pedidofinal_precios',
  `producto_nombre`     VARCHAR(100) NOT NULL COMMENT 'Nombre del producto al momento del registro',
  `id_negocio`          INT          NULL     DEFAULT NULL COMMENT 'ID del negocio/sucursal',
  `app_id`              VARCHAR(50)  NULL     DEFAULT NULL COMMENT 'App ID del negocio',
  `nombre_negocio`      VARCHAR(100) NULL     DEFAULT NULL COMMENT 'Nombre del negocio (ej: Merced, Providencia)',
  `cantidad_reportada`  DECIMAL(10,2) NOT NULL COMMENT 'Cantidad de stock que reportó el negocio',
  `unidad_medida`       VARCHAR(50)  NULL     DEFAULT NULL COMMENT 'Unidad de medida (kg, unidad, litros, etc)',
  `usuario`             VARCHAR(100) NULL     DEFAULT NULL COMMENT 'Usuario que hizo el registro',
  `observacion`         TEXT         NULL     DEFAULT NULL COMMENT 'Observaciones adicionales',
  `fecha_registro`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_producto`    (`id_producto`),
  KEY `idx_negocio`     (`id_negocio`),
  KEY `idx_app_id`      (`app_id`),
  KEY `idx_fecha`       (`fecha_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de stock diario por negocio y producto';

SELECT '✅ Tabla historial_stock_diario creada correctamente' as mensaje;
