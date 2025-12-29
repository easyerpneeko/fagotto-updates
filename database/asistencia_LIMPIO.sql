-- ============================================================
-- SISTEMA DE ASISTENCIA - INSTALACIÓN LIMPIA
-- Borra todo lo viejo y crea solo lo necesario
-- ============================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS asistencias 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE asistencias;

-- ============================================================
-- PASO 1: BORRAR TABLAS VIEJAS (si existen)
-- ============================================================
DROP TABLE IF EXISTS asistencias_records;
DROP TABLE IF EXISTS asistencias_sessions;
DROP TABLE IF EXISTS asistencias_employees;
DROP TABLE IF EXISTS asistencias_locales;
DROP TABLE IF EXISTS attendance_records;
DROP TABLE IF EXISTS checkin_sessions;
DROP TABLE IF EXISTS employees;

-- ============================================================
-- PASO 2: CREAR TABLAS LIMPIAS
-- ============================================================

-- TABLA 1: Negocios/Locales
CREATE TABLE asistencias_locales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre del negocio (Las Condes, Agustinas, etc)',
  latitud DECIMAL(10, 8) DEFAULT -33.4372,
  longitud DECIMAL(11, 8) DEFAULT -70.6506,
  radio_metros INT DEFAULT 50 COMMENT 'Radio de validación GPS en metros',
  active BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_nombre (nombre),
  INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 2: Sesiones de QR (temporales, 30 minutos)
CREATE TABLE asistencias_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(50) UNIQUE NOT NULL COMMENT 'ID único del QR',
  negocio_nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del negocio que generó el QR',
  expires_at DATETIME NOT NULL COMMENT 'Cuándo expira (30 min)',
  used BOOLEAN DEFAULT 0 COMMENT '¿Ya se usó?',
  used_at DATETIME COMMENT 'Cuándo se usó',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_session (session_id),
  INDEX idx_expires (expires_at),
  INDEX idx_negocio (negocio_nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 3: Empleados
CREATE TABLE asistencias_employees (
  id VARCHAR(20) PRIMARY KEY COMMENT 'ID del empleado (ej: EMP001)',
  nombre VARCHAR(100) NOT NULL,
  rut VARCHAR(12) NOT NULL UNIQUE COMMENT 'RUT sin puntos, con guión',
  cargo VARCHAR(50),
  email VARCHAR(100),
  telefono VARCHAR(20),
  
  -- AWS Rekognition
  face_indexed BOOLEAN DEFAULT 0 COMMENT '¿Rostro registrado en AWS?',
  face_id VARCHAR(100) COMMENT 'Face ID de AWS Rekognition',
  face_confidence DECIMAL(5,2) COMMENT 'Confianza del registro (0-100)',
  foto_registro LONGTEXT COMMENT 'Foto base64 del registro',
  
  active BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_active (active),
  INDEX idx_rut (rut),
  INDEX idx_face_indexed (face_indexed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 4: Registros de Asistencia
CREATE TABLE asistencias_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  
  -- Negocio y empleado
  negocio_nombre VARCHAR(100) NOT NULL COMMENT 'En qué negocio marcó',
  employee_id VARCHAR(20) NOT NULL COMMENT 'ID del empleado',
  nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del empleado (copia)',
  cargo VARCHAR(50),
  
  -- Fecha y tipo
  fecha_hora DATETIME NOT NULL COMMENT 'Cuándo marcó',
  tipo_marcacion VARCHAR(50) DEFAULT 'entrada' COMMENT 'entrada, salida, salida_colacion, regreso_colacion',
  
  -- Foto y reconocimiento
  foto_capturada LONGTEXT COMMENT 'Foto tomada en ese momento',
  coincidencia_facial INT COMMENT 'Porcentaje de similitud con AWS (0-100)',
  
  -- GPS
  gps_lat DECIMAL(10, 8) COMMENT 'Latitud donde marcó',
  gps_lng DECIMAL(11, 8) COMMENT 'Longitud donde marcó',
  gps_distancia_local DECIMAL(10, 2) COMMENT 'Distancia al local en metros',
  
  validacion_exitosa BOOLEAN DEFAULT 1,
  notas TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_negocio_fecha (negocio_nombre, fecha_hora),
  INDEX idx_employee_fecha (employee_id, fecha_hora),
  INDEX idx_fecha_hora (fecha_hora),
  INDEX idx_tipo (tipo_marcacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PASO 3: DATOS DE PRUEBA (OPCIONAL)
-- ============================================================

-- Agregar negocios de prueba
INSERT INTO asistencias_locales (nombre, latitud, longitud) VALUES
('Las Condes', -33.4167, -70.5833),
('Agustinas', -33.4372, -70.6506)
ON DUPLICATE KEY UPDATE nombre=nombre;

-- Empleado de prueba
INSERT INTO asistencias_employees (id, nombre, rut, cargo, active) VALUES
('EMP001', 'Juan Pérez', '12345678-9', 'Cajero', 1)
ON DUPLICATE KEY UPDATE nombre=nombre;

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
SELECT '✅ Sistema de Asistencia instalado correctamente' as mensaje;
SELECT 'Tablas creadas:' as info;
SHOW TABLES LIKE 'asistencias_%';
