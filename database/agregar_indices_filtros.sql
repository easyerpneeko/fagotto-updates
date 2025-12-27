-- Agregar índices para mejorar performance en filtros
USE easyerp;

-- Índice para filtrar por local y fecha
ALTER TABLE asistencias_records 
ADD INDEX idx_app_id_fecha (app_id, fecha_hora);

-- Índice para filtrar por empleado y fecha
ALTER TABLE asistencias_records 
ADD INDEX idx_employee_fecha (employee_id, fecha_hora);

-- Índice compuesto para filtros combinados
ALTER TABLE asistencias_records 
ADD INDEX idx_filtros (app_id, employee_id, fecha_hora);
