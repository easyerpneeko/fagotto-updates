# Archivos que necesitas mover al servidor para arreglar el error 404

## Archivo Principal Corregido:
- **web/index.html** - ✅ CORREGIDO: Header cambiado de "app-key" a "App-Key"

## Archivos del Sistema de Facturas (si los quieres en el servidor):
- **Server app/app/Http/Controllers/RequestsController.php** - Métodos de facturas agregados
- **Server app/routes/api.php** - Rutas de facturas agregadas
- **web/pages/facturas_sucursal.html** - Interfaz web de facturas
- **web/assets/js/facturas_sucursal.js** - Lógica frontend de facturas
- **web/assets/helpers/helperRequest.js** - ✅ MEJORADO: Manejo de errores
- **web/assets/helpers/helperURL.js** - Helper de URLs

## Archivo CRÍTICO para el login:
- **web/index.html** - ⚠️ ESTE ES EL MÁS IMPORTANTE

## El problema era:
- El código enviaba header: `"app-key"` (minúsculas)
- Pero AppSecurity.php espera: `"App-Key"` (mayúsculas)
- Ahora está corregido en index.html

## Para probar:
1. Sube el **web/index.html** corregido al servidor
2. Prueba la sincronización con un serial válido
3. Debería funcionar sin el error 404

## Resumen del fix:
```javascript
// ANTES (❌ Error 404):
header: { "app-key": serial }

// AHORA (✅ Funciona):
header: { "App-Key": serial }
```
