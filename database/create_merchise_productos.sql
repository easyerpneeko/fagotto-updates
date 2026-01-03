-- ============================================================================
-- TABLA: merchise_productos (MENÚ MAESTRO)
-- Descripción: Menú único que se replica en todos los locales de Mercadise
-- ============================================================================

CREATE TABLE IF NOT EXISTS `merchise_productos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre del producto',
  `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio del producto',
  `categoria` VARCHAR(100) NOT NULL COMMENT 'Categoría del producto',
  `descripcion` TEXT NULL COMMENT 'Descripción detallada del producto',
  `foto` VARCHAR(500) NULL COMMENT 'URL o ruta de la foto del producto',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Visible en Mercadise, 0 = Oculto',
  `orden` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Menú maestro único para todos los locales en Mercadise';

-- ============================================================================
-- DATOS DE EJEMPLO (Opcional - puedes borrar esto)
-- ============================================================================

INSERT INTO `merchise_productos` 
  (`nombre`, `precio`, `categoria`, `descripcion`, `foto`, `activo`) 
VALUES
  ('Pizza Margarita', 8990, 'Pizzas', 'Pizza con salsa de tomate, mozzarella y albahaca fresca', 'https://ejemplo.com/pizza.jpg', 1),
  ('Pasta Bolognesa', 6990, 'Pastas', 'Pasta con salsa bolognesa tradicional', 'https://ejemplo.com/pasta.jpg', 1),
  ('Coca Cola 500ml', 1500, 'Bebidas', 'Bebida gaseosa', 'https://ejemplo.com/coca.jpg', 1),
  ('Ensalada César', 5990, 'Ensaladas', 'Lechuga, pollo, crutones y aderezo césar', 'https://ejemplo.com/ensalada.jpg', 1);

-- ============================================================================
-- VERIFICAR DATOS
-- ============================================================================

SELECT 
    id,
    nombre,
    CONCAT('$', FORMAT(precio, 0)) AS precio_formato,
    categoria,
    CASE WHEN activo = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
    created_at
FROM merchise_productos
ORDER BY categoria, nombre;

-- ============================================================================
-- CAMPOS DE LA TABLA:
-- ============================================================================
-- id             : Identificador único autoincremental
-- nombre         : Nombre del producto (máx 255 caracteres)
-- precio         : Precio del producto (decimal 10,2)
-- categoria      : Categoría del producto (máx 100 caracteres)
-- descripcion    : Descripción larga del producto (opcional)
-- foto           : URL o ruta de la imagen del producto (opcional)
-- activo         : 1 = visible en Mercadise, 0 = oculto
-- orden          : Número para ordenar productos (menor = primero)
-- created_at     : Fecha de creación (automática)
-- updated_at     : Fecha de actualización (automática)
-- ============================================================================

-- ============================================================================
-- NOTA IMPORTANTE:
-- Este es un MENÚ MAESTRO único que se replica IGUAL en todos los locales
-- Si activas/desactivas un producto, afecta a TODOS los negocios
-- ============================================================================
