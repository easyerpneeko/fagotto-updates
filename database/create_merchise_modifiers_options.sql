-- ============================================================================
-- NUEVAS TABLAS PARA MODIFIERS Y OPTIONS DE MERCHISE
-- ============================================================================

-- ============================================================================
-- TABLA 1: merchise_sections (Categorías/Secciones del menú)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `merchise_sections` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(50) NOT NULL COMMENT 'SKU único de la sección',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nombre de la sección (Combos, Pastas, Bebidas, etc)',
  `description` TEXT NULL COMMENT 'Descripción de la sección',
  `image` VARCHAR(500) NULL COMMENT 'URL de imagen de la sección',
  `orden` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Activa, 0 = Oculta',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_activo` (`activo`),
  KEY `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Secciones/Categorías del menú Merchise';

-- ============================================================================
-- TABLA 2: merchise_items (Productos con posibilidad de modifiers)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `merchise_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID de la sección a la que pertenece',
  `sku` VARCHAR(50) NOT NULL COMMENT 'SKU único del producto',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nombre del producto',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio del producto en CLP',
  `description` TEXT NULL COMMENT 'Descripción del producto',
  `image` VARCHAR(500) NULL COMMENT 'URL de imagen del producto',
  `available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Disponible, 0 = No disponible',
  `calories` INT(11) NULL COMMENT 'Calorías del producto',
  `preparation_time` INT(11) NULL COMMENT 'Tiempo de preparación en minutos',
  `tags` JSON NULL COMMENT 'Etiquetas del producto ["popular", "vegetariano"]',
  `orden` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden dentro de la sección',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_section_id` (`section_id`),
  KEY `idx_available` (`available`),
  KEY `idx_orden` (`orden`),
  CONSTRAINT `fk_items_section` FOREIGN KEY (`section_id`) REFERENCES `merchise_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Items/Productos del menú con soporte para modifiers';

-- ============================================================================
-- TABLA 3: merchise_modifiers (Modificadores: Elige tu pasta, bebida, etc)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `merchise_modifiers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del item al que pertenece',
  `sku` VARCHAR(50) NOT NULL COMMENT 'SKU único del modificador',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nombre del modificador (Elige tu pasta)',
  `description` TEXT NULL COMMENT 'Descripción del modificador',
  `required` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Obligatorio, 0 = Opcional',
  `min` INT(11) NOT NULL DEFAULT 0 COMMENT 'Mínimo de opciones que debe elegir',
  `max` INT(11) NOT NULL DEFAULT 1 COMMENT 'Máximo de opciones que puede elegir',
  `orden` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_orden` (`orden`),
  CONSTRAINT `fk_modifiers_item` FOREIGN KEY (`item_id`) REFERENCES `merchise_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Modificadores de productos (Elige tu pasta, bebida, etc)';

-- ============================================================================
-- TABLA 4: merchise_options (Opciones de cada modificador)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `merchise_options` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `modifier_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del modificador al que pertenece',
  `sku` VARCHAR(50) NOT NULL COMMENT 'SKU único de la opción',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nombre de la opción (Pasta Pesto)',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio adicional (0 si incluido)',
  `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT 'Cantidad de unidades que incluye',
  `description` TEXT NULL COMMENT 'Descripción de la opción',
  `available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Disponible, 0 = No disponible',
  `default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Preseleccionada, 0 = No',
  `orden` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_modifier_id` (`modifier_id`),
  KEY `idx_available` (`available`),
  KEY `idx_orden` (`orden`),
  CONSTRAINT `fk_options_modifier` FOREIGN KEY (`modifier_id`) REFERENCES `merchise_modifiers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Opciones de cada modificador';

-- ============================================================================
-- TABLA 5: merchise_pedidos_items (Desglose de items del pedido)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `merchise_pedidos_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del pedido',
  `item_sku` VARCHAR(50) NOT NULL COMMENT 'SKU del producto',
  `item_name` VARCHAR(255) NOT NULL COMMENT 'Nombre del producto',
  `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT 'Cantidad',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio unitario',
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Descuento aplicado al item',
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal (quantity * price - discount)',
  `notes` TEXT NULL COMMENT 'Notas especiales del item',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pedido_id` (`pedido_id`),
  KEY `idx_item_sku` (`item_sku`),
  CONSTRAINT `fk_pedidos_items_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `merchise_pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Desglose de productos en cada pedido';

-- ============================================================================
-- TABLA 6: merchise_pedidos_modifiers (Modifiers seleccionados en el pedido)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `merchise_pedidos_modifiers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_item_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del item del pedido',
  `modifier_sku` VARCHAR(50) NOT NULL COMMENT 'SKU del modificador',
  `modifier_name` VARCHAR(255) NOT NULL COMMENT 'Nombre del modificador',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pedido_item_id` (`pedido_item_id`),
  CONSTRAINT `fk_pedidos_modifiers_item` FOREIGN KEY (`pedido_item_id`) REFERENCES `merchise_pedidos_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Modificadores seleccionados en cada item del pedido';

