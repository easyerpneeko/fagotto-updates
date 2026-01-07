# 🚀 Sistema de Logs Instalado

## ✅ ¿Qué se instaló?

He agregado un **sistema completo de logging en tiempo real** para diagnosticar problemas al registrar empleados.

## 📺 Cómo ver los logs

### Opción 1: Página principal de logs
```
http://tu-servidor/asistencia/index-logs.html
```

### Opción 2: Ver logs directamente
```
http://tu-servidor/asistencia/ver-logs.html
```

## 🎯 ¿Cómo funciona?

1. **Abre la página de logs** en tu navegador
2. **Mantén la pestaña abierta** mientras intentas registrar un empleado
3. **Los logs aparecerán automáticamente** mostrando:
   - ✅ Pasos exitosos (en verde)
   - ⚠️ Advertencias (en amarillo)
   - ❌ Errores (en rojo)
   - ℹ️ Información general (en azul)

## 🔍 Ejemplo de uso

**Escenario:** Intentas registrar un empleado y falla

**Antes (sin logs):**
- No sabes qué pasó
- Error genérico en pantalla
- Pierdes tiempo adivinando

**Ahora (con logs):**
```
[2025-12-27 20:45:30] [INFO] === NUEVO INTENTO DE REGISTRO INICIADO ===
[2025-12-27 20:45:30] [INFO] Datos recibidos | {"nombre":"Juan Pérez","rut":"12345678-9"}
[2025-12-27 20:45:30] [INFO] Conectando a base de datos...
[2025-12-27 20:45:30] [INFO] Conexión a BD exitosa
[2025-12-27 20:45:31] [ERROR] RUT duplicado encontrado | {"rut":"12345678-9","existing_id":"EMP001"}
```

**Ahora sabes exactamente:** El RUT ya está registrado con el ID EMP001

## 🛠️ Funciones disponibles

### En el visualizador de logs:

1. **🔄 Refrescar** - Actualiza los logs manualmente
2. **⏸️ Pausar** - Detiene la actualización automática
3. **🗑️ Limpiar** - Borra todos los logs
4. **🔍 Filtrar** - Muestra solo INFO, WARNING o ERROR
5. **🔎 Buscar** - Busca texto específico (RUT, nombre, etc.)

## 📊 Niveles de log

| Nivel | Color | Significado |
|-------|-------|-------------|
| INFO | 🔵 Azul | Información del proceso |
| SUCCESS | 🟢 Verde | Operación exitosa |
| WARNING | 🟡 Amarillo | Advertencia (no crítico) |
| ERROR | 🔴 Rojo | Error que impide el registro |

## 🐛 Errores comunes que detectará

### 1. Sesión expirada
```
[ERROR] Sesión inválida o expirada | {"session_id":"REG-..."}
```
**Solución:** Generar nuevo código QR

### 2. RUT duplicado
```
[ERROR] RUT duplicado encontrado | {"rut":"12345678-9","existing_id":"EMP001"}
```
**Solución:** El empleado ya existe, usar otro RUT

### 3. Email duplicado
```
[ERROR] Email duplicado encontrado | {"email":"test@mail.com"}
```
**Solución:** Usar otro email

### 4. Datos incompletos
```
[ERROR] Datos incompletos en la petición | {"cargo":"❌ FALTA"}
```
**Solución:** Llenar todos los campos del formulario

### 5. Error de base de datos
```
[ERROR] Error PDO (Base de datos) | {"message":"Connection refused"}
```
**Solución:** Verificar que MySQL esté corriendo

## 📁 Archivos creados/modificados

```
✅ /asistencia/ver-logs.html          → Visualizador de logs
✅ /asistencia/index-logs.html        → Página principal
✅ /asistencia/test-logs.php          → Generar logs de prueba
✅ /asistencia/api/get-logs.php       → API para leer logs
✅ /asistencia/api/registrar-rostro.php → Mejorado con logging
✅ /asistencia/config.php             → Logging de conexión DB
✅ /asistencia/registro_empleados.log → Archivo de logs (auto)
✅ /asistencia/LOGS_README.md         → Documentación completa
```

## 🧪 Probar el sistema

### Paso 1: Generar logs de prueba
```
http://tu-servidor/asistencia/test-logs.php
```

### Paso 2: Ver los logs
```
http://tu-servidor/asistencia/ver-logs.html
```

Deberías ver logs de ejemplo con diferentes niveles (INFO, SUCCESS, WARNING, ERROR)

## 💡 Consejos profesionales

1. **Siempre abre los logs ANTES de registrar** un empleado problemático
2. **Usa el filtro ERROR** para ver solo problemas
3. **Busca por RUT o nombre** para seguir un caso específico
4. **Copia el log completo** si necesitas reportar un bug
5. **Limpia los logs** cada semana para mantener rendimiento

## 📱 Uso desde móvil

El visualizador funciona perfectamente en móviles y tablets. Puedes:
- Ver logs mientras registras desde otro dispositivo
- Usar split screen para ver logs y formulario
- Compartir URL con soporte técnico

## 🆘 Si algo no funciona

1. Verifica que el archivo exista:
   ```bash
   ls -la /var/www/asistencia/registro_empleados.log
   ```

2. Verifica permisos de escritura:
   ```bash
   chmod 666 /var/www/asistencia/registro_empleados.log
   ```

3. Verifica que PHP pueda escribir:
   ```bash
   php -r "error_log('test', 3, '/var/www/asistencia/registro_empleados.log');"
   ```

## 📞 Próximos pasos

1. **Abre:** `http://tu-servidor/asistencia/index-logs.html`
2. **Haz clic en:** "Ver Logs en Vivo"
3. **Intenta registrar** un empleado con problema
4. **Mira los logs** aparecer en tiempo real
5. **Identifica el error** exacto con el mensaje en rojo

---

**¡Listo! Ahora tienes visibilidad total de lo que pasa cuando registras empleados.** 🎉
