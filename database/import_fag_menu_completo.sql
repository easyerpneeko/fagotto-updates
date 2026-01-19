-- ============================================================================
-- IMPORTACIÓN COMPLETA DEL MENÚ FAGOTTO DESDE EXCEL
-- Archivo fuente: fag-menu-20260106.xlsx
-- Fecha: 19/01/2026
-- ============================================================================

-- Limpiar tablas en orden correcto (respetando foreign keys)
DELETE FROM merchise_pedidos_options;
DELETE FROM merchise_pedidos_modifiers;
DELETE FROM merchise_pedidos_items;
DELETE FROM merchise_pedidos;
DELETE FROM merchise_options;
DELETE FROM merchise_modifiers;
DELETE FROM merchise_items;
DELETE FROM merchise_sections;

-- ============================================================================
-- 1. CREAR SECCIONES
-- ============================================================================

INSERT INTO `merchise_sections` (`id`, `sku`, `name`, `description`, `orden`, `activo`) VALUES
(1, '1', 'Combos', 'Nuestros combos más populares', 1, 1),
(41, '41', 'Pastas bigoli', 'Pasta artesanal italiana fresca', 2, 1),
(55, '55', 'Bebidas', 'Bebidas refrescantes para acompañar', 3, 1),
(60, 'PASTA', 'Pastas', 'Pastas frescas con salsas a elegir', 4, 1),
(70, 'COMBOS', 'Combos con Bebida', 'Combos de pasta con bebida incluida', 5, 1),
(80, 'PARA_COMPARTIR', 'Para Compartir', 'Packs familiares para compartir', 6, 1),
(90, 'SANDWICHIS', 'Sándwich', 'Ciabattas artesanales', 7, 1),
(100, 'FAMILY_PARTY', 'Family Party', 'Combos especiales para fiestas', 8, 1);

-- ============================================================================
-- 2. INSERTAR PRODUCTOS (46 total)
-- ============================================================================

