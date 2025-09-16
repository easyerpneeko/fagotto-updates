-- Migración para agregar campo uber_payment_info a tabla sells
-- Ejecutar en cada base de datos de cliente

ALTER TABLE sells ADD COLUMN uber_payment_info TEXT NULL AFTER special_payment_info;

-- Comentario: Este campo almacena información JSON específica de pagos de Uber Eats
-- para diferenciación en reportes manteniendo la generación de boleta SII normal