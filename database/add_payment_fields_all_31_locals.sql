-- ========================================
-- AGREGAR CAMPOS DE PAGO A TODAS LAS 31 BASES DE DATOS LOCALES
-- Ejecutar en: ssh root@posfagotto.cl
-- Luego: mysql -u root -p < este_archivo.sql
-- ========================================

-- 1. Fagotto Agustinas
USE erd_app_faggotoo_agustina_65a757ec101ac;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 2. Fagotto Plaza
USE erd_app_faggotoo_plaza_65a7ddfb3acd5;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 3. Almacen Agustinas
USE erd_app_almacen_agustina_65a92c854aae2;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 4. Negocio Prueba
USE erd_app_negocio_prueba_65ae866348b4d;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 5. Fagotto Merced
USE erd_app_fagotto_merced_65eaf8c2b8c16;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 6. Kokoro Spa
USE erd_app_kokoro_spa_661ed74ec4469;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 7. Facturación Fagotto
USE erd_app_facturacion_fafotto_66420ee700c12;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 8. Fagotto Ahumada
USE erd_app_fagotto_ahumada_6666af8b73317;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 9. Firenze Spa
USE erd_app_firenze_spa_66aa50b6d3e63;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 10. Fagotto Suecia
USE erd_app_fagotto_suecia_66abda54b5b14;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 11. Fagotto Rosario Norte
USE erd_app_fagotto_rosario_norte_66ba7e785b9ae;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 12. Fagotto Manuel Montt
USE erd_app_fagotto_manuel_mont_66c8afbb815f9;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 13. Fagotto Independencia
USE erd_app_fagotto_independencia_66f5c521cd0ba;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 14. Fagotto Turbus
USE erd_app_fagotto_turbus_671cdb235d0d1;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 15. Rosario Norte Gelato
USE erd_app_rosario_norte_gelato_6789337030f12;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 16. Fagotto Bulnes
USE erd_app_fagotto_bulnes_678a64c729636;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 17. Fagotto Puente Alto
USE erd_app_fagotto_puente_alto_679a2099af4fd;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 18. Trai i Pasti
USE erd_app_trai_i_pasti_67b35bfe97f28;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 19. Fagotto Quilín
USE erd_app_fagotto_quilin_67c6df873105a;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 20. Fagotto Mall Imperio
USE erd_app_fagotto_mall_imperio_67dd6d487b0e2;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 21. Fagotto Moneda
USE erd_app_fagotto_moneda_68091fd4e40a7;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 22. Fagotto Vergara
USE erd_app_fagotto_vergara_682ccb8cb0ee6;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 23. Fagotto Amunategi
USE erd_app_fagotto_amunategi_687aad4b29200;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 24. Fagotto Italian
USE erd_app_fagotto_italian_68a79fde57307;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 25. Fagotto Las Condes
USE erd_app_fagotto_las_condes_68b5ff81287c2;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 26. Fagotto Rancagua
USE erd_app_fagotto_rancagua_68d4feced7dca;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 27. Fagotto San Francisco
USE erd_app_fagotto_san_francisco_68d4fedcac0ed;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 28. Fagotto Food Truck
USE erd_app_fagotto_food_truck_6928b5232f4dd;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 29. Espacio Causino
USE erd_app_espacio_causino_692dbcf185154;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 30. Mustang
USE erd_app_mustang_692dce62ecf6f;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- 31. Zis Spa
USE erd_app_zis_spa_69500cc4b494b;
ALTER TABLE requests ADD COLUMN payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER status;
ALTER TABLE requests ADD COLUMN payment_id VARCHAR(255) AFTER payment_status;
ALTER TABLE requests ADD COLUMN payment_method VARCHAR(50) AFTER payment_id;
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- ========================================
-- ¡COMPLETADO! Verificación
-- ========================================
SELECT '✅ Script completado - 31 bases de datos actualizadas' AS mensaje;
