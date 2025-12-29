-- =========================================
-- INSERTAR FOLIOS DEL SII (CAF) - FACTURA
-- =========================================
-- Este archivo inserta los folios de Factura Electrónica
-- Tipo de Documento (TD): 33 = Factura Electrónica
-- Rango: 3046 a 4445
-- RUT Emisor: 77742774-1 (FAGOTTO SPA)
-- Fecha Autorización: 2025-07-28
-- =========================================

-- Insertar el XML cargado (CAF) - Factura Electrónica
INSERT INTO `xml_cargados` 
(`id`, `typeNumber`, `type`, `folio_inicial`, `folio_final`, `jsonXML`, `contentXML`, `created_at`, `updated_at`) 
VALUES 
(
  NULL, -- id auto_increment
  33, -- typeNumber (33 = Factura Electrónica)
  'factura', -- type
  3046, -- folio_inicial
  4445, -- folio_final
  -- jsonXML (estructura parseada del XML - escapado correctamente)
  '{"CAF":{"@attributes":{"version":"1.0"},"DA":{"RE":"77742774-1","RS":"FAGOTTO SPA","TD":"33","RNG":{"D":"3046","H":"4445"},"FA":"2025-07-28","RSAPK":{"M":"sOOyntP98uFc5+2EvGHtV6qoMysR768ouGEUxhS7WPrtfsdBxRBax0niLxddfvNbhMLUWvzyTL6dHiWeHa\\/N8w==","E":"Aw=="},"IDK":"300"},"FRMA":"lke0JOfBPXBpIdA5rBv\\/wrN8QHL\\/L3V+I2j3z+PPFKRnHegqVfGVVG\\/S5TQ6hzOz08zBm1Qft\\/73kDiFXAfmog=="},"RSASK":"-----BEGIN RSA PRIVATE KEY-----\\nMIIBOgIBAAJBALDjsp7T\\/fLhXOfthLxh7VeqqDMrEe+vKLhhFMYUu1j67X7HQcUQ\\nWsdJ4i8XXX7zW4TC1Fr88ky+nR4lnh2vzfMCAQMCQHXtIb83\\/qHrk0VJAyhBSOUc\\ncCIctp\\/KGyWWDdljJ5CmLQaa2QL1DdTU7vtBHriYG4Tv52WiavNSF+1wJkWTEZsC\\nIQDdstMKYd7hbIZXCHuh\\/\\/jdyFbfooXjz6dDE\\/GMh0FxPwIhAMxCC\\/HeweSbhCSt\\nug1qFlR1BBmgA24QHDYmC9guEcJNAiEAk8yMsZaUlkhZj1r9Fqql6TA56mxZQopv\\ngg1LswTWS38CIQCILAf2lIFDElgYc9FeRrmNo1gRFVeetWgkGV06yWEsMwIhAIbR\\n2XYUF1XpKsmCk4Ltfw3u7wSP0m4\\/XzAMeB9TOWjj\\n-----END RSA PRIVATE KEY-----\\n","RSAPUBK":"-----BEGIN PUBLIC KEY-----\\nMFowDQYJKoZIhvcNAQEBBQADSQAwRgJBALDjsp7T\\/fLhXOfthLxh7VeqqDMrEe+v\\nKLhhFMYUu1j67X7HQcUQWsdJ4i8XXX7zW4TC1Fr88ky+nR4lnh2vzfMCAQM=\\n-----END PUBLIC KEY-----\\n"}',
  -- contentXML (contenido completo del XML)
  '<?xml version="1.0"?>
<AUTORIZACION>
<CAF version="1.0">
<DA>
<RE>77742774-1</RE>
<RS>FAGOTTO SPA</RS>
<TD>33</TD>
<RNG><D>3046</D><H>4445</H></RNG>
<FA>2025-07-28</FA>
<RSAPK><M>sOOyntP98uFc5+2EvGHtV6qoMysR768ouGEUxhS7WPrtfsdBxRBax0niLxddfvNbhMLUWvzyTL6dHiWeHa/N8w==</M><E>Aw==</E></RSAPK>
<IDK>300</IDK>
</DA>
<FRMA algoritmo="SHA1withRSA">lke0JOfBPXBpIdA5rBv/wrN8QHL/L3V+I2j3z+PPFKRnHegqVfGVVG/S5TQ6hzOz08zBm1Qft/73kDiFXAfmog==</FRMA>
</CAF>
<RSASK>-----BEGIN RSA PRIVATE KEY-----
MIIBOgIBAAJBALDjsp7T/fLhXOfthLxh7VeqqDMrEe+vKLhhFMYUu1j67X7HQcUQ
WsdJ4i8XXX7zW4TC1Fr88ky+nR4lnh2vzfMCAQMCQHXtIb83/qHrk0VJAyhBSOUc
cCIctp/KGyWWDdljJ5CmLQaa2QL1DdTU7vtBHriYG4Tv52WiavNSF+1wJkWTEZsC
IQDdstMKYd7hbIZXCHuh//jdyFbfooXjz6dDE/GMh0FxPwIhAMxCC/HeweSbhCSt
ug1qFlR1BBmgA24QHDYmC9guEcJNAiEAk8yMsZaUlkhZj1r9Fqql6TA56mxZQopv
gg1LswTWS38CIQCILAf2lIFDElgYc9FeRrmNo1gRFVeetWgkGV06yWEsMwIhAIbR
2XYUF1XpKsmCk4Ltfw3u7wSP0m4/XzAMeB9TOWjj
-----END RSA PRIVATE KEY-----
</RSASK>

<RSAPUBK>-----BEGIN PUBLIC KEY-----
MFowDQYJKoZIhvcNAQEBBQADSQAwRgJBALDjsp7T/fLhXOfthLxh7VeqqDMrEe+v
KLhhFMYUu1j67X7HQcUQWsdJ4i8XXX7zW4TC1Fr88ky+nR4lnh2vzfMCAQM=
-----END PUBLIC KEY-----
</RSAPUBK>
</AUTORIZACION>',
  NOW(), -- created_at
  NOW()  -- updated_at
);

-- =========================================
-- NOTA IMPORTANTE:
-- =========================================
-- Este INSERT carga el CAF de FACTURAS ELECTRÓNICAS
-- Rango de folios: 3046 a 4445 (1,399 folios disponibles)
-- Los folios individuales se crean automáticamente al emitir facturas
-- =========================================

-- Para verificar que se insertó correctamente:
-- SELECT * FROM xml_cargados WHERE type = 'factura' ORDER BY id DESC LIMIT 1;
