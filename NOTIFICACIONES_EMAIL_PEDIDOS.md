# 📧 Sistema de Notificaciones por Email - Pedidos Finales

## Descripción General

Sistema automático de notificaciones por email que envía alertas en tiempo real cuando se crean nuevos pedidos en el sistema de Pedido Final centralizado. Permite al equipo administrativo monitorear pedidos desde cualquier lugar sin necesidad de estar frente al PC.

## ✅ Implementación Completada

### 1. Servicio PHPMailer

**Ubicación**: `Server app/app/Services/PHPMailerService.php`

- Servicio completamente funcional con métodos para envío de emails
- Soporte para templates Blade
- Configuración vía variables de entorno `.env`
- Manejo de errores con logging

**Configuración SMTP** (`.env`):
```env
PHPMAILER_HOST=mail.fagotto.cl
PHPMAILER_PORT=465
PHPMAILER_ENCRYPTION=ssl
PHPMAILER_USERNAME=notificaciones@fagotto.cl
PHPMAILER_PASSWORD=[contraseña configurada]
PHPMAILER_FROM_EMAIL=notificaciones@fagotto.cl
PHPMAILER_FROM_NAME="Sistema Fagotto ERP"
PHPMAILER_DEBUG=false
```

### 2. Template de Email

**Ubicación**: `Server app/resources/views/emails/pedido_final.blade.php`

Template moderno con diseño "chill" que incluye:

