-- Crear tabla pedidofinal_precios en la base de datos MAESTRA (easyerp)
-- Esta tabla centraliza los productos disponibles para el sistema de Pedido Final

CREATE TABLE IF NOT EXISTS `pedidofinal_precios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto` varchar(255) NOT NULL COMMENT 'Nombre del producto',
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio unitario',
  `stock` decimal(10,2) DEFAULT NULL COMMENT 'Stock disponible (NULL = sin control de stock)',
  `unidad_medida` varchar(50) DEFAULT 'unidad' COMMENT 'Unidad de medida (ej: Bolsa 2k, Caja 500, kg, unidad)',
  `categoria` varchar(100) DEFAULT NULL COMMENT 'Categoría del producto',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción adicional del producto',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = activo, 0 = inactivo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_activo` (`activo`),
  KEY `idx_stock` (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo centralizado de productos para Pedido Final';

-- Índices adicionales para mejorar rendimiento
CREATE INDEX `idx_producto_activo` ON `pedidofinal_precios` (`producto`, `activo`);
CREATE INDEX `idx_categoria_activo` ON `pedidofinal_precios` (`categoria`, `activo`);

-- Insertar productos de ejemplo (ajusta según tu catálogo real)
INSERT INTO `pedidofinal_precios` (`producto`, `precio`, `stock`, `unidad_medida`, `categoria`, `activo`) VALUES
('Salsa Alfredo', 15000.00, 50, 'Bolsa 2k', 'Salsas', 1),
('Salsa Boloñesa', 14000.00, 45, 'Bolsa 2k', 'Salsas', 1),
('Salsa Camarón', 16000.00, 30, 'Bolsa 2k', 'Salsas', 1),
('Salsa Champiñón', 15500.00, 35, 'Bolsa 2k', 'Salsas', 1),
('Salsa Pesto', 17000.00, 25, 'Bolsa 2k', 'Salsas', 1),
('Salsa Queso Cheddar', 15800.00, 40, 'Bolsa 2k', 'Salsas', 1),
('Salsa Pollo Mostaza', 15200.00, 38, 'Bolsa 2k', 'Salsas', 1),
('Mezcla 2.9k', 8500.00, 100, 'Bolsa 2.9k', 'Harinas', 1),
('Harina', 7000.00, 120, 'Bolsa 2.9k', 'Harinas', 1),
('Vaso PP 470ml', 25000.00, 15, 'Caja 500', 'Desechables', 1),
('Sobre de tenedor', 12000.00, 20, 'Caja 500', 'Desechables', 1),
('Queso Parmesano', 18000.00, 25, 'Bolsa 1k', 'Lácteos', 1),
('Ciabatta Pesto', 1200.00, 50, 'unidad', 'Panadería', 1),
('Ciabatta Queso Crema Salame', 1300.00, 45, 'unidad', 'Panadería', 1),
('Ciabatta Aliato', 1100.00, 40, 'unidad', 'Panadería', 1),
('Papel Mantequilla', 5000.00, 10, 'unidad', 'Insumos', 1),
('Stickers Fagotto', 8000.00, 5, 'unidad', 'Marketing', 1),
('Bolsa Delivery Biodegradable', 15000.00, 30, 'unidad', 'Desechables', 1),
('Pasta seca Fettuccini', 8500.00, 80, 'kg', 'Pastas', 1);

-- Nota: Ajusta los productos, precios y stock según tu inventario real
