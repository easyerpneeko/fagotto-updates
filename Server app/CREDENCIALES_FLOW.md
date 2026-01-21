# Flow.cl - Configuración de Credenciales

## 🔑 Tus Credenciales

```env
FLOW_API_KEY=77F1B4FF-B86C-4960-8206-6B51B5LD813D
FLOW_SECRET_KEY=3cf5929dcfc742bfceb1c1100ad2a0fa75d0d1e5
FLOW_API_URL=https://www.flow.cl/api
```

## 📝 Agregar al archivo .env

Abre tu archivo `.env` en el servidor y agrega estas 3 líneas al final:

```bash
# Flow.cl Credentials
FLOW_API_KEY=77F1B4FF-B86C-4960-8206-6B51B5LD813D
FLOW_SECRET_KEY=3cf5929dcfc742bfceb1c1100ad2a0fa75d0d1e5
FLOW_API_URL=https://www.flow.cl/api
```

## ⚠️ IMPORTANTE: El código ya tiene las credenciales por defecto

El `FlowController.php` ya tiene tus credenciales configuradas por defecto, así que **funcionará inmediatamente** aunque no estén en el `.env`.

Pero es mejor práctica agregarlas al `.env` para mayor seguridad.

## 🚀 Siguiente Paso: Instalar SDK

```bash
cd "Server app"
composer require flow-cl/flow-api
```

## 🔗 Configurar Webhooks en Flow

Ve a: https://dashboard.flow.cl/

Configura:
- **URL Confirmación**: `https://posfagotto.cl/api/local/flow/confirm`
- **URL Retorno**: `https://posfagotto.cl/api/local/flow/return`

## ✅ Flujo Funcional

1. ✅ Cliente selecciona "Tarjeta + 3.18%"
2. ✅ Sistema crea pedido
3. ✅ Flow genera link de pago
4. ✅ Se envía link por WhatsApp al cliente
5. ✅ Cliente abre link y ve detalle del pedido
6. ✅ Cliente paga con tarjeta en Flow
7. ✅ Flow confirma y actualiza BD automáticamente

**¡Ya está todo listo!** Solo falta instalar el SDK y subir los archivos.
