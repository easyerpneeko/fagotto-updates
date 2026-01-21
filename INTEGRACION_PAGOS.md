# 🎯 Integración Dual de Pagos: Linkify + Flow.cl

## ✅ Implementación Completada

Se han implementado **2 métodos de pago** en el sistema:

### 1. Transferencia Bancaria (Linkify) - GRATIS
- Sin comisión adicional
- Pago mediante transferencia bancaria
- Link enviado por WhatsApp

### 2. Tarjeta Crédito/Débito (Flow.cl) + 3.18%
- Pago inmediato con tarjeta
- Comisión de 3.18% sobre el total
- Procesado a través de Flow.cl

---

## 📋 Pasos para Completar la Integración

### 1. Instalar SDK de Flow en el Servidor

```bash
cd "Server app"
composer require flow-cl/flow-api
```

### 2. Configurar Credenciales de Flow

Obtener credenciales desde: https://dashboard.flow.cl/private/dashboard/

Agregar al archivo `.env`:

```env
# Flow.cl Credentials
FLOW_API_KEY=tu_api_key_aqui
FLOW_SECRET_KEY=tu_secret_key_aqui
FLOW_API_URL=https://sandbox.flow.cl/api
```

**Para producción:**
```env
FLOW_API_URL=https://www.flow.cl/api
```

### 3. Configurar Webhooks en Flow Dashboard

En Flow Dashboard → Integración → Webhooks:

- **URL de Confirmación**: `https://posfagotto.cl/api/local/flow/confirm`
- **URL de Retorno**: `https://posfagotto.cl/api/local/flow/return`

### 4. Subir Archivos al Servidor

Archivos modificados/creados:

**Backend:**
- `Server app/app/Http/Controllers/Controllers_local/FlowController.php` ✅ NUEVO
- `Server app/routes/api.php` ✅ MODIFICADO
- `Server app/.env` ⚠️ AGREGAR CREDENCIALES

**Frontend:**
- `src/renderer/views/pedidofinal.vue` ✅ MODIFICADO

### 5. Probar la Integración

#### Modo Sandbox (Pruebas):
1. Usar `FLOW_API_URL=https://sandbox.flow.cl/api`
2. Tarjetas de prueba: https://www.flow.cl/docs/testing

#### Modo Producción:
1. Cambiar a `FLOW_API_URL=https://www.flow.cl/api`
2. Cuenta Flow aprobada y verificada

---

## 🎨 Características Implementadas

### Modal de Selección de Pago
- ✅ 2 opciones visuales con tarjetas seleccionables
- ✅ Cálculo automático de comisión Flow (3.18%)
- ✅ Totales dinámicos según método seleccionado
- ✅ Validación de selección antes de procesar

### Backend FlowController
- ✅ Crear pagos en Flow
- ✅ Webhook para confirmación automática
- ✅ Actualización automática de BD al confirmar pago
- ✅ Página de retorno después del pago
- ✅ Logs detallados para debugging

### Frontend (pedidofinal.vue)
- ✅ Variable `metodoPagoSeleccionado` ('linkify' o 'flow')
- ✅ Variable `comisionFlow` (3.18%)
- ✅ Computed `totalConComisionFlow` (calcula el total con comisión)
- ✅ Método `procesarPagoLinkify()` (proceso original)
- ✅ Método `procesarPagoFlow()` (nuevo proceso Flow)
- ✅ Integración con WhatsApp automático en ambos casos

---

## 🔍 Flujo de Pago

### Opción 1: Transferencia (Linkify)
1. Usuario selecciona "Transferencia Bancaria"
2. Ingresa WhatsApp
3. Sistema crea pedido con `status_payment='impagado'`
4. Genera URL Linkify
5. Envía WhatsApp con link
6. Usuario transfiere
7. Webhook Linkify confirma y actualiza BD

### Opción 2: Tarjeta (Flow)
1. Usuario selecciona "Tarjeta Crédito/Débito"
2. Ve total + 3.18% de comisión
3. Ingresa WhatsApp
4. Sistema crea pedido con `status_payment='impagado'`
5. Crea orden en Flow con monto + comisión
6. Envía WhatsApp con link Flow
7. Abre Flow en navegador
8. Usuario paga con tarjeta
9. Webhook Flow confirma y actualiza BD a `status_payment='pagado'`

---

## 🚀 Testing

### Verificar Logs
```bash
tail -f storage/logs/laravel.log
```

Buscar:
- `💳 Creando pago Flow`
- `✅ Pago Flow creado`
- `🔔 Webhook Flow confirm recibido`
- `✅ Pago Flow confirmado y BD actualizada`

### Verificar en Flow Dashboard
https://dashboard.flow.cl/private/dashboard/
- Ver pagos creados
- Estado de transacciones
- Webhooks recibidos

---

## ⚠️ Recordatorios

1. **WhatsApp (Twilio)**
   - Actualmente usa tu número: `+18582953672`
   - Estado: "Offline" - esperando aprobación Meta (15 días)
   - Alternativa temporal: Sandbox `+14155238886` (requiere "join")

2. **Flow Comisión**
   - 3.18% configurable en `pedidofinal.vue` → `comisionFlow: 3.18`
   - Ajustar según tu acuerdo con Flow

3. **Producción**
   - Cambiar `FLOW_API_URL` a producción
   - Verificar que cuenta Flow esté aprobada
   - Probar ambos métodos de pago

---

## 📞 Soporte

¿Problemas?
1. Revisar logs: `storage/logs/laravel.log`
2. Verificar credenciales en `.env`
3. Verificar webhooks configurados en Flow Dashboard
4. Verificar que el SDK de Flow esté instalado: `composer show flow-cl/flow-api`

---

¡Listo para recibir pagos! 🎉
