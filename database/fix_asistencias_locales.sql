-- Insertar locales faltantes en asistencias_locales
-- Ejecutar esto en la base de datos easyerp

-- Verificar si la tabla existe
CREATE TABLE IF NOT EXISTS asistencias_locales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_id VARCHAR(10) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    radio_metros INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar locales con coordenadas GPS reales
INSERT INTO asistencias_locales (app_id, nombre, latitud, longitud, radio_metros)
VALUES 
    ('FAC001', 'Facturacion Fagotto', 0.00000000, 0.00000000, 100),
    ('AGU001', 'Fagotto Agustinas', -33.44061300, -70.64901733, 100),
    ('AHU001', 'Fagotto Ahumada', -33.44281143, -70.65052816, 100),
    ('AMU001', 'Fagotto Amunategi', -33.44316240, -70.65609710, 100),
    ('BOM001', 'Fagotto Bombero Osa', -33.44128466, -70.65180888, 100),
    ('BUL001', 'Fagotto Bulnes', -33.44954290, -70.65283572, 100),
    ('ENC001', 'Fagotto Encomenderos', -33.41676104, -70.60205544, 12000),
    ('FOO001', 'Fagotto Food Truck', 0.00000000, 0.00000000, 100),
    ('IND001', 'Fagotto Independencia', -33.42136880, -70.65538715, 100),
    ('CON001', 'Fagotto Las Condes', -33.40375556, -70.55680698, 100),
    ('IMP001', 'Fagotto Mall Imperio', -33.43946038, -70.64866716, 100),
    ('MAN001', 'Fagotto Manuel Mont', -33.42906753, -70.61923681, 100),
    ('MER001', 'Fagotto Merced', -33.43769560, -70.64392252, 100),
    ('MON001', 'Fagotto Moneda', -33.44216330, -70.65219707, 100),
    ('PLZ001', 'Fagotto Plaza De Armas', -33.43848310, -70.65016982, 100),
    ('PUE001', 'Fagotto Puente Alto', -33.59929034, -70.57840032, 100),
    ('QUI001', 'Fagotto Quilin', 0.00000000, 0.00000000, 100),
    ('RAN001', 'Fagotto Rancagua', -34.16662783, -70.74091635, 100),
    ('ROS001', 'Fagotto Rosario norte', -33.40849752, -70.57093926, 100),
    ('SAN001', 'Fagotto San Francisco', -33.45208578, -70.64640130, 100),
    ('SUE001', 'Fagotto Suecia', -33.42062910, -70.60789592, 100),
    ('TUR001', 'Fagotto Turbus', -33.45328820, -70.68932472, 100),
    ('VER001', 'Fagotto Vergara', -33.02467618, -71.55267561, 100),
    ('VIN001', 'Fagotto Viña Del Mar', -33.02409501, -71.55791833, 100),
    ('TAG001', 'Trai i Pasti Agustina', 0.00000000, 0.00000000, 100),
    ('TBU001', 'Trai i Pasti Bulnes', 0.00000000, 0.00000000, 100)
ON DUPLICATE KEY UPDATE 
    nombre = VALUES(nombre),
    latitud = VALUES(latitud),
    longitud = VALUES(longitud),
    radio_metros = VALUES(radio_metros);

-- Verificar que se insertaron
SELECT * FROM asistencias_locales;
