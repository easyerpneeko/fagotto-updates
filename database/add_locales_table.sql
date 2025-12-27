-- =====================================================
-- AGREGAR TABLA DE LOCALES CON APPID
-- =====================================================

USE asistencias;

-- Tabla de Locales (Sucursales)
CREATE TABLE IF NOT EXISTS locales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_id VARCHAR(50) UNIQUE NOT NULL COMMENT 'ID único del local (ej: AGU001, CON002)',
  nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del local',
  direccion VARCHAR(200) COMMENT 'Dirección completa',
  
  -- GPS del local
  latitud DECIMAL(10, 8) NOT NULL COMMENT 'Latitud GPS',
  longitud DECIMAL(11, 8) NOT NULL COMMENT 'Longitud GPS',
  radio_metros INT DEFAULT 50 COMMENT 'Radio de validación en metros',
  
  -- Contacto
  telefono VARCHAR(20),
  email VARCHAR(100),
  encargado VARCHAR(100),
  
  -- Control
  active BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_app_id (app_id),
  INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTAR LOCALES DE EJEMPLO
-- =====================================================

INSERT INTO locales (app_id, nombre, direccion, latitud, longitud, radio_metros, telefono, encargado, active) VALUES
('AGU001', 'Agustinas', 'Agustinas 1234, Santiago Centro', -33.4372, -70.6506, 50, '+56912345678', 'Juana Pérez', 1),
('CON001', 'Las Condes', 'Av. Apoquindo 5678, Las Condes', -33.4150, -70.5843, 50, '+56987654321', 'Miguel González', 1),
('PRO001', 'Providencia', 'Av. Providencia 999, Providencia', -33.4250, -70.6100, 50, '+56911222333', 'Ana Silva', 1),
('MAI001', 'Maipú', 'Pajaritos 3456, Maipú', -33.5088, -70.7644, 50, '+56944556677', 'Carlos Rojas', 1);

-- =====================================================
-- AGREGAR COLUMNA app_id A TABLAS EXISTENTES
-- =====================================================

-- Agregar app_id a checkin_sessions
ALTER TABLE checkin_sessions
ADD COLUMN app_id VARCHAR(50) COMMENT 'ID del local' AFTER session_id,
ADD INDEX idx_app_id (app_id),
ADD FOREIGN KEY (app_id) REFERENCES locales(app_id) ON DELETE CASCADE;

-- Agregar app_id y nombre_local a attendance_records
ALTER TABLE attendance_records
ADD COLUMN app_id VARCHAR(50) COMMENT 'ID del local' AFTER employee_id,
ADD COLUMN nombre_local VARCHAR(100) COMMENT 'Nombre del local' AFTER app_id,
ADD INDEX idx_app_id (app_id),
ADD FOREIGN KEY (app_id) REFERENCES locales(app_id) ON DELETE CASCADE;

-- =====================================================
-- VISTAS ACTUALIZADAS CON LOCALES
-- =====================================================

-- Vista: Asistencia de hoy por local
DROP VIEW IF EXISTS v_asistencia_hoy;
CREATE VIEW v_asistencia_hoy AS
SELECT 
  ar.id,
  ar.app_id,
  ar.nombre_local,
  ar.employee_id,
  ar.nombre,
  ar.cargo,
  DATE_FORMAT(ar.fecha_hora, '%H:%i:%s') as hora,
  ar.tipo,
  ar.coincidencia_facial,
  ar.validacion_exitosa,
  ar.gps_distancia_local
FROM attendance_records ar
WHERE DATE(ar.fecha_hora) = CURDATE()
ORDER BY ar.nombre_local, ar.fecha_hora DESC;

-- Vista: Resumen de asistencia por local HOY
CREATE OR REPLACE VIEW v_resumen_local_hoy AS
SELECT 
  ar.app_id,
  ar.nombre_local,
  COUNT(DISTINCT ar.employee_id) as total_empleados,
  COUNT(*) as total_registros,
  MIN(DATE_FORMAT(ar.fecha_hora, '%H:%i')) as primer_entrada,
  MAX(DATE_FORMAT(ar.fecha_hora, '%H:%i')) as ultima_entrada,
  AVG(ar.coincidencia_facial) as promedio_similitud
FROM attendance_records ar
WHERE DATE(ar.fecha_hora) = CURDATE()
  AND ar.validacion_exitosa = 1
GROUP BY ar.app_id, ar.nombre_local
ORDER BY ar.nombre_local;

-- Vista: Estadísticas mensuales por local
DROP VIEW IF EXISTS v_estadisticas_mes;
CREATE VIEW v_estadisticas_mes AS
SELECT 
  ar.app_id,
  ar.nombre_local,
  e.id as employee_id,
  e.nombre,
  e.cargo,
  COUNT(ar.id) as total_registros,
  COUNT(DISTINCT DATE(ar.fecha_hora)) as dias_asistidos,
  AVG(ar.coincidencia_facial) as promedio_similitud,
  MIN(DATE_FORMAT(ar.fecha_hora, '%H:%i')) as hora_mas_temprana,
  MAX(DATE_FORMAT(ar.fecha_hora, '%H:%i')) as hora_mas_tardia
FROM employees e
LEFT JOIN attendance_records ar ON e.id = ar.employee_id 
  AND MONTH(ar.fecha_hora) = MONTH(CURDATE())
  AND YEAR(ar.fecha_hora) = YEAR(CURDATE())
  AND ar.validacion_exitosa = 1
WHERE e.active = 1
GROUP BY ar.app_id, ar.nombre_local, e.id, e.nombre, e.cargo
ORDER BY ar.nombre_local, total_registros DESC;

-- =====================================================
-- STORED PROCEDURE: Obtener asistencia por local y fecha
-- =====================================================

DROP PROCEDURE IF EXISTS sp_asistencia_por_local;
DELIMITER //
CREATE PROCEDURE sp_asistencia_por_local(
  IN p_app_id VARCHAR(50),
  IN p_fecha_inicio DATE,
  IN p_fecha_fin DATE
)
BEGIN
  SELECT 
    ar.fecha_hora,
    ar.employee_id,
    ar.nombre,
    ar.cargo,
    ar.tipo,
    ar.coincidencia_facial,
    ar.validacion_exitosa,
    DATE_FORMAT(ar.fecha_hora, '%H:%i:%s') as hora
  FROM attendance_records ar
  WHERE ar.app_id = p_app_id
  AND DATE(ar.fecha_hora) BETWEEN p_fecha_inicio AND p_fecha_fin
  ORDER BY ar.fecha_hora DESC;
END //
DELIMITER ;

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

SELECT 
  'LOCALES CREADOS' as tipo,
  COUNT(*) as cantidad 
FROM locales
UNION ALL
SELECT 
  'EMPLEADOS' as tipo,
  COUNT(*) as cantidad 
FROM employees;

SELECT '✅ Tabla de locales creada - Sistema multi-sucursal listo' as mensaje;
