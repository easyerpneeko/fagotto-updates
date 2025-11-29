-- ============================================
-- LIMPIAR Y CREAR 1000 CUPONES DESDE CERO
-- Promoción 31 de Octubre
-- ============================================

-- 1. LIMPIAR TABLA (ELIMINAR TODOS LOS CUPONES EXISTENTES)
TRUNCATE TABLE cupones;

-- 2. INSERTAR 1001 CUPONES (Del 0 al 1000)
-- Generamos cupones: 0, 1, 2, 3, ... 999, 1000

INSERT INTO cupones (codigo, descripcion, tipo_descuento, valor_descuento, activo, usado, fecha_expiracion) VALUES
('0', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('1', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('2', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('3', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('4', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('5', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('6', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('7', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('8', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('9', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31'),
('10', 'Promoción 31 de Diciembre', 'porcentaje', 100.00, 1, 0, '2026-12-31');

-- Generar los cupones del 11 al 1000 usando un procedimiento
DROP PROCEDURE IF EXISTS generar_cupones_numericos;

DELIMITER $$
CREATE PROCEDURE generar_cupones_numericos()
BEGIN
    DECLARE i INT DEFAULT 11;
    WHILE i <= 1000 DO
        INSERT INTO cupones (codigo, descripcion, tipo_descuento, valor_descuento, activo, usado, fecha_expiracion)
        VALUES (CAST(i AS CHAR), 'valido 31 de diciembre.', 'porcentaje', 100.00, 1, 0, '2025-12-31');
        SET i = i + 1;
    END WHILE;
END$$
DELIMITER ;

-- Ejecutar el procedimiento
CALL generar_cupones_numericos();

-- Eliminar el procedimiento después de usarlo
DROP PROCEDURE generar_cupones_numericos;

-- 3. VERIFICAR QUE SE CREARON CORRECTAMENTE
SELECT 
    COUNT(*) AS 'Total Cupones Creados',
    MIN(CAST(codigo AS UNSIGNED)) AS 'Primer Cupón',
    MAX(CAST(codigo AS UNSIGNED)) AS 'Último Cupón',
    descripcion AS 'Descripción'
FROM cupones
GROUP BY descripcion;

-- 4. VERIFICAR LOS PRIMEROS 10 CUPONES
SELECT * FROM cupones ORDER BY CAST(codigo AS UNSIGNED) LIMIT 10;

-- 5. VERIFICAR LOS ÚLTIMOS 10 CUPONES
SELECT * FROM cupones ORDER BY CAST(codigo AS UNSIGNED) DESC LIMIT 10;

-- 6. VERIFICAR CUPONES ESPECÍFICOS
SELECT * FROM cupones WHERE codigo IN ('0', '1', '500', '999', '1000');
