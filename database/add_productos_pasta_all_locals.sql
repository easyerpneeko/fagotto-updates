-- ====================================================================
-- Script para agregar 3 productos a TODOS los negocios
-- ====================================================================

-- ⚠️ IMPORTANTE: Ejecutar este script EN CADA base de datos de negocio
-- Ejemplo: USE fagotto_local_1; SOURCE add_productos_pasta_all_locals.sql;

-- Deshabilitar verificación de claves foráneas temporalmente
SET FOREIGN_KEY_CHECKS=0;

-- ====================================================================
-- 1. Bigoli Pomodoro
-- ====================================================================

-- Solo insertar si NO existe un producto con el mismo nombre
INSERT INTO products (
    `image`,
    `name`,
    `stock`,
    `cecina`,
    `compra`,
    `price`,
    `ganancia`,
    `active`,
    `trash`,
    `user`,
    `created_at`,
    `updated_at`,
    `category`,
    `is_combo`,
    `product_variable_category`
)
SELECT 
    'productDefault',                    -- image
    'Bigoli Pomodoro',                   -- name
    0,                                   -- stock (cada negocio ajusta)
    0,                                   -- cecina
    2990.00,                             -- compra (costo)
    3900.00,                             -- price (precio venta)
    910.00,                              -- ganancia (3900 - 2990 = 910)
    1,                                   -- active
    0,                                   -- trash
    1,                                   -- user
    NOW(),                               -- created_at
    NOW(),                               -- updated_at
    1,                                   -- category
    0,                                   -- is_combo
    3                                    -- product_variable_category
WHERE NOT EXISTS (
    SELECT 1 FROM products 
    WHERE name = 'Bigoli Pomodoro' 
    AND trash = 0
    LIMIT 1
);

-- ====================================================================
-- 2. Fettucine Pomodoro
-- ====================================================================

-- Solo insertar si NO existe un producto con el mismo nombre
INSERT INTO products (
    `image`,
    `name`,
    `stock`,
    `cecina`,
    `compra`,
    `price`,
    `ganancia`,
    `active`,
    `trash`,
    `user`,
    `created_at`,
    `updated_at`,
    `category`,
    `is_combo`,
    `product_variable_category`
)
SELECT 
    'productDefault',                    -- image
    'Fettucine Pomodoro',                -- name
    0,                                   -- stock (cada negocio ajusta)
    0,                                   -- cecina
    2990.00,                             -- compra (costo)
    3990.00,                             -- price (precio venta)
    1000.00,                             -- ganancia (3990 - 2990 = 1000)
    1,                                   -- active
    0,                                   -- trash
    1,                                   -- user
    NOW(),                               -- created_at
    NOW(),                               -- updated_at
    2,                                   -- category
    0,                                   -- is_combo
    3                                    -- product_variable_category
WHERE NOT EXISTS (
    SELECT 1 FROM products 
    WHERE name = 'Fettucine Pomodoro' 
    AND trash = 0
    LIMIT 1
);

-- ====================================================================
-- 3. Pop corns
-- ====================================================================

-- Solo insertar si NO existe un producto con el mismo nombre
INSERT INTO products (
    `image`,
    `name`,
    `stock`,
    `cecina`,
    `compra`,
    `price`,
    `ganancia`,
    `active`,
    `trash`,
    `user`,
    `created_at`,
    `updated_at`,
    `category`,
    `is_combo`,
    `product_variable_category`
)
SELECT 
    'productDefault',                    -- image
    'Pop corns',                         -- name
    0,                                   -- stock (cada negocio ajusta)
    0,                                   -- cecina
    0.00,                                -- compra (sin costo)
    1990.00,                             -- price (precio venta)
    1990.00,                             -- ganancia (1990 - 0 = 1990)
    1,                                   -- active
    0,                                   -- trash
    NULL,                                -- user
    NOW(),                               -- created_at
    NOW(),                               -- updated_at
    4,                                   -- category
    0,                                   -- is_combo
    0                                    -- product_variable_category
WHERE NOT EXISTS (
    SELECT 1 FROM products 
    WHERE name = 'Pop corns' 
    AND trash = 0
    LIMIT 1
);

-- Rehabilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS=1;

-- ====================================================================
-- Verificar resultados
-- ====================================================================

SELECT 
    id,
    name,
    price,
    compra,
    ganancia,
    stock,
    category,
    active,
    is_combo,
    product_variable_category,
    created_at
FROM products 
WHERE name IN ('Bigoli Pomodoro', 'Fettucine Pomodoro', 'Pop corns')
AND trash = 0
ORDER BY id DESC;

-- ====================================================================
-- NOTAS:
-- ====================================================================
-- ✅ No inserta duplicados (verifica por nombre)
-- ✅ Stock inicial = 0 (ajustar manualmente en cada negocio)
-- ✅ Categorías: Bigoli = 1, Fettucine = 2, Pop corns = 4
-- ✅ Product Variable Category: Pastas = 3, Pop corns = 0
-- ✅ is_combo = 0 (todos son productos simples)
-- ====================================================================
