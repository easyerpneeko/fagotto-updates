# Cambios Solicitados por Merchise

## 3 Cambios Críticos Implementados ✅

### 1️⃣ Datos del Cliente NO son Obligatorios

**Problema:** Algunos agregadores (Uber Eats, Rappi, etc) no entregan datos del cliente por privacidad.

**Solución:** Todos los campos del cliente ahora son OPCIONALES:

```json
{
  "order_id": "MERCH-2026-001",
  "platform": "uber_eats",
  "customer": {  // ⚠️ TODO OPCIONAL - Puede venir NULL o vacío
    "name": "Juan Pérez",      // OPCIONAL
    "phone": "+56912345678",   // OPCIONAL  
    "email": "juan@email.com", // OPCIONAL
    "address": "Calle Falsa 123" // OPCIONAL
  }
}
```

**Ejemplo pedido SIN datos de cliente (válido):**
```json
{
  "order_id": "MERCH-2026-002",
  "platform": "rappi",
  "customer": null,  // ← VÁLIDO
  "items": [...]
}
```

---

### 2️⃣ Campo de Plataforma/Agregador

**Problema:** No se sabía por qué plataforma se vendió (Uber Eats, Rappi, PedidosYa, etc)

**Solución:** Nuevo campo `platform`:

```json
{
  "order_id": "MERCH-2026-001",
  "platform": "uber_eats",  // ← NUEVO CAMPO
  "items": [...]
}
```

**Valores permitidos:**
- `uber_eats`
- `rappi`
- `pedidosya`
- `didi_food`
- `cornershop`
- `justo`
- `mercadise_app` (si es directo desde app de Merchise)
- `web` (si es desde web)
- `other`

---

### 3️⃣ Registro de Descuentos

**Problema:** No había forma de registrar descuentos aplicados en el pedido.

**Solución:** Nuevos campos para descuentos:

```json
{
  "order_id": "MERCH-2026-001",
  "items": [...],
  "subtotal": 15090,  // ← Subtotal ANTES de descuento
  "discount": {       // ← NUEVO OBJETO
    "amount": 1500,
    "type": "percentage",  // percentage, fixed, coupon, promo
    "code": "VERANO2026",
    "description": "Descuento de verano 10%"
  },
  "total": 13590  // ← Total DESPUÉS de descuento (subtotal - discount.amount)
}
```

---

## Ejemplo Completo de Pedido Mejorado

```json
{
  "order_id": "MERCH-2026-001",
  "platform": "uber_eats",
  "customer": {
    "name": "Juan Pérez",
    "phone": "+56912345678",
    "email": "juan@email.com",
    "address": "Av. Providencia 1234, Depto 501"
  },
  "items": [
    {
      "product_sku": "15.0",
      "product_name": "Combo para 2",
      "quantity": 1,
      "price": 15090,
      "modifiers": [
        {
          "modifier_sku": "mod-pasta-combo2",
          "modifier_name": "Elige tus pastas (2)",
          "selected_options": [
            {"sku": "opt-pesto", "name": "Pasta Pesto", "quantity": 1},
            {"sku": "opt-alfredo", "name": "Pasta Alfredo", "quantity": 1}
          ]
        },
        {
          "modifier_sku": "mod-bebida-combo2",
          "modifier_name": "Elige tus bebidas (2)",
          "selected_options": [
            {"sku": "opt-cocacola", "name": "Coca-Cola", "quantity": 1},
            {"sku": "opt-sprite", "name": "Sprite", "quantity": 1}
          ]
        }
      ]
    }
  ],
  "subtotal": 15090,
  "discount": {
    "amount": 1500,
    "type": "percentage",
    "code": "PROMO10",
    "description": "10% de descuento en combos"
  },
  "total": 13590,
  "payment_method": "online",
  "notes": "Sin cebolla por favor"
}
```

---

## Ejemplo Sin Cliente (Rappi no envía datos)

```json
{
  "order_id": "MERCH-2026-002",
  "platform": "rappi",
  "customer": null,  // ← Sin datos de cliente
  "items": [
    {
      "product_sku": "42.0",
      "product_name": "Pasta Pesto",
      "quantity": 1,
      "price": 6290
    }
  ],
  "subtotal": 6290,
  "discount": null,  // ← Sin descuento
  "total": 6290,
  "payment_method": "online"
}
```

---

## Ejemplo Con Descuento Fijo

```json
{
  "order_id": "MERCH-2026-003",
  "platform": "pedidosya",
  "customer": {
    "name": "María González",
    "phone": "+56987654321"
  },
  "items": [
    {
      "product_sku": "28.0",
      "product_name": "Combo Familiar",
      "quantity": 1,
      "price": 30190
    }
  ],
  "subtotal": 30190,
  "discount": {
    "amount": 5000,
    "type": "fixed",
    "code": "PRIMERA_COMPRA",
    "description": "$5.000 de descuento por primera compra"
  },
  "total": 25190,
  "payment_method": "online"
}
```

---

## Cambios en la Base de Datos

### Tabla `merchise_pedidos` - Campos Nuevos:

