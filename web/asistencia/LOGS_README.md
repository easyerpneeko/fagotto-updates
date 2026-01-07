# 📊 Sistema de Logs en Tiempo Real - Registro de Empleados

## 🚀 Cómo usar

### 1. Ver logs en tiempo real
Abre en tu navegador:
```
http://tu-servidor/asistencia/ver-logs.html
```

### 2. Funcionalidades

- **Auto-refresh**: Los logs se actualizan automáticamente cada 2 segundos
- **Pausar/Reanudar**: Detén la actualización automática para revisar logs
- **Filtrar por nivel**: INFO, SUCCESS, WARNING, ERROR
- **Buscar**: Busca texto específico en los logs
- **Limpiar**: Borra todos los logs del archivo

### 3. Niveles de Log

- 🟢 **INFO**: Información general del proceso
- ✅ **SUCCESS**: Operación completada exitosamente
- 🟡 **WARNING**: Advertencias (no críticas)
- 🔴 **ERROR**: Errores que impiden el registro

## 🔍 Información que verás en los logs

### Durante el registro de un empleado:

1. **Inicio del proceso**
   - Request method, IP cliente, User Agent
   - Datos recibidos (nombre, rut, cargo, email)

2. **Validaciones**
   - Conexión a base de datos
   - Validación de sesión
   - Verificación de RUT duplicado
   - Verificación de email duplicado

3. **Generación de ID**
   - ID del último empleado
   - Nuevo ID generado

4. **Registro en AWS**
   - Intento de indexación facial
   - Resultado de AWS Rekognition (o mock si no está disponible)

5. **Inserción en BD**
   - Datos del empleado insertado
   - Face ID asignado

6. **Finalización**
   - Sesión marcada como usada
   - Respuesta enviada al cliente

## 🐛 Errores comunes y cómo identificarlos

### Error: "Sesión inválida o expirada"
**En logs verás:**
```
[ERROR] Sesión inválida o expirada
```
**Solución:** La sesión QR expiró o ya fue usada. Generar nuevo QR.

### Error: "RUT duplicado"
**En logs verás:**
```
[ERROR] RUT duplicado encontrado | {"rut": "12345678-9", "existing_id": "EMP001"}
```
**Solución:** El RUT ya está registrado.

### Error: "Email duplicado"
**En logs verás:**
```
[ERROR] Email duplicado encontrado | {"email": "test@example.com"}
```
**Solución:** El email ya está en uso.

### Error: "Error de base de datos"
**En logs verás:**
```
[ERROR] Error PDO (Base de datos) | {"message": "...", "code": "..."}
```
**Solución:** Verificar conexión y permisos de BD.

### Error: "JSON inválido recibido"
**En logs verás:**
```
[ERROR] JSON inválido recibido | {"json_error": "Syntax error"}
```
**Solución:** El cliente está enviando datos mal formateados.

## 📁 Archivos del sistema de logs

- `/asistencia/ver-logs.html` - Visualizador web de logs
- `/asistencia/api/get-logs.php` - API para obtener/limpiar logs
- `/asistencia/api/registrar-rostro.php` - API principal con logging
- `/asistencia/registro_empleados.log` - Archivo de log (se crea automáticamente)

## 💡 Tips

1. **Mantén los logs abiertos** mientras registras empleados para ver errores en tiempo real
2. **Usa el filtro ERROR** para ver solo problemas
3. **Busca por RUT o nombre** para seguir un registro específico
4. **Limpia los logs** periódicamente para mantener el rendimiento
5. **Aumenta las líneas** a 500 si necesitas ver más historial

## 🔧 Para desarrolladores

El logging usa una función helper `writeLog($level, $message, $data)`:

```php
writeLog('INFO', 'Mensaje descriptivo', ['key' => 'value']);
writeLog('ERROR', 'Descripción del error', ['error' => $e->getMessage()]);
```

Los logs se escriben en formato:
```
[2025-12-27 10:30:45] [INFO] Mensaje | {"data": "json"}
```

## 📞 Soporte

Si ves un error que no entiendes, copia el log completo y busca:
- El timestamp del error
- El nivel (ERROR)
- El mensaje descriptivo
- Los datos JSON asociados
