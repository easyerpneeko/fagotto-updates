-- =============================================
-- TABLA DE CONFIGURACIÓN DE PEDIDO FINAL
-- Ejecutar en phpMyAdmin de easyerp (DB MAESTRA)
-- Permite activar/desactivar bloqueo por deuda por local
-- =============================================

USE easyerp;

-- Crear tabla de configuración
CREATE TABLE IF NOT EXISTS pedidofinal_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_id INT NOT NULL UNIQUE COMMENT 'ID del local/negocio',
  bloquear_por_deuda TINYINT(1) DEFAULT 0 COMMENT '0 = desactivado, 1 = activado',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_app_id (app_id)
) ENGINE=InnoDB COMMENT='Configuración de pedidos finales por local';

-- =============================================
-- DATOS DE EJEMPLO
-- =============================================

-- Activar bloqueo para algunos locales de prueba (opcional)
-- INSERT INTO pedidofinal_config (app_id, bloquear_por_deuda) VALUES
-- (102, 1),  -- Turbus con bloqueo activado
-- (114, 0);  -- Amunategui sin bloqueo

-- =============================================
-- QUERIES ÚTILES
-- =============================================

-- Ver configuración de todos los locales
SELECT pc.*, a.name_public 
FROM pedidofinal_config pc
LEFT JOIN aplications a ON a.id = pc.app_id;

-- 🔓 Activar bloqueo por deuda para un local específico
INSERT INTO pedidofinal_config (app_id, bloquear_por_deuda) 
VALUES (102, 1)  -- Reemplaza 102 con el app_id del local
ON DUPLICATE KEY UPDATE bloquear_por_deuda = 1;

-- 🔒 Desactivar bloqueo por deuda para un local específico
UPDATE pedidofinal_config 
SET bloquear_por_deuda = 0 
WHERE app_id = 102;  -- Reemplaza 102 con el app_id del local

-- Ver solo locales con bloqueo activado
SELECT pc.app_id, a.name_public, pc.bloquear_por_deuda, pc.updated_at
FROM pedidofinal_config pc
LEFT JOIN aplications a ON a.id = pc.app_id
WHERE pc.bloquear_por_deuda = 1;

SELECT '✅ Tabla pedidofinal_config creada exitosamente' as mensaje;
SELECT '💡 Usa los queries de arriba para activar/desactivar bloqueo por local' as instruccion;
