<template>
  <div class="mapa-calor-container">
    <div class="modern-card">
      <div class="card-header">
        <div class="header-content">
          <div class="title-section">
            <i class="fas fa-fire"></i>
            <span>Mapa de Calor de Ventas</span>
          </div>
          <div class="controls-section">
            <button @click="cargarDatos" :disabled="cargando" class="btn-refresh">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': cargando }"></i>
              Refrescar
            </button>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <!-- Loading State -->
        <div v-show="cargando" class="loading-state">
          <i class="fas fa-spinner fa-spin fa-3x"></i>
          <p>Cargando datos del mapa de calor...</p>
        </div>
        
        <!-- No Data State -->
        <div v-show="!cargando && !tieneVentas" class="no-data-state">
          <i class="fas fa-chart-line fa-3x"></i>
          <h3>Sin datos de ventas</h3>
          <p>No hay ventas registradas en el rango de fechas seleccionado</p>
          <p class="date-range">{{ formatDate(startDate) }} - {{ formatDate(endDate) }}</p>
        </div>
        
        <!-- Heatmap Table - SIEMPRE RENDERIZADA, solo oculta -->
        <div v-show="!cargando && tieneVentas" class="heatmap-container">
          <div class="table-responsive">
            <table class="heatmap-table">
              <thead>
                <tr>
                  <th class="corner-cell">Día/Hora</th>
                  <th class="hour-header">7:00</th>
                  <th class="hour-header">8:00</th>
                  <th class="hour-header">9:00</th>
                  <th class="hour-header">10:00</th>
                  <th class="hour-header">11:00</th>
                  <th class="hour-header">12:00</th>
                  <th class="hour-header">13:00</th>
                  <th class="hour-header">14:00</th>
                  <th class="hour-header">15:00</th>
                  <th class="hour-header">16:00</th>
                  <th class="hour-header">17:00</th>
                  <th class="hour-header">18:00</th>
                  <th class="hour-header">19:00</th>
                  <th class="hour-header">20:00</th>
                  <th class="hour-header">21:00</th>
                  <th class="hour-header">22:00</th>
                  <th class="hour-header">23:00</th>
                </tr>
              </thead>
              <tbody>
                <!-- Domingo -->
                <tr>
                  <td class="day-header">Domingo</td>
                  <td v-for="h in horasArray" :key="'d0-h'+h" :data-day="0" :data-hour="h" class="heat-cell heat-0">0</td>
                </tr>
                <!-- Lunes -->
                <tr>
                  <td class="day-header">Lunes</td>
                  <td v-for="h in horasArray" :key="'d1-h'+h" :data-day="1" :data-hour="h" class="heat-cell heat-0">0</td>
                </tr>
                <!-- Martes -->
                <tr>
                  <td class="day-header">Martes</td>
                  <td v-for="h in horasArray" :key="'d2-h'+h" :data-day="2" :data-hour="h" class="heat-cell heat-0">0</td>
                </tr>
                <!-- Miércoles -->
                <tr>
                  <td class="day-header">Miércoles</td>
                  <td v-for="h in horasArray" :key="'d3-h'+h" :data-day="3" :data-hour="h" class="heat-cell heat-0">0</td>
                </tr>
                <!-- Jueves -->
                <tr>
                  <td class="day-header">Jueves</td>
                  <td v-for="h in horasArray" :key="'d4-h'+h" :data-day="4" :data-hour="h" class="heat-cell heat-0">0</td>
                </tr>
                <!-- Viernes -->
                <tr>
                  <td class="day-header">Viernes</td>
                  <td v-for="h in horasArray" :key="'d5-h'+h" :data-day="5" :data-hour="h" class="heat-cell heat-0">0</td>
                </tr>
                <!-- Sábado -->
                <tr>
                  <td class="day-header">Sábado</td>
                  <td v-for="h in horasArray" :key="'d6-h'+h" :data-day="6" :data-hour="h" class="heat-cell heat-0">0</td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Stats -->
          <div class="heatmap-stats">
            <div class="stat-item">
              <i class="fas fa-chart-line"></i>
              <span class="stat-label">Total Ventas:</span>
              <span class="stat-value">{{ stats.totalVentas }}</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-dollar-sign"></i>
              <span class="stat-label">Monto Total:</span>
              <span class="stat-value">${{ stats.montoTotal.toLocaleString('es-CL') }}</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-clock"></i>
              <span class="stat-label">Hora Pico:</span>
              <span class="stat-value">{{ stats.horaPico }}</span>
            </div>
            <div class="stat-item">
              <i class="fas fa-calendar-day"></i>
              <span class="stat-label">Día Pico:</span>
              <span class="stat-value">{{ stats.diaPico }}</span>
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