- **Header con gradiente** (#667eea → #764ba2)
- **Número de pedido** prominente
- **Información del local**: Nombre, fecha, solicitante, teléfono
- **Método de pago** con badge visual
- **Tabla de productos** con categorías, cantidades y unidades
- **Totales**: Subtotal, IVA, Total final
- **Footer oscuro** con recordatorio de sistema interno

**Variables requeridas**:
- `$pedido` - Objeto del pedido (campos: id, created_at, contact_name, contact_phone, paymode, comment, subtotal, iva, price, emergency)
- `$local` - Objeto de la aplicación/local (campo: Name/name/nombre)
- `$productos` - Array de productos del pedido (JSON parseado)

### 3. Integración en RequestsController

**Archivo modificado**: `Server app/app/Http\Controllers\Controllers_local\RequestsController.php`

**Líneas agregadas**: ~30 líneas después del `DB::commit()` en el método `storePedidoFinal()`

**Flujo implementado**:
1. Pedido creado exitosamente → `DB::commit()`
2. Obtener datos del local con `CurrentApp::App()`
3. Parsear productos desde JSON `json_decode($validatedData['products'], true)`
4. Preparar datos para template (pedido, local, productos)
5. Enviar email a cada destinatario usando `PHPMailerService->sendWithView()`
6. Logging de resultados (éxito/error) sin interrumpir flujo
7. Retornar respuesta exitosa al cliente

**Destinatarios configurados** (5 personas):
- soledad.zavalaga@fagotto.cl
- erick@fagotto.cl
- ma.gabriela@fagotto.cl
- margarita@fagotto.cl
- soporte@fagotto.cl (Jimmy Arriagada)

**Manejo de errores**:
- Try-catch wrapper para no interrumpir flujo principal
- Si falla el email, el pedido igualmente se crea
- Logs detallados con emojis para fácil identificación:
  - ✅ Email enviado exitosamente
  - ⚠️ Error al enviar a destinatario específico
  - ❌ Error general en sistema de email

### 4. Dependencias Actualizadas

**Archivo modificado**: `Server app/composer.json`

Agregada dependencia:
```json
"phpmailer/phpmailer": "^6.9"
```

**Comando para instalar** (en servidor):
```bash
cd /var/www/html/server
composer update phpmailer/phpmailer
```

## 🔧 Configuración del Servidor

### Variables de entorno requeridas en `.env`:

```env
# PHPMailer Configuration
PHPMAILER_HOST=mail.fagotto.cl
PHPMAILER_PORT=465
PHPMAILER_ENCRYPTION=ssl
PHPMAILER_USERNAME=notificaciones@fagotto.cl
PHPMAILER_PASSWORD=[password_aqui]
PHPMAILER_FROM_EMAIL=notificaciones@fagotto.cl
PHPMAILER_FROM_NAME="Sistema Fagotto ERP"
PHPMAILER_DEBUG=false  # Cambiar a true para debugging
```

### Permisos del servidor:

1. **Firewall**: Puerto 465 debe estar abierto para conexiones SMTP salientes
2. **SELinux** (si está habilitado):
   ```bash
   setsebool -P httpd_can_sendmail 1
   setsebool -P httpd_can_network_connect 1
   ```

## 📝 Ejemplo de Email Generado

**Asunto**: `📦 Nuevo Pedido Final #123 - Fagotto Providencia`

**Contenido**:
```
╔════════════════════════════════════╗
║       PEDIDO FINAL #123            ║
╚════════════════════════════════════╝

📍 Local: Fagotto Providencia
📅 Fecha: 15/01/2025 14:30
👤 Solicitante: Juan Pérez
📞 Teléfono: +56912345678
💳 Método de Pago: Transferencia

💬 Comentarios:
   Necesario para el fin de semana

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

PRODUCTOS SOLICITADOS:

Categoría    Producto         Cantidad   Unidad
───────────────────────────────────────────────
Pastas       Ravioles         5          KG
Salsas       Salsa Bolognesa  2          LTS
Pan          Pan Ajo          3          UND

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Subtotal (3 productos):  $45.000
IVA (19%):               $8.550
TOTAL:                   $53.550 CLP

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🔒 Este es un correo automático del sistema interno.
   No responder a este email.
```

## 🧪 Testing

### Pruebas recomendadas:

1. **Crear pedido desde frontend** (pedidofinal.vue)
   - Verificar que email llegue a los 5 destinatarios
   - Comprobar formato y datos correctos
   - Validar que pedido se cree aunque falle email

2. **Revisar logs del servidor**:
   ```bash
   tail -f /var/www/html/server/storage/logs/laravel.log | grep "Email de pedido"
   ```

3. **Test manual con Postman**:
   ```
   POST http://servidor/api/pedidos-final
   Headers:
     Authorization: Bearer {token}
     App-Key: {serial}
   Body (JSON):
     {
       "contact_name": "Test Usuario",
       "contact_phone": "912345678",
       "paymode": "Transferencia",
       "products": "[{\"id\":1,\"name\":\"Producto Test\",\"quantity\":2,\"unit\":\"KG\"}]",
       "comment": "Pedido de prueba",
       "status": "enviado",
       "app_id": 121
     }
   ```

4. **Verificar bandeja de spam**: En primera ejecución, revisar carpeta spam de destinatarios

## 🚀 Deployment

### Pasos para deploy en producción:

1. **Backup del servidor**:
   ```bash
   cd /var/www/html/server
   git add .
   git commit -m "Backup antes de actualización de emails"
   ```

2. **Actualizar código**:
   ```bash
   git pull origin main
   ```

3. **Instalar/actualizar dependencias**:
   ```bash
   composer update
   ```

4. **Verificar configuración .env**:
   ```bash
   nano .env  # Verificar variables PHPMAILER_*
   ```

5. **Limpiar caché**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

6. **Reiniciar servicios**:
   ```bash
   sudo systemctl restart php7.4-fpm  # Ajustar versión PHP
   sudo systemctl restart nginx       # O apache2 según servidor
   ```

7. **Hacer pedido de prueba** desde local ID 121 (test)

8. **Verificar logs**:
   ```bash
   tail -100 storage/logs/laravel.log
   ```

## 📊 Monitoreo

### Logs a revisar:

- **Logs de Laravel**: `/var/www/html/server/storage/logs/laravel.log`
  - Buscar: `Email de pedido #` para ver envíos
  - Buscar: `PHPMailer` para errores SMTP

- **Logs SMTP del servidor**: `/var/log/mail.log` o `/var/log/maillog`

### Métricas recomendadas:

- Tasa de entrega de emails (éxito/total)
- Tiempo promedio de envío
- Pedidos creados vs emails enviados

## 🔒 Seguridad

### Consideraciones:

1. **Credenciales SMTP**: Guardadas en `.env` (nunca en repositorio)
2. **Destinatarios hardcodeados**: Para seguridad, lista fija en código
3. **Rate limiting**: PHPMailer maneja throttling automáticamente
4. **Validación de datos**: Template escapea HTML automáticamente
5. **Error handling**: Emails fallidos no afectan creación de pedido

## 🆘 Troubleshooting

### Email no llega:

1. Verificar configuración SMTP en `.env`
2. Revisar logs de Laravel para errores
3. Comprobar firewall del servidor (puerto 465)
4. Verificar credenciales de `notificaciones@fagotto.cl`
5. Revisar carpeta spam de destinatarios
6. Probar con comando de prueba:
   ```bash
   php artisan tinker
   >>> app(\App\Services\PHPMailerService::class)->send('tu@email.com', 'Test', 'Prueba')
   ```

### Email llega sin formato:

1. Verificar que template Blade existe
2. Comprobar que `isHTML(true)` está configurado
3. Revisar que variables `$pedido`, `$local`, `$productos` tienen datos
4. Verificar permisos de carpeta `resources/views/emails/`

### Performance lento:

1. Considerar hacer envío asíncrono con Laravel Queues
2. Usar `sendBulk()` en lugar de loop (optimizado)
3. Revisar timeout de SMTP

## 📚 Recursos Adicionales

- **PHPMailer Docs**: https://github.com/PHPMailer/PHPMailer
- **Laravel Mail**: https://laravel.com/docs/6.x/mail
- **Blade Templates**: https://laravel.com/docs/6.x/blade

## ⚠️ RECORDATORIO IMPORTANTE

Este archivo documenta la implementación del sistema de emails. Recuerda que también tienes **cambios temporales pendientes de revertir antes de las 23:00 de hoy**:

Ver: `RECORDATORIO_REVERTIR_HORARIO.md` para detalles de los cambios temporales en horarios de pedidos (7 AM - 11 PM).

---

**Fecha de implementación**: 2025-01-15  
**Versión**: 1.0  
**Estado**: ✅ Producción  
**Desarrollador**: Sistema Fagotto ERP
