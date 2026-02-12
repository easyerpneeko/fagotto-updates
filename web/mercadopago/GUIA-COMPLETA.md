# 📖 GUÍA COMPLETA - MercadoPago Point Integration

## 🎯 OBJETIVO
Configurar terminales Point en modo PDV para recibir pagos desde tu sistema

---

## 📋 PASO 1: OBTENER CREDENCIALES

### 1.1 Entrar a MercadoPago Developers
```
https://www.mercadopago.com/developers/panel/credentials
```

### 1.2 Seleccionar "Producción"
⚠️ **IMPORTANTE**: Debe ser producción, NO pruebas

### 1.3 Copiar Credenciales
- ✅ **Access Token** (APP_USR-...)
- ✅ **Client ID** (número)
- ✅ **Client Secret** (string)
- ❌ Public Key (no se usa para Point)

**Ejemplo:**
```
Access Token: APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034
Client ID: 8228397783120956
Client Secret: nxDp3sCINQCR716fCShUmJpZlAspyI4d
```

---

## 📋 PASO 2: OBTENER DEVICE ID

### 2.1 Crear archivo `.env` con las credenciales
```env
MP_ACCESS_TOKEN=APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034
MP_DEVICE_ID=temporal
MP_CLIENT_ID=8228397783120956
MP_CLIENT_SECRET=nxDp3sCINQCR716fCShUmJpZlAspyI4d
MP_REDIRECT_URI=https://tu-dominio.com/oauth-callback.php
WEBHOOK_URL=https://tu-dominio.com/webhook.php
MP_ENVIRONMENT=production
```

### 2.2 Ver tus terminales vinculados
```powershell
php ver-mi-terminal.php
```

### 2.3 Copiar el TERMINAL ID que aparece
**Ejemplos:**
```
NEWLAND_N950__N950NCC302980807
NEWLAND_N950__N950NCC302980808
PAX_A910__SMARTPOS1495485450
```

### 2.4 Actualizar el `.env` con el Device ID correcto
```env
MP_DEVICE_ID=NEWLAND_N950__N950NCC302980807
```

---

## 📋 PASO 3: CAMBIAR TERMINAL A MODO PDV

### 3.1 Verificar el modo actual del terminal
```powershell
php ver-mi-terminal.php
```

Buscar la línea: **"⚙️ MODO DE OPERACIÓN"**
- Si dice **STANDALONE** → Necesitas cambiarlo
- Si dice **PDV** → Ya está listo ✅

### 3.2 Cambiar de STANDALONE a PDV
```powershell
php cambiar-modo-pdv.php
```

**Salida esperada:**
```
✅ ¡ÉXITO! Terminal cambiado a modo PDV
🆔 Terminal ID: NEWLAND_N950__N950NCC302980807
⚙️ Modo: PDV
```

### 3.3 Verificar que el cambio se aplicó
```powershell
php ver-mi-terminal.php
```

Ahora debe decir: **"⚙️ MODO DE OPERACIÓN: PDV"** ✅

---

## 📋 PASO 4: PROBAR EL TERMINAL

### 4.1 Enviar un pago de prueba
```powershell
php enviar-pago.php 1000
```

**Salida esperada:**
```
✅ ¡PAGO ENVIADO EXITOSAMENTE!
🆔 Order ID: ORD01KH7QNFD271XGYYR1C2VEG6DY
💰 Monto: $1000 CLP
🖨️ El cobro YA DEBE ESTAR EN EL TERMINAL
```

### 4.2 Verificar en el terminal físico
- Debe aparecer el monto: **$1.000 CLP**
- Pantalla esperando tarjeta 💳

### 4.3 Opciones
- **Cancelar**: Presiona X o botón de cancelar en el terminal
- **Completar**: Pasa una tarjeta de prueba/real
- **Ver estado**: `php validar-pago.php [ORDER_ID]`

---

## 📋 PASO 5: MÚLTIPLES TERMINALES

### 5.1 Si tienes varios terminales, crear archivos separados

