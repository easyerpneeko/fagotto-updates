# Sistema de Pedido Final - Precios Centralizados

## 📋 Descripción

Sistema centralizado de gestión de precios de productos para pedidos finales. Permite mantener una única fuente de verdad para los precios, evitando el hardcodeo y facilitando las actualizaciones.

## 🎯 Características

- ✅ **Base de datos centralizada**: Todos los precios en la tabla `pedidofinal_precios` de la DB maestra (easyerp)
- ✅ **Actualización única**: Modifica precios en un solo lugar y se replican en todos los locales
- ✅ **Interfaz visual**: Vista amigable para consultar precios en tiempo real
- ✅ **Filtros y búsqueda**: Filtra por categoría o busca productos específicos
- ✅ **Categorización**: Productos organizados por empaque, salsas, ciabattas, etc.
- ✅ **Estados**: Productos activos/inactivos para control de disponibilidad
- ✅ **Timestamps**: Registro automático de última actualización

## 📁 Archivos Creados

### 1. Base de Datos
**`database/pedidofinal.sql`**
- Crea la tabla `pedidofinal_precios` en la DB maestra (easyerp)
- Incluye datos iniciales de productos según la imagen proporcionada
- Estructura:
  - `id`: Identificador único
  - `producto`: Nombre del producto
  - `unidad_venta`: Cantidad por unidad de venta
  - `unidad_medida`: Tipo de medida (Bolsa, Caja, unidad)
  - `precio_por_unidad`: Precio en pesos chilenos
  - `categoria`: empaque, salsas, ciabatta, general
  - `activo`: Estado del producto (1=activo, 0=inactivo)
  - `fecha_actualizacion`: Timestamp automático

### 2. API Backend
**`consultar_precios_pedido.php`**
- API RESTful para consultar y actualizar precios
- Endpoint: `https://api.posfagotto.cl/v2/consultar_precios_pedido.php`
- Acciones disponibles:
  - `obtener_precios`: Obtiene todos los precios
  - `obtener_por_categoria`: Filtra por categoría
  - `obtener_producto`: Obtiene un producto específico
  - `actualizar_precio`: Actualiza el precio de un producto

### 3. Componente Vue
**`src/renderer/views/pedidofinal.vue`**
- Interfaz visual para consultar precios
- Features:
  - Tabla con todos los productos y precios
  - Búsqueda en tiempo real
  - Filtros por categoría (Todos, Empaque, Salsas, Ciabattas)
  - Actualización automática cada 5 minutos
  - Resumen de productos activos/inactivos
  - Diseño responsive

### 4. Ruta
**`src/renderer/router/index.js`**
- Ruta agregada: `/inicio/pedido-final`
- No requiere turno activo
- Accesible desde el menú principal

## 🚀 Instalación

### Paso 1: Instalar la Base de Datos

```bash
# En phpMyAdmin de la DB maestra (easyerp)
# Ejecutar el archivo: database/pedidofinal.sql
```

O desde MySQL CLI:
```bash
mysql -u root -p easyerp < database/pedidofinal.sql
```

### Paso 2: Subir API al Servidor

```bash
# Copiar el archivo a tu servidor API
scp consultar_precios_pedido.php usuario@api.posfagotto.cl:/var/www/html/v2/
```

**Importante**: Ajusta las credenciales de la base de datos en el archivo PHP:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'easyerp');
```

### Paso 3: Compilar la Aplicación

```bash
# En el proyecto Vue
npm run build
```

## 📊 Productos Incluidos

### Empaque
- Mezcla: 2.90 por Bolsa → $11,835
- Vaso: 500.00 por Caja 500 → $61,000
- Sobre de tenedor: 500.00 por Caja 500 → $32,400

### Salsas
- Salsa Boloñesa: 2.00 por Bolsa 2k → $9,604
- Queso: 1.00 por Bolsa 1k → $10,668
- Salsa Alfredo: 2.00 por Bolsa 2k → $8,603
- Salsa Camarón: 2.00 por Bolsa 2k → $13,840
- Salsa Champiñón: 2.00 por Bolsa 2k → $13,869
- Salsa Pesto: 2.00 por Bolsa 2k → $37,125

### Ciabattas
- Ciabatta Pesto: 1.00 por unidad → $1,597
- Ciabatta Queso Crema Salame: 1.00 por unidad → $1,597
- Ciabatta Aliato: 1.00 por unidad → $1,080

## 🔧 Uso

### Desde la Aplicación

1. Abre la aplicación ERP
2. Navega a **Pedido Final** desde el menú
3. Consulta los precios en tiempo real
4. Usa los filtros para encontrar productos específicos
5. La tabla se actualiza automáticamente cada 5 minutos

### Actualizar Precios

#### Opción 1: Directamente en la DB (Recomendado)
```sql
UPDATE pedidofinal_precios 
SET precio_por_unidad = 15000 
WHERE producto = 'Salsa Pesto';
```

#### Opción 2: Mediante API
```javascript
axios.post('https://api.posfagotto.cl/v2/consultar_precios_pedido.php', {
  action: 'actualizar_precio',
  id: 9,
  precio: 15000
});
```

## 🎨 Capturas de Pantalla

La interfaz muestra:
- ✅ Tabla ordenada por categorías
- ✅ Colores distintivos por tipo de producto
- ✅ Badges para estado activo/inactivo
- ✅ Formato de precios en pesos chilenos
- ✅ Última fecha de actualización
- ✅ Resumen de totales

## 🔄 Flujo de Datos

```
DB Maestra (easyerp)
      ↓
pedidofinal_precios (tabla)
      ↓
API PHP (consultar_precios_pedido.php)
      ↓
Componente Vue (pedidofinal.vue)
      ↓
Usuario Final
```

## 🛡️ Seguridad

- ✅ Validación de datos en API
- ✅ Prepared statements para prevenir SQL injection
- ✅ CORS configurado correctamente
- ✅ Manejo de errores robusto
- ⚠️ Implementar autenticación en producción

## 📝 Notas Importantes

1. **Sin hardcodeo**: Nunca más necesitas hardcodear precios en el código
2. **Actualización instantánea**: Los cambios se reflejan en tiempo real
3. **Multi-local**: Todos los locales consultan la misma DB maestra
4. **Histórico**: El campo `fecha_actualizacion` registra cambios automáticamente
5. **Escalable**: Fácil agregar nuevos productos o categorías

## 🐛 Troubleshooting

### Error de conexión a la DB
```
Verificar credenciales en consultar_precios_pedido.php
Verificar que la tabla existe: SHOW TABLES LIKE 'pedidofinal_precios';
```

### Productos no aparecen
```
Verificar que productos tengan activo = 1
Revisar logs del navegador (F12 → Console)
Verificar que la API esté accesible
```

### Precios no se actualizan
```
Hacer clic en botón "Actualizar"
Verificar conexión a internet
Limpiar caché del navegador
```

## 🚀 Mejoras Futuras

- [ ] Función de edición inline en la tabla
- [ ] Historial de cambios de precios
- [ ] Importar/Exportar precios en Excel
- [ ] Notificaciones de cambios de precios
- [ ] Control de acceso por roles
- [ ] Gráficos de evolución de precios

## 📞 Soporte

Para soporte o consultas, contactar al equipo de desarrollo.

---
**Creado**: Diciembre 2025  
**Versión**: 1.0.0  
**Patrón**: Similar a registro-asistencia (DB maestra centralizada)
