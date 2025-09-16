-- ===================================================
-- AGREGAR CAMPOS FALTANTES PARA SISTEMA DE ARQUEO DE CAJA
-- ===================================================

-- Verificar la estructura actual de la tabla
SHOW COLUMNS FROM TurnosCaja;

-- Agregar los campos monetarios faltantes
ALTER TABLE TurnosCaja 
ADD COLUMN IF NOT EXISTS monto_inicial DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto inicial del turno' AFTER usuario_nombre,
ADD COLUMN IF NOT EXISTS monto_final DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto final contado (solo efectivo)' AFTER monto_inicial,
ADD COLUMN IF NOT EXISTS total_otros_medios DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total de otros medios de pago' AFTER total_contado,
ADD COLUMN IF NOT EXISTS diferencia_general DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Diferencia total general' AFTER diferencia;

-- También agregar índices para mejorar el rendimiento
ALTER TABLE TurnosCaja 
ADD INDEX IF NOT EXISTS idx_app_usuario (app_id, usuario_id),
ADD INDEX IF NOT EXISTS idx_turno_abierto (turno_abierto),
ADD INDEX IF NOT EXISTS idx_fecha_inicio (fecha_inicio);

-- Verificar que los campos se agregaron correctamente
SHOW COLUMNS FROM TurnosCaja;

-- Opcional: Mostrar la estructura completa de la tabla
DESC TurnosCaja;

-- Verificar algunos registros existentes
SELECT id, app_id, usuario_nombre, monto_inicial, monto_final, total_contado, total_otros_medios, diferencia, diferencia_general 
FROM TurnosCaja 
ORDER BY id DESC 
LIMIT 5;

-- Mensaje de éxito
SELECT '✅ CAMPOS AGREGADOS EXITOSAMENTE' AS mensaje;

-- Verificar que los campos nuevos están presentes
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'TurnosCaja' 
  AND COLUMN_NAME IN ('monto_inicial', 'monto_final', 'total_otros_medios', 'diferencia_general')
ORDER BY ORDINAL_POSITION;

-- ===================================================
-- NOTAS:
-- ===================================================
-- 1. Este script agrega los campos necesarios para que el sistema pueda guardar correctamente:
--    - monto_inicial: Monto con el que inicia el turno
--    - monto_final: Solo el efectivo contado al final
--    - total_otros_medios: Total de tarjetas, transferencias, etc.
--    - diferencia_general: Diferencia considerando todos los medios de pago
--
-- 2. Los campos se agregan con DEFAULT 0.00 para no afectar registros existentes
-- 
-- 3. Después de ejecutar este script, el sistema debería poder guardar correctamente
--    todos los valores monetarios en lugar de 0.00
-- ===================================================