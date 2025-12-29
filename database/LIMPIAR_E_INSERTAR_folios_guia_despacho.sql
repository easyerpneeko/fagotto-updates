-- =========================================
-- LIMPIAR Y REINSERTAR FOLIOS DE GUÍA DE DESPACHO
-- =========================================
-- Este script ELIMINA todos los folios de guía de despacho existentes
-- y luego inserta los nuevos desde cero
-- =========================================

-- =========================================
-- PASO 1: LIMPIEZA COMPLETA
-- =========================================

-- 1.1 Eliminar todos los folios de guía de despacho
DELETE FROM folios WHERE type = 'guia_de_despacho';

-- 1.2 Eliminar todos los XML de guía de despacho
DELETE FROM xml_cargados WHERE type = 'guia_de_despacho';

-- 1.3 Verificar que se eliminó todo
SELECT 'Folios eliminados:' as descripcion, COUNT(*) as cantidad FROM folios WHERE type = 'guia_de_despacho'
UNION ALL
SELECT 'XML eliminados:', COUNT(*) FROM xml_cargados WHERE type = 'guia_de_despacho';

-- =========================================
-- PASO 2: INSERTAR NUEVO CAF (XML)
-- =========================================
-- Tipo de Documento (TD): 52 = Guía de Despacho Electrónica
-- Rango: 501 a 3500 (2,999 folios)
-- RUT Emisor: 77742774-1 (FAGOTTO SPA)
-- Fecha Autorización: 2025-12-29
-- =========================================

INSERT INTO `xml_cargados` 
(`typeNumber`, `type`, `folio_inicial`, `folio_final`, `jsonXML`, `contentXML`, `created_at`, `updated_at`) 
VALUES 
(
  52, -- typeNumber
  'guia_de_despacho', -- type (IMPORTANTE: con guion bajo)
  501, -- folio_inicial
  3500, -- folio_final
  -- jsonXML (JSON escapado correctamente)
  '{"CAF":{"@attributes":{"version":"1.0"},"DA":{"RE":"77742774-1","RS":"FAGOTTO SPA","TD":"52","RNG":{"D":"501","H":"3500"},"FA":"2025-12-29","RSAPK":{"M":"xaBdwkW37R1jBTcg\\/nXhn1VwftR3hvkB2j0olTXnCXsyIZCDPFF2g7gG3tnbWrytqMHrCKGU1MfgHq0k7WFv9Q==","E":"Aw=="},"IDK":"300"},"FRMA":"bzt2bXz4JxdKM6kNrSD+03A26Bbf6MonN1TXwc+ILLztDiCIzdp1WNJf0pGlUc8+GnHu9oiAjxbThI7Xbk1YFA=="},"RSASK":"-----BEGIN RSA PRIVATE KEY-----\\nMIIBOwIBAAJBAMWgXcJFt+0dYwU3IP514Z9VcH7Ud4b5Ado9KJU15wl7MiGQgzxR\\ndoO4Bt7Z21q8rajB6wihlNTH4B6tJO1hb\\/UCAQMCQQCDwD6Bg8\\/zaOyuJMCpo+u\\/\\njkr\\/OE+vUKvm03BjeUSw+57Jag9ACKEkPTkwHBX9fp3bOzy7622k1D4xuTQlt5Jr\\nAiEA97ayxeNFB+NZwxzP4tv+VVCe7Ba1dEv3\\/tJLNMP+YZkCIQDMPL6meP986gJt\\n+d\\/XgoBrj0oj2Ar8EZGEAcwh8M+yvQIhAKUkdy6Xg1qXkSy93+ySqY41v0gPI6Ld\\nT\\/823M3X\\/uu7AiEAiCh\\/GaX\\/qJwBnqaVOlcAR7TcF+VcqAu2WAEywUs1IdMCIQDP\\n6NOD3Tq3WLptRjPImLj1sr9jkAL6RiqyrN2C2z+v5A==\\n-----END RSA PRIVATE KEY-----\\n","RSAPUBK":"-----BEGIN PUBLIC KEY-----\\nMFowDQYJKoZIhvcNAQEBBQADSQAwRgJBAMWgXcJFt+0dYwU3IP514Z9VcH7Ud4b5\\nAdo9KJU15wl7MiGQgzxRdoO4Bt7Z21q8rajB6wihlNTH4B6tJO1hb\\/UCAQM=\\n-----END PUBLIC KEY-----\\n"}',
  -- contentXML (XML completo)
  '<?xml version="1.0"?>
<AUTORIZACION>
<CAF version="1.0">
<DA>
<RE>77742774-1</RE>
<RS>FAGOTTO SPA</RS>
<TD>52</TD>
<RNG><D>501</D><H>3500</H></RNG>
<FA>2025-12-29</FA>
<RSAPK><M>xaBdwkW37R1jBTcg/nXhn1VwftR3hvkB2j0olTXnCXsyIZCDPFF2g7gG3tnbWrytqMHrCKGU1MfgHq0k7WFv9Q==</M><E>Aw==</E></RSAPK>
<IDK>300</IDK>
</DA>
<FRMA algoritmo="SHA1withRSA">bzt2bXz4JxdKM6kNrSD+03A26Bbf6MonN1TXwc+ILLztDiCIzdp1WNJf0pGlUc8+GnHu9oiAjxbThI7Xbk1YFA==</FRMA>
</CAF>
<RSASK>-----BEGIN RSA PRIVATE KEY-----
MIIBOwIBAAJBAMWgXcJFt+0dYwU3IP514Z9VcH7Ud4b5Ado9KJU15wl7MiGQgzxR
doO4Bt7Z21q8rajB6wihlNTH4B6tJO1hb/UCAQMCQQCDwD6Bg8/zaOyuJMCpo+u/
jkr/OE+vUKvm03BjeUSw+57Jag9ACKEkPTkwHBX9fp3bOzy7622k1D4xuTQlt5Jr
AiEA97ayxeNFB+NZwxzP4tv+VVCe7Ba1dEv3/tJLNMP+YZkCIQDMPL6meP986gJt
+d/XgoBrj0oj2Ar8EZGEAcwh8M+yvQIhAKUkdy6Xg1qXkSy93+ySqY41v0gPI6Ld
T/823M3X/uu7AiEAiCh/GaX/qJwBnqaVOlcAR7TcF+VcqAu2WAEywUs1IdMCIQDP
6NOD3Tq3WLptRjPImLj1sr9jkAL6RiqyrN2C2z+v5A==
-----END RSA PRIVATE KEY-----
</RSASK>

<RSAPUBK>-----BEGIN PUBLIC KEY-----
MFowDQYJKoZIhvcNAQEBBQADSQAwRgJBAMWgXcJFt+0dYwU3IP514Z9VcH7Ud4b5
Ado9KJU15wl7MiGQgzxRdoO4Bt7Z21q8rajB6wihlNTH4B6tJO1hb/UCAQM=
-----END PUBLIC KEY-----
</RSAPUBK>
</AUTORIZACION>',
  NOW(), -- created_at
  NOW()  -- updated_at
);