**`.env.cuenta1`** (Máquina 1)
```env
# === CUENTA 1 - FAGOTTO ===
# Terminal: NEWLAND_N950__N950NCC302980808

MP_ACCESS_TOKEN=APP_USR-6283355973944660-111310-a9bb1118923d4beb4d7574d3f057abd9-67282805
MP_DEVICE_ID=NEWLAND_N950__N950NCC302980808

MP_CLIENT_ID=6283355973944660
MP_CLIENT_SECRET=f4AEk80wBgiUUcwvv4EJTgofXVR0Ls3e
MP_REDIRECT_URI=https://fagottoerp.cl/mercadopago/oauth-callback.php

WEBHOOK_URL=https://fagottoerp.cl/mercadopago/webhook.php
MP_ENVIRONMENT=production
```

**`.env.cuenta2`** (Máquina 2)
```env
# === CUENTA 2 ===
# Terminal 1: NEWLAND_N950__N950NCC302980807
# Terminal 2: PAX_A910__SMARTPOS1495485450

MP_ACCESS_TOKEN=APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034
MP_DEVICE_ID=NEWLAND_N950__N950NCC302980807

MP_CLIENT_ID=8228397783120956
MP_CLIENT_SECRET=nxDp3sCINQCR716fCShUmJpZlAspyI4d
MP_REDIRECT_URI=https://fagottoerp.cl/mercadopago/oauth-callback.php

WEBHOOK_URL=https://fagottoerp.cl/mercadopago/webhook.php
MP_ENVIRONMENT=production
```

### 5.2 Cambiar entre terminales
```powershell
# Usar Máquina 1
Copy-Item .env.cuenta1 .env -Force

# Usar Máquina 2
Copy-Item .env.cuenta2 .env -Force

# Verificar cuál está activa
cat .env | Select-String "MP_DEVICE_ID"
```

---

## 📁 ESTRUCTURA DE ARCHIVOS

```
mercadopago/
├── .env                    # ⚙️ Configuración activa (la que se usa)
├── .env.cuenta1           # 💾 Credenciales cuenta 1 (backup)
├── .env.cuenta2           # 💾 Credenciales cuenta 2 (backup)
├── config.php             # 🔧 Carga variables de .env
├── composer.json          # 📦 Dependencias PHP
├── ver-mi-terminal.php    # 🔍 Ver todos los terminales
├── cambiar-modo-pdv.php   # ⚙️ Cambiar terminal a modo PDV
├── enviar-pago.php        # 💳 Enviar cobro rápido
├── enviar-pago-web.php    # 🌐 Interfaz web para enviar pagos
├── validar-pago.php       # 📊 Ver estado de un pago
├── limpiar-terminal.php   # 🧹 Cancelar pago pendiente
├── webhook-point.php      # 📡 Recibir notificaciones
├── storage/               # 📂 Logs y datos
│   ├── logs/
│   ├── tokens/
│   └── notifications/
└── vendor/                # 📚 Librerías MercadoPago SDK
```

---

## 🛠️ COMANDOS PRINCIPALES

### Ver información de terminales
```powershell
php ver-mi-terminal.php
```

### Cambiar terminal a modo PDV
```powershell
php cambiar-modo-pdv.php
```

### Enviar pago desde terminal
```powershell
# Monto por defecto ($1000)
php enviar-pago.php

# Monto personalizado
php enviar-pago.php 5000
```

### Validar estado de un pago
```powershell
php validar-pago.php [ORDER_ID]
```

### Cancelar pago pendiente
```powershell
php limpiar-terminal.php
```

### Interfaz web
```powershell
# Iniciar servidor PHP
php -S localhost:8000

# Abrir en navegador
http://localhost:8000/enviar-pago-web.php
```

---

## ✅ CHECKLIST DE CONFIGURACIÓN

### Para cada terminal nuevo:

