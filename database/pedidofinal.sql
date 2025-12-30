-- =============================================
-- TABLA DE PRECIOS CENTRALIZADOS - PEDIDO FINAL
-- Ejecutar en phpMyAdmin de easyerp (DB MAESTRA)
-- =============================================

USE easyerp;

-- TABLA: Precios de Productos
CREATE TABLE IF NOT EXISTS pedidofinal_precios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  producto VARCHAR(100) NOT NULL,
  unidad_venta DECIMAL(10,2) NOT NULL COMMENT 'Cantidad por unidad de venta',
  unidad_medida VARCHAR(50) NOT NULL COMMENT 'Bolsa, Caja, unidad, etc',
  precio_por_unidad DECIMAL(10,2) NOT NULL COMMENT 'Precio en pesos chilenos',
  categoria VARCHAR(50) DEFAULT 'general' COMMENT 'Categoría del producto',
  activo TINYINT DEFAULT 1,
  fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_producto (producto),
  INDEX idx_categoria (categoria),
  INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- DATOS INICIALES (según imagen proporcionada)
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo) VALUES
-- Productos de empaque
('Mezcla', 2.90, 'Bolsa', 11835, 'empaque', 1),
('Vaso', 500.00, 'Caja 500', 61000, 'empaque', 1),
('Sobre de tenedor', 500.00, 'Caja 500', 32400, 'empaque', 1),

-- Salsas y aderezos
('Salsa Boloñesa', 2.00, 'Bolsa 2k', 9604, 'salsas', 1),
('Queso', 1.00, 'Bolsa 1k', 10668, 'salsas', 1),
('Salsa Alfredo', 2.00, 'Bolsa 2k', 8603, 'salsas', 1),
('Salsa Camarón', 2.00, 'Bolsa 2k', 13840, 'salsas', 1),
('Salsa Champiñón', 2.00, 'Bolsa 2k', 13869, 'salsas', 1),
('Salsa Pesto', 2.00, 'Bolsa 2k', 37125, 'salsas', 1),

-- Ciabattas
('Ciabatta Pesto', 1.00, 'unidad', 1597, 'ciabatta', 1),
('Ciabatta Queso Crema Salame', 1.00, 'unidad', 1597, 'ciabatta', 1),
('Ciabatta Aliato', 1.00, 'unidad', 1080, 'ciabatta', 1),

-- Productos adicionales (sin datos en imagen)
('Salsa Queso Cheddar', 2.00, 'Bolsa 2k', 0, 'salsas', 0),
('Salsa Pollo Mostaza', 2.00, 'Bolsa 2k', 0, 'salsas', 0);

-- LISTO
SELECT '✅ Tabla pedidofinal_precios creada en easyerp' as mensaje;
SELECT '📊 Total de productos insertados:' as mensaje, COUNT(*) as total FROM pedidofinal_precios;

-- Consulta de verificación
SELECT 
  id,
  producto,
  unidad_venta,
  unidad_medida,
  FORMAT(precio_por_unidad, 0, 'es_CL') as precio,
  categoria,
  activo
FROM pedidofinal_precios
ORDER BY categoria, producto;