-- Obtener el ID del XML recién insertado
SET @xml_id = LAST_INSERT_ID();

SELECT CONCAT('XML insertado con ID: ', @xml_id) as resultado;

-- =========================================
-- PASO 3: CREAR TODOS LOS FOLIOS (501 a 3500)
-- =========================================

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS crear_folios_guia_despacho(IN p_xml_id BIGINT)
BEGIN
    DECLARE v_folio INT DEFAULT 501;
    DECLARE v_folio_final INT DEFAULT 3500;
    DECLARE v_contador INT DEFAULT 0;
    
    WHILE v_folio <= v_folio_final DO
        INSERT INTO folios (folio, type, xml_id, trash, created_at, updated_at)
        VALUES (v_folio, 'guia_de_despacho', p_xml_id, 0, NOW(), NOW());
        
        SET v_folio = v_folio + 1;
        SET v_contador = v_contador + 1;
    END WHILE;
    
    SELECT CONCAT('Se crearon ', v_contador, ' folios exitosamente') as resultado;
END$$
DELIMITER ;

-- Ejecutar el procedimiento para crear los folios
CALL crear_folios_guia_despacho(@xml_id);

-- Eliminar el procedimiento
DROP PROCEDURE IF EXISTS crear_folios_guia_despacho;

-- =========================================
-- PASO 4: VERIFICACIÓN FINAL
-- =========================================

-- Ver el XML insertado
SELECT 
    '=== XML CARGADO ===' as seccion,
    id, 
    typeNumber, 
    type, 
    folio_inicial, 
    folio_final, 
    (folio_final - folio_inicial + 1) as total_folios_en_rango,
    created_at 
FROM xml_cargados 
WHERE id = @xml_id;

-- Contar folios creados
SELECT 
    '=== FOLIOS CREADOS ===' as seccion,
    COUNT(*) as total_folios_creados,
    MIN(folio) as folio_minimo,
    MAX(folio) as folio_maximo
FROM folios 
WHERE xml_id = @xml_id AND type = 'guia_de_despacho';

-- Ver los primeros 5 folios
SELECT 
    '=== PRIMEROS 5 FOLIOS ===' as seccion,
    id, 
    folio, 
    type, 
    xml_id, 
    trash, 
    guia_id,
    created_at 
FROM folios 
WHERE xml_id = @xml_id AND type = 'guia_de_despacho'
ORDER BY folio ASC 
LIMIT 5;

-- Ver los últimos 5 folios
SELECT 
    '=== ÚLTIMOS 5 FOLIOS ===' as seccion,
    id, 
    folio, 
    type, 
    xml_id, 
    trash, 
    guia_id,
    created_at 
FROM folios 
WHERE xml_id = @xml_id AND type = 'guia_de_despacho'
ORDER BY folio DESC 
LIMIT 5;

-- Contar folios disponibles (no asignados)
SELECT 
    '=== RESUMEN ===' as seccion,
    COUNT(*) as folios_disponibles,
    COUNT(CASE WHEN guia_id IS NOT NULL THEN 1 END) as folios_asignados,
    COUNT(CASE WHEN trash = 1 THEN 1 END) as folios_en_papelera
FROM folios 
WHERE xml_id = @xml_id AND type = 'guia_de_despacho';

-- =========================================
-- ✅ ¡PROCESO COMPLETADO!
-- =========================================
-- Se han eliminado todos los folios antiguos de guía de despacho
-- y se han creado 2,999 nuevos folios (del 501 al 3500)
-- listos para ser asignados a guías de despacho
-- =========================================
