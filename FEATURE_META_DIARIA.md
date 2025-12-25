# Feature: Meta Diaria en Órdenes Activas

## 📋 Descripción
Se agregó un footer en el panel de "Órdenes Activas" que muestra la meta diaria establecida para el local actual del mes en curso.

## 🎯 Características Implementadas

### 1. Backend (Laravel/PHP)
- **Nuevo Endpoint**: `GET /api/metas-locales/current`
  - Obtiene la meta diaria del local actual
  - Usa `CurrentApp::App()` para identificar el local
  - Retorna la meta del mes/año actual
  - Ubicación: [Server app/app/Http/Controllers/MetaLocalController.php](Server%20app/app/Http/Controllers/MetaLocalController.php#L110-L148)

### 2. Frontend (Vue.js)
- **Nuevo Módulo Vuex**: `store/metas/`
  - `state.js`: Estado de la meta actual
  - `actions.js`: Acción `fetchCurrentMeta` para obtener la meta desde el API
  - `mutations.js`: Mutación `SET_CURRENT_META`
  - `getters.js`: Getter `getCurrentMeta`

- **Componente Modificado**: [src/renderer/components/boardsActives.vue](src/renderer/components/boardsActives.vue)
  - Footer con diseño elegante y degradado
  - Muestra la meta con formato de moneda
  - Muestra mensaje de alerta si no hay meta configurada
  - Se carga automáticamente al montar el componente

### 3. Diseño Visual
- Footer fijo en la parte inferior del panel
- Gradiente de fondo (#f8f9fa → #e9ecef)
- Icono 📊 para la meta
- Icono ⚠️ si no hay meta configurada
- Valor destacado en color primario
- Responsive y consistente con el diseño existente

## 🚀 Uso

### Configurar Meta Diaria
1. Desde el panel web de administración, ir a "Metas Diarias"
2. Seleccionar el local y mes
3. Ingresar la meta diaria (ejemplo: $500,000)
4. Guardar

### O usar SQL directamente:
```sql
-- Insertar o actualizar meta para Fagotto Las Condes (ID: 116)
INSERT INTO easyerp.metas_locales (aplication_id, mes, anio, meta_diaria, created_at, updated_at)
VALUES 
  (116, 12, 2025, 500000.00, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
  meta_diaria = 500000.00,
  updated_at = NOW();
```

### Ver la Meta en la Aplicación
1. Abrir la aplicación Electron
2. Ir al panel de Cafetería / Órdenes Activas
3. Ver el footer en la parte inferior mostrando:
   - "📊 Meta del Día: $500,000" (si hay meta configurada)
   - "⚠️ Sin meta configurada" (si no hay meta)

## 🗂️ Archivos Modificados/Creados

### Backend
- ✅ [Server app/app/Http/Controllers/MetaLocalController.php](Server%20app/app/Http/Controllers/MetaLocalController.php) - Nuevo método `getCurrentLocalMeta()`
- ✅ [Server app/routes/api.php](Server%20app/routes/api.php#L44) - Nueva ruta `/metas-locales/current`

### Frontend
- ✅ [src/renderer/store/metas/index.js](src/renderer/store/metas/index.js) - Módulo principal
- ✅ [src/renderer/store/metas/state.js](src/renderer/store/metas/state.js) - Estado
- ✅ [src/renderer/store/metas/actions.js](src/renderer/store/metas/actions.js) - Acciones
- ✅ [src/renderer/store/metas/mutations.js](src/renderer/store/metas/mutations.js) - Mutaciones
- ✅ [src/renderer/store/metas/getters.js](src/renderer/store/metas/getters.js) - Getters
- ✅ [src/renderer/store/index.js](src/renderer/store/index.js) - Registro del módulo metas
- ✅ [src/renderer/components/boardsActives.vue](src/renderer/components/boardsActives.vue) - Footer con meta

### SQL
- ✅ [insertar_meta_prueba.sql](insertar_meta_prueba.sql) - Script de prueba

## 🔧 Detalles Técnicos

### Flujo de Datos
1. El componente `boardsActives.vue` se monta
2. Se dispara la acción `metas/fetchCurrentMeta`
3. Se hace un GET a `/api/metas-locales/current`
4. El backend obtiene el ID del local desde `CurrentApp::App()`
5. Busca la meta del mes/año actual en `easyerp.metas_locales`
6. Retorna la meta al frontend
7. Se actualiza el estado de Vuex
8. El footer se renderiza con la meta

### Estructura de Respuesta API
```json
{
  "success": true,
  "meta": {
    "id": 1,
    "aplication_id": 116,
    "mes": 12,
    "anio": 2025,
    "meta_diaria": "500000.00",
    "created_at": "2025-12-20 10:00:00",
    "updated_at": "2025-12-20 10:00:00"
  },
  "local_name": "Fagotto Las Condes"
}
```

## 📝 Notas
- La meta se obtiene automáticamente cada vez que se abre el panel de órdenes activas
- El mes y año se obtienen del servidor (fecha actual)
- Si no hay meta configurada para el mes actual, se muestra un mensaje de alerta
- El formato de moneda usa el helper `FormatNumber` existente del proyecto

## 🎨 Mejoras Futuras (Opcionales)
- [ ] Actualizar la meta automáticamente cada X minutos
- [ ] Mostrar progreso de ventas vs meta (barra de progreso)
- [ ] Mostrar porcentaje de cumplimiento
- [ ] Animación al cargar la meta
- [ ] Notificación cuando se alcanza la meta

---
**Fecha de Implementación**: 20 de Diciembre, 2025
**Desarrollado para**: Fagotto ERP - Sistema de Gestión
