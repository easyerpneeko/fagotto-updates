# 🏢 CONFIGURACIÓN DE LOCALES POR COMPUTADORA

## 📋 Problema Resuelto

**Antes**: Todos los links de registro usaban AGU001 por defecto, sin importar el local.

**Ahora**: Cada computadora puede configurarse con su APPID específico, y los links generados automáticamente usarán el APPID correcto.

## 🔧 Cómo Funciona

El sistema lee la configuración desde `src/renderer/config/app-config.json`:

```json
{
  "appId": "MAN001",              // ← Define el local
  "localNombre": "Manuel Montt",
  "gps": {
    "latitud": -33.42906753,
    "longitud": -70.61923681,
    "radio": 100
  }
}
```

## 🚀 Instalación por Local

### Opción 1: Usar Script (Recomendado)

#### Windows (PowerShell):
```powershell
# Para Manuel Montt
.\cambiar-local.ps1 MAN001

# Para Encomenderos
.\cambiar-local.ps1 ENC001

# Para Agustinas
.\cambiar-local.ps1 AGU001
```

#### Linux/Mac (Bash):
```bash
# Dar permisos de ejecución (solo la primera vez)
chmod +x cambiar-local.sh

# Para Manuel Montt
./cambiar-local.sh MAN001

# Para Encomenderos
./cambiar-local.sh ENC001

# Para Agustinas
./cambiar-local.sh AGU001
```

### Opción 2: Copiar Manualmente

```bash
cd src/renderer/config

# Para Manuel Montt
cp app-config-MAN001.json app-config.json

# Para Encomenderos
cp app-config-ENC001.json app-config.json

# Para Agustinas
cp app-config-AGU001.json app-config.json
```

### Opción 3: Editar Directamente

Abre `src/renderer/config/app-config.json` y cambia:

```json
{
  "appId": "MAN001",  // ← Cambia esto por el código de tu local
  "localNombre": "Manuel Montt",  // ← Y esto por el nombre
  ...
}
```

## 🌍 Locales Disponibles

| Código | Local            | Archivo de Configuración       |
|--------|------------------|--------------------------------|
| AGU001 | Agustinas        | app-config-AGU001.json         |
| MAN001 | Manuel Montt     | app-config-MAN001.json         |
| ENC001 | Encomenderos     | app-config-ENC001.json         |

### Agregar Más Locales

Para crear configuración de un nuevo local:

1. Copia un archivo existente:
```bash
cp app-config-AGU001.json app-config-NUE001.json
```

2. Edita el nuevo archivo:
```json
{
  "appId": "NUE001",
  "localNombre": "Nuevo Local",
  "gps": {
    "latitud": -33.12345,
    "longitud": -70.67890,
    "radio": 100
  }
}
```

3. Inserta en la base de datos:
```sql
INSERT INTO asistencias_locales (app_id, nombre, latitud, longitud, radio_metros, activo)
VALUES ('NUE001', 'Nuevo Local', -33.12345, -70.67890, 100, 1);
```

## ✅ Verificación

Después de cambiar la configuración:

### 1. Reinicia la Aplicación
```bash
npm run dev
```

### 2. Abre la Consola (F12)

### 3. Genera un QR de Registro

Deberías ver en la consola:
```
📍 Generando QR para local: { appId: 'MAN001', localNombre: 'Manuel Montt' }
```

### 4. Verifica el Link Generado

El link debe contener el APPID correcto:
```
https://asistencia.fagottoerp.cl/register-employee.php?session=REG1766796990931&appid=MAN001
                                                                                        ^^^^^^
                                                                                    Debe ser MAN001
```

## 📱 Flujo Completo

```
1. Computadora de Manuel Montt
   └─ app-config.json tiene appId: "MAN001"
   
2. Admin genera QR de registro
   └─ Sistema lee app-config.json
   └─ Crea session con app_id = 'MAN001'
   
3. QR generado contiene:
   └─ https://...register-employee.php?session=REG123&appid=MAN001
   
4. Empleado escanea QR
   └─ Se registra en local MAN001 (Manuel Montt)
   └─ GPS valida contra coordenadas de Manuel Montt
   
5. Cuando marca asistencia:
   └─ Sistema verifica que esté en Manuel Montt
   └─ Guarda registro con app_id = 'MAN001'
```

