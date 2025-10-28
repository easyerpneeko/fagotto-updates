# 🎯 SISTEMA DE DESCUENTOS POR SUCURSAL - GUÍA COMPLETA

## ✅ LO QUE SE CREÓ:

### 1️⃣ **BASE DE DATOS**
- ✅ Tabla `product_discounts_by_branch` 
- Ubicación SQL: `c:\xampp\htdocs\create_product_discounts_table.sql`

### 2️⃣ **BACKEND (Laravel)**
- ✅ Modelo: `app/ProductDiscountByBranch.php`
- ✅ Controlador: `app/Http/Controllers/Controllers_local/ProductDiscountController.php`
- ✅ Rutas API: agregadas en `routes/api.php`

### 3️⃣ **FRONTEND (Vue.js) - PANEL DE ADMINISTRACIÓN**
- ✅ Store Vuex: `resources/assets/store/discounts/`
- ✅ Vista principal: `resources/js/Views/discounts.vue`
- ✅ Modal crear/editar: `resources/js/Components/modals/discounts/createToEdit.vue`
- ✅ Modal clonar: `resources/js/Components/modals/discounts/cloneModal.vue`
- ✅ Ruta: `/admin/descuentos`
- ✅ Menú: "Asignar Descuentos" con ícono %

---

## 🎮 CÓMO USAR:

### **1. Ejecutar SQL** 
```bash
# En phpMyAdmin o MySQL, ejecutar:
c:\xampp\htdocs\create_product_discounts_table.sql
```

### **2. Acceder al Admin**
URL: `http://posfagotto.cl/admin/descuentos`
Menú: **"Asignar Descuentos"** (ícono %)

### **3. Crear Descuento**
- **Negocio**: Selecciona 1 sucursal
- **Tipo**:
  - **Por Patrón**: `%salsa%` (todas las salsas)
  - **Por Categoría**: `salsas`
  - **Producto Específico**: ID 123
- **Descuento**: 10% o $500
- **Vigencia**: Fechas opcional
- Click **"Guardar"**

### **4. Clonar a Múltiples Sucursales**
1. Crear descuento en 1 sucursal
2. Click botón **"Clonar"** 📋
3. Seleccionar 4 sucursales más
4. Click **"Clonar a X sucursales"**

---

## 📋 EJEMPLOS:

### **5 sucursales con 10% en salsas:**
1. Crear: Sucursal 1, Patrón `%salsa%`, 10%
2. Clonar a: Sucursales 2, 3, 4, 5

### **Solo Salsa Pesto 15%:**
1. Crear: Sucursal X, Patrón `%pesto%`, 15%

### **Descuento temporal (marzo):**
1. Crear: Patrón `%salsa%`, 10%
2. Fecha inicio: 01/03/2025, Fin: 31/03/2025

---

## 🔌 APIs PARA EL POS:

```javascript
// Verificar descuento al escanear producto:
POST /api/discount/check
{
  application_id: 5,
  product_id: 123,
  product_name: "Salsa Verde",
  product_price: 2990
}

// Respuesta:
{
  has_discount: true,
  discount: {
    discount_amount: 299,
    original_price: 2990,
    final_price: 2691
  }
}
```

---

## 🚀 DEPLOY A PRODUCCIÓN:

```bash
# 1. Compilar
npm run production

# 2. Subir a /var/www/html/server/:
- app/ProductDiscountByBranch.php
- app/Http/Controllers/Controllers_local/ProductDiscountController.php
- routes/api.php
- public/js/app.js
- public/css/app.css

# 3. SQL en producción
mysql -u usuario -p fagottodb < create_product_discounts_table.sql

# 4. Caché
php artisan cache:clear
```

---

## ✨ CARACTERÍSTICAS:

✅ Asignar descuentos a sucursales específicas
✅ Patrón de búsqueda (`%salsa%`)
✅ Por categoría o producto
✅ Porcentaje o monto fijo
✅ Fechas de vigencia
✅ Activar/Desactivar
✅ Clonar a múltiples sucursales
✅ Interfaz visual completa
✅ APIs listas