-- COMBOS BÁSICOS (Sección 1)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `image`, `available`, `orden`) VALUES
(1, '2.0', 'Combo individual', 7550.00, 'Pasta y bebida de 350 ml a elegir.', 'https://tb-static.uber.com/prod/image-proc/processed_images/3bb79d3d936054cc2c908b5d58d39b23/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 1),
(1, '15.0', 'Combo para 2', 15090.00, '2 Pastas y 2 bebidas de 350 ml a elegir.', 'https://tb-static.uber.com/prod/image-proc/processed_images/b9e0541d5e91b52ae92629edb74ee949/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 2),
(1, '28.0', 'Combo familiar', 30190.00, '4 Pastas y 4 bebidas de 350 ml a elegir.', 'https://tb-static.uber.com/prod/image-proc/processed_images/783c70459dc7fe10c5d8a57af889a4b9/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 3),
(1, 'Combo_individual_2_s', 'Combo individual 2 salsas', 8430.00, 'Elige 2 salsas para tu pasta y 1 bebida de 350 ml.', 'https://tb-static.uber.com/prod/image-proc/processed_images/d412f0aef9da2af389c21ec0eeb922de/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 4);

-- PASTAS BIGOLI INDIVIDUALES (Sección 41)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `image`, `available`, `orden`) VALUES
(41, '42.0', 'Pasta pesto', 6290.00, 'Pasta bigoli artesanal con salsa pesto.', 'https://tb-static.uber.com/prod/image-proc/processed_images/d4eab2a1d67b1daa1ec996c0a13bd03d/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 1),
(41, '44.0', 'Pasta Alfredo', 6290.00, 'Pasta bigoli artesanal con salsa alfredo.', 'https://tb-static.uber.com/prod/image-proc/processed_images/2e4792507f77e9d35e1eed97bdc2ea49/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 2),
(41, '47.0', 'Pasta boloñesa', 6290.00, 'Pasta bigoli artesanal con salsa boloñesa.', 'https://tb-static.uber.com/prod/image-proc/processed_images/aaa51d1274c11bbb482a45a32fd28687/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 3),
(41, '48.0', 'Pasta champiñon', 6290.00, 'Pasta bigoli artesanal con champiñones.', 'https://tb-static.uber.com/prod/image-proc/processed_images/ee5b174cc1e5c7ffcec0928abf0ff197/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 4),
(41, 'Pasta_Camarón', 'Pasta Camarón', 6290.00, 'Pasta bigoli, salsa blanca con camarones', 'https://tb-static.uber.com/prod/image-proc/processed_images/0417e907f88ae03ebf9aa7a34f8ae5d2/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 5),
(41, 'Pasta_Pollo_Mostaza', 'Pasta Pollo Mostaza', 6290.00, 'Pollo, crema, mostaza.', 'https://tb-static.uber.com/prod/image-proc/processed_images/530fa8cb5613d07de3a5069b8f661005/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 6);

-- BEBIDAS (Sección 55)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `image`, `available`, `orden`) VALUES
(55, '56.0', 'Coca-Cola Original', 2800.00, '350 ml.', 'https://tb-static.uber.com/prod/image-proc/processed_images/7d6bb40d97275d6b381f6f9c66fff4cf/7f4ae9ca0446cbc23e71d8d395a98428.jpeg', 1, 1),
(55, '57.0', 'Sprite Original', 2800.00, '350 ml.', 'https://tb-static.uber.com/prod/image-proc/processed_images/901d987e535883f799ca45f8e3fbbfb5/7f4ae9ca0446cbc23e71d8d395a98428.jpeg', 1, 2),
(55, '58.0', 'Sprite Sin azucar', 2800.00, '350 ml.', 'https://tb-static.uber.com/prod/image-proc/processed_images/6584afc99aa05494c5a9c59de3a85e25/7f4ae9ca0446cbc23e71d8d395a98428.jpeg', 1, 3);

-- SÁNDWICH (Sección 90)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `image`, `available`, `orden`) VALUES
(90, 'Focaccia_pesto', 'Focaccia pesto', 3770.00, 'Pan artesanal, pesto, pomodoro, jamón y queso', 'https://tb-static.uber.com/prod/image-proc/processed_images/80bd20a0b0e62aeecd493b9f0b41010c/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 1),
(90, 'Focaccia_salame', 'Focaccia salame', 3770.00, 'Pan artesanal, queso crema y salame', 'https://tb-static.uber.com/prod/image-proc/processed_images/aca7d90bfa713b0eb635f60d95b9e1d2/f0d1762b91fd823a1aa9bd0dab5c648d.jpeg', 1, 2);

-- PASTAS CON 1 SALSA (Sección 60)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `available`, `orden`) VALUES
(60, '13c6e5f7-5902-4519-a138-2fa05094aa7e', 'Pasta Bigoli 1 Salsa', 5990.00, 'Eligie 1 salsa de las 5 opciones', 1, 1),
(60, 'cc229610-0720-401b-9606-6f48a907e0fd', 'Pastas Fettuccini 1 Salsa', 5990.00, 'Eligie 1 salsa de las 5 opciones', 1, 2),
(60, 'f6eb0cf5-3329-4aaf-9110-c4422faef706', 'Noquis  1 Salsa', 5990.00, 'Eligie 1 salsa de las 5 opciones', 1, 3),
(60, '5ffdf235-abe5-4d4d-9dea-9e5d918bf969', 'Noquis Fritos 1 Salsa', 5990.00, 'Eligie 1 salsa de las 5 opciones', 1, 4);

-- PASTAS CON 2 SALSAS (Sección 60)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `available`, `orden`) VALUES
(60, '3eae2840-163e-4597-ad98-23786f26bd67', 'Pasta Bigoli 2 Salsa', 6390.00, 'Eligie 2 salsa de las 5 opciones', 1, 5),
(60, '58ca607a-fc4a-4216-9b6e-c3519968cf14', 'Pastas Fettuccini 2 Salsa', 6390.00, 'Eligie 2 salsa de las 5 opciones', 1, 6),
(60, 'bbc2d11b-bf78-4d03-93ce-443f722c688a', 'Noquis  2 Salsa', 6390.00, 'Eligie 2 salsa de las 5 opciones', 1, 7),
(60, '30449ade-d202-4372-b2dd-d4edf2c901a5', 'Noquis Fritos 2 Salsa', 6390.00, 'Eligie 2 salsa de las 5 opciones', 1, 8);

-- COMBOS CON BEBIDA LATA - 1 SALSA (Sección 70)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `available`, `orden`) VALUES
(70, '52ef85a9-1e80-4e19-ba18-e1b75fa680d5', 'Pasta Bigoli + Bebida Lata', 6990.00, 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 1),
(70, 'b534f8c8-09d5-4a96-8309-bcaf4726bc87', 'Pasta Fettuccine + Bebida Lata', 6990.00, 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 2),
(70, '854a57e8-a8c9-475c-b913-5c1736c26daf', 'Noquis 1 Salsa + Bebida Lata', 6990.00, 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 3),
(70, 'fa404154-3856-443c-a369-3c451cf53c82', 'Noquis Fritos 1 Salsa + Bebida Lata', 6990.00, 'Eligie 1 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 4);

-- COMBOS CON BEBIDA LATA - 2 SALSAS (Sección 70)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `available`, `orden`) VALUES
(70, 'bf15d3dc-92ef-4bf6-983c-cb70fcb1c488', 'Pasta Bigoli 2 Salsas + Bebida Lata', 7290.00, 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 5),
(70, '4fe3f583-fce1-43af-a482-945710b631b7', 'Pasta Fettuccine 2 Salsa + Bebida Lata', 7290.00, 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 6),
(70, 'feda54dd-7516-48aa-b305-86cadafd2042', 'Noquis 2 Salsa + Bebida Lata', 7290.00, 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 7),
(70, '37615b6b-eb7f-48b8-87e6-3a5f9f3ae7c1', 'Noquis Fritos 2 Salsa + Bebida Lata', 7290.00, 'Eligie 2 salsa de las 5 opciones+ elige 1 bebida de las 4', 1, 8);

-- PARA COMPARTIR (Sección 80)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `available`, `orden`) VALUES
(80, '6d034018-7da5-42c1-a4f7-1a0cefe0c61a', '2 Pastas a eleccion + 2 bebida', 13480.00, '2 Pastas a eleccion + 2 bebida', 1, 1),
(80, '097b5b5e-6675-4d78-83c3-5b03e8e341c9', '3 Pastas a eleccion + Bebida Familiar 1,5 Lt', 20470.00, '3 Pastas a eleccion + 1 bebida', 1, 2),
(80, '101abbd4-52df-4084-afb8-6cf5ab91617f', '4 Pastas a eleccion + Bebida Familiar', 26960.00, '4 Pastas a eleccion + 1 bebida', 1, 3);

-- SANDWICHIS (Sección 90)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `description`, `available`, `orden`) VALUES
(90, '1326ad45-8ad7-4beb-aa25-a35382466b44', 'Ciabatta Pesto', 2990.00, 'Pan de masa madre artesanal de parmesano, albahaca, tomate cherry y mozzarella.', 1, 10),
(90, 'cd44537d-6e49-4dde-8219-36a52c25c9e3', 'Ciabatta Pollo', 2990.00, 'Pan de masa madre con pollo grillado y pimentón asado.', 1, 11),
(90, 'a11aa8b6-1c17-468d-aee7-96016d72ef1c', 'Ciabatta Salami', 2990.00, 'Pan de masa madre con salami italiano, queso crema y tomate seco.', 1, 12),
(90, 'f066e015-390a-4bcd-a3d5-de9b5e4fbdd3', 'Ciabatta Alleato', 2990.00, 'Pan de masa madre con jamón serrano y queso manchego.', 1, 13),
(90, 'eb387c0a-3532-44a2-b09e-a3f9f1054d90', 'Ciabatta Pesto + Bebida Lata', 3990.00, 'Pan de masa madre artesanal de parmesano, albahaca, tomate cherry y mozzarella.', 1, 14),
(90, 'fd0b6de8-a70a-4233-bc0c-f6d46ae55b20', 'Ciabatta Pollo + Bebida Lata', 3990.00, 'Pan de masa madre con pollo grillado y pimentón asado.', 1, 15),
(90, '80cdfc7a-a0ad-42b0-bc2f-e5048d8f2ad3', 'Ciabatta Salami + Bebida Lata', 3990.00, 'Pan de masa madre con salami italiano, queso crema y tomate seco.', 1, 16),
(90, '6ab91854-33ce-464a-9ace-e8622bb1feda', 'Ciabatta Alleato + Bebida Lata', 3990.00, 'Pan de masa madre con jamón serrano y queso manchego.', 1, 17);

-- FAMILY PARTY (Sección 100)
INSERT INTO `merchise_items` (`section_id`, `sku`, `name`, `price`, `available`, `orden`) VALUES
(100, '821988d3-8b21-46cc-b731-cbe83b5f943a', 'Fontana 2 Salsa', 7590.00, 1, 1),
(100, '2efdc598-b812-4004-b6ac-3337c9374097', 'Fontana 2 Salsas + Bebida Familiar', 9900.00, 1, 2),
(100, '9de96de0-60e8-49ca-b7c2-9a05a7401e42', '2 pastas a eleccion +2 ciabatta + Bebida Familiar', 16990.00, 1, 3),
(100, '7e2e01cc-e13a-4f55-a4fe-cfdbad46b68d', '2 Ciabattas+ 1 Noquis frito + 2 Pastas', 10990.00, 1, 4);

-- ============================================================================
-- 3. CREAR MODIFIERS (SELECTORES)
-- ============================================================================

-- Variables para IDs (nota: en producción se usarían los IDs reales generados)
-- Por simplicidad, asumiré IDs secuenciales

-- MODIFIERS PARA COMBO INDIVIDUAL (SKU: 2.0)
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '2.0_pasta', 'Elige tu pasta', 1, 1, 1, 1 FROM merchise_items WHERE sku = '2.0';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '2.0_bebida', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = '2.0';

-- MODIFIERS PARA COMBO PARA 2 (SKU: 15.0)
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '15.0_pasta', 'Elige tu pasta', 1, 1, 2, 1 FROM merchise_items WHERE sku = '15.0';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '15.0_bebida', 'Elige sabor de tu bebida', 1, 1, 2, 2 FROM merchise_items WHERE sku = '15.0';

-- MODIFIERS PARA COMBO FAMILIAR (SKU: 28.0)
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '28.0_pasta', 'Elige tu pasta', 1, 1, 4, 1 FROM merchise_items WHERE sku = '28.0';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '28.0_bebida', 'Elige sabor de tu bebida', 1, 1, 4, 2 FROM merchise_items WHERE sku = '28.0';

-- MODIFIERS PARA COMBO INDIVIDUAL 2 SALSAS
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'Combo_individual_2_s_bebida', 'Elige sabor de tu bebida', 1, 1, 1, 1 FROM merchise_items WHERE sku = 'Combo_individual_2_s';

-- MODIFIERS PARA PASTAS CON 1 SALSA
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '13c6e5f7_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = '13c6e5f7-5902-4519-a138-2fa05094aa7e';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'cc229610_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = 'cc229610-0720-401b-9606-6f48a907e0fd';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'f6eb0cf5_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = 'f6eb0cf5-3329-4aaf-9110-c4422faef706';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '5ffdf235_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = '5ffdf235-abe5-4d4d-9dea-9e5d918bf969';

-- MODIFIERS PARA PASTAS CON 2 SALSAS
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '3eae2840_salsa2', 'Elige tus 2 salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = '3eae2840-163e-4597-ad98-23786f26bd67';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '58ca607a_salsa2', 'Elige tus 2  salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = '58ca607a-fc4a-4216-9b6e-c3519968cf14';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'bbc2d11b_salsa2', 'Elige tus 2  salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = 'bbc2d11b-bf78-4d03-93ce-443f722c688a';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '30449ade_salsa2', 'Elige tus 2  salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = '30449ade-d202-4372-b2dd-d4edf2c901a5';

-- MODIFIERS PARA COMBOS CON BEBIDA LATA (1 SALSA)
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '52ef85a9_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = '52ef85a9-1e80-4e19-ba18-e1b75fa680d5';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '52ef85a9_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = '52ef85a9-1e80-4e19-ba18-e1b75fa680d5';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'b534f8c8_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = 'b534f8c8-09d5-4a96-8309-bcaf4726bc87';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'b534f8c8_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = 'b534f8c8-09d5-4a96-8309-bcaf4726bc87';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '854a57e8_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = '854a57e8-a8c9-475c-b913-5c1736c26daf';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '854a57e8_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = '854a57e8-a8c9-475c-b913-5c1736c26daf';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'fa404154_salsa', 'Elige tu salsa', 1, 1, 1, 1 FROM merchise_items WHERE sku = 'fa404154-3856-443c-a369-3c451cf53c82';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'fa404154_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = 'fa404154-3856-443c-a369-3c451cf53c82';

-- MODIFIERS PARA COMBOS CON BEBIDA LATA (2 SALSAS)
INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'bf15d3dc_salsa2', 'Elige tus 2  salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = 'bf15d3dc-92ef-4bf6-983c-cb70fcb1c488';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'bf15d3dc_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = 'bf15d3dc-92ef-4bf6-983c-cb70fcb1c488';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '4fe3f583_salsa2', 'Elige tus 2  salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = '4fe3f583-fce1-43af-a482-945710b631b7';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '4fe3f583_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = '4fe3f583-fce1-43af-a482-945710b631b7';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'feda54dd_salsa2', 'Elige tus 2  salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = 'feda54dd-7516-48aa-b305-86cadafd2042';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, 'feda54dd_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = 'feda54dd-7516-48aa-b305-86cadafd2042';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '37615b6b_salsa2', 'Elige tus 2  salsas', 1, 2, 2, 1 FROM merchise_items WHERE sku = '37615b6b-eb7f-48b8-87e6-3a5f9f3ae7c1';

INSERT INTO `merchise_modifiers` (`item_id`, `sku`, `name`, `required`, `min`, `max`, `orden`) 
SELECT id, '37615b6b_lata', 'Elige sabor de tu bebida', 1, 1, 1, 2 FROM merchise_items WHERE sku = '37615b6b-eb7f-48b8-87e6-3a5f9f3ae7c1';

-- ============================================================================
-- 4. CREAR OPTIONS (OPCIONES DE CADA SELECTOR)
-- ============================================================================

-- OPTIONS PARA COMBO INDIVIDUAL - PASTAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '2.0_pasta_6.0', 'Pasta mixta boloñesa y Alfredo', 0.00, 1, 1 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '2.0' AND m.sku = '2.0_pasta';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '2.0_pasta_8.0', 'Pasta pesto', 0.00, 1, 2 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '2.0' AND m.sku = '2.0_pasta';

-- OPTIONS PARA COMBO INDIVIDUAL - BEBIDAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '2.0_bebida_11.0', 'Coca-Cola Sin Azúcar 350 ml', 0.00, 1, 1 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '2.0' AND m.sku = '2.0_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '2.0_bebida_12.0', 'Coca-Cola Original 350 ml', 0.00, 1, 2 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '2.0' AND m.sku = '2.0_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '2.0_bebida_13.0', 'Coca-Cola Light 350 ml', 0.00, 1, 3 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '2.0' AND m.sku = '2.0_bebida';

-- OPTIONS PARA COMBO PARA 2 - PASTAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '15.0_pasta_17.0', 'Pasta mixta boloñesa y pesto', 0.00, 1, 1 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '15.0' AND m.sku = '15.0_pasta';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '15.0_pasta_20.0', 'Pasta mixta Alfredo y pesto', 0.00, 1, 2 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '15.0' AND m.sku = '15.0_pasta';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '15.0_pasta_22.0', 'Pasta boloñesa', 0.00, 1, 3 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '15.0' AND m.sku = '15.0_pasta';

-- OPTIONS PARA COMBO PARA 2 - BEBIDAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '15.0_bebida_24.0', 'Coca-Cola Sin Azúcar 350 ml', 0.00, 1, 1 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '15.0' AND m.sku = '15.0_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '15.0_bebida_26.0', 'Coca-Cola Light 350 ml', 0.00, 1, 2 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '15.0' AND m.sku = '15.0_bebida';

-- OPTIONS PARA COMBO FAMILIAR - PASTAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '28.0_pasta_34.0', 'Pasta pesto', 0.00, 1, 1 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '28.0' AND m.sku = '28.0_pasta';

-- OPTIONS PARA COMBO FAMILIAR - BEBIDAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '28.0_bebida_37.0', 'Coca-Cola Sin Azúcar 350 ml', 0.00, 1, 1 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '28.0' AND m.sku = '28.0_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '28.0_bebida_38.0', 'Coca-Cola Original 350 ml', 0.00, 1, 2 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '28.0' AND m.sku = '28.0_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '28.0_bebida_39.0', 'Coca-Cola Light 350 ml', 0.00, 1, 3 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '28.0' AND m.sku = '28.0_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, '28.0_bebida_40.0', 'Sprite 350 ml', 0.00, 1, 4 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = '28.0' AND m.sku = '28.0_bebida';

-- OPTIONS PARA COMBO INDIVIDUAL 2 SALSAS - BEBIDAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, 'Combo_individual_2_s_bebida_11.0', 'Coca-Cola Sin Azúcar 350 ml', 0.00, 1, 1 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = 'Combo_individual_2_s' AND m.sku = 'Combo_individual_2_s_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, 'Combo_individual_2_s_bebida_12.0', 'Coca-Cola Original 350 ml', 0.00, 1, 2 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = 'Combo_individual_2_s' AND m.sku = 'Combo_individual_2_s_bebida';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`) 
SELECT m.id, 'Combo_individual_2_s_bebida_13.0', 'Coca-Cola Light 350 ml', 0.00, 1, 3 
FROM merchise_modifiers m 
JOIN merchise_items i ON m.item_id = i.id 
WHERE i.sku = 'Combo_individual_2_s' AND m.sku = 'Combo_individual_2_s_bebida';

-- ============================================================================
-- OPTIONS PARA TODAS LAS PASTAS CON SALSA (6 OPCIONES ESTÁNDAR)
-- ============================================================================

-- Crear las 6 opciones de salsas para cada producto que tenga modifier de salsa (1 salsa)
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bolenosa'),
    'Salsa Boleñosa tradicional',
    0.00,
    'Salsa de carne molida tradicional',
    1,
    1
FROM merchise_modifiers m
WHERE m.sku IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_jamon'),
    'Salsa Bechamel con Jamón',
    0.00,
    'Cremosa salsa blanca con jamón',
    1,
    2
FROM merchise_modifiers m
WHERE m.sku IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_pesto'),
    'Pesto de Albaca, Queso y Mani',
    0.00,
    'Salsa de albahaca fresca con piñones',
    1,
    3
FROM merchise_modifiers m
WHERE m.sku IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_camarones'),
    'Salsa Bechamel con Camarones',
    0.00,
    'Salsa cremosa con camarones frescos',
    1,
    4
FROM merchise_modifiers m
WHERE m.sku IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_champinones'),
    'Salsa Bechamel con Champiñones',
    0.00,
    'Cremosa salsa blanca con champiñones',
    1,
    5
FROM merchise_modifiers m
WHERE m.sku IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_pollo'),
    'Salsa Bechamel con Pollo y mostaza',
    0.00,
    'Pollo, crema y mostaza',
    1,
    6
FROM merchise_modifiers m
WHERE m.sku IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa');

-- MISMAS 6 OPCIONES PARA MODIFIERS CON 2 SALSAS
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bolenosa'),
    'Salsa Boleñosa tradicional',
    0.00,
    'Salsa de carne molida tradicional',
    1,
    1
FROM merchise_modifiers m
WHERE m.sku IN ('3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_jamon'),
    'Salsa Bechamel con Jamón',
    0.00,
    'Cremosa salsa blanca con jamón',
    1,
    2
FROM merchise_modifiers m
WHERE m.sku IN ('3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_pesto'),
    'Pesto de Albaca, Queso y Mani',
    0.00,
    'Salsa de albahaca fresca con piñones',
    1,
    3
FROM merchise_modifiers m
WHERE m.sku IN ('3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_camarones'),
    'Salsa Bechamel con Camarones',
    0.00,
    'Salsa cremosa con camarones frescos',
    1,
    4
FROM merchise_modifiers m
WHERE m.sku IN ('3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_champinones'),
    'Salsa Bechamel con Champiñones',
    0.00,
    'Cremosa salsa blanca con champiñones',
    1,
    5
FROM merchise_modifiers m
WHERE m.sku IN ('3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_pollo'),
    'Salsa Bechamel con Pollo y mostaza',
    0.00,
    'Pollo, crema y mostaza',
    1,
    6
FROM merchise_modifiers m
WHERE m.sku IN ('3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2');

-- ============================================================================
-- OPTIONS PARA BEBIDAS LATA (4 OPCIONES ESTÁNDAR DE PEPSI)
-- ============================================================================

-- Insertar las 4 opciones de bebidas lata para todos los combos que las necesiten
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_pepsi'),
    'Pepsi Original 350 ml',
    0.00,
    1,
    1
FROM merchise_modifiers m
WHERE m.name = 'Elige sabor de tu bebida' AND m.sku LIKE '%lata%';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_pepsi_zero'),
    'Pepsi Zero 350 ml',
    0.00,
    1,
    2
FROM merchise_modifiers m
WHERE m.name = 'Elige sabor de tu bebida' AND m.sku LIKE '%lata%';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_7up'),
    '7up 350 ml',
    0.00,
    1,
    3
FROM merchise_modifiers m
WHERE m.name = 'Elige sabor de tu bebida' AND m.sku LIKE '%lata%';

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_cruchs'),
    'Cruchs 350 ml',
    0.00,
    1,
    4
FROM merchise_modifiers m
WHERE m.name = 'Elige sabor de tu bebida' AND m.sku LIKE '%lata%';

-- TAMBIÉN PARA LOS MODIFIERS DE SALSA DE COMBOS CON LATA
INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bolenosa'),
    'Salsa Boleñosa tradicional',
    0.00,
    'Salsa de carne molida tradicional',
    1,
    1
FROM merchise_modifiers m
WHERE m.sku LIKE '%salsa%' AND m.sku NOT IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa', '3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2', '2.0_pasta', '15.0_pasta', '28.0_pasta');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_jamon'),
    'Salsa Bechamel con Jamón',
    0.00,
    'Cremosa salsa blanca con jamón',
    1,
    2
FROM merchise_modifiers m
WHERE m.sku LIKE '%salsa%' AND m.sku NOT IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa', '3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2', '2.0_pasta', '15.0_pasta', '28.0_pasta');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_pesto'),
    'Pesto de Albaca, Queso y Mani',
    0.00,
    'Salsa de albahaca fresca con piñones',
    1,
    3
FROM merchise_modifiers m
WHERE m.sku LIKE '%salsa%' AND m.sku NOT IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa', '3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2', '2.0_pasta', '15.0_pasta', '28.0_pasta');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_camarones'),
    'Salsa Bechamel con Camarones',
    0.00,
    'Salsa cremosa con camarones frescos',
    1,
    4
