# Formato de Menú Merchise - Versión Mejorada

## Estructura Completa del JSON

```json
{
  "success": true,
  "restaurant": {
    "name": "Fagotto Pasta & Salsa",
    "description": "Pasta artesanal italiana",
    "logo": "https://posfagotto.cl/assets/logo.png",
    "phone": "+56912345678",
    "address": "Av. Providencia 1234"
  },
  "menu": {
    "sections": [
      {
        "sku": "1",
        "name": "Combos",
        "description": "Nuestros combos más populares",
        "image": "https://posfagotto.cl/assets/combos.jpg",
        "items": [
          {
            "sku": "15.0",
            "name": "Combo para 2",
            "price": 15090,
            "description": "2 Pastas y 2 bebidas de 350 ml a elegir.",
            "image": "https://posfagotto.cl/assets/combo-para-2.jpg",
            "available": true,
            "calories": 1200,
            "tags": ["popular", "combo", "para-compartir"],
            "modifiers": [
              {
                "sku": "mod-pasta-combo2",
                "name": "Elige tus pastas (2)",
                "description": "Selecciona 2 pastas de tu preferencia",
                "required": true,
                "min": 2,
                "max": 2,
                "options": [
                  {
                    "sku": "opt-pesto",
                    "name": "Pasta Pesto",
                    "price": 0,
                    "quantity": 1,
                    "description": "Salsa de albahaca fresca",
                    "available": true,
                    "default": false
                  },
                  {
                    "sku": "opt-alfredo",
                    "name": "Pasta Alfredo",
                    "price": 0,
                    "quantity": 1,
                    "description": "Cremosa salsa blanca",
                    "available": true,
                    "default": false
                  },
                  {
                    "sku": "opt-bolognesa",
                    "name": "Pasta Bolognesa",
                    "price": 0,
                    "quantity": 1,
                    "description": "Salsa de carne tradicional",
                    "available": true,
                    "default": false
                  },
                  {
                    "sku": "opt-champinon",
                    "name": "Pasta Champiñón",
                    "price": 0,
                    "quantity": 1,
                    "description": "Cremosa con champiñones",
                    "available": true,
                    "default": false
                  }
                ]
              },
              {
                "sku": "mod-bebida-combo2",
                "name": "Elige tus bebidas (2)",
                "description": "Selecciona 2 bebidas de 350ml",
                "required": true,
                "min": 2,
                "max": 2,
                "options": [
                  {
                    "sku": "opt-cocacola",
                    "name": "Coca-Cola Original",
                    "price": 0,
                    "quantity": 1,
                    "available": true,
                    "default": false
                  },
                  {
                    "sku": "opt-sprite",
                    "name": "Sprite Original",
                    "price": 0,
                    "quantity": 1,
                    "available": true,
                    "default": false
                  },
                  {
                    "sku": "opt-sprite-zero",
                    "name": "Sprite Sin Azúcar",
                    "price": 0,
                    "quantity": 1,
                    "available": true,
                    "default": false
                  },
                  {
                    "sku": "opt-fanta",
                    "name": "Fanta Naranja",
                    "price": 0,
                    "quantity": 1,
                    "available": true,
                    "default": false
                  }
                ]
              }
            ]
          }
        ]
      },
      {
        "sku": "41",
        "name": "Pastas Bigoli",
        "description": "Pasta artesanal italiana fresca",
        "image": "https://posfagotto.cl/assets/pastas.jpg",
        "items": [
          {
            "sku": "42.0",
            "name": "Pasta Pesto",
            "price": 6290,
            "description": "Pasta bigoli artesanal con salsa pesto de albahaca fresca.",
            "image": "https://posfagotto.cl/assets/pasta-pesto.jpg",
            "available": true,
            "calories": 480,
            "preparation_time": 15,
            "tags": ["vegetariano", "popular"],
            "modifiers": []
          }
        ]
      }
    ]
  },
  "metadata": {
    "version": "1.0",
    "last_updated": "2026-01-04T10:30:00Z",
    "currency": "CLP",
    "timezone": "America/Santiago"
  }
}
```

## Mejoras Implementadas

