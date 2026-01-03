# 🧪 Pruebas API Mercadise con Postman

## 📋 Configuración Previa

### 1️⃣ Variables de Entorno en Postman

Crear una colección con estas variables:

```
baseUrl: https://posfagotto.cl
app_key: 463E-A767-C73C-58B5-2116
```

---

## 🔍 Test 1: Obtener Menú (GET)

**Mercadise solicita el catálogo de productos**

### Request:
```http
GET {{baseUrl}}/api/mercadise/menu
Headers:
  App-Key: {{app_key}}
  Accept: application/json
```

### Response Esperado (200 OK):
```json
{
  "success": true,
  "local": {
    "id": 97,
    "nombre": "Fagotto Rosario norte",
    "serial": "463E-A767-C73C-58B5-2116"
  },
  "productos": [
    {
      "id": 1,
      "nombre": "Pizza Margarita",
      "precio": 8500,
      "categoria": "Pizzas",
      "descripcion": "Salsa de tomate, mozzarella y albahaca",
      "foto": null,
      "disponible": true,
      "orden": 1
    },
    {
      "id": 2,
      "nombre": "Coca Cola",
      "precio": 2000,
      "categoria": "Bebidas",
      "descripcion": "Bebida 500ml",
      "foto": null,
      "disponible": true,
      "orden": 2
    }
  ],
  "total_productos": 2,
  "timestamp": "2026-01-01 14:30:00"
}
```

### Postman Test Script:
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has success true", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
});

pm.test("Response has local info", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.local).to.have.property('id');
    pm.expect(jsonData.local).to.have.property('nombre');
});

pm.test("Response has productos array", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.productos).to.be.an('array');
});
```

---

## 📦 Test 2: Recibir Pedido (POST)

**Mercadise envía un nuevo pedido**

### Request:
```http
POST {{baseUrl}}/api/webhooks/mercadise
Headers:
  App-Key: {{app_key}}
  Content-Type: application/json
  
Body:
{
  "order_id": "MERC-2026-00001",
  "customer": {
    "name": "Juan Pérez",
    "phone": "+56912345678",
    "address": "Las Condes, Santiago"
  },
  "items": [
    {
      "product_id": 1,
      "nombre": "Pizza Margarita",
      "cantidad": 2,
      "precio": 8500
    },
    {
      "product_id": 2,
      "nombre": "Coca Cola",
      "cantidad": 1,
      "precio": 2000
    }
  ],
  "total": 19000,
  "payment_method": "efectivo"
}
```

### Response Esperado (201 Created):
```json
{
  "success": true,
  "message": "Pedido recibido exitosamente",
  "pedido_id": 1,
  "order_id": "MERC-2026-00001",
  "status": "received",
  "estimated_time": "30 minutos",
  "local": {
    "id": 97,
    "nombre": "Fagotto Rosario norte"
  }
}
```

### Postman Test Script:
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Order was created", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
    pm.expect(jsonData.pedido_id).to.be.a('number');
});

pm.test("Order ID matches", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.order_id).to.eql("MERC-2026-00001");
});

// Guardar pedido_id para próximos tests
pm.environment.set("pedido_id", pm.response.json().pedido_id);
```

---

## 🔄 Test 3: Pedido Duplicado

**Enviar el mismo order_id dos veces**

### Request:
```http
POST {{baseUrl}}/api/webhooks/mercadise
Headers:
  App-Key: {{app_key}}
  Content-Type: application/json
  
Body:
{
  "order_id": "MERC-2026-00001",
  "customer": {
    "name": "Juan Pérez",
    "phone": "+56912345678"
  },
  "items": [
    {
      "nombre": "Pizza Margarita",
      "cantidad": 1,
      "precio": 8500
    }
  ],
  "total": 8500
}
```

### Response Esperado (200 OK):
```json
{
  "success": true,
  "message": "Pedido ya registrado previamente",
  "order_id": "MERC-2026-00001",
  "status": "pending"
}
```

### Postman Test Script:
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Duplicate detected", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.message).to.include("ya registrado");
});
```

---

## 📊 Test 4: Listar Pedidos (GET)

**Obtener lista de pedidos del local**

### Request:
```http
GET {{baseUrl}}/api/mercadise/orders
Headers:
  App-Key: {{app_key}}
  Accept: application/json
```

### Query Parameters Opcionales:
```
?status=pending
?fecha_desde=2026-01-01
?fecha_hasta=2026-01-31
```

### Response Esperado (200 OK):
```json
{
  "success": true,
  "pedidos": [
    {
      "id": 1,
      "app_id": 97,
      "order_id_mercadise": "MERC-2026-00001",
      "customer_name": "Juan Pérez",
      "customer_phone": "+56912345678",
      "customer_address": "Las Condes, Santiago",
      "items": [
        {
          "product_id": 1,
          "nombre": "Pizza Margarita",
          "cantidad": 2,
          "precio": 8500
        }
      ],
      "total": 19000,
      "status": "pending",
      "payment_method": "efectivo",
      "received_at": "2026-01-01 14:30:00",
      "created_at": "2026-01-01 14:30:00"
    }
  ],
  "total": 1,
  "local": {
    "id": 97,
    "nombre": "Fagotto Rosario norte"
  }
}
```

### Postman Test Script:
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has pedidos array", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.pedidos).to.be.an('array');
});
```

