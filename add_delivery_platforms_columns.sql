-- Script SQL para agregar columnas de información de plataformas de delivery
-- Ejecutar en la base de datos local del sistema

-- 1. Agregar columnas para información de pagos de plataformas
ALTER TABLE sells 
ADD COLUMN uber_payment_info TEXT NULL AFTER other_type,
ADD COLUMN rappi_payment_info TEXT NULL AFTER uber_payment_info,
ADD COLUMN pedidos_ya_payment_info TEXT NULL AFTER rappi_payment_info,
ADD COLUMN special_payment_info TEXT NULL AFTER pedidos_ya_payment_info;

-- 2. Modificar el ENUM de other_type para incluir todas las plataformas
ALTER TABLE sells 
MODIFY COLUMN other_type ENUM(
    'debito',
    'transferencia',
    'cheque',
    'banco',
    'amipass',
    'multicaja',
    'edenred',
    'convenio_empresa',
    'sodexo',
    'efectivo',
    'credito',
    'guia_despacho',
    'rappi',
    'junaeb',
    'uber',
    'uber_eats',
    'pedidos_ya',
    'pluxee',
    'banco_chile_20',
    'fagotto_10',
    'halloween_20'
) NULL;

-- Verificar que las columnas se agregaron correctamente
SHOW COLUMNS FROM sells LIKE '%payment_info';
