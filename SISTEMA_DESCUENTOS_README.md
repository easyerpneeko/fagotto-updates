# 🎯 Sistema de Descuentos por Producto en Pedidos

## 📋 Resumen de la Implementación

Este sistema permite configurar un **porcentaje de descuento** a nivel de producto (ej: Champiñón, Camarón, Pesto) y aplicarlo automáticamente en los pedidos.

---

## 🗄️ PASO 1: Ejecutar Migraciones en Producción

### ⚡ Opción A: Script Automático (RECOMENDADO)

Este script agrega `discount_percentage` a products (matriz) y `discount_amount` a requests de TODOS los locales automáticamente:

```sql
-- Ejecutar el archivo: ADD_DISCOUNT_TO_ALL_LOCALS.sql
-- Ubicación: Server app/database/migrations/ADD_DISCOUNT_TO_ALL_LOCALS.sql
```

Este script hace:
1. ✅ Agrega `discount_percentage` a `products` en **easyerp** (matriz)
2. ✅ Lee todas las BDs de locales desde `data_bases`
3. ✅ Agrega `discount_amount` a `requests` en **cada local** automáticamente

---

### 🔧 Opción B: Manual (paso por paso)

#### 1.1 Agregar `discount_percentage` a la tabla `products` (MATRIZ)

```sql
-- Ejecutar en base de datos MATRIZ (easyerp)
USE easyerp;

ALTER TABLE `products` 
ADD COLUMN `discount_percentage` DECIMAL(5,2) NULL DEFAULT 0.00 
COMMENT 'Porcentaje de descuento para pedidos (0-100)';
```

#### 1.2 Agregar `discount_amount` a la tabla `requests` (CADA LOCAL)

```sql
-- ⚠️ Ejecutar en CADA base de datos de local (NO en easyerp)
-- Cambiar 'nombre_bd_local' por cada BD real

USE nombre_bd_local;  -- Ej: bd_fagotto_providencia, bd_villa_africana, etc.

ALTER TABLE `requests` 
ADD COLUMN `discount_amount` DECIMAL(10,2) NULL DEFAULT 0.00 
COMMENT 'Monto de descuento aplicado por productos con discount_percentage';
```

Repetir para cada local:
- `bd_fagotto_providencia`
- `bd_fagotto_maipu`
- `bd_villa_africana`
- etc...

---

### 1.3 Verificar que las columnas se crearon correctamente

```sql
-- ✅ Verificar products en MATRIZ
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'easyerp' 
  AND TABLE_NAME = 'products' 
  AND COLUMN_NAME = 'discount_percentage';

-- ✅ Verificar requests en TODOS los locales
SELECT 
    TABLE_SCHEMA as 'Base de Datos',
    COLUMN_NAME as 'Columna',
    DATA_TYPE as 'Tipo',
    COLUMN_DEFAULT as 'Default'
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE COLUMN_NAME = 'discount_amount' 
  AND TABLE_NAME = 'requests'
  AND TABLE_SCHEMA IN (SELECT name FROM easyerp.data_bases)
ORDER BY TABLE_SCHEMA;
```

---

## 🎛️ PASO 2: Configurar Descuentos en el Dashboard

### 2.1 Acceder al Panel de Administración
1. Ir a `Dashboard` → `Productos`
2. Seleccionar el producto (ej: Champiñón)
3. Buscar el nuevo campo: **"% Descuento en Pedidos"**
4. Ingresar el porcentaje (ej: 15%)
5. Guardar el producto

### 2.2 Ejemplos de configuración:

| Producto | % Descuento |
|----------|-------------|
| Champiñón | 15% |
| Camarón | 20% |
| Pesto | 10% |

---

## 📱 PASO 3: Cómo Funciona en Pedidos

### Flujo automático:

1. **Cliente hace un pedido** con productos que tienen descuento
2. **Sistema calcula automáticamente**:
   - Detecta productos con `discount_percentage > 0`
   - Calcula el descuento por producto
   - Suma todos los descuentos
3. **Muestra el resumen**:
   ```
   💰 Subtotal: $10,000
   🎯 Descuento: -$1,500
   ─────────────────────────
   💵 Total Final: $8,500
   
   Has ahorrado $1,500 en este pedido
   ```
4. **Guarda en la base de datos**:
   - `price`: Total con descuento aplicado ($8,500)
   - `discount_amount`: Monto descontado ($1,500)

---

## 🔧 Archivos Modificados

### Backend:
- ✅ `Server app/app/Product.php` - Agregado `discount_percentage` al fillable
- ✅ `Server app/database/migrations/2025_10_27_000001_add_discount_percentage_to_products_table.php`
- ✅ `Server app/database/migrations/ADD_DISCOUNT_PERCENTAGE_TO_PRODUCTS.sql`
- ✅ `Server app/database/migrations/ADD_DISCOUNT_AMOUNT_TO_REQUESTS.sql`

### Frontend:
- ✅ `src/renderer/components/modals/newProduct.vue` - Campo de % descuento en formulario
- ✅ `src/renderer/views/pedidos.vue` - Cálculo automático y visualización

---

## 🧪 Pruebas Recomendadas

### 1. Crear producto con descuento:
```
Producto: Champiñón Especial
Precio: $5,000
% Descuento: 15%
```

### 2. Hacer un pedido:
```
- 2x Champiñón Especial = $10,000
- Descuento 15% = -$1,500
- Total Final = $8,500
```

### 3. Verificar en base de datos:
```sql
SELECT id, contact_name, price, discount_amount, (price + discount_amount) as total_original
FROM requests 
WHERE discount_amount > 0
ORDER BY id DESC 
LIMIT 10;
```

---

## ✅ Ventajas del Sistema

1. **✨ Flexibilidad**: Cambias descuentos sin programar
2. **⚡ Automático**: El sistema calcula todo solo
3. **📊 Trazabilidad**: Se guarda el monto descontado
4. **🎯 Por Producto**: Cada producto tiene su propio descuento
5. **💰 Transparencia**: El cliente ve el ahorro

---

## 🚀 Siguiente Paso: Deploy

Una vez ejecutadas las migraciones en producción, hacer **push y deploy** de la versión actualizada.

```bash
# 1. Ejecutar SQLs en producción (phpMyAdmin o CLI)
# 2. Commit y push
git add .
git commit -m "feat: Sistema de descuentos por producto en pedidos"
git push origin fagotto-dev

# 3. Deploy (cuando tengas token válido)
$env:GH_TOKEN="tu_token"; npx electron-builder build --win --publish always
```

---

## 📞 Soporte

Si encuentras algún problema:
1. Verificar que las columnas existan en la BD
2. Revisar consola del navegador para errores
3. Verificar que los productos tengan `discount_percentage > 0`

---

**Desarrollado por:** GitHub Copilot 🤖  
**Fecha:** Octubre 27, 2025  
**Versión:** 1.11.22+