FROM merchise_modifiers m
WHERE m.sku LIKE '%salsa%' AND m.sku NOT IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa', '3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2', '2.0_pasta', '15.0_pasta', '28.0_pasta');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_champinones'),
    'Salsa Bechamel con Champiñones',
    0.00,
    'Cremosa salsa blanca con champiñones',
    1,
    5
FROM merchise_modifiers m
WHERE m.sku LIKE '%salsa%' AND m.sku NOT IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa', '3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2', '2.0_pasta', '15.0_pasta', '28.0_pasta');

INSERT INTO `merchise_options` (`modifier_id`, `sku`, `name`, `price`, `description`, `available`, `orden`)
SELECT 
    m.id,
    CONCAT(m.sku, '_bechamel_pollo'),
    'Salsa Bechamel con Pollo y mostaza',
    0.00,
    'Pollo, crema y mostaza',
    1,
    6
FROM merchise_modifiers m
WHERE m.sku LIKE '%salsa%' AND m.sku NOT IN ('13c6e5f7_salsa', 'cc229610_salsa', 'f6eb0cf5_salsa', '5ffdf235_salsa', '3eae2840_salsa2', '58ca607a_salsa2', 'bbc2d11b_salsa2', '30449ade_salsa2', '2.0_pasta', '15.0_pasta', '28.0_pasta');

-- ============================================================================
-- VERIFICACIÓN FINAL
-- ============================================================================

SELECT '✅ RESUMEN DE IMPORTACIÓN' AS '';

SELECT 'Secciones creadas:' AS '', COUNT(*) AS total FROM merchise_sections;
SELECT 'Items creados:' AS '', COUNT(*) AS total FROM merchise_items;
SELECT 'Modifiers creados:' AS '', COUNT(*) AS total FROM merchise_modifiers;
SELECT 'Options creadas:' AS '', COUNT(*) AS total FROM merchise_options;

SELECT '✅ MENÚ COMPLETO POR SECCIÓN' AS '';

SELECT 
    s.name AS 'Sección',
    COUNT(DISTINCT i.id) AS 'Items',
    COUNT(DISTINCT m.id) AS 'Modifiers',
    COUNT(o.id) AS 'Options'
FROM merchise_sections s
LEFT JOIN merchise_items i ON s.id = i.section_id
LEFT JOIN merchise_modifiers m ON i.id = m.item_id
LEFT JOIN merchise_options o ON m.id = o.modifier_id
GROUP BY s.id, s.name
ORDER BY s.orden;

-- ============================================================================
-- FIN DE LA IMPORTACIÓN
-- ============================================================================
