-- ========================================
-- SISTEMA DE COLACIONES
-- Fecha: 2026-01-20
-- Descripción: Sistema simple de colaciones gratuitas para empleados
-- ========================================

USE easyerp;

-- Tabla para registrar quién retira cada colación
-- Se vincula con products_sells para saber qué producto retiró
CREATE TABLE IF NOT EXISTS colaciones_retiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sell_id INT NOT NULL COMMENT 'ID de la venta (sells.id)',
    product_sell_id INT NOT NULL COMMENT 'ID del producto vendido (products_sells.id)',
    empleado_nombre VARCHAR(255) NOT NULL COMMENT 'Nombre del empleado que retira',
    pasta VARCHAR(50) COMMENT 'Tipo de pasta elegida (bigoli/fettuccini)',
    salsa VARCHAR(50) COMMENT 'Tipo de salsa elegida (alfredo/bolonesa/cheddar)',
    fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del retiro',
    
    -- Índices
    INDEX idx_sell_id (sell_id),
    INDEX idx_product_sell_id (product_sell_id),
    INDEX idx_empleado (empleado_nombre),
    INDEX idx_fecha_retiro (fecha_retiro)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de colaciones retiradas por empleados';

-- ========================================
-- VERIFICACIÓN
-- ========================================
SELECT 
    'Tabla colaciones_retiros' AS Tabla,
    COUNT(*) AS 'Total Registros'
FROM colaciones_retiros;

-- ========================================
-- CONSULTA ÚTIL: Ver colaciones de hoy
-- ========================================
-- SELECT 
--     cr.empleado_nombre,
--     ps.description_sii AS producto,
--     cr.pasta,
--     cr.salsa,
--     cr.fecha_retiro,
--     s.total
-- FROM colaciones_retiros cr
-- INNER JOIN products_sells ps ON cr.product_sell_id = ps.id
-- INNER JOIN sells s ON cr.sell_id = s.id
-- WHERE DATE(cr.fecha_retiro) = CURDATE()
-- ORDER BY cr.fecha_retiro DESC;

-- ========================================
-- CONSULTA ÚTIL: Estadísticas por empleado
-- ========================================
-- SELECT 
--     empleado_nombre,
--     COUNT(*) AS total_colaciones,
--     GROUP_CONCAT(DISTINCT pasta) AS pastas_elegidas,
--     GROUP_CONCAT(DISTINCT salsa) AS salsas_elegidas,
--     MAX(fecha_retiro) AS ultima_colacion
-- FROM colaciones_retiros
-- GROUP BY empleado_nombre
-- ORDER BY total_colaciones DESC;
