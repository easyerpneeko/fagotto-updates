-- =============================================
-- TABLAS DE ASISTENCIAS EN DB MAESTRA: easyerp
-- Ejecutar en phpMyAdmin de easyerp
-- =============================================

USE easyerp;

-- TABLA 1: Locales (Sucursales)
CREATE TABLE IF NOT EXISTS asistencias_locales (
  app_id VARCHAR(50) PRIMARY KEY,
  id_local_original INT NULL COMMENT 'ID del local en el sistema ERP original',
  nombre VARCHAR(100) NOT NULL,
  latitud DECIMAL(10,8) NOT NULL,
  longitud DECIMAL(11,8) NOT NULL,
  radio_metros INT DEFAULT 50,
  active TINYINT DEFAULT 1
) ENGINE=InnoDB;

-- TABLA 2: Empleados
CREATE TABLE IF NOT EXISTS asistencias_employees (
  id VARCHAR(20) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  rut VARCHAR(12) UNIQUE,
  cargo VARCHAR(50),
  face_indexed TINYINT DEFAULT 0,
  face_id VARCHAR(100),
  foto_registro LONGTEXT,
  active TINYINT DEFAULT 1
) ENGINE=InnoDB;

-- TABLA 3: Sesiones QR
CREATE TABLE IF NOT EXISTS asistencias_sessions (
  session_id VARCHAR(50) PRIMARY KEY,
  app_id VARCHAR(50),
  expires_at DATETIME NOT NULL,
  used TINYINT DEFAULT 0,
  FOREIGN KEY (app_id) REFERENCES asistencias_locales(app_id)
) ENGINE=InnoDB;

-- TABLA 4: Registros de Asistencia
CREATE TABLE IF NOT EXISTS asistencias_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_id VARCHAR(50),
  nombre_local VARCHAR(100),
  employee_id VARCHAR(20),
  nombre VARCHAR(100),
  cargo VARCHAR(50),
  fecha_hora DATETIME NOT NULL,
  coincidencia_facial INT,
  foto_capturada LONGTEXT,
  gps_lat DECIMAL(10,8),
  gps_lng DECIMAL(11,8),
  INDEX idx_fecha (fecha_hora),
  INDEX idx_local (app_id),
  FOREIGN KEY (app_id) REFERENCES asistencias_locales(app_id),
  FOREIGN KEY (employee_id) REFERENCES asistencias_employees(id)
) ENGINE=InnoDB;

-- DATOS INICIALES
INSERT INTO asistencias_locales VALUES
('AGU001', 'Agustinas', -33.4372, -70.6506, 50, 1),
('CON001', 'Las Condes', -33.4150, -70.5843, 50, 1);

INSERT INTO asistencias_employees VALUES
('EMP001', 'Juan Pérez', '12345678-9', 'Cajero', 0, NULL, NULL, 1),
('EMP002', 'María González', '98765432-1', 'Supervisor', 0, NULL, NULL, 1);

-- LISTO
SELECT '✅ Tablas de asistencias creadas en easyerp' as mensaje;
