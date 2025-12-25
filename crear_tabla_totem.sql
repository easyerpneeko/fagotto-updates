-- ============================================
-- TABLA PRODUCTOS TOTEM
-- Base de datos única para todos los kioscos
-- ============================================

CREATE TABLE IF NOT EXISTS `totem_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(500),
  `categoria_id` int(11),
  `activo` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA CATEGORÍAS TOTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `totem_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(50) DEFAULT 'fas fa-utensils',
  `color` varchar(20) DEFAULT '#ffbc0d',
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- RELACIÓN: Productos con Categorías
-- ============================================

ALTER TABLE `totem_productos`
  ADD CONSTRAINT `fk_totem_categoria` 
  FOREIGN KEY (`categoria_id`) 
  REFERENCES `totem_categorias` (`id`) 
  ON DELETE SET NULL 
  ON UPDATE CASCADE;

-- ============================================
-- DATOS INICIALES - CATEGORÍAS
-- ============================================

INSERT INTO `totem_categorias` (`nombre`, `icono`, `color`, `orden`) VALUES
('Hamburguesas', 'fas fa-hamburger', '#dc3545', 1),
('Pizzas', 'fas fa-pizza-slice', '#fd7e14', 2),
('Bebidas', 'fas fa-glass-whiskey', '#0dcaf0', 3),
('Postres', 'fas fa-ice-cream', '#d63384', 4),
('Combos', 'fas fa-box', '#ffc107', 5);

-- ============================================
-- DATOS INICIALES - PRODUCTOS DE EJEMPLO
-- ============================================

INSERT INTO `totem_productos` (`nombre`, `descripcion`, `precio`, `categoria_id`, `orden`) VALUES
-- Hamburguesas
('Hamburguesa Clásica', 'Carne, lechuga, tomate, queso y salsa especial', 3990, 1, 1),
('Hamburguesa BBQ', 'Doble carne con salsa BBQ y cebolla caramelizada', 4990, 1, 2),
('Hamburguesa Veggie', 'Hamburguesa vegetariana con vegetales frescos', 3790, 1, 3),

-- Pizzas
('Pizza Pepperoni', 'Pepperoni, queso mozzarella y salsa de tomate', 5990, 2, 1),
('Pizza Hawaiana', 'Jamón, piña, queso mozzarella', 5990, 2, 2),
('Pizza Napolitana', 'Tomate, albahaca fresca y mozzarella', 5490, 2, 3),

-- Bebidas
('Coca Cola 500ml', 'Bebida gaseosa', 1500, 3, 1),
('Sprite 500ml', 'Bebida gaseosa de limón', 1500, 3, 2),
('Agua Mineral 500ml', 'Agua sin gas', 1200, 3, 3),
('Jugo Natural Naranja', 'Jugo 100% natural', 1990, 3, 4),

-- Postres
('Helado Chocolate', 'Copa de helado de chocolate', 2490, 4, 1),
('Torta Tres Leches', 'Porción individual', 2990, 4, 2),
('Brownie con Helado', 'Brownie caliente con helado', 3490, 4, 3),

-- Combos
('Combo Hamburguesa', 'Hamburguesa + Papas + Bebida', 5990, 5, 1),
('Combo Pizza Personal', 'Pizza personal + Bebida', 6990, 5, 2),
('Combo Familiar', '2 Hamburguesas + 2 Bebidas + Papas grandes', 12990, 5, 3);

-- ============================================
-- TABLA DE PEDIDOS TOTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `totem_pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_orden` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','procesando','completado','cancelado') DEFAULT 'pendiente',
  `tipo_pago` varchar(50) DEFAULT 'mercadopago',
  `order_id_mp` varchar(100),
  `items` text NOT NULL COMMENT 'JSON con los productos del pedido',
  `cliente_nombre` varchar(100),
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_orden` (`numero_orden`),
  KEY `idx_estado` (`estado`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA DE CONFIGURACIÓN TOTEM
-- ============================================

CREATE TABLE IF NOT EXISTS `totem_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text,
  `descripcion` varchar(255),
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuraciones iniciales
INSERT INTO `totem_config` (`clave`, `valor`, `descripcion`) VALUES
('nombre_negocio', 'Fagotto Totem', 'Nombre del negocio mostrado en el totem'),
('logo_url', '', 'URL del logo del negocio'),
('color_primario', '#ffbc0d', 'Color principal del tema'),
('color_secundario', '#dc3545', 'Color secundario del tema'),
('mensaje_bienvenida', '¡Bienvenido! Hacé tu pedido tocando los productos', 'Mensaje de bienvenida'),
('tiempo_inactividad', '60', 'Segundos antes de resetear (0 = deshabilitado)'),
('mostrar_imagenes', '1', 'Mostrar imágenes de productos (1=sí, 0=no)'),
('mercadopago_activo', '1', 'Habilitar pagos con Mercado Pago');

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
