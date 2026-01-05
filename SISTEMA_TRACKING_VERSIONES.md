# 📊 Sistema de Tracking de Versiones por Negocio

## ¿Qué problema resuelve?

Antes no sabías si los negocios actualizaban o no la aplicación. Ahora tendrás visibilidad completa de:
- ✅ Qué versión usa cada negocio
- ✅ Cuándo fue su última conexión
- ✅ Quiénes están actualizados y quiénes no

## 🎯 Cómo Funciona

### 1. Sin depender del archivo `aplication.json`
- Usa el **Serial** del `localStorage` (igual que merchise y arqueo de caja)
- No hay problemas si el JSON se corrompe o no existe
- El Serial se guarda en la key `912338BA` del localStorage

### 2. Reporte Automático cada 5 minutos
Cuando la aplicación inicia:
- Espera 10 segundos
- Envía la versión actual + serial al servidor
- Repite cada 5 minutos automáticamente

### 3. Backend centralizado
- Recibe el **Serial** en el header `App-Key` (igual que tus otros endpoints)
- Busca el negocio en la tabla `Aplication`
- Guarda/actualiza la versión en la base de datos

## 📁 Archivos Creados

### 1. SQL
**`crear_tabla_app_version_tracking.sql`**
- Crea tabla `app_version_tracking` (versión actual de cada negocio)
- Crea tabla `app_version_history` (historial de cambios de versión)

### 2. Backend PHP
**`track_version.php`**
- Endpoint que recibe los pings de versión
- Usa el header `App-Key` con el Serial
- Actualiza la tabla automáticamente

**`get_app_versions.php`**
- API para consultar versiones de todos los negocios
- Retorna estadísticas (cuántos actualizados, desactualizados, offline)

### 3. Frontend
**`ver_versiones.html`**
- Dashboard visual hermoso para ver todas las versiones
- Se actualiza automáticamente cada 30 segundos
- Filtros: Todos, Última versión, Desactualizados, Sin conexión
- Búsqueda por nombre o ID de negocio

### 4. Electron
**`src/renderer/helpers/VersionTracker.js`**
- Módulo que envía los pings automáticos
- Lee el Serial del localStorage
- Envía al main process

**`src/main/index.js`** (modificado)
- Handler `track_version` que recibe del renderer
- Hace la petición HTTPS al backend con el Serial en el header

**`src/renderer/main.js`** (modificado)
- Inicia el VersionTracker automáticamente
- Se activa 10 segundos después de cargar la app

## 🚀 Instalación

### Paso 1: Ejecutar el SQL
```sql
-- En tu base de datos CENTRALIZADA (no en cada local)
mysql> source crear_tabla_app_version_tracking.sql
```

### Paso 2: Subir archivos PHP al servidor
```bash
# Subir estos archivos a tu servidor
track_version.php       -> https://posfagotto.cl/track_version.php
get_app_versions.php    -> https://posfagotto.cl/get_app_versions.php
ver_versiones.html      -> https://posfagotto.cl/ver_versiones.html
```

### Paso 3: Recompilar la aplicación
```bash
npm run build
# O el comando que uses para compilar
```

### Paso 4: Distribuir la nueva versión
- Cuando los negocios actualicen a esta versión, empezarán a reportar automáticamente
- Los que no actualicen... ¡lo verás en el dashboard! 😄

## 📱 Cómo Usar

### Ver el Dashboard
Abre en tu navegador:
```
https://posfagotto.cl/ver_versiones.html
```

Verás:
- **Total Negocios**: Cuántos están registrados
- **Actualizados**: Con la última versión
- **Desactualizados**: Versiones antiguas pero conectados
- **Sin Conexión**: Más de 1 hora sin reportar

### Entender los Estados

🟢 **Verde (Actualizado)**
- Tiene la última versión disponible
- Reportó en los últimos 10 minutos

🟡 **Amarillo (Desactualizado)**
- No tiene la última versión
- Pero está conectado (reportó en la última hora)

