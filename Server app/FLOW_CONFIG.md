# Configuración de Flow.cl

## 1. Instalar SDK de Flow

```bash
cd "Server app"
composer require flow-cl/flow-api
```

## 2. Configurar credenciales en .env

Agrega estas líneas a tu archivo `.env`:

```env
# Flow.cl Credentials
FLOW_API_KEY=tu_api_key_aqui
FLOW_SECRET_KEY=tu_secret_key_aqui
FLOW_API_URL=https://sandbox.flow.cl/api
# Para producción usar: https://www.flow.cl/api
```

## 3. Obtener las credenciales desde Flow Dashboard

1. Ve a: https://dashboard.flow.cl/private/dashboard/
2. En el menú lateral, busca **"Integración"** o **"API Keys"**
3. Copia tu **API Key** y **Secret Key**
4. Pégalas en el archivo `.env`

## 4. Configurar Webhooks en Flow

1. En Flow Dashboard, ve a **"Integración"** → **"Webhooks"**
2. Agrega estas URLs:
   - **URL de confirmación**: `https://posfagotto.cl/api/local/flow/confirm`
   - **URL de retorno**: `https://posfagotto.cl/api/local/flow/return`

## 5. Modo Sandbox vs Producción

**Sandbox (pruebas):**
```env
FLOW_API_URL=https://sandbox.flow.cl/api
```
- Usar tarjetas de prueba de Flow
- No se cobran pagos reales

**Producción:**
```env
FLOW_API_URL=https://www.flow.cl/api
```
- Cobros reales
- Requiere cuenta Flow aprobada

## 6. Subir archivos al servidor

Sube estos archivos a tu servidor (posfagotto.cl):
- `app/Http/Controllers/Controllers_local/FlowController.php`
- `routes/api.php` (actualizado con rutas Flow)
- `.env` (con las credenciales Flow)

## 7. Probar la integración

Ejecuta un pago de prueba desde el frontend y verifica:
- Los logs en `storage/logs/laravel.log`
- El estado del pago en Flow Dashboard
