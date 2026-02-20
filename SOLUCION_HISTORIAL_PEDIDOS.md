# Solución: Error "insertBefore" en Historial de Pedidos

## 📋 Problema Original

Al hacer click en "VER HISTORIAL" en pedidofinal.vue, la aplicación crasheaba con el siguiente error:

```
DOMException: Failed to execute 'insertBefore' on 'Node': The node before which 
the new node is to be inserted is not a child of this node.
```

La app se quedaba pegada y había que cerrarla y abrirla de nuevo.

## 🔍 Causa Raíz

Vue.js estaba intentando hacer **patch del DOM** (reutilizar nodos existentes) mientras:
- El array `historialPedidos` cambiaba
- Los componentes se estaban renderizando
- Había watchers y setInterval actualizando concurrentemente

Esto causaba un **conflicto de estados** donde Vue intentaba actualizar nodos del DOM que ya no existían o estaban en proceso de cambio.

## 🛠️ Soluciones Aplicadas

### 1. **Eliminar Actualizaciones Automáticas**
```javascript
// ANTES: setInterval actualizaba el historial cada 15s mientras estaba visible
this.historialRefreshInterval = setInterval(async () => {
    if (this.mostrarHistorial) {
        await this.cargarHistorial(); // ❌ Causaba conflictos
    }
}, 15000);

// DESPUÉS: Deshabilitado completamente
iniciarActualizacionHistorial() {
    console.log('⏸️ Actualización automática de historial DESHABILITADA');
    return;
}
```

### 2. **Eliminar Watchers Innecesarios**
```javascript
// ANTES: Watcher causaba re-renders
historialPedidos: {
    handler(newVal, oldVal) {
        console.log('🔔 historialPedidos cambió'); // ❌ Triggereaba actualizaciones
    },
    deep: true
},

// DESPUÉS: Comentado/eliminado
```

### 3. **Cambiar `v-show` por `v-if`**
```vue
<!-- ANTES: v-show mantenía el DOM y causaba conflictos -->
<div v-show="loadingHistorial">Loading...</div>
<div v-show="!loadingHistorial && historialPedidos.length > 0">
    <div v-for="pedido in historialPedidos">...</div>
</div>

<!-- DESPUÉS: v-if destruye y recrea el DOM limpiamente -->
<div v-if="loadingHistorial">Loading...</div>
<div v-else-if="historialPedidos.length === 0">Empty</div>
<div v-else>
    <div v-for="pedido in historialPedidos">...</div>
</div>
```

### 4. **Control de Estado con Flag "historialListo"**

**Archivo:** `src/renderer/views/pedidofinal.vue`

```javascript
data() {
    return {
        mostrarHistorial: false,
        historialListo: false, // ✅ NUEVO: Solo true cuando datos están cargados
        historialPedidos: [],
        loadingHistorial: false,
        historialKey: 0, // Para forzar re-render completo
    }
}
```

### 5. **Flujo de Carga Secuencial**

```javascript
async verHistorialDirecto() {
    // 1. Validar app
    if (!this.app || !this.app.Id) {
        this.$awn.alert('Sistema no está listo');
        return;
    }
    
    // 2. Ocultar y limpiar PRIMERO
    this.historialListo = false;
    this.mostrarHistorial = false;
    this.historialPedidos = [];
    this.loadingHistorial = true;
    
    // 3. Esperar a que Vue destruya el DOM anterior
    await this.$nextTick();
    
    // 4. Activar vista (pero NO renderiza aún por historialListo=false)
    this.mostrarHistorial = true;
    
    // 5. Cargar datos completamente
    await this.cargarHistorial();
    
    // 6. AHORA SÍ mostrar con datos completos
    this.historialListo = true;
    this.historialKey++;
}
```

### 6. **Template con Doble Condición**

```vue
<!-- La vista SOLO se renderiza cuando AMBAS son true -->
<div v-else-if="mostrarHistorial && historialListo" class="historial-standalone-container">
    <div class="section-card-body">
        <div v-if="loadingHistorial">Loading...</div>
        <div v-else-if="historialPedidos.length === 0">Empty</div>
        <div v-else :key="'grid-' + historialKey" class="historial-grid">
            <div v-for="pedido in historialPedidos" :key="'pedido-' + pedido.id">
                <!-- Contenido -->
            </div>
        </div>
    </div>
</div>
```

### 7. **Cache para Configuración**

```javascript
// ANTES: Cargaba configuración cada segundo (60 veces/minuto)
actualizarCountdown() {
    this.cargarConfiguracion(); // ❌ Cada segundo!
}

// DESPUÉS: Solo cada 30 segundos
data() {
    return {
        lastConfigLoadTime: 0,
    }
},

actualizarCountdown() {
    const ahora = Date.now();
    const tiempoTranscurrido = ahora - this.lastConfigLoadTime;
    
    if (tiempoTranscurrido > 30000) { // 30 segundos
        this.lastConfigLoadTime = ahora;
        this.cargarConfiguracion();
    }
}
```

### 8. **Eliminar Carga Automática del Historial**

```javascript
// ANTES: Cargaba automáticamente al entrar a pedido-final
mounted() {
    if (this.inicioSesion && this.app && this.app.Id) {
        this.cargarHistorial(); // ❌ Innecesario
    }
}

// DESPUÉS: Solo carga cuando usuario presiona botón
mounted() {
    // NO cargar historial automáticamente
    // El historial se carga manualmente con el botón "VER HISTORIAL"
}
```

## ✅ Resultado Final

**Flujo correcto:**
1. Usuario hace click en "VER HISTORIAL"
2. Vue destruye el componente anterior (si existe)
3. Limpia el array de pedidos
4. Espera un tick para asegurar DOM limpio
5. Activa `mostrarHistorial = true` (sin renderizar contenido)
6. Carga datos del backend
7. Cuando datos están listos → `historialListo = true`
8. Vue renderiza TODO desde cero con datos completos
9. El `key` dinámico asegura que el grid es completamente nuevo

**Sin:**
- ❌ SetIntervals actualizando concurrentemente
- ❌ Watchers causando re-renders
- ❌ `v-show` manteniendo DOM viejo
- ❌ Estados intermedios siendo renderizados
- ❌ Cargas automáticas innecesarias

**Con:**
- ✅ Render único y completo solo cuando datos están listos
- ✅ `v-if` para destruir/crear DOM limpiamente
- ✅ Control secuencial del flujo con async/await
- ✅ Cache para configuraciones
- ✅ Flag `historialListo` para evitar renders parciales

## 📝 Archivos Modificados

- `src/renderer/views/pedidofinal.vue` - Componente principal con toda la lógica

## 🎯 Lecciones Aprendidas

1. **Vue.js Reactivity**: Cuidado con actualizaciones concurrentes del DOM
2. **v-show vs v-if**: Usar `v-if` cuando necesitas destruir/recrear componentes
3. **Keys dinámicas**: Útiles para forzar re-render completo y evitar reutilización de nodos
4. **Flags de estado**: Controlar CUÁNDO se renderizan los componentes
5. **setInterval**: Evitar actualizaciones automáticas cuando el usuario está interactuando
6. **Watchers**: Usar con moderación, pueden causar ciclos infinitos de re-renders

## 👨‍💻 Autor

Jimmy Arriagada - Febrero 2026

---

**Nota**: Si el problema reaparece, verificar que no se hayan agregado nuevos watchers o setInterval que actualicen `historialPedidos` concurrentemente.
