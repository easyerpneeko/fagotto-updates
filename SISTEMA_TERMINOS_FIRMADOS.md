# Sistema de Términos y Condiciones con Firma Digital

## 📋 Descripción

Sistema de registro de términos y condiciones laborales con firma digital para el control de asistencia biométrica de Fagotto.

## ✨ Características

- ✅ Formulario de registro con validación de RUT chileno
- ✅ Campo de correo electrónico obligatorio
- ✅ Términos y condiciones completos según normativa DT (Ley N°19.628)
- ✅ Firma digital con canvas HTML5 (táctil y mouse)
- ✅ Envío automático de términos firmados por correo
- ✅ Almacenamiento seguro de firmas digitales
- ✅ Validación de email con regex
- ✅ Links de un solo uso (one-time tokens)

## 🔄 Flujo del Proceso

```
1. Admin genera QR/link para empleado
   ↓
2. Empleado escanea QR o abre link
   ↓
3. Completa formulario:
   - Nombre completo
   - RUT (con validación)
   - Email corporativo/personal
   - Cargo (Cajero/Cocina/Jefe de Tienda)
   - Acepta términos ☑️
   ↓
4. Captura su rostro con la cámara
   ↓
5. Confirma la foto
   ↓
6. **NUEVO: Dibuja su firma digital**
   ↓
7. Sistema procesa:
   - Indexa rostro en AWS Rekognition
   - Guarda firma PNG en servidor
   - Genera HTML con términos completos
   - Envía correo con documento firmado
   - Marca token como usado
   ↓
8. Empleado recibe correo con:
   - Términos y condiciones completos
   - Sus datos personales
   - Su firma digital
   - Fecha de aceptación
```

## 📁 Archivos del Sistema

### Frontend
- `web/qrregister.php` - Página de registro con Vue.js
- `web/assets/css/mobile-checkin.css` - Estilos del canvas de firma

### Backend
- `web/api/enviar-terminos-firmados.php` - API para envío de correos
- `web/api/registrar-rostro.php` - Registro en AWS Rekognition
- `web/api/marcar-token-usado.php` - Marca token como usado

### Base de Datos
- `database/add_email_column.sql` - Agrega columna email a employees
- `database/add_registro_tokens.sql` - Tabla de tokens de un solo uso

### Almacenamiento
- `web/uploads/firmas/` - Directorio de firmas digitales (Git-ignored)

## 📊 Estructura de la Base de Datos

### Tabla `employees` (actualizada)
```sql
- id (INT)
- employee_id (VARCHAR)
- nombre (VARCHAR)
- rut (VARCHAR)
- email (VARCHAR) ← NUEVO
- cargo (VARCHAR)
- face_indexed (BOOLEAN)
- registro_token (VARCHAR)
- token_usado (BOOLEAN)
- token_fecha_uso (DATETIME)
- created_at (TIMESTAMP)
```

## 📧 Configuración del Correo

El sistema usa la función `mail()` de PHP. Asegúrate de tener configurado un servidor SMTP.

### Opción 1: PHP mail() nativo
```ini
; php.ini
[mail function]
SMTP = smtp.tuservidor.cl
smtp_port = 587
sendmail_from = noreply@fagotto.cl
```

