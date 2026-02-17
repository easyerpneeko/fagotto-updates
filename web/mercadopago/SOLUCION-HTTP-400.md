# 🎯 SOLUCION ENCONTRADA - HTTP 400 MercadoPago

## El Problema

MercadoPago API estaba rechazando el campo `external_store_id` con error:

```json
{
  "code": "unsupported_properties",
  "message": "Properties not supported",
  "details": ["additionalProperties '$.external_store_id' not allowed"]
}
```

## ✅ Corrección Aplicada

Se eliminó el campo `external_store_id` del payload en `api-enviar-pago.php`. 

El `site_id` se determina automáticamente por MercadoPago basándose en el `device_id` configurado en cada cuenta.

## 📝 Pasos para Aplicar la Solución

### 1. Subir archivo corregido

Sube el archivo **api-enviar-pago.php** corregido a:
```
fagottoerp.cl/mercadopago/api-enviar-pago.php
```

### 2. Arreglar permisos de logs

Conéctate por SSH al servidor y ejecuta:

```bash
cd /var/www/html/mercadopago
chmod +x fix-permisos.sh
./fix-permisos.sh
```

O manualmente:
```bash
cd /var/www/html/mercadopago
mkdir -p storage/logs
chmod 755 storage storage/logs
chown -R www-data:www-data storage/logs
```

### 3. Probar el pago

1. Abre la aplicación en Las Condes (app_id 116)
2. Agrega un producto al carrito
3. Selecciona "Pago con Tarjeta"
4. Elige Débito o Crédito
5. El terminal debería recibir el pago

### 4. Verificar logs

Después de hacer una prueba, revisa los logs en:
```
https://fagottoerp.cl/mercadopago/ver-logs.php
```

Deberían aparecer logs con:
- Payload enviado
- Respuesta de MercadoPago con order_id
- HTTP 201 (éxito)

## 🔍 Si Aún Hay Problemas

Ejecuta el diagnóstico nuevamente:
```
https://fagottoerp.cl/mercadopago/test-completo-desde-laravel.php
```

Debería mostrar HTTP 201 en lugar de HTTP 400.

## ⚠️ Problema Secundario: Impresión

El usuario reportó que dejó de funcionar la impresión en todos los locales. Este es un problema SEPARADO de MercadoPago y debe investigarse:

- Revisar si se modificaron archivos de impresión (IMNodePrinter.js, settings_printer en aplication.json)
- Verificar logs de Electron en cada local
- Probar impresión en negocio de pruebas primero

---

**Fecha de solución:** 2026-02-16  
**Terminal corregida:** Las Condes (app_id 116, Terminal 807)  
**Error original:** HTTP 400 - external_store_id not allowed
