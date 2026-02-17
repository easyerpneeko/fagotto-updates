# 🚨 RECORDATORIO URGENTE - REVERTIR CAMBIO

## ⏰ CAMBIO TEMPORAL ACTIVO

**Fecha modificación**: 16 de febrero 2026  
**Modificado por**: Jimmy (pruebas PHPMailer)  
**Válido hasta**: HOY 23:00 hrs  
**Estado**: ✅ APLICADO (SUBIR AL SERVIDOR)

---

## 📍 ARCHIVOS MODIFICADOS

### **BACKEND**
**Servidor**: `/var/www/html/server/app/Http/Controllers/Controllers_local/RequestsController.php`  
**Línea**: 162 y 167  
**Método**: `storePedidoFinal()`

### **FRONTEND** 
**Local**: `src/renderer/views/pedidofinal.vue`  
**Líneas**: 1450, 1458, 1473, 1508  
**Métodos**: `validarHorarioPedidos()` y `actualizarCountdown()`

---

## ✅ CAMBIOS REALIZADOS

### **🧪 EXCEPCIÓN PERMANENTE (NO REVERTIR)**
Negocio ID 121 tiene excepción permanente para hacer pedidos 24/7 (local de pruebas de Jimmy).

**Backend línea 156-158:**
```php
$isTestLocal = ($appId == 121);
if (!$isTestLocal) { /* validaciones */ }
```

**Frontend línea 1406-1411:**
```javascript
const isTestLocal = this.app && this.app.Id === 121;
if (isTestLocal) { return true; }
```

### **⏰ CAMBIOS TEMPORALES (REVERTIR ANTES DE LAS 23:00)**

**BACKEND - RequestsController.php:**

Línea 162:
```php
// ANTES:
$horaFin = Carbon::createFromTime(13, 0, 0, 'America/Santiago');

// AHORA (TEMPORAL):
$horaFin = Carbon::createFromTime(23, 0, 0, 'America/Santiago'); // 🚨 TEMPORAL
```

Línea 167:
```php
// ANTES:
'message' => "⏰ Los pedidos solo están habilitados entre las 7:00 AM y las 1:00 PM.\n\n..."

// AHORA:
'message' => "⏰ Los pedidos solo están habilitados entre las 7:00 AM y las 11:00 PM (TEMPORAL).\n\n..."
```

**FRONTEND - pedidofinal.vue:**

Línea 1450:
```javascript
// ANTES:
const horaFin = 13;

// AHORA:
const horaFin = 23; // 🚨 TEMPORAL JIMMY TESTING - REVERTIR A 13
```

Línea 1458:
```javascript
// ANTES:
`⏰ Los pedidos solo están habilitados entre las 7:00 AM y las 1:00 PM.\n\n` +

// AHORA:
`⏰ Los pedidos solo están habilitados entre las 7:00 AM y las 11:00 PM (TEMPORAL).\n\n` +
```

Línea 1508:
```javascript
// ANTES:
const horaFin = 13;

// AHORA:
const horaFin = 23; // 🚨 TEMPORAL JIMMY TESTING - REVERTIR A 13
```

---

## 🔄 REVERTIR ANTES DE LAS 23:00

### **⚠️ BACKEND - RequestsController.php**

**Línea 162:**
```php
$horaFin = Carbon::createFromTime(13, 0, 0, 'America/Santiago');
```

**Línea 167:**
```php
'message' => "⏰ Los pedidos solo están habilitados entre las 7:00 AM y las 1:00 PM.\n\nHora actual: {$horaActualFormateada}\n\nPor favor, intenta nuevamente dentro del horario permitido."
```

### **⚠️ FRONTEND - pedidofinal.vue**

**Línea 1450:**
```javascript
const horaFin = 13;
```

**Línea 1458:**
```javascript
`⏰ Los pedidos solo están habilitados entre las 7:00 AM y las 1:00 PM.\n\n` +
```

**Línea 1508:**
```javascript
const horaFin = 13;
```

### **SERVIDOR BACKEND:**
- [ ] Subiste RequestsController.php al servidor
- [ ] Terminaste pruebas de PHPMailer
- [ ] Revertiste línea 162 a `13` en RequestsController.php
- [ ] Revertiste línea 167 al mensaje original "1:00 PM"
- [ ] Probaste que ya NO puedes hacer pedidos después de 1 PM (con otro negocio)

### **APP ELECTRON FRONTEND:**
- [ ] Compilaste la app con los cambios (`npm run build`)
- [ ] Revertiste línea 1450 a `const horaFin = 13;`
- [ ] Revertiste línea 1458 al mensaje "1:00 PM"
- [ ] Revertiste línea 1508 a `const horaFin = 13;`
- [ ] Desplegaste la nueva versión

### **FINAL:**
- [ ] Negocio 121 puede hacer pedidos 24/7 (OK, es permanente)
- [ ] Otros negocios solo pueden pedidos 7 AM -
## ⚠️ CONSECUENCIAS SI NO REVIERTES

Si dejas el horario en 23:00:
- ❌ Los locales podrán hacer pedidos hasta las 11 PM (fuera de horario operacional)
- ❌ Problemas con bodega/despacho (cierran a las 2 PM)
- ❌ Pedidos que no se pueden cumplir
- ❌ Incidentes con franquicias

---

## 📋 CHECKLIST ANTES DE IRTE HOY

- [ ] Subiste el archivo modificado al servidor
- [ ] Terminaste pruebas de PHPMailer
- [ ] Revertiste línea 159 a `13` en RequestsController.php
- [ ] Revertiste línea 164 al mensaje original "1:00 PM"
- [ ] Probaste que ya NO puedes hacer pedidos después de 1 PM
- [ ] Eliminaste este recordatorio

---

**HORA DE REVERTIR**: Antes de las 23:00 de hoy 16/02/2026

