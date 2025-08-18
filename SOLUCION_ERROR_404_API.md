# 🔧 SOLUCIÓN PARA ERROR 404 API

## 🎯 **PROBLEMA ACTUAL:**
```
POST https://fagottoerp.cl/api/getApps 404 (Not Found)
```

## 🔍 **ANÁLISIS DEL PROBLEMA:**

### **1. Headers Corregidos** ✅
- Se cambió de `app-key` a `App-Key` (el middleware espera mayúsculas)
- Se agregó validación de credenciales
- Se mejoró el manejo de errores

### **2. Problema Principal** ❌
**El endpoint `/api/getApps` no está accesible desde la web**

## 🛠️ **SOLUCIONES POSIBLES:**

### **OPCIÓN A: Verificar Credenciales (MÁS PROBABLE)**

El problema puede ser que no hay credenciales guardadas en el navegador.

**Para solucionarlo:**

1. **Acceder primero al dashboard principal:**
   ```
   https://fagottoerp.cl/pages/dashboard.html
   ```

2. **Hacer login correctamente** en el dashboard

3. **Luego acceder a facturas:**
   ```
   https://fagottoerp.cl/pages/facturas_sucursal.html
   ```

**El dashboard guarda las credenciales necesarias en localStorage.**

---

### **OPCIÓN B: Mover Rutas de Facturas (TÉCNICA)**

Las rutas nuevas están dentro del middleware `Config:modulos.pedidos` que puede estar bloqueando el acceso.

**Modificar `Server app/routes/api.php`:**

```php
// MOVER ESTAS LÍNEAS (524-527):
Route::get('/web/sucursal/{app_id}/facturas', 'Controllers_local\RequestsController@getFacturasSucursal');
Route::get('/web/sucursal/{app_id}/factura/{factura_id}', 'Controllers_local\RequestsController@getFacturaSucursal');
Route::get('/web/sucursal/{app_id}/factura/{factura_id}/pdf', 'Controllers_local\RequestsController@descargarFacturaPDF');
Route::get('/web/sucursal/{app_id}/estadisticas', 'Controllers_local\RequestsController@getEstadisticasFacturas');

// DESDE: Dentro del grupo Config:modulos.pedidos (línea ~500)
// HACIA: Dentro del grupo AppSecurity principal (línea ~162, después de Route::post('/getApps'))
```

**Ubicación correcta en api.php:**
```php
Route::group(['middleware' => ['AppSecurity']], function () {

  Route::get('/getApp', 'AplicationController@getApp');
  Route::post('/getApps', 'AplicationController@getApps');
  
  // ✅ AGREGAR AQUÍ LAS RUTAS NUEVAS:
  Route::get('/web/sucursal/{app_id}/facturas', 'Controllers_local\RequestsController@getFacturasSucursal');
  Route::get('/web/sucursal/{app_id}/factura/{factura_id}', 'Controllers_local\RequestsController@getFacturaSucursal');
  Route::get('/web/sucursal/{app_id}/factura/{factura_id}/pdf', 'Controllers_local\RequestsController@descargarFacturaPDF');
  Route::get('/web/sucursal/{app_id}/estadisticas', 'Controllers_local\RequestsController@getEstadisticasFacturas');

  // resto de rutas...
```

---

### **OPCIÓN C: Usar Endpoint Alternativo (RÁPIDA)**

Si `/getApps` no funciona, usar otro endpoint que sí funcione.

**Cambiar en `facturas_sucursal.js`:**
```javascript
// EN LUGAR DE:
const response = await fetch(generarURLApi('/getApps'), {

// USAR:
const response = await fetch(generarURLApi('/web/getApp'), {
    method: 'GET',  // Cambiar a GET
    headers: getApiHeaders()
    // Sin body para GET
});
```

---

## 🚀 **RECOMENDACIÓN: PROBAR EN ORDEN**

### **1. PRIMERO - Verificar Credenciales:**
1. Ir a `https://fagottoerp.cl/pages/dashboard.html`
2. Hacer login si es necesario
3. Abrir consola del navegador (F12)
4. Verificar que hay datos en localStorage:
   ```javascript
   console.log('App Key:', localStorage.getItem('912338BA'));
   console.log('Auth Token:', localStorage.getItem('HAS72J29S'));
   ```
5. Si hay datos, probar facturas_sucursal.html

### **2. SI NO FUNCIONA - Implementar Opción B:**
- Mover las rutas nuevas al grupo AppSecurity principal
- Actualizar api.php en servidor

### **3. SI AÚN NO FUNCIONA - Implementar Opción C:**
- Cambiar endpoint a uno que funcione
- Actualizar facturas_sucursal.js

---

## 📁 **ARCHIVOS A ACTUALIZAR:**

### **Para Opción A (Solo verificar):**
- Ninguno, solo verificar proceso de login

### **Para Opción B:**
- `Server app/routes/api.php` (mover rutas)

### **Para Opción C:**
- `web/assets/js/facturas_sucursal.js` (cambiar endpoint)

---

## ✅ **VERIFICACIÓN:**

Después de implementar cualquier opción, verificar en consola del navegador:
- Sin errores 404
- Respuesta JSON con lista de sucursales
- Dropdown poblado con opciones

---

## 🎯 **SOLUCIÓN MÁS PROBABLE:**

**El usuario no ha hecho login correctamente**, por lo que no tiene las credenciales necesarias en localStorage.

**Solución: Acceder primero al dashboard y hacer login, luego usar facturas_sucursal.html**
