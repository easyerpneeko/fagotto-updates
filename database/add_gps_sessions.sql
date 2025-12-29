-- Agregar columnas GPS a asistencias_sessions
-- Para guardar las coordenadas del local y validar distancia

-- Si las columnas ya existen, este ALTER dará error pero no afectará la BD
ALTER TABLE asistencias_sessions
ADD COLUMN gps_lat DECIMAL(10, 8) NULL COMMENT 'Latitud del local';

ALTER TABLE asistencias_sessions
ADD COLUMN gps_lng DECIMAL(11, 8) NULL COMMENT 'Longitud del local';

-- Verificar estructura
DESCRIBE asistencias_sessions;
