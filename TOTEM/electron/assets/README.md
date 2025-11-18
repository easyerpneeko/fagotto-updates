# TOTEM Fagotto - Sistema de Pagos
## Iconos de la Aplicación

Este directorio contiene los iconos de la aplicación TOTEM Fagotto.

### Archivos

- **icon.svg** - Ícono vectorial fuente (512x512)
- **icon.png** - Ícono principal para Electron (512x512)
- **icon.ico** - Ícono para Windows (multi-resolución)
- **icon.icns** - Ícono para macOS (multi-resolución)
- **tray-icon.png** - Ícono para la bandeja del sistema (32x32)

### Generación de Iconos

Para generar los iconos en todos los formatos necesarios, puedes usar:

#### Opción 1: electron-icon-builder (Recomendado)
```bash
npm install -g electron-icon-builder
electron-icon-builder --input=./electron/assets/icon.png --output=./electron/assets
```

#### Opción 2: Herramientas en línea
- **Windows (.ico)**: https://convertio.co/es/png-ico/
- **macOS (.icns)**: https://cloudconvert.com/png-to-icns
- **PNG desde SVG**: https://svgtopng.com/

#### Opción 3: ImageMagick
```bash
# Convertir SVG a PNG
magick convert icon.svg -resize 512x512 icon.png

# Generar .ico (Windows)
magick convert icon.png -define icon:auto-resize=256,128,64,48,32,16 icon.ico

# Generar .icns (macOS) - requiere png2icns
png2icns icon.icns icon.png
```

### Resoluciones Necesarias

**Windows (.ico):**
- 256x256, 128x128, 64x64, 48x48, 32x32, 16x16

**macOS (.icns):**
- 512x512@2x (1024x1024)
- 512x512
- 256x256@2x (512x512)
- 256x256
- 128x128@2x (256x256)
- 128x128
- 32x32@2x (64x64)
- 32x32
- 16x16@2x (32x32)
- 16x16

**Tray Icon:**
- 32x32 (transparente)

### Notas

- El SVG es el archivo fuente - edita este para cambiar el diseño
- Regenera todos los formatos después de modificar el SVG
- El tray-icon.png debe ser simple y visible en tamaños pequeños
- electron-builder generará automáticamente los iconos si solo proporcionas icon.png de 512x512
