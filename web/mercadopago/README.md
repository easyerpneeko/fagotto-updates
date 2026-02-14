# 💳 Mercado Pago Point Smart - Sistema de Pagos

Sistema integrado para enviar pagos al terminal Point Smart de Mercado Pago desde web.

## ⚙️ Configuraciones Disponibles

### Cuenta 1 - Fagotto
- Terminal: NEWLAND_N950__N950NCC302980808
- Store ID: 76216860
- Modo: PDV ✅

### Cuenta 2 - Env2
- Terminal 1: NEWLAND_N950__N950NCC302980807 (Store: 75998370)
- Terminal 2: PAX_A910__SMARTPOS1495485450 (Store: 73565262)
- Modo: PDV ✅

### Cuenta 3 - PVD
- Terminal: PAX_A910__SMARTPOS1495485450
- Store ID: 73565262
- S/N: SMARTPOS1495485450
- Modo: PDV ✅

### Cuenta 4 - Env4
- Terminal: NEWLAND_N950__N950NCC804178629
- Store ID: 77440880
- S/N: N950NCC804178629
- Modo: PDV ✅

## 🔄 Cambiar entre Terminales

```powershell
# Cuenta 1
Copy-Item .env.cuenta1 .env -Force

# Cuenta 2 (Env2)
Copy-Item .env.cuenta2 .env -Force

# Cuenta 3 (PVD)
Copy-Item .env.cuenta3 .env -Force

# Cuenta 4 (Env4)
Copy-Item .env.cuenta4 .env -Force

# Verificar cual está activa
cat .env | Select-String "MP_DEVICE_ID"
```

## ?? Archivos principales

### 1. enviar-pago-web.php
Interfaz web para crear y enviar pagos al terminal.
URL: http://localhost/mercadopago-point-example/enviar-pago-web.php

### 2. validar-pago.php
Consultar el estado de un pago en tiempo real con auto-refresh.
URL: http://localhost/mercadopago-point-example/validar-pago.php

### 3. webhook-point.php
Recibe notificaciones autom�ticas de Mercado Pago.

### 4. ver-mi-terminal.php
Consultar informaci�n del terminal vinculado.

## ?? Flujo de trabajo

1. Crear pago: enviar-pago-web.php ? Ingresar monto ? Copiar Order ID
2. Validar: validar-pago.php ? Pegar Order ID ? Activar auto-refresh
3. Completar: Pasar tarjeta en Point Smart ? Estado cambia a APROBADO

## ?? Estados de pago

- created - Esperando pago
- approved - Aprobado ?
- rejected - Rechazado ?
- cancelled - Cancelado ??

## ?? URLs Producci�n

- Enviar: https://fagottoerp.cl/mercadopago/enviar-pago-web.php
- Validar: https://fagottoerp.cl/mercadopago/validar-pago.php
- Webhook: https://fagottoerp.cl/mercadopago/webhook-point.php
