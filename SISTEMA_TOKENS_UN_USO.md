# Sistema de Tokens de Un Solo Uso - Registro Biométrico

## 📋 Implementación Completa

### 🔧 Configuración de Base de Datos

1. **Ejecutar el script SQL**:
   ```bash
   mysql -u usuario -p nombre_bd < database/add_registro_tokens.sql
   ```

2. **Estructura de Tablas**:

   **Tabla `registro_tokens`**:
   - `id`: ID auto-incremental
   - `token`: Token único del link
   - `employee_id`: ID del empleado
   - `tipo_uso`: 'registro' o 'asistencia'
   - `usado`: 0 (no usado) o 1 (usado)
   - `usado_fecha`: Fecha y hora de uso
   - `usado_ip`: IP desde donde se usó
   - `usado_user_agent`: Navegador usado
   - `fecha_creacion`: Fecha de creación del token
   - `fecha_expiracion`: Fecha de expiración (opcional)

   **Columnas añadidas a `employees`**:
   - `registro_token`: Token asociado al empleado
   - `token_usado`: 0 (no usado) o 1 (usado)
   - `token_fecha_uso`: Fecha de uso del token

### 🔐 Funcionamiento

#### Flujo de Registro:
1. **Admin genera link**: `https://fagotto.cl/qrregister.php?token=ABC123XYZ`
2. **Empleado abre el link** por primera vez:
   - ✅ Muestra formulario de registro
   - ✅ Permite captura facial
   - ✅ Al completar exitosamente, marca token como usado
3. **Si alguien intenta reenviar el link**:
   - ❌ Muestra página de error "Link Ya Usado"
   - ❌ No permite registro
   - ❌ Sugiere contactar al supervisor

#### Verificaciones de Seguridad:
- ✅ Token debe existir en BD
- ✅ Token no debe estar marcado como usado
- ✅ Empleado no debe tener rostro ya registrado
- ✅ Se registra IP y User-Agent del dispositivo
- ✅ Se guarda timestamp exacto del uso

### 📱 Página de Error

Cuando un link ya fue usado, se muestra:
- ⚠️ Header rojo con icono de prohibido
- 📋 Mensaje claro: "Link Ya Usado"
- 🔍 Razones del bloqueo
- 💡 Soluciones sugeridas
- 📞 Contacto para soporte

### 🎨 Diseño

#### Estilos Implementados:
- **Header error**: Gradiente rojo (#ef4444 → #dc2626)
- **Icono pulsante**: Animación errorPulse con shadow expansivo
- **Mensaje de error**: Fondo rojo claro con borde
- **Razones**: Fondo azul con iconos verdes de check
- **Soluciones**: Cajas verdes y amarillas con bordes laterales
- **Contacto**: Caja gris con icono de auriculares
- **Responsive**: Adaptado para móviles

### 🔄 Integración con Backend

#### Archivo: `api/marcar-token-usado.php`

**Request**:
```json
{
  "token": "ABC123XYZ",
  "employeeId": "EMP001"
}
```

**Response Exitoso**:
```json
{
  "success": true,
  "message": "Token marcado como usado exitosamente",
  "data": {
    "employeeId": "EMP001",
    "fecha_uso": "2025-12-26 14:30:00"
  }
}
```

**Response Error**:
```json
{
  "success": false,
  "message": "Error al marcar token: [detalles]"
}
```

### 📝 Logs del Sistema

Cada uso de token se registra en el log del servidor:
```
[REGISTRO] Token usado - Employee: EMP001, Token: ABC123XYZ..., IP: 192.168.1.100, Fecha: 2025-12-26 14:30:00
```

### 🛡️ Seguridad

#### Prevención de Reutilización:
1. **Verificación en PHP**: Antes de mostrar formulario
2. **Marca en BD**: Después de registro exitoso
3. **Transacción atómica**: Garantiza integridad
4. **Log de auditoría**: Trackea todos los usos

#### Información Capturada:
- Token usado
- ID del empleado
- IP del dispositivo
- User-Agent del navegador
- Timestamp preciso

### 🔗 Generación de Links

Para generar un nuevo link de registro:

```php
// Generar token único
$token = bin2hex(random_bytes(32));

// Guardar en BD
$stmt = $pdo->prepare("
    INSERT INTO registro_tokens (token, employee_id, tipo_uso)
    VALUES (?, ?, 'registro')
");
$stmt->execute([$token, $employeeId]);

// Actualizar employee
$stmt2 = $pdo->prepare("
    UPDATE employees 
    SET registro_token = ?
    WHERE id = ?
");
$stmt2->execute([$token, $employeeId]);

// Generar QR y URL
$url = "https://fagotto.cl/qrregister.php?token=" . $token;
```

### ✅ Checklist de Implementación

- [x] Script SQL creado
- [x] Tabla `registro_tokens` definida
- [x] Columnas en `employees` agregadas
- [x] Verificación de token en `qrregister.php`
- [x] Página de error implementada
- [x] API para marcar token usado
- [x] Estilos CSS completos
- [x] Logs de auditoría
- [x] Captura de IP y User-Agent
- [x] Transacciones atómicas
- [x] Responsive design

### 🚀 Próximos Pasos

1. Ejecutar script SQL en producción
2. Probar flujo completo de registro
3. Verificar que tokens se marquen como usados
4. Confirmar que links usados muestren error
5. Revisar logs de auditoría

### 📞 Soporte

Si un empleado necesita un nuevo link:
1. Admin verifica en BD si token fue usado
2. Admin genera nuevo token único
3. Admin envía nuevo QR al empleado
4. Empleado completa registro con nuevo link

---

**Fecha de Implementación**: 26 de Diciembre, 2025
**Versión**: 1.0.0
**Sistema**: Fagotto ERP - Registro Biométrico
