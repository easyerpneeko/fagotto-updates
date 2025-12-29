-- =========================================
-- INSERTAR FOLIOS DEL SII (CAF)
-- =========================================
-- Este archivo inserta los folios autorizados por el SII
-- Tipo de Documento (TD):
-- 33 = Factura Electrónica
-- 39 = Boleta Electrónica
-- 52 = Guía de Despacho Electrónica
-- 61 = Nota de Crédito Electrónica
-- =========================================

-- Insertar el XML cargado (CAF) - Guía de Despacho Electrónica
-- TD: 52 = Guía de Despacho Electrónica
-- Rango: 501 a 3500
-- RUT Emisor: 77742774-1 (FAGOTTO SPA)
-- Fecha Autorización: 2025-12-29

INSERT INTO `xml_cargados` 
(`id`, `typeNumber`, `type`, `folio_inicial`, `folio_final`, `jsonXML`, `contentXML`, `created_at`, `updated_at`) 
VALUES 
(
  NULL, -- id auto_increment
  52, -- typeNumber (52 = Guía de Despacho Electrónica)
  'guia_de_despacho', -- type
  501, -- folio_inicial
  3500, -- folio_final
  -- jsonXML (estructura parseada del XML - debe ser JSON válido y escapado)
  '{"CAF":{"@attributes":{"version":"1.0"},"DA":{"RE":"77742774-1","RS":"FAGOTTO SPA","TD":"52","RNG":{"D":"501","H":"3500"},"FA":"2025-12-29","RSAPK":{"M":"xaBdwkW37R1jBTcg\\/nXhn1VwftR3hvkB2j0olTXnCXsyIZCDPFF2g7gG3tnbWrytqMHrCKGU1MfgHq0k7WFv9Q==","E":"Aw=="},"IDK":"300"},"FRMA":"bzt2bXz4JxdKM6kNrSD+03A26Bbf6MonN1TXwc+ILLztDiCIzdp1WNJf0pGlUc8+GnHu9oiAjxbThI7Xbk1YFA=="},"RSASK":"-----BEGIN RSA PRIVATE KEY-----\\nMIIBOwIBAAJBAMWgXcJFt+0dYwU3IP514Z9VcH7Ud4b5Ado9KJU15wl7MiGQgzxR\\ndoO4Bt7Z21q8rajB6wihlNTH4B6tJO1hb\\/UCAQMCQQCDwD6Bg8\\/zaOyuJMCpo+u\\/\\njkr\\/OE+vUKvm03BjeUSw+57Jag9ACKEkPTkwHBX9fp3bOzy7622k1D4xuTQlt5Jr\\nAiEA97ayxeNFB+NZwxzP4tv+VVCe7Ba1dEv3\\/tJLNMP+YZkCIQDMPL6meP986gJt\\n+d\\/XgoBrj0oj2Ar8EZGEAcwh8M+yvQIhAKUkdy6Xg1qXkSy93+ySqY41v0gPI6Ld\\nT\\/823M3X\\/uu7AiEAiCh\\/GaX\\/qJwBnqaVOlcAR7TcF+VcqAu2WAEywUs1IdMCIQDP\\n6NOD3Tq3WLptRjPImLj1sr9jkAL6RiqyrN2C2z+v5A==\\n-----END RSA PRIVATE KEY-----\\n","RSAPUBK":"-----BEGIN PUBLIC KEY-----\\nMFowDQYJKoZIhvcNAQEBBQADSQAwRgJBAMWgXcJFt+0dYwU3IP514Z9VcH7Ud4b5\\nAdo9KJU15wl7MiGQgzxRdoO4Bt7Z21q8rajB6wihlNTH4B6tJO1hb\\/UCAQM=\\n-----END PUBLIC KEY-----\\n"}',
  -- contentXML (contenido completo del XML)
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

-- =========================================
-- NOTA IMPORTANTE:
-- =========================================
-- Después de insertar el XML, el sistema debe crear automáticamente
-- los folios individuales (501 a 3500) en la tabla 'folios'
-- cuando se emitan las guías de despacho.
-- 
-- El sistema asigna folios automáticamente al momento de:
-- 1. Crear una nueva guía de despacho
-- 2. Tomar el siguiente folio disponible del rango
-- 3. Insertar en la tabla 'folios' con el xml_id correspondiente
-- =========================================

-- Para verificar que se insertó correctamente:
-- SELECT * FROM xml_cargados WHERE type = 'guia_de_despacho' ORDER BY id DESC LIMIT 1;
