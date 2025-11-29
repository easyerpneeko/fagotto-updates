-- ============================================
-- RESETEAR TODOS LOS CUPONES A NO USADOS
-- ============================================

UPDATE cupones 
SET usado = 0,
    usos_actuales = 0,
    usado_en = NULL,
    orden_id = NULL,
    sucursal_id = NULL,
    usuario_id = NULL,
    categoria_id = NULL,
    categoria_nombre = NULL,
    producto_id = NULL,
    producto_nombre = NULL,
    precio_original = NULL,
    precio_con_cupon = NULL;

-- Verificar
SELECT 
    COUNT(*) AS 'Total Cupones',
    SUM(usado = 0) AS 'Disponibles',
    SUM(usado = 1) AS 'Usados'
FROM cupones;