export default {
  name: 'MapaDeCalor',
  props: {
    startDate: String,
    endDate: String
  },
  data() {
    return {
      cargando: false,
      heatmapData: this.inicializarEstructura(), // Inicializar con estructura completa
      diasArray: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      horasArray: [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]
    };
  },
  computed: {
    tieneVentas() {
      // Verificar si hay alguna venta real (count > 0)
      try {
        for (let d = 0; d < 7; d++) {
          if (this.heatmapData && this.heatmapData[d]) {
            for (let h of this.horasArray) {
              if (this.heatmapData[d][h]) {
                const count = this.heatmapData[d][h].count;
                if (count !== undefined && count !== null && count > 0) {
                  console.log(`✅ [MAPA] Ventas encontradas: día ${d}, hora ${h}, count: ${count}`);
                  return true;
                }
              }
            }
          }
        }
        console.log('⚠️ [MAPA] No se encontraron ventas');
        return false;
      } catch (error) {
        console.error('❌ [MAPA] Error en tieneVentas:', error);
        return false;
      }
    },
    stats() {
      let totalVentas = 0;
      let montoTotal = 0;
      const ventasPorHora = {};
      const ventasPorDia = {};
      
      // Calcular stats
      for (let d = 0; d < 7; d++) {
        ventasPorDia[d] = 0;
        for (let h of this.horasArray) {
          if (this.heatmapData[d] && this.heatmapData[d][h]) {
            const data = this.heatmapData[d][h];
            const count = data.count || 0;
            const total = parseFloat(data.total) || 0;
            
            totalVentas += count;
            montoTotal += total;
            ventasPorHora[h] = (ventasPorHora[h] || 0) + count;
            ventasPorDia[d] += count;
          }
        }
      }
      
      // Encontrar hora pico
      let maxHora = { hora: 7, ventas: 0 };
      for (let h of this.horasArray) {
        if (ventasPorHora[h] > maxHora.ventas) {
          maxHora = { hora: h, ventas: ventasPorHora[h] };
        }
      }
      
      // Encontrar día pico
      let maxDia = { dia: 0, ventas: 0 };
      for (let d = 0; d < 7; d++) {
        if (ventasPorDia[d] > maxDia.ventas) {
          maxDia = { dia: d, ventas: ventasPorDia[d] };
        }
      }
      
      return {
        totalVentas,
        montoTotal,
        horaPico: `${maxHora.hora}:00`,
        diaPico: this.diasArray[maxDia.dia]
      };
    },
    maxVentas() {
      let max = 0;
      for (let d = 0; d < 7; d++) {
        for (let h of this.horasArray) {
          if (this.heatmapData[d] && this.heatmapData[d][h]) {
            const count = this.heatmapData[d][h].count || 0;
            if (count > max) max = count;
          }
        }
      }
      return max;
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
    console.log('🔥 [MAPA DE CALOR] Componente montado');
    this.cargarDatos();
  },
  methods: {
    async cargarDatos() {
      if (!this.startDate || !this.endDate) {
        console.log('⚠️ [MAPA DE CALOR] Sin fechas');
        return;
      }
      
      this.cargando = true;
      console.log('🔥 [MAPA DE CALOR] Cargando...', {
        startDate: this.startDate,
        endDate: this.endDate
      });
      
      try {
        // Obtener ID del negocio desde ConfigHelper (mismo que sucursal.js)
        const ConfigHelper = require('@/helpers/ConfigHelper.js').default;
        const appConfig = ConfigHelper.Config();
        const id = appConfig ? appConfig.Id : null;
        
        console.log('🏢 [MAPA DE CALOR] App config:', appConfig);
        console.log('🏢 [MAPA DE CALOR] ID negocio:', id);
        
        // Agregar hora al endDate (igual que sucursal.js)
        const endDateWithTime = this.endDate + ' 23:59:59';
        
        const url = BaseUrl.getUrl(`api/web/getAppSellsByHour?id=${id}&startDate=${this.startDate}&endDate=${endDateWithTime}`);
        console.log('🌐 [MAPA DE CALOR] URL:', url);
        
        const response = await Connection.request('GET', url);
        console.log('📊 [MAPA DE CALOR] Respuesta:', response);
        
        if (response && response.success && response.data) {
          this.procesarDatos(response.data);
        } else {
          console.log('⚠️ [MAPA DE CALOR] Respuesta sin datos válidos');
          this.heatmapData = this.inicializarEstructura();
        }
      } catch (error) {
        console.error('❌ [MAPA DE CALOR] Error:', error);
        this.$awn.alert('Error al cargar el mapa de calor');
        this.heatmapData = this.inicializarEstructura();
      } finally {
        this.cargando = false;
      }
    },
    
    procesarDatos(data) {
      console.log('🔄 [MAPA DE CALOR] Procesando:', data);
      
      // SIEMPRE inicializar estructura completa primero
      const estructuraVacia = this.inicializarEstructura();
      
      // Buscar heatmap_data en la estructura (mismo approach que sucursal.js)
      let heatmapDataApi = null;
      
      if (data && typeof data === 'object') {
        for (const app in data) {
          // Formato directo: app.heatmap_data
          if (data[app] && data[app].heatmap_data) {
            heatmapDataApi = data[app].heatmap_data;
            console.log('✅ [MAPA DE CALOR] Encontrado en heatmap_data directo');
            break;
          }
          // Formato anidado: app.original.heatmap_data
          else if (data[app] && data[app].original && data[app].original.heatmap_data) {
            heatmapDataApi = data[app].original.heatmap_data;
            console.log('✅ [MAPA DE CALOR] Encontrado en original.heatmap_data');
            break;
          }
        }
      }
      
      // Mezclar datos de API con estructura vacía
      // heatmapDataApi viene como OBJETO {dia: {hora: {count, total}}}, NO array
      if (heatmapDataApi && typeof heatmapDataApi === 'object') {
        console.log('✅ [MAPA DE CALOR] Mezclando datos del objeto heatmap');
        
        // Iterar sobre cada día en el objeto
        for (const dia in heatmapDataApi) {
          const diaNum = parseInt(dia);
          if (estructuraVacia[diaNum]) {
            // Iterar sobre cada hora en ese día
            for (const hora in heatmapDataApi[dia]) {
              const horaNum = parseInt(hora);
              if (estructuraVacia[diaNum][horaNum]) {
                const dataItem = heatmapDataApi[dia][hora];
                estructuraVacia[diaNum][horaNum] = {
                  count: typeof dataItem === 'object' ? (parseInt(dataItem.count) || 0) : (parseInt(dataItem) || 0),
                  total: typeof dataItem === 'object' ? (parseFloat(dataItem.total) || 0) : 0
                };
              }
            }
          }
        }
        console.log('✅ [MAPA DE CALOR] Datos mezclados correctamente');
      } else {
        console.log('⚠️ [MAPA DE CALOR] Sin datos de API válidos, usando estructura vacía');
      }
      
      console.log('✅ [MAPA DE CALOR] Estructura final:', estructuraVacia);
      
      // Actualizar DOM directamente como sucursal.js
      this.actualizarTablaDOM(estructuraVacia);
    },
    
    actualizarTablaDOM(heatmapData) {
      console.log('🎯 [MAPA] Actualizando tabla DOM...');
      
      // Encontrar valor máximo para normalizar colores
      let maxValue = 0;
      for (let day = 0; day <= 6; day++) {
        for (let hour = 7; hour <= 23; hour++) {
          if (heatmapData[day] && heatmapData[day][hour]) {
            const value = heatmapData[day][hour].count || 0;
            maxValue = Math.max(maxValue, value);
          }
        }
      }
      
      console.log(`📊 [MAPA] Valor máximo de ventas: ${maxValue}`);
      
      // Actualizar cada celda usando querySelector (igual que sucursal.js)
      for (let day = 0; day <= 6; day++) {
        for (let hour = 7; hour <= 23; hour++) {
          const cell = this.$el.querySelector(`[data-day="${day}"][data-hour="${hour}"]`);
          if (cell) {
            let value = 0;
            let total = 0;
            
            if (heatmapData[day] && heatmapData[day][hour]) {
              value = heatmapData[day][hour].count || 0;
              total = heatmapData[day][hour].total || 0;
            }
            
            // Actualizar contenido de texto
            cell.textContent = value;
            
            // Calcular intensidad (0-10) igual que sucursal.js
            const intensity = maxValue > 0 ? Math.floor((value / maxValue) * 10) : 0;
            
            // Mapear a 5 niveles de color (heat-0 a heat-5)
            let colorClass = 'heat-0';
            if (intensity >= 8) colorClass = 'heat-5';
            else if (intensity >= 6) colorClass = 'heat-4';
            else if (intensity >= 4) colorClass = 'heat-3';
            else if (intensity >= 2) colorClass = 'heat-2';
            else if (intensity > 0) colorClass = 'heat-1';
            
            // Aplicar clase de color
            cell.className = `heat-cell ${colorClass}`;
            
            // Agregar tooltip si hay ventas
            if (value > 0) {
              const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
              const tooltipText = `${dayNames[day]} ${hour}:00 - ${value} ventas ($${total.toLocaleString('es-CL')})`;
              cell.setAttribute('title', tooltipText);
            } else {
              cell.removeAttribute('title');
            }
          }
        }
      }
      
      // Actualizar heatmapData para los stats
      this.heatmapData = heatmapData;
      
      console.log('✅ [MAPA] Tabla DOM actualizada');
    },
    
    inicializarEstructura() {
      // Crear estructura completa con todos los días y horas en 0
      const estructura = {};
      for (let d = 0; d < 7; d++) {
        estructura[d] = {};
        for (let h of [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]) {
          estructura[d][h] = {
            count: 0,
            total: 0
          };
        }
      }
      return estructura;
    },
    
    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr + 'T00:00:00');
      return date.toLocaleDateString('es-CL');
    }
  }
};
</script>

