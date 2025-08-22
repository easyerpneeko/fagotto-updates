# Sistema de Arqueo de Caja

## Descripción
El sistema de Arqueo de Caja permite realizar conteos de efectivo diarios de manera organizada y controlada, comparando el dinero físico contado con las ventas en efectivo registradas en el sistema.

## ¿Qué es un Arqueo de Caja?

Un **Arqueo de Caja** es el proceso de contar físicamente todo el dinero en efectivo disponible en caja (billetes y monedas) y compararlo con las ventas registradas en el sistema. Esto permite:

- ✅ **Control financiero**: Verificar que el dinero físico coincida con los registros
- ✅ **Detección de faltantes o sobrantes**: Identificar diferencias entre lo contado y lo vendido
- ✅ **Auditoría diaria**: Mantener un registro histórico de los arqueos
- ✅ **Responsabilidad**: Asignar el arqueo a un usuario específico con fecha y hora

## Funcionalidades Principales

### 🧮 Conteo por Denominaciones
- Billetes: $20.000, $10.000, $5.000, $2.000, $1.000
- Monedas: $500, $100, $50, $10, $5, $1
- Cálculo automático de subtotales por denominación
- Total general calculado automáticamente

### 📊 Comparación con Ventas
- Obtiene automáticamente el total de ventas en efectivo del día
- Calcula la diferencia entre lo contado y lo vendido
- Indica si hay sobrante, faltante o coincidencia exacta

### 👤 Información de Responsabilidad
- **Usuario**: Quién realiza el arqueo
- **Fecha**: Cuándo se realiza el conteo
- **Negocio**: En qué establecimiento se realiza
- **Observaciones**: Notas adicionales sobre el arqueo

### 📄 Generación de Reportes
- Impresión del resumen de arqueo
- Historial de arqueos anteriores
- Reportes por períodos de tiempo

## Instalación

### 1. Ejecutar Script de Base de Datos

```sql
-- Para instalación nueva
mysql -u usuario -p nombre_db < install_arqueo_caja.sql

-- Para actualizar tabla existente
mysql -u usuario -p nombre_db < migration_update_arqueo_caja.sql
```

### 2. Verificar Permisos
Asegurarse de que el usuario tenga los permisos:
- `gestionar_arqueo`: Para crear, editar y eliminar arqueos
- `obtener_arqueo`: Para consultar arqueos y generar reportes

### 3. Configurar en el Menú
El sistema se integra automáticamente en el menú principal cuando el usuario tiene los permisos correspondientes.

## Uso del Sistema

### Realizar un Nuevo Arqueo

1. **Acceder al módulo**: Ir a "Ventas" → "Arqueo de Caja"
2. **Contar denominaciones**: Ingresar la cantidad de billetes y monedas
3. **Revisar información**: Verificar usuario, fecha y negocio
4. **Agregar observaciones**: Opcional, notas sobre el arqueo
5. **Guardar**: Confirmar el arqueo

### Interpretar Resultados

- **Diferencia = 0**: ✅ Arqueo exacto (ideal)
- **Diferencia > 0**: ⚠️ Hay sobrante de dinero
- **Diferencia < 0**: ❌ Hay faltante de dinero

### Consultar Historial

- Ver arqueos anteriores
- Filtrar por fechas
- Exportar reportes

## Estructura de Archivos

### Frontend (Vue.js)
- `src/renderer/views/arqueo-caja.vue`: Componente principal
- `src/renderer/router/index.js`: Configuración de rutas

### Backend (Laravel)
- `app/Http/Controllers/Controllers_local/ArqueoCajaController.php`: Controlador
- `app/models_local/ArqueoCaja.php`: Modelo de datos
- `routes/api.php`: Rutas API

### Base de Datos
- `install_arqueo_caja.sql`: Script de instalación
- `migration_update_arqueo_caja.sql`: Script de actualización

## API Endpoints

### Públicos (con permisos)
- `GET /local/report/arqueo-resumen`: Resumen de ventas del día
- `GET /local/negocio/info`: Información del negocio

### Gestión (requiere `gestionar_arqueo`)
- `POST /local/arqueo/guardar`: Guardar nuevo arqueo
- `PUT /local/arqueo/{id}`: Actualizar arqueo existente
- `DELETE /local/arqueo/{id}`: Eliminar arqueo

### Consulta (requiere `obtener_arqueo`)
- `GET /local/arqueo/historial`: Historial de arqueos
- `GET /local/arqueo/fecha`: Arqueo por fecha específica
- `GET /local/arqueo/reporte`: Reporte por período

## Estructura de Datos

### Arqueo de Caja
```json
{
  "id": 1,
  "app_id": 1,
  "fecha_arqueo": "2024-01-15",
  "total_contado": 50000.00,
  "total_ventas_efectivo": 48500.00,
  "diferencia": 1500.00,
  "detalle_conteo": "{\"bill_20000\":{\"cantidad\":2,\"valor\":20000,\"subtotal\":40000}}",
  "observaciones": "Arqueo de cierre del día",
  "usuario_id": 1,
  "usuario_nombre": "Juan Pérez",
  "negocio_id": 1,
  "negocio_nombre": "Mi Tienda",
  "estado": "cerrado",
  "created_at": "2024-01-15T18:30:00.000000Z"
}
```

## Solución de Problemas

### Error: "No se puede acceder al módulo"
- Verificar que el usuario tenga los permisos necesarios
- Revisar configuración de módulos en el sistema

### Error: "No se pueden cargar las ventas"
- Verificar conexión a la base de datos
- Comprobar que existan registros de ventas para el día

### Diferencias frecuentes en arqueos
- Revisar procedimientos de manejo de efectivo
- Verificar que todas las ventas se registren correctamente
- Capacitar al personal en el uso del sistema

## Mantenimiento

### Respaldo de Datos
```sql
-- Exportar arqueos
SELECT * FROM arqueo_caja WHERE fecha_arqueo >= '2024-01-01';

-- Resumen mensual
SELECT 
    DATE_FORMAT(fecha_arqueo, '%Y-%m') as mes,
    COUNT(*) as total_arqueos,
    AVG(diferencia) as promedio_diferencia,
    SUM(total_contado) as total_contado_mes
FROM arqueo_caja 
GROUP BY DATE_FORMAT(fecha_arqueo, '%Y-%m')
ORDER BY mes DESC;
```

### Limpieza de Datos Antiguos
```sql
-- Eliminar arqueos antiguos (más de 2 años)
DELETE FROM arqueo_caja 
WHERE fecha_arqueo < DATE_SUB(CURDATE(), INTERVAL 2 YEAR);
```

## Soporte

Para soporte técnico o reportar problemas:
1. Revisar los logs de la aplicación
2. Verificar la configuración de permisos
3. Consultar este manual
4. Contactar al administrador del sistema

---

**Versión**: 1.0  
**Última actualización**: Enero 2024  
**Compatibilidad**: Vue.js 2.x, Laravel 8.x+
