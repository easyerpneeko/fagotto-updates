# 📊 Centro de Costos - Documentación Rápida
**Versión 2.0** | Actualizado: 9 de Diciembre, 2025

---

## 🎉 ¿Qué cambios locos se hicieron?

### ✅ **1. Base de Datos Mejorada**
- ✨ **Nueva tabla `categorias`**: Ahora las categorías son dinámicas
- 🔄 **Migración automática**: De ENUM a Foreign Key
- 📝 **14 categorías predefinidas**: Alimentos, Aseo, Embalaje, Bebidas, Gas, Luz, Agua, Arriendo, Publicidad, Mantenimiento, Transporte, Personal, Tecnología, Otros
- 🎨 **Iconos y colores personalizables**: Cada categoría tiene su propio emoji y color

### ✅ **2. Gráficas Interactivas** 📈
- 🥧 **Gráfica de torta**: Distribución de costos por categoría
- 📉 **Gráfica temporal**: Evolución de costos día a día
- 🎨 **Colores personalizados**: Cada categoría tiene su color distintivo
- 🔄 **Actualización automática**: Las gráficas se actualizan con los filtros

### ✅ **3. Validaciones Mejoradas** 🛡️
- ✔️ **Verificación de documentos**: Valida que el archivo exista antes de mostrarlo
- 🔍 **Loading states**: Indicadores visuales de carga
- ⚠️ **Mensajes claros**: Errores y éxitos con SweetAlert2
- 📄 **Edición mejorada**: Puedes reemplazar el documento al editar

### ✅ **4. Interfaz Mejorada** 🎨
- 🎯 **Resumen en tabla**: Muestra cantidad de registros y total
- 📅 **Filtros rápidos mejorados**: Hoy, Semana, Mes, Trimestre, Año
- 🔄 **Botón actualizar**: Refresca los datos con animación
- ✨ **Animaciones suaves**: Efectos de entrada y hover
- 📱 **Responsive**: Se adapta a móviles y tablets

### ✅ **5. Funcionalidades Agregadas** 🚀
- 📊 **Comparación con ventas**: Ve qué % de tus ventas son costos
- 🎯 **Estados inteligentes**: Excelente, Aceptable, Alto, Crítico
- 💰 **Cálculo de utilidad bruta**: Ventas - Costos
- 📈 **Margen de utilidad**: % de ganancia
- 📋 **Exportar a Excel mejorado**: Con todos los filtros aplicados

---

## 🔧 ¿Qué archivos se modificaron?

### **1. SQL - Base de Datos**
📄 `/var/www/html/ct-migration-v2.sql`
- Crea tabla `categorias`
- Migra datos de ENUM a FK
- Inserta 14 categorías por defecto
- Backup de tabla antigua: `centro_costos_old_backup`

### **2. Frontend - HTML**
📄 `/var/www/html/pages/centro-costos.html`
- ✅ Corregido: `$('#filtroCategor铆a')` → `$('#filtroCategoria')`
- ➕ Agregado: Chart.js para gráficas
- ➕ Agregado: 2 canvas para gráficas (categorías y temporal)
- ➕ Agregado: Botones de filtro rápido (Trimestre, Año)
- ➕ Agregado: Botón actualizar con spinner
- ➕ Agregado: Resumen de tabla con totales
- 🔄 Mejorado: Función `verDocumento()` con validación
- 🔄 Mejorado: Función `editarCosto()` con reset de modal
- 🔄 Mejorado: Función `limpiarFormulario()` resetea título
- ➕ Agregado: Funciones `renderizarGraficas()`, `renderizarGraficaCategorias()`, `renderizarGraficaTemporal()`
- 🎨 Mejorado: Estilos con animaciones y efectos hover

### **3. Backend - API PHP**
📄 `/var/www/html/mixreporte/api/centro-costos.php`
- ➕ Agregado: Endpoint `grafica_temporal`
- ✅ Ya existía: Todo lo demás funcionaba bien

---

## 🚀 ¿Cómo usar las nuevas funcionalidades?

### **Gestionar Categorías** 📋
1. Click en el **➕** junto al select de Categoría al crear un costo
2. O click en **⚙️** para gestionar todas las categorías
3. Puedes agregar emoji, nombre y color personalizado
4. Las categorías con registros no se pueden eliminar

