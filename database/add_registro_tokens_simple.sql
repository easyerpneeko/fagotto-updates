-- ============================================
-- SCRIPT SIMPLE - EJECUTAR PASO A PASO
-- ============================================
-- Si hay algún error, simplemente continúa con el siguiente paso

-- PASO 1: Crear tabla de tokens
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

-- PASO 2a: Agregar columna registro_token
-- (Si da error "Duplicate column", es porque ya existe - continuar al siguiente paso)
ALTER TABLE employees ADD COLUMN registro_token VARCHAR(100) UNIQUE NULL;

-- PASO 2b: Agregar columna token_usado
-- (Si da error "Duplicate column", es porque ya existe - continuar al siguiente paso)
ALTER TABLE employees ADD COLUMN token_usado TINYINT(1) DEFAULT 0;

-- PASO 2c: Agregar columna token_fecha_uso
-- (Si da error "Duplicate column", es porque ya existe - continuar al siguiente paso)
ALTER TABLE employees ADD COLUMN token_fecha_uso DATETIME NULL;

-- PASO 3: Confirmar
COMMIT;

-- VERIFICACIÓN: Ejecutar para ver si todo se creó correctamente
SHOW TABLES LIKE 'registro_tokens';
DESCRIBE employees;
