-- =========================================================
-- CREAR USUARIOS CAJEROS - BASE: erd_app_fagotto_ahumada_6666af8b73317
-- =========================================================

USE erd_app_fagotto_ahumada_6666af8b73317;

-- Usuario 1: Andrea Boulanger
-- Username: aboulanger - Contraseña: aboulanger - Rol: 3 (Cajero)
INSERT INTO users 
  (username, fullname, password, avatar, active, trash, role, created_at, updated_at)
VALUES
  ('aboulanger', 'Andrea Boulanger', '$2y$10$J1GkncZprSW.74A06/NbY.PvhR60hjI8CVooYNlTyJzn2Hskp0Cgq', 'default', 1, 0, 3, NOW(), NOW());

-- Usuario 2: Juan Araya
-- Username: jaraya - Contraseña: jaraya - Rol: 3 (Cajero)
INSERT INTO users 
  (username, fullname, password, avatar, active, trash, role, created_at, updated_at)
VALUES
  ('jaraya', 'Juan Araya', '$2y$10$8LJ/59e23NLyVlnXPdQWuOHpFdtDbRNOgXglL/7kJE5.O.lEZ.wbO', 'default', 1, 0, 3, NOW(), NOW());

-- Verificar creación
SELECT id, username, fullname, role, active, trash FROM users WHERE username IN ('aboulanger', 'jaraya');
