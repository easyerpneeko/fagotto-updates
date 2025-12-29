-- ============================================================
-- REINSTALACIÓN LIMPIA - Sistema de Asistencia
-- BORRA TODO Y CREA NUEVAS TABLAS
-- ============================================================

USE asistencias;

-- ============================================================
-- PASO 1: BORRAR TABLAS VIEJAS
-- ============================================================
DROP TABLE IF EXISTS asistencias_records;
DROP TABLE IF EXISTS asistencias_sessions;
DROP TABLE IF EXISTS asistencias_employees;
DROP TABLE IF EXISTS asistencias_locales;

-- ============================================================
-- PASO 2: CREAR TABLAS NUEVAS CON negocio_nombre
-- ============================================================

-- TABLA 1: Negocios
CREATE TABLE asistencias_locales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre del negocio',
  latitud DECIMAL(10, 8) DEFAULT -33.4372,
  longitud DECIMAL(11, 8) DEFAULT -70.6506,
  radio_metros INT DEFAULT 50,
  active BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA 2: Sesiones QR
CREATE TABLE asistencias_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(50) UNIQUE NOT NULL,
  negocio_nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del negocio',
  expires_at DATETIME NOT NULL,
  used BOOLEAN DEFAULT 0,
  used_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_session (session_id),
  INDEX idx_negocio (negocio_nombre),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA 3: Empleados
CREATE TABLE asistencias_employees (
  id VARCHAR(20) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  rut VARCHAR(12) NOT NULL UNIQUE,
  cargo VARCHAR(50),
  email VARCHAR(100),
  telefono VARCHAR(20),
  
  face_indexed BOOLEAN DEFAULT 0,
  face_id VARCHAR(100),
  face_confidence DECIMAL(5,2),
  foto_registro LONGTEXT,
  
  active BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_rut (rut),
  INDEX idx_face_indexed (face_indexed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLA 4: Registros de Asistencia
CREATE TABLE asistencias_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  
  negocio_nombre VARCHAR(100) NOT NULL COMMENT 'En qué negocio marcó',
  employee_id VARCHAR(20) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  cargo VARCHAR(50),
  
  fecha_hora DATETIME NOT NULL,
  tipo_marcacion VARCHAR(50) DEFAULT 'entrada',
  
  foto_capturada LONGTEXT,
  coincidencia_facial INT,
  
  gps_lat DECIMAL(10, 8),
  gps_lng DECIMAL(11, 8),
  gps_distancia_local DECIMAL(10, 2),
  
  validacion_exitosa BOOLEAN DEFAULT 1,
  notas TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_negocio_fecha (negocio_nombre, fecha_hora),
  INDEX idx_employee_fecha (employee_id, fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- VERIFICACION
-- ============================================================

SELECT 'TABLAS CREADAS CORRECTAMENTE' as status;
SHOW TABLES LIKE 'asistencias_%';
