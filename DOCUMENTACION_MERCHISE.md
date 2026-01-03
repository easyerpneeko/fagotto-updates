# API Merchise - Fagotto

Acá está toda la info de la API para la integración.

## Resumen

La API tiene 4 endpoints:
1. **GET** /api/merchise/menu → Para que lean nuestro catálogo
2. **POST** /api/webhooks/merchise → Para que nos envíen pedidos
3. **GET** /api/merchise/orders → Para ver los pedidos que llegaron
4. **PUT** /api/merchise/orders/{id}/status → Para actualizar el estado

## Autenticación

Super simple, solo necesitan mandar el header:
```
App-Key: {Serial del local}
```

Cada uno de nuestros 22 locales tiene su Serial único. Así sabemos de qué local es cada pedido.

## 1. Obtener Menú

```bash
GET https://posfagotto.cl/api/merchise/menu
```

**Headers:**
```
App-Key: 463E-A767-C73C-58B5-2116
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Menú obtenido correctamente",
  "data": {
    "local": {
      "id": 97,
      "nombre": "Fagotto Providencia"
    },
    "productos": [
      {
        "id": 1,
        "nombre": "Pizza Margherita",
        "precio": 8990,
        "categoria": "Pizzas",
        "descripcion": "Salsa de tomate, mozzarella y albahaca",
        "foto": "https://...",
        "activo": 1
      }
    ],
    "total": 8,
    "timestamp": "2026-01-01 14:30:00"
  }
}
```

## 2. Recibir Pedido (Webhook)

Este endpoint es para que nos manden los pedidos cuando un cliente ordena.

```bash
POST https://posfagotto.cl/api/webhooks/merchise
```

**Headers:**
```
App-Key: 463E-A767-C73C-58B5-2116
Content-Type: application/json
```

**Body:**
```json
{
  "order_id": "MERCH-2026-001",
  "customer": {
    "name": "Juan Pérez",
    "phone": "+56912345678",
    "address": "Av. Providencia 1234, Depto 501"
  },
  "items": [
    {
      "product_id": 1,
      "product_name": "Pizza Margherita",
      "quantity": 2,
      "price": 8990
    }
  ],
  "total": 17980,
  "payment_method": "online"
}
```

**Respuesta OK:**
```json
{
  "success": true,
  "message": "Pedido recibido correctamente",
  "data": {
    "pedido_id": 123,
    "order_id": "MERCH-2026-001",
    "status": "pending",
    "estimated_time": 35,
    "local": {
      "id": 97,
      "nombre": "Fagotto Providencia"
    }
  }
}
```

**Si es duplicado:**
```json
{
  "success": true,
  "message": "Pedido ya registrado previamente",
  "data": {
    "pedido_id": 123,
    "order_id": "MERCH-2026-001",
    "status": "accepted"
  }
}
```

### Validaciones

Campos obligatorios:
- `order_id` → string, único
- `customer.name` → string
- `customer.phone` → string
- `items` → array con mínimo 1 producto
- `total` → número mayor a 0

## 3. Listar Pedidos

Para ver los pedidos que han llegado:

```bash
GET https://posfagotto.cl/api/merchise/orders
```

**Headers:**
```
App-Key: 463E-A767-C73C-58B5-2116
```

**Filtros opcionales (query params):**
- `status` → pending, accepted, preparing, ready, delivered, cancelled
- `fecha_desde` → 2026-01-01
- `fecha_hasta` → 2026-01-31

**Ejemplo con filtros:**
```bash
GET https://posfagotto.cl/api/merchise/orders?status=pending&fecha_desde=2026-01-01
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Pedidos encontrados",
  "data": {
    "local": {
      "id": 97,
      "nombre": "Fagotto Providencia"
    },
    "pedidos": [
      {
        "id": 123,
        "order_id_merchise": "MERCH-2026-001",
        "customer_name": "Juan Pérez",
        "customer_phone": "+56912345678",
        "customer_address": "Av. Providencia 1234",
        "items": [
          {
            "product_id": 1,
            "product_name": "Pizza Margherita",
            "quantity": 2,
            "price": 8990
          }
        ],
        "total": 17980,
        "status": "pending",
        "payment_method": "online",
        "received_at": "2026-01-01 14:30:00"
      }
    ],
    "total": 1
  }
}
```

## 4. Actualizar Estado

Para cambiar el estado de un pedido:

```bash
PUT https://posfagotto.cl/api/merchise/orders/123/status
```

**Headers:**
```
App-Key: 463E-A767-C73C-58B5-2116
Content-Type: application/json
```

**Body:**
```json
{
  "status": "accepted"
}
```

**Estados válidos:**
- `pending` → cuando recién llega
- `accepted` → cuando lo aceptan
- `preparing` → cuando lo están haciendo
- `ready` → cuando está listo
- `delivered` → cuando lo entregaron
- `cancelled` → si se cancela

**Respuesta:**
```json
{
  "success": true,
  "message": "Estado actualizado correctamente",
  "data": {
    "pedido_id": 123,
    "order_id": "MERCH-2026-001",
    "status_anterior": "pending",
    "status_nuevo": "accepted",
    "accepted_at": "2026-01-01 14:35:00"
  }
}
```

**Nota:** Cuando cambias a "accepted", "ready" o "delivered" se guarda automáticamente la hora en `accepted_at`, `ready_at` o `delivered_at`.

## Códigos de Error

**401 - No Autorizado**
```json
{
  "success": false,
  "message": "Authorization Token not found"
}
```
→ Falta el header App-Key

**404 - No Encontrado**
```json
{
  "success": false,
  "message": "Local no encontrado"
}
```
→ El Serial es inválido o no existe

**422 - Validación Fallida**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "order_id": ["The order id field is required."],
    "customer.name": ["The customer.name field is required."]
  }
}
```
→ Faltan campos obligatorios o tienen formato inválido

**500 - Error del Servidor**
```json
{
  "success": false,
  "message": "Error al procesar la solicitud"
}
```
→ Algo falló de nuestro lado, contactarnos

## Checklist de Integración

Para que funcione todo necesitan:

1. ✅ Tener Postman instalado
2. ✅ Importar la colección Merchise_API_Tests.postman_collection.json
3. ✅ Tener el Excel con los 22 App-Keys (Locales_Fagotto.xlsx)
4. ✅ Cambiar la variable `app_key` en Postman
5. ✅ Correr los tests del 1 al 5 para validar que funciona
6. ✅ Configurar el webhook en producción
7. ✅ Probar con un pedido real

## Flujo de Integración

1. Merchise llama GET /api/merchise/menu con el App-Key del local
2. Guardan nuestro catálogo en su sistema
3. Cuando un cliente hace un pedido, Merchise llama POST /api/webhooks/merchise
4. Nosotros guardamos el pedido y respondemos
5. Opcionalmente pueden llamar GET /api/merchise/orders para ver el historial
6. Pueden llamar PUT /api/merchise/orders/{id}/status para actualizar estados

## Base de Datos

Si necesitan ver los datos directo:

**Productos:**
```sql
SELECT * FROM easyerp_master.merchise_productos;
```

**Pedidos:**
```sql
SELECT * FROM easyerp_master.merchise_pedidos ORDER BY id DESC;
```

**Ventas por local:**
```sql
SELECT 
    app_id,
    COUNT(*) as total_pedidos,
    SUM(total) as total_ventas
FROM easyerp_master.merchise_pedidos 
WHERE status != 'cancelled'
GROUP BY app_id;
```

## Contacto

Cualquier duda o problema avisen nomás.
