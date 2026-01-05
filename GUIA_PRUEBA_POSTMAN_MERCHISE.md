# Guía de Prueba Completa - Merchise con Modifiers

## 📋 Pasos para Probar Todo

### PASO 1: Ejecutar los SQL en orden ✅

```bash
# 1. Actualizar tabla merchise_pedidos (agregar columnas)
mysql -u root -p easyerp_master < database/migration_add_platform_discounts.sql

# 2. Crear tablas nuevas (sections, items, modifiers, options)
mysql -u root -p easyerp_master < database/create_merchise_modifiers_options.sql

# 3. Cargar datos de prueba
mysql -u root -p easyerp_master < database/test_data_merchise_complete.sql
```

O desde MySQL Workbench/phpMyAdmin: ejecuta los 3 archivos en ese orden.

---

### PASO 2: Actualizar el Controller para devolver el menú con modifiers

Abre: `Server app/app/Http/Controllers/MercadiseController.php`

Busca el método `getMenu()` y cámbialo por esto:

```php
public function getMenu(Request $request)
{
    try {
        $appKey = $request->header('App-Key');
        
        if (!$appKey) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Token not found'
            ], 401);
        }

        // Obtener info del local
        $localInfo = DB::connection('central')
            ->table('aplicaciones')
            ->where('Serial', $appKey)
            ->first();

        if (!$localInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Local no encontrado'
            ], 404);
        }

        // Obtener menú con estructura completa
        $sections = DB::connection('central')
            ->table('merchise_sections')
            ->where('activo', 1)
            ->orderBy('orden')
            ->get();

        $menuSections = [];

        foreach ($sections as $section) {
            // Obtener items de la sección
            $items = DB::connection('central')
                ->table('merchise_items')
                ->where('section_id', $section->id)
                ->where('available', 1)
                ->orderBy('orden')
                ->get();

            $sectionItems = [];

            foreach ($items as $item) {
                // Obtener modifiers del item
                $modifiers = DB::connection('central')
                    ->table('merchise_modifiers')
                    ->where('item_id', $item->id)
                    ->orderBy('orden')
                    ->get();

                $itemModifiers = [];

                foreach ($modifiers as $modifier) {
                    // Obtener options del modifier
                    $options = DB::connection('central')
                        ->table('merchise_options')
                        ->where('modifier_id', $modifier->id)
                        ->where('available', 1)
                        ->orderBy('orden')
                        ->get();

                    $modifierOptions = [];

                    foreach ($options as $option) {
                        $modifierOptions[] = [
                            'sku' => $option->sku,
                            'name' => $option->name,
                            'price' => (float) $option->price,
                            'quantity' => (int) $option->quantity,
                            'description' => $option->description,
                            'available' => (bool) $option->available,
                            'default' => (bool) $option->default
                        ];
                    }

                    $itemModifiers[] = [
                        'sku' => $modifier->sku,
                        'name' => $modifier->name,
                        'description' => $modifier->description,
                        'required' => (bool) $modifier->required,
                        'min' => (int) $modifier->min,
                        'max' => (int) $modifier->max,
                        'options' => $modifierOptions
                    ];
                }

                $sectionItems[] = [
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'description' => $item->description,
                    'image' => $item->image,
                    'available' => (bool) $item->available,
                    'calories' => $item->calories,
                    'preparation_time' => $item->preparation_time,
                    'tags' => $item->tags ? json_decode($item->tags) : [],
                    'modifiers' => $itemModifiers
                ];
            }

            $menuSections[] = [
                'sku' => $section->sku,
                'name' => $section->name,
                'description' => $section->description,
                'image' => $section->image,
                'items' => $sectionItems
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Menú obtenido correctamente',
            'data' => [
                'local' => [
                    'id' => $localInfo->ID_Aplicacion,
                    'nombre' => $localInfo->Nombre,
                    'serial' => $localInfo->Serial
                ],
                'menu' => [
                    'sections' => $menuSections
                ],
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener el menú',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

---

### PASO 3: Probar en Postman 🚀

#### Test 1: GET Menu con Modifiers

```
GET https://posfagotto.cl/api/merchise/menu
Headers:
  App-Key: 463E-A767-C73C-58B5-2116
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Menú obtenido correctamente",
  "data": {
    "local": {
      "id": 97,
      "nombre": "Fagotto Providencia",
      "serial": "463E-A767-C73C-58B5-2116"
    },
    "menu": {
      "sections": [
        {
          "sku": "sec-1",
          "name": "Combos",
          "description": "Nuestros combos más populares para compartir",
          "items": [
            {
              "sku": "item-15.0",
              "name": "Combo para 2",
              "price": 15090,
              "modifiers": [
                {
                  "sku": "mod-pasta-combo2",
                  "name": "Elige tus pastas (2)",
                  "required": true,
                  "min": 2,
                  "max": 2,
                  "options": [
                    {
                      "sku": "opt-pesto",
                      "name": "Pasta Pesto",
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
}
```

✅ **Verificar:**
- Tiene secciones
- Cada item tiene modifiers
- Cada modifier tiene options
- Los SKUs están presentes

---

#### Test 2: POST Pedido con Modifiers Seleccionados

```
POST https://posfagotto.cl/api/webhooks/merchise
Headers:
  App-Key: 463E-A767-C73C-58B5-2116
  Content-Type: application/json

Body:
{
  "order_id": "MERCH-TEST-001",
  "platform": "uber_eats",
  "customer": {
    "name": "Juan Pérez",
    "phone": "+56912345678",
    "email": "juan@email.com",
    "address": "Av. Providencia 1234, Depto 501"
  },
  "items": [
    {
      "product_sku": "item-15.0",
      "product_name": "Combo para 2",
      "quantity": 1,
      "price": 15090,
      "modifiers": [
        {
          "modifier_sku": "mod-pasta-combo2",
          "modifier_name": "Elige tus pastas (2)",
          "selected_options": [
            {
              "sku": "opt-pesto",
              "name": "Pasta Pesto",
              "quantity": 1,
              "price": 0
            },
            {
              "sku": "opt-alfredo",
              "name": "Pasta Alfredo",
              "quantity": 1,
              "price": 0
            }
          ]
        },
        {
          "modifier_sku": "mod-bebida-combo2",
          "modifier_name": "Elige tus bebidas (2)",
          "selected_options": [
            {
              "sku": "opt-cocacola",
              "name": "Coca-Cola Original 350ml",
              "quantity": 1,
              "price": 0
            },
            {
              "sku": "opt-sprite",
              "name": "Sprite Original 350ml",
              "quantity": 1,
              "price": 0
            }
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
    "description": "10% de descuento"
  },
  "total": 13590,
  "payment_method": "online",
  "notes": "Sin cebolla por favor"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Pedido recibido correctamente",
  "data": {
    "pedido_id": 1,
    "order_id": "MERCH-TEST-001",
    "status": "pending"
  }
}
```

---

#### Test 3: Pedido SIN Cliente (Rappi)

```json
{
  "order_id": "MERCH-TEST-002",
  "platform": "rappi",
  "customer": null,
  "items": [
    {
      "product_sku": "item-42.0",
      "product_name": "Pasta Pesto",
      "quantity": 1,
      "price": 6290
    }
  ],
  "subtotal": 6290,
  "total": 6290,
  "payment_method": "online"
}
```

✅ **Verificar:**
- Se acepta pedido sin customer
- Platform se guarda correctamente
- Discount es opcional

---

#### Test 4: GET Orders (Ver pedidos recibidos)

```
GET https://posfagotto.cl/api/merchise/orders
Headers:
  App-Key: 463E-A767-C73C-58B5-2116
```

✅ **Verificar:**
- Muestra los pedidos con platform
- Muestra subtotal y descuento
- Muestra modifiers seleccionados

---

## 🔍 Queries para Verificar en MySQL

```sql
-- Ver pedido completo con modifiers
SELECT 
    p.order_id_mercadise,
    p.platform,
    p.customer_name,
    pi.item_name,
    pm.modifier_name,
    po.option_name,
    po.quantity
FROM merchise_pedidos p
JOIN merchise_pedidos_items pi ON p.id = pi.pedido_id
LEFT JOIN merchise_pedidos_modifiers pm ON pi.id = pm.pedido_item_id
LEFT JOIN merchise_pedidos_options po ON pm.id = po.pedido_modifier_id
WHERE p.order_id_mercadise = 'MERCH-TEST-001';

-- Ver ventas por plataforma
SELECT 
    platform,
    COUNT(*) AS pedidos,
    SUM(subtotal) AS subtotal,
    SUM(discount_amount) AS descuentos,
    SUM(total) AS total
FROM merchise_pedidos
GROUP BY platform;
```

---

## ✅ Checklist de Verificación

- [ ] SQL migration_add_platform_discounts ejecutado
- [ ] SQL create_merchise_modifiers_options ejecutado
- [ ] SQL test_data_merchise_complete ejecutado
- [ ] Controller getMenu() actualizado
- [ ] GET /api/merchise/menu devuelve sections con modifiers
- [ ] POST /api/webhooks/merchise acepta pedidos con modifiers
- [ ] POST acepta pedidos SIN customer
- [ ] Campo platform se guarda correctamente
- [ ] Descuentos se registran correctamente
- [ ] GET /api/merchise/orders muestra pedidos completos

---

## 🎯 Resultado Esperado

Al final deberías poder:
1. ✅ Ver el menú con todas las opciones disponibles
2. ✅ Recibir pedidos con las opciones que eligió el cliente
3. ✅ Ver qué pasta y bebida eligió en cada combo
4. ✅ Saber de qué plataforma vino el pedido
5. ✅ Registrar descuentos aplicados
6. ✅ Aceptar pedidos sin datos de cliente

¡Todo listo para enviarle al señor de Merchise! 🚀
