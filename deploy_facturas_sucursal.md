# 📋 GUÍA DE DEPLOYMENT - FUNCIONALIDAD FACTURAS POR SUCURSAL

## 🎯 **ARCHIVOS A SUBIR AL SERVIDOR**

### **1. BACKEND (Laravel) - fagottoerp.cl**

#### **Archivo modificado: RequestsController.php**
```
Ubicación servidor: /public_html/app/Http/Controllers/Controllers_local/RequestsController.php
Ubicación local: Server app/app/Http/Controllers/Controllers_local/RequestsController.php
```

**Métodos agregados al final del archivo:**
- `getFacturasSucursal($app_id, Request $request)` - Línea ~831
- `getFacturaSucursal($app_id, $factura_id)` - Línea ~924  
- `descargarFacturaPDF($app_id, $factura_id)` - Línea ~991
- `getEstadisticasFacturas($app_id, Request $request)` - Línea ~1055

#### **Archivo modificado: api.php**
```
Ubicación servidor: /public_html/routes/api.php
Ubicación local: Server app/routes/api.php
```

**Rutas agregadas (agregar al final del archivo dentro del grupo de rutas):**
```php
Route::get('/web/sucursal/{app_id}/facturas', 'Controllers_local\RequestsController@getFacturasSucursal');
Route::get('/web/sucursal/{app_id}/factura/{factura_id}', 'Controllers_local\RequestsController@getFacturaSucursal');
Route::get('/web/sucursal/{app_id}/factura/{factura_id}/pdf', 'Controllers_local\RequestsController@descargarFacturaPDF');
Route::get('/web/sucursal/{app_id}/estadisticas', 'Controllers_local\RequestsController@getEstadisticasFacturas');
```

---

### **2. FRONTEND (Web) - fagottoerp.cl/pages/**

#### **Archivo NUEVO: facturas_sucursal.html**
```
Ubicación servidor: /public_html/pages/facturas_sucursal.html
Ubicación local: web/pages/facturas_sucursal.html
```

#### **Archivo NUEVO: facturas_sucursal.js**
```
Ubicación servidor: /public_html/assets/js/facturas_sucursal.js
Ubicación local: web/assets/js/facturas_sucursal.js
```

#### **Archivo modificado: dashboard.html**
```
Ubicación servidor: /public_html/pages/dashboard.html
Ubicación local: web/pages/dashboard.html
```

**Modificación:** Agregar enlace en el dashboard (buscar línea ~1590):
```html
<a href="facturas_sucursal.html" class="btn btn-primary btn-sm">
    <i class="fas fa-file-invoice"></i> Ver Facturas
</a>
```

---

## 🚀 **COMANDOS DE DEPLOYMENT**

### **Opción 1: Via FTP/cPanel File Manager**
1. Subir `facturas_sucursal.html` a `/public_html/pages/`
2. Subir `facturas_sucursal.js` a `/public_html/assets/js/`
3. Reemplazar `RequestsController.php` en `/public_html/app/Http/Controllers/Controllers_local/`
4. Modificar `api.php` en `/public_html/routes/` agregando las 4 rutas
5. Modificar `dashboard.html` en `/public_html/pages/` agregando el enlace

### **Opción 2: Via SSH (si tienes acceso)**
```bash
# Subir archivos nuevos
scp web/pages/facturas_sucursal.html usuario@fagottoerp.cl:/public_html/pages/
scp web/assets/js/facturas_sucursal.js usuario@fagottoerp.cl:/public_html/assets/js/

# Subir archivos modificados (hacer backup primero)
scp "Server app/app/Http/Controllers/Controllers_local/RequestsController.php" usuario@fagottoerp.cl:/public_html/app/Http/Controllers/Controllers_local/
```

---

## ✅ **VERIFICACIÓN POST-DEPLOYMENT**

### **1. Verificar Backend**
- Probar endpoints:
  - `GET /api/web/sucursal/1/facturas`
  - `GET /api/web/sucursal/1/factura/123`
  - `GET /api/web/sucursal/1/factura/123/pdf`
  - `GET /api/web/sucursal/1/estadisticas`

### **2. Verificar Frontend**
- Acceder a: `https://fagottoerp.cl/pages/facturas_sucursal.html`
- Verificar que cargan las sucursales
- Probar selección de sucursal y carga de facturas
- Verificar descarga de PDFs

### **3. Verificar Dashboard**
- Acceder a: `https://fagottoerp.cl/pages/dashboard.html`
- Verificar que aparece el botón "Ver Facturas"
- Verificar que el enlace funciona correctamente

---

## 🔧 **SOLUCIÓN DE PROBLEMAS**

### **Error 404 - Archivo no encontrado**
- Verificar que el archivo está en la ubicación correcta
- Verificar permisos de archivo (644 para archivos, 755 para directorios)
- ✅ **CORREGIDO**: Removido core.js faltante del HTML
- ✅ **CORREGIDO**: Agregado favicon inline para evitar 404

### **Error JavaScript - Función no definida**
- Verificar que todos los helpers están incluidos en facturas_sucursal.html
- Verificar el orden de carga de scripts
- ✅ **CORREGIDO**: Agregados todos los helpers al HTML

### **Error 405 - Method Not Allowed**
- ✅ **CORREGIDO**: Cambiado de GET a POST para /getApps
- ✅ **CORREGIDO**: URL corregida de posfagotto.cl a fagottoerp.cl

### **Error 500 - Error del servidor**
- Verificar logs de Laravel en `/storage/logs/`
- Verificar que las rutas están bien definidas en api.php
- Verificar que la clase RequestsController se cargó correctamente

### **Problemas de conexión a base de datos**
- Verificar que ConectionDB helper está disponible
- Verificar permisos de usuario de base de datos
- Revisar logs de MySQL

---

## 📁 **ESTRUCTURA FINAL EN SERVIDOR**

```
/public_html/
├── pages/
│   ├── dashboard.html (modificado)
│   └── facturas_sucursal.html (nuevo)
├── assets/
│   └── js/
│       └── facturas_sucursal.js (nuevo)
├── app/
│   └── Http/
│       └── Controllers/
│           └── Controllers_local/
│               └── RequestsController.php (modificado)
└── routes/
    └── api.php (modificado)
```

---

## 🎉 **RESULTADO ESPERADO**

Una vez completado el deployment, tendrás:

1. **Nueva página web** para visualizar facturas por sucursal
2. **4 nuevos endpoints API** para gestionar facturas
3. **Enlace en dashboard** para acceso rápido
4. **Funcionalidades disponibles:**
   - Selección de sucursal
   - Lista paginada de facturas
   - Filtros por fecha y estado
   - Vista detallada de factura
   - Descarga de PDF
   - Estadísticas básicas

**URL de acceso:** `https://fagottoerp.cl/pages/facturas_sucursal.html`
