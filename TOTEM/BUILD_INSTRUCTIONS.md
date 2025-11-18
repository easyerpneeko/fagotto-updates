# TOTEM Fagotto - Instrucciones de Instalación y Build

## 📦 Proyecto Electron Configurado

El proyecto TOTEM ahora está empaquetado como aplicación Electron con las siguientes características:

### ✨ Características
- 🖥️ Aplicación de escritorio nativa
- 🐘 Servidor PHP integrado
- 🔄 Inicio automático del backend
- 🎨 Interfaz fullscreen tipo kiosko
- 📌 Icono en bandeja del sistema
- 🔧 Panel de configuración desde el tray

### 📁 Estructura del Proyecto

```
TOTEM/
├── electron/
│   ├── main.js          # Proceso principal Electron
│   ├── preload.js       # Script de seguridad
│   ├── loading.html     # Pantalla de carga
│   └── assets/
│       ├── icon.svg     # Icono vectorial
│       └── icon.png     # Icono principal (512x512)
├── totem.html           # Interfaz del kiosko
├── *.php                # Backend PHP
├── vendor/              # Dependencias Composer
├── storage/             # Logs y datos
└── package.json         # Configuración Electron
```

## 🚀 Instalación de Dependencias

### Prerequisitos

**Node.js 14+ requerido** (actualmente tienes v12.22.12)
- Descarga Node.js 18 LTS: https://nodejs.org/
- Instalar y reiniciar terminal

**PHP 7.4+ requerido**
- PHP debe estar instalado y accesible en PATH
- Verifica con: `php --version`

### Instalar Dependencias

```powershell
cd "c:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\TOTEM"
npm install
```

## 🎯 Ejecutar en Desarrollo

```powershell
npm start
```

Esto abrirá la aplicación en modo desarrollo con:
- Servidor PHP en puerto 8080 (o el primer puerto libre)
- DevTools habilitadas
- Recarga automática

## 🏗️ Compilar Instalador .EXE

### 1. Preparar Icono (Importante)

Convierte el SVG a PNG de 512x512:

**Opción A: Online (más fácil)**
1. Ve a https://svgtopng.com/
2. Sube `electron/assets/icon.svg`
3. Descarga como PNG 512x512
4. Guarda como `electron/assets/icon.png`

**Opción B: Con herramienta**
```powershell
npm install -g electron-icon-builder
electron-icon-builder --input=electron/assets/icon.svg --output=electron/assets
```

### 2. Actualizar Node.js (Crítico)

electron-builder requiere Node.js 14+

1. Descarga e instala Node.js 18 LTS desde https://nodejs.org/
2. Cierra y reabre PowerShell
3. Verifica: `node --version` (debe ser >= 14)

### 3. Reinstalar Dependencias

```powershell
rm -rf node_modules package-lock.json
npm install
```

### 4. Compilar para Windows

```powershell
npm run build:win
```

Esto generará:
- `dist/TOTEM Fagotto Setup 1.0.0.exe` - Instalador NSIS
- `dist/win-unpacked/` - Archivos de la aplicación

### 5. Instalar y Probar

1. Ve a la carpeta `dist/`
2. Ejecuta `TOTEM Fagotto Setup 1.0.0.exe`
3. Sigue el asistente de instalación
4. La aplicación se instalará en `C:\Program Files\TOTEM Fagotto`
5. Se creará acceso directo en Escritorio y Menú Inicio

## ⚙️ Configuración

### Variables de Entorno

Edita `.env` en la carpeta raíz:

```env
MP_ACCESS_TOKEN=tu_token_aqui
MP_USER_ID=tu_user_id
TERMINAL_ID=tu_terminal_id
WEBHOOK_URL=https://fagottoerp.cl/mercadopago/webhook-point.php
```

### PHP Runtime

Para incluir PHP portable en el instalador:

1. Descarga PHP Windows binaries: https://windows.php.net/download/
2. Extrae en la carpeta `php/` del proyecto:
   ```
   TOTEM/
   └── php/
       ├── php.exe
       ├── php.ini
       └── ext/
   ```
3. La configuración en `package.json` ya incluye esta carpeta como `extraResources`

## 🐛 Solución de Problemas

### Error: "No se pudo iniciar servidor PHP"

- Verifica que PHP esté instalado: `php --version`
- Agrega PHP al PATH de Windows
- O incluye PHP portable en la carpeta `php/`

### Error: "electron-builder requires Node.js 14+"

- Actualiza Node.js a versión 18 LTS
- Reinstala dependencias después de actualizar

### Icono no aparece

- Asegúrate de tener `electron/assets/icon.png` de 512x512
- electron-builder lo convertirá a .ico automáticamente

### La aplicación no carga totem.html

- Verifica que el servidor PHP esté corriendo
- Revisa logs en la consola de DevTools (F12 en desarrollo)
- Verifica que el puerto 8080 no esté ocupado

## 📝 Comandos Disponibles

```powershell
npm start              # Ejecutar en desarrollo
npm run build          # Build para la plataforma actual
npm run build:win      # Build para Windows (.exe)
npm run build:mac      # Build para macOS (.dmg)
npm run build:linux    # Build para Linux (.AppImage)
```

## 🎨 Personalización

### Cambiar Icono

1. Edita `electron/assets/icon.svg`
2. Regenera PNG con herramienta online
3. Rebuild: `npm run build:win`

### Cambiar Nombre

Edita `package.json`:
```json
{
  "name": "mi-nombre-app",
  "productName": "Mi Aplicación"
}
```

### Modo Ventana vs Fullscreen

Edita `electron/main.js`:
```javascript
fullscreen: false,  // Cambiar a false para modo ventana
frame: true,        // Cambiar a true para mostrar barra de título
```

## 📦 Distribución

El instalador .exe incluye:
- Aplicación Electron
- Servidor PHP integrado
- Archivos PHP del TOTEM
- Dependencias de Composer
- Runtime de PHP (si está en carpeta `php/`)

**Tamaño aproximado:** ~100-150 MB (con PHP) o ~80 MB (sin PHP)

## 🔐 Seguridad

- No incluyas tokens reales en el repositorio
- Usa variables de entorno (.env)
- .env NO se incluye en el instalador por defecto
- Los usuarios deben configurar sus propios tokens después de instalar

## ✅ Checklist Final

Antes de compilar el instalador:

- [ ] Node.js 14+ instalado
- [ ] Icono PNG generado (512x512)
- [ ] PHP instalado o incluido en carpeta `php/`
- [ ] `.env` configurado (opcional para testing)
- [ ] Dependencias instaladas (`npm install`)
- [ ] Probado en desarrollo (`npm start`)
- [ ] Build ejecutado (`npm run build:win`)
- [ ] Instalador probado en máquina limpia

---

**¿Problemas?** Revisa los logs en:
- DevTools (F12 en desarrollo)
- `storage/logs/` (logs de PHP)
- Console de PowerShell (logs de Electron)
