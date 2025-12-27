-- ============================================
-- SCRIPT COMPLETO - SISTEMA BIOMÉTRICO
-- ============================================
-- Base de datos: easyerp
-- Fecha: 26 de Diciembre, 2025

-- Crear tabla employees con todos los campos necesarios
CREATE TABLE IF NOT EXISTS employees (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    rut VARCHAR(20) NULL,
    cargo VARCHAR(100) NULL,
    email VARCHAR(255) NULL,
    telefono VARCHAR(20) NULL,
    foto_url TEXT NULL,
    face_indexed TINYINT(1) DEFAULT 0,
    face_id VARCHAR(255) NULL,
    registro_token VARCHAR(100) UNIQUE NULL,
    token_usado TINYINT(1) DEFAULT 0,
    token_fecha_uso DATETIME NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_rut (rut),
    INDEX idx_face_indexed (face_indexed),
    INDEX idx_registro_token (registro_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de tokens de registro
CREATE TABLE IF NOT EXISTS registro_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(100) UNIQUE NOT NULL,
    employee_id VARCHAR(50) NOT NULL,
    tipo_uso ENUM('registro', 'asistencia') NOT NULL DEFAULT 'registro',
    usado TINYINT(1) DEFAULT 0,
    usado_fecha DATETIME NULL,
    usado_ip VARCHAR(45) NULL,
    usado_user_agent TEXT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME NULL,
    INDEX idx_token (token),
    INDEX idx_employee (employee_id),
    INDEX idx_usado (usado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de asistencias
CREATE TABLE IF NOT EXISTS asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME NULL,
    hora_salida TIME NULL,
    tipo_marcacion ENUM('entrada', 'salida') NOT NULL,
    foto_verificacion TEXT NULL,
    confianza_rekognition DECIMAL(5,2) NULL,
    ubicacion VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    device_info TEXT NULL,
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee (employee_id),
    INDEX idx_fecha (fecha),
    INDEX idx_tipo (tipo_marcacion),
    UNIQUE KEY unique_employee_fecha_tipo (employee_id, fecha, tipo_marcacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE PRUEBA (Opcional - Comentar si no se necesita)
-- ============================================

-- Insertar empleados de prueba
INSERT IGNORE INTO employees (id, nombre, rut, cargo, email, activo) VALUES
('EMP001', 'Juan Pérez González', '12.345.678-9', 'Cajero', 'juan.perez@fagotto.cl', 1),
('EMP002', 'María González López', '98.765.432-1', 'Cocina', 'maria.gonzalez@fagotto.cl', 1),
('EMP003', 'Pedro Martínez Silva', '11.222.333-4', 'Jefe de Tienda', 'pedro.martinez@fagotto.cl', 1),
('EMP004', 'Ana Rodríguez Castro', '55.666.777-8', 'Cajero', 'ana.rodriguez@fagotto.cl', 1),
('EMP005', 'Carlos Sánchez Muñoz', '33.444.555-6', 'Cocina', 'carlos.sanchez@fagotto.cl', 1);

-- Confirmar cambios
COMMIT;

-- ============================================
-- VERIFICACIÓN
-- ============================================
SELECT 'Tabla employees creada' AS mensaje, COUNT(*) AS total FROM employees;
SELECT 'Tabla registro_tokens creada' AS mensaje, COUNT(*) AS total FROM registro_tokens;
SELECT 'Tabla asistencias creada' AS mensaje, COUNT(*) AS total FROM asistencias;

-- Ver estructura de employees
DESCRIBE employees;