## 🔍 Troubleshooting

### ❌ Los links siguen usando AGU001

**Solución**:
1. Verifica que `app-config.json` tenga el APPID correcto
2. Reinicia la aplicación completamente
3. Revisa la consola para ver qué configuración está leyendo

### ❌ Error: "app-config.json no encontrado"

**Solución**:
```bash
cd src/renderer/config
ls -la app-config.json  # Verifica que existe

# Si no existe, copia uno de los templates:
cp app-config-MAN001.json app-config.json
```

### ❌ El script no funciona

**Windows**:
```powershell
# Habilitar ejecución de scripts (una sola vez):
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Luego ejecuta el script:
.\cambiar-local.ps1 MAN001
```

**Linux/Mac**:
```bash
# Dar permisos de ejecución:
chmod +x cambiar-local.sh

# Ejecutar:
./cambiar-local.sh MAN001
```

## 🎯 Casos de Uso

### Caso 1: Instalación en Local Nuevo
```bash
# 1. Clonar repositorio en la PC del local
git clone https://github.com/fagotto/asistencia.git

# 2. Instalar dependencias
npm install

# 3. Configurar local
.\cambiar-local.ps1 MAN001

# 4. Iniciar aplicación
npm run dev
```

### Caso 2: Cambiar de Local en PC Existente
```bash
# Si la PC se movió de Agustinas a Manuel Montt:
.\cambiar-local.ps1 MAN001

# Reiniciar aplicación
npm run dev
```

### Caso 3: Desarrollo con Múltiples Locales
```bash
# Cambiar rápidamente entre locales para pruebas:
.\cambiar-local.ps1 AGU001  # Probar Agustinas
npm run dev
# ... hacer pruebas ...

.\cambiar-local.ps1 MAN001  # Probar Manuel Montt
npm run dev
# ... hacer pruebas ...
```

## 📊 Resumen de Archivos

```
FRONT-PROJECT-VUE-DEV/
├── cambiar-local.ps1           ← Script para Windows
├── cambiar-local.sh            ← Script para Linux/Mac
└── src/
    └── renderer/
        ├── config/
        │   ├── app-config.json           ← ARCHIVO ACTIVO (gitignored)
        │   ├── app-config-AGU001.json    ← Template Agustinas
        │   ├── app-config-MAN001.json    ← Template Manuel Montt
        │   ├── app-config-ENC001.json    ← Template Encomenderos
        │   └── README.md                 ← Documentación
        └── views/
            └── registrohora.vue          ← Lee app-config.json
```

## ⚠️ IMPORTANTE

### ❌ NO Hacer Commit de app-config.json

El archivo `app-config.json` está en `.gitignore` porque:
- Cada computadora debe tener su propia configuración
- Evita conflictos al hacer pull/push
- Protege credenciales de base de datos

### ✅ SÍ Hacer Commit de Templates

Los archivos `app-config-*.json` SÍ deben estar en Git:
- Son plantillas para cada local
- Facilitan la configuración inicial
- Documentan los locales disponibles

## 🎓 Para Nuevos Desarrolladores

Si vas a trabajar en el código:

1. **Clona el repo**:
```bash
git clone https://github.com/fagotto/asistencia.git
cd asistencia
```

2. **Instala dependencias**:
```bash
npm install
```

3. **Configura tu local** (o usa AGU001 por defecto para desarrollo):
```bash
.\cambiar-local.ps1 AGU001
```

4. **Inicia la app**:
```bash
npm run dev
```

5. **Verifica en consola** que esté usando el APPID correcto.

---

**Última actualización**: 26 de diciembre de 2025  
**Versión**: 2.0.0  
**Autor**: Sistema de Desarrollo Fagotto
