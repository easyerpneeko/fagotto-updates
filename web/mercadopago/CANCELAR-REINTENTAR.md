# 🔄 CANCELAR Y REINTENTAR PAGOS - MercadoPago Point

## 📋 ESCENARIOS CUANDO EL CLIENTE SE EQUIVOCA

### 1️⃣ ANTES DE PASAR LA TARJETA (Estado: `created` o `at_terminal`)

**Problema:** Se envió un monto incorrecto y el cobro está esperando en el terminal.

**Soluciones:**

#### ✅ Opción A: Cancelar en Terminal Físico
```
1. En el terminal NEWLAND/PAX
2. Presionar botón "X", "Cancelar" o "Volver"
3. El pago cambia a estado: canceled
4. Enviar nuevo pago: php enviar-pago.php [MONTO_CORRECTO]
```

#### ✅ Opción B: Esperar Timeout (5-10 minutos)
```
1. No hacer nada
2. El pago expira automáticamente
3. Estado cambia a: canceled o expired
4. Enviar nuevo pago
```

#### ❌ NO Funciona en Chile:
```
DELETE /point/integration-api/devices/{deviceId}/payment-intents/{orderId}
→ Respuesta: 403 - "site id is not valid"

La API de Point Integration NO está disponible para Chile
```

---

### 2️⃣ DESPUÉS DE PASAR LA TARJETA - APROBADO (Estado: `approved`)

**Problema:** El pago ya fue procesado con un monto incorrecto.

**Solución: REEMBOLSO**

#### ✅ Reembolso Total
```bash
php reembolsar-pago.php [PAYMENT_ID]
```

**Endpoint:**
```
POST https://api.mercadopago.com/v1/payments/{PAYMENT_ID}/refunds
Authorization: Bearer {ACCESS_TOKEN}
Body: (vacío para reembolso total)
```

#### ✅ Reembolso Parcial
```bash
php reembolsar-pago.php [PAYMENT_ID] 500
```

**Endpoint:**
```
POST https://api.mercadopago.com/v1/payments/{PAYMENT_ID}/refunds
Authorization: Bearer {ACCESS_TOKEN}
Body: {"amount": 500}
```

**Tiempos:**
- ⏱️ Procesamiento: Inmediato (API responde OK)
- 💳 Devolución al cliente: 5-10 días hábiles

**Luego:**
```bash
php enviar-pago.php [MONTO_CORRECTO]
```

---

### 3️⃣ DESPUÉS DE PASAR LA TARJETA - RECHAZADO (Estado: `rejected`)

**Problema:** El pago fue rechazado por el banco/procesador.

**Solución: ENVIAR NUEVO PAGO**

```bash
php enviar-pago.php [MONTO]
```

**Motivos comunes de rechazo:**

| Status Detail | Significado |
|--------------|-------------|
| `cc_rejected_insufficient_amount` | Fondos insuficientes |
| `cc_rejected_bad_filled_card_number` | Número de tarjeta inválido |
| `cc_rejected_bad_filled_security_code` | CVV incorrecto |
| `cc_rejected_call_for_authorize` | Banco rechazó la transacción |
| `cc_rejected_card_disabled` | Tarjeta bloqueada/deshabilitada |

**El terminal queda libre automáticamente** después de un rechazo.

---

## 🔧 COMANDOS ÚTILES

### Verificar Estado y Opciones
```bash
php verificar-opciones-pago.php [ORDER_ID]
```

### Consultar Estado de un Pago
```bash
php consultar-estado-cli.php [ORDER_ID]
```

### Reembolsar un Pago Aprobado
```bash
# Reembolso total
php reembolsar-pago.php [PAYMENT_ID]

# Reembolso parcial
php reembolsar-pago.php [PAYMENT_ID] 500
```

### Cambiar entre Cuentas
```bash
# Cuenta 1
Copy-Item .env.cuenta1 .env -Force

# Cuenta 4
Copy-Item .env.cuenta4 .env -Force
```

---

## 📊 ESTADOS DE PAGO

| Estado | Descripción | Acción |
|--------|-------------|--------|
| `created` | Pago creado, aún no enviado al terminal | Cancelar en terminal |
| `at_terminal` | Esperando que el cliente pase la tarjeta | Cancelar en terminal |
| `approved` | Pago exitoso | Reembolsar vía API |
| `rejected` | Pago rechazado | Enviar nuevo pago |
| `canceled` | Cancelado por usuario/timeout | Enviar nuevo pago |
| `expired` | Expiró por inactividad | Enviar nuevo pago |

---

## 🚨 LIMITACIONES EN CHILE

### ❌ NO Funciona:
```
DELETE /point/integration-api/devices/{deviceId}/payment-intents/{orderId}
GET /point/integration-api/devices/{deviceId}
GET /point/integration-api/devices
```

**Error:** `403 - "site id is not valid"`

### ✅ SÍ Funciona:
```
GET /v1/orders/{orderId} (consultar estado)
POST /v1/payments/{paymentId}/refunds (reembolsos)
GET /terminals/devices (listar terminales)
PATCH /terminals/v1/setup (cambiar modo PDV)
GET /pos/{posId} (info del POS)
PUT /pos/{posId} (actualizar POS)
```

---

## 💡 MEJORES PRÁCTICAS

1. **Validar monto antes de enviar** → Confirmar con el cliente
2. **Si hay error, cancelar rápido** → Usar botón X del terminal
3. **Si ya pasó tarjeta (approved)** → Reembolsar vía API
4. **Guardar PAYMENT_ID** → Necesario para reembolsos
5. **Informar tiempos al cliente** → Reembolsos toman 5-10 días

---

## 📞 FLUJO RECOMENDADO

```
Enviar Pago → Cliente espera → ¿Monto correcto?
                                   ↓
                              ┌────NO───┐         ┌────SÍ────┐
                              ↓          ↓         ↓           ↓
                    ¿Pasó tarjeta?  [Cancelar]  [Pasar]  [Aprobado]
                         ↓                        tarjeta      ↓
                    ┌────NO───┐                              ↓
                    ↓          ↓                         [Reembolso]
            [Cancelar terminal] ← ← ← ← ← ← ← ← ← ← ←  [Nuevo pago]
                    ↓
            [Nuevo pago correcto]
```

---

## 🔑 PAYMENT_ID vs ORDER_ID

- **ORDER_ID**: Identificador de la orden (`ORD01...`)
  - Usado para consultar estado
  - Ejemplo: `ORD01KHCY3MR3WNK36J7SM6GA9WK1`

- **PAYMENT_ID**: Identificador del pago procesado (`PAY01...`)
  - Usado para reembolsos
  - Obtenido de: `transactions.payments[0].id`
  - Ejemplo: `PAY01KHCY3MR3WNK36J7SM9PKGZC6`

Para obtener PAYMENT_ID:
```bash
php consultar-estado-cli.php [ORDER_ID]
# Buscar: "Payment ID: PAY01..."
```

---

## ✅ RESUMEN RÁPIDO

| Situación | Acción |
|-----------|--------|
| Monto incorrecto, tarjeta NO pasada | Cancelar en terminal (botón X) |
| Monto incorrecto, tarjeta SÍ pasada (aprobado) | Reembolsar vía API + Nuevo pago |
| Pago rechazado | Nuevo pago (terminal libre automático) |
| Timeout/expirado | Nuevo pago (terminal libre automático) |
