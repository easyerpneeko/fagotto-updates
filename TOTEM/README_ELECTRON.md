# 🍰 TOTEM Fagotto - Sistema de Pagos Point Smart

Sistema de autoservicio (kiosko) integrado con **Mercado Pago Point Smart** para restaurante Fagotto.

## 📋 Descripción

Aplicación de escritorio Electron que proporciona:
- **Interface de kiosko** para pedidos de clientes
- **Integración Mercado Pago Point** para pagos con terminal
- **Servidor PHP embebido** para el backend
- **Instalador .exe** para Windows

## 🚀 Inicio Rápido

### Opción 1: Ejecutar en Desarrollo

```powershell
# 1. Instalar dependencias
npm install

# 2. Configurar variables de entorno
# Edita .env con tus credenciales de Mercado Pago

# 3. Ejecutar
npm start
```

### Opción 2: Instalar desde .exe

1. Compila el instalador: `npm run build:win`
2. Ejecuta `dist/TOTEM Fagotto Setup 1.0.0.exe`
3. Sigue el asistente de instalación
4. Configura `.env` en la carpeta de instalación

## 📦 Tecnologías

- **Electron 28** - Framework de aplicación de escritorio
- **PHP 7.4+** - Backend y lógica de negocio
- **Mercado Pago API** - Integración de pagos
- **electron-builder** - Empaquetado y distribución

## 📁 Estructura

```
TOTEM/
├── electron/              # Código Electron
│   ├── main.js           # Proceso principal
│   ├── preload.js        # Bridge de seguridad
│   ├── loading.html      # Splash screen
│   └── assets/           # Iconos
├── totem.html            # UI del kiosko
├── enviar-pago-web.php   # Envío de pago a terminal
├── validar-pago.php      # Validación de estado
├── webhook-point.php     # Webhook de notificaciones
├── config.php            # Configuración
├── .env                  # Variables de entorno
└── vendor/               # Dependencias Composer
```

## ⚙️ Configuración

### Variables de Entorno (.env)

```env
MP_ACCESS_TOKEN=APP_USR-xxxxxxxxx
MP_USER_ID=123456789
TERMINAL_ID=N950NCC302980808
WEBHOOK_URL=https://fagottoerp.cl/mercadopago/webhook-point.php
```

### Configuración de Terminal

Terminal actual: **NEWLAND N950** (ID: N950NCC302980808)

Ver detalles en: `ver-mi-terminal.php`

## 🔧 Desarrollo

### Prerequisitos

- Node.js 14+ (recomendado 18 LTS)
- PHP 7.4+ con extensiones: curl, json, mbstring
- Composer (para dependencias PHP)

### Scripts Disponibles

```bash
npm start          # Desarrollo con DevTools
npm run build      # Build para plataforma actual
npm run build:win  # Build Windows (.exe)
npm run build:mac  # Build macOS (.dmg)
npm run build:linux # Build Linux (.AppImage)
```

### Testing Local

```bash
# Terminal 1: Servidor PHP manual (opcional)
php -S localhost:8080

# Terminal 2: Electron
npm start
```

## 🏗️ Compilar Instalador

### Paso 1: Actualizar Node.js

Verifica versión: `node --version` (debe ser >= 14)

Si es < 14, instala Node.js 18 LTS: https://nodejs.org/

### Paso 2: Generar Icono PNG

Opción A - Online (recomendado):
1. Ve a https://svgtopng.com/
2. Sube `electron/assets/icon.svg`
3. Descarga como 512x512 PNG
4. Guarda en `electron/assets/icon.png`

Opción B - CLI:
```bash
npm install -g electron-icon-builder
electron-icon-builder --input=electron/assets/icon.svg --output=electron/assets
```

### Paso 3: Build

```powershell
npm run build:win
```

Resultado en: `dist/TOTEM Fagotto Setup 1.0.0.exe`

## 🐘 PHP Runtime

### Opción A: Usar PHP del Sistema

Requiere PHP instalado en el sistema del usuario.

### Opción B: Incluir PHP Portable (Recomendado)

1. Descarga PHP Windows: https://windows.php.net/download/
2. Extrae en `TOTEM/php/`:
   ```
   php/
   ├── php.exe
   ├── php.ini
   └── ext/
   ```
3. Build incluirá PHP automáticamente

## 📱 Funcionalidades

### Kiosko (totem.html)
- Catálogo de productos (pastas, salsas, agregados, bebidas, postres)
- Carrito de compras
- Sistema de upselling
- Selector de salsas modal
- Cálculo de total en tiempo real

### Backend PHP
- Envío de pagos a terminal Point
- Validación de estado de pagos
- Webhook para notificaciones de Mercado Pago
- Gestión de configuración
- Logs de transacciones

### Aplicación Electron
- Modo fullscreen tipo kiosko
- Sin barra de título
- Icono en bandeja del sistema
- Menú contextual con opciones:
  - Mostrar/Ocultar ventana
  - Recargar aplicación
  - Ver configuración
  - Ver terminal
  - Salir

## 🔐 Seguridad

- **Context Isolation** habilitado
- **Node Integration** deshabilitado en renderer
- **preload.js** para exposición segura de APIs
- Variables sensibles en `.env` (no versionado)

## 📊 Logs

Logs de PHP: `storage/logs/`
Logs de Electron: Console de DevTools (desarrollo)

## 🐛 Troubleshooting

### PHP no inicia
- Verifica `php --version`
- Agrega PHP al PATH
- O incluye PHP portable en `php/`

### Puerto 8080 ocupado
La app busca automáticamente el siguiente puerto libre.

### Icono no aparece
Asegura que `electron/assets/icon.png` existe y es 512x512.

### Build falla con "Node.js 14 required"
Actualiza Node.js a versión 18 LTS o superior.

## 📄 Documentación Adicional

- [BUILD_INSTRUCTIONS.md](BUILD_INSTRUCTIONS.md) - Guía detallada de compilación
- [GENERAR_ICONOS.md](GENERAR_ICONOS.md) - Cómo generar iconos
- [README.md](README.md) - Documentación de Mercado Pago API

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama: `git checkout -b feature/nueva-funcionalidad`
3. Commit: `git commit -m 'Agrega nueva funcionalidad'`
4. Push: `git push origin feature/nueva-funcionalidad`
5. Abre un Pull Request

## 📞 Soporte

**Fagotto ERP System**
- URL: https://fagottoerp.cl
- Webhook: https://fagottoerp.cl/mercadopago/webhook-point.php

## 📜 Licencia

MIT License - ver LICENSE file

---

Desarrollado con ❤️ para Fagotto
