-- ============================================
-- LIMPIAR TODO Y CREAR CATEGORÍAS SIMPLES
-- ============================================

-- Primero eliminar todos los productos y categorías existentes
DELETE FROM totem_productos;
DELETE FROM totem_categorias;

-- Resetear los auto_increment
ALTER TABLE totem_productos AUTO_INCREMENT = 1;
ALTER TABLE totem_categorias AUTO_INCREMENT = 1;

-- ============================================
-- CREAR SOLO 4 CATEGORÍAS SIMPLES
-- ============================================

INSERT INTO `totem_categorias` (`nombre`, `icono`, `color`, `orden`, `activo`) VALUES
('Pastas', 'fas fa-pizza-slice', '#dc3545', 1, 1),
('Bebidas', 'fas fa-glass-whiskey', '#0dcaf0', 2, 1),
('Promociones', 'fas fa-star', '#ffc107', 3, 1),
('Extras', 'fas fa-cheese', '#6c757d', 4, 1);

-- ============================================
-- PRODUCTOS ESPECIALES - PASTAS (Categoría 1)
-- ============================================

INSERT INTO `totem_productos` (`nombre`, `descripcion`, `precio`, `categoria_id`, `activo`, `orden`) VALUES
('Bigoli', 'Pasta artesanal italiana - Elige tu salsa favorita', 4990, 1, 1, 1),
('Fettuccine', 'Pasta fresca tradicional - Elige tu salsa favorita', 4990, 1, 1, 2);

-- ============================================
-- PRODUCTOS ESPECIALES - PROMOCIONES (Categoría 3)
-- ============================================

INSERT INTO `totem_productos` (`nombre`, `descripcion`, `precio`, `categoria_id`, `activo`, `orden`) VALUES
('Promoción Completa', 'Pasta + Salsa + Bebida + Extras', 5990, 3, 1, 1);

-- ============================================
-- BEBIDAS 350cc - VENTA INDIVIDUAL (Categoría 2)
-- ============================================

INSERT INTO `totem_productos` (`nombre`, `descripcion`, `precio`, `categoria_id`, `activo`, `orden`) VALUES
('Pepsi 350cc', 'Bebida gaseosa', 1500, 2, 1, 1),
('Pepsi Zero 350cc', 'Bebida gaseosa sin azúcar', 1500, 2, 1, 2),
('Crush 350cc', 'Bebida gaseosa sabor naranja', 1500, 2, 1, 3),
('7up 350cc', 'Bebida gaseosa lima-limón', 1500, 2, 1, 4),
('Kem 350cc', 'Bebida gaseosa', 1500, 2, 1, 5),
('Cachantun con gas 350cc', 'Agua mineral con gas', 1200, 2, 1, 6),
('Cachantun sin gas 350cc', 'Agua mineral sin gas', 1200, 2, 1, 7),
('Coca Cola 350cc', 'Bebida gaseosa', 1500, 2, 1, 8),
('Coca Cola Zero 350cc', 'Bebida gaseosa sin azúcar', 1500, 2, 1, 9),
('Fanta 350cc', 'Bebida gaseosa sabor naranja', 1500, 2, 1, 10),
('Sprite 350cc', 'Bebida gaseosa lima-limón', 1500, 2, 1, 11);

-- ============================================
-- SALSAS - PARA SELECCIÓN DE PASTAS (Categoría 4 - Extras)
-- ============================================

INSERT INTO `totem_productos` (`nombre`, `descripcion`, `precio`, `categoria_id`, `activo`, `orden`) VALUES
('ALFREDO', 'Salsa cremosa con queso parmesano', 3990, 4, 1, 1),
('BOLOÑESA', 'Salsa tradicional con carne molida', 3990, 4, 1, 2),
('CAMARÓN', 'Salsa de camarones', 4990, 4, 1, 3),
('CHAMPIÑÓN', 'Salsa de champiñones', 4990, 4, 1, 4),
('PESTO', 'Salsa de albahaca y piñones', 4990, 4, 1, 5),
('CREMA POLLO MOSTAZA', 'Salsa cremosa con pollo y mostaza', 4990, 4, 1, 6),
('Queso Extra', 'Porción adicional de queso', 500, 4, 1, 7),
('Salsa Extra', 'Porción adicional de salsa', 700, 4, 1, 8);

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