### Opción 2: PHPMailer (recomendado)
```php
// Instalar: composer require phpmailer/phpmailer
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'tu-email@gmail.com';
$mail->Password = 'tu-app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

## 🎨 Canvas de Firma Digital

### Características
- **Responsive**: Se adapta a móviles y tablets
- **Táctil**: Funciona con touch en pantallas táctiles
- **Mouse**: Compatible con computadores de escritorio
- **Limpiar**: Botón para reiniciar la firma
- **Validación**: No permite continuar sin firma

### Uso en Móvil
```javascript
// Touch events
@touchstart="iniciarFirmaTactil"
@touchmove="dibujarFirmaTactil"
@touchend="terminarFirma"
```

### Uso en Desktop
```javascript
// Mouse events
@mousedown="iniciarFirma"
@mousemove="dibujarFirma"
@mouseup="terminarFirma"
```

## 📜 Términos y Condiciones (DT Chile)

El documento incluye todos los puntos exigidos por la Dirección del Trabajo:

### I. Identificación del Empleador
- Razón Social: **Fagotto**
- RUT: **77.742.774-1**
- Domicilio: Av. Las Condes 7253, Las Condes, RM

### II-XII. Contenido Legal
1. Objeto del sistema
2. Datos personales tratados
3. Reconocimiento facial (biometría)
4. Geolocalización
5. Integridad de datos
6. Confidencialidad
7. Cumplimiento normativo
8. Derechos del trabajador
9. Consentimiento expreso
10. Sistema alternativo
11. Aceptación

## 🔐 Seguridad

### Protección de Datos
- ✅ Firmas almacenadas en servidor con permisos 0755
- ✅ Nombres de archivo con timestamp único
- ✅ Directorio ignorado en Git (.gitignore)
- ✅ Validación de tipos de archivo
- ✅ Tokens de un solo uso

### Validaciones
- RUT chileno con dígito verificador
- Email con regex RFC 5322
- Nombre mínimo 3 caracteres
- Cargo debe ser una de las 3 opciones válidas
- Firma digital obligatoria

## 🚀 Instalación

### 1. Actualizar Base de Datos
```bash
mysql -u root -p easyerp < database/add_email_column.sql
```

### 2. Crear Directorio de Firmas
```bash
mkdir -p web/uploads/firmas
chmod 755 web/uploads/firmas
```

### 3. Configurar Permisos
```bash
chown www-data:www-data web/uploads/firmas
```

### 4. Verificar PHP mail()
```bash
php -r "mail('test@ejemplo.com', 'Test', 'Hola');"
```

## 📱 Pruebas

### Debug QR
Usa `web/debug-qr.php` para generar links de prueba sin necesidad de escanear QR:

```bash
# Acceder al debug
https://fagotto.cl/debug-qr.php

# 1. Buscar empleado
# 2. Click en "Generar Nuevo Token"
# 3. Click en "Copiar Link" o "Abrir Link"
# 4. Completar formulario
# 5. Verificar email
```

### Checklist de Pruebas
- [ ] Formulario valida RUT correcto
- [ ] Formulario rechaza RUT inválido
- [ ] Email valida formato correcto
- [ ] Canvas dibuja con mouse
- [ ] Canvas dibuja con touch (móvil)
- [ ] Botón "Limpiar" resetea canvas
- [ ] No permite continuar sin firma
- [ ] Correo llega con HTML correcto
- [ ] Firma se ve en el correo
- [ ] Archivo PNG se guarda en servidor
- [ ] Token se marca como usado
- [ ] Link usado muestra error

## 🐛 Troubleshooting

### ❌ Correo no llega
```bash
# Verificar logs de PHP
tail -f /var/log/apache2/error.log

# Verificar configuración SMTP
php -i | grep mail
```

### ❌ Canvas no dibuja
```javascript
// Verificar en consola del navegador
console.log(this.ctxFirma); // Debe ser CanvasRenderingContext2D
```

### ❌ Firma no se guarda
```bash
# Verificar permisos del directorio
ls -la web/uploads/firmas/
chmod 755 web/uploads/firmas/
```

### ❌ Email inválido
```javascript
// Regex de validación
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
```

## 📞 Soporte

Para problemas o consultas:
- **Email**: soporte@fagotto.cl
- **Teléfono**: +56 2 XXXX XXXX
- **Dirección del Trabajo**: www.dt.gob.cl

## 📄 Licencia

Sistema propietario de Fagotto © 2025. Todos los derechos reservados.

---

**Última actualización**: 26 de diciembre de 2025
**Versión**: 1.0.0
**Autor**: Sistema de Desarrollo Fagotto
