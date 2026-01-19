-- ============================================
-- INSERTS DE RECETAS BASE - FAGOTTO
-- ============================================
-- Este archivo inserta todas las recetas base en la BD MAESTRA
-- Ejecutar después de create_recetario_tables.sql

USE `fagotto-erd-aplicacion`;

-- ============================================
-- RECETAS GLOBALES
-- ============================================

-- Ciabattas
INSERT INTO `recetas_global` (`id`, `nombre`, `descripcion`, `codigo`, `activa`) VALUES
(1, 'Ciabatta Salame', 'Receta para preparación de Ciabatta Salame', 'REC-001', 1),
(2, 'Ciabatta Pesto', 'Receta para preparación de Ciabatta Pesto', 'REC-002', 1),
(3, 'Ciabatta Aleatto', 'Receta para preparación de Ciabatta Aleatto', 'REC-003', 1),

-- Fetuccini
(4, 'Fetuccini Boloñesa', 'Pasta Fetuccini con salsa boloñesa', 'REC-004', 1),
(5, 'Fetuccini Alfredo', 'Pasta Fetuccini con salsa alfredo', 'REC-005', 1),
(6, 'Fetuccini Camarón', 'Pasta Fetuccini con salsa de camarón', 'REC-006', 1),
(7, 'Fetuccini Pollo Mostaza', 'Pasta Fetuccini con pollo en salsa mostaza', 'REC-007', 1),
(8, 'Fetuccini Champiñón', 'Pasta Fetuccini con salsa de champiñones', 'REC-008', 1),
(9, 'Fetuccini Pesto', 'Pasta Fetuccini con salsa pesto', 'REC-009', 1),
(10, 'Fetuccini Queso Cheddar', 'Pasta Fetuccini con salsa de queso cheddar', 'REC-010', 1),

-- Bigoli
(11, 'Bigoli Boloñesa', 'Pasta Bigoli con salsa boloñesa', 'REC-011', 1),
(12, 'Bigoli Alfredo', 'Pasta Bigoli con salsa alfredo', 'REC-012', 1),
(13, 'Bigoli Camarón', 'Pasta Bigoli con salsa de camarón', 'REC-013', 1),
(14, 'Bigoli Pollo Mostaza', 'Pasta Bigoli con pollo en salsa mostaza', 'REC-014', 1),
(15, 'Bigoli Champiñón', 'Pasta Bigoli con salsa de champiñones', 'REC-015', 1),
(16, 'Bigoli Pesto', 'Pasta Bigoli con salsa pesto', 'REC-016', 1),
(17, 'Bigoli Queso Cheddar', 'Pasta Bigoli con salsa de queso cheddar', 'REC-017', 1),

-- Otros
(18, 'Botón Delivery', 'Empaque para delivery', 'REC-018', 1);

-- ============================================
-- INGREDIENTES POR RECETA
-- ============================================

-- Receta 1: Ciabatta Salame
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(1, 'Ciabatta Salame', 1, 'unidad', 1),
(1, 'Papel Mantequilla', 1, 'unidad', 2),
(1, 'Sticker', 1, 'unidad', 3);

-- Receta 2: Ciabatta Pesto
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(2, 'Ciabatta Pesto', 1, 'unidad', 1),
(2, 'Papel Mantequilla', 1, 'unidad', 2),
(2, 'Sticker', 1, 'unidad', 3);

-- Receta 3: Ciabatta Aleatto
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(3, 'Ciabatta Aleatto', 1, 'unidad', 1),
(3, 'Papel Mantequilla', 1, 'unidad', 2),
(3, 'Sticker', 1, 'unidad', 3);

-- Receta 4: Fetuccini Boloñesa
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(4, 'Pasta Fetuccini', 240, 'g', 1),
(4, 'Salsa Boloñesa', 125, 'g', 2),
(4, 'Queso Parmesano', 10, 'g', 3),
(4, 'Vaso', 1, 'unidad', 4),
(4, 'Cubierto', 1, 'unidad', 5);

-- Receta 5: Fetuccini Alfredo
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(5, 'Pasta Fetuccini', 240, 'g', 1),
(5, 'Salsa Alfredo', 140, 'g', 2),
(5, 'Queso Parmesano', 10, 'g', 3),
(5, 'Vaso', 1, 'unidad', 4),
(5, 'Cubierto', 1, 'unidad', 5);

-- Receta 6: Fetuccini Camarón
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(6, 'Pasta Fetuccini', 240, 'g', 1),
(6, 'Salsa Camarón', 140, 'g', 2),
(6, 'Queso Parmesano', 10, 'g', 3),
(6, 'Vaso', 1, 'unidad', 4),
(6, 'Cubierto', 1, 'unidad', 5);