<style scoped>
.mapa-calor-container {
  width: 100%;
  padding: 0;
}

.modern-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
}

.title-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.title-section i {
  font-size: 1.5rem;
}

.title-section span {
  font-size: 1.25rem;
  font-weight: 600;
}

.btn-refresh {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.btn-refresh:hover {
  background: rgba(255, 255, 255, 0.3);
}

.btn-refresh:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.card-body {
  padding: 1.5rem;
}

.loading-state,
.no-data-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
  color: #64748b;
}

.no-data-state i {
  color: #cbd5e1;
  margin-bottom: 1rem;
}

.no-data-state h3 {
  color: #1e293b;
  margin: 1rem 0 0.5rem;
}

.date-range {
  font-size: 0.875rem;
  margin-top: 0.5rem;
  background: #f1f5f9;
  padding: 0.5rem 1rem;
  border-radius: 6px;
}

.heatmap-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.table-responsive {
  overflow-x: auto;
}

.heatmap-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 4px;
}

.corner-cell {
  background: #f1f5f9;
  color: #64748b;
  padding: 0.75rem;
  font-weight: 600;
  text-align: center;
  border-radius: 8px;
}

.hour-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.75rem 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  text-align: center;
  min-width: 60px;
  border-radius: 8px;
}

.day-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.75rem;
  font-weight: 600;
  text-align: center;
  min-width: 100px;
  font-size: 0.875rem;
  border-radius: 8px;
}

