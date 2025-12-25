-- Script para insertar meta de ejemplo para el local "Fagotto Las Condes"
-- ID del local: 116 (según aplication.json)
-- Meta diaria: $500,000 CLP (ejemplo)

INSERT INTO easyerp.metas_locales (aplication_id, mes, anio, meta_diaria, created_at, updated_at)
VALUES 
  (116, 12, 2025, 500000.00, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  meta_diaria = 500000.00,
  updated_at = NOW();

-- Verificar que se insertó correctamente
SELECT * FROM easyerp.metas_locales WHERE aplication_id = 116 AND mes = 12 AND anio = 2025;
