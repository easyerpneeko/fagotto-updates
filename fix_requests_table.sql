-- Script para agregar las columnas faltantes en la tabla requests
-- Esto soluciona el error: Column not found: 1054 Unknown column 'payment_method'

-- Agregar payment_method a la tabla requests
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(255) DEFAULT 'contado' AFTER paymode;

-- Agregar invoice_type a la tabla requests  
ALTER TABLE requests ADD COLUMN invoice_type VARCHAR(255) DEFAULT 'ticket' AFTER payment_method;

-- También para requests_reposteria si existe
ALTER TABLE requests_reposteria ADD COLUMN payment_method VARCHAR(255) DEFAULT 'contado' AFTER paymode;
ALTER TABLE requests_reposteria ADD COLUMN invoice_type VARCHAR(255) DEFAULT 'ticket' AFTER payment_method;

-- Verificar que las columnas se agregaron correctamente
DESCRIBE requests;
