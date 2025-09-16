SHOW COLUMNS FROM TurnosCaja;

ALTER TABLE TurnosCaja 
ADD COLUMN IF NOT EXISTS monto_inicial DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER usuario_nombre,
ADD COLUMN IF NOT EXISTS monto_final DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER monto_inicial,
ADD COLUMN IF NOT EXISTS total_otros_medios DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER total_contado,
ADD COLUMN IF NOT EXISTS diferencia_general DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER diferencia;

ALTER TABLE TurnosCaja 
ADD INDEX IF NOT EXISTS idx_app_usuario (app_id, usuario_id),
ADD INDEX IF NOT EXISTS idx_turno_abierto (turno_abierto),
ADD INDEX IF NOT EXISTS idx_fecha_inicio (fecha_inicio);

SHOW COLUMNS FROM TurnosCaja;

DESC TurnosCaja;

SELECT id, app_id, usuario_nombre, monto_inicial, monto_final, total_contado, total_otros_medios, diferencia, diferencia_general 
FROM TurnosCaja 
ORDER BY id DESC 
LIMIT 5;

SELECT '✅ CAMPOS AGREGADOS EXITOSAMENTE' AS mensaje;

SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'TurnosCaja' 
  AND COLUMN_NAME IN ('monto_inicial', 'monto_final', 'total_otros_medios', 'diferencia_general')
ORDER BY ORDINAL_POSITION;