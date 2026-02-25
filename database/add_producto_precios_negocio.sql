-- =====================================================
-- SISTEMA DE PRECIOS PERSONALIZADOS POR NEGOCIO
-- Para tabla: pedidofinal_precios
-- =====================================================
-- 
-- PROBLEMA: 
-- - pedidofinal_precios tiene precio_por_unidad global
-- - Necesitamos que cada negocio (app_id) tenga precios diferentes
--
-- SOLUCIÓN:
-- - Tabla nueva: pedidofinal_precios_negocio
-- - Relaciona app_id + producto_id con precio personalizado
-- - Si no existe precio custom → usa precio_por_unidad de pedidofinal_precios
--
-- =====================================================

CREATE TABLE IF NOT EXISTS pedidofinal_precios_negocio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    app_id INT NOT NULL COMMENT 'ID del negocio (15, 22, 30, etc)',
    producto_id INT NOT NULL COMMENT 'ID de pedidofinal_precios',
    precio_por_unidad DECIMAL(10,2) NOT NULL COMMENT 'Precio específico para este negocio',
    activo TINYINT(1) DEFAULT 1 COMMENT '1=activo 0=usar precio base',
    nota VARCHAR(255) NULL COMMENT 'Razón del precio custom (opcional)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para performance
    UNIQUE KEY unique_precio_negocio (app_id, producto_id),
    KEY idx_app_id (app_id),
    KEY idx_producto_id (producto_id),
    KEY idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Precios personalizados por negocio para productos de pedidofinal';

-- =====================================================
-- EJEMPLOS DE INSERCIÓN
-- =====================================================

-- EJEMPLO: Negocio 1 (app_id=15) paga menos por Salsa Boloñesa (id=4)
-- INSERT INTO pedidofinal_precios_negocio (app_id, producto_id, precio_por_unidad, nota) 
-- VALUES (15, 4, 8500.00, 'Descuento por volumen');

-- EJEMPLO: Negocio 2 (app_id=22) paga más por Salsa Pesto (id=9)
-- INSERT INTO pedidofinal_precios_negocio (app_id, producto_id, precio_por_unidad, nota) 
-- VALUES (22, 9, 39000.00, 'Precio premium zona alta');

-- EJEMPLO: Negocio 3 (app_id=30) tiene precios custom para varias salsas
-- INSERT INTO pedidofinal_precios_negocio (app_id, producto_id, precio_por_unidad) VALUES
-- (30, 4, 10000.00),  -- Boloñesa
-- (30, 6, 9000.00),   -- Alfredo
-- (30, 7, 14500.00),  -- Camarón
-- (30, 9, 38000.00);  -- Pesto

-- =====================================================
-- QUERY PRINCIPAL: Obtener productos con precio correcto
-- =====================================================

-- Reemplaza tu query actual por esta:
-- 
-- SELECT 
--     p.*,
--     COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_por_unidad
-- FROM pedidofinal_precios p
-- LEFT JOIN pedidofinal_precios_negocio ppn 
--     ON ppn.producto_id = p.id 
--     AND ppn.app_id = ? -- Tu app_id actual desde CurrentApp::getApp()
--     AND ppn.activo = 1
-- WHERE p.activo = 1;

-- =====================================================
-- QUERY DE PRUEBA: Ver precios para un negocio específico
-- =====================================================

-- Ver todos los productos con precio base y precio custom del negocio 15:
-- 
-- SELECT 
--     p.id,
--     p.producto,
--     p.categoria,
--     p.precio_por_unidad as precio_base,
--     ppn.precio_por_unidad as precio_custom,
--     COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_final,
--     ppn.nota
-- FROM pedidofinal_precios p
-- LEFT JOIN pedidofinal_precios_negocio ppn 
--     ON ppn.producto_id = p.id 
--     AND ppn.app_id = 15
--     AND ppn.activo = 1
-- WHERE p.activo = 1
-- ORDER BY p.categoria, p.producto;

-- =====================================================
-- QUERY: Ver qué negocios tienen precios personalizados
-- =====================================================

-- SELECT 
--     ppn.app_id,
--     COUNT(*) as productos_custom,
--     GROUP_CONCAT(p.producto SEPARATOR ', ') as productos
-- FROM pedidofinal_precios_negocio ppn
-- JOIN pedidofinal_precios p ON p.id = ppn.producto_id
-- WHERE ppn.activo = 1
-- GROUP BY ppn.app_id;

-- =====================================================
-- QUERY: Comparar precios entre negocios
-- =====================================================

-- Ver precio de un producto específico en todos los negocios:
-- 
-- SELECT 
--     ppn.app_id,
--     ppn.precio_por_unidad as precio_custom,
--     p.precio_por_unidad as precio_base,
--     ppn.nota
-- FROM pedidofinal_precios_negocio ppn
-- JOIN pedidofinal_precios p ON p.id = ppn.producto_id
-- WHERE ppn.producto_id = 9  -- Salsa Pesto
--   AND ppn.activo = 1;
