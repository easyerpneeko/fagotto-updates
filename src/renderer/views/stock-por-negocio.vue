<template>
  <div class="stock-negocio-container">
    <div class="header-section">
      <div class="title-section">
        <i class="fas fa-store"></i>
        <h1>Stock por Negocio</h1>
        <span class="subtitle">Historial de stock reportado por cada sucursal</span>
      </div>
      <div class="actions-section">
        <button class="btn-action btn-refresh" @click="cargarDatos" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ spinning: loading }"></i>
          Actualizar
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-section">
      <div class="filtro-item">
        <label>Negocio:</label>
        <input 
          type="text" 
          v-model="filtros.nombre_negocio" 
          placeholder="Ej: Merced"
          @input="aplicarFiltros"
          class="input-filtro"
        />
      </div>
      <div class="filtro-item">
        <label>Producto:</label>
        <input 
          type="text" 
          v-model="filtros.producto" 
          placeholder="Buscar producto..."
          @input="aplicarFiltros"
          class="input-filtro"
        />
      </div>
      <div class="filtro-item">
        <label>Desde:</label>
        <input 
          type="date" 
          v-model="filtros.fecha_desde" 
          @change="aplicarFiltros"
          class="input-filtro"
        />
      </div>
      <div class="filtro-item">
        <label>Hasta:</label>
        <input 
          type="date" 
          v-model="filtros.fecha_hasta" 
          @change="aplicarFiltros"
          class="input-filtro"
        />
      </div>
      <div class="filtro-item">
        <button class="btn-limpiar" @click="limpiarFiltros">
          <i class="fas fa-times"></i>
          Limpiar
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
      <p>Cargando datos...</p>
    </div>

    <!-- Tabs -->
    <div class="tabs-section">
      <button 
        class="tab-btn" 
        :class="{ active: vistaActual === 'resumen' }"
        @click="vistaActual = 'resumen'"
      >
        <i class="fas fa-chart-bar"></i>
        Resumen (Último stock)
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: vistaActual === 'historial' }"
        @click="vistaActual = 'historial'"
      >
        <i class="fas fa-history"></i>
        Historial Completo
      </button>
    </div>

    <!-- Vista Resumen -->
    <div v-if="vistaActual === 'resumen' && !loading" class="table-container">
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Negocio</th>
              <th>Producto</th>
              <th>Categoría</th>
              <th>Tipo</th>
              <th>Stock Actual</th>
              <th>Última Actualización</th>
              <th>Usuario</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in datosResumenFiltrados" :key="`${item.id_negocio}-${item.id_producto}`">
              <td class="negocio-cell">
                <strong>{{ item.nombre_negocio || 'Sin nombre' }}</strong>
                <small v-if="item.app_id">({{ item.app_id }})</small>
              </td>
              <td>{{ item.producto_nombre }}</td>
              <td>{{ item.categoria || '-' }}</td>
              <td>
                <span class="badge-tipo" :class="getTipoClass(item.unidad_medida)">
                  {{ getTipoTexto(item.unidad_medida) }}
                </span>
              </td>
              <td class="cantidad-cell">
                <span class="cantidad">{{ formatearCantidad(item.cantidad_reportada, item.unidad_medida) }}</span>
                <small class="unidad-text">{{ item.unidad_medida }}</small>
              </td>
              <td>{{ formatearFecha(item.fecha_registro) }}</td>
              <td>{{ item.usuario }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="datosResumenFiltrados.length === 0" class="no-data">
          <i class="fas fa-inbox"></i>
          <p>No hay datos para mostrar</p>
        </div>
      </div>
    </div>

    <!-- Vista Historial -->
    <div v-if="vistaActual === 'historial' && !loading" class="table-container">
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Negocio</th>
              <th>Producto</th>
              <th>Tipo</th>
              <th>Cantidad</th>
              <th>Usuario</th>
              <th>Observación</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in datosHistorialFiltrados" :key="item.id">
              <td>{{ formatearFecha(item.fecha_registro) }}</td>
              <td class="negocio-cell">
                <strong>{{ item.nombre_negocio || 'Sin nombre' }}</strong>
                <small v-if="item.app_id">({{ item.app_id }})</small>
              </td>
              <td>{{ item.producto_nombre }}</td>
              <td>
                <span class="badge-tipo" :class="getTipoClass(item.unidad_medida)">
                  {{ getTipoTexto(item.unidad_medida) }}
                </span>
              </td>
              <td class="cantidad-cell">
                <span class="cantidad">{{ formatearCantidad(item.cantidad_reportada, item.unidad_medida) }}</span>
                <small class="unidad-text">{{ item.unidad_medida }}</small>
              </td>
              <td>{{ item.usuario }}</td>
              <td>{{ item.observacion || '-' }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="datosHistorialFiltrados.length === 0" class="no-data">
          <i class="fas fa-inbox"></i>
          <p>No hay datos para mostrar</p>
        </div>
      </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-section">
      <div class="stat-item">
        <i class="fas fa-store"></i>
        <span>Negocios: <strong>{{ totalNegocios }}</strong></span>
      </div>
      <div class="stat-item">
        <i class="fas fa-box"></i>
        <span>Productos: <strong>{{ totalProductos }}</strong></span>
      </div>
      <div class="stat-item">
        <i class="fas fa-list"></i>
        <span>Registros: <strong>{{ totalRegistros }}</strong></span>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection';
import BaseUrl from '@/helpers/baseUrl';
import moment from 'moment';

export default {
  name: 'StockPorNegocio',
  data() {
    return {
      datosResumen: [],
      datosHistorial: [],
      loading: false,
      vistaActual: 'resumen', // 'resumen' o 'historial'
      filtros: {
        nombre_negocio: '',
        producto: '',
        fecha_desde: '',
        fecha_hasta: ''
      }
    };
  },
  computed: {
    datosResumenFiltrados() {
      return this.filtrarDatos(this.datosResumen);
    },
    datosHistorialFiltrados() {
      return this.filtrarDatos(this.datosHistorial);
    },
    totalNegocios() {
      const negocios = new Set();
      this.datosResumen.forEach(item => {
        negocios.add(item.id_negocio || item.app_id);
      });
      return negocios.size;
    },
    totalProductos() {
      const productos = new Set();
      this.datosResumen.forEach(item => {
        productos.add(item.id_producto);
      });
      return productos.size;
    },
    totalRegistros() {
      return this.vistaActual === 'resumen' 
        ? this.datosResumenFiltrados.length 
        : this.datosHistorialFiltrados.length;
    }
  },
  mounted() {
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      this.loading = true;
      try {
        await Promise.all([
          this.cargarResumen(),
          this.cargarHistorial()
        ]);
        this.$awn.success('Datos cargados correctamente');
      } catch (error) {
        console.error('Error cargando datos:', error);
        this.$awn.alert('Error al cargar los datos');
      } finally {
        this.loading = false;
      }
    },
    
    async cargarResumen() {
      try {
        let url = BaseUrl.getUrl('api/local/pedidofinal/stock-negocio/resumen');
        const request = await Connection.request('get', url);
        
        if (request.ok || request.success) {
          if (Array.isArray(request.data)) {
            this.datosResumen = request.data;
          } else if (request.data && Array.isArray(request.data.data)) {
            this.datosResumen = request.data.data;
          }
        }
      } catch (error) {
        console.error('Error cargando resumen:', error);
      }
    },
    
    async cargarHistorial() {
      try {
        let url = BaseUrl.getUrl('api/local/pedidofinal/stock-negocio');
        const request = await Connection.request('get', url);
        
        if (request.ok || request.success) {
          if (Array.isArray(request.data)) {
            this.datosHistorial = request.data;
          } else if (request.data && Array.isArray(request.data.data)) {
            this.datosHistorial = request.data.data;
          }
        }
      } catch (error) {
        console.error('Error cargando historial:', error);
      }
    },
    
    filtrarDatos(datos) {
      let resultado = [...datos];
      
      // Filtrar por negocio
      if (this.filtros.nombre_negocio) {
        const busqueda = this.filtros.nombre_negocio.toLowerCase();
        resultado = resultado.filter(item => 
          (item.nombre_negocio && item.nombre_negocio.toLowerCase().includes(busqueda)) ||
          (item.app_id && item.app_id.toLowerCase().includes(busqueda))
        );
      }
      
      // Filtrar por producto
      if (this.filtros.producto) {
        const busqueda = this.filtros.producto.toLowerCase();
        resultado = resultado.filter(item => 
          item.producto_nombre && item.producto_nombre.toLowerCase().includes(busqueda)
        );
      }
      
      // Filtrar por fecha
      if (this.filtros.fecha_desde) {
        resultado = resultado.filter(item => 
          moment(item.fecha_registro).isSameOrAfter(moment(this.filtros.fecha_desde))
        );
      }
      
      if (this.filtros.fecha_hasta) {
        resultado = resultado.filter(item => 
          moment(item.fecha_registro).isSameOrBefore(moment(this.filtros.fecha_hasta).endOf('day'))
        );
      }
      
      return resultado;
    },
    
    aplicarFiltros() {
      // Los computed se actualizan automáticamente
    },
    
    limpiarFiltros() {
      this.filtros = {
        nombre_negocio: '',
        producto: '',
        fecha_desde: '',
        fecha_hasta: ''
      };
    },
    
    detectarTipoMedida(unidadMedida) {
      if (!unidadMedida) return 'unidades';
      const unidad = String(unidadMedida).toLowerCase().trim();
      
      if (unidad.includes('litro') || unidad.includes('lt') || unidad.includes('ml')) {
        return 'litros';
      }
      if (unidad.includes('kg') || unidad.includes('kilo') || unidad.includes('gr') || unidad.includes('bolsa') && unidad.includes('k')) {
        return 'kilos';
      }
      return 'unidades';
    },

    getTipoTexto(unidadMedida) {
      const tipo = this.detectarTipoMedida(unidadMedida);
      return tipo.charAt(0).toUpperCase() + tipo.slice(1);
    },

    getTipoClass(unidadMedida) {
      return `tipo-${this.detectarTipoMedida(unidadMedida)}`;
    },

    formatearCantidad(cantidad, unidadMedida) {
      const tipo = this.detectarTipoMedida(unidadMedida);
      const num = parseFloat(cantidad) || 0;
      
      // Solo kilos permite decimales
      if (tipo === 'kilos') {
        return num.toFixed(2);
      }
      // Litros y unidades son enteros
      return Math.round(num).toString();
    },
    
    detectarTipoMedida(unidadMedida) {
      if (!unidadMedida) return 'unidades';
      const unidad = String(unidadMedida).toLowerCase().trim();
      
      if (unidad.includes('litro') || unidad.includes('lt') || unidad.includes('ml')) {
        return 'litros';
      }
      if (unidad.includes('kg') || unidad.includes('kilo') || unidad.includes('gr') || unidad.includes('bolsa') && unidad.includes('k')) {
        return 'kilos';
      }
      return 'unidades';
    },

    getTipoTexto(unidadMedida) {
      const tipo = this.detectarTipoMedida(unidadMedida);
      return tipo.charAt(0).toUpperCase() + tipo.slice(1);
    },

    getTipoClass(unidadMedida) {
      return `tipo-${this.detectarTipoMedida(unidadMedida)}`;
    },

    formatearCantidad(cantidad, unidadMedida) {
      const tipo = this.detectarTipoMedida(unidadMedida);
      const num = parseFloat(cantidad) || 0;
      
      // Solo kilos permite decimales
      if (tipo === 'kilos') {
        return num.toFixed(2);
      }
      // Litros y unidades son enteros
      return Math.round(num).toString();
    },
    
    formatearNumero(num) {
      return parseFloat(num).toFixed(2);
    },
    
    formatearFecha(fecha) {
      return moment(fecha).format('DD/MM/YYYY HH:mm');
    }
  }
};
</script>

<style scoped>
.stock-negocio-container {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #f5f7fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
}

/* Header */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 30px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.title-section {
  display: flex;
  align-items: center;
  gap: 15px;
}

.title-section i {
  font-size: 32px;
}

.title-section h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 14px;
}