```sql
-- Campo de plataforma
`platform` VARCHAR(50) NULL COMMENT 'uber_eats, rappi, pedidosya, etc'

-- Campos de descuento
`subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Subtotal antes de descuentos'
`discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto del descuento'
`discount_type` VARCHAR(50) NULL COMMENT 'percentage, fixed, coupon, promo'
`discount_code` VARCHAR(100) NULL COMMENT 'Código del cupón'
`discount_description` TEXT NULL COMMENT 'Descripción del descuento'
`total` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total final'

-- Campos del cliente ahora TODOS son NULL (opcionales)
`customer_name` VARCHAR(255) NULL
`customer_phone` VARCHAR(50) NULL
`customer_email` VARCHAR(255) NULL
`customer_address` TEXT NULL

-- Campo adicional para notas
`notes` TEXT NULL COMMENT 'Instrucciones especiales'
```

---

## Queries Útiles para Reportes

### Ventas por Plataforma:
```sql
SELECT 
    COALESCE(platform, 'Sin plataforma') AS 'Plataforma',
    COUNT(*) AS 'Total Pedidos',
    SUM(subtotal) AS 'Subtotal',
    SUM(discount_amount) AS 'Total Descuentos',
    SUM(total) AS 'Total Vendido',
    AVG(total) AS 'Ticket Promedio'
FROM merchise_pedidos
WHERE status != 'cancelled'
GROUP BY platform
ORDER BY SUM(total) DESC;
```

### Descuentos Aplicados:
```sql
SELECT 
    order_id_mercadise AS 'ID Pedido',
    platform AS 'Plataforma',
    discount_code AS 'Código',
    discount_type AS 'Tipo',
    subtotal AS 'Subtotal',
    discount_amount AS 'Descuento',
    total AS 'Total Final',
    CONCAT(ROUND((discount_amount / subtotal * 100), 1), '%') AS '% Desc',
    received_at AS 'Fecha'
FROM merchise_pedidos
WHERE discount_amount > 0
ORDER BY received_at DESC;
```

### Pedidos Sin Datos de Cliente:
```sql
SELECT 
    order_id_mercadise,
    platform,
    customer_name,
    total,
    received_at
FROM merchise_pedidos
WHERE customer_name IS NULL OR customer_phone IS NULL
ORDER BY received_at DESC;
```

---

## Validaciones en el Backend

### ✅ VÁLIDO - Con todos los datos:
```json
{
  "order_id": "MERCH-001",
  "platform": "uber_eats",
  "customer": {"name": "Juan", "phone": "+56912345678"},
  "items": [...],
  "subtotal": 15090,
  "discount": {"amount": 1500, "type": "percentage", "code": "PROMO10"},
  "total": 13590
}
```

### ✅ VÁLIDO - Sin cliente (Rappi):
```json
{
  "order_id": "MERCH-002",
  "platform": "rappi",
  "customer": null,
  "items": [...],
  "subtotal": 6290,
  "total": 6290
}
```

### ✅ VÁLIDO - Sin descuento:
```json
{
  "order_id": "MERCH-003",
  "platform": "pedidosya",
  "customer": {"name": "María"},
  "items": [...],
  "subtotal": 8990,
  "total": 8990
}
```

### ❌ INVÁLIDO - Falta order_id:
```json
{
  "platform": "uber_eats",
  "items": [...]
}
```

### ❌ INVÁLIDO - Falta platform:
```json
{
  "order_id": "MERCH-004",
  "items": [...]
}
```

### ❌ INVÁLIDO - Total no cuadra con subtotal - descuento:
```json
{
  "subtotal": 15090,
  "discount": {"amount": 1500},
  "total": 15000  // ← ERROR: Debería ser 13590
}
```

---

## Resumen de Cambios

| Campo | Antes | Ahora |
|-------|-------|-------|
| `customer.*` | Obligatorio | ✅ OPCIONAL |
| `platform` | ❌ No existía | ✅ OBLIGATORIO |
| `subtotal` | ❌ No existía | ✅ OBLIGATORIO |
| `discount.*` | ❌ No existía | ✅ OPCIONAL |
| `total` | Solo total | ✅ Total después de descuento |
| `notes` | ❌ No existía | ✅ OPCIONAL |

---

## Archivos SQL Actualizados

1. **[create_merchise_pedidos.sql](database/create_merchise_pedidos.sql)**
   - ✅ Campos de cliente opcionales
   - ✅ Campo `platform`
   - ✅ Campos de descuento
   - ✅ Índice en `platform`
   - ✅ Queries de ejemplo actualizados

2. **[create_merchise_modifiers_options.sql](database/create_merchise_modifiers_options.sql)**
   - ✅ Campo `discount_amount` en items
   - ✅ Campo `notes` en items
   - ✅ Estructura completa de modifiers/options

---

## Próximos Pasos

1. **Ejecutar los SQL actualizados** en la base de datos
2. **Actualizar el controller** `MercadiseController.php` para:
   - Validar `platform` (obligatorio)
   - Aceptar `customer` como opcional
   - Procesar descuentos
   - Calcular y validar totales
3. **Actualizar la documentación** de la API
4. **Probar** con Postman los nuevos campos

