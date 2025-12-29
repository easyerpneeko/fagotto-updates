-- ====================================================================
-- MIGRACIÓN: Agregar coordenadas GPS a asistencias_locales
-- Radio de validación: 30 metros desde el local
-- Total locales: 22
-- ====================================================================

-- 1. Agregar columnas GPS (si ya existen, ejecutar igual los UPDATE)
ALTER TABLE asistencias_locales 
ADD COLUMN gps_lat DECIMAL(10, 8) NULL COMMENT 'Latitud del local';

ALTER TABLE asistencias_locales 
ADD COLUMN gps_lng DECIMAL(11, 8) NULL COMMENT 'Longitud del local';

-- ALTER TABLE asistencias_locales 
-- ADD COLUMN radio_metros INT DEFAULT 30 COMMENT 'Radio de validación en metros';
-- (Ya existe, comentado)

-- 2. Actualizar coordenadas de cada local
UPDATE asistencias_locales SET gps_lat = -33.43769560, gps_lng = -70.64392252 WHERE nombre LIKE '%Merced 461%';
UPDATE asistencias_locales SET gps_lat = -33.44061300, gps_lng = -70.64901733 WHERE nombre LIKE '%Agustinas 883%';
UPDATE asistencias_locales SET gps_lat = -33.43848310, gps_lng = -70.65016982 WHERE nombre LIKE '%Plaza de Armas 910%';
UPDATE asistencias_locales SET gps_lat = -33.45328820, gps_lng = -70.68932472 WHERE nombre LIKE '%Terminal Tur Bus%';
UPDATE asistencias_locales SET gps_lat = -33.42062910, gps_lng = -70.60789592 WHERE nombre LIKE '%Providencia 2319%';
UPDATE asistencias_locales SET gps_lat = -33.44954290, gps_lng = -70.65283572 WHERE nombre LIKE '%Paseo Bulnes 277%';
UPDATE asistencias_locales SET gps_lat = -33.41676104, gps_lng = -70.60205544 WHERE nombre LIKE '%Encomenderos 113%';
UPDATE asistencias_locales SET gps_lat = -33.44128466, gps_lng = -70.65180888 WHERE nombre LIKE '%Bombero Ossa 1067%';
UPDATE asistencias_locales SET gps_lat = -33.42906753, gps_lng = -70.61923681 WHERE nombre LIKE '%Barros Borgoño%';
UPDATE asistencias_locales SET gps_lat = -33.44281143, gps_lng = -70.65052816 WHERE nombre LIKE '%Paseo Ahumada 47%';
UPDATE asistencias_locales SET gps_lat = -33.40849752, gps_lng = -70.57093926 WHERE nombre LIKE '%Rosario Norte 41%';
UPDATE asistencias_locales SET gps_lat = -33.59929034, gps_lng = -70.57840032 WHERE nombre LIKE '%Concha y Toro 1184%';
UPDATE asistencias_locales SET gps_lat = -33.44216330, gps_lng = -70.65219707 WHERE nombre LIKE '%Moneda 1110%';
UPDATE asistencias_locales SET gps_lat = -33.02467618, gps_lng = -71.55267561 WHERE nombre LIKE '%Plaza Vergara 172%' OR nombre LIKE '%Viña%Vergara%';
UPDATE asistencias_locales SET gps_lat = -33.02409501, gps_lng = -71.55791833 WHERE nombre LIKE '%Valparaiso 335%';
UPDATE asistencias_locales SET gps_lat = -33.44316240, gps_lng = -70.65609710 WHERE nombre LIKE '%Amunategui 72%';
UPDATE asistencias_locales SET gps_lat = -33.45208578, gps_lng = -70.64640130 WHERE nombre LIKE '%San Francisco 602%';
UPDATE asistencias_locales SET gps_lat = -33.40375556, gps_lng = -70.55680698 WHERE nombre LIKE '%Las Condes 7253%';
UPDATE asistencias_locales SET gps_lat = -33.42136880, gps_lng = -70.65538715 WHERE nombre LIKE '%Independencia 851%';
UPDATE asistencias_locales SET gps_lat = -33.43946038, gps_lng = -70.64866716 WHERE nombre LIKE '%Imperio Mall%' OR nombre LIKE '%Vivo%';
UPDATE asistencias_locales SET gps_lat = -34.16662783, gps_lng = -70.74091635 WHERE nombre LIKE '%Rancagua%' OR nombre LIKE '%Cáceres%';

-- 3. Verificar que todos tengan coordenadas
SELECT nombre, gps_lat, gps_lng, radio_metros 
FROM asistencias_locales 
ORDER BY nombre;