.actions-section {
  display: flex;
  gap: 10px;
}

.btn-action {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-refresh {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.btn-refresh:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.3);
}

.btn-refresh:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Filtros */
.filtros-section {
  display: flex;
  gap: 15px;
  padding: 20px 30px;
  background: white;
  border-bottom: 1px solid #e0e0e0;
  flex-wrap: wrap;
}

.filtro-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.filtro-item label {
  font-size: 12px;
  font-weight: 500;
  color: #666;
}

.input-filtro {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  min-width: 150px;
}

.btn-limpiar {
  padding: 8px 16px;
  background: #f44336;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: auto;
  display: flex;
  align-items: center;
  gap: 5px;
}

.btn-limpiar:hover {
  background: #d32f2f;
}

/* Tabs */
.tabs-section {
  display: flex;
  gap: 10px;
  padding: 20px 30px 0;
  background: white;
}

.tab-btn {
  padding: 12px 24px;
  border: none;
  background: #f5f5f5;
  color: #666;
  border-radius: 8px 8px 0 0;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.tab-btn.active {
  background: white;
  color: #667eea;
  border-bottom: 3px solid #667eea;
}

/* Loading */
.loading-overlay {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 20px;
  background: white;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.spinning {
  animation: spin 1s linear infinite;
}

/* Tabla */
.table-container {
  flex: 1;
  background: white;
  padding: 20px 30px;
  overflow: auto;
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.data-table thead {
  background: #f8f9fa;
  position: sticky;
  top: 0;
  z-index: 10;
}

.data-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #333;
  border-bottom: 2px solid #e0e0e0;
  font-size: 13px;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
  color: #555;
}

.data-table tbody tr:hover {
  background: #f8f9fa;
}

.negocio-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.negocio-cell strong {
  color: #667eea;
}

.negocio-cell small {
  color: #999;
  font-size: 11px;
}

.cantidad-cell {
  text-align: right;
  font-weight: 600;
}

.cantidad-cell .cantidad {
  font-size: 1.1rem;
  color: #2c3e50;
  display: block;
}

.cantidad-cell .unidad-text {
  display: block;
  font-size: 0.75rem;
  color: #95a5a6;
  font-weight: normal;
  margin-top: 0.2rem;
}

.badge-tipo {
  display: inline-block;
  padding: 0.3rem 0.7rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-tipo.tipo-kilos {
  background: #e3f2fd;
  color: #1976d2;
}

.badge-tipo.tipo-litros {
  background: #e0f7fa;
  color: #0097a7;
}

.badge-tipo.tipo-unidades {
  background: #f3e5f5;
  color: #7b1fa2;
}

.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.no-data i {
  font-size: 48px;
  margin-bottom: 15px;
}

/* Estadísticas */
.stats-section {
  display: flex;
  gap: 30px;
  padding: 20px 30px;
  background: white;
  border-top: 1px solid #e0e0e0;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: #666;
}

.stat-item i {
  font-size: 18px;
  color: #667eea;
}

.stat-item strong {
  color: #333;
  font-size: 16px;
}
</style>
