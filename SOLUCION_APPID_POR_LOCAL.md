# ✅ PROBLEMA RESUELTO: Links de Registro con APPID Correcto

## 🎯 Problema Original

**URL generada**:
```
https://asistencia.fagottoerp.cl/register-employee.php?session=REG1766796990931&appid=AGU001
                                                                                        ^^^^^^
                                                                                    Siempre AGU001
```

**Problema**: Todos los links usaban AGU001 por defecto, sin importar el local.

## ✅ Solución Implementada

Ahora cada computadora puede configurarse con su APPID específico:

```
Local Manuel Montt → appid=MAN001
Local Encomenderos → appid=ENC001
Local Agustinas    → appid=AGU001
```

## 🚀 Cómo Usar

### Para Manuel Montt:
```powershell
.\cambiar-local.ps1 MAN001
```

### Para Encomenderos:
```powershell
.\cambiar-local.ps1 ENC001
```

### Para Agustinas:
```powershell
.\cambiar-local.ps1 AGU001
```

## 📋 Flujo Actualizado

```
1. PC en Manuel Montt
   ↓
2. Ejecutar: .\cambiar-local.ps1 MAN001
   ↓
3. Sistema lee: app-config.json → "appId": "MAN001"
   ↓
4. QR generado: ...&appid=MAN001
   ↓
5. Empleado se registra en MAN001 ✅
   ↓
6. GPS valida contra coordenadas de Manuel Montt ✅
```

## 📂 Archivos Modificados

### ✏️ Editados:
- [registrohora.vue](src/renderer/views/registrohora.vue) - Ahora lee `app-config.json`
- [.gitignore](.gitignore) - Ignorar `app-config.json` (específico por PC)

### 🆕 Creados:
- [app-config-MAN001.json](src/renderer/config/app-config-MAN001.json) - Config Manuel Montt
- [app-config-ENC001.json](src/renderer/config/app-config-ENC001.json) - Config Encomenderos
- [app-config-AGU001.json](src/renderer/config/app-config-AGU001.json) - Config Agustinas
- [cambiar-local.ps1](cambiar-local.ps1) - Script para Windows
- [cambiar-local.sh](cambiar-local.sh) - Script para Linux/Mac
- [CONFIGURACION_LOCALES.md](CONFIGURACION_LOCALES.md) - Documentación completa
- [src/renderer/config/README.md](src/renderer/config/README.md) - Guía rápida

## 🎓 Instrucciones para Instalación

### En cada local:

1. **Clonar o actualizar repo**:
```bash
git pull origin main
```

2. **Configurar local específico**:
```powershell
# En Manuel Montt:
.\cambiar-local.ps1 MAN001

# En Encomenderos:
.\cambiar-local.ps1 ENC001

# En Agustinas:
.\cambiar-local.ps1 AGU001
```

3. **Reiniciar aplicación**:
```bash
npm run dev
# o
npm run build
```

4. **Verificar en consola** (F12):
```
📍 Generando QR para local: { appId: 'MAN001', localNombre: 'Manuel Montt' }
```

## 🔍 Verificación

### Link de Registro Generado:
```
https://asistencia.fagottoerp.cl/register-employee.php?session=REG123&appid=MAN001
                                                                                ^^^^^^
                                                                            Correcto! ✅
```

### Link de Check-in Generado:
```
https://asistencia.fagottoerp.cl/qrcheck.php?session=CHK456&appid=MAN001
                                                                   ^^^^^^
                                                               Correcto! ✅
```

## 📊 Tabla de Configuraciones

| Local          | APPID  | Archivo Config              | GPS Latitud    | GPS Longitud   |
|----------------|--------|----------------------------|----------------|----------------|
| Agustinas      | AGU001 | app-config-AGU001.json     | -33.440613     | -70.64901733   |
| Manuel Montt   | MAN001 | app-config-MAN001.json     | -33.42906753   | -70.61923681   |
| Encomenderos   | ENC001 | app-config-ENC001.json     | -33.41676104   | -70.60205544   |

## 🎉 Resultado Final

### ✅ Antes (Problema):
```javascript
// Hardcodeado en registrohora.vue línea 248
let appId = 'AGU001';  // ❌ Siempre AGU001
```

### ✅ Ahora (Solución):
```javascript
// Lee desde app-config.json
const config = JSON.parse(fs.readFileSync('app-config.json'));
let appId = config.appId;  // ✅ MAN001, ENC001, etc.
```

## 🛡️ Protecciones Implementadas

1. **Git Ignore**: `app-config.json` no se sube a Git
2. **Templates**: `app-config-*.json` SÍ están en Git
3. **Valores por defecto**: Si no existe archivo, usa AGU001
4. **Logs de debug**: Muestra en consola qué APPID está usando
5. **Scripts helper**: Facilitan cambio de configuración

## 📞 Soporte

Si un link sigue mostrando AGU001 en lugar del local correcto:

1. Verifica que ejecutaste el script de cambio
2. Reinicia la aplicación completamente
3. Revisa la consola (F12) para ver qué config lee
4. Confirma que `app-config.json` tiene el APPID correcto

---

**✅ PROBLEMA RESUELTO**

Ahora cada computadora genera links con su APPID correcto automáticamente.

**Fecha**: 26 de diciembre de 2025  
**Autor**: Sistema de Desarrollo Fagotto
