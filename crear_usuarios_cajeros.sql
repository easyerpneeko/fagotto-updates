-- =========================================================
-- CREAR USUARIOS CAJEROS - FAGOTTO ERP
-- Fecha: 28 de Enero 2026
-- Rol: 3 (Cajero)
-- =========================================================

-- =========================================================
-- USUARIO 1: ANDREA BOULANGER
-- =========================================================
-- NOMBRE: ANDREA ZENAIDA BOULANGER VILLEGAS
-- RUT: 33.691.119-2
-- CORREO: andreaboulanger710@gmail.com
-- USERNAME: aboulanger
-- CONTRASEÑA: aboulanger
-- ROL: 3 (Cajero)
-- =========================================================

INSERT INTO `users` 
  (`username`, `fullname`, `password`, `avatar`, `active`, `trash`, `role`, `created_at`, `updated_at`)
VALUES
  (
    'aboulanger',                                                               -- Username
    'Andrea Boulanger',                                                         -- Nombre completo
    '$2y$10$vbNphxL9aXPSuQH4QNWatusX8SwhPwsCG07p6QwCpTPl2Wn0m3NrW',          -- Password: aboulanger
    'default',                                                                  -- Avatar
    1,                                                                          -- Activo
    0,                                                                          -- No eliminado
    3,                                                                          -- ROL: Cajero (ID 3)
    NOW(),                                                                      -- Fecha creación
    NOW()                                                                       -- Fecha actualización
  );

-- =========================================================
-- USUARIO 2: JUAN ARAYA
-- =========================================================
-- NOMBRE: JUAN IGNACIO ARAYA FERNANDEZ
-- RUT: 19.321.340-5
-- CORREO: juanoaraya20@gmail.com
-- USERNAME: jaraya
-- CONTRASEÑA: jaraya
-- ROL: 3 (Cajero)
-- =========================================================

INSERT INTO `users` 
  (`username`, `fullname`, `password`, `avatar`, `active`, `trash`, `role`, `created_at`, `updated_at`)
VALUES
  (
    'jaraya',                                                                   -- Username
    'Juan Araya',                                                               -- Nombre completo
    '$2y$10$d1VLyrA6y7E7UFSFyO2ngus1XCzCyBjj/zw89PQeyVBGysAx45e0O',          -- Password: jaraya
    'default',                                                                  -- Avatar
    1,                                                                          -- Activo
    0,                                                                          -- No eliminado
    3,                                                                          -- ROL: Cajero (ID 3)
    NOW(),                                                                      -- Fecha creación
    NOW()                                                                       -- Fecha actualización
  );

-- =========================================================
-- VERIFICACIÓN: Listar usuarios creados
-- =========================================================

SELECT 
  u.id,
  u.username,
  u.fullname,
  u.active AS activo,
  u.trash AS eliminado,
  t.name AS rol_nombre,
  u.created_at AS fecha_creacion
FROM users u
LEFT JOIN type_users t ON u.role = t.id
WHERE u.username IN ('aboulanger', 'jaraya')
ORDER BY u.created_at DESC;

-- =========================================================
-- DATOS DE ACCESO PARA LOS USUARIOS
-- =========================================================
-- 
-- USUARIO 1:
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- Nombre: Andrea Boulanger Villegas
-- RUT: 33.691.119-2
-- Email: andreaboulanger710@gmail.com
-- Usuario: aboulanger
-- Contraseña: aboulanger
-- Rol: Cajero
-- 
-- USUARIO 2:
-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
-- Nombre: Juan Araya Fernandez
-- RUT: 19.321.340-5
-- Email: juanoaraya20@gmail.com
-- Usuario: jaraya
-- Contraseña: jaraya
-- Rol: Cajero
-- 
-- =========================================================
-- PERMISOS DEL ROL CAJERO (ID 3):
-- =========================================================
-- ✓ obtener_mesas
-- ✓ obtener_meseros
-- ✓ crear_venta
-- ✓ getionar_tickets
-- ✓ gestionar_ventas
-- ✓ productos_obtener
-- ✓ productos_gestion
-- ✓ productos_modificar_stock
-- ✓ productos_categorias_gestion
-- ✓ productos_categorias_obtener
-- ✓ gestionar_gastos
-- ✓ monto_inicial
-- =========================================================
