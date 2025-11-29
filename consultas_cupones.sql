-- ============================================
-- CONSULTAS PARA ANALIZAR USO DE CUPONES
-- ============================================

-- 1. VER TODOS LOS CUPONES Y SU ESTADO
SELECT 
    id,
    codigo,
    activo,
    usado,
    usado_en,
    orden_id,
    sucursal_id,
    created_at
FROM cupones
ORDER BY usado_en DESC;

-- 2. CUPONES USADOS CON DETALLES
SELECT 
    c.codigo AS 'Cupón',
    c.usado_en AS 'Fecha Uso',
    c.orden_id AS 'ID Orden',
    c.sucursal_id AS 'ID Sucursal',
    c.usuario_id AS 'ID Usuario',
    c.categoria_id AS 'Categoría',
    c.producto_id AS 'Producto',
    c.precio_original AS 'Precio Original',
    c.precio_con_cupon AS 'Precio Cupón'
FROM cupones c
WHERE c.usado = 1
ORDER BY c.usado_en DESC;

-- 3. CUPONES DISPONIBLES
SELECT 
    codigo AS 'Cupón Disponible',
    created_at AS 'Creado'
FROM cupones
WHERE usado = 0 AND activo = 1
ORDER BY codigo;

-- 4. RESUMEN POR SUCURSAL
SELECT 
    sucursal_id AS 'Sucursal',
    COUNT(*) AS 'Cupones Usados',
    SUM(precio_con_cupon) AS 'Total Vendido',
    SUM(precio_original - precio_con_cupon) AS 'Descuento Total'
FROM cupones
WHERE usado = 1
GROUP BY sucursal_id
ORDER BY COUNT(*) DESC;

-- 5. PRODUCTOS MÁS CANJEADOS
SELECT 
    producto_id AS 'ID Producto',
    producto_nombre AS 'Producto',
    COUNT(*) AS 'Veces Canjeado',
    AVG(precio_original) AS 'Precio Original Promedio'
FROM cupones
WHERE usado = 1
GROUP BY producto_id, producto_nombre
ORDER BY COUNT(*) DESC;

-- 6. USO DE CUPONES POR FECHA
SELECT 
    DATE(usado_en) AS 'Fecha',
    COUNT(*) AS 'Cupones Usados',
    SUM(precio_con_cupon) AS 'Total Día'
FROM cupones
WHERE usado = 1
GROUP BY DATE(usado_en)
ORDER BY DATE(usado_en) DESC;

-- 7. CUPONES USADOS HOY
SELECT 
    codigo AS 'Cupón',
    orden_id AS 'Orden',
    producto_nombre AS 'Producto',
    precio_con_cupon AS 'Precio',
    TIME(usado_en) AS 'Hora'
FROM cupones
WHERE usado = 1 
  AND DATE(usado_en) = CURDATE()
ORDER BY usado_en DESC;

-- 8. DETALLE COMPLETO DE UN CUPÓN ESPECÍFICO
SELECT 
    c.*,
    o.total AS 'Total Orden',
    o.created_at AS 'Fecha Orden'
FROM cupones c
LEFT JOIN orders o ON c.orden_id = o.id
WHERE c.codigo = 'CUPON-A1B2C3';  -- Cambiar por el código del cupón

-- 9. CUPONES SIN USAR (DISPONIBLES)
SELECT 
    COUNT(*) AS 'Cupones Disponibles'
FROM cupones
WHERE usado = 0 AND activo = 1;

-- 10. EFICIENCIA DE CUPONES (USADOS VS TOTAL)
SELECT 
    COUNT(*) AS 'Total Cupones',
    SUM(CASE WHEN usado = 1 THEN 1 ELSE 0 END) AS 'Usados',
    SUM(CASE WHEN usado = 0 THEN 1 ELSE 0 END) AS 'Disponibles',
    ROUND((SUM(CASE WHEN usado = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) AS '% Usado'
FROM cupones
WHERE activo = 1;

-- 11. CUPONES POR CATEGORÍA
SELECT 
    categoria_nombre AS 'Categoría',
    COUNT(*) AS 'Cupones Usados',
    SUM(precio_con_cupon) AS 'Total Vendido'
FROM cupones
WHERE usado = 1
GROUP BY categoria_nombre
ORDER BY COUNT(*) DESC;

-- 12. BUSCAR CUPÓN ESPECÍFICO POR CÓDIGO
SELECT * FROM cupones WHERE codigo LIKE '%A1B2C3%';

-- 13. ÚLTIMOS 10 CUPONES USADOS
SELECT 
    codigo,
    producto_nombre,
    sucursal_id,
    usado_en
FROM cupones
WHERE usado = 1
ORDER BY usado_en DESC
LIMIT 10;