### 1. **Información del Restaurante Expandida**
```json
"restaurant": {
  "name": "Fagotto Pasta & Salsa",
  "description": "Pasta artesanal italiana",
  "logo": "https://posfagotto.cl/assets/logo.png",
  "phone": "+56912345678",
  "address": "Av. Providencia 1234"
}
```
- Logo del restaurante
- Teléfono de contacto
- Dirección

### 2. **SKUs en Todos los Niveles**
```json
{
  "sku": "mod-pasta-combo2",  // Identificador único del modificador
  "sku": "opt-pesto"          // Identificador único de la opción
}
```
- Cada modifier tiene su SKU
- Cada option tiene su SKU
- Facilita el tracking y la gestión de inventario

### 3. **Descripciones Detalladas**
```json
{
  "description": "2 Pastas y 2 bebidas de 350 ml a elegir.",  // En items
  "description": "Selecciona 2 pastas de tu preferencia",     // En modifiers
  "description": "Salsa de albahaca fresca"                   // En options
}
```
- Descripciones en items (ya las tenías)
- Descripciones en modifiers (nuevo)
- Descripciones en options (nuevo)

### 4. **Control de Disponibilidad**
```json
{
  "available": true  // En items, sections y options
}
```
- Permite activar/desactivar productos sin eliminarlos
- Se puede aplicar a secciones completas
- Se puede aplicar a opciones individuales de modifiers

### 5. **Imágenes en Todos los Niveles**
```json
{
  "image": "https://posfagotto.cl/assets/combo-para-2.jpg"  // Items
  "image": "https://posfagotto.cl/assets/combos.jpg"        // Sections
}
```
- Imágenes para cada producto
- Imágenes para cada sección/categoría

### 6. **Metadatos Adicionales**
```json
{
  "calories": 1200,           // Calorías del producto
  "preparation_time": 15,     // Tiempo de preparación en minutos
  "tags": ["popular", "combo"] // Etiquetas para filtros
}
```

### 7. **Opciones por Defecto**
```json
{
  "default": false  // Marca si una opción viene preseleccionada
}
```

### 8. **Metadata Global**
```json
"metadata": {
  "version": "1.0",
  "last_updated": "2026-01-04T10:30:00Z",
  "currency": "CLP",
  "timezone": "America/Santiago"
}
```
- Versión del formato
- Última actualización
- Moneda usada
- Zona horaria

## Comparación: Antes vs Después

### ❌ Antes (Básico)
```json
{
  "sku": "15.0",
  "name": "Combo para 2",
  "price": 15090,
  "modifiers": [
    {
      "name": "Elige tu pasta",
      "required": true,
      "min": 2,
      "max": 2,
      "options": [
        {"name": "Pesto", "price": 0},
        {"name": "Alfredo", "price": 0}
   

**❌ PROBLEMAS:**
- Las options NO tienen SKU (no se puede trackear qué eligió el cliente)
- Las options NO tienen quantity (no se sabe cuántas unidades de cada opción)   ]
    }
  ]
}
```

### ✅ Después (Mejorado)
```json
{
  "sku": "15.0",
  "name": "Combo para 2",
  "price": 15090,
  "description": "2 Pastas y 2 bebidas de 350 ml a elegir.",
  "image": "https://posfagotto.cl/assets/combo-para-2.jpg",
  "available": true,
  "calories": 1200,
  "preparation_time": 20,
  "tags": ["popular", "combo", "para-compartir"],
  "modifiers": [
    {
      "sku": "mod-pasta-combo2",
      "name": "Elige tus pastas (2)",
      "description": "Selecciona 2 pastas de tu preferencia",
      "required": true,
      "min": 2,
      "max": 2,
      "options": [
        {quantity": 1,
          "description": "Salsa de albahaca fresca",
          "available": true,
          "default": false
        },
        {
          "sku": "opt-alfredo",
          "name": "Pasta Alfredo",
          "price": 0,
          "quantity": 1,
          "description": "Cremosa salsa blanca",
          "available": true,
          "default": false
        }
      ]
    }
  ]
}
```

