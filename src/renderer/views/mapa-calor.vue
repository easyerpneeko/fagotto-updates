<template>
  <div class="heatmap-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <div class="title-with-icon">
            <div class="icon-fire">
              <i class="fas fa-fire"></i>
            </div>
            <div>
              <h1 class="page-title">Mapas de Calor</h1>
              <p class="page-subtitle">Análisis visual de ventas y productos</p>
            </div>
          </div>
        </div>
        <div class="header-actions">
          <div class="date-range-controls">
            <label class="control-label">
              <i class="fas fa-calendar-alt"></i>
              Rango de fechas
            </label>
            <date-picker 
              class="date-picker-input" 
              format="YYYY-MM-DD" 
              type="date" 
              v-model="rangeDate" 
              range 
              placeholder="Seleccionar fechas" 
              confirm
              @change="updateHeatmap"
            ></date-picker>
          </div>
        </div>
      </div>
    </div>

    <!-- Heatmap Card - Ventas por Hora -->
    <div class="heatmap-container">
      <div class="heatmap-card">
        <div class="card-header">
          <div class="card-info">
            <h3 class="card-title">
              <i class="fas fa-chart-area"></i>
              Visualización de Ventas por Hora
            </h3>
            <p class="card-description">
              Identifica los patrones de ventas por día de la semana y hora del día. 
              Los colores más intensos representan mayor actividad de ventas.
            </p>
          </div>
          <div class="card-stats">
            <div class="stat-badge">
              <i class="fas fa-calendar-check"></i>
              <span>{{ formatDateRange }}</span>
            </div>
          </div>
        </div>
        
        <div class="card-body">
          <MapaDeCalor 
            ref="heatmap"
            :key="heatmapKey"
            :start-date="formatDate(rangeDate[0])" 
            :end-date="formatDate(rangeDate[1])"
          />
        </div>
      </div>
    </div>
    
    <!-- Mapa de Salsas -->
    <div class="heatmap-container" style="margin-bottom: 30px;">
      <MapaDeSalsas
        ref="mapaSalsas"
        :key="salsasKey"
        :start-date="formatDate(rangeDate[0])" 
        :end-date="formatDate(rangeDate[1])"
      />
    </div>

    <!-- Info Cards -->
    <div class="heatmap-container">
      <!-- Info Cards -->
      <div class="info-section">
        <div class="info-card">
          <div class="info-icon blue">
            <i class="fas fa-info-circle"></i>
          </div>
          <div class="info-content">
            <h4>¿Cómo leer el mapa?</h4>
            <p>Cada celda representa la cantidad de ventas en un día y hora específica. Los colores más intensos indican mayor actividad.</p>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon purple">
            <i class="fas fa-clock"></i>
          </div>
          <div class="info-content">
            <h4>Horas de operación</h4>
            <p>El mapa muestra las ventas desde las 7:00 AM hasta las 11:00 PM, capturando todo el horario comercial.</p>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon orange">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="info-content">
            <h4>Análisis de patrones</h4>
            <p>Identifica tus horas pico y días más activos para optimizar personal y recursos.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MapaDeCalor from '../components/charts/MapaDeCalor.vue';
import MapaDeSalsas from '../components/charts/MapaDeSalsas.vue';
import moment from 'moment';

export default {
  name: 'MapaCalorView',
  components: {
    MapaDeCalor,
    MapaDeSalsas
  },
  data() {
    return {
      rangeDate: [
        new Date(), // Fecha de hoy
        new Date()  // Fecha de hoy
      ],
      heatmapKey: 0, // Key único para forzar re-mount del componente
      salsasKey: 0 // Key para mapa de salsas
    };
  },
  computed: {
    formatDateRange() {
      if (!this.rangeDate || this.rangeDate.length !== 2) return '';
      const start = moment(this.rangeDate[0]).format('DD MMM');
      const end = moment(this.rangeDate[1]).format('DD MMM YYYY');
      return `${start} - ${end}`;
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return moment().format('YYYY-MM-DD');
      return moment(date).format('YYYY-MM-DD');
    },
    updateHeatmap() {
      // Incrementar keys para forzar re-mount completo de los componentes
      // El mounted() de cada componente cargará los datos automáticamente
      this.heatmapKey++;
      this.salsasKey++;
      console.log('🔄 [VIEW] Keys actualizados - Heatmap:', this.heatmapKey, 'Salsas:', this.salsasKey);
    }
  },
  mounted() {
    console.log('🔥 Mapa de Calor view mounted');
  }
};
</script>

<style scoped>
/* Page Layout */
.heatmap-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

/* Page Header */
.page-header {
  margin-bottom: 2rem;
  animation: fadeInDown 0.6s ease-out;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  flex-wrap: wrap;
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.icon-fire {
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  animation: pulse-fire 2s ease-in-out infinite;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

@keyframes pulse-fire {
  0%, 100% { 
    transform: scale(1);
    box-shadow: 0 8px 32px rgba(255, 107, 0, 0.2);
  }
  50% { 
    transform: scale(1.05);
    box-shadow: 0 8px 40px rgba(255, 107, 0, 0.4);
  }
}

.icon-fire i {
  font-size: 3rem;
  color: #fff;
  filter: drop-shadow(0 2px 8px rgba(255, 107, 0, 0.5));
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.page-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 0.5rem 0 0 0;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Date Range Controls */
.date-range-controls {
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(10px);
}

.control-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

.control-label i {
  color: #667eea;
}

.date-picker-input {
  width: 100%;
  min-width: 280px;
}

/* Heatmap Container */
.heatmap-container {
  animation: fadeInUp 0.6s ease-out 0.2s backwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Heatmap Card */
.heatmap-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  flex-wrap: wrap;
}

.card-info {
  flex: 1;
  min-width: 250px;
}

.card-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 0.75rem 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-title i {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.5rem;
  border-radius: 10px;
}

.card-description {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  line-height: 1.6;
}

.card-stats {
  display: flex;
  gap: 1rem;
}

.stat-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.75rem 1.25rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  backdrop-filter: blur(10px);
}

.stat-badge i {
  font-size: 1.1rem;
}

.card-body {
  padding: 2rem;
}

/* Info Section */
.info-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.info-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  gap: 1.25rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.info-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.info-icon.blue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.info-icon.purple {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.info-icon.orange {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.info-icon i {
  font-size: 1.5rem;
  color: white;
}

.info-content h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.info-content p {
  margin: 0;
  font-size: 0.9rem;
  color: #6c757d;
  line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .header-content {
    flex-direction: column;
  }
  
  .date-range-controls {
    width: 100%;
  }
  
  .date-picker-input {
    min-width: 100%;
  }
}

@media (max-width: 768px) {
  .heatmap-page {
    padding: 1rem;
  }
  
  .title-with-icon {
    flex-direction: column;
    text-align: center;
  }
  
  .icon-fire {
    width: 60px;
    height: 60px;
  }
  
  .icon-fire i {
    font-size: 2rem;
  }
  
  .page-title {
    font-size: 1.8rem;
  }
  
  .page-subtitle {
    font-size: 1rem;
  }
  
  .card-header {
    padding: 1.5rem;
    flex-direction: column;
  }
  
  .card-body {
    padding: 1rem;
  }
  
  .info-section {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .page-header {
    margin-bottom: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .heatmap-card {
    border-radius: 16px;
  }
  
  .info-card {
    flex-direction: column;
    text-align: center;
  }
  
  .info-icon {
    margin: 0 auto;
  }
}
</style>
