-- ============================================
-- Crear tabla cupones si no existe
-- ============================================

CREATE TABLE IF NOT EXISTS cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    descripcion TEXT,
    tipo_descuento ENUM('porcentaje', 'monto') DEFAULT 'porcentaje',
    valor_descuento DECIMAL(10,2) NOT NULL,
    monto_minimo DECIMAL(10,2) DEFAULT 0,
    usos_maximos INT DEFAULT 1,
    usos_actuales INT DEFAULT 0,
    fecha_inicio DATE,
    fecha_expiracion DATE,
    activo TINYINT(1) DEFAULT 1,
    usado TINYINT(1) DEFAULT 0,
    usado_en DATETIME NULL,
    sucursal_id INT NULL,
    sucursal_nombre VARCHAR(255) NULL,
    usuario_id INT NULL,
    usuario_nombre VARCHAR(255) NULL,
    producto_id INT NULL,
    producto_nombre VARCHAR(255) NULL,
    producto2_id INT NULL,
    producto2_nombre VARCHAR(255) NULL,
    precio_original DECIMAL(10,2) NULL,
    precio_con_cupon DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);