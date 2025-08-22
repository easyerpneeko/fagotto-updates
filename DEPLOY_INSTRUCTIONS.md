# 🚀 Instrucciones de Deploy - Fagotto ERP

## 📋 Pasos para Deploy Exitoso

### 1. **Verificar Estado del Repositorio**
```powershell
git status
git log --oneline -5
```

### 2. **Incrementar Versión en package.json**
- Abrir `package.json`
- Cambiar la versión en la línea 3: `"version": "1.11.X"`
- Incrementar el último número (ejemplo: 1.11.13 → 1.11.14)

### 3. **Commit y Push de Cambios**
```powershell
git add .
git commit -m "feat: descripción de los cambios realizados - v1.11.X"
git push origin fagotto-dev
```

### 4. **Ejecutar Build**
```powershell
node .electron-vue/build.js
```

### 5. **Deploy con Token (COMANDO ÚNICO - SOLUCIÓN DEFINITIVA)**
```powershell
# ⚠️ IMPORTANTE: Usar token + deploy en UNA SOLA LÍNEA
# Esto evita que electron-builder pierda el token durante el empaquetado
$env:GH_TOKEN="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"; npx electron-builder build --win --publish always
```

### 6. **❌ NO USAR (Método que falla)**
```powershell
# ❌ ESTO NO FUNCIONA - electron-builder pierde el token
$env:GH_TOKEN = "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"
npx electron-builder build --win --publish always
```

---

## ⚡ Comando Rápido (Un Solo Paso) - **MÉTODO GARANTIZADO**

```powershell
# Build + Deploy en comandos separados pero seguros
node .electron-vue/build.js

# Deploy con token configurado en la misma línea (FUNCIONA 100%)
$env:GH_TOKEN="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"; npx electron-builder build --win --publish always
```

## ⚠️ **PROBLEMA COMÚN Y SOLUCIÓN**

### **❌ Error Típico:**
```
Error: GitHub Personal Access Token is not set, neither programmatically, nor using env "GH_TOKEN"
```

### **✅ Causa y Solución:**
- **Problema:** electron-builder crea procesos hijo que no heredan variables de entorno configuradas por separado
- **Solución:** Configurar el token **en la misma línea** del comando de deploy

### **✅ Comando Correcto:**
```powershell
$env:GH_TOKEN="TOKEN"; npx electron-builder build --win --publish always
```

### **❌ Comando que Falla:**
```powershell
$env:GH_TOKEN = "TOKEN"
npx electron-builder build --win --publish always
```

---

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

- [ ] Código probado y funcionando
- [ ] Versión incrementada en package.json
- [ ] Cambios commiteados y pusheados
- [ ] Token de GitHub configurado
- [ ] Build exitoso
- [ ] Deploy completado
- [ ] Release verificado en GitHub

---

## 🎯 Resultado Esperado

Al completar el deploy exitosamente verás:
```
✅ publishing      publisher=Github (owner: easyerpneeko, project: fagotto-updates, version: 1.11.X)
✅ uploading       file=fagotto-erp-app-setup-1.11.X.exe.blockmap provider=GitHub
✅ uploading       file=fagotto-erp-app-setup-1.11.X.exe provider=GitHub
✅ creating GitHub release reason=release doesn't exist tag=v1.11.X version=1.11.X
```

---

**Última actualización:** Agosto 18, 2025 - v1.11.14 ✅  
**Solución token definitiva:** Aplicada y documentada ✅  
**Próxima versión:** v1.11.15

**🔧 Fix Aplicado:**
- Solución definitiva para error de GitHub Token en electron-builder
- Comando único que garantiza el deploy exitoso
- Documentación actualizada con método que funciona 100%