**✅ SOLUCIONES:**
- ✅ Cada option tiene su **SKU único** → Permite identificar exactamente qué eligió
- ✅ Cada option tiene **quantity** → Define cuántas unidades incluye esa opción   ]
    }
  ]
}
```

## Beneficios de las Mejoras

1. **Tracking Completo**: Con SKUs en todos los niveles puedes rastrear qué modificadores y opciones son más populares

2. **Mejor UX**: Las descripciones e imágenes ayudan al cliente a decidir mejor

3. **Control de Inventario**: El campo `available` permite desactivar productos temporalmente

4. **Análisis de Datos**: Los tags y metadata facilitan reportes y análisis

5. **Información Nutricional**: Calorías para clientes que las necesitan

6. **Optimización de Cocina**: El `preparation_time` ayuda a estimar tiempos de entrega

7. **Flexibilidad**: Los campos opcionales pueden usarse o no según necesidad

8. **Escalabilidad**: Estructura preparada para crecer con nuevas features

## Campos Obligatorios vs Opcionales

### 📌 Obligatorios (Nivel Mínimo)
```json
{
  "success": true,
  "menu": {
    "sections": [
      {
        "sku": "1",
        "name": "Combos",
        "items": [
          {
            "sku": 
                    "sku": "opt-pesto",
                    "name": "Pesto",
                    "price": 0,
                    "quantity": 1
                  }
                ]
              }
            ]
          }
        ]
      }
    ]
  }
}
``` y options
- `sku` en modifierATORIOS en options:**
- `sku` → Identificador único de la opción
- `name` → Nombre de la opción
- `price` → Precio adicional (puede ser 0)
- `quantity` → Cantidad de unidades que incluye               {"name": "Pesto", "price": 0}
                ]
              }
            ]
          }
        ]
      }
    ]
  }
}
```

### ⭐ Recomendados (Nivel Intermedio)
- `description` en items
- `image` en items
- `available` en items
- `sku` en modifiers
- `sku` en options

- **🔴 OBLIGATORIO:** Cada option DEBE tener `sku` y `quantity`
- El `quantity` indica cuántas unidades de esa opción se incluyen (normalmente 1)

## Ejemplo Completo de una Option

```json
{
  "sku": "opt-pesto",        // 🔴 OBLIGATORIO - Identificador único
  "name": "Pasta Pesto",     // 🔴 OBLIGATORIO - Nombre visible
  "price": 0,                // 🔴 OBLIGATORIO - Precio adicional (0 si incluido)
  "quantity": 1,             // 🔴 OBLIGATORIO - Cantidad de unidades
  "description": "...",      // ⭐ RECOMENDADO - Descripción de la opción
  "available": true,         // ⭐ RECOMENDADO - Disponibilidad
  "default": false           // 🚀 OPCIONAL - Si viene preseleccionada
}
```

## ¿Por qué son obligatorios SKU y Quantity?

### SKU en Options
- ✅ Permite identificar exactamente qué eligió el cliente
- ✅ Facilita el tracking de preferencias
- ✅ Esencial para reportes de ventas por opción
- ✅ Necesario para gestión de inventario

### Quantity en Options
- ✅ Define cuántas unidades incluye cada opción
- ✅ Importante para combos (ej: "2 pastas" → quantity: 2)
- ✅ Facilita cálculo de inventario
- ✅ Necesario para preparación en cocina

**Ejemplo práctico:**
```json
{
  "name": "Elige tus pastas (2)",
  "min": 2,
  "max": 2,
  "options": [
    {
      "sku": "opt-pesto",
      "name": "Pasta Pesto",
      "price": 0,
      "quantity": 1  // Cliente elige 1 pasta Pesto
    },
    {
      "sku": "opt-alfredo", 
      "name": "Pasta Alfredo",
      "price": 0,
      "quantity": 1  // Cliente elige 1 pasta Alfredo
    }
  ]
}
```

Si el cliente selecciona 2 opciones distintas con quantity=1 cada una, obtiene 2 pastas diferentes.
### 🚀 Avanzados (Nivel Completo)
- `description` en modifiers y options
- `calories` y `preparation_time`
- `tags`
- `default` en options
- `restaurant` info completa
- `metadata` global

## Notas Importantes

- Todos los precios en **centavos** (15090 = $15.090 CLP)
- Las imágenes deben ser URLs públicas HTTPS
- Los SKUs deben ser únicos en su nivel
- El campo `required` en modifiers determina si el cliente DEBE elegir
- `min` y `max` controlan cuántas opciones puede/debe elegir el cliente
- Si `available: false`, el producto no se muestra al cliente
