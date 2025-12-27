-- Agregar sistema de tokens de un solo uso para registro biométrico
-- Fecha: 26 de Diciembre, 2025

-- ============================================
-- PASO 0: Crear tabla employees si no existe
-- ============================================
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
    activo TINYINT(1) DEFAULT 1,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_rut (rut),
    INDEX idx_face_indexed (face_indexed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PASO 1: Crear tabla para tokens de registro
-- ============================================
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

-- ============================================
-- PASO 2: Agregar columnas a employees
-- ============================================
-- NOTA: Si alguna columna ya existe, saltará error pero puedes continuar
-- Para evitar errores, ejecuta solo las columnas que no existan

-- Agregar columna registro_token (si ya existe, comentar esta línea)
ALTER TABLE employees ADD COLUMN registro_token VARCHAR(100) UNIQUE NULL;

-- Agregar columna token_usado (si ya existe, comentar esta línea)
ALTER TABLE employees ADD COLUMN token_usado TINYINT(1) DEFAULT 0;

-- Agregar columna token_fecha_uso (si ya existe, comentar esta línea)
ALTER TABLE employees ADD COLUMN token_fecha_uso DATETIME NULL;

-- ============================================
-- PASO 3: Confirmar cambios
-- ============================================
COMMIT;
