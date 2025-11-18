# Instrucciones para Generar Iconos

ImageMagick no está instalado. Usa una de estas alternativas:

## Opción 1: Convertidor en línea (MÁS FÁCIL)
1. Abre https://svgtopng.com/
2. Sube `electron/assets/icon.svg`
3. Descarga como PNG de 512x512
4. Guarda como `electron/assets/icon.png`

## Opción 2: electron-icon-builder
```powershell
npm install -g electron-icon-builder
cd electron/assets
electron-icon-builder --input=icon.svg --output=.
```

## Opción 3: Instalar ImageMagick
1. Descarga desde: https://imagemagick.org/script/download.php#windows
2. Instala con "Add to PATH" activado
3. Ejecuta:
```powershell
magick convert electron/assets/icon.svg -resize 512x512 electron/assets/icon.png
```

## Nota Importante
**electron-builder generará automáticamente .ico e .icns desde icon.png**

Solo necesitas `icon.png` de 512x512 y electron-builder hará el resto durante el build.

Por ahora, el build funcionará con el SVG como placeholder.
