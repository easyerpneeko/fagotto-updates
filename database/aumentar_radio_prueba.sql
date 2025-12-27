-- Ajustar radio GPS para prueba de validación
-- Encomenderos: establecer en 12km para alcanzar la ubicación de prueba

USE easyerp;

UPDATE asistencias_locales 
SET radio_metros = 12000 
WHERE app_id = 'ENC001';

-- Verificar el cambio
SELECT app_id, nombre, radio_metros 
FROM asistencias_locales 
WHERE app_id = 'ENC001';
