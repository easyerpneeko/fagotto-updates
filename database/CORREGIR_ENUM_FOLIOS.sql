-- =========================================
-- SOLUCIÓN COMPLETA - PROBLEMA CON FOLIOS
-- =========================================
-- Este script corrige el ENUM de la tabla folios
-- y luego te permite cargar el XML desde el panel
-- =========================================

-- PASO 1: Ver el ENUM actual
SELECT COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'folios' 
AND COLUMN_NAME = 'type';

-- PASO 2: Corregir el ENUM si no tiene 'guia_de_despacho'
ALTER TABLE folios 
MODIFY COLUMN type ENUM('factura','boleta','nota_de_credito','guia_de_despacho') NOT NULL;

-- PASO 3: Verificar que se corrigió
SELECT COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'folios' 
AND COLUMN_NAME = 'type';

-- PASO 4: También verificar y corregir xml_cargados
SELECT COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'xml_cargados' 
AND COLUMN_NAME = 'type';

ALTER TABLE xml_cargados 
MODIFY COLUMN type ENUM('factura','boleta','nota_de_credito','guia_de_despacho') NOT NULL;

-- =========================================
-- ¡LISTO! Ahora puedes cargar el XML desde el panel
-- =========================================

SELECT '✅ Tablas corregidas. Ahora recarga el panel admin y sube el XML' as mensaje;
