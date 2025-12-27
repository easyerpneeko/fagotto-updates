# 📦 LISTA DE ARCHIVOS PARA SUBIR A FAGOTTO.CL

## 🎯 CARPETA: web/ → Subir a fagotto.cl/

```
web/
├── .env                              ← Configuración (¡IMPORTANTE!)
├── config.php                        ← Lee el .env
├── qrcheck.php                       ← Página de check-in móvil
├── qrregister.php                    ← Página de registro facial
│
├── api/
│   ├── create-session.php           ← Crear sesión QR
│   ├── reconocimiento-facial.php    ← Validar rostro (AWS)
│   └── registrar-rostro.php         ← Registrar rostro nuevo
│
├── helpers/
│   └── AWSRekognition.php           ← Clase AWS Rekognition
│
├── assets/
│   └── css/
│       └── mobile-checkin.css       ← Estilos móviles
│
└── vendor/                          ← (Composer install)
    └── aws/                         ← SDK AWS (se genera con composer)
```

---

## ⚡ PASO A PASO

### 1️⃣ VIA FTP/SFTP (FileZilla)

```bash
Conectar a: fagotto.cl
Usuario: tu_usuario_ftp
Password: tu_password_ftp

Carpeta destino: /public_html/ o /www/ o /htdocs/

Subir carpeta: web/* 
```

### 2️⃣ VIA cPanel File Manager

```bash
1. https://fagotto.cl/cpanel
2. File Manager
3. Ir a public_html/
4. Upload → Subir archivos de web/
```

### 3️⃣ VIA SSH (si tienes acceso)

```bash
# Comprimir archivos localmente
cd C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV
tar -czf web.tar.gz web/

# Subir al servidor
scp web.tar.gz usuario@fagotto.cl:/var/www/html/

# Descomprimir en servidor
ssh usuario@fagotto.cl
cd /var/www/html
tar -xzf web.tar.gz
```

---

## 🔧 DESPUÉS DE SUBIR

### 1. Instalar Composer en el servidor

```bash
# SSH al servidor
ssh usuario@fagotto.cl

# Ir a carpeta web
cd /var/www/html/web

# Instalar dependencias
composer install
```

### 2. Configurar .env en el servidor

```bash
# Editar .env
nano .env

# Cambiar:
DB_HOST=localhost              # O IP del servidor MySQL
APP_ID=AGU001                  # Según el local
```

### 3. Verificar permisos

```bash
chmod 755 web/
chmod 644 web/*.php
chmod 644 web/.env
```

---

## ✅ ARCHIVOS YA EN TU PC:

| Archivo | Ruta Local |
|---------|-----------|
| .env | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\.env` |
| config.php | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\config.php` |
| qrcheck.php | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\qrcheck.php` |
| qrregister.php | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\qrregister.php` |
| API create-session.php | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\api\create-session.php` |
| API reconocimiento-facial.php | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\api\reconocimiento-facial.php` |
| API registrar-rostro.php | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\api\registrar-rostro.php` |
| Helper AWS | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\helpers\AWSRekognition.php` |
| CSS | `C:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\web\assets\css\mobile-checkin.css` |

---

## 🚨 NO OLVIDES:

1. ✅ Ejecutar SQL en servidor: `database/INSTALAR_DB.sql`
2. ✅ Subir carpeta `web/` completa
3. ✅ Configurar `.env` con datos del servidor
4. ✅ Ejecutar `composer install` en servidor
5. ✅ Verificar que MySQL acepta conexiones remotas
6. ✅ Configurar SSL/HTTPS (obligatorio para cámara)

---

## 📞 PRUEBA RÁPIDA

Después de subir, abre en navegador:

```
https://fagotto.cl/test-conexion.php
```

Debe mostrar:
- ✅ Conexión a DB exitosa
- ✅ 4 tablas creadas
- ✅ 2 locales
- ✅ 2 empleados
