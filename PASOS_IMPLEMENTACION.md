# 🚀 GUÍA PASO A PASO - IMPLEMENTACIÓN COMPLETA

## 📋 ÍNDICE RÁPIDO
1. [Preparar Base de Datos](#paso-1-preparar-base-de-datos) ⏱️ 5 min
2. [Configurar AWS Rekognition](#paso-2-configurar-aws-rekognition) ⏱️ 15 min
3. [Instalar Dependencias PHP](#paso-3-instalar-dependencias-php) ⏱️ 5 min
4. [Configurar Archivos](#paso-4-configurar-archivos) ⏱️ 10 min
5. [Subir a Servidor](#paso-5-subir-a-servidor) ⏱️ 10 min
6. [Probar Sistema](#paso-6-probar-sistema) ⏱️ 10 min

**Tiempo total estimado:** 55 minutos

---

## PASO 1: Preparar Base de Datos

### 1.1 Abrir phpMyAdmin o terminal MySQL

**Opción A: phpMyAdmin (más fácil)**
```
1. Ve a http://localhost/phpmyadmin (desarrollo local)
   O http://fagotto.cl/phpmyadmin (producción)

2. Usuario: root (o tu usuario)
3. Contraseña: (tu contraseña)
```

**Opción B: Terminal/CMD**
```bash
mysql -u root -p
```

### 1.2 Ejecutar el script SQL

```sql
-- Copiar TODO el contenido de: database/schema_asistencia.sql
-- Pegarlo en phpMyAdmin y dar click en "Continuar"

-- O desde terminal:
source C:/Users/jimmy/Documents/GitHub/FRONT-PROJECT-VUE-DEV/database/schema_asistencia.sql
```

### 1.3 Verificar que se creó correctamente

```sql
-- Ejecutar esta consulta:
USE fagotto_asistencia;
SHOW TABLES;

-- Deberías ver:
-- employees
-- checkin_sessions
-- attendance_records
-- v_asistencia_hoy (vista)
-- v_empleados_registrados (vista)
-- v_estadisticas_mes (vista)
```

✅ **CHECKPOINT:** Si ves las 3 tablas, ¡perfecto! Continúa.

---

## PASO 2: Configurar AWS Rekognition

### 2.1 Crear cuenta AWS (si no tienes)

```
1. Ve a: https://aws.amazon.com
2. Click en "Crear una cuenta de AWS"
3. Completa el formulario:
   - Email
   - Contraseña
   - Nombre de cuenta: "fagotto-rekognition"
4. Información de contacto
5. Verificar tarjeta de crédito (no te cobran nada por ahora)
6. Verificación de identidad (SMS o llamada)
7. Elegir plan: "Plan de soporte básico (Gratis)"
```

### 2.2 Acceder a la consola AWS

```
1. Ve a: https://console.aws.amazon.com
2. Inicia sesión con tu cuenta
3. En la barra superior, busca "IAM"
4. Click en "IAM" (Identity and Access Management)
```

### 2.3 Crear usuario IAM

```
1. En el menú izquierdo → "Usuarios" → "Crear usuario"

2. Configuración:
   ┌─────────────────────────────────────────┐
   │ Nombre de usuario: fagotto-rekognition │
   │ ☑ Clave de acceso programático         │
   │ ☐ Acceso a la consola de AWS           │
   └─────────────────────────────────────────┘

3. Click "Siguiente: Permisos"

4. Adjuntar políticas:
   ┌─────────────────────────────────────────┐
   │ Buscar: "Rekognition"                   │
   │ ☑ AmazonRekognitionFullAccess          │
   └─────────────────────────────────────────┘

5. Click "Siguiente: Etiquetas" → Omitir
6. Click "Siguiente: Revisar"
7. Click "Crear usuario"
```

### 2.4 Guardar credenciales (¡IMPORTANTE!)

```
🚨 PANTALLA FINAL - NO LA CIERRES SIN COPIAR:

┌───────────────────────────────────────────────────────┐
│ Usuario creado correctamente                         │
│                                                       │
│ Access Key ID:                                        │
│ AKIAIOSFODNN7EXAMPLE                                 │
│                                                       │
│ Secret Access Key:         [Mostrar]                 │
│ wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY           │
│                                                       │
│ [Descargar .csv]                                     │
└───────────────────────────────────────────────────────┘

✅ COPIA ESTOS VALORES AHORA - No podrás verlos después
✅ O descarga el archivo .csv
```

### 2.5 Crear Collection en AWS Rekognition

**Opción A: Usando AWS CLI (si tienes instalado)**
```bash
aws rekognition create-collection --collection-id fagotto-employees --region us-east-1
```

**Opción B: Usando tu código PHP (más fácil)**
```
No hagas nada ahora - la colección se creará automáticamente
cuando registres el primer empleado (el código ya lo hace)
```

✅ **CHECKPOINT:** Tienes Access Key ID y Secret Access Key guardados.

---

## PASO 3: Instalar Dependencias PHP

### 3.1 Verificar que tienes Composer instalado

```bash
# En PowerShell o CMD:
composer --version

# Debería mostrar:
# Composer version 2.x.x
```

**Si NO tienes Composer:**
```
1. Ve a: https://getcomposer.org/download/
2. Descarga Composer-Setup.exe
3. Ejecuta el instalador
4. Reinicia PowerShell/CMD
```

### 3.2 Ir a la carpeta web/

```bash
cd C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web
```

### 3.3 Crear composer.json

```bash
# Crea el archivo composer.json con este contenido:
echo {
  "require": {
    "aws/aws-sdk-php": "^3.0"
  }
} > composer.json
```

### 3.4 Instalar AWS SDK

```bash
composer install
```

**Deberías ver:**
```
Loading composer repositories with package information
Updating dependencies
Lock file operations: 10 installs, 0 updates, 0 removals
  - Installing aws/aws-crt-php (v1.x)
  - Installing aws/aws-sdk-php (3.x)
Writing lock file
Installing dependencies from lock file
Package operations: 10 installs, 0 updates, 0 removals
...
✅ Generating autoload files
```

✅ **CHECKPOINT:** Carpeta `vendor/` creada con librerías AWS.

---

## PASO 4: Configurar Archivos

### 4.1 Configurar AWS Credentials

```bash
# Abrir archivo:
notepad C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\config\aws.php
```

**Reemplazar estas líneas:**
```php
// ANTES:
define('AWS_ACCESS_KEY_ID', 'TU_ACCESS_KEY_AQUI');
define('AWS_SECRET_ACCESS_KEY', 'TU_SECRET_KEY_AQUI');

// DESPUÉS (con tus valores del Paso 2.4):
define('AWS_ACCESS_KEY_ID', 'AKIAIOSFODNN7EXAMPLE');
define('AWS_SECRET_ACCESS_KEY', 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY');
```

**Guardar y cerrar (Ctrl+S)**

### 4.2 Configurar Base de Datos

```bash
# Abrir archivo:
notepad C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\config\database.php
```

**Desarrollo Local:**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fagotto_asistencia');
define('DB_USER', 'root');
define('DB_PASS', ''); // Tu contraseña MySQL
```

**Producción (fagotto.cl):**
```php
define('DB_HOST', 'localhost'); // O la IP de tu BD
define('DB_NAME', 'fagotto_asistencia');
define('DB_USER', 'fagotto_user');
define('DB_PASS', 'tu_password_seguro');
```

**Guardar y cerrar (Ctrl+S)**

✅ **CHECKPOINT:** Archivos de config completos con tus datos.

---

## PASO 5: Subir a Servidor (Producción)

### 5.1 Estructura de archivos a subir

```
fagotto.cl/
├── qrcheck.php
├── qrregister.php
├── api/
│   ├── reconocimiento-facial.php
│   ├── registrar-rostro.php
├── helpers/
│   └── AWSRekognition.php
├── config/
│   ├── aws.php
│   └── database.php
├── assets/
│   └── css/
│       └── mobile-checkin.css
└── vendor/ (toda la carpeta)
```

### 5.2 Subir vía FTP/SFTP

**Opción A: FileZilla (recomendado)**
```
1. Descargar: https://filezilla-project.org/
2. Conectar:
   - Host: fagotto.cl (o ftp.fagotto.cl)
   - Usuario: tu_usuario_ftp
   - Contraseña: tu_password_ftp
   - Puerto: 21 (FTP) o 22 (SFTP)

3. Navegar a la carpeta pública:
   /public_html/ o /www/ o /htdocs/

4. Arrastrar toda la carpeta web/ al servidor
```

**Opción B: cPanel File Manager**
```
1. Ve a: https://fagotto.cl/cpanel
2. File Manager → public_html/
3. Upload → Seleccionar archivos
4. Subir todo el contenido de web/
```

**Opción C: Git Deploy**
```bash
# Si tienes acceso SSH:
ssh usuario@fagotto.cl
cd /var/www/html
git pull origin main
composer install --no-dev
```

### 5.3 Configurar SSL/HTTPS (¡OBLIGATORIO!)

**La cámara solo funciona con HTTPS. Opciones:**

**Opción A: cPanel AutoSSL (automático)**
```
1. cPanel → SSL/TLS Status
2. Click "Run AutoSSL"
3. Esperar 5 minutos
```

**Opción B: Let's Encrypt (gratis)**
```bash
# SSH al servidor:
sudo certbot --apache -d fagotto.cl -d www.fagotto.cl
```

**Opción C: Cloudflare (más fácil)**
```
1. Ve a: https://cloudflare.com
2. Agregar sitio: fagotto.cl
3. Cambiar nameservers (en tu proveedor de dominio)
4. Cloudflare te dará SSL automático
```

✅ **CHECKPOINT:** Archivos en servidor y HTTPS funcionando.

---

## PASO 6: Probar Sistema

### 6.1 Probar conexión a Base de Datos

```
1. Ve a: https://fagotto.cl/test-db.php

Crear este archivo en el servidor:
```

```php
<?php
// test-db.php
require_once 'config/database.php';
try {
    $pdo = getDBConnection();
    echo "✅ Conexión a BD exitosa<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    $count = $stmt->fetchColumn();
    echo "✅ Empleados en BD: " . $count;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

### 6.2 Probar AWS Rekognition

```
1. Ve a: https://fagotto.cl/test-aws.php

Crear este archivo:
```

```php
<?php
// test-aws.php
require_once 'config/aws.php';
require_once 'helpers/AWSRekognition.php';
require_once 'vendor/autoload.php';

try {
    $rekognition = new AWSRekognition();
    echo "✅ AWS SDK cargado correctamente<br>";
    echo "✅ Access Key ID: " . substr(AWS_ACCESS_KEY_ID, 0, 10) . "...<br>";
    echo "✅ Region: " . AWS_REGION . "<br>";
    
    // Intentar crear collection
    $rekognition->crearCollectionSiNoExiste();
    echo "✅ Collection creada/verificada";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

### 6.3 Registrar primer empleado

```
1. Abrir la app de Electron en tu PC
2. Ir a "Admin Carnets"
3. Crear empleado:
   - Nombre: Tu Nombre
   - RUT: 12345678-9
   - Cargo: Administrador

4. Click en "Descargar Carnet"
5. Imprimir o guardar el QR

6. Con tu celular, escanear el QR del carnet
7. Deberías abrir: https://fagotto.cl/qrregister.php?employee=EMP001

8. Seguir los pasos en pantalla:
   ✓ Buena iluminación
   ✓ Mirar a la cámara
   ✓ Capturar foto
   ✓ Confirmar

9. Debería decir: "✅ Rostro registrado exitosamente"
```

### 6.4 Probar check-in

```
1. En la app de Electron en tu PC
2. Ir a "Registro Asistencia"
3. Ver el QR en pantalla

4. Con tu celular, escanear el QR
5. Deberías abrir: https://fagotto.cl/qrcheck.php?session=ABC123

6. Seleccionar tu nombre de la lista
7. Permitir acceso a GPS
8. Permitir acceso a cámara
9. Capturar foto de tu rostro

10. Debería decir: "✅ Entrada registrada"
    - Nombre: Tu Nombre
    - Hora: 09:23:15
    - Coincidencia: 95%
```

### 6.5 Verificar en Base de Datos

```sql
-- Ver registros de hoy:
SELECT * FROM v_asistencia_hoy;

-- Debería mostrar:
-- Tu nombre, hora, similitud facial, etc.
```

✅ **CHECKPOINT:** Sistema funcionando end-to-end.

---

## 🎯 RESUMEN DE PASOS

```
☑ Paso 1: Base de datos creada (schema_asistencia.sql)
☑ Paso 2: AWS IAM configurado + credenciales guardadas
☑ Paso 3: Composer instalado + AWS SDK descargado
☑ Paso 4: Archivos config/*.php completados
☑ Paso 5: Archivos subidos a fagotto.cl + HTTPS activo
☑ Paso 6: Primer empleado registrado + check-in exitoso
```

---

## ❓ SOLUCIÓN DE PROBLEMAS

### Error: "No se puede conectar a la base de datos"
```
✓ Verifica config/database.php
✓ Usuario y contraseña correctos
✓ Base de datos creada
✓ MySQL corriendo (services.msc)
```

### Error: "AWS credentials not found"
```
✓ Verifica config/aws.php
✓ Access Key ID copiado correctamente
✓ Secret Access Key sin espacios extras
✓ Region = 'us-east-1'
```

### Error: "Class 'Aws\Rekognition\RekognitionClient' not found"
```
✓ Ejecutar: composer install
✓ Verificar que existe: vendor/autoload.php
✓ Incluir: require_once 'vendor/autoload.php';
```

### Error: "Camera access denied"
```
✓ Página debe estar en HTTPS (no HTTP)
✓ Permitir cámara en ajustes del navegador
✓ Probar en Chrome/Safari (mejor compatibilidad)
```

### Error: "GPS not working"
```
✓ Permitir ubicación en ajustes del navegador
✓ Debe estar en HTTPS
✓ Probar fuera de edificios (mejor señal)
```

### Error: "InvalidParameterException: Collection not found"
```
✓ Ejecutar desde navegador: test-aws.php
✓ O crear manualmente:
   aws rekognition create-collection --collection-id fagotto-employees
```

---

## 📞 CONTACTO Y SOPORTE

**AWS Support:**
- https://console.aws.amazon.com/support/

**Composer Issues:**
- https://getcomposer.org/doc/

**MySQL/MariaDB:**
- https://dev.mysql.com/doc/

---

## 🎉 ¡SISTEMA LISTO!

Si llegaste hasta aquí y todo funcionó:

✅ Base de datos operativa
✅ AWS Rekognition configurado
✅ Primer empleado registrado
✅ Check-in exitoso con reconocimiento facial

**Costo mensual:** $3-6 USD (AWS Rekognition)
**Precisión:** 99%+
**Escalabilidad:** Ilimitada

🚀 **Próximos pasos:**
- Registrar más empleados
- Configurar reportes de asistencia
- Dashboard de admin
- Notificaciones automáticas

---

**Última actualización:** Diciembre 25, 2025