🔴 **Rojo (Offline)**
- No ha reportado en más de 1 hora
- Puede estar cerrado o sin internet

## 🧪 Testing

### En desarrollo (antes de compilar)
Abre la consola del DevTools:
```javascript
// Ver el tracker
window.versionTracker

// Enviar ping manual
window.versionTracker.sendManualPing()

// Ver el serial
localStorage.getItem('912338BA')
```

### Verificar que funciona
1. Abre la app en un negocio
2. Espera 10 segundos
3. En el dashboard web deberías ver aparecer el negocio
4. Cada 5 minutos se actualizará `last_ping`

## 🔧 Configuración

### Cambiar la frecuencia de reporte
En `src/renderer/helpers/VersionTracker.js`:
```javascript
this.TRACKING_INTERVAL_MS = 5 * 60 * 1000; // 5 minutos
// Cambiar a lo que necesites
```

### Cambiar el delay inicial
En `src/renderer/main.js`:
```javascript
setTimeout(() => {
  VersionTracker.startTracking();
}, 10000); // 10 segundos
// Cambiar a lo que necesites
```

### Cambiar la URL del endpoint
En `src/main/index.js`:
```javascript
const trackUrl = 'https://posfagotto.cl/track_version.php';
// Cambiar si usas otro dominio
```

## 🎨 Personalizar el Dashboard

El archivo `ver_versiones.html` es standalone (HTML + CSS + JS todo en uno).
Puedes personalizar:
- Colores en el `<style>`
- Frecuencia de auto-refresh (línea final del `<script>`)
- Estadísticas mostradas

## 📊 Consultas SQL Útiles

### Ver todos los negocios y sus versiones
```sql
SELECT app_id, app_name, current_version, last_ping 
FROM app_version_tracking 
ORDER BY last_ping DESC;
```

### Ver quiénes NO están en la última versión
```sql
SELECT app_id, app_name, current_version 
FROM app_version_tracking 
WHERE current_version != '1.11.42'  -- Cambiar por tu versión actual
ORDER BY app_name;
```

### Ver quiénes no se han conectado hoy
```sql
SELECT app_id, app_name, last_ping 
FROM app_version_tracking 
WHERE DATE(last_ping) < CURDATE()
ORDER BY last_ping DESC;
```

### Historial de actualizaciones de un negocio
```sql
SELECT * FROM app_version_history 
WHERE app_id = 97  -- ID del negocio
ORDER BY updated_at DESC;
```

## 🔒 Seguridad

✅ Usa el sistema de autenticación existente (Serial en header `App-Key`)
✅ Mismo middleware que merchise y arqueo de caja (`AppSecurity`)
✅ No expone información sensible
✅ Solo registra: app_id, nombre, versión, fecha

## 🐛 Troubleshooting

### "No aparece mi negocio en el dashboard"
1. Verifica que el negocio tenga Serial en localStorage
2. Abre consola y busca: `📊 Version tracking activado`
3. Verifica en Network tab que la petición se haga
4. Revisa los logs del servidor PHP

### "Sale error 401 o 404"
1. Verifica que `track_version.php` esté en el servidor
2. Verifica que el header `App-Key` se esté enviando
3. Verifica que el Serial exista en la tabla `Aplication`

### "No se actualiza el last_ping"
1. Verifica que la app esté abierta
2. Espera 5 minutos (es el intervalo)
3. Fuerza un ping: `window.versionTracker.sendManualPing()`

## 💡 Próximas Mejoras

Ideas para el futuro:
- [ ] Notificaciones cuando un negocio no se conecta en X días
- [ ] Forzar actualización remota para negocios específicos
- [ ] Gráficos de adopción de versiones en el tiempo
- [ ] Exportar reportes a Excel
- [ ] Ver información de hardware/sistema de cada negocio

## 🎉 ¡Listo!

Ahora tienes control total sobre qué versión usa cada negocio.
Ya no más "actualicé pero no sé si funcionó" 😄
