-- Tabla para historial de cambios de precios en pedidofinal_precios
-- Ejecutar este SQL en la base de datos master

CREATE TABLE IF NOT EXISTS pedidofinal_precio_history (
  id int(11) NOT NULL AUTO_INCREMENT,
  producto_id int(11) NOT NULL COMMENT 'ID del producto en pedidofinal_precios',
  producto_nombre varchar(100) NOT NULL COMMENT 'Nombre del producto al momento del cambio',
  precio_anterior decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio anterior',
  precio_nuevo decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio nuevo',
  usuario varchar(100) DEFAULT 'Sistema' COMMENT 'Usuario que realizo el cambio',
  fecha timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del cambio',
  PRIMARY KEY (id),
  KEY idx_producto_id (producto_id),
  KEY idx_fecha (fecha),
  KEY idx_producto_fecha (producto_id, fecha DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Historial de cambios de precios por unidad';
