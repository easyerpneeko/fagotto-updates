-- ============================================
-- Crear 46 cupones para promoción 2x1 en Pastas
-- Solo para Bigoli y Fettuccini (Paso 2: Boloñesa, Alfredo, Cheddar)
-- Segunda pasta GRATIS
-- ============================================

-- IMPORTANTE: Estos cupones son de UN SOLO USO por sucursal

INSERT INTO cupones (
    codigo,
    descripcion,
    tipo_descuento,
    valor_descuento,
    usos_maximos,
    usos_actuales,
    fecha_inicio,
    fecha_expiracion,
    monto_minimo,
    activo,
    usado,
    created_at,
    updated_at
) VALUES
('01', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('02', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('03', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('04', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('05', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('06', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('07', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('08', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('09', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('10', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('11', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('12', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('13', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('14', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('15', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('16', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('17', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('18', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('19', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('20', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('21', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('22', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('23', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('24', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('25', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('26', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('27', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('28', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('29', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('30', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('31', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('32', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('33', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('34', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('35', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('36', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('37', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('38', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('39', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('40', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('41', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('42', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('43', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('44', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('45', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW()),
('46', '2x1 en Pastas - Segunda GRATIS', 'porcentaje', 100, 1, 0, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), 0, 1, 0, NOW(), NOW());

-- ============================================
-- Verificar cupones creados
-- ============================================
SELECT 
    codigo,
    descripcion,
    tipo_descuento,
    valor_descuento,
    usado,
    activo,
    fecha_expiracion
FROM cupones
WHERE codigo IN ('01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
                 '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
                 '21', '22', '23', '24', '25', '26', '27', '28', '29', '30',
                 '31', '32', '33', '34', '35', '36', '37', '38', '39', '40',
                 '41', '42', '43', '44', '45', '46')
ORDER BY codigo;

-- ============================================
-- NOTAS:
-- - Cada cupón solo se puede usar 1 vez (usos_maximos = 1)
-- - Válidos por 6 meses desde la creación
-- - Sin monto mínimo de compra
-- - Al usarse, se marca: usado = 1, usado_en = fecha, + datos de sucursal/usuario
-- ============================================
