-- Agregar sistema de tokens de un solo uso para registro biométrico
-- Fecha: 26 de Diciembre, 2025
-- Versión: 1.1 (Compatible con todas las versiones de MySQL)

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

-- Verificar si la columna 'registro_token' NO existe y agregarla
SET @dbname = DATABASE();
SET @tablename = 'employees';
SET @columnname = 'registro_token';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1", -- La columna existe, no hacer nada
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(100) UNIQUE NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar si la columna 'token_usado' NO existe y agregarla
SET @columnname = 'token_usado';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) DEFAULT 0")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar si la columna 'token_fecha_uso' NO existe y agregarla
SET @columnname = 'token_fecha_uso';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " DATETIME NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- PASO 3: Confirmar cambios
-- ============================================

COMMIT;

-- ============================================
-- Verificación (Opcional - Comentar si no se necesita)
-- ============================================

SELECT 
    'Tabla registro_tokens creada' AS status,
    COUNT(*) AS registros
FROM registro_tokens;

SELECT 
    'Columnas agregadas a employees' AS status,
    COUNT(*) AS total_empleados
FROM employees;
