-- Insertar todos los locales de Fagotto con APP_ID de 3 letras
-- Coordenadas GPS tomadas del sistema web de tiendas
-- Radio de 100 metros para margen de error

INSERT INTO asistencias_locales (app_id, id_local_original, nombre, latitud, longitud, radio_metros) VALUES
('AGU001', 58, 'Fagotto Agustinas', -33.440613, -70.64901733, 100),
('PLZ001', 59, 'Fagotto Plaza De Armas', -33.4384831, -70.65016982, 100),
('MER001', 77, 'Fagotto Merced', -33.4376956, -70.64392252, 100),
('ENC001', 78, 'Fagotto Encomenderos', -33.41676104, -70.60205544, 100),
('FAC001', 79, 'Facturacion Fagotto', 0, 0, 100),
('AHU001', 86, 'Fagotto Ahumada', -33.44281143, -70.65052816, 100),
('BOM001', 95, 'Fagotto Bombero Osa', -33.44128466, -70.65180888, 100),
('SUE001', 96, 'Fagotto Suecia', -33.4206291, -70.60789592, 100),
('ROS001', 97, 'Fagotto Rosario norte', -33.40849752, -70.57093926, 100),
('MAN001', 98, 'Fagotto Manuel Mont', -33.42906753, -70.61923681, 100),
('IND001', 100, 'Fagotto Independencia', -33.42136880, -70.65538715, 100),
('TUR001', 102, 'Fagotto Turbus', -33.4532882, -70.68932472, 100),
('TBU001', 106, 'Trai i Pasti Bulnes', 0, 0, 100),
('BUL001', 107, 'Fagotto Bulnes', -33.4495429, -70.65283572, 100),
('PUE001', 108, 'Fagotto Puente Alto', -33.59929034, -70.57840032, 100),
('TAG001', 109, 'Trai i Pasti Agustina', 0, 0, 100),
('QUI001', 110, 'Fagotto Quilin', 0, 0, 100),
('IMP001', 111, 'Fagotto Mall Imperio', -33.43946038, -70.64866716, 100),
('MON001', 112, 'Fagotto Moneda', -33.44216330, -70.65219707, 100),
('VER001', 113, 'Fagotto Vergara', -33.02467618, -71.55267561, 100),
('AMU001', 114, 'Fagotto Amunategi', -33.4431624, -70.6560971, 100),
('VIN001', 115, 'Fagotto Viña Del Mar', -33.02409501, -71.55791833, 100),
('CON001', 116, 'Fagotto Las Condes', -33.40375556, -70.55680698, 100),
('RAN001', 117, 'Fagotto Rancagua', -34.16662783, -70.74091635, 100),
('SAN001', 118, 'Fagotto San Francisco', -33.45208578, -70.64640130, 100),
('FOO001', 119, 'Fagotto Food Truck', 0, 0, 100);

-- Ver todos los locales insertados
SELECT app_id, nombre, latitud, longitud, radio_metros FROM asistencias_locales ORDER BY app_id;
