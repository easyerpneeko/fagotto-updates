-- =============================================
-- MIGRACIÓN: Llenar pedidofinal_detalle con pedidos existentes
-- Fecha: 2026-01-05
-- Descripción: Migra pedidos históricos de la tabla requests a pedidofinal_detalle
-- =============================================

USE easyerp;

-- Verificar cuántos pedidos hay actualmente
SELECT 
    '📊 Pedidos actuales en requests (tipo pedido final):' as mensaje,
    COUNT(*) as total
FROM requests
WHERE status IN ('pendiente', 'aprobado', 'enviado', 'completado');

-- =============================================
-- IMPORTANTE: Este script intenta reconstruir el historial
-- desde los pedidos existentes en la tabla requests.
-- Solo ejecutar UNA VEZ.
-- =============================================

-- Insertar detalle de pedidos existentes
INSERT INTO pedidofinal_detalle (
    request_id,
    app_id,
    producto_id,
    producto_nombre,
    categoria,
    cantidad,
    unidad_medida,
    precio_unitario,
    subtotal,
    contact_name,
    status,
    fecha_pedido,
    created_at,
    updated_at
)
SELECT 
    r.id as request_id,
    r.app_id,
    
    -- Extraer datos del JSON de products
    JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.id')) as producto_id,
    JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.name')) as producto_nombre,
    
    -- Buscar categoría del producto (si existe)
    COALESCE(
        (SELECT categoria 
         FROM pedidofinal_precios 
         WHERE id = JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.id'))
         LIMIT 1),
        'general'
    ) as categoria,
    
    -- Cantidad y precio
    CAST(JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.quantity')) AS DECIMAL(10,2)) as cantidad,
    
    -- Unidad de medida del producto
    COALESCE(
        (SELECT unidad_medida 
         FROM pedidofinal_precios 
         WHERE id = JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.id'))
         LIMIT 1),
        'unidad'
    ) as unidad_medida,
    
    CAST(JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.price')) AS DECIMAL(10,2)) as precio_unitario,
    
    -- Subtotal calculado
    CAST(JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.quantity')) AS DECIMAL(10,2)) * 
    CAST(JSON_UNQUOTE(JSON_EXTRACT(producto.value, '$.price')) AS DECIMAL(10,2)) as subtotal,
    
    r.contact_name,
    r.status,
    r.created_at as fecha_pedido,
    r.created_at,
    r.updated_at
    
FROM requests r
-- Descomponer el JSON array de productos
CROSS JOIN JSON_TABLE(
    r.products,
    '$[*]' COLUMNS(
        value JSON PATH '$'
    )
) AS producto
WHERE r.products IS NOT NULL
  AND r.products != '[]'
  AND JSON_VALID(r.products)
  -- Solo migrar pedidos que no estén ya en pedidofinal_detalle
  AND NOT EXISTS (
      SELECT 1 
      FROM pedidofinal_detalle pd 
      WHERE pd.request_id = r.id
  )
ORDER BY r.created_at DESC;

-- Verificar resultado
SELECT 
    '✅ Migración completada' as mensaje,
    COUNT(*) as registros_insertados,
    MIN(fecha_pedido) as pedido_mas_antiguo,
    MAX(fecha_pedido) as pedido_mas_reciente
FROM pedidofinal_detalle;

-- Resumen por producto
SELECT 
    '📊 TOP 10 productos más comprados (histórico):' as mensaje;

SELECT 
    producto_nombre,
    COUNT(*) as veces_pedido,
    SUM(cantidad) as cantidad_total,
    CONCAT('$', FORMAT(SUM(subtotal), 0)) as total_gastado
FROM pedidofinal_detalle
GROUP BY producto_id, producto_nombre
ORDER BY SUM(subtotal) DESC
LIMIT 10;

-- =============================================
-- LISTO! ✅
-- Ahora puedes ver el historial en:
-- https://posfagotto.cl/historial_pedidos.html
-- =============================================