-- ============================================================================
-- TABLA 7: merchise_pedidos_options (Opciones seleccionadas del modifier)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `merchise_pedidos_options` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_modifier_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID del modifier del pedido',
  `option_sku` VARCHAR(50) NOT NULL COMMENT 'SKU de la opción',
  `option_name` VARCHAR(255) NOT NULL COMMENT 'Nombre de la opción',
  `quantity` INT(11) NOT NULL DEFAULT 1 COMMENT 'Cantidad seleccionada',
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio adicional',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pedido_modifier_id` (`pedido_modifier_id`),
  CONSTRAINT `fk_pedidos_options_modifier` FOREIGN KEY (`pedido_modifier_id`) REFERENCES `merchise_pedidos_modifiers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Opciones seleccionadas en cada modificador del pedido';

-- ============================================================================
-- DATOS DE EJEMPLO
-- ============================================================================

-- 1. Crear sección de Combos
INSERT INTO `merchise_sections` (`sku`, `name`, `description`, `orden`, `activo`) VALUES
('1', 'Combos', 'Nuestros combos más populares', 1, 1),
('41', 'Pastas Bigoli', 'Pasta artesanal italiana fresca', 2, 1),
('55', 'Bebidas', 'Bebidas refrescantes', 3, 1);

-- 2. Crear item: Combo para 2
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `available`, `calories`, `preparation_time`, `tags`, `orden`) VALUES
(1, '15.0', 'Combo para 2', 15090.00, '2 Pastas y 2 bebidas de 350 ml a elegir.', 1, 1200, 20, '["popular", "combo", "para-compartir"]', 1);

-- 3. Crear modifier: Elige tus pastas
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `description`, `required`, `min`, `max`, `orden`) VALUES
(1, 'mod-pasta-combo2', 'Elige tus pastas (2)', 'Selecciona 2 pastas de tu preferencia', 1, 2, 2, 1);

-- 4. Crear options de pastas
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `quantity`, `description`, `available`, `default`, `orden`) VALUES
(1, 'opt-pesto', 'Pasta Pesto', 0.00, 1, 'Salsa de albahaca fresca', 1, 0, 1),
(1, 'opt-alfredo', 'Pasta Alfredo', 0.00, 1, 'Cremosa salsa blanca', 1, 0, 2),
(1, 'opt-bolognesa', 'Pasta Bolognesa', 0.00, 1, 'Salsa de carne tradicional', 1, 0, 3),
(1, 'opt-champinon', 'Pasta Champiñón', 0.00, 1, 'Cremosa con champiñones', 1, 0, 4);

-- 5. Crear modifier: Elige tus bebidas
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `description`, `required`, `min`, `max`, `orden`) VALUES
(1, 'mod-bebida-combo2', 'Elige tus bebidas (2)', 'Selecciona 2 bebidas de 350ml', 1, 2, 2, 2);

-- 6. Crear options de bebidas
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `quantity`, `available`, `default`, `orden`) VALUES
(2, 'opt-cocacola', 'Coca-Cola Original', 0.00, 1, 1, 0, 1),
(2, 'opt-sprite', 'Sprite Original', 0.00, 1, 1, 0, 2),
(2, 'opt-sprite-zero', 'Sprite Sin Azúcar', 0.00, 1, 1, 0, 3),
(2, 'opt-fanta', 'Fanta Naranja', 0.00, 1, 1, 0, 4);

-- ============================================================================
-- QUERIES DE VERIFICACIÓN
-- ============================================================================

-- Ver menú completo con estructura
SELECT 
    s.name AS 'Sección',
    i.sku AS 'SKU Item',
    i.name AS 'Item',
    i.price AS 'Precio',
    COUNT(DISTINCT m.id) AS 'Modifiers',
    COUNT(o.id) AS 'Options Total'
FROM merchise_sections s
LEFT JOIN merchise_items i ON s.id = i.section_id
LEFT JOIN merchise_modifiers m ON i.id = m.item_id
LEFT JOIN merchise_options o ON m.id = o.modifier_id
WHERE i.available = 1
GROUP BY s.id, i.id
ORDER BY s.orden, i.orden;

-- Ver detalle de un item con sus modifiers y options
SELECT 
    i.name AS 'Item',
    m.name AS 'Modifier',
    m.required AS 'Requerido',
    CONCAT(m.min, ' a ', m.max) AS 'Rango',
    o.sku AS 'Option SKU',
    o.name AS 'Option',
    o.price AS 'Precio Extra',
    o.quantity AS 'Cantidad'
FROM merchise_items i
JOIN merchise_modifiers m ON i.id = m.item_id
JOIN merchise_options o ON m.id = o.modifier_id
WHERE i.sku = '15.0'
ORDER BY m.orden, o.orden;

-- Ver pedidos con su desglose completo
SELECT 
    p.order_id_mercadise AS 'ID Pedido',
    p.customer_name AS 'Cliente',
    pi.item_name AS 'Item',
    pm.modifier_name AS 'Modifier',
    GROUP_CONCAT(po.option_name SEPARATOR ', ') AS 'Opciones Elegidas',
    p.total AS 'Total'
FROM merchise_pedidos p
JOIN merchise_pedidos_items pi ON p.id = pi.pedido_id
LEFT JOIN merchise_pedidos_modifiers pm ON pi.id = pm.pedido_item_id
LEFT JOIN merchise_pedidos_options po ON pm.id = po.pedido_modifier_id
GROUP BY p.id, pi.id, pm.id
ORDER BY p.received_at DESC;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
