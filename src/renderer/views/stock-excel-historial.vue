<template>
  <div class="stock-excel-container">
    <!-- Header -->
    <div class="header-section">
      <div class="title-section">
        <i class="fas fa-table"></i>
        <h1>Stock (Excel) - Historial por Día</h1>
        <span class="subtitle">Gestión de productos - pedidofinal_precios</span>
      </div>
      <div class="actions-section">
        <button class="btn-action btn-history" @click="toggleHistorial">
          <i class="fas fa-history"></i>
          {{ mostrarHistorial ? 'Ver Actual' : 'Ver Historial' }}
        </button>
        <button class="btn-action btn-refresh" @click="cargarDatos" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ spinning: loading }"></i>
          Actualizar
        </button>
        <button v-if="!mostrarHistorial" class="btn-action btn-save" @click="guardarCambios" :disabled="!hayCambios || saving">
          <i class="fas fa-save"></i>
          Guardar Cambios ({{ cambiosPendientes.length }})
        </button>
      </div>
    </div>

    <!-- Vista de Historial por Día -->
    <div v-if="mostrarHistorial" class="historial-container">
      <!-- Filtros de fecha -->
      <div class="filtros-fecha">
        <div class="filtro-item">
          <label><i class="fas fa-calendar-alt"></i> Seleccionar Fecha:</label>
          <input 
            type="date" 
            v-model="fechaSeleccionada" 
            @change="cargarHistorialPorFecha"
            class="date-input"
          />
        </div>
        <div class="filtro-item">
          <button class="btn-filtro" @click="cargarFechasDisponibles">
            <i class="fas fa-list"></i>
            Ver Fechas Disponibles
          </button>
        </div>
        <div class="filtro-item">
          <button class="btn-filtro btn-today" @click="irAHoy">
            <i class="fas fa-calendar-day"></i>
            Hoy
          </button>
        </div>
      </div>

      <!-- Lista de fechas disponibles -->
      <div v-if="mostrandoFechas" class="fechas-disponibles">
        <h3><i class="fas fa-calendar-check"></i> Fechas con Stock Registrado</h3>
        <div class="fechas-grid">
          <div 
            v-for="fecha in fechasDisponibles" 
            :key="fecha.fecha"
            class="fecha-card"
            :class="{ 'fecha-activa': fecha.fecha === fechaSeleccionada }"
            @click="seleccionarFecha(fecha.fecha)"
          >
            <div class="fecha-dia">
              <i class="fas fa-calendar"></i>
              {{ formatearFecha(fecha.fecha) }}
            </div>
            <div class="fecha-info">
              <span class="badge">{{ fecha.total_productos }} productos</span>
              <span class="badge badge-user">{{ fecha.usuario }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Historial de la fecha seleccionada -->
      <div v-if="!mostrandoFechas && historialFecha.length > 0" class="historial-tabla">
        <div class="historial-header">
          <h3>
            <i class="fas fa-history"></i>
            Stock del día: <strong>{{ formatearFecha(fechaSeleccionada) }}</strong>
          </h3>
          <button class="btn-export" @click="exportarHistorial">
            <i class="fas fa-file-excel"></i>
            Exportar Excel
          </button>
        </div>
        
        <div class="table-wrapper">
          <table class="excel-table">
            <thead>
              <tr>
                <th class="col-id">#</th>
                <th class="col-nombre">Producto</th>
                <th class="col-stock">Cantidad</th>
                <th class="col-unidad">Unidad</th>
                <th class="col-usuario">Usuario</th>
                <th class="col-hora">Hora Registro</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in historialFecha" :key="item.id">
                <td class="col-id">{{ item.id_producto }}</td>
                <td class="col-nombre">{{ item.producto_nombre }}</td>
                <td class="col-stock text-right">{{ item.cantidad_reportada }}</td>
                <td class="col-unidad">{{ item.unidad_medida }}</td>
                <td class="col-usuario">
                  <span class="badge badge-user">
                    <i class="fas fa-user"></i>
                    {{ item.usuario }}
                  </span>
                </td>
                <td class="col-hora">
                  <i class="fas fa-clock"></i>
                  {{ formatearHora(item.created_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mensaje cuando no hay datos -->
      <div v-if="!mostrandoFechas && historialFecha.length === 0 && !loading" class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h3>No hay stock registrado para el {{ formatearFecha(fechaSeleccionada) }}</h3>
        <p>Selecciona otra fecha o registra stock para hoy</p>
      </div>
    </div>

    <!-- Vista Actual (Tabla de Edición) -->
    <div v-else class="tabla-actual">
      <!-- Loading -->
      <div v-if="loading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Cargando datos...</p>
      </div>

      <!-- Tabla tipo Excel -->
      <div v-else class="table-container">
        <div class="table-wrapper">
          <table class="excel-table">
            <thead>
              <tr>
                <th class="col-id">#</th>
                <th class="col-nombre">Producto</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-stock">Stock</th>
                <th class="col-precio">Precio Unitario</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="producto in productos" :key="producto.id" :class="{ 'row-modified': productoModificado(producto.id) }">
                <td class="col-id">{{ producto.id }}</td>
                <td class="col-nombre">{{ producto.producto }}</td>
                <td class="col-tipo">
                  <select 
                    v-model="producto.tipo_medida" 
                    @change="onTipoChange(producto)"
                    class="cell-select"
                    disabled
                  >
                    <option value="kilos">Kilos</option>
                    <option value="litros">Litros</option>
                    <option value="unidades">Unidades</option>
                  </select>
                </td>
                <td class="col-stock editable">
                  <input 
                    type="text" 
                    v-model="producto.stock" 
                    @blur="validarStock(producto)"
                    @keypress="validarTecla($event, producto)"
                    class="cell-input text-right"
                    :placeholder="producto.tipo_medida === 'kilos' ? 'Ej: 3.45' : 'Ej: 5'"
                    inputmode="decimal"
                  />
                </td>
                <td class="col-precio text-right">$ {{ formatearPrecio(producto.precio_por_unidad) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Footer con estadísticas -->
    <div class="footer-stats">
      <div class="stat-item">
        <i class="fas fa-box"></i>
        <span>{{ mostrarHistorial ? 'Stock Registrado' : 'Total Productos' }}: <strong>{{ mostrarHistorial ? historialFecha.length : productos.length }}</strong></span>
      </div>
      <div v-if="!mostrarHistorial" class="stat-item">
        <i class="fas fa-edit"></i>
        <span>Cambios Pendientes: <strong>{{ cambiosPendientes.length }}</strong></span>
      </div>
      <div class="stat-item">
        <i class="fas fa-calendar"></i>
        <span><strong>{{ mostrarHistorial ? formatearFecha(fechaSeleccionada) : 'Hoy - ' + formatearFecha(getFechaHoy()) }}</strong></span>
      </div>
      <div class="stat-item">
        <i class="fas fa-clock"></i>
        <span>Actualizado: <strong>{{ ultimaActualizacion }}</strong></span>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection';
import BaseUrl from '@/helpers/baseUrl';

export default {
  name: 'StockExcelHistorial',
  data() {
    return {
      // Vista actual
      productos: [],
      productosOriginales: [],
      cambiosPendientes: [],
      
      // Historial
      mostrarHistorial: false,
      fechaSeleccionada: this.getFechaHoy(),
      historialFecha: [],
      fechasDisponibles: [],
      mostrandoFechas: false,
      
      // Estados
      loading: false,
      saving: false,
      ultimaActualizacion: '-',
      appInfo: null
    };
  },
  computed: {
    hayCambios() {
      return this.cambiosPendientes.length > 0;
    }
  },
  async mounted() {
    await this.cargarAppInfo();
    this.cargarDatos();
  },
  methods: {
    getFechaHoy() {
      const hoy = new Date();
      return hoy.toISOString().split('T')[0];
    },
    
    formatearFecha(fecha) {
      if (!fecha) return '-';
      const [year, month, day] = fecha.split('-');
      return `${day}/${month}/${year}`;
    },
    
    formatearHora(datetime) {
      if (!datetime) return '-';
      const date = new Date(datetime);
      return date.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
    },
    
    irAHoy() {
      this.fechaSeleccionada = this.getFechaHoy();
      this.mostrandoFechas = false;
      this.cargarHistorialPorFecha();
    },
    
    toggleHistorial() {
      this.mostrarHistorial = !this.mostrarHistorial;
      
      // Si cambiamos a modo historial, cargar el historial de hoy automáticamente
      if (this.mostrarHistorial) {
        this.fechaSeleccionada = this.getFechaHoy();
        this.mostrandoFechas = false;
        this.cargarHistorialPorFecha();
      }
    },
    
    seleccionarFecha(fecha) {
      this.fechaSeleccionada = fecha;
      this.mostrandoFechas = false;
      this.cargarHistorialPorFecha();
    },
    
    async cargarFechasDisponibles() {
      this.loading = true;
      try {
        const app = this.appInfo || {};
        const idNegocio = app.Id || app.id || null;
        
        let url = BaseUrl.getUrl('api/local/pedidofinal/historial-fechas');
        if (idNegocio) {
          url += `?id_negocio=${idNegocio}`;
        }
        
        const request = await Connection.request('get', url);
        
        if (request.ok || request.success) {
          this.fechasDisponibles = Array.isArray(request.data) ? request.data : ((request.data && request.data.data) || []);
          this.mostrandoFechas = true;
          console.log('📅 Fechas disponibles:', this.fechasDisponibles);
        } else {
          throw new Error('Error al cargar fechas');
        }
      } catch (error) {
        console.error('❌ Error cargando fechas:', error);
        this.$awn.alert('Error al cargar las fechas disponibles');
      } finally {
        this.loading = false;
      }
    },
    
    async cargarHistorialPorFecha() {
      this.loading = true;
      this.mostrandoFechas = false;
      try {
        const app = this.appInfo || {};
        const idNegocio = app.Id || app.id || null;
        
        let url = BaseUrl.getUrl(`api/local/pedidofinal/historial-dia?fecha=${this.fechaSeleccionada}`);
        if (idNegocio) {
          url += `&id_negocio=${idNegocio}`;
        }
        
        const request = await Connection.request('get', url);
        
        if (request.ok || request.success) {
          this.historialFecha = Array.isArray(request.data) ? request.data : ((request.data && request.data.data) || []);
          console.log('📊 Historial del día:', this.historialFecha.length, 'productos');
          
          if (this.historialFecha.length === 0) {
            this.$awn.info(`No hay stock registrado para el ${this.formatearFecha(this.fechaSeleccionada)}`);
          }
        } else {
          throw new Error('Error al cargar historial');
        }
      } catch (error) {
        console.error('❌ Error cargando historial:', error);
        this.$awn.alert('Error al cargar el historial de la fecha seleccionada');
        this.historialFecha = [];
      } finally {
        this.loading = false;
      }
    },
    
    async exportarHistorial() {
      try {
        const XLSX = require('xlsx');
        
        // Preparar datos para Excel
        const datos = this.historialFecha.map((item, index) => ({
          '#': index + 1,
          'ID Producto': item.id_producto,
          'Producto': item.producto_nombre,
          'Cantidad': item.cantidad_reportada,
          'Unidad': item.unidad_medida,
          'Usuario': item.usuario,
          'Hora': this.formatearHora(item.created_at)
        }));
        
        // Crear libro de Excel
        const ws = XLSX.utils.json_to_sheet(datos);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Stock');
        
        // Guardar archivo
        const nombreArchivo = `stock_${this.fechaSeleccionada}_${(this.appInfo && this.appInfo.Name) || 'negocio'}.xlsx`;
        XLSX.writeFile(wb, nombreArchivo);
        
        this.$awn.success('Excel exportado exitosamente');
      } catch (error) {
        console.error('❌ Error exportando:', error);
        this.$awn.alert('Error al exportar Excel');
      }
    },
    
    async cargarAppInfo() {
      try {
        const request = await this.$store.dispatch('main/refreshData', '?slim');
        this.appInfo = request.data;
        console.log('✅ App info cargada:', this.appInfo);
      } catch (error) {
        console.error('❌ Error cargando app info:', error);
      }
    },
    
    async cargarDatos() {
      this.loading = true;
      try {
        // 1. Cargar lista de productos desde pedidofinal_precios
        let url = BaseUrl.getUrl('api/local/pedidofinal/stock');
        const request = await Connection.request('get', url);
        
        const isSuccess = request.ok === true || request.success === true || (request.data && request.data.success === true);
        
        if (isSuccess) {
          let data = null;
          
          if (Array.isArray(request.data)) {
            data = request.data;
          } else if (request.data && request.data.success && Array.isArray(request.data.data)) {
            data = request.data.data;
          } else if (request.data && Array.isArray(request.data.data)) {
            data = request.data.data;
          }
          
          if (data && data.length > 0) {
            // 2. Cargar el historial de HOY desde historial_stock_diario
            const app = this.appInfo || {};
            const idNegocio = app.Id || app.id || null;
            const fechaHoy = this.getFechaHoy();
            
            let urlHistorial = BaseUrl.getUrl(`api/local/pedidofinal/historial-dia?fecha=${fechaHoy}`);
            if (idNegocio) {
              urlHistorial += `&id_negocio=${idNegocio}`;
            }
            
            const historialRequest = await Connection.request('get', urlHistorial);
            
            // 3. Crear mapa de historial por id_producto
            let historialHoy = {};
            const isHistorialSuccess = historialRequest.ok === true || historialRequest.success === true;
            
            if (isHistorialSuccess) {
              const historialData = Array.isArray(historialRequest.data) ? historialRequest.data : ((historialRequest.data && historialRequest.data.data) || []);
              
              historialData.forEach(item => {
                historialHoy[item.id_producto] = item.cantidad_reportada || 0;
              });
              
              console.log('📊 Historial de hoy encontrado:', historialData.length, 'productos');
            } else {
              console.log('📝 No hay historial para hoy, iniciando en 0');
            }
            
            // 4. Mapear productos con el stock del historial de hoy (o 0 si no existe)
            this.productos = data.map(p => ({
              ...p,
              stock: historialHoy[p.id] || 0,  // Stock desde historial de HOY o 0
              tipo_medida: this.detectarTipoMedida(p.unidad_medida)
            })).sort((a, b) => a.id - b.id);
            
            this.productosOriginales = JSON.parse(JSON.stringify(this.productos));
            this.cambiosPendientes = [];
            this.actualizarHora();
            
            if (Object.keys(historialHoy).length === 0) {
              this.$awn.info(`${data.length} productos cargados - Stock en 0 (sin registros para hoy)`);
            } else {
              this.$awn.success(`${data.length} productos cargados - ${Object.keys(historialHoy).length} con stock registrado hoy`);
            }
          }
        }
      } catch (error) {
        console.error('❌ Error cargando datos:', error);
        this.$awn.alert('Error al cargar los datos');
      } finally {
        this.loading = false;
      }
    },
    
    marcarModificado(id, campo, valor) {
      const index = this.cambiosPendientes.findIndex(c => c.id === id);
      
      if (index >= 0) {
        this.cambiosPendientes[index].cambios[campo] = valor;
      } else {
        this.cambiosPendientes.push({
          id: id,
          cambios: {
            [campo]: valor
          }
        });
      }
    },
    
    productoModificado(id) {
      return this.cambiosPendientes.some(c => c.id === id);
    },
    
    async guardarCambios() {
      if (!this.hayCambios) return;
      
      this.saving = true;
      let exitosos = 0;
      let errores = 0;
      
      try {
        const app = this.appInfo || {};
        const idNegocio = app.Id || app.id || null;
        const nombreNegocio = app.Name || app.name || 'Sin nombre';
        const appId = app.app_id || app.appId || app.Id || app.id || null;
        const usuario = localStorage.getItem('username') || 'Sistema';
        
        for (const cambio of this.cambiosPendientes) {
          try {
            if (cambio.cambios.stock !== undefined) {
              const producto = this.productos.find(p => p.id === cambio.id);
              
              if (producto) {
                const payload = {
                  id_producto: parseInt(cambio.id),
                  producto_nombre: producto.producto || 'Sin nombre',
                  cantidad_reportada: parseFloat(cambio.cambios.stock),
                  unidad_medida: producto.unidad_medida || 'kg',
                  observacion: 'Actualización de stock desde Stock (Excel)',
                  fecha_reporte: this.getFechaHoy() // Guardar con fecha de hoy
                };
                
                if (idNegocio !== null) payload.id_negocio = parseInt(idNegocio);
                if (appId !== null) payload.app_id = String(appId);
                if (nombreNegocio && nombreNegocio !== 'Sin nombre') payload.nombre_negocio = nombreNegocio;
                if (usuario) payload.usuario = usuario;
                
                // Guardar en historial diario
                let url = BaseUrl.getUrl('api/local/pedidofinal/historial-dia');
                const request = await Connection.request('post', url, payload);
                
                const isSaveSuccess = request.ok === true || request.success === true || (request.data && request.data.success === true);
                
                if (isSaveSuccess) {
                  exitosos++;
                } else {
                  errores++;
                  if (request.data && request.data.message) {
                    this.$awn.alert(`Error en ${producto.producto}: ${request.data.message}`);
                  }
                }
              }
            }
          } catch (error) {
            console.error(`❌ Error guardando:`, error);
            errores++;
          }
        }
        
        if (exitosos > 0) {
          this.$awn.success(`${exitosos} stock(s) registrado(s) para hoy`);
        }
        
        if (errores > 0) {
          this.$awn.warning(`${errores} stock(s) con error`);
        }
        
        await this.cargarDatos();
        
      } catch (error) {
        console.error('Error guardando cambios:', error);
        this.$awn.alert('Error al guardar los cambios');
      } finally {
        this.saving = false;
      }
    },
    
    actualizarHora() {
      const now = new Date();
      this.ultimaActualizacion = now.toLocaleString('es-CL');
    },
    
    formatearPrecio(precio) {
      return parseFloat(precio).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    },
    
    detectarTipoMedida(unidadMedida) {
      if (!unidadMedida) return 'unidades';
      const unidad = String(unidadMedida).toLowerCase().trim();
      if (unidad.includes('litro') || unidad.includes('lt') || unidad.includes('ml')) return 'litros';
      if (unidad.includes('kg') || unidad.includes('kilo') || unidad.includes('gr') || unidad.includes('bolsa') && unidad.includes('k')) return 'kilos';
      return 'unidades';
    },

    onTipoChange(producto) {
      this.validarStock(producto);
    },
    
    validarTecla(event, producto) {
      const key = event.key;
      const value = producto.stock ? producto.stock.toString() : '';
      
      if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(key)) {
        return true;
      }
      
      if (key === '.' || key === ',') {
        if (producto.tipo_medida !== 'kilos') {
          event.preventDefault();
          return false;
        }
        if (value.includes('.') || value.includes(',')) {
          event.preventDefault();
          return false;
        }
        return true;
      }
      
      if (!/[0-9]/.test(key)) {
        event.preventDefault();
        return false;
      }
    },
    
    validarStock(producto) {
      let valor = producto.stock ? producto.stock.toString() : '0';
      valor = valor.replace(',', '.');
      valor = valor.replace(/[^0-9.]/g, '');
      
      const partes = valor.split('.');
      if (partes.length > 2) {
        valor = partes[0] + '.' + partes.slice(1).join('');
      }
      
      let numero = parseFloat(valor) || 0;
      
      if (producto.tipo_medida === 'kilos') {
        numero = Math.round(numero * 100) / 100;
      } else {
        numero = Math.round(numero);
      }
      
      if (numero < 0) numero = 0;
      
      producto.stock = numero;
      this.marcarModificado(producto.id, 'stock', numero);
    }
  }
};
</script>

<style scoped>
.stock-excel-container {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #f5f7fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
}

.header-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.title-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.title-section i {
  font-size: 2rem;
}

.title-section h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
}

.subtitle {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-left: 1rem;
}

.actions-section {
  display: flex;
  gap: 1rem;
}

.btn-action {
  padding: 0.7rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.btn-history {
  background: #ffc107;
  color: #333;
}

.btn-history:hover {
  background: #ffb300;
  transform: translateY(-2px);
}

.btn-refresh {
  background: white;
  color: #667eea;
}

.btn-refresh:hover {
  background: #f0f0f0;
  transform: translateY(-2px);
}

.btn-save {
  background: #38ef7d;
  color: white;
}

.btn-save:hover:not(:disabled) {
  background: #2ed86f;
  transform: translateY(-2px);
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Historial Container */
.historial-container {
  flex: 1;
  overflow: auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  min-height: 0; /* Importante para flex scroll */
}

.filtros-fecha {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 1.5rem;
  align-items: center;
  margin-bottom: 1.5rem;
}

.filtro-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.filtro-item label {
  font-weight: 600;
  color: #333;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-input {
  padding: 0.6rem 1rem;
  border: 2px solid #667eea;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.date-input:focus {
  outline: none;
  border-color: #764ba2;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-filtro {
  padding: 0.6rem 1.2rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.btn-filtro:hover {
  background: #764ba2;
  transform: translateY(-2px);
}

.btn-today {
  background: #38ef7d;
}

.btn-today:hover {
  background: #2ed86f;
}

/* Fechas Disponibles */
.fechas-disponibles {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.fechas-disponibles h3 {
  margin: 0 0 1.5rem 0;
  color: #333;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.fechas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.fecha-card {
  padding: 1.2rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.fecha-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  border-color: #667eea;
}

.fecha-card.fecha-activa {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #764ba2;
}

.fecha-dia {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.fecha-info {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.badge {
  padding: 0.3rem 0.8rem;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.fecha-activa .badge {
  background: rgba(255, 255, 255, 0.2);
}

.badge-user {
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

/* Historial Tabla */
.historial-tabla {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  min-height: 0; /* Importante para flex scroll */
}

.historial-header {
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #667eea;
  flex-shrink: 0; /* No se comprime */
}

.historial-tabla .table-wrapper {
  flex: 1;
  overflow: auto;
  min-height: 0; /* Importante para flex scroll */
}

.historial-header h3 {
  margin: 0;
  color: #333;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.btn-export {
  padding: 0.7rem 1.5rem;
  background: #28a745;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.btn-export:hover {
  background: #218838;
  transform: translateY(-2px);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.empty-state i {
  font-size: 4rem;
  color: #ddd;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #666;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #999;
}

/* Loading */
.loading-overlay {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Tabla */
.tabla-actual,
.table-container {
  flex: 1;
  overflow: hidden;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  min-height: 0; /* Importante para flex scroll */
}

.table-wrapper {
  flex: 1;
  overflow-y: auto;
  overflow-x: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  min-height: 0; /* Importante para flex scroll */
  max-height: 100%; /* Asegurar que no se expanda más allá */
}

.excel-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem;
  min-width: 800px; /* Asegurar ancho mínimo para scroll horizontal */
}

.excel-table thead {
  position: sticky;
  top: 0;
  z-index: 10;
  background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
}

.excel-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 700;
  color: #667eea;
  border-bottom: 2px solid #667eea;
  white-space: nowrap;
}

.excel-table tbody tr {
  transition: all 0.2s ease;
}

.excel-table tbody tr:hover {
  background: #f8f9ff;
}

.excel-table tbody tr.row-modified {
  background: #fff9e6;
  border-left: 4px solid #ffc107;
}

.excel-table td {
  padding: 0.8rem 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.col-id {
  width: 60px;
  text-align: center;
  font-weight: 600;
  color: #666;
}

.col-nombre {
  min-width: 250px;
}

.col-tipo {
  width: 140px;
}

.col-stock {
  width: 100px;
}

.col-unidad {
  width: 100px;
}

.col-precio {
  width: 130px;
}

.col-usuario {
  width: 150px;
}

.col-hora {
  width: 120px;
  color: #666;
}

.cell-input,
.cell-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  background: white;
}

.cell-input:focus,
.cell-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
}

.text-right {
  text-align: right;
}

/* Footer */
.footer-stats {
  background: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-around;
  align-items: center;
  border-top: 1px solid #e0e0e0;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #666;
}

.stat-item i {
  color: #667eea;
  font-size: 1.2rem;
}

.stat-item strong {
  color: #333;
  font-weight: 700;
}
</style>