-- Receta 7: Fetuccini Pollo Mostaza
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(7, 'Pasta Fetuccini', 240, 'g', 1),
(7, 'Pollo Mostaza', 140, 'g', 2),
(7, 'Queso Parmesano', 10, 'g', 3),
(7, 'Vaso', 1, 'unidad', 4),
(7, 'Cubierto', 1, 'unidad', 5);

-- Receta 8: Fetuccini Champiñón
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(8, 'Pasta Fetuccini', 240, 'g', 1),
(8, 'Salsa Champiñón', 140, 'g', 2),
(8, 'Queso Parmesano', 10, 'g', 3),
(8, 'Vaso', 1, 'unidad', 4),
(8, 'Cubierto', 1, 'unidad', 5);

-- Receta 9: Fetuccini Pesto
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(9, 'Pasta Fetuccini', 240, 'g', 1),
(9, 'Salsa Pesto', 80, 'g', 2),
(9, 'Queso Parmesano', 10, 'g', 3),
(9, 'Vaso', 1, 'unidad', 4),
(9, 'Cubierto', 1, 'unidad', 5);

-- Receta 10: Fetuccini Queso Cheddar
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(10, 'Pasta Fetuccini', 240, 'g', 1),
(10, 'Salsa Cheddar', 125, 'g', 2),
(10, 'Queso Parmesano', 10, 'g', 3),
(10, 'Vaso', 1, 'unidad', 4),
(10, 'Cubierto', 1, 'unidad', 5);

-- Receta 11: Bigoli Boloñesa
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(11, 'Pasta Bigoli', 240, 'g', 1),
(11, 'Salsa Boloñesa', 125, 'g', 2),
(11, 'Queso Parmesano', 10, 'g', 3),
(11, 'Vaso', 1, 'unidad', 4),
(11, 'Cubierto', 1, 'unidad', 5);

-- Receta 12: Bigoli Alfredo
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(12, 'Pasta Bigoli', 240, 'g', 1),
(12, 'Salsa Alfredo', 140, 'g', 2),
(12, 'Queso Parmesano', 10, 'g', 3),
(12, 'Vaso', 1, 'unidad', 4),
(12, 'Cubierto', 1, 'unidad', 5);

-- Receta 13: Bigoli Camarón
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(13, 'Pasta Bigoli', 240, 'g', 1),
(13, 'Salsa Camarón', 140, 'g', 2),
(13, 'Queso Parmesano', 10, 'g', 3),
(13, 'Vaso', 1, 'unidad', 4),
(13, 'Cubierto', 1, 'unidad', 5);

-- Receta 14: Bigoli Pollo Mostaza
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(14, 'Pasta Bigoli', 240, 'g', 1),
(14, 'Pollo Mostaza', 140, 'g', 2),
(14, 'Queso Parmesano', 10, 'g', 3),
(14, 'Vaso', 1, 'unidad', 4),
(14, 'Cubierto', 1, 'unidad', 5);

-- Receta 15: Bigoli Champiñón
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(15, 'Pasta Bigoli', 240, 'g', 1),
(15, 'Salsa Champiñón', 140, 'g', 2),
(15, 'Queso Parmesano', 10, 'g', 3),
(15, 'Vaso', 1, 'unidad', 4),
(15, 'Cubierto', 1, 'unidad', 5);

-- Receta 16: Bigoli Pesto
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(16, 'Pasta Bigoli', 240, 'g', 1),
(16, 'Salsa Pesto', 80, 'g', 2),
(16, 'Queso Parmesano', 10, 'g', 3),
(16, 'Vaso', 1, 'unidad', 4),
(16, 'Cubierto', 1, 'unidad', 5);

-- Receta 17: Bigoli Queso Cheddar
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(17, 'Pasta Bigoli', 240, 'g', 1),
(17, 'Salsa Cheddar', 125, 'g', 2),
(17, 'Queso Parmesano', 10, 'g', 3),
(17, 'Vaso', 1, 'unidad', 4),
(17, 'Cubierto', 1, 'unidad', 5);

-- Receta 18: Botón Delivery
INSERT INTO `receta_ingredientes_global` (`receta_global_id`, `ingrediente_nombre`, `cantidad`, `unidad`, `orden`) VALUES
(18, 'Bolsa', 1, 'unidad', 1),
(18, 'Sticker', 1, 'unidad', 2);

-- ============================================
-- Fin de inserts
-- ============================================
-- Total: 18 recetas con sus ingredientes
-- Ahora cada negocio puede asociar estas recetas a sus productos
