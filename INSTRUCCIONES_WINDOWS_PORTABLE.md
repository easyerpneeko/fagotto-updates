# 📦 Fagotto ERP - Versión Windows Portable 1.11.40

## ✅ Archivo Generado

**Archivo:** `fagotto-erd-aplication-1.11.40-win-x64-portable.zip`  
**Tamaño:** 212 MB  
**Fecha:** 31 de Diciembre de 2025  
**Versión:** 1.11.40

## 🚀 Instrucciones de Instalación

### 1. Descomprimir el Archivo

1. Localiza el archivo `fagotto-erd-aplication-1.11.40-win-x64-portable.zip`
2. Haz clic derecho sobre el archivo
3. Selecciona "Extraer todo..." o usa WinRAR/7-Zip
4. Elige una carpeta de destino (ejemplo: `C:\Fagotto-ERP\`)

### 2. Ejecutar la Aplicación

1. Abre la carpeta extraída `win-unpacked`
2. Busca el archivo `fagotto-erd-aplication.exe`
3. Haz doble clic para ejecutar

**Ruta del ejecutable:**
```
win-unpacked/fagotto-erd-aplication.exe
```

### 3. Crear Acceso Directo (Opcional)

1. Clic derecho sobre `fagotto-erd-aplication.exe`
2. Selecciona "Crear acceso directo"
3. Arrastra el acceso directo al Escritorio

## ⚠️ Notas Importantes

### Windows Defender / Antivirus
Es posible que Windows Defender o tu antivirus bloquee la aplicación porque:
- No está firmada digitalmente (compilada desde macOS)
- Es detectada como "aplicación desconocida"

**Solución:**
1. Clic derecho en el archivo `.exe`
2. Selecciona "Propiedades"
3. Marca la casilla "Desbloquear" (si aparece)
4. Aplica y cierra

Si Windows SmartScreen bloquea la ejecución:
1. Haz clic en "Más información"
2. Selecciona "Ejecutar de todas formas"

### Requisitos del Sistema

- **Sistema Operativo:** Windows 10/11 (64-bit)
- **RAM:** Mínimo 4 GB (recomendado 8 GB)
- **Espacio en Disco:** 500 MB libres
- **Procesador:** Intel/AMD x64 compatible

## 📋 Contenido del Paquete

El ZIP contiene toda la carpeta `win-unpacked` con:

```
win-unpacked/
├── fagotto-erd-aplication.exe    ← Ejecutable principal
├── resources/
│   └── app.asar                   ← Aplicación empaquetada
├── locales/                       ← Idiomas (58 locales)
├── chrome_100_percent.pak
├── chrome_200_percent.pak
├── d3dcompiler_47.dll
├── ffmpeg.dll
├── libEGL.dll
├── libGLESv2.dll
├── vulkan-1.dll
├── swiftshader/                   ← Renderizado de software
└── ... (más archivos de Electron/Chromium)
```

## 🆕 Novedades de la Versión 1.11.40

### ✨ Nuevo Sistema de Pedidos Responsivo

**Componente `pedidofinal.vue` Actualizado:**
- ✅ **Diseño 100% responsivo** para móviles, tablets y desktop
- ✅ **Grid adaptativo** de productos (1-4 columnas según pantalla)
- ✅ **Formulario responsivo** con validaciones
- ✅ **Productos organizados por categorías:**
  - 📦 Insumos (7 productos)
  - 🍝 Salsas (9 productos)
  - 🥖 Ciabatta (2 productos)
  - 🍝 Pastas (2 productos)
- ✅ **Nuevos productos agregados:**
  - Papel Mantequilla ($80)
  - Stickers Fagotto ($5,000)
  - Bolsa Delivery Biodegradable ($138)
  - Pasta Fettuccini ($4,081/kg)
  - Pasta Bigoli ($4,081/kg)

### 🎨 Mejoras de UI/UX

- Header adaptativo con Bootstrap Grid
- Botones de acción full-width en móvil
- Indicador visual de productos seleccionados
- Animaciones suaves (fade-in-up)
- Emojis por categoría para mejor identificación
- Ordenamiento alfabético dentro de cada categoría

### 📱 Media Queries Implementadas

```css
@media (max-width: 576px)  → Móvil pequeño
@media (max-width: 768px)  → Tablet
@media (max-width: 1024px) → Laptop
@media (max-width: 1400px) → Desktop
```

## 🐛 Problemas Conocidos

### Advertencias de Windows
- La aplicación puede mostrar advertencias de seguridad porque no está firmada digitalmente
- **Esto es normal** para aplicaciones compiladas en macOS para Windows sin certificado

### Solución para "No se puede verificar el editor"
```
1. Clic derecho → Propiedades
2. Pestaña "General"
3. Marcar "Desbloquear"
4. Aplicar
```

## 📞 Soporte

Si tienes problemas con la instalación o ejecución:

1. Verifica que Windows Defender no esté bloqueando la app
2. Asegúrate de tener permisos de administrador
3. Ejecuta como administrador si es necesario:
   - Clic derecho en el .exe
   - "Ejecutar como administrador"

## 🔄 Actualización

Para actualizar a una nueva versión:
1. Cierra la aplicación actual
2. Elimina la carpeta `win-unpacked` anterior
3. Descomprime el nuevo ZIP
4. Ejecuta el nuevo `fagotto-erd-aplication.exe`

## 📝 Notas Técnicas

- **Electron Version:** 13.6.9
- **Node.js:** Integrado en Electron
- **Arquitectura:** x64 (64-bit)
- **Tipo:** Aplicación portable (no requiere instalación)
- **Auto-update:** Configurado con GitHub releases

---

**Desarrollado por:** Fagotto ERP Team  
**Fecha de compilación:** 31 de Diciembre de 2025  
**Build:** 1.11.40  
**Plataforma:** Windows 10/11 x64