.heat-cell {
  padding: 0.75rem 0.5rem;
  text-align: center;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  border-radius: 8px;
  min-width: 60px;
  font-size: 0.9rem;
}

.heat-cell:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 10;
}

/* Colores del heatmap simplificados */
.heat-0 { 
  background: #f8f9fa; 
  color: #6c757d; 
}

.heat-1 { 
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  color: #1565c0; 
}

.heat-2 { 
  background: linear-gradient(135deg, #90caf9 0%, #64b5f6 100%);
  color: #0d47a1; 
}

.heat-3 { 
  background: linear-gradient(135deg, #42a5f5 0%, #2196f3 100%);
  color: white; 
}

.heat-4 { 
  background: linear-gradient(135deg, #1e88e5 0%, #1976d2 100%);
  color: white; 
}

.heat-5 { 
  background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
  color: white; 
  font-weight: 700;
}

.heatmap-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  border-radius: 12px;
  border: 1px solid rgba(102, 126, 234, 0.2);
}

.stat-item i {
  font-size: 1.5rem;
  color: #667eea;
}

.stat-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin-left: auto;
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    gap: 1rem;
  }
  
  .hour-header,
  .heat-cell {
    min-width: 50px;
    font-size: 0.75rem;
    padding: 0.5rem 0.25rem;
  }
  
  .heatmap-stats {
    grid-template-columns: 1fr;
  }
}
</style>
