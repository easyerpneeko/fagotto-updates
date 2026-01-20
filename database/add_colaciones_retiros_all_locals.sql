-- Crear tabla colaciones_retiros en todas las bases de datos de locales (30 locales)
-- Ejecutar este script completo en MySQL

CREATE TABLE IF NOT EXISTS erd_app_espacio_causino_692dbcf185154.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_facturacion_fafotto_66420ee700c12.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_faggotoo_agustina_65a757ec101ac.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_faggotoo_plaza_65a7ddfb3acd5.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_ahumada_6666af8b73317.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_amunategi_687aad4b29200.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_bulnes_678a64c729636.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_food_truck_6928b5232f4dd.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_independencia_66f5c521cd0ba.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_italian_68a79fde57307.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_las_condes_68b5ff81287c2.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_mall_imperio_67dd6d487b0e2.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_manuel_mont_66c8afbb815f9.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_merced_65eaf8c2b8c16.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_moneda_68091fd4e40a7.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_puente_alto_679a2099af4fd.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_quilin_67c6df873105a.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_rancagua_68d4feced7dca.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_rosario_norte_66ba7e785b9ae.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_san_francisco_68d4fedcac0ed.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_suecia_66abda54b5b14.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_turbus_671cdb235d0d1.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_vergara_682ccb8cb0ee6.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_firenze_spa_66aa50b6d3e63.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_kokoro_spa_661ed74ec4469.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_mustang_692dce62ecf6f.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_negocio_prueba_65ae866348b4d.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_rosario_norte_gelato_6789337030f12.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_trai_i_pasti_67b35bfe97f28.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_zis_spa_69500cc4b494b.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_faggotoo_plaza_65a7ddfb3acd5.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_almacen_agustina_65a92c854aae2.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_negocio_prueba_65ae866348b4d.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_merced_65eaf8c2b8c16.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_kokoro_spa_661ed74ec4469.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_facturacion_fafotto_66420ee700c12.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_ahumada_6666af8b73317.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_firenze_spa_66aa50b6d3e63.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_suecia_66abda54b5b14.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_rosario_norte_66ba7e785b9ae.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_manuel_mont_66c8afbb815f9.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_independencia_66f5c521cd0ba.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_turbus_671cdb235d0d1.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_rosario_norte_gelato_6789337030f12.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_bulnes_678a64c729636.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_puente_alto_679a2099af4fd.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_trai_i_pasti_67b35bfe97f28.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_quilin_67c6df873105a.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_mall_imperio_67dd6d487b0e2.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_moneda_68091fd4e40a7.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_vergara_682ccb8cb0ee6.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_amunategi_687aad4b29200.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_italian_68a79fde57307.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_las_condes_68b5ff81287c2.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_rancagua_68d4feced7dca.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_san_francisco_68d4fedcac0ed.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_fagotto_food_truck_6928b5232f4dd.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_espacio_causino_692dbcf185154.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_mustang_692dce62ecf6f.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS erd_app_zis_spa_69500cc4b494b.colaciones_retiros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sell_id INT NOT NULL,
  product_sell_id INT NOT NULL,
  empleado_nombre VARCHAR(255) NOT NULL,
  pasta VARCHAR(50),
  salsa VARCHAR(50),
  fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sell_id (sell_id),
  INDEX idx_empleado (empleado_nombre),
  INDEX idx_fecha (fecha_retiro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
