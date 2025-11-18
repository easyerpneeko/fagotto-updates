# Instrucciones para Activar el Módulo Gelateria

## 📋 Resumen
Este módulo permite activar/desactivar el botón "Gelateria" en el sistema de ventas desde el panel de administración.

## 🔧 Pasos de Instalación

### 1. Ejecutar el Script SQL
Ejecuta el archivo SQL en tu base de datos para crear la tabla de configuración y el submódulo:

```bash
mysql -u tu_usuario -p tu_base_de_datos < create_gelateria_module.sql
```

O desde phpMyAdmin/MySQL Workbench, importa el archivo `create_gelateria_module.sql`

### 2. Archivos Creados

#### Backend (Laravel):
- **Controller**: `app/Http/Controllers/Admin/GelateriaConfigController.php`
  - Métodos:
    - `index()` - Vista de configuración
    - `update()` - Actualizar estado
    - `getStatus()` - API para obtener estado

- **Vista Blade**: `resources/views/admin/gelateria/config.blade.php`
  - Interfaz de administración con switch de activación/desactivación
  - Muestra información de las categorías (55, 56, 57, 58)

#### Frontend (Vue.js):
- **Modificado**: `src/renderer/components/modals/cafeteria/catalog.vue`
  - Agregado: `gelateriaActive` en data
  - Agregado: `checkGelateriaStatus()` método
  - Agregado: `v-if="gelateriaActive"` en botón Gelateria

#### Rutas:
- **API Routes** (`routes/api.php`):
  ```php
  // Admin (con JWT)
  Route::get('/gelateria/config', 'Admin\GelateriaConfigController@index');
  Route::post('/gelateria/config/update', 'Admin\GelateriaConfigController@update');
  Route::get('/gelateria/status', 'Admin\GelateriaConfigController@getStatus');
  
  // Local (sin JWT, solo App-Key)
  Route::get('/local/gelateria/status', 'Admin\GelateriaConfigController@getStatus');
  ```

### 3. Estructura de la Base de Datos

#### Tabla: `gelateria_config`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | int(11) | ID único (siempre 1) |
| is_active | tinyint(1) | Estado: 0=Inactivo, 1=Activo |
| created_at | timestamp | Fecha de creación |
| updated_at | timestamp | Última actualización |

#### Tabla: `settings_submodules`
Se agrega un nuevo registro:
- **name**: Gelateria
- **description**: Activar o desactivar el módulo de venta de copas, barquillos y postres de gelateria
- **route**: /admin/gelateria/config
- **icon**: fas fa-ice-cream
- **module_id**: 79 (Gestión de Ventas)

### 4. Acceder a la Configuración

1. Ir al panel de administración
2. Navegar a: **Gestión de Ventas → Gelateria**
3. URL directa: `https://posfagotto.cl/admin/gelateria/config`

### 5. Cómo Funciona

#### Activar el Módulo:
1. En el admin, ve a la página de configuración de Gelateria
2. Activa el switch "Estado del Módulo"
3. Haz clic en "Guardar Configuración"
4. El botón Gelateria aparecerá en el POS

#### Desactivar el Módulo:
1. En el admin, desactiva el switch
2. Guarda la configuración
3. El botón Gelateria desaparecerá del POS

### 6. Flujo de Funcionamiento

```
┌─────────────────────────────────────────────┐
│  Admin Panel (config.blade.php)             │
│  ┌─────────────────────────────────────┐   │
│  │  Switch: [ON/OFF]                   │   │
│  │  Guardar Configuración              │   │
│  └─────────────────────────────────────┘   │
│              ↓                              │
│  GelateriaConfigController::update()        │
│              ↓                              │
│  UPDATE gelateria_config SET is_active=1/0  │
└─────────────────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────┐
│  POS Frontend (catalog.vue)                 │
│  mounted() {                                │
│    checkGelateriaStatus()                   │
│  }                                          │
│              ↓                              │
│  GET /api/local/gelateria/status            │
│              ↓                              │
│  gelateriaActive = response.is_active       │
│              ↓                              │
│  <button v-if="gelateriaActive">            │
│    Gelateria                                │
│  </button>                                  │
└─────────────────────────────────────────────┘
```

### 7. Categorías del Sistema

Asegúrate de tener productos en estas categorías:

| ID | Nombre | Descripción |
|----|--------|-------------|
| 55 | Copas | Helados en copa |
| 56 | Barquillos | Helados en barquillo |
| 57 | Postres | Postres y dulces |
| 58 | Extras | Salsas y extras adicionales |

### 8. Testing

1. **Verificar la tabla**:
   ```sql
   SELECT * FROM gelateria_config;
   ```

2. **Probar el API**:
   ```bash
   curl -X GET "https://posfagotto.cl/api/local/gelateria/status" \
        -H "App-Key: tu_app_key"
   ```

3. **En el POS**:
   - Con módulo activo: Botón Gelateria visible
   - Con módulo inactivo: Botón Gelateria oculto

### 9. Solución de Problemas

#### El botón no aparece/desaparece:
1. Verifica que el SQL se ejecutó correctamente
2. Revisa la consola del navegador (F12) para ver el log: `🍦 Estado Gelateria: true/false`
3. Verifica que la ruta API responde correctamente
4. Recarga la página del POS (Ctrl+R)

#### Error al guardar la configuración:
1. Verifica que el controller está en la ruta correcta
2. Revisa los permisos de la base de datos
3. Chequea el log de Laravel: `storage/logs/laravel.log`

### 10. Mantenimiento

- **Reiniciar estado**: Ejecutar el SQL nuevamente
- **Limpiar caché**: `php artisan cache:clear`
- **Ver logs**: Buscar en consola del navegador y Laravel logs

## 🎉 ¡Listo!

El módulo Gelateria ahora puede ser activado/desactivado desde el panel de administración.
