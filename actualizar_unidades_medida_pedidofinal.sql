-- Actualizar unidades de medida en pedidofinal_precios
-- Ejecutar en base de datos: easyerp

USE easyerp;

-- Harinas y mezclas
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2.9k' WHERE producto LIKE '%Mezcla 2.9%' OR producto LIKE '%Harina%';

-- Vasos
UPDATE pedidofinal_precios SET unidad_medida = 'Caja 500' WHERE producto LIKE '%Vaso PP 470ml%';

-- Tenedores
UPDATE pedidofinal_precios SET unidad_medida = 'Caja 500' WHERE producto LIKE '%Sobre de tenedor%' OR producto LIKE '%tenedor%';

-- Salsas en bolsa 2k
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2k' WHERE producto LIKE '%Salsa Boloñesa%';
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2k' WHERE producto LIKE '%Salsa Alfredo%';
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2k' WHERE producto LIKE '%Salsa Camarón%';
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2k' WHERE producto LIKE '%Salsa Champiñón%';
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2k' WHERE producto LIKE '%Salsa Pesto%';
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2k' WHERE producto LIKE '%Salsa Queso Cheddar%';
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 2k' WHERE producto LIKE '%Salsa Pollo Mostaza%';

-- Queso
UPDATE pedidofinal_precios SET unidad_medida = 'Bolsa 1k' WHERE producto LIKE '%Queso%' AND producto NOT LIKE '%Ciabatta%' AND producto NOT LIKE '%Salsa%';

-- Ciabattas (unidad)
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Ciabatta Pesto%';
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Ciabatta Queso Crema Salame%';
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Ciabatta Aliato%';
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Ciabatta%';

-- Papel mantequilla
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Papel Mantequilla%';

-- Stickers
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Stickers Fagotto%';
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Pliego%' AND producto LIKE '%Stickers%';

-- Bolsas delivery
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%Bolsa%' AND (producto LIKE '%Delivery%' OR producto LIKE '%Biodegradable%');

-- Pastas secas
UPDATE pedidofinal_precios SET unidad_medida = 'kg' WHERE producto LIKE '%Pasta seca Fettuccini%';
UPDATE pedidofinal_precios SET unidad_medida = 'kg' WHERE producto LIKE '%Pasta seca Bigoli%';
UPDATE pedidofinal_precios SET unidad_medida = 'kg' WHERE producto LIKE '%Pasta Fettuccini%' AND producto NOT LIKE '%seca%';
UPDATE pedidofinal_precios SET unidad_medida = 'kg' WHERE producto LIKE '%Pasta Bigoli%' AND producto NOT LIKE '%seca%';

-- Vaso individual
UPDATE pedidofinal_precios SET unidad_medida = 'unidad' WHERE producto LIKE '%precio Vaso individual%';

-- Actualizar fecha de modificación
UPDATE pedidofinal_precios SET fecha_actualizacion = NOW() WHERE 1=1;

-- Verificar cambios
SELECT id, producto, unidad_medida, categoria 
FROM pedidofinal_precios 
ORDER BY categoria, producto;
