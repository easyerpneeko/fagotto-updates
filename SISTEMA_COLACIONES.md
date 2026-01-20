# Sistema de Colaciones 🍝

Sistema de colaciones gratuitas para empleados dentro del módulo de cafetería.

## Características

- ✅ **Colaciones gratuitas** (costo $0)
- ✅ **Selección de pasta**: Bigoli o Fettuccini
- ✅ **Selección de salsa**: Alfredo, Boloñesa o Salsa Cheddar
- ✅ **Registro de empleado** que retira
- ✅ **Modal con fondo oscuro** y diseño moderno
- ✅ **Integrado en el carro de compras** normal
- ✅ **Registro en base de datos** con timestamp

## Base de Datos

### Tabla: `colaciones_retiros`

```sql
CREATE TABLE colaciones_retiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sell_id INT NOT NULL,
    product_sell_id INT NOT NULL,
    empleado_nombre VARCHAR(255) NOT NULL,
    pasta VARCHAR(50),
    salsa VARCHAR(50),
    fecha_retiro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Instalación

```bash
# Conectar a MySQL y ejecutar:
mysql -u root -p easyerp < database/create_colaciones.sql
```

## Frontend

### Archivos modificados:

1. **`src/renderer/components/modals/cafeteria/colacion.vue`** (NUEVO)
   - Modal principal del sistema de colaciones
   - Selectores de pasta y salsa
   - Input de nombre del empleado

2. **`src/renderer/components/modals/cafeteria/catalog.vue`**
   - Botón "Colación" agregado en la lista de categorías
   - Handler `handleAddColacion()` para agregar al carro
   - Integración con el sistema de ventas existente

### Uso en Frontend:

```javascript
// El modal emite el evento 'addColacion' con la siguiente estructura:
{
  id: 'colacion_1737123456789',
  name: 'Bigoli con Alfredo',
  price: 0,
  quantity: 1,
  is_colacion: true,
  empleado_retira: 'Juan Pérez',
  pasta: 'bigoli',
  salsa: 'alfredo',
  colacion_details: {
    pasta: 'bigoli',
    salsa: 'alfredo'
  }
}
```

## Backend

### Archivos modificados:

1. **`Server app/app/Http/Controllers/Controllers_local/SellsController.php`**
   - Detección de `is_colacion` flag
   - Guardado de nombre en `description_sii`
   - Registro en tabla `colaciones_retiros`
   - Logs detallados para debugging

### Flujo en Backend:

```php
// 1. Detectar si es colación
$is_colacion = isset($item->is_colacion) && $item->is_colacion === true;

// 2. Guardar nombre en description_sii
if ($is_colacion && isset($item->name)) {
    $newProductSell['description_sii'] = $item->name;
}

// 3. Registrar quién retira
if ($is_colacion && isset($item->empleado_retira)) {
    DB::table('colaciones_retiros')->insert([
        'sell_id' => $sell->id,
        'product_sell_id' => $created->id,
        'empleado_nombre' => $item->empleado_retira,
        'pasta' => $item->colacion_details->pasta,
        'salsa' => $item->colacion_details->salsa,
        'fecha_retiro' => now()
    ]);
}
```

## Consultas Útiles

### Ver colaciones de hoy:

```sql
SELECT 
    cr.empleado_nombre,
    ps.description_sii AS producto,
    cr.pasta,
    cr.salsa,
    cr.fecha_retiro,
    s.total
FROM colaciones_retiros cr
INNER JOIN products_sells ps ON cr.product_sell_id = ps.id
INNER JOIN sells s ON cr.sell_id = s.id
WHERE DATE(cr.fecha_retiro) = CURDATE()
ORDER BY cr.fecha_retiro DESC;
```

### Estadísticas por empleado:

```sql
SELECT 
    empleado_nombre,
    COUNT(*) AS total_colaciones,
    GROUP_CONCAT(DISTINCT pasta) AS pastas_elegidas,
    GROUP_CONCAT(DISTINCT salsa) AS salsas_elegidas,
    MAX(fecha_retiro) AS ultima_colacion
FROM colaciones_retiros
GROUP BY empleado_nombre
ORDER BY total_colaciones DESC;
```

### Colaciones del mes actual:

```sql
SELECT 
    DATE(cr.fecha_retiro) AS fecha,
    COUNT(*) AS total_colaciones,
    GROUP_CONCAT(DISTINCT cr.empleado_nombre) AS empleados
FROM colaciones_retiros cr
WHERE YEAR(cr.fecha_retiro) = YEAR(CURDATE())
  AND MONTH(cr.fecha_retiro) = MONTH(CURDATE())
GROUP BY DATE(cr.fecha_retiro)
ORDER BY fecha DESC;
```

## Testing

### Probar en desarrollo:

1. Abrir el módulo de cafetería
2. Click en botón "Colación" (color rojo)
3. Seleccionar pasta y salsa
4. Ingresar nombre del empleado
5. Click "Agregar Colación"
6. Verificar que aparece en el carro con precio $0
7. Procesar venta normalmente

### Verificar en base de datos:

```sql
-- Ver última colación registrada
SELECT * FROM colaciones_retiros ORDER BY id DESC LIMIT 1;

-- Ver producto en products_sells
SELECT ps.*, cr.empleado_nombre 
FROM products_sells ps
LEFT JOIN colaciones_retiros cr ON ps.id = cr.product_sell_id
WHERE cr.id IS NOT NULL
ORDER BY ps.id DESC LIMIT 1;
```

## Notas Importantes

- ⚠️ **Siempre costo $0**: Las colaciones nunca tienen precio
- ⚠️ **Sin validación de Product**: No busca en tabla `products`, similar a merchise
- ⚠️ **Sin actualización de stock**: No afecta inventario
- ⚠️ **Fecha del servidor**: Modal muestra fecha actual del servidor
- ⚠️ **Registro obligatorio**: Siempre se guarda quién retira

## Futuras Mejoras

- [ ] Límite de colaciones por empleado por día
- [ ] Reporte mensual de colaciones
- [ ] Validación de empleados contra tabla de RRHH
- [ ] Horario restringido para colaciones (ej: 12:00-14:00)
- [ ] Notificación al administrador cuando se registra colación
- [ ] Dashboard con estadísticas de uso
