# Sistema de Control de Stock - Pedido Final

## 📦 Descripción General

Sistema para gestionar el stock de productos de la tabla `pedidofinal_precios` (base de datos maestra `easyerp`). Este sistema reemplaza el antiguo control de stock local.

## 🎯 Características

- ✅ Control de stock centralizado desde DB maestra
- ✅ Validación automática de stock al crear pedidos
- ✅ Descuento automático de stock cuando se aprueba un pedido
- ✅ Historial completo de cambios de stock
- ✅ Interfaz web amigable para gestión de inventario
- ✅ Alertas visuales de stock bajo (rojo ≤5, amarillo ≤20)

## 🗄️ Base de Datos

### Tabla Principal: `pedidofinal_precios`
```sql
- id: Identificador único del producto
- producto: Nombre del producto
- precio: Precio unitario
- stock: Cantidad disponible (puede ser NULL si no tiene control de stock)
- unidad_medida: Unidad (ej: "Bolsa 2k", "Caja 500", "Unidad")
- categoria: Categoría del producto
```

### Tabla de Historial: `pedidofinal_stock_history`
```sql
- id: Identificador único del registro
- producto_id: ID del producto modificado
- producto_nombre: Nombre del producto
- stock_anterior: Stock antes del cambio
- stock_agregado: Cantidad agregada (positivo o negativo)
- stock_nuevo: Stock después del cambio
- usuario: Nombre del usuario que hizo el cambio
- fecha: Fecha y hora del cambio
```

## 🔌 API Endpoints

### 1. Obtener Productos con Stock
```
GET /api/local/pedidofinal/stock
```
**Respuesta:**
```json
[
  {
    "id": 1,
    "name": "Salsa Alfredo",
    "stock": 45,
    "unidad_medida": "Bolsa 2k",
    "precio": "15000",
    "categoria": "Salsas"
  }
]
```

### 2. Actualizar Stock de Producto
```
POST /api/local/pedidofinal/stock/{id}
```
**Body:**
```json
{
  "stock_added": 10  // Positivo para agregar, negativo para restar
}
```
**Respuesta:**
```json
{
  "success": true,
  "message": "Stock actualizado correctamente",
  "stock_anterior": 45,
  "stock_nuevo": 55
}
```

### 3. Obtener Historial de Cambios
```
GET /api/local/pedidofinal/stock/history
```
**Respuesta:**
```json
[
  {
    "id": 123,
    "producto_nombre": "Salsa Alfredo",
    "stock_anterior": 45,
    "stock_agregado": 10,
    "stock_nuevo": 55,
    "usuario": "Admin",
    "fecha": "2026-01-05 14:30:00"
  }
]
```

### 4. Crear Pedido Final (con validación de stock)
```
POST /api/local/request/pedido-final
```
**Validaciones automáticas:**
- ✅ Verifica que el producto existe en `pedidofinal_precios`
- ✅ Valida stock disponible si el producto tiene control de stock
- ✅ Descuenta automáticamente el stock al crear el pedido
- ✅ Usa transacciones para garantizar atomicidad

## 🖥️ Interfaz Web

### Acceso
```
https://tu-dominio.com/web/pages/stock.html
```

### Funcionalidades

1. **Vista de Productos**
   - Lista todos los productos con control de stock
   - Muestra stock actual con colores según nivel
   - Verde: Stock > 20 unidades
   - Amarillo: Stock entre 6-20 unidades
   - Rojo: Stock ≤ 5 unidades (crítico)

2. **Editar Stock**
   - **Modo Agregar**: Suma stock al existente
   - **Modo Establecer**: Define stock específico
   - Confirmación al reducir stock
   - Validación de valores positivos

3. **Historial de Cambios**
   - Muestra últimos 100 cambios
   - Fecha, hora y usuario del cambio
   - Stock anterior, nuevo y diferencia
   - Iconos visuales (↑ aumentó, ↓ disminuyó)

## 🔄 Flujo de Trabajo

### Crear Pedido
```
1. Usuario crea pedido en "Pedido Final"
   ↓
2. Sistema valida stock en pedidofinal_precios
   ↓
3. Si hay stock suficiente:
   - Crea el pedido
   - Descuenta stock automáticamente
   - Registra en historial
   ↓
4. Si NO hay stock:
   - Rechaza pedido
   - Muestra mensaje: "⚠️ [Producto]: solo quedan X [unidad]"
```

### Actualizar Stock Manualmente
```
1. Persona encargada accede a stock.html
   ↓
2. Busca el producto a actualizar
   ↓
3. Click en "Editar"
   ↓
4. Elige modo:
   - Agregar: +10 unidades
   - Establecer: Fijar a 50 unidades
   ↓
5. Sistema actualiza y registra en historial
```

## 👥 Roles y Permisos

- **Administrador**: Puede actualizar stock manualmente
- **Usuario Normal**: Solo puede crear pedidos (stock se descuenta automático)
- **Sistema**: Descuenta stock al aprobar pedidos

## 📋 Instalación

### 1. Ejecutar SQL
```bash
mysql -u root -p easyerp < database/crear_tabla_pedidofinal_stock_history.sql
```

### 2. Verificar Conexión
Asegurarse que existe la conexión `easyerp_master` en `config/database.php`:
```php
'easyerp_master' => [
    'driver' => 'mysql',
    'host' => env('DB_MASTER_HOST', '127.0.0.1'),
    'database' => 'easyerp',
    'username' => env('DB_MASTER_USERNAME', 'root'),
    'password' => env('DB_MASTER_PASSWORD', ''),
]
```

### 3. Subir Archivos
- `Server app/app/Http/Controllers/Controllers_local/RequestsController.php`
- `Server app/routes/api.php`
- `web/assets/js/stock.js`
- `web/pages/stock.html`

## 🐛 Troubleshooting

### Error: "Producto no encontrado"
- Verificar que el producto existe en `pedidofinal_precios`
- Confirmar que tiene la columna `stock` con valor

### Error: "Stock no puede ser negativo"
- Intentas restar más stock del disponible
- Verificar stock actual antes de actualizar

### Historial no muestra cambios
- Verificar que la tabla `pedidofinal_stock_history` existe
- Revisar permisos de la conexión a DB maestra

## 📞 Soporte

Para problemas o dudas, contactar al equipo de desarrollo.

---

**Última actualización**: 5 de enero de 2026
**Versión**: 1.0.0
