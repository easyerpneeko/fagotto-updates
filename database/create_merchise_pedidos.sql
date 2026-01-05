-- ============================================================================
-- TABLA: merchise_pedidos
-- Descripción: Pedidos recibidos desde Mercadise con seguimiento por local
-- ============================================================================

CREATE TABLE IF NOT EXISTS `merchise_pedidos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` INT(11) NOT NULL COMMENT 'ID del local que recibió el pedido',
  `order_id_mercadise` VARCHAR(100) NOT NULL COMMENT 'ID del pedido en Mercadise',
  `platform` VARCHAR(50) NULL COMMENT 'Plataforma de venta: uber_eats, rappi, pedidosya, didi_food, cornershop, etc',
  `customer_name` VARCHAR(255) NULL COMMENT 'Nombre del cliente (OPCIONAL - algunos agregadores no lo envían)',
  `customer_phone` VARCHAR(50) NULL COMMENT 'Teléfono del cliente (OPCIONAL)',
  `customer_address` TEXT NULL COMMENT 'Dirección de entrega (OPCIONAL)',
  `customer_email` VARCHAR(255) NULL COMMENT 'Email del cliente (OPCIONAL)',
  `items` JSON NOT NULL COMMENT 'Productos del pedido en formato JSON',
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal antes de descuentos',
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto del descuento aplicado',
  `discount_type` VARCHAR(50) NULL COMMENT 'Tipo de descuento: percentage, fixed, coupon, promo',
  `discount_code` VARCHAR(100) NULL COMMENT 'Código del cupón o promoción aplicada',
  `discount_description` TEXT NULL COMMENT 'Descripción del descuento aplicado',
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total final del pedido (subtotal - descuento)',
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'pending, accepted, preparing, ready, delivered, cancelled',
  `payment_method` VARCHAR(50) NULL COMMENT 'Método de pago',
  `notes` TEXT NULL COMMENT 'Notas o instrucciones especiales del pedido',
  `received_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo llegó el pedido',
  `accepted_at` TIMESTAMP NULL COMMENT 'Cuándo se aceptó',
  `ready_at` TIMESTAMP NULL COMMENT 'Cuándo estuvo listo',
  `delivered_at` TIMESTAMP NULL COMMENT 'Cuándo se entregó',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id_mercadise` (`order_id_mercadise`),
  KEY `idx_app_id` (`app_id`),
  KEY `idx_platform` (`platform`),
  KEY `idx_status` (`status`),
  KEY `idx_received_at` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pedidos de Mercadise con seguimiento por local y plataforma';

-- ============================================================================
-- DATOS DE EJEMPLO
-- ============================================================================

INSERT INTO `merchise_pedidos` 
  (`app_id`, `order_id_mercadise`, `platform`, `customer_name`, `customer_phone`, `customer_address`, `items`, `subtotal`, `discount_amount`, `discount_type`, `discount_code`, `total`, `status`, `payment_method`) 
VALUES
  (
    5, 
    'MERC-12345', 
    'uber_eats',
    'Juan Pérez', 
    '+56912345678', 
    'Calle Falsa 123, Santiago',
    '{"products":[{"id":1,"name":"Pizza Margarita","quantity":2,"price":8990},{"id":2,"name":"Coca Cola","quantity":1,"price":1500}]}',
    19480,
    1500,
    'percentage',
    'PROMO10',
    17980,
    'pending',
    'online'
  ),
  (
    12, 
    'MERC-12346', 
    'rappi',
    NULL, 
    NULL, 
    NULL,
    '{"products":[{"id":1,"name":"Pizza Margarita","quantity":1,"price":8990}]}',
    8990,
    0,
    NULL,
    NULL,
    8990,
    'accepted',
    'online'
  ),
  (
    5, 
    'MERC-12347', 
    'pedidosya',
    'María González', 
    '+56987654321', 
    'Av. Principal 456, Valparaíso',
    '{"products":[{"id":1,"name":"Combo para 2","quantity":1,"price":15090}]}',
    15090,
    2000,
    'fixed',
    'VERANO2026',
    13090,
    'delivered',
    'online'
  );

-- ============================================================================
-- QUERY PARA VER VENTAS POR LOCAL
-- ============================================================================

