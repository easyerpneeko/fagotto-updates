-- 🧹 LIMPIAR TURNOS HUÉRFANOS
-- Cerrar turnos creados con usuario_id=1 que no pueden cerrarse desde el frontend

-- Actualizar turnos abiertos del usuario_id=1 para marcarlos como cerrados
UPDATE TurnosCaja 
SET estado = 'cerrado', 
    fecha_termino = NOW(),
    puede_hacer_arqueo = 0
WHERE usuario_id = 1 
  AND estado = 'abierto' 
  AND fecha_termino IS NULL;

-- Verificar el resultado
SELECT id, usuario_id, usuario_nombre, estado, fecha_inicio, fecha_termino 
FROM TurnosCaja 
ORDER BY id DESC 
LIMIT 5;