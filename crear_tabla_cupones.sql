-- ============================================
-- CREAR TABLA DE CUPONES CON TRACKING COMPLETO
-- ============================================

CREATE TABLE IF NOT EXISTS cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descripcion VARCHAR(255) DEFAULT 'Cupón de descuento 100% - GRATIS',
    tipo_descuento VARCHAR(20) DEFAULT 'porcentaje',
    valor_descuento DECIMAL(10,2) DEFAULT 100.00,
    activo TINYINT(1) DEFAULT 1,
    usado TINYINT(1) DEFAULT 0,
    usado_en DATETIME NULL,
    fecha_expiracion DATE DEFAULT '2026-12-31',
    
    -- TRACKING DE USO
    orden_id INT NULL,
    sucursal_id INT NULL,
    usuario_id INT NULL,
    
    -- INFORMACIÓN DEL PRODUCTO CANJEADO
    categoria_id INT NULL,
    categoria_nombre VARCHAR(100) NULL,
    producto_id INT NULL,
    producto_nombre VARCHAR(255) NULL,
    
    -- INFORMACIÓN DE PRECIOS
    precio_original DECIMAL(10,2) NULL,
    precio_con_cupon DECIMAL(10,2) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_usado (usado),
    INDEX idx_activo (activo),
    INDEX idx_sucursal (sucursal_id),
    INDEX idx_orden (orden_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
