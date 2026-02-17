-- =========================================================
-- CREAR USUARIOS CAJEROS - LAS CONDES
-- =========================================================
-- Contraseña para todos: fagotto2026

-- Usuario 1: Javier Ignacio Melo Fahrion
-- RUT: 18.211.326-3 | Email: javier.melo.f15@gmail.com
-- Username: jmelo - Contraseña: fagotto2026 - Rol: 3 (Cajero)
INSERT INTO users 
  (username, fullname, password, avatar, active, trash, role, created_at, updated_at)
VALUES
  ('jmelo', 'Javier Ignacio Melo Fahrion', '$2a$10$EFHTr2qz.BL4Ikrs8/bOueo792RYS7J38uQhoNQ3tBT3x4CeNOcy.', 'default', 1, 0, 3, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  password = '$2a$10$EFHTr2qz.BL4Ikrs8/bOueo792RYS7J38uQhoNQ3tBT3x4CeNOcy.',
  updated_at = NOW();

-- Usuario 2: Fanceska Cerchiaro Andrades
-- RUT: 26.021.432-2 | Email: Franceskacerchiaro2@gmail.com
-- Username: fcerchiaro - Contraseña: fagotto2026 - Rol: 3 (Cajero)
INSERT INTO users 
  (username, fullname, password, avatar, active, trash, role, created_at, updated_at)
VALUES
  ('fcerchiaro', 'Fanceska Cerchiaro Andrades', '$2a$10$EFHTr2qz.BL4Ikrs8/bOueo792RYS7J38uQhoNQ3tBT3x4CeNOcy.', 'default', 1, 0, 3, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  password = '$2a$10$EFHTr2qz.BL4Ikrs8/bOueo792RYS7J38uQhoNQ3tBT3x4CeNOcy.',
  updated_at = NOW();

-- Verificar creación LAS CONDES
SELECT id, username, fullname, role, active, trash, created_at FROM users 
WHERE username IN ('jmelo', 'fcerchiaro')
ORDER BY id DESC;
