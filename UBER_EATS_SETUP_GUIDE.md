# Integración de Uber Eats - Guía de Configuración

## 📋 Resumen

Este módulo permite gestionar pedidos de Uber Eats directamente desde la aplicación, sin necesidad de usar el navegador. Incluye:

- ✅ Recepción de pedidos en tiempo real
- ✅ Notificaciones visuales y sonoras
- ✅ Aceptar/rechazar pedidos desde la app
- ✅ Seguimiento de estado de pedidos
- ✅ Cancelación de pedidos
- ✅ Dashboard completo de gestión

## 🚀 Pasos de Configuración

### 1. Obtener Credenciales de Uber Eats

1. Ve a [Uber Developer Dashboard](https://developer.uber.com/)
2. Inicia sesión con tu cuenta de Uber
3. Haz clic en "Create an App"
4. Selecciona "Uber Eats" como tipo de aplicación
5. Completa la información de tu restaurante
6. En "Scopes", selecciona:
   - `eats.store`
   - `eats.orders`
   - `eats.orders.read`
   - `eats.orders.manage`

### 2. Configurar Variables de Entorno

1. Copia el archivo `.env.uber-eats.example` como `.env.uber-eats`
2. Reemplaza los valores con tus credenciales reales:

```bash
# Desde tu Uber Developer Dashboard
UBER_EATS_CLIENT_ID=tu_client_id_aqui
UBER_EATS_CLIENT_SECRET=tu_client_secret_aqui
UBER_EATS_ACCESS_TOKEN=tu_access_token_aqui
UBER_EATS_STORE_ID=tu_store_id_aqui

# URL para webhooks (debe ser pública)
UBER_EATS_WEBHOOK_URL=https://tu-dominio.com/api/uber-eats/webhook
```

### 3. Configurar Webhooks

Para recibir notificaciones en tiempo real:

1. En tu Uber Developer Dashboard, ve a "Webhooks"
2. Agrega una nueva URL de webhook: `https://tu-dominio.com/api/uber-eats/webhook`
3. Selecciona los eventos:
   - `orders.notification`
   - `orders.status_changed`
   - `orders.cancelled`

### 4. Instalar Dependencias (si es necesario)

```bash
# En el directorio Server app/
composer install
npm install
```

### 5. Probar la Conexión

1. Inicia el servidor Laravel
2. Ve a la sección "Uber Eats" en la aplicación
3. Verifica que aparezca "Conectado" en el estado

## 📂 Archivos Creados

### Frontend (Vue.js)
- `src/renderer/components/uber-eats/UberEatsOrders.vue` - Componente principal
- `src/renderer/helpers/UberEatsHelper.js` - Funciones auxiliares
- Rutas agregadas en `router/index.js`
- Menú agregado en `layout.vue`

### Backend (Laravel)
- `Server app/app/Http/Controllers/UberEatsController.php` - Controlador API
- Rutas agregadas en `routes/api.php`

## 🔧 Funcionalidades

### Dashboard Principal
- **Estado de Conexión**: Muestra si la API está configurada correctamente
- **Pedidos Pendientes**: Lista de pedidos esperando aceptación/rechazo
- **Pedidos Activos**: Pedidos en proceso de preparación/entrega

### Notificaciones
- **Alerta Visual**: Banner rojo para nuevos pedidos
- **Sonido**: Reproducción automática para alertar
- **Notificaciones del Sistema**: Si están habilitadas en el navegador

### Gestión de Pedidos
- **Aceptar**: Confirma el pedido y lo mueve a activos
- **Rechazar**: Con razón seleccionable
- **Actualizar Estado**: Preparación → Listo → Recogido
- **Cancelar**: Para pedidos activos si es necesario

## 🎯 API Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/uber-eats/status` | Estado de conexión |
| GET | `/api/uber-eats/orders/pending` | Pedidos pendientes |
| GET | `/api/uber-eats/orders/active` | Pedidos activos |
| POST | `/api/uber-eats/orders/{id}/accept` | Aceptar pedido |
| POST | `/api/uber-eats/orders/{id}/reject` | Rechazar pedido |
| POST | `/api/uber-eats/orders/{id}/status` | Actualizar estado |
| POST | `/api/uber-eats/orders/{id}/cancel` | Cancelar pedido |
| POST | `/api/uber-eats/webhook` | Recibir webhooks |

## 🔐 Seguridad

- Las credenciales se almacenan en variables de entorno
- Los webhooks usan secret para validar autenticidad
- Todas las llamadas API incluyen autenticación OAuth
- Logs de auditoría para todas las operaciones

## 🚨 Solución de Problemas

### "No Configurado" en el Estado
- Verifica que las variables de entorno estén correctas
- Confirma que el `UBER_EATS_ACCESS_TOKEN` sea válido
- Revisa los logs del servidor para errores de autenticación

### No Llegan Notificaciones en Tiempo Real
- Confirma que la URL del webhook sea accesible públicamente
- Verifica que los eventos estén configurados en Uber Developer Dashboard
- Revisa que el `UBER_EATS_WEBHOOK_SECRET` coincida

### Errores al Aceptar/Rechazar Pedidos
- Confirma que tu tienda tenga permisos para gestionar pedidos
- Verifica que el `UBER_EATS_STORE_ID` sea correcto
- Revisa los scopes en tu aplicación de Uber

## 📞 Soporte

Para problemas específicos de Uber Eats API:
- [Documentación Oficial](https://developer.uber.com/docs/eats)
- [Soporte Uber Developer](https://developer.uber.com/support)

## 🎉 ¡Listo!

Una vez configurado correctamente, podrás gestionar todos tus pedidos de Uber Eats directamente desde la aplicación, sin necesidad de cambiar de ventana o usar el navegador.