---

## ✅ Test 5: Actualizar Estado (PUT)

**Cambiar estado de un pedido**

### Request:
```http
PUT {{baseUrl}}/api/mercadise/orders/{{pedido_id}}/status
Headers:
  App-Key: {{app_key}}
  Content-Type: application/json
  
Body:
{
  "status": "accepted"
}
```

### Estados Válidos:
- `pending` → Recién recibido
- `accepted` → Aceptado por el local
- `preparing` → En preparación
- `ready` → Listo para entregar
- `delivered` → Entregado
- `cancelled` → Cancelado

### Response Esperado (200 OK):
```json
{
  "success": true,
  "message": "Estado actualizado exitosamente",
  "order_id": "MERC-2026-00001",
  "status": "accepted"
}
```

### Postman Test Script:
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Status updated", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
    pm.expect(jsonData.status).to.eql("accepted");
});
```

---

## ❌ Test 6: Error - Sin App-Key

**Request sin header App-Key**

### Request:
```http
GET {{baseUrl}}/api/mercadise/menu
Headers:
  Accept: application/json
```

### Response Esperado (401 Unauthorized):
```json
"Codigo de aplicacion no encontrado en header."
```

### Postman Test Script:
```javascript
pm.test("Status code is 401", function () {
    pm.response.to.have.status(401);
});
```

---

## ❌ Test 7: Error - App-Key Inválido

**Request con App-Key que no existe**

### Request:
```http
GET {{baseUrl}}/api/mercadise/menu
Headers:
  App-Key: INVALID-KEY-12345
  Accept: application/json
```

### Response Esperado (404 Not Found):
```json
"Serial no encontrado."
```

### Postman Test Script:
```javascript
pm.test("Status code is 404", function () {
    pm.response.to.have.status(404);
});
```

---

## ❌ Test 8: Error - Datos Inválidos

**Pedido con datos faltantes**

### Request:
```http
POST {{baseUrl}}/api/webhooks/mercadise
Headers:
  App-Key: {{app_key}}
  Content-Type: application/json
  
Body:
{
  "order_id": "MERC-2026-00002",
  "customer": {
    "name": "María López"
    // Falta phone (requerido)
  },
  "items": [],  // Array vacío (mínimo 1 item)
  "total": 0
}
```

### Response Esperado (422 Unprocessable Entity):
```json
{
  "success": false,
  "message": "Datos del pedido inválidos",
  "errors": {
    "customer.phone": ["The customer.phone field is required."],
    "items": ["The items must have at least 1 items."]
  }
}
```

### Postman Test Script:
```javascript
pm.test("Status code is 422", function () {
    pm.response.to.have.status(422);
});

pm.test("Has validation errors", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.errors).to.be.an('object');
});
```

---

## 🧪 Test 9: Múltiples Locales

**Probar con diferente App-Key (otro local)**

### Request:
```http
GET {{baseUrl}}/api/mercadise/menu
Headers:
  App-Key: OTRO-SERIAL-LOCAL-2
  Accept: application/json
```

### Verificar:
- El `local.id` es diferente
- El `local.nombre` corresponde al local correcto
- Los productos son los mismos (maestro centralizado)

---

## 📝 Notas Importantes

### Orden de Ejecución Recomendado:
1. Test 1 (GET Menu) - Verificar que hay productos
2. Test 2 (POST Order) - Crear pedido nuevo
3. Test 4 (GET Orders) - Verificar que aparece
4. Test 5 (PUT Status) - Actualizar a "accepted"
5. Test 3 (Duplicate) - Verificar protección duplicados
6. Tests 6-8 (Errores) - Validar manejo de errores

### Headers Obligatorios:
- `App-Key`: Identifica el local (cada local tiene su Serial único)
- `Content-Type: application/json` (para POST/PUT)
- `Accept: application/json` (recomendado)

### Base de Datos:
Antes de las pruebas, ejecuta los SQL de:
- `database/create_merchise_productos.sql`
- `database/create_merchise_pedidos.sql`

---

## 🚀 Colección Postman JSON

Importa esta colección completa:

```json
{
  "info": {
    "name": "Mercadise API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "baseUrl",
      "value": "https://posfagotto.cl"
    },
    {
      "key": "app_key",
      "value": "463E-A767-C73C-58B5-2116"
    }
  ]
}
```

---

## 📞 Soporte

Si algún test falla:
1. Verifica que las tablas SQL están creadas
2. Confirma que el `App-Key` existe en la tabla `aplications`
3. Revisa los logs en `Server app/storage/logs/laravel.log`
4. Verifica conexión a `easyerp_master` en `.env`

---

✅ **Sistema listo para pruebas con Postman**
