-- ============================================================================
-- MIGRACIÓN: Agregar columnas de plataforma y descuentos a merchise_pedidos
-- Fecha: 2026-01-04
-- ============================================================================

-- Verificar que la tabla existe
SELECT 'Agregando nuevas columnas a merchise_pedidos...' AS 'Status';

-- Agregar columna de plataforma (después de order_id_mercadise)
ALTER TABLE `merchise_pedidos` 
ADD COLUMN `platform` VARCHAR(50) NULL COMMENT 'Plataforma de venta: uber_eats, rappi, pedidosya, didi_food, cornershop, etc' 
AFTER `order_id_mercadise`;

-- Agregar columna de email del cliente (después de customer_address)
ALTER TABLE `merchise_pedidos` 
ADD COLUMN `customer_email` VARCHAR(255) NULL COMMENT 'Email del cliente (OPCIONAL)' 
AFTER `customer_address`;

-- Agregar columna de subtotal (antes de total)
ALTER TABLE `merchise_pedidos` 
ADD COLUMN `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal antes de descuentos' 
AFTER `items`;

-- Agregar columnas de descuento (después de subtotal)
ALTER TABLE `merchise_pedidos` 
ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto del descuento aplicado' 
AFTER `subtotal`;

ALTER TABLE `merchise_pedidos` 
ADD COLUMN `discount_type` VARCHAR(50) NULL COMMENT 'Tipo de descuento: percentage, fixed, coupon, promo' 
AFTER `discount_amount`;

ALTER TABLE `merchise_pedidos` 
ADD COLUMN `discount_code` VARCHAR(100) NULL COMMENT 'Código del cupón o promoción aplicada' 
AFTER `discount_type`;

ALTER TABLE `merchise_pedidos` 
ADD COLUMN `discount_description` TEXT NULL COMMENT 'Descripción del descuento aplicado' 
AFTER `discount_code`;

-- Agregar columna de notas (después de payment_method)
ALTER TABLE `merchise_pedidos` 
ADD COLUMN `notes` TEXT NULL COMMENT 'Notas o instrucciones especiales del pedido' 
AFTER `payment_method`;

-- Agregar índice para la columna platform
ALTER TABLE `merchise_pedidos` 
ADD KEY `idx_platform` (`platform`);

-- Actualizar comentarios de columnas existentes para dejar claro que son opcionales
ALTER TABLE `merchise_pedidos` 
MODIFY COLUMN `customer_name` VARCHAR(255) NULL COMMENT 'Nombre del cliente (OPCIONAL - algunos agregadores no lo envían)';

ALTER TABLE `merchise_pedidos` 
MODIFY COLUMN `customer_phone` VARCHAR(50) NULL COMMENT 'Teléfono del cliente (OPCIONAL)';

ALTER TABLE `merchise_pedidos` 
MODIFY COLUMN `customer_address` TEXT NULL COMMENT 'Dirección de entrega (OPCIONAL)';

ALTER TABLE `merchise_pedidos` 
MODIFY COLUMN `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total final del pedido (subtotal - descuento)';

-- Actualizar subtotal de pedidos existentes (copiar el valor de total)
UPDATE `merchise_pedidos` 
SET `subtotal` = `total` 
WHERE `subtotal` = 0;

SELECT 'Migración completada exitosamente!' AS 'Status';

-- ============================================================================
-- VERIFICAR CAMBIOS
-- ============================================================================

-- Ver estructura de la tabla actualizada
DESCRIBE `merchise_pedidos`;

-- Ver registros con las nuevas columnas
SELECT 
    id,
    order_id_mercadise,
    platform,
    customer_name,
    subtotal,
    discount_amount,
    total,
    status
FROM merchise_pedidos
LIMIT 5;

-- ============================================================================
-- CAMPOS AGREGADOS:
-- ============================================================================
-- platform              : Plataforma de venta (uber_eats, rappi, etc)
-- customer_email        : Email del cliente (opcional)
-- subtotal              : Subtotal antes de descuentos
-- discount_amount       : Monto del descuento aplicado
-- discount_type         : Tipo de descuento (percentage, fixed, coupon, promo)
-- discount_code         : Código del cupón o promoción
-- discount_description  : Descripción del descuento
-- notes                 : Notas o instrucciones especiales
-- ============================================================================
