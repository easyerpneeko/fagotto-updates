# Colección Postman - API Merchise

## ¿Qué es esto?

Es una colección de Postman con todos los tests listos para probar la integración con Merchise.

## Cómo importar

**Opción 1 - Arrastrar**
- Arrastra el archivo JSON a Postman y listo

**Opción 2 - Manual**
1. Abre Postman
2. Click en "Import" 
3. Selecciona el archivo Merchise_API_Tests.postman_collection.json
4. Click "Import"

## Configurar variables

Antes de correr los tests necesitas cambiar el App-Key:

1. Click derecho en la colección → Edit
2. Ve a la pestaña "Variables"
3. Cambia `app_key` por el Serial de tu local (está en el Excel)
4. Guarda

## Tests incluidos

### Tests funcionales (córrelos en orden):

**1. GET Menu** - Obtiene el catálogo de productos
- Endpoint: GET /api/merchise/menu
- Solo necesita el header App-Key
- Te devuelve todos los productos activos

**2. POST Nuevo Pedido** - Simula que Merchise envía un pedido
- Endpoint: POST /api/webhooks/merchise
- Genera un order_id random automáticamente
- Guarda el ID del pedido creado para los siguientes tests

**3. GET Lista Pedidos** - Ver los pedidos recibidos
- Endpoint: GET /api/merchise/orders
- Muestra todos los pedidos del local
- Puedes filtrar por status, fecha_desde, fecha_hasta

**4. PUT Actualizar Estado** - Cambiar el estado de un pedido
- Endpoint: PUT /api/merchise/orders/{id}/status
- Usa el ID guardado del test anterior
- Cambia de "pending" a "accepted"

**5. POST Duplicado** - Verifica detección de duplicados
- Intenta crear el mismo pedido otra vez
- Debería responder "ya registrado previamente"

### Tests de errores:

**6. Sin App-Key** - Verifica que pide autenticación (401)

**7. App-Key Inválido** - Verifica que valida el App-Key (404)

**8. Body Inválido** - Verifica validaciones de campos requeridos (422)

## Estados disponibles

Los pedidos pueden tener estos estados:
- `pending` → recién llegó
- `accepted` → lo aceptaste
- `preparing` → lo están preparando
- `ready` → está listo para entregar
- `delivered` → ya se entregó
- `cancelled` → se canceló

Cuando cambias el estado automáticamente se guarda la hora (accepted_at, ready_at, delivered_at).

## Verificar en la BD

Si quieres ver los datos directo en MySQL:

```sql
-- Ver productos
SELECT * FROM easyerp_master.merchise_productos;

-- Ver pedidos recibidos
SELECT * FROM easyerp_master.merchise_pedidos ORDER BY id DESC;

-- Ver ventas por local
SELECT 
    app_id,
    COUNT(*) as total_pedidos,
    SUM(total) as total_ventas
FROM easyerp_master.merchise_pedidos 
WHERE status != 'cancelled'
GROUP BY app_id;
```

## Problemas comunes

**"Authorization Token not found"**
- Verifica que pusiste el header App-Key
- Revisa que el valor sea el Serial correcto

**"Local no encontrado"**
- El Serial no existe o está mal escrito
- Verifica en el Excel Locales_Fagotto.xlsx

**"The order_id field is required"**
- Falta el campo order_id en el body
- Asegúrate de enviar JSON válido

**"Pedido ya registrado"**
- Ya existe un pedido con ese order_id
- Es normal, significa que la detección de duplicados funciona

## Contacto

Si hay algún problema o duda avisen nomás.
