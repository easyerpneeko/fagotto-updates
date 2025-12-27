-- Agregar columna email a la tabla employees
-- Fecha: 26 de diciembre de 2025
-- Descripción: Campo para almacenar el correo electrónico del empleado donde se envían los términos firmados

ALTER TABLE employees 
ADD COLUMN email VARCHAR(255) NULL AFTER rut;

-- Crear índice para búsquedas rápidas por email
CREATE INDEX idx_employees_email ON employees(email);

-- Comentario descriptivo
ALTER TABLE employees 
MODIFY COLUMN email VARCHAR(255) NULL COMMENT 'Correo electrónico del empleado para envío de términos y notificaciones';
