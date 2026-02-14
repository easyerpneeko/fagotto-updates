# 🎫 IMPLEMENTACIÓN: Marcar cupones como usado al completar venta

## ⚠️ PROBLEMA RESUELTO
El cupón ya NO se marca como usado al agregarlo al carrito. Ahora solo se marcará cuando la venta se PROCESE/PAGUE.

## ✅ CAMBIOS REALIZADOS

### 1. modalSeleccionCupon.vue - LIMPIADO
- ❌ Eliminado: Método `marcarCuponUsado()`
- ❌ Eliminado: Método `getAppData()`
- ❌ Eliminado: Llamada a `marcarCuponUsado()` en `confirmar()`  
- ✅ Los productos siguen llevando `cupon_codigo` y `cupon_id` en sus datos

## 📋 PENDIENTE: Implementar en el componente TICKET

### Ubicación
El componente ticket está referenciado en:
```
src/renderer/components/modals/cafeteria/catalog.vue:406
<ticket 
  :key="`ticket-${ticketComponentKey}`"
  :typeCreateTicket="typeCreateTicket" 
  v-model="ticketData" 
  @changeValue="changeValue"
  @closeModal="closeModal" />
```

### Flujo actual
1. Usuario ingresa cupón → Valida
2. Selecciona 2 pastas (1ra precio normal, 2da gratis)
3. Productos se agregan al carrito con `cupon_codigo` y `cupon_id`
4. Usuario procesa la venta → **AQUÍ debe marcarse el cupón**

### Lógica a implementar en el componente TICKET

**Cuando la venta se procese exitosamente:**

```javascript
// 🔍 DETECTAR si hay productos con cupón en la venta
const productosConCupon = ticketData.products.filter(p => p.has_cupon);

if (productosConCupon.length > 0) {
  // Obtener los cupones únicos usados (puede haber varios cupones en una venta)
  const cuponesUsados = {};
  
  productosConCupon.forEach(prod => {
    if (prod.cupon_codigo && !cuponesUsados[prod.cupon_codigo]) {
      cuponesUsados[prod.cupon_codigo] = {
        codigo: prod.cupon_codigo,
        cupon_id: prod.cupon_id,
        productos: []
      };
    }
    if (prod.cupon_codigo) {
      cuponesUsados[prod.cupon_codigo].productos.push(prod.name);
    }
  });
  
  // 🔒 Marcar cada cupón como usado
  for (const codigo in cuponesUsados) {
    await marcarCuponComoUsado(cuponesUsados[codigo]);
  }
}

// Método auxiliar
async marcarCuponComoUsado(cuponData) {
  try {
    const appData = await obtenerDatosApp(); // Leer aplication.json
    const usuario = localStorage.getItem('username') || 'Usuario desconocido';
    
    // DESARROLLO: Mock en localStorage
    if (process.env.NODE_ENV === 'development') {
      const cuponesUsados = JSON.parse(localStorage.getItem('cuponesUsados') || '[]');
      cuponesUsados.push({
        codigo: cuponData.codigo,
        usado_en: new Date().toISOString(),
        productos: cuponData.productos.join(', '),
        sucursal_id: appData.Id,
        sucursal_nombre: appData.Name,
        usuario_nombre: usuario
      });
      localStorage.setItem('cuponesUsados', JSON.stringify(cuponesUsados));
      console.log('✅ Cupón marcado como usado:', cuponData.codigo);
      return;
    }
    
    // PRODUCCIÓN: Llamar al backend
    const url = 'https://posfagotto.cl/api/cupones/aplicar';
    const payload = {
      codigo: cuponData.codigo,
      cupon_id: cuponData.cupon_id,
      sucursal_id: appData.Id,
      sucursal_nombre: appData.Name,
      usuario_nombre: usuario,
      producto_nombre: cuponData.productos.join(' + '),
      categoria_nombre: 'Pastas',
      // Agregar orden_id si está disponible
      // orden_id: ordenId
    };
    
    const response = await Connection.fetch(url, 'POST', payload, null, false);
    
    if (response && response.ok && response.data && response.data.success) {
      console.log('✅ Cupón marcado en backend:', cuponData.codigo);
    } else {
      console.error('❌ Error al marcar cupón:', response);
    }
  } catch (error) {
    console.error('❌ Error marcando cupón:', error);
    // NO bloquear la venta si falla el marcado
  }
}

function obtenerDatosApp() {
  return new Promise((resolve) => {
    ConfigHelper.readAppFile((err, data) => {
      if (err || !data) {
        resolve({ Id: null, Name: 'Sucursal desconocida' });
        return;
      }
      try {
        const appData = JSON.parse(data);
        resolve({
          Id: appData.Id || null,
          Name: appData.Name || 'Sucursal desconocida'
        });
      } catch (error) {
        resolve({ Id: null, Name: 'Sucursal desconocida' });
      }
    });
  });
}
```

### Imports necesarios en el componente ticket
```javascript
import Connection from '@/helpers/Connection';
import ConfigHelper from '@/helpers/ConfigHelper.js';
```

## 🎯 PUNTO DE INTEGRACIÓN
Buscar en el componente ticket donde se hace el request exitoso al backend para crear la venta.
Después de ese `response.success` o similar, agregar la lógica de marcar cupones.

## ✅ VERIFICACIÓN
1. Agregar producto con cupón al carrito
2. Cancelar la venta → Cupón sigue disponible ✅
3. Intentar usar el mismo cupón → Funciona ✅
4. Procesar la venta completa → Cupón se marca como usado ✅
5. Intentar reusar el cupón → Error "ya utilizado" ✅

## 📝 NOTAS IMPORTANTES
- NO bloquear la venta si falla el marcado del cupón (try-catch)
- El cupón se debe marcar SOLO si la venta se procesa exitosamente
- Si hay error en la venta, el cupón queda disponible
- Un carrito puede tener múltiples cupones (manejar array)
