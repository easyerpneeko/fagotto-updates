-- Agregar columnas de control de usos que el backend requiere
ALTER TABLE cupones 
ADD COLUMN usos_actuales INT DEFAULT 0 NOT NULL AFTER usado,
ADD COLUMN usos_maximos INT DEFAULT 1 NOT NULL AFTER usos_actuales,
ADD COLUMN fecha_inicio DATE NULL AFTER fecha_expiracion,
ADD COLUMN monto_minimo DECIMAL(10,2) NULL AFTER valor_descuento;

-- Verificar estructura
DESCRIBE cupones;
