# 🚀 INSTRUCCIONES DE DEPLOY - FAGOTTO ERP

## 🔧 Información Técnica

### **Repositorio de Deploy:**
- **Owner**: easyerpneeko
- **Repo**: fagotto-updates
- **Branch**: fagotto-dev

### **Archivos Generados:**
- `fagotto-erd-aplication Setup 1.11.X.exe`
- `fagotto-erd-aplication Setup 1.11.X.exe.blockmap`
- `latest.yml`

### **Configuración Automática:**
- Auto-updater habilitado
- Release automático en GitHub
- Distribución multiplataforma

---

## ⚠️ Problemas Comunes y Soluciones

### **Error: GitHub Token inválido - SOLUCIÓN DEFINITIVA**
```powershell
# ✅ USAR ESTE COMANDO (token + deploy en una línea)
$env:GH_TOKEN="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"; npx electron-builder build --win --publish always
```

### **Token Actual:**
```
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
```

### **Error: electron-builder no encontrado**
```powershell
# Solución: Usar npx
npx electron-builder build --win --publish always
```

### **Error: Build fallido**
```powershell
# Solución: Limpiar y rebuild
npm run build:clean
node .electron-vue/build.js
```

### **⚠️ IMPORTANTE: Proceso de empaquetado de electron-builder**
- electron-builder **SIEMPRE** crea procesos hijo durante el empaquetado
- Estos procesos **NO HEREDAN** variables de entorno configuradas en comandos separados
- **SOLUCIÓN:** Configurar token en la misma línea del comando de deploy

---

## 📝 Checklist Pre-Deploy

- [ ] Versión actualizada en `package.json`
- [ ] Código compilado con `node .electron-vue/build.js`
- [ ] Commit y push realizado a `fagotto-dev`
- [ ] Token GH_TOKEN configurado correctamente
- [ ] Ejecutar comando de deploy en una sola línea

---

## 🎯 Comando de Deploy Completo

```powershell
# Paso 1: Actualizar versión en package.json (manual o con script)

# Paso 2: Commit y push
git add -A
git commit -m "v1.11.X - Descripción de cambios"
git push origin fagotto-dev

# Paso 3: Build y Deploy (TODO EN UNA LÍNEA)
$env:GH_TOKEN="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"; node .electron-vue/build.js; if($?) { npx electron-builder build --win --publish always }
```

---

## 📦 Deploy Manual (Si el automático falla)

1. Ir a: https://github.com/easyerpneeko/fagotto-updates/releases/new
2. Configurar:
   - **Tag version:** v1.11.X
   - **Release title:** v1.11.X
   - **Description:** Descripción de cambios
3. Subir archivos desde `build/`:
   - `fagotto-erd-aplication Setup 1.11.X.exe`
   - `fagotto-erd-aplication Setup 1.11.X.exe.blockmap`
   - `latest.yml`
4. Click en **"Publish release"**

---

## 🔄 Verificación Post-Deploy

```powershell
# Verificar que la release se creó
# Visitar: https://github.com/easyerpneeko/fagotto-updates/releases/latest

# Verificar archivos en build/
Get-ChildItem build | Where-Object {$_.Name -like "*1.11.X*"}
```

---

## 📋 Notas Adicionales

- Siempre usar PowerShell (no CMD)
- El token debe estar en la misma línea que electron-builder
- Si falla el deploy automático, usar deploy manual
- Los archivos se generan en la carpeta `build/`
- El auto-updater verifica automáticamente nuevas versiones