- [ ] 1. Obtener Access Token de MercadoPago (Producción)
- [ ] 2. Ejecutar `php ver-mi-terminal.php` para ver Device ID
- [ ] 3. Copiar el Device ID del terminal (ej: NEWLAND_N950__...)
- [ ] 4. Crear/Actualizar `.env` con Access Token y Device ID
- [ ] 5. Verificar modo actual: `php ver-mi-terminal.php`
- [ ] 6. Si está en STANDALONE, ejecutar: `php cambiar-modo-pdv.php`
- [ ] 7. Verificar que cambió a PDV: `php ver-mi-terminal.php`
- [ ] 8. Probar con: `php enviar-pago.php 1000`
- [ ] 9. Confirmar que aparece el monto en el terminal físico
- [ ] 10. ¡Listo para usar! 🚀

---

## 🔍 SOLUCIÓN DE PROBLEMAS

### ❌ Error: "Ya hay un pago pendiente"
**Solución:**
1. Cancelar en el terminal físico (botón X)
2. O esperar 5-10 minutos (expira automáticamente)
3. O ejecutar: `php limpiar-terminal.php`

### ❌ Error: "site id is not valid"
**Causa:** Endpoint incorrecto o token inválido
**Solución:**
1. Verificar que el Access Token sea de **Producción**
2. Verificar que no haya espacios extras en el `.env`

### ❌ El pago no llega al terminal
**Verificar:**
1. Terminal está en modo **PDV** (no STANDALONE)
2. Terminal está encendido y conectado
3. Device ID es correcto en el `.env`
4. Access Token es de la cuenta correcta

### ❌ Error al cambiar a PDV
**Requisitos:**
- Solo funciona con **NEWLAND_N950** y **PAX_A910**
- Terminal debe estar vinculado a una Store y POS
- Solo puede haber 1 terminal en PDV por POS

---

## 🔐 CREDENCIALES DE TUS CUENTAS

### CUENTA 1 (Fagotto - Máquina ...808)
```
Access Token: APP_USR-6283355973944660-111310-a9bb1118923d4beb4d7574d3f057abd9-67282805
Client ID: 6283355973944660
Client Secret: f4AEk80wBgiUUcwvv4EJTgofXVR0Ls3e
Device ID: NEWLAND_N950__N950NCC302980808
Serial: N950NCC302980808
```

### CUENTA 2 (Máquinas ...807 y PAX)
```
Access Token: APP_USR-8228397783120956-021115-7c76e279e9d0f1afd0960348ab7fa65b-2039372034
Client ID: 8228397783120956
Client Secret: nxDp3sCINQCR716fCShUmJpZlAspyI4d

Terminal 1:
  Device ID: NEWLAND_N950__N950NCC302980807
  Serial: N950NCC302980807
  
Terminal 2:
  Device ID: PAX_A910__SMARTPOS1495485450
```

---

## 📚 ENDPOINT DE LA API

### Cambiar modo de terminal
```
PATCH https://api.mercadopago.com/terminals/v1/setup

Headers:
  Authorization: Bearer {ACCESS_TOKEN}
  Content-Type: application/json

Body:
{
  "terminals": [
    {
      "id": "NEWLAND_N950__N950NCC302980807",
      "operating_mode": "PDV"
    }
  ]
}
```

### Enviar pago
```
POST https://api.mercadopago.com/v1/orders

Headers:
  Authorization: Bearer {ACCESS_TOKEN}
  Content-Type: application/json
  X-Idempotency-Key: {UNIQUE_KEY}

Body:
{
  "type": "point",
  "external_reference": "REF-123",
  "description": "Venta $1000",
  "transactions": {
    "payments": [
      {
        "amount": "1000"
      }
    ]
  },
  "config": {
    "point": {
      "terminal_id": "NEWLAND_N950__N950NCC302980807",
      "print_on_terminal": "seller_ticket"
    }
  }
}
```

---

## 🎉 ¡TODO LISTO!

Con esta guía puedes:
- ✅ Configurar nuevos terminales
- ✅ Cambiarlos a modo PDV
- ✅ Enviar pagos desde tu sistema
- ✅ Gestionar múltiples terminales
- ✅ Resolver problemas comunes

**¡A cobrar!** 🚀💳
