-- Tabla para historial de cambios de nombres en pedidofinal_precios
-- Ejecutar este SQL en la base de datos master

CREATE TABLE IF NOT EXISTS pedidofinal_nombre_history (
  id int(11) NOT NULL AUTO_INCREMENT,
  producto_id int(11) NOT NULL COMMENT 'ID del producto en pedidofinal_precios',
  nombre_anterior varchar(100) NOT NULL COMMENT 'Nombre anterior del producto',
  nombre_nuevo varchar(100) NOT NULL COMMENT 'Nombre nuevo del producto',
  usuario varchar(100) DEFAULT 'Sistema' COMMENT 'Usuario que realizo el cambio',
  fecha timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del cambio',
  PRIMARY KEY (id),
  KEY idx_producto_id (producto_id),
  KEY idx_fecha (fecha),
  KEY idx_producto_fecha (producto_id, fecha DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Historial de cambios de nombres de productos';
