-- ========================================
-- IMPORTACIÓN COMPLETA MERCHISE
-- Fecha: 2026-01-20
-- Descripción: Limpia TODO e importa 31 productos nuevos
-- ========================================

USE easyerp;

-- ========================================
-- PASO 0: ELIMINAR TABLAS EXISTENTES
-- ========================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS merchise_item_modifiers;
DROP TABLE IF EXISTS merchise_modifier_options;
DROP TABLE IF EXISTS merchise_items;
DROP TABLE IF EXISTS merchise_options;
DROP TABLE IF EXISTS merchise_modifiers;
DROP TABLE IF EXISTS merchise_sections;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- PASO 1: CREAR TABLAS DESDE CERO
-- ========================================

-- Tabla de secciones (categorías principales)
CREATE TABLE merchise_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de productos
CREATE TABLE merchise_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    section_id INT NOT NULL,
    image VARCHAR(500),
    orden INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (section_id) REFERENCES merchise_sections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de modificadores (grupos de opciones)
CREATE TABLE merchise_modifiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    required TINYINT(1) DEFAULT 0,
    min_selections INT DEFAULT 1,
    max_selections INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de opciones (opciones individuales)
CREATE TABLE merchise_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla relación: modificador tiene opciones
CREATE TABLE merchise_modifier_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modifier_id INT NOT NULL,
    option_id INT NOT NULL,
    orden INT DEFAULT 0,
    FOREIGN KEY (modifier_id) REFERENCES merchise_modifiers(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES merchise_options(id) ON DELETE CASCADE,
    UNIQUE KEY unique_modifier_option (modifier_id, option_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla relación: producto tiene modificadores
CREATE TABLE merchise_item_modifiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    modifier_id INT NOT NULL,
    FOREIGN KEY (item_id) REFERENCES merchise_items(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_id) REFERENCES merchise_modifiers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_item_modifier (item_id, modifier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- PASO 2: INSERTAR SECCIONES (5 categorías)
-- ========================================
INSERT INTO merchise_sections (id, name, sku, description, orden) VALUES
(1, 'Pasta', 'PASTA', 'Pastas artesanales con salsas a elegir', 1),
(2, 'Combos', 'COMBOS', 'Combos de pasta + bebida', 2),
(3, 'Para compartir', 'PARA_COMPARTIR', 'Promociones para compartir', 3),
(4, 'Sandwichis', 'SANDWICHIS', 'Ciabattas de masa madre', 4),
(5, 'Family Party', 'FAMILY_PARTY', 'Combos familiares', 5);

-- ========================================
-- PASO 3: INSERTAR PRODUCTOS (31 productos)
-- ========================================

-- SECCIÓN 1: PASTA (8 productos) - Orden: 100-199
INSERT INTO merchise_items (sku, name, description, price, section_id, image, orden) VALUES
('13c6e5f7-5902-4519-a138-2fa05094aa7e', 'Pasta Bigoli 1 Salsa', 'Eligie 1 salsa de las 5 opciones', 5990, 1, '', 101),
('cc229610-0720-401b-9606-6f48a907e0fd', 'Pastas Fettuccini 1 Salsa', 'Eligie 1 salsa de las 5 opciones', 5990, 1, '', 102),
('f6eb0cf5-3329-4aaf-9110-c4422faef706', 'Noquis 1 Salsa', 'Eligie 1 salsa de las 5 opciones', 5990, 1, '', 103),
('5ffdf235-abe5-4d4d-9dea-9e5d918bf969', 'Noquis Fritos 1 Salsa', 'Eligie 1 salsa de las 5 opciones', 5990, 1, '', 104),
('3eae2840-163e-4597-ad98-23786f26bd67', 'Pasta Bigoli 2 Salsa', 'Eligie 2 salsa de las 5 opciones', 6390, 1, '', 111),
('58ca607a-fc4a-4216-9b6e-c3519968cf14', 'Pastas Fettuccini 2 Salsa', 'Eligie 2 salsa de las 5 opciones', 6390, 1, '', 112),
('bbc2d11b-bf78-4d03-93ce-443f722c688a', 'Noquis 2 Salsa', 'Eligie 2 salsa de las 5 opciones', 6390, 1, '', 113),
('30449ade-d202-4372-b2dd-d4edf2c901a5', 'Noquis Fritos 2 Salsa', 'Eligie 2 salsa de las 5 opciones', 6390, 1, '', 114);

-- SECCIÓN 2: COMBOS (9 productos) - Orden: 200-299
INSERT INTO merchise_items (sku, name, description, price, section_id, image, orden) VALUES
('aba45499-8957-48b3-8c54-d63e06033b96', 'Pasta Bigoli con Salsa Cheddar + Bebida Lata', 'Pasta bigoli al dente con cremosa salsa cheddar. Sabor intenso, textura perfecta y un plato ideal para los amantes del queso.', 5990, 2, '', 200),
('52ef85a9-1e80-4e19-ba18-e1b75fa680d5', 'Pasta Bigoli + Bebida Lata', 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 6990, 2, '', 201),
('b534f8c8-09d5-4a96-8309-bcaf4726bc87', 'Pasta Fettuccine + Bebida Lata', 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 6990, 2, '', 202),
('854a57e8-a8c9-475c-b913-5c1736c26daf', 'Noquis 1 Salsa + Bebida Lata', 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 6990, 2, '', 203),
('fa404154-3856-443c-a369-3c451cf53c82', 'Noquis Fritos 1 Salsa + Bebida Lata', 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 6990, 2, '', 204),
('bf15d3dc-92ef-4bf6-983c-cb70fcb1c488', 'Pasta Bigoli 2 Salsas + Bebida Lata', 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 7290, 2, '', 211),
('4fe3f583-fce1-43af-a482-945710b631b7', 'Pasta Fettuccine 2 Salsa + Bebida Lata', 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 7290, 2, '', 212),
('feda54dd-7516-48aa-b305-86cadafd2042', 'Noquis 2 Salsa + Bebida Lata', 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 7290, 2, '', 213),
('37615b6b-eb7f-48b8-87e6-3a5f9f3ae7c1', 'Noquis Fritos 2 Salsa + Bebida Lata', 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 7290, 2, '', 214);

-- SECCIÓN 3: PARA COMPARTIR (3 productos) - Orden: 300-399
INSERT INTO merchise_items (sku, name, description, price, section_id, image, orden) VALUES
('6d034018-7da5-42c1-a4f7-1a0cefe0c61a', '2 Pastas a eleccion + 2 bebida', '2 Pastas a eleccion + 2 bebida', 13480, 3, '', 301),
('097b5b5e-6675-4d78-83c3-5b03e8e341c9', '3 Pastas a eleccion + Bebida Familiar 1,5 Lt', '3 Pastas a eleccion + 1 bebida', 20470, 3, '', 302),
('101abbd4-52df-4084-afb8-6cf5ab91617f', '4 Pastas a eleccion + Bebida Familiar', '4 Pastas a eleccion + 1 bebida', 26960, 3, '', 303);

-- SECCIÓN 4: SANDWICHIS (8 productos) - Orden: 400-499
INSERT INTO merchise_items (sku, name, description, price, section_id, image, orden) VALUES
('1326ad45-8ad7-4beb-aa25-a35382466b44', 'Ciabatta Pesto', 'Pan de masa madre artesanal de parmesano, albahaca, tomate cherry y mozzarella.', 3590, 4, '', 401),
('a11aa8b6-1c17-468d-aee7-96016d72ef1c', 'Ciabatta Salami', 'Pan de masa madre con salami italiano, queso crema y tomate seco.', 3590, 4, '', 402),
('f066e015-390a-4bcd-a3d5-de9b5e4fbdd3', 'Ciabatta Alleato', 'Pan de masa madre con jamón serrano y queso manchego.', 2990, 4, '', 403),
('eb387c0a-3532-44a2-b09e-a3f9f1054d90', 'Ciabatta Pesto + Bebida Lata', 'Pan de masa madre artesanal de parmesano, albahaca, tomate cherry y mozzarella.', 4990, 4, '', 411),
('80cdfc7a-a0ad-42b0-bc2f-e5048d8f2ad3', 'Ciabatta Salami + Bebida Lata', 'Pan de masa madre con salami italiano, queso crema y tomate seco.', 4990, 4, '', 412),
('6ab91854-33ce-464a-9ace-e8622bb1feda', 'Ciabatta Alleato + Bebida Lata', 'Pan de masa madre con jamón serrano y queso manchego.', 4590, 4, '', 413);

-- SECCIÓN 5: FAMILY PARTY (4 productos) - Orden: 500-599
INSERT INTO merchise_items (sku, name, description, price, section_id, image, orden) VALUES
('821988d3-8b21-46cc-b731-cbe83b5f943a', 'Fontana 2 Salsa', 'Elige dos tipos de pasta y dos salsas para compartir y disfrutar variedad.', 7590, 5, '', 501),
('2efdc598-b812-4004-b6ac-3337c9374097', 'Fontana 2 Salsas + Bebida Familiar', 'Dos tipos de pasta con dos salsas y bebida familiar. Ideal para compartir en casa.', 9900, 5, '', 502),
('9de96de0-60e8-49ca-b7c2-9a05a7401e42', '2 pastas a eleccion +2 ciabatta + Bebida Familiar', 'Dos pastas con salsas, dos ciabattas y bebida familiar. Completo y perfecto para compartir.', 16990, 5, '', 503),
('7e2e01cc-e13a-4f55-a4fe-cfdbad46b68d', '2 Ciabattas+ 1 Noquis frito + 2 Pastas', 'Dos ciabattas, ñoquis fritos con salsa y dos pastas con salsas. Ideal para compartir variedad.', 16990, 5, '', 504);

-- ========================================
-- PASO 4: INSERTAR MODIFICADORES (3 grupos)
-- ========================================
INSERT INTO merchise_modifiers (id, name, sku, required, min_selections, max_selections) VALUES
(1, 'Elige tu salsa', 'SALSA_1', 1, 1, 1),
(2, 'Elige tus 2 salsas', 'SALSA_2', 1, 2, 2),
(3, 'Elige sabor de tu bebida', 'LATA_1', 1, 1, 1),
(4, 'Elige tus 2 bebidas', 'LATA_2', 1, 2, 2),
(5, 'Elige tipo de pasta 1', 'PASTA_TIPO_1', 1, 1, 1),
(6, 'Elige salsa para pasta 1', 'SALSA_PASTA_1', 1, 1, 1),
(7, 'Elige tipo de pasta 2', 'PASTA_TIPO_2', 1, 1, 1),
(8, 'Elige salsa para pasta 2', 'SALSA_PASTA_2', 1, 1, 1),
(9, 'Elige tipo de pasta 3', 'PASTA_TIPO_3', 1, 1, 1),
(10, 'Elige salsa para pasta 3', 'SALSA_PASTA_3', 1, 1, 1),
(11, 'Elige tipo de pasta 4', 'PASTA_TIPO_4', 1, 1, 1),
(12, 'Elige salsa para pasta 4', 'SALSA_PASTA_4', 1, 1, 1),
(13, 'Elige tu bebida familiar', 'BEBIDA_FAMILIAR', 1, 1, 1);

-- ========================================
-- PASO 5: INSERTAR OPCIONES (6 salsas + 4 bebidas + 4 tipos de pasta)
-- ========================================
-- SKUs tomados de selectores.txt COLUMNA 9
INSERT INTO merchise_options (id, sku, name, price) VALUES
-- Salsas (1-6)
(1, '09201e81-bc1f-44fb-9310-6d7e3d0c541f', 'Salsa Boleñosa tradicional', 0),
(2, '91aeabb1-5a2a-4aa1-a5f1-ff8d53883e01', 'Salsa Bechamel con Jamón', 0),
(3, 'c4fa3db4-40cb-4c4e-bd5f-a33ff51185dc', 'Pesto de Albaca, Queso y Mani', 0),
(4, '267b22b3-1762-47d5-aff8-e67f0ed33959', 'Salsa Bechamel con Camarones', 0),
(5, '745f1435-ec21-4411-b46e-45ca65cdae05', 'Salsa Bechamel con Champiñones', 0),
(6, '896c4233-bc60-49d6-80f4-ec6fadb8deba', 'Salsa Bechamel con Pollo y mostaza', 0),
-- Bebidas (7-10)
(7, '43f1193c-9b3c-476b-8343-c0bc9f22f634', 'Pepsi Original 350 ml', 0),
(8, 'e3f20170-569e-49cf-9f0c-08eead2defc6', 'Pepsi Zero 350 ml', 0),
(9, '1074ef2b-9dc3-417c-be0f-deb1e1a73651', '7up 350 ml', 0),
(10, '6c5df17b-c024-415f-8d45-8667c5620743', 'Cruchs 350 ml', 0),
-- Tipos de Pasta (11-14)
(11, '13c6e5f7-5902-4519-a138-2fa05094aa7e', 'Pasta Bigoli', 0),
(12, 'cc229610-0720-401b-9606-6f48a907e0fd', 'Pasta Fettuccini', 0),
(13, 'f6eb0cf5-3329-4aaf-9110-c4422faef706', 'Noquis', 0),
(14, '5ffdf235-abe5-4d4d-9dea-9e5d918bf969', 'Noquis Fritos', 0),
-- Bebidas Familiares 1.5L (15-18)
(15, 'bebida-pepsi-1.5', 'Pepsi 1.5L', 0),
(16, 'bebida-pepsi-zero-1.5', 'Pepsi Zero 1.5L', 0),
(17, 'bebida-7up-1.5', '7up 1.5L', 0),
(18, 'bebida-cachantun-1.5', 'Cachantun 1.5L', 0);

-- ========================================
-- PASO 6: RELACIONAR MODIFICADORES CON OPCIONES
-- ========================================
-- Modifier SALSA_1 tiene las 6 salsas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(1, 1, 1),
(1, 2, 2),
(1, 3, 3),
(1, 4, 4),
(1, 5, 5),
(1, 6, 6);

-- Modifier SALSA_2 tiene las 6 salsas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(2, 1, 1),
(2, 2, 2),
(2, 3, 3),
(2, 4, 4),
(2, 5, 5),
(2, 6, 6);

-- Modifier LATA_1 tiene las 4 bebidas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(3, 7, 1),
(3, 8, 2),
(3, 9, 3),
(3, 10, 4);

-- Modifier LATA_2 tiene las 4 bebidas (para elegir 2)
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(4, 7, 1),
(4, 8, 2),
(4, 9, 3),
(4, 10, 4);

-- Modifier PASTA_TIPO_1 tiene las 4 pastas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(5, 11, 1),
(5, 12, 2),
(5, 13, 3),
(5, 14, 4);

-- Modifier SALSA_PASTA_1 tiene las 6 salsas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(6, 1, 1),
(6, 2, 2),
(6, 3, 3),
(6, 4, 4),
(6, 5, 5),
(6, 6, 6);

-- Modifier PASTA_TIPO_2 tiene las 4 pastas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(7, 11, 1),
(7, 12, 2),
(7, 13, 3),
(7, 14, 4);

-- Modifier SALSA_PASTA_2 tiene las 6 salsas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(8, 1, 1),
(8, 2, 2),
(8, 3, 3),
(8, 4, 4),
(8, 5, 5),
(8, 6, 6);

-- Modifier PASTA_TIPO_3 tiene las 4 pastas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(9, 11, 1),
(9, 12, 2),
(9, 13, 3),
(9, 14, 4);

-- Modifier SALSA_PASTA_3 tiene las 6 salsas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(10, 1, 1),
(10, 2, 2),
(10, 3, 3),
(10, 4, 4),
(10, 5, 5),
(10, 6, 6);

-- Modifier PASTA_TIPO_4 tiene las 4 pastas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(11, 11, 1),
(11, 12, 2),
(11, 13, 3),
(11, 14, 4);

-- Modifier SALSA_PASTA_4 tiene las 6 salsas
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(12, 1, 1),
(12, 2, 2),
(12, 3, 3),
(12, 4, 4),
(12, 5, 5),
(12, 6, 6);

-- Modifier BEBIDA_FAMILIAR tiene las 4 bebidas de 1.5L
INSERT INTO merchise_modifier_options (modifier_id, option_id, orden) VALUES
(13, 15, 1),
(13, 16, 2),
(13, 17, 3),
(13, 18, 4);

-- ========================================
-- PASO 7: RELACIONAR PRODUCTOS CON MODIFICADORES
-- ========================================

-- PASTA con 1 SALSA (4 productos)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 1 FROM merchise_items WHERE sku IN (
    '13c6e5f7-5902-4519-a138-2fa05094aa7e',  -- Pasta Bigoli 1 Salsa
    'cc229610-0720-401b-9606-6f48a907e0fd',  -- Pastas Fettuccini 1 Salsa
    'f6eb0cf5-3329-4aaf-9110-c4422faef706',  -- Noquis 1 Salsa
    '5ffdf235-abe5-4d4d-9dea-9e5d918bf969'   -- Noquis Fritos 1 Salsa
);

-- PASTA con 2 SALSAS (4 productos)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 2 FROM merchise_items WHERE sku IN (
    '3eae2840-163e-4597-ad98-23786f26bd67',  -- Pasta Bigoli 2 Salsa
    '58ca607a-fc4a-4216-9b6e-c3519968cf14',  -- Pastas Fettuccini 2 Salsa
    'bbc2d11b-bf78-4d03-93ce-443f722c688a',  -- Noquis 2 Salsa
    '30449ade-d202-4372-b2dd-d4edf2c901a5'   -- Noquis Fritos 2 Salsa
);

-- COMBOS con 1 SALSA + 1 BEBIDA (4 productos)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 1 FROM merchise_items WHERE sku IN (
    '52ef85a9-1e80-4e19-ba18-e1b75fa680d5',  -- Pasta Bigoli + Bebida Lata
    'b534f8c8-09d5-4a96-8309-bcaf4726bc87',  -- Pasta Fettuccine + Bebida Lata
    '854a57e8-a8c9-475c-b913-5c1736c26daf',  -- Noquis 1 Salsa + Bebida Lata
    'fa404154-3856-443c-a369-3c451cf53c82'   -- Noquis Fritos 1 Salsa + Bebida Lata
);

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 3 FROM merchise_items WHERE sku IN (
    '52ef85a9-1e80-4e19-ba18-e1b75fa680d5',  -- Pasta Bigoli + Bebida Lata
    'b534f8c8-09d5-4a96-8309-bcaf4726bc87',  -- Pasta Fettuccine + Bebida Lata
    '854a57e8-a8c9-475c-b913-5c1736c26daf',  -- Noquis 1 Salsa + Bebida Lata
    'fa404154-3856-443c-a369-3c451cf53c82'   -- Noquis Fritos 1 Salsa + Bebida Lata
);

-- COMBOS con 2 SALSAS + 1 BEBIDA (4 productos)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 2 FROM merchise_items WHERE sku IN (
    'bf15d3dc-92ef-4bf6-983c-cb70fcb1c488',  -- Pasta Bigoli 2 Salsas + Bebida Lata
    '4fe3f583-fce1-43af-a482-945710b631b7',  -- Pasta Fettuccine 2 Salsa + Bebida Lata
    'feda54dd-7516-48aa-b305-86cadafd2042',  -- Noquis 2 Salsa + Bebida Lata
    '37615b6b-eb7f-48b8-87e6-3a5f9f3ae7c1'   -- Noquis Fritos 2 Salsa + Bebida Lata
);

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 3 FROM merchise_items WHERE sku IN (
    'bf15d3dc-92ef-4bf6-983c-cb70fcb1c488',  -- Pasta Bigoli 2 Salsas + Bebida Lata
    '4fe3f583-fce1-43af-a482-945710b631b7',  -- Pasta Fettuccine 2 Salsa + Bebida Lata
    'feda54dd-7516-48aa-b305-86cadafd2042',  -- Noquis 2 Salsa + Bebida Lata
    '37615b6b-eb7f-48b8-87e6-3a5f9f3ae7c1'   -- Noquis Fritos 2 Salsa + Bebida Lata
);

-- COMBO Pasta Bigoli Cheddar + Bebida (solo bebida, salsa cheddar fija)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 3 FROM merchise_items WHERE sku = 'aba45499-8957-48b3-8c54-d63e06033b96';  -- LATA_1

-- CIABATTAS con BEBIDA (solo bebida)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 3 FROM merchise_items WHERE sku IN (
    'eb387c0a-3532-44a2-b09e-a3f9f1054d90',  -- Ciabatta Pesto + Bebida Lata
    '80cdfc7a-a0ad-42b0-bc2f-e5048d8f2ad3',  -- Ciabatta Salami + Bebida Lata
    '6ab91854-33ce-464a-9ace-e8622bb1feda'   -- Ciabatta Alleato + Bebida Lata
);

-- PARA_COMPARTIR: 2 Pastas a eleccion + 2 bebida (4 modifiers: tipo pasta 1, salsa 1, tipo pasta 2, salsa 2, 2 bebidas)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 5 FROM merchise_items WHERE sku = '6d034018-7da5-42c1-a4f7-1a0cefe0c61a';  -- PASTA_TIPO_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 6 FROM merchise_items WHERE sku = '6d034018-7da5-42c1-a4f7-1a0cefe0c61a';  -- SALSA_PASTA_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 7 FROM merchise_items WHERE sku = '6d034018-7da5-42c1-a4f7-1a0cefe0c61a';  -- PASTA_TIPO_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 8 FROM merchise_items WHERE sku = '6d034018-7da5-42c1-a4f7-1a0cefe0c61a';  -- SALSA_PASTA_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 4 FROM merchise_items WHERE sku = '6d034018-7da5-42c1-a4f7-1a0cefe0c61a';  -- LATA_2 (2 bebidas)

-- PARA_COMPARTIR: 3 Pastas a eleccion + Bebida Familiar (6 modifiers: 3 tipos de pasta + 3 salsas + 1 bebida familiar)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 5 FROM merchise_items WHERE sku = '097b5b5e-6675-4d78-83c3-5b03e8e341c9';  -- PASTA_TIPO_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 6 FROM merchise_items WHERE sku = '097b5b5e-6675-4d78-83c3-5b03e8e341c9';  -- SALSA_PASTA_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 7 FROM merchise_items WHERE sku = '097b5b5e-6675-4d78-83c3-5b03e8e341c9';  -- PASTA_TIPO_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 8 FROM merchise_items WHERE sku = '097b5b5e-6675-4d78-83c3-5b03e8e341c9';  -- SALSA_PASTA_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 9 FROM merchise_items WHERE sku = '097b5b5e-6675-4d78-83c3-5b03e8e341c9';  -- PASTA_TIPO_3

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 10 FROM merchise_items WHERE sku = '097b5b5e-6675-4d78-83c3-5b03e8e341c9';  -- SALSA_PASTA_3

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 13 FROM merchise_items WHERE sku = '097b5b5e-6675-4d78-83c3-5b03e8e341c9';  -- BEBIDA_FAMILIAR

-- PARA_COMPARTIR: 4 Pastas a eleccion + Bebida Familiar (8 modifiers: 4 tipos de pasta + 4 salsas + 1 bebida familiar)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 5 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- PASTA_TIPO_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 6 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- SALSA_PASTA_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 7 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- PASTA_TIPO_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 8 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- SALSA_PASTA_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 9 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- PASTA_TIPO_3

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 10 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- SALSA_PASTA_3

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 11 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- PASTA_TIPO_4

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 12 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- SALSA_PASTA_4

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 13 FROM merchise_items WHERE sku = '101abbd4-52df-4084-afb8-6cf5ab91617f';  -- BEBIDA_FAMILIAR

-- FAMILY_PARTY: Fontana 2 Salsa (2 tipos pasta + 2 salsas)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 5 FROM merchise_items WHERE sku = '821988d3-8b21-46cc-b731-cbe83b5f943a';  -- PASTA_TIPO_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 6 FROM merchise_items WHERE sku = '821988d3-8b21-46cc-b731-cbe83b5f943a';  -- SALSA_PASTA_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 7 FROM merchise_items WHERE sku = '821988d3-8b21-46cc-b731-cbe83b5f943a';  -- PASTA_TIPO_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 8 FROM merchise_items WHERE sku = '821988d3-8b21-46cc-b731-cbe83b5f943a';  -- SALSA_PASTA_2

-- FAMILY_PARTY: Fontana 2 Salsas + Bebida Familiar (2 tipos pasta + 2 salsas + bebida)
INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 5 FROM merchise_items WHERE sku = '2efdc598-b812-4004-b6ac-3337c9374097';  -- PASTA_TIPO_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 6 FROM merchise_items WHERE sku = '2efdc598-b812-4004-b6ac-3337c9374097';  -- SALSA_PASTA_1

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 7 FROM merchise_items WHERE sku = '2efdc598-b812-4004-b6ac-3337c9374097';  -- PASTA_TIPO_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 8 FROM merchise_items WHERE sku = '2efdc598-b812-4004-b6ac-3337c9374097';  -- SALSA_PASTA_2

INSERT INTO merchise_item_modifiers (item_id, modifier_id)
SELECT id, 13 FROM merchise_items WHERE sku = '2efdc598-b812-4004-b6ac-3337c9374097';  -- BEBIDA_FAMILIAR

-- ========================================
-- VERIFICACIÓN
-- ========================================
SELECT 
    'Secciones' AS Tipo, 
    COUNT(*) AS Total,
    GROUP_CONCAT(name SEPARATOR ', ') AS Detalle
FROM merchise_sections

UNION ALL

SELECT 
    'Productos' AS Tipo, 
    COUNT(*) AS Total,
    CONCAT(COUNT(*), ' productos') AS Detalle
FROM merchise_items

UNION ALL

SELECT 
    'Modificadores' AS Tipo, 
    COUNT(*) AS Total,
    GROUP_CONCAT(name SEPARATOR ', ') AS Detalle
FROM merchise_modifiers

UNION ALL

SELECT 
    'Opciones' AS Tipo, 
    COUNT(*) AS Total,
    CONCAT(COUNT(*), ' salsas') AS Detalle
FROM merchise_options

UNION ALL

SELECT 
    'Productos con modificadores' AS Tipo, 
    COUNT(DISTINCT item_id) AS Total,
    CONCAT(COUNT(DISTINCT item_id), ' de 31 productos') AS Detalle
FROM merchise_item_modifiers;

-- ========================================
-- FIN DEL SCRIPT
-- ========================================
-- RESULTADO ESPERADO:
-- - 5 secciones
-- - 29 productos (8 pasta, 9 combos, 3 para compartir, 6 sandwichis, 4 family party)
-- - 13 modificadores (SALSA_1, SALSA_2, LATA_1, LATA_2, PASTA_TIPO_1-4, SALSA_PASTA_1-4, BEBIDA_FAMILIAR)
-- - 18 opciones (6 salsas + 4 bebidas lata + 4 tipos pasta + 4 bebidas familiares)
-- - Productos con modificadores:
--   * 8 PASTA: 4 con SALSA_1, 4 con SALSA_2
--   * 9 COMBOS: 1 con LATA_1, 4 con SALSA_1+LATA_1, 4 con SALSA_2+LATA_1
--   * 3 CIABATTAS con LATA_1
--   * 3 PARA_COMPARTIR con selectores complejos
--   * 2 FAMILY_PARTY con selectores complejos
-- - Productos simples sin modificadores: 3 ciabattas, 2 family party complejos
-- ========================================
