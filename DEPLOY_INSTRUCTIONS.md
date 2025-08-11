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

### 4. **Configurar Token de GitHub**
```powershell
$env:GH_TOKEN = "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"
```

### 5. **Verificar Token**
```powershell
echo $env:GH_TOKEN
```

### 6. **Ejecutar Build**
```powershell
node .electron-vue/build.js
```

### 7. **Ejecutar Deploy**
```powershell
npx electron-builder build --win --publish always
```

---

## ⚡ Comando Rápido (Un Solo Paso)

Una vez configurado el token, puedes usar:

```powershell
# Configurar token una sola vez por sesión
$env:GH_TOKEN = "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"

# Deploy completo
node .electron-vue/build.js && npx electron-builder build --win --publish always
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

### **Error: GitHub Token inválido**
```powershell
# Solución: Reconfigurar token
$env:GH_TOKEN = "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"
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

**Última actualización:** Agosto 9, 2025 - v1.11.13 ✅
**Próxima versión:** v1.11.14