SELECT 
    app_id AS 'Local',
    COUNT(*) AS 'Total Pedidos',
    SUM(total) AS 'Total Vendido',
    AVG(total) AS 'Ticket Promedio'
FROM merchise_pedidos
GROUP BY app_id
ORDER BY SUM(total) DESC;

-- ============================================================================
-- QUERY PARA VER VENTAS POR PLATAFORMA
-- ============================================================================

SELECT 
    COALESCE(platform, 'Sin plataforma') AS 'Plataforma',
    COUNT(*) AS 'Total Pedidos',
    SUM(subtotal) AS 'Subtotal',
    SUM(discount_amount) AS 'Total Descuentos',
    SUM(total) AS 'Total Vendido',
    AVG(total) AS 'Ticket Promedio'
FROM merchise_pedidos
WHERE status != 'cancelled'
GROUP BY platform
ORDER BY SUM(total) DESC;

-- ============================================================================
-- QUERY PARA VER DESCUENTOS APLICADOS
-- ============================================================================

SELECT 
    order_id_mercadise AS 'ID Pedido',
    platform AS 'Plataforma',
    discount_code AS 'Código',
    discount_type AS 'Tipo',
    subtotal AS 'Subtotal',
    discount_amount AS 'Descuento',
    total AS 'Total Final',
    CONCAT(ROUND((discount_amount / subtotal * 100), 1), '%') AS '% Desc',
    received_at AS 'Fecha'
FROM merchise_pedidos
WHERE discount_amount > 0
ORDER BY received_at DESC;

-- ============================================================================
-- QUERY PARA VER PEDIDOS DE HOY POR LOCAL
-- ============================================================================

SELECT 
    app_id AS 'Local',
    order_id_mercadise AS 'ID Pedido',
    customer_name AS 'Cliente',
    total AS 'Total',
    status AS 'Estado',
    received_at AS 'Recibido'
FROM merchise_pedidos
WHERE DATE(received_at) = CURDATE()
ORDER BY received_at DESC;

-- ============================================================================
-- QUERY PARA VER RANKING DE PRODUCTOS MÁS VENDIDOS
-- ============================================================================

SELECT 
    JSON_UNQUOTE(JSON_EXTRACT(item, '$.name')) AS 'Producto',
    SUM(JSON_UNQUOTE(JSON_EXTRACT(item, '$.quantity'))) AS 'Cantidad Vendida',
    SUM(JSON_UNQUOTE(JSON_EXTRACT(item, '$.quantity')) * JSON_UNQUOTE(JSON_EXTRACT(item, '$.price'))) AS 'Total Ingresos'
FROM merchise_pedidos,
JSON_TABLE(items, '$.products[*]' COLUMNS (item JSON PATH '$')) AS products
GROUP BY JSON_UNQUOTE(JSON_EXTRACT(item, '$.name'))
ORDER BY SUM(JSON_UNQUOTE(JSON_EXTRACT(item, '$.quantity'))) DESC;

-- ============================================================================
-- CAMPOS DE LA TABLA:
-- ============================================================================
-- id                  : Identificador único
-- app_id              : ID del local (1-22 en tu caso)
-- order_id_mercadise  : ID único del pedido en Mercadise
-- customer_name       : Nombre del cliente
-- customer_phone      : Teléfono del cliente
-- customer_address    : Dirección de entrega
-- items               : JSON con los productos [{id, name, quantity, price}]
-- total               : Total del pedido
-- status              : Estado del pedido
-- payment_method      : Forma de pago
-- received_at         : Cuándo llegó
-- accepted_at         : Cuándo se aceptó
-- ready_at            : Cuándo estuvo listo
-- delivered_at        : Cuándo se entregó
-- created_at          : Fecha creación
-- updated_at          : Fecha actualización
-- ============================================================================

-- ============================================================================
-- VENTAJAS:
-- ============================================================================
-- ✅ Sabes QUÉ LOCAL vendió cada pedido (app_id)
-- ✅ Puedes hacer reportes de ventas por local
-- ✅ Ves qué locales venden más
-- ✅ Historial completo de pedidos
-- ✅ Seguimiento de estados (pending → delivered)
-- ============================================================================
