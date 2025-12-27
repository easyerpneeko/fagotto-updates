-- =====================================================
-- SISTEMA DE ASISTENCIA CON AWS REKOGNITION
-- Schema de Base de Datos
-- =====================================================

-- Crear base de datos MAESTRA centralizada
CREATE DATABASE IF NOT EXISTS asistencias 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE asistencias;

-- =====================================================
-- TABLA: employees (Empleados)
-- =====================================================
CREATE TABLE IF NOT EXISTS employees (
  id VARCHAR(20) PRIMARY KEY COMMENT 'ID del empleado (ej: EMP001)',
  nombre VARCHAR(100) NOT NULL COMMENT 'Nombre completo',
  rut VARCHAR(12) NOT NULL UNIQUE COMMENT 'RUT sin puntos, con guión',
  cargo VARCHAR(50) COMMENT 'Cargo o puesto',
  email VARCHAR(100) COMMENT 'Email corporativo',
  telefono VARCHAR(20) COMMENT 'Teléfono de contacto',
  
  -- AWS Rekognition
  face_indexed BOOLEAN DEFAULT 0 COMMENT '¿Rostro registrado en AWS?',
  face_id VARCHAR(100) COMMENT 'Face ID de AWS Rekognition',
  face_confidence DECIMAL(5,2) COMMENT 'Confianza del registro (0-100)',
  foto_registro LONGTEXT COMMENT 'Foto base64 del registro',
  face_registered_at DATETIME COMMENT 'Fecha de registro facial',
  
  -- Control
  active BOOLEAN DEFAULT 1 COMMENT 'Empleado activo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_active (active),
  INDEX idx_rut (rut),
  INDEX idx_face_indexed (face_indexed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: checkin_sessions (Sesiones de QR)
-- =====================================================
CREATE TABLE IF NOT EXISTS checkin_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(50) UNIQUE NOT NULL COMMENT 'ID único de sesión',
  
  -- Ubicación del local
  local_lat DECIMAL(10, 8) COMMENT 'Latitud del local',
  local_lng DECIMAL(11, 8) COMMENT 'Longitud del local',
  radius_meters INT DEFAULT 50 COMMENT 'Radio de validación (metros)',
  
  -- Control de sesión
  expires_at DATETIME NOT NULL COMMENT 'Fecha de expiración (5 minutos)',
  used BOOLEAN DEFAULT 0 COMMENT '¿Ya fue utilizada?',
  used_at DATETIME COMMENT 'Fecha de uso',
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_session (session_id),
  INDEX idx_expires (expires_at),
  INDEX idx_used (used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: attendance_records (Registros de Asistencia)
-- =====================================================
CREATE TABLE IF NOT EXISTS attendance_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  
  -- Relaciones
  session_id VARCHAR(50) COMMENT 'ID de sesión de QR',
  employee_id VARCHAR(20) NOT NULL COMMENT 'ID del empleado',
  
  -- Datos del empleado (desnormalizado para histórico)
  nombre VARCHAR(100) NOT NULL,
  cargo VARCHAR(50),
  
  -- Timestamp
  fecha_hora DATETIME NOT NULL COMMENT 'Fecha y hora del marcado',
  tipo ENUM('entrada', 'salida') DEFAULT 'entrada' COMMENT 'Tipo de registro',
  
  -- Foto capturada
  foto_capturada LONGTEXT COMMENT 'Foto base64 capturada en el momento',
  
  -- GPS
  gps_lat DECIMAL(10, 8) COMMENT 'Latitud del marcado',
  gps_lng DECIMAL(11, 8) COMMENT 'Longitud del marcado',
  gps_distancia_local DECIMAL(10, 2) COMMENT 'Distancia al local (metros)',
  
  -- AWS Rekognition
  coincidencia_facial INT COMMENT 'Porcentaje de similitud (0-100)',
  face_sdk_provider VARCHAR(50) DEFAULT 'aws-rekognition' COMMENT 'Proveedor de reconocimiento',
  face_sdk_response JSON COMMENT 'Respuesta completa del SDK',
  
  -- Metadata del dispositivo
  device_info JSON COMMENT 'Info del dispositivo (user agent, navegador, etc)',
  
  -- Control
  validacion_exitosa BOOLEAN DEFAULT 1 COMMENT '¿Validación exitosa?',
  notas TEXT COMMENT 'Observaciones o motivos de rechazo',
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  
  INDEX idx_employee_fecha (employee_id, fecha_hora),
  INDEX idx_fecha_hora (fecha_hora),
  INDEX idx_tipo (tipo),
  INDEX idx_validacion (validacion_exitosa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTAR DATOS DE PRUEBA
-- =====================================================

-- Empleado de prueba 1
INSERT INTO employees (id, nombre, rut, cargo, email, telefono, active) VALUES
('EMP001', 'Juan Pérez González', '12345678-9', 'Cajero', 'juan.perez@fagotto.cl', '+56912345678', 1);

-- Empleado de prueba 2
INSERT INTO employees (id, nombre, rut, cargo, email, telefono, active) VALUES
('EMP002', 'María González López', '98765432-1', 'Supervisor', 'maria.gonzalez@fagotto.cl', '+56987654321', 1);

-- Empleado de prueba 3
INSERT INTO employees (id, nombre, rut, cargo, email, telefono, active) VALUES
('EMP003', 'Pedro Sánchez Muñoz', '11222333-4', 'Bodeguero', 'pedro.sanchez@fagotto.cl', '+56911222333', 1);

-- =====================================================
-- VISTAS ÚTILES
-- =====================================================

-- Vista: Asistencia de hoy
CREATE OR REPLACE VIEW v_asistencia_hoy AS
SELECT 
  ar.id,
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
ORDER BY ar.fecha_hora DESC;

-- Vista: Empleados con rostro registrado
CREATE OR REPLACE VIEW v_empleados_registrados AS
SELECT 
  id,
  nombre,
  rut,
  cargo,
  face_indexed,
  face_confidence,
  face_registered_at,
  active
FROM employees
WHERE face_indexed = 1 AND active = 1
ORDER BY nombre;

-- Vista: Estadísticas de asistencia mensual
CREATE OR REPLACE VIEW v_estadisticas_mes AS
SELECT 
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
GROUP BY e.id, e.nombre, e.cargo
ORDER BY total_registros DESC;

-- =====================================================
-- FUNCIONES ÚTILES
-- =====================================================

-- Función: Limpiar sesiones expiradas
DELIMITER //
CREATE PROCEDURE sp_limpiar_sesiones_expiradas()
BEGIN
  DELETE FROM checkin_sessions 
  WHERE expires_at < NOW() 
  AND used = 0;
  
  SELECT ROW_COUNT() as sesiones_eliminadas;
END //
DELIMITER ;

-- Función: Obtener asistencia de un empleado en un rango
DELIMITER //
CREATE PROCEDURE sp_obtener_asistencia(
  IN p_employee_id VARCHAR(20),
  IN p_fecha_inicio DATE,
  IN p_fecha_fin DATE
)
BEGIN
  SELECT 
    DATE(fecha_hora) as fecha,
    DATE_FORMAT(fecha_hora, '%H:%i:%s') as hora,
    tipo,
    coincidencia_facial,
    gps_distancia_local,
    validacion_exitosa
  FROM attendance_records
  WHERE employee_id = p_employee_id
  AND DATE(fecha_hora) BETWEEN p_fecha_inicio AND p_fecha_fin
  ORDER BY fecha_hora ASC;
END //
DELIMITER ;

-- =====================================================
-- EVENT SCHEDULER (Limpieza automática)
-- =====================================================

-- Habilitar event scheduler
SET GLOBAL event_scheduler = ON;

-- Evento: Limpiar sesiones expiradas cada hora
CREATE EVENT IF NOT EXISTS evt_limpiar_sesiones
ON SCHEDULE EVERY 1 HOUR
DO
  CALL sp_limpiar_sesiones_expiradas();

-- =====================================================
-- PERMISOS (Opcional - para usuario específico)
-- =====================================================

-- Crear usuario para la aplicación (reemplaza 'tu_password')
-- CREATE USER 'fagotto_app'@'localhost' IDENTIFIED BY 'tu_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON fagotto_asistencia.* TO 'fagotto_app'@'localhost';
-- FLUSH PRIVILEGES;

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Verificar que todo se creó correctamente
SELECT 
  'TABLAS CREADAS' as tipo,
  COUNT(*) as cantidad 
FROM information_schema.tables 
WHERE table_schema = 'fagotto_asistencia'
UNION ALL
SELECT 
  'EMPLEADOS DE PRUEBA' as tipo,
  COUNT(*) as cantidad 
FROM employees
UNION ALL
SELECT 
  'VISTAS CREADAS' as tipo,
  COUNT(*) as cantidad 
FROM information_schema.views 
WHERE table_schema = 'fagotto_asistencia';

-- =====================================================
-- COMPLETADO
-- =====================================================
SELECT '✅ Base de datos creada exitosamente' as mensaje;
