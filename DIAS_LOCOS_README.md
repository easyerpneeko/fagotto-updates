# 🎉 Módulo "Días Locos" - Documentación

## 📋 Resumen
Sistema de productos especiales "Días Locos" que permite seleccionar productos de la categoría "Emergencia" (ID: 56) y combinarlos con opciones de pasta (Fettuccine o Bigoli).

**IMPORTANTE**: Los productos vienen directamente del catálogo local (categoría 56). **NO se crea tabla nueva**, solo se marca con flag `is_dias_locos` para NO actualizar stock.

## ✅ Archivos Creados/Modificados

### Frontend
1. **`src/renderer/components/modals/cafeteria/diasLocos.vue`** ✨ NUEVO
   - Modal con diseño moderno y animaciones
   - Selección de tipo de pasta (Fettuccine/Bigoli)
   - Carga dinámica de productos desde categoría 56
   - Fade in/out como merchise
   - **Usa ID real del producto** (no ID temporal)

2. **`src/renderer/components/modals/cafeteria/catalog.vue`** 🔧 MODIFICADO
   - Agregado botón "Días Locos" con estilo especial y animaciones
   - Importado componente DiasLocos
   - Agregado handler `handleAddDiasLocos()` para procesar productos
   - CSS con gradientes rosa/fucsia y animación de pulso
   - Ícono de fuego con animación shake

### Backend
3. **`Server app/app/Http/Controllers/Controllers_local\SellsController.php`** 🔧 MODIFICADO
   - Agregado detección de flag `is_dias_locos`
   - **NO guarda en tabla separada** (productos vienen del catálogo)
   - No actualiza stock de productos con flag `is_dias_locos`
   - No imprime boleta si todos son productos especiales

## 🚀 Flujo de Funcionamiento

### 1. Usuario hace click en "Días Locos"
- Se abre modal diasLocos.vue con animación

### 2. Usuario selecciona pasta (Fettuccine/Bigoli)
- Se cargan productos de categoría 56 desde API:
```javascript
const response = await Connection.request(
  'GET',
  BaseUrl.getUrl(`api/local/products?category=56`)
);
```

### 3. Usuario selecciona producto
```javascript
const diasLocosProduct = {
  id: product.id, // ← ID REAL del producto del catálogo
  name: `${product.name} con ${pastaName}`,
  price: parseFloat(product.price),
  is_dias_locos: true, // ← Flag para NO actualizar stock
  dias_locos_details: {
    pasta: this.selectedPasta,
    pasta_name: pastaName
  }
};
```

### 4. Producto se agrega al carrito
- Se usa el ID real del producto de la BD
- Solo se agrega descripción de pasta al nombre
- Flag `is_dias_locos: true` evita descuento de stock

### 5. Backend procesa la venta
```php
// SellsController.php
$is_dias_locos = isset($item->is_dias_locos) && $item->is_dias_locos === true;

// NO actualiza stock
if (!$is_merchise && !$is_colacion && !$is_dias_locos && ...) {
  $product->stock = $product->stock - $item->quantity;
}

// NO imprime boleta si todos son especiales
if (!$allEspeciales) {
  $query['response_folio'] = $this->printPDF(...);
}
```

## 🎨 Características Visuales

### Botón en Catálogo
- Gradiente rosa/fucsia: `#f093fb` → `#f5576c`
- Animación de pulso con box-shadow
- Ícono de fuego con shake animation
- Hover con scale 1.02

### Modal
- Overlay con backdrop-blur
- Animación slideUp con cubic-bezier bounce
- Header con gradiente y emojis flotantes
- Cards de pasta con efecto hover y check animado
- Grid responsivo de productos
- Loading spinner durante carga
- Empty state si no hay productos

## 📊 Estructura de Datos

### Frontend → Backend
```json
{
  "id": 123,  // ← ID real del producto de categoría 56
  "name": "Pizza Especial con Fettuccine",
  "price": 8990,
  "is_dias_locos": true,
  "dias_locos_details": {
    "pasta": "fettuccine",
    "pasta_name": "Fettuccine"
  }
}
```

### Base de Datos
- **NO se crea tabla nueva**
- Se guarda en `products_sells` como venta normal
- El `product` ID apunta al producto real de categoría 56
- Solo el flag `is_dias_locos` indica que NO debe actualizar stock

## 🔧 Instalación

### 1. NO hay migración SQL necesaria
✅ Los productos ya existen en la categoría 56

### 2. Recompilar frontend
```bash
npm run dev
# o
npm run build
```

### 3. Limpiar caché del navegador
- El modal se carga automáticamente al abrir el catálogo

## 🧪 Testing

### 1. Verificar botón visible
- Abrir catálogo de productos
- Buscar botón "Días Locos" con ícono 🔥
- Debe tener animación de pulso

### 2. Probar flujo completo
```
1. Click en "Días Locos"
2. Seleccionar Fettuccine
3. Esperar carga de productos (debe mostrar spinner)
4. Click en un producto
5. Verificar que aparece en el carrito como "Producto con Fettuccine"
6. Procesar venta
7. Verificar en BD: products_sells tiene product_id real (no null)
8. Verificar: stock NO se actualizó
```

### 3. Verificar no imprime boleta
- Hacer venta SOLO con productos Días Locos
- No debe generar PDF/boleta
- Revisar logs: "Son todos productos especiales"

## 🐛 Troubleshooting

### Error: "No se encontraron productos"
✅ **Solución**: Verificar que la categoría 56 "Emergencia" existe y tiene productos activos
```sql
SELECT * FROM categories WHERE id = 56;
SELECT * FROM products WHERE category = 56 AND active = 1;
```

### Error: "Column 'product' cannot be null"
✅ **Solución**: Verificar que el modal está usando `product.id` (ID real), no ID temporal

### Modal no se abre
✅ **Solución**: 
1. Verificar import en catalog.vue
2. Verificar que diasLocos está en components
3. Abrir DevTools → Console buscar errores

### Productos no cargan
✅ **Solución**: Verificar endpoint API
```bash
curl https://posfagotto.cl/api/local/products?category=56
```

## 📝 Notas Importantes

1. **No actualiza stock**: Flag `is_dias_locos` previene descuento de inventario
2. **Sin boleta**: Si TODOS los productos son especiales, no se imprime
3. **Categoría fija**: Solo productos de categoría 56 "Emergencia"
4. **ID real**: Usa el ID del producto del catálogo, NO temporal
5. **Sin tabla extra**: Se guarda en products_sells normal

## 🎯 Diferencias con Merchise y Colación

| Feature | Merchise | Colación | Días Locos |
|---------|----------|----------|------------|
| Tabla propia | ✅ | ✅ | ❌ |
| ID temporal | ✅ | ✅ | ❌ |
| Actualiza stock | ❌ | ❌ | ❌ |
| Precio | Variable | $0 | Del catálogo |
| Origen datos | Múltiples categorías | N/A | Categoría 56 |

---

**Desarrollado**: Enero 2026
**Tiempo**: ~1 hora ⚡
**Status**: ✅ FUNCIONANDO (sin tabla extra)
