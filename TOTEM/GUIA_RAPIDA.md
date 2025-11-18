# 🚀 GUÍA RÁPIDA - TOTEM FAGOTTO

## ⏰ 5 Pasos para tener tu .exe

### 1️⃣ Actualizar Node.js (15 min)
```powershell
# Verifica versión actual
node --version
# Si es < 14, instala Node.js 18 LTS desde:
# https://nodejs.org/

# Después de instalar, reinicia PowerShell
# Reinstala dependencias
cd "c:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV\TOTEM"
rm -rf node_modules package-lock.json
npm install
```

### 2️⃣ Generar Icono PNG (2 min)
```
1. Abre: https://svgtopng.com/
2. Sube: electron/assets/icon.svg
3. Tamaño: 512x512
4. Descarga y guarda como: electron/assets/icon.png
```

### 3️⃣ Probar en Desarrollo (5 min)
```powershell
npm start
```
**Verificar:**
- ✅ Se abre ventana fullscreen
- ✅ Carga totem.html
- ✅ Icono en bandeja del sistema
- ✅ DevTools disponibles (F12)

### 4️⃣ Compilar Instalador (10 min)
```powershell
.\build.bat
# O manualmente:
npm run build:win
```

### 5️⃣ Instalar y Probar (5 min)
```powershell
# Ejecuta el instalador
.\dist\TOTEM Fagotto Setup 1.0.0.exe

# Se instalará en:
# C:\Program Files\TOTEM Fagotto

# Configura .env con tus credenciales
# Ejecuta desde acceso directo
```

---

## 🐘 OPCIONAL: Incluir PHP Portable

**Para que la app funcione sin instalar PHP:**

```powershell
# 1. Descarga PHP 7.4+ para Windows
#    https://windows.php.net/download/

# 2. Extrae en la carpeta php/
mkdir php
# Copia archivos de PHP aquí

# 3. Estructura debe quedar:
# php/
#   php.exe
#   php.ini
#   php7ts.dll
#   ext/

# 4. Build incluirá PHP automáticamente
npm run build:win
```

---

## ⚙️ Configuración .env

```env
MP_ACCESS_TOKEN=APP_USR-xxxxxxxxxx
MP_USER_ID=123456789
TERMINAL_ID=N950NCC302980808
WEBHOOK_URL=https://fagottoerp.cl/mercadopago/webhook-point.php
```

---

## 🎨 Personalización Rápida

### Cambiar Logo
1. Edita `electron/assets/icon.svg`
2. Regenera PNG (paso 2 arriba)
3. `npm run build:win`

### Cambiar Nombre
Edita `package.json`:
```json
{
  "name": "mi-totem",
  "productName": "Mi TOTEM"
}
```

### Modo Ventana (no fullscreen)
Edita `electron/main.js` línea ~72:
```javascript
fullscreen: false,  // Cambiar a false
frame: true,        // Cambiar a true
```

---

## 🆘 Problemas Comunes

### ❌ "PHP no inicia"
```powershell
# Verifica PHP
php --version

# Si no está instalado:
# Opción A: Instala PHP en Windows
# Opción B: Incluye PHP portable en carpeta php/
```

### ❌ "electron-builder requires Node 14+"
```powershell
# Actualiza Node.js
# https://nodejs.org/ (instala 18 LTS)
# Reinicia PowerShell
# Reinstala: rm -rf node_modules; npm install
```

### ❌ "Icono no aparece"
```powershell
# Verifica que existe:
ls electron\assets\icon.png

# Si no, genera desde SVG (paso 2 arriba)
```

### ❌ "Build falla"
```powershell
# Limpia y rebuild
rm -rf node_modules dist
npm install
npm run build:win
```

---

## 📦 Resultado Final

**Instalador:** `dist/TOTEM Fagotto Setup 1.0.0.exe`

**Tamaño:**
- Sin PHP: ~80 MB
- Con PHP: ~120-150 MB

**Features:**
- ✅ Instalador NSIS profesional
- ✅ Acceso directo en escritorio
- ✅ Acceso en menú inicio
- ✅ Desinstalador incluido
- ✅ Servidor PHP integrado
- ✅ Interfaz fullscreen tipo kiosko
- ✅ Icono en bandeja del sistema

---

## 📞 Soporte Rápido

**Documentación Completa:**
- `README_ELECTRON.md` - README principal
- `BUILD_INSTRUCTIONS.md` - Guía detallada
- `RESUMEN_PROYECTO.md` - Estado del proyecto

**Comandos Útiles:**
```powershell
npm start              # Desarrollo
npm run build:win      # Build Windows
npm audit fix          # Arreglar vulnerabilidades
npm update             # Actualizar dependencias
```

---

**¡Listo!** 🎉

En menos de 40 minutos tendrás tu instalador .exe funcionando.
