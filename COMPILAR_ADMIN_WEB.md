# 🔧 COMPILAR CAMBIOS DEL ADMIN WEB - Sistema de Descuentos

## 📋 ¿Qué se modificó?

Se agregó el campo **"% Descuento en Pedidos"** en el formulario de productos del admin web Laravel.

**Archivo modificado:**
- `Server app/resources/js/Components/modals/products/createToEdit.vue`

---

## 🚀 PASOS PARA COMPILAR Y VER LOS CAMBIOS

### 1️⃣ Abrir Terminal en la carpeta del servidor

```powershell
cd "c:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV4\Server app"
```

### 2️⃣ Compilar los assets de Vue (Laravel Mix)

```powershell
npm run production
```

**O si estás en desarrollo:**
```powershell
npm run dev
```

**O para modo watch (recompila automáticamente):**
```powershell
npm run watch
```

---

## ⚙️ ¿Qué hace cada comando?

| Comando | Descripción | Cuándo usar |
|---------|-------------|-------------|
| `npm run dev` | Compila rápido para desarrollo | Desarrollo local |
| `npm run production` | Compila optimizado y minificado | **Producción/Deploy** |
| `npm run watch` | Recompila al detectar cambios | Desarrollo activo |

---

## ✅ Verificar que funcionó

### 1. Ver en consola:
Deberías ver algo como:
```
DONE  Compiled successfully in 25461ms
       Asset      Size   Chunks             Chunk Names
  /js/app.js  1.23 MiB      app  [emitted]  app
 /css/app.css  181 KiB      app  [emitted]  app
```

### 2. Refrescar navegador:
```
https://posfagotto.cl/admin/pedidos
```

### 3. Abrir modal de producto:
- Click en "Crear producto" o editar uno existente
- Deberías ver el campo: **"% Descuento en Pedidos (0-100)"**

---

## 🔥 En PRODUCCIÓN

### Pasos completos:

```powershell
# 1. Posicionarte en Server app
cd "c:\Users\jimmy\Documents\GitHub\FRONT-PROJECT-VUE-DEV4\Server app"

# 2. Compilar para producción
npm run production

# 3. Subir archivos compilados al servidor
# Los archivos generados están en: Server app/public/js/ y Server app/public/css/
```

### Archivos que suben al servidor:
- ✅ `public/js/app.js` (código Vue compilado)
- ✅ `public/css/app.css` (estilos compilados)
- ✅ `public/mix-manifest.json` (registro de versiones)

---

## ⚠️ IMPORTANTE

### Si da error al compilar:

```powershell
# Limpiar cache de node y reinstalar
rm -r node_modules
rm package-lock.json
npm install
npm run production
```

### Si da error "command not found":

```powershell
# Instalar dependencias primero
npm install
```

---

## 🎯 Resumen Rápido

```powershell
# Comando único para compilar todo
cd "Server app" ; npm run production
```

**Listo!** Ya puedes refrescar `https://posfagotto.cl/admin/pedidos` y verás el campo de descuento. 🚀

---

## 📱 ¿Dónde aparece el campo?

1. Ir a: `https://posfagotto.cl/admin/pedidos`
2. Click en **"Crear producto"** o editar uno existente
3. Buscar el campo: **"% Descuento en Pedidos"**
4. Ingresar porcentaje (ej: 15 para Champiñón)
5. Guardar

El descuento se guardará en la BD y se aplicará automáticamente en los pedidos! ✨

---

**Nota:** Si estás en LOCAL, el servidor debe estar corriendo con `php artisan serve` para ver los cambios.
