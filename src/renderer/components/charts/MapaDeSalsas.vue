<template>
  <div class="mapa-salsas-container">
    <div class="modern-card">
      <!-- Header con selector de vista -->
      <div class="card-header">
        <div class="header-content">
          <div class="title-section">
            <i class="fas fa-utensils"></i>
            <span>{{ titulo }}</span>
          </div>
          <div class="controls-section">
            <div class="btn-group">
              <button 
                @click="cambiarVista('month')" 
                :class="['btn-vista', { active: vista === 'month' }]"
              >
                <i class="fas fa-calendar-alt"></i>
                Mes
              </button>
              <button 
                @click="cambiarVista('week')" 
                :class="['btn-vista', { active: vista === 'week' }]"
              >
                <i class="fas fa-calendar-week"></i>
                Semana
              </button>
            </div>
            <button @click="cargarDatos" :disabled="cargando" class="btn-refresh">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': cargando }"></i>
              Refrescar
            </button>
          </div>
        </div>
      </div>
      
      <!-- Body -->
      <div class="card-body">
        <!-- Loading -->
        <div v-show="cargando" class="loading-state">
          <i class="fas fa-spinner fa-spin fa-3x"></i>
          <p>Cargando datos de salsas...</p>
        </div>
        
        <!-- KPIs de Totales -->
        <div v-show="!cargando" class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <i class="fas fa-bottle-droplet fa-2x"></i>
              <div class="kpi-content">
                <div class="kpi-label">Salsa Extra</div>
                <div class="kpi-value">{{ totales.salsaExtra }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
              <i class="fas fa-bacon fa-2x"></i>
              <div class="kpi-content">
                <div class="kpi-label">Fettucine Total</div>
                <div class="kpi-value">{{ totales.fettucine }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
              <i class="fas fa-bacon fa-2x"></i>
              <div class="kpi-content">
                <div class="kpi-label">Bigoli Total</div>
                <div class="kpi-value">{{ totales.bigoli }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
              <i class="fas fa-cheese fa-2x"></i>
              <div class="kpi-content">
                <div class="kpi-label">Queso Extra</div>
                <div class="kpi-value">{{ totales.quesoExtra }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
              <i class="fas fa-bread-slice fa-2x"></i>
              <div class="kpi-content">
                <div class="kpi-label">Ciabattas Total</div>
                <div class="kpi-value">{{ totales.focaccias }}</div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Tabla de Salsas -->
        <div v-show="!cargando" class="table-responsive mb-4">
          <table class="table table-sm table-bordered sauce-heatmap-table">
            <thead>
              <tr>
                <th class="sticky-col">Salsa</th>
                <th v-for="(dia, index) in diasMostrar" :key="'dia-'+index" class="day-column">
                  {{ formatDia(dia) }}<br>
                  <small>{{ formatDiaCompleto(dia) }}</small>
                </th>
                <th class="total-column">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="salsa in salsasConfig" :key="salsa.nombre">
                <td class="sticky-col sauce-name">
                  {{ salsa.emoji }} {{ salsa.nombre }} ({{ salsa.gramaje }}g)
                </td>
                <td 
                  v-for="(dia, index) in diasMostrar" 
                  :key="salsa.nombre+'-'+index"
                  :class="getColorClass(ventasPorSalsa[salsa.nombre] ? ventasPorSalsa[salsa.nombre][dia.getDate()] : 0)"
                  class="sauce-cell"
                >
                  <div class="cell-content">
                    <strong>{{ getVentasDia(salsa.nombre, dia) }}</strong><br>
                    <small>{{ getPesoDia(salsa.nombre, dia) }}</small>
                  </div>
                </td>
                <td class="total-cell">
                  <div class="cell-content">
                    <strong>{{ getTotalSalsa(salsa.nombre) }}</strong><br>
                    <small>{{ getPesoTotalSalsa(salsa.nombre) }}</small>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Leyenda y Stats -->
        <div v-show="!cargando" class="row mb-4">
          <div class="col-md-6">
            <div class="sauce-legend">
              <h6><i class="fas fa-palette me-2"></i>Leyenda de Intensidad</h6>
              <div class="d-flex gap-2 flex-wrap">
                <span class="badge level-0">0-5</span>
                <span class="badge level-1">6-15</span>
                <span class="badge level-2">16-30</span>
                <span class="badge level-3">31-50</span>
                <span class="badge level-4">51+</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="sauce-stats">
              <h6><i class="fas fa-trophy me-2"></i>Salsa Más Vendida</h6>
              <div class="top-sauce" v-if="salsaMasVendida">
                <strong>{{ salsaMasVendida.emoji }} {{ salsaMasVendida.nombre }}</strong><br>
                {{ salsaMasVendida.total }} unidades vendidas
                <span class="percentage">{{ salsaMasVendida.porcentaje }}% del total</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Desglose de Ciabattas -->
        <div v-show="!cargando && focacciasDesglose.length > 0" class="focaccias-section">
          <div class="focaccias-card">
            <h6 class="mb-3">
              <i class="fas fa-bread-slice me-2"></i>
              Desglose de Ciabattas por Nombre
            </h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered mb-0">
                <thead>
                  <tr>
                    <th>Nombre Ciabatta</th>
                    <th class="text-center">Cantidad Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="focaccia in focacciasDesglose" :key="focaccia.nombre">
                    <td><i class="fas fa-bread-slice"></i> {{ focaccia.nombre }}</td>
                    <td class="text-center"><strong>{{ focaccia.total }}</strong></td>
                  </tr>
                  <tr class="table-active">
                    <td><strong>TOTAL CIABATTAS:</strong></td>
                    <td class="text-center"><strong>{{ totales.focaccias }}</strong></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';
import moment from 'moment';

const SALSAS_CONFIG = [
  { nombre: 'Boloñesa', keywords: ['bolonesa', 'boloñesa'], emoji: '🍝', gramaje: 125 },
  { nombre: 'Pesto', keywords: ['pesto'], emoji: '🌿', gramaje: 80 },
  { nombre: 'Alfredo', keywords: ['alfredo'], emoji: '🧀', gramaje: 140 },
  { nombre: 'Champiñón', keywords: ['champinon', 'champiñon', 'champiñón'], emoji: '🍄', gramaje: 140 },
  { nombre: 'Camarón', keywords: ['camaron', 'camarón'], emoji: '🦐', gramaje: 140 },
  { nombre: 'Pollo Mostaza', keywords: ['pollo mostaza', 'crema/pollo/mostaza', 'crema pollo mostaza'], emoji: '🍗', gramaje: 140 },
  { nombre: 'Cheddar', keywords: ['cheddar'], emoji: '🧀', gramaje: 140 },
  { nombre: 'Pomodoro', keywords: ['pomodoro'], emoji: '🍅', gramaje: 125 }
];

export default {
  name: 'MapaDeSalsas',
  props: {
    startDate: String,
    endDate: String
  },
  data() {
    return {
      cargando: false,
      vista: 'month', // 'month' o 'week'
      salsasConfig: SALSAS_CONFIG,
      ventasPorSalsa: {},
      diasMostrar: [],
      totales: {
        salsaExtra: 0,
        fettucine: 0,
        bigoli: 0,
        quesoExtra: 0,
        focaccias: 0
      },
      focacciasDesglose: []
    };
  },
  computed: {
    titulo() {
      if (!this.startDate || !this.endDate) return 'Mapa de Calor - Ventas de Salsas';
      
      const inicio = moment(this.startDate);
      const fin = moment(this.endDate);
      
      if (inicio.isSame(fin, 'day')) {
        return `Mapa de Calor - Ventas de Salsas (${inicio.format('D [de] MMMM [de] YYYY')})`;
      }
      
      return `Mapa de Calor - Ventas de Salsas (${inicio.format('D MMM')} - ${fin.format('D MMM YYYY')})`;
    },
    salsaMasVendida() {
      let maxSalsa = null;
      let maxTotal = 0;
      let totalGeneral = 0;
      
      this.salsasConfig.forEach(salsa => {
        const total = this.getTotalSalsa(salsa.nombre);
        totalGeneral += total;
        
        if (total > maxTotal) {
          maxTotal = total;
          maxSalsa = { ...salsa, total };
        }
      });
      
      if (maxSalsa && totalGeneral > 0) {
        maxSalsa.porcentaje = ((maxTotal / totalGeneral) * 100).toFixed(1);
      }
      
      return maxSalsa;
    }
  },
  watch: {
    startDate() {
      this.cargarDatos();
    },
    endDate() {
      this.cargarDatos();
    }
  },
  mounted() {
    console.log('🌶️ [MAPA SALSAS] Componente montado');
    this.cargarDatos();
  },
  methods: {
    cambiarVista(nuevaVista) {
      this.vista = nuevaVista;
      this.cargarDatos();
    },
    
    async cargarDatos() {
      if (!this.startDate || !this.endDate) return;
      
      this.cargando = true;
      console.log('🌶️ [MAPA SALSAS] Cargando datos...');
      
      try {
        const ConfigHelper = require('@/helpers/ConfigHelper.js').default;
        const appConfig = ConfigHelper.Config();
        const id = appConfig ? appConfig.Id : null;
        
        const endDateWithTime = this.endDate + ' 23:59:59';
        const url = BaseUrl.getUrl(`api/web/getAppSells?id=${id}&startDate=${this.startDate}&endDate=${endDateWithTime}&boleta=true&factura=true&per_page=10000`);
        
        console.log('🌐 [MAPA SALSAS] URL:', url);
        
        const response = await Connection.request('GET', url);
        
        if (response && response.success && response.data) {
          this.procesarVentas(response.data);
        }
      } catch (error) {
        console.error('❌ [MAPA SALSAS] Error:', error);
        this.$awn.alert('Error al cargar datos de salsas');
      } finally {
        this.cargando = false;
      }
    },
    
    procesarVentas(data) {
      console.log('🔍 [MAPA SALSAS] Procesando ventas...');
      
      // Extraer ventas del formato de respuesta
      let ventas = [];
      for (const app in data) {
        if (data[app] && data[app].original) {
          if (data[app].original.data) {
            ventas = data[app].original.data;
          } else if (Array.isArray(data[app].original)) {
            ventas = data[app].original;
          }
          break;
        }
      }
      
      console.log(`📦 [MAPA SALSAS] Ventas encontradas: ${ventas.length}`);
      
      // ✅ REINICIAR TODOS LOS TOTALES ANTES DE PROCESAR
      this.totales = {
        salsaExtra: 0,
        fettucine: 0,
        bigoli: 0,
        quesoExtra: 0,
        focaccias: 0
      };
      this.focacciasDesglose = [];
      
      // Extraer productos
      const productos = [];
      ventas.forEach(venta => {
        if (venta.products && Array.isArray(venta.products)) {
          venta.products.forEach(producto => {
            // Validar que el producto tenga nombre antes de agregarlo
            if (producto.name && typeof producto.name === 'string') {
              productos.push({
                name: producto.name.toLowerCase(),
                originalName: producto.name,
                quantity: parseInt(producto.quantity) || 1,
                created_at: venta.created_at
              });
            }
          });
        }
      });
      
      console.log(`📦 [MAPA SALSAS] Productos extraídos: ${productos.length}`);
      
      // Generar días
      this.generarDias();
      
      // Inicializar matriz de ventas
      this.ventasPorSalsa = {};
      this.salsasConfig.forEach(salsa => {
        this.ventasPorSalsa[salsa.nombre] = {};
      });
      
      // Procesar cada producto
      productos.forEach(producto => {
        const fecha = moment(producto.created_at);
        const dia = fecha.date();
        
        // Buscar salsa correspondiente
        this.salsasConfig.forEach(salsa => {
          const encontrado = salsa.keywords.some(keyword => 
            producto.name.includes(keyword.toLowerCase())
          );
          
          if (encontrado) {
            if (!this.ventasPorSalsa[salsa.nombre][dia]) {
              this.ventasPorSalsa[salsa.nombre][dia] = 0;
            }
            this.ventasPorSalsa[salsa.nombre][dia] += parseInt(producto.quantity) || 0;
          }
        });
        
        // Contar totales especiales
        const nombreLower = producto.name;
        if (nombreLower.includes('extra') && nombreLower.includes('salsa')) {
          this.totales.salsaExtra += parseInt(producto.quantity) || 0;
        }
        if (nombreLower.includes('fettucine') || nombreLower.includes('fettuccine')) {
          this.totales.fettucine += parseInt(producto.quantity) || 0;
        }
        if (nombreLower.includes('bigoli')) {
          this.totales.bigoli += parseInt(producto.quantity) || 0;
        }
        if (nombreLower.includes('extra') && nombreLower.includes('queso')) {
          this.totales.quesoExtra += parseInt(producto.quantity) || 0;
        }
        if (nombreLower.includes('ciabatta')) {
          this.totales.focaccias += parseInt(producto.quantity) || 0;
          
          // Agregar al desglose
          const focacciaExistente = this.focacciasDesglose.find(f => f.nombre === producto.originalName);
          if (focacciaExistente) {
            focacciaExistente.total += parseInt(producto.quantity) || 0;
          } else {
            this.focacciasDesglose.push({
              nombre: producto.originalName,
              total: parseInt(producto.quantity) || 0
            });
          }
        }
      });
      
      console.log('✅ [MAPA SALSAS] Datos procesados');
    },
    
    generarDias() {
      this.diasMostrar = [];
      const inicio = moment(this.startDate);
      const fin = moment(this.endDate);
      
      const current = inicio.clone();
      while (current.isSameOrBefore(fin, 'day')) {
        this.diasMostrar.push(current.toDate());
        current.add(1, 'day');
      }
    },
    
    formatDia(dia) {
      return moment(dia).format('D');
    },
    
    formatDiaCompleto(dia) {
      return moment(dia).format('ddd');
    },
    
    getVentasDia(nombreSalsa, dia) {
      const diaNum = dia.getDate();
      return this.ventasPorSalsa[nombreSalsa] && this.ventasPorSalsa[nombreSalsa][diaNum] 
        ? this.ventasPorSalsa[nombreSalsa][diaNum] 
        : 0;
    },
    
    getPesoDia(nombreSalsa, dia) {
      const ventas = this.getVentasDia(nombreSalsa, dia);
      const salsa = this.salsasConfig.find(s => s.nombre === nombreSalsa);
      if (!salsa || ventas === 0) return '0g';
      
      const pesoGramos = ventas * salsa.gramaje;
      if (pesoGramos >= 1000) {
        return `${(pesoGramos / 1000).toFixed(1)}kg`;
      }
      return `${pesoGramos}g`;
    },
    
    getTotalSalsa(nombreSalsa) {
      let total = 0;
      if (this.ventasPorSalsa[nombreSalsa]) {
        Object.values(this.ventasPorSalsa[nombreSalsa]).forEach(valor => {
          total += valor;
        });
      }
      return total;
    },
    
    getPesoTotalSalsa(nombreSalsa) {
      const total = this.getTotalSalsa(nombreSalsa);
      const salsa = this.salsasConfig.find(s => s.nombre === nombreSalsa);
      if (!salsa || total === 0) return '0g';
      
      const pesoGramos = total * salsa.gramaje;
      if (pesoGramos >= 1000) {
        return `${(pesoGramos / 1000).toFixed(1)}kg`;
      }
      return `${pesoGramos}g`;
    },
    
    getColorClass(valor) {
      if (valor === 0) return 'level-0';
      if (valor <= 5) return 'level-0';
      if (valor <= 15) return 'level-1';
      if (valor <= 30) return 'level-2';
      if (valor <= 50) return 'level-3';
      return 'level-4';
    }
  }
};
</script>

<style scoped>
/* Base Container */
.mapa-salsas-container {
  width: 100%;
}

/* Card Styles */
.modern-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.title-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 600;
}

.controls-section {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn-group {
  display: flex;
  gap: 0.5rem;
}

.btn-vista, .btn-refresh {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.btn-vista:hover, .btn-refresh:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.btn-vista.active {
  background: white;
  color: #667eea;
  font-weight: 600;
}

.btn-refresh:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Card Body */
.card-body {
  padding: 2rem;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 3rem;
  color: #667eea;
}

.loading-state i {
  margin-bottom: 1rem;
}

/* KPI Cards */
.kpi-card {
  padding: 1.5rem;
  border-radius: 12px;
  color: white;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.kpi-content {
  flex: 1;
}

.kpi-label {
  font-size: 0.75rem;
  opacity: 0.9;
  margin-bottom: 0.25rem;
}

.kpi-value {
  font-size: 1.5rem;
  font-weight: bold;
}

/* Table Styles */
.sauce-heatmap-table {
  font-size: 0.85rem;
  margin-bottom: 0;
}

.sauce-heatmap-table thead th {
  background: #f8f9fa;
  font-weight: 600;
  text-align: center;
  padding: 0.75rem 0.5rem;
  border: 1px solid #dee2e6;
  vertical-align: middle;
}

.sauce-heatmap-table .sticky-col {
  position: sticky;
  left: 0;
  background: white;
  z-index: 10;
  font-weight: 600;
}

.sauce-heatmap-table tbody .sticky-col {
  background: #f8f9fa;
  border-right: 2px solid #dee2e6;
}

.sauce-name {
  white-space: nowrap;
  padding: 0.75rem;
}

.sauce-cell {
  text-align: center;
  padding: 0.5rem;
  border: 1px solid #dee2e6;
  transition: all 0.2s ease;
}

.sauce-cell:hover {
  transform: scale(1.05);
  z-index: 5;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.cell-content {
  line-height: 1.4;
}

.cell-content strong {
  font-size: 1rem;
}

.cell-content small {
  font-size: 0.75rem;
  color: #6c757d;
}

.total-cell {
  background: #e3f2fd;
  font-weight: bold;
  text-align: center;
  padding: 0.5rem;
}

/* Color Levels */
.level-0 {
  background: #f8f9fa;
  color: #6c757d;
}

.level-1 {
  background: #e3f2fd;
  color: #1976d2;
}

.level-2 {
  background: #90caf9;
  color: #0d47a1;
}

.level-3 {
  background: #42a5f5;
  color: white;
}

.level-4 {
  background: #1565c0;
  color: white;
}

/* Legend */
.sauce-legend h6, .sauce-stats h6 {
  font-size: 0.95rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.75rem;
}

.sauce-legend .badge {
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  font-weight: 500;
}

/* Top Sauce */
.top-sauce {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

.top-sauce strong {
  font-size: 1.1rem;
  color: #667eea;
  display: block;
  margin-bottom: 0.25rem;
}

.percentage {
  display: inline-block;
  margin-left: 0.5rem;
  padding: 0.25rem 0.5rem;
  background: #667eea;
  color: white;
  border-radius: 4px;
  font-size: 0.8rem;
}

/* Focaccias Section */
.focaccias-section {
  margin-top: 2rem;
}

.focaccias-card {
  background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
  padding: 1.5rem;
  border-radius: 12px;
  color: white;
}

.focaccias-card h6 {
  color: white;
  font-weight: 600;
  margin-bottom: 1rem;
}

.focaccias-card .table-responsive {
  background: white;
  border-radius: 8px;
  padding: 1rem;
}

.focaccias-card table {
  margin-bottom: 0;
  color: #000000;
}

.focaccias-card thead {
  background: #f8f9fa;
}

.focaccias-card th {
  font-weight: 600;
  padding: 0.75rem;
  color: #000000;
}

.focaccias-card td {
  padding: 0.75rem;
  color: #000000;
}

/* Responsive */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: stretch;
  }
  
  .controls-section {
    flex-direction: column;
  }
  
  .kpi-card {
    margin-bottom: 1rem;
  }
  
  .sauce-heatmap-table {
    font-size: 0.75rem;
  }
  
  .cell-content strong {
    font-size: 0.9rem;
  }
}
</style>
