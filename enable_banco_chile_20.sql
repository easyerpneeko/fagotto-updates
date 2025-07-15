-- Comando SQL para habilitar el método de pago "Banco De Chile 20%" en la base de datos
-- Ejecute este comando en su gestor de base de datos MySQL

INSERT INTO settings_submodules (name, keyname, submodule_id, created_at, updated_at) 
VALUES ('Banco De Chile 20%', 'banco_chile_20', 1, NOW(), NOW());

-- Verificar que se insertó correctamente
SELECT * FROM settings_submodules WHERE keyname = 'banco_chile_20';
