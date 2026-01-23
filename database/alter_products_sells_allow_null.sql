-- ===============================================
-- Script para permitir NULL en columna product
-- ===============================================
-- Este script modifica la tabla products_sells para permitir
-- que la columna 'product' sea NULL (necesario para merchise y colación)
-- ===============================================

-- Modificar columna product para permitir NULL
ALTER TABLE `products_sells` 
MODIFY COLUMN `product` bigint(20) UNSIGNED NULL;

-- Verificar el cambio
DESCRIBE `products_sells`;