### **Ver Gráficas** 📊
- Las gráficas se actualizan automáticamente con los filtros
- **Gráfica de Torta**: Muestra % de cada categoría
- **Gráfica Temporal**: Muestra evolución día a día
- Hover sobre las gráficas para ver detalles

### **Comparación con Ventas** 💰
En la parte superior verás 4 tarjetas:
- 💰 **Total Ventas**: De la BD mixreporte
- 📊 **Total Costos**: De centro de costos
- 📈 **% Costos**: Cuánto % de ventas son costos
- 💵 **Utilidad Bruta**: Ventas - Costos

**Estados:**
- ✅ **Excelente**: < 20% costos
- ⚠️ **Aceptable**: 20-25% costos
- 🟠 **Alto**: 25-30% costos
- 🔴 **Crítico**: > 30% costos

### **Filtros Rápidos** 📅
- **Hoy**: Solo registros de hoy
- **Esta Semana**: Desde el domingo
- **Este Mes**: Desde el día 1
- **Trimestre**: Últimos 3 meses
- **Este Año**: Desde enero 1

### **Exportar Excel** 📥
- Click en **Exportar Excel**
- Descarga CSV con todos los registros filtrados
- Incluye: Fecha, Categoría, Descripción, Monto, Proveedor, Notas, Documento

---

## ⚠️ Consideraciones Importantes

### **Migración de Datos**
- ✅ Los registros antiguos se migraron automáticamente
- 🔒 Hay backup en `centro_costos_old_backup`
- 🎯 Categorías antiguas mapeadas:
  - `alimentos` → Categoría ID 1 (Alimentos)
  - `aseo` → Categoría ID 2 (Aseo)
  - `embalaje` → Categoría ID 3 (Embalaje)
  - `otros` → Categoría ID 14 (Otros)

### **Documentos**
- 📁 Directorio: `/var/www/html/uploads/centro-costos/`
- 📄 Formatos: PDF, JPG, PNG
- 📏 Tamaño máximo: 10MB
- 🔗 URL pública: `https://fagottoerp.cl/uploads/centro-costos/`

### **Permisos Requeridos**
- El servidor necesita permisos de escritura en `/var/www/html/uploads/centro-costos/`
- La BD `ct` debe existir con usuario `root` y password configurado

---

## 🐛 Bugs Corregidos

1. ✅ **Caracteres corruptos**: `$('#filtroCategor铆a')` → `$('#filtroCategoria')`
2. ✅ **Categorías ENUM**: Ahora son tabla dinámica con FK
3. ✅ **Documentos sin validar**: Ahora verifica que existan antes de mostrar
4. ✅ **Modal sin limpiar**: Ahora resetea título al cerrar/abrir
5. ✅ **Sin gráficas**: Ahora tiene 2 gráficas interactivas

---

## 📌 Próximas Mejoras Sugeridas

### **Funcionalidades Futuras**
- 📊 Dashboard ejecutivo con más KPIs
- 🔔 Alertas cuando costos superen % configurado
- 📸 OCR para leer datos de facturas automáticamente
- 💾 Backup automático de documentos
- 👥 Control de usuarios y permisos
- 📧 Reportes por email automáticos
- 🔍 Búsqueda avanzada con múltiples filtros
- 📱 App móvil nativa

---

## 🎯 Resumen de Cambios

| Categoría | Cambios |
|-----------|---------|
| **Base de Datos** | Nueva tabla categorías, migración de ENUM a FK, 14 categorías predefinidas |
| **Frontend** | Gráficas Chart.js, validaciones mejoradas, filtros rápidos extendidos |
| **Backend** | Endpoint para gráfica temporal |
| **UX/UI** | Animaciones, resumen de tabla, botón actualizar, estilos mejorados |
| **Bugs** | 5 bugs críticos corregidos |

---

## 🤝 Soporte

Si encuentras algún problema:
1. Revisa la consola del navegador (F12)
2. Verifica que la migración SQL se ejecutó correctamente
3. Confirma que el directorio de uploads tiene permisos
4. Verifica la conexión a la BD `ct` y `mixreporte`

---

**¡Centro de Costos está listo para producción! 🚀**

*Desarrollado con ❤️ para FagottoERP*
