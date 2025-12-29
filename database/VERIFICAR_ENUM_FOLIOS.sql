-- =========================================
-- VERIFICAR Y CORREGIR ENUM DE TABLA FOLIOS
-- =========================================
-- Este script verifica que la tabla folios tenga el enum correcto
-- y lo corrige si es necesario
-- =========================================

-- Ver la estructura actual de la tabla folios
SHOW CREATE TABLE folios;

-- Ver los valores del ENUM type
SELECT COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'folios' 
AND COLUMN_NAME = 'type';

-- Si el ENUM no tiene 'guia_de_despacho', ejecuta esto:
-- ALTER TABLE folios 
-- MODIFY COLUMN type ENUM('factura','boleta','nota_de_credito','guia_de_despacho') NOT NULL;

-- =========================================
-- VERIFICAR TAMBIÉN XML_CARGADOS
-- =========================================

-- Ver los valores del ENUM type en xml_cargados
SELECT COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'xml_cargados' 
AND COLUMN_NAME = 'type';

-- Si el ENUM no tiene 'guia_de_despacho', ejecuta esto:
-- ALTER TABLE xml_cargados 
-- MODIFY COLUMN type ENUM('factura','boleta','nota_de_credito','guia_de_despacho') NOT NULL;
