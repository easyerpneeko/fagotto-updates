-- =========================================
-- INSERTAR FOLIOS DEL SII (CAF) - GUÍA DE DESPACHO
-- =========================================
-- Este script verifica e inserta los folios de Guía de Despacho
-- Tipo de Documento (TD): 52 = Guía de Despacho Electrónica
-- Rango: 501 a 3500
-- RUT Emisor: 77742774-1 (FAGOTTO SPA)
-- Fecha Autorización: 2025-12-29
-- =========================================

-- PASO 1: Verificar si ya existe un rango similar
SELECT * FROM xml_cargados 
WHERE type = 'guia_de_despacho' 
AND folio_inicial <= 501 
AND folio_final >= 3500;

-- Si el SELECT anterior devuelve resultados, NO ejecutes el INSERT
-- Si no devuelve nada, continúa con el INSERT

-- PASO 2: Insertar el XML cargado (CAF)
INSERT INTO `xml_cargados` 
(`typeNumber`, `type`, `folio_inicial`, `folio_final`, `jsonXML`, `contentXML`, `created_at`, `updated_at`) 
VALUES 
(
  52, -- typeNumber (52 = Guía de Despacho Electrónica)
  'guia_de_despacho', -- type (IMPORTANTE: con guion bajo)
  501, -- folio_inicial
  3500, -- folio_final
  -- jsonXML (JSON escapado correctamente para MySQL)
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

-- PASO 3: Obtener el ID del XML recién insertado
SET @xml_id = LAST_INSERT_ID();

-- PASO 4: Crear todos los folios individuales (501 a 3500)
-- NOTA: Este proceso puede tardar varios segundos
-- Puedes ejecutar esto en bloques si prefieres

-- Procedimiento almacenado para crear folios
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS crear_folios_guia_despacho(IN p_xml_id BIGINT)
BEGIN
    DECLARE v_folio INT DEFAULT 501;
    DECLARE v_folio_final INT DEFAULT 3500;
    
    WHILE v_folio <= v_folio_final DO
        -- Verificar que el folio no exista
        IF NOT EXISTS (SELECT 1 FROM folios WHERE folio = v_folio AND type = 'guia_de_despacho') THEN
            INSERT INTO folios (folio, type, xml_id, trash, created_at, updated_at)
            VALUES (v_folio, 'guia_de_despacho', p_xml_id, 0, NOW(), NOW());
        END IF;
        
        SET v_folio = v_folio + 1;
    END WHILE;
END$$
DELIMITER ;

-- Ejecutar el procedimiento
CALL crear_folios_guia_despacho(@xml_id);

-- Eliminar el procedimiento después de usarlo
DROP PROCEDURE IF EXISTS crear_folios_guia_despacho;

-- =========================================
-- VERIFICACIÓN FINAL
-- =========================================

-- Ver el XML insertado
SELECT id, typeNumber, type, folio_inicial, folio_final, created_at 
FROM xml_cargados 
WHERE id = @xml_id;

-- Contar folios creados
SELECT COUNT(*) as total_folios_creados 
FROM folios 
WHERE xml_id = @xml_id AND type = 'guia_de_despacho';

-- Ver los primeros 10 folios
SELECT id, folio, type, xml_id, trash, created_at 
FROM folios 
WHERE xml_id = @xml_id AND type = 'guia_de_despacho'
ORDER BY folio ASC 
LIMIT 10;

-- Ver folios disponibles (no asignados)
SELECT COUNT(*) as folios_disponibles 
FROM folios 
WHERE xml_id = @xml_id 
AND type = 'guia_de_despacho' 
AND guia_id IS NULL 
AND trash = 0;

-- =========================================
-- ¡LISTO! Ahora tienes 2,999 folios de guía de despacho disponibles
-- =========================================
