<template>
  <div class="bg-home-gradient">
    <div class="bg-full-height-gradient">
      <div class="welcome-overlay d-flex flex-column justify-content-center align-items-center py-5">
        
        <!-- Mensaje de Turno Requerido -->
        <div v-if="!turnoActivo" class="turno-required-overlay">
          <div class="turno-required-card text-center p-5">
            <div class="turno-icon-container mb-4">
              <i class="fas fa-clock-o turno-icon"></i>
            </div>
            
            <h2 class="turno-title mb-3">
              ¡Hola, {{ this.me.fullname }}! 👋
            </h2>
            
            <div class="admin-message mb-4">
              <i class="fas fa-user-shield me-2"></i>
              <strong>Mensaje del Administrador del Sistema</strong>
            </div>
            
            <p class="turno-description mb-4">
              Para garantizar un control adecuado de la caja y mantener la integridad de nuestros procesos financieros, 
              <strong>debes iniciar un turno de caja</strong> antes de comenzar a utilizar la aplicación.
            </p>
            
            <div class="turno-benefits mb-4">
              <div class="benefit-item">
                <i class="fas fa-shield-alt text-success me-2"></i>
                Control y seguridad financiera
              </div>
              <div class="benefit-item">
                <i class="fas fa-chart-line text-info me-2"></i>
                Seguimiento de transacciones
              </div>
              <div class="benefit-item">
                <i class="fas fa-history text-warning me-2"></i>
                Auditoría completa
              </div>
            </div>
            
            <button 
              class="btn btn-primary btn-lg turno-btn"
              @click="irAArqueo"
              :disabled="cargandoNavegacion"
            >
              <i class="fas fa-play me-2"></i>
              {{ cargandoNavegacion ? 'Redirigiendo...' : 'Iniciar Turno de Caja' }}
            </button>
            
            <p class="turno-note mt-3">
              <i class="fas fa-info-circle me-1"></i>
              Este proceso es obligatorio y solo toma unos segundos
            </p>
          </div>
        </div>

        <!-- Contenido normal del home (solo se muestra con turno activo) -->
        <div v-else class="welcome-content-only text-center p-5 my-4">
          <div class="welcome-logo-container mb-4">
            <img class="home_logo" src="../assets/logo.png" alt="fagotto-erp">
          </div>
          
          <!-- 🎃 MENSAJE DE HALLOWEEN (solo visible el 31 de Octubre) -->
          <div v-if="isHalloweenDay" class="halloween-message">
            <div class="halloween-pumpkins">
              <span class="pumpkin">🎃</span>
              <span class="pumpkin">🎃</span>
              <span class="pumpkin">🎃</span>
            </div>
            <h2 class="halloween-title">¡FELIZ HALLOWEEN!</h2>
            <p class="halloween-subtitle">Te Desea Fagotto</p>
            <div class="halloween-decoration">
              <span>👻</span>
              <span>💀</span>
              <span>🕷️</span>
              <span>🦇</span>
            </div>
          </div>
          
          <div class="welcome-content">
            <h1 class="welcome-title mb-3">
              <span class="greeting-time">{{ greetingMessage }}</span>, 
              <span class="user-name">{{ this.me.fullname }}</span>! 👋
            </h1>
            <p class="welcome-subtitle">
              Tu sistema está listo para <span class="highlight-text">seguir creciendo</span> 🚀
            </p>
            <div class="welcome-badge">
              <i class="fas fa-rocket me-2"></i>
              Fagotto ERP 
            </div>
            
            <!-- Badge de turno activo -->
            <div class="turno-activo-badge mt-4">
              <i class="fas fa-check-circle me-2"></i>
              Turno de caja activo
            </div>
          </div>
        </div>

      </div>
    </div>
    
    <autoUpdate />
  </div>
</template>

<script>
import Loader from '@/helpers/Loader';
import ConfigHelper from '@/helpers/ConfigHelper.js';
import autoUpdate from '../components/autoUpdate.vue';

export default {
  name: 'home',
  props: ['value', 'feedsWatch'],
  components: { autoUpdate },
  data() {
    return {
      xml_string: null,
      app_id: 0,
      cargandoNavegacion: false,
    }
  },
  async mounted() {
    console.log('🏠 HOME: USUARIO LOGUEADO', this.me);
    // Verificar estado del turno desde la base de datos (no localStorage)
    await this.verificarEstadoTurno();
  },
  computed: {
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input', offOn) }
    },
    me: { get() { return this.$store.getters['main/user']; } },

    siiInstalled: { async get() { return await ConfigHelper.ConfStr('modulos.ventas.submodulos.sii'); } },

    // Computed para saludo dinámico
    greetingMessage() {
      const hora = new Date().getHours();
      if (hora < 12) return 'Buenos días';
      if (hora < 18) return 'Buenas tardes';
      return 'Buenas noches';
    },

    // ✅ NUEVO: Obtener estado del turno desde el store (base de datos)
    turnoActivo() {
      return this.$store.getters['arqueo/turnoActivo'];
    },

    // 🎃 NUEVO: Verificar si es semana de Halloween (28-31 de Octubre)
    isHalloweenDay() {
      const hoy = new Date();
      const mes = hoy.getMonth(); // 0 = Enero, 9 = Octubre
      const dia = hoy.getDate();
      // Mostrar desde el 28 hasta el 31 de Octubre (toda la semana de Halloween)
      return mes === 9 && dia >= 28 && dia <= 31;
    }

  },
  watch: {
    // Watch removido - ya no necesitamos feeds
  },
  methods: {
    async getApp() {
      var request = await this.$store.dispatch('main/refreshData', '?slim');
      this.app_id = request.data.Id;
      return request;
    },

    // ✅ NUEVO: Verificar estado del turno desde la BASE DE DATOS
    async verificarEstadoTurno() {
      try {
        console.log('🏠 HOME: Verificando estado del turno en la base de datos...');
        
        // Llamar al action del store que consulta la API
        const resultado = await this.$store.dispatch('arqueo/verificarEstadoTurno');
        
        if (resultado.success) {
          console.log('🏠 HOME: ✅ Estado del turno obtenido:', resultado.data);
          // El store ya actualiza automáticamente el estado
          
          if (this.turnoActivo) {
            console.log('🏠 HOME: ✅ Turno activo, acceso permitido');
          } else {
            console.log('🏠 HOME: ❌ No hay turno activo, mostrando mensaje obligatorio');
          }
        } else {
          console.log('🏠 HOME: ⚠️ Error consultando estado del turno:', resultado.message);
          // En caso de error, asumir que no hay turno activo
        }
        
      } catch (error) {
        console.error('🏠 HOME: Error verificando estado del turno:', error);
        // En caso de error, asumir que no hay turno activo
      }
    },

    // Redirigir al arqueo de caja
    async irAArqueo() {
      try {
        this.cargandoNavegacion = true;
        
        // Pequeño delay para mejor UX
        setTimeout(() => {
          // Navegar al arqueo de caja - RUTA CORREGIDA
          this.$router.push('/inicio/arqueo-caja');
        }, 500);
        
      } catch (error) {
        console.error('🏠 HOME: Error navegando al arqueo:', error);
        this.cargandoNavegacion = false;
      }
    }
  }
}
</script>
<style scoped>
/* ===== FONDO GRADIENTE COMPLETO PARA TODA LA PÁGINA ===== */
.bg-home-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
  width: 100%;
  position: relative;
  overflow: hidden;
}

.bg-home-gradient::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.05), transparent);
  animation: page-shine 8s infinite;
  pointer-events: none;
}

@keyframes page-shine {
  0% { transform: translateX(-100%) translateY(-100%); }
  50% { transform: translateX(100%) translateY(100%); }
  100% { transform: translateX(-100%) translateY(-100%); }
}

.bg-full-height-gradient {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.welcome-overlay {
  position: relative;
  z-index: 2;
  width: 100%;
}

.order-card {
  color: #fff;
}

.bg-c-blue {
  background: linear-gradient(45deg, #06192f, #4da0ff);
}

.bg-c-green {
  background: linear-gradient(45deg, #006d1d, #4fc38e);
}

.bg-c-yellow {
  background: linear-gradient(45deg, #a26000, #ffb54b);
}

.bg-c-pink {
  background: linear-gradient(45deg, #731a22, #ec0000);
}


.card {
  border-radius: 5px;
  -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
  box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
  border: none;
  margin-bottom: 30px;
  -webkit-transition: all 0.3s ease-in-out;
  transition: all 0.3s ease-in-out;
}

.card .card-block {
  padding: 25px;
}

.card-title {
  float: left;
  font-size: 1.1rem;
  font-weight: 400;
  margin: 0;
}

.order-card i {
  font-size: 26px;
}

.f-left {
  float: left;
}

.f-right {
  float: right;
}

.bg-one {
  background-color: var(--primary);
  color: #fff !important;
}

/* ===== NUEVO HERO WELCOME STYLES SIN CUADRO ===== */
.welcome-content-only {
  max-width: 600px;
  margin: 0 auto;
  position: relative;
  animation: welcome-float-subtle 8s ease-in-out infinite alternate;
}

@keyframes welcome-float-subtle {
  0% { 
    transform: translateY(0px);
  }
  100% { 
    transform: translateY(-8px);
  }
}

@keyframes welcome-shine {
  0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
  50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
  100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
}

.welcome-logo-container {
  position: relative;
  z-index: 2;
}

.home_logo {
  max-width: 140px;
  filter: drop-shadow(0 15px 30px rgba(0,0,0,0.4));
  transition: transform 0.5s ease;
  animation: logo-glow 4s ease-in-out infinite alternate;
}

@keyframes logo-glow {
  0% { 
    filter: drop-shadow(0 15px 30px rgba(0,0,0,0.4));
  }
  100% { 
    filter: drop-shadow(0 20px 40px rgba(0,0,0,0.6)) drop-shadow(0 0 20px rgba(255,255,255,0.3));
  }
}

.home_logo:hover {
  transform: scale(1.1) rotate(3deg);
}

.welcome-content {
  position: relative;
  z-index: 2;
  color: white;
}

.welcome-title {
  font-size: 2.8rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  text-shadow: 0 8px 16px rgba(0,0,0,0.5);
  animation: title-pulse 6s ease-in-out infinite alternate;
}

@keyframes title-pulse {
  0% { 
    text-shadow: 0 8px 16px rgba(0,0,0,0.5);
  }
  100% { 
    text-shadow: 0 12px 24px rgba(0,0,0,0.7), 0 0 30px rgba(255,255,255,0.2);
  }
}

.greeting-time {
  background: linear-gradient(45deg, #fff, #e1d5ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  display: inline-block;
}

.user-name {
  background: linear-gradient(45deg, #ffd700, #ffed4e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  display: inline-block;
  text-transform: capitalize;
}

.welcome-subtitle {
  font-size: 1.3rem;
  margin-bottom: 2rem;
  opacity: 0.95;
  line-height: 1.6;
  text-shadow: 0 4px 8px rgba(0,0,0,0.4);
  animation: subtitle-glow 5s ease-in-out infinite alternate;
}

@keyframes subtitle-glow {
  0% { 
    text-shadow: 0 4px 8px rgba(0,0,0,0.4);
  }
  100% { 
    text-shadow: 0 6px 12px rgba(0,0,0,0.6), 0 0 20px rgba(255,255,255,0.1);
  }
}

.highlight-text {
  background: linear-gradient(45deg, #ffd700, #ffed4e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 600;
}

.welcome-badge {
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid rgba(255, 255, 255, 0.6);
  border-radius: 50px;
  padding: 15px 30px;
  display: inline-flex;
  align-items: center;
  font-weight: 700;
  font-size: 1rem;
  backdrop-filter: blur(10px);
  transition: all 0.4s ease;
  color: white;
  text-shadow: 0 2px 4px rgba(0,0,0,0.5);
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  animation: badge-float 7s ease-in-out infinite alternate;
}

@keyframes badge-float {
  0% { 
    transform: translateY(0px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  }
  100% { 
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.4);
  }
}

.welcome-badge:hover {
  background: rgba(0, 0, 0, 0.5);
  border-color: rgba(255, 255, 255, 0.8);
  transform: translateY(-5px) scale(1.05);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
}

.welcome-badge i {
  color: #ffd700;
  font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-content-only {
    margin: 0 15px;
    padding: 2rem !important;
  }
  
  .welcome-title {
    font-size: 2.2rem;
  }
  
  .welcome-subtitle {
    font-size: 1.1rem;
  }
  
  .home_logo {
    max-width: 120px;
  }
  
  .welcome-badge {
    padding: 12px 24px;
    font-size: 0.9rem;
  }
}

/* ===== ESTILOS PARA MENSAJE DE TURNO OBLIGATORIO ===== */
.turno-required-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.turno-required-card {
  background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
  max-width: 600px;
  width: 100%;
  position: relative;
  animation: turno-card-appear 0.8s ease-out;
  border: 3px solid #e9ecef;
}

@keyframes turno-card-appear {
  0% {
    opacity: 0;
    transform: translateY(50px) scale(0.9);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.turno-icon-container {
  position: relative;
}

.turno-icon {
  font-size: 4rem;
  color: #6c757d;
  animation: turno-icon-pulse 2s ease-in-out infinite;
}

@keyframes turno-icon-pulse {
  0%, 100% {
    transform: scale(1);
    color: #6c757d;
  }
  50% {
    transform: scale(1.1);
    color: #495057;
  }
}

.turno-title {
  font-size: 2.2rem;
  font-weight: 700;
  color: #212529;
  margin-bottom: 1rem;
}

.admin-message {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  border: 2px solid #2196f3;
  border-radius: 15px;
  padding: 15px 20px;
  color: #1565c0;
  font-weight: 600;
  display: inline-block;
  animation: admin-glow 3s ease-in-out infinite alternate;
}

@keyframes admin-glow {
  0% {
    box-shadow: 0 5px 15px rgba(33, 150, 243, 0.2);
  }
  100% {
    box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
  }
}

.turno-description {
  font-size: 1.1rem;
  line-height: 1.6;
  color: #495057;
  max-width: 500px;
  margin: 0 auto;
}

.turno-benefits {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 20px;
  border: 2px solid #e9ecef;
}

.benefit-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  font-weight: 500;
  color: #495057;
}

.benefit-item:last-child {
  margin-bottom: 0;
}

.turno-btn {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
  border-radius: 15px;
  padding: 15px 30px;
  font-size: 1.1rem;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
  position: relative;
  overflow: hidden;
}

.turno-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: left 0.5s;
}

.turno-btn:hover::before {
  left: 100%;
}

.turno-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 35px rgba(40, 167, 69, 0.4);
}

.turno-btn:disabled {
  opacity: 0.7;
  transform: none;
  cursor: not-allowed;
}

.turno-note {
  font-size: 0.9rem;
  color: #6c757d;
  opacity: 0.8;
}

.turno-activo-badge {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 25px;
  padding: 10px 20px;
  display: inline-flex;
  align-items: center;
  font-weight: 600;
  font-size: 0.9rem;
  color: white;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
  box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
  animation: turno-activo-glow 4s ease-in-out infinite alternate;
}

@keyframes turno-activo-glow {
  0% {
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
  }
  100% {
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.5);
  }
}

/* Responsive para mensaje de turno */
@media (max-width: 768px) {
  .turno-required-card {
    margin: 10px;
    padding: 2rem !important;
  }
  
  .turno-title {
    font-size: 1.8rem;
  }
  
  .turno-description {
    font-size: 1rem;
  }
  
  .turno-icon {
    font-size: 3rem;
  }
  
  .admin-message {
    padding: 12px 16px;
    font-size: 0.9rem;
  }
}

/* ===== 🎃 ESTILOS PARA MENSAJE DE HALLOWEEN ===== */
.halloween-message {
  background: linear-gradient(135deg, #ff6a00 0%, #ff9a56 100%);
  border: 3px solid #ff3300;
  border-radius: 25px;
  padding: 30px;
  margin: 30px 0;
  box-shadow: 0 15px 40px rgba(255, 102, 0, 0.5);
  animation: halloween-float 3s ease-in-out infinite alternate, halloween-glow 4s ease-in-out infinite alternate;
  position: relative;
  overflow: hidden;
}

.halloween-message::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  animation: halloween-shine 8s infinite;
}

@keyframes halloween-float {
  0% {
    transform: translateY(0px);
  }
  100% {
    transform: translateY(-10px);
  }
}

@keyframes halloween-glow {
  0% {
    box-shadow: 0 15px 40px rgba(255, 102, 0, 0.5);
  }
  100% {
    box-shadow: 0 20px 60px rgba(255, 102, 0, 0.8), 0 0 40px rgba(255, 154, 86, 0.6);
  }
}

@keyframes halloween-shine {
  0% {
    transform: translateX(-100%) translateY(-100%) rotate(45deg);
  }
  100% {
    transform: translateX(100%) translateY(100%) rotate(45deg);
  }
}

.halloween-pumpkins {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 15px;
}

.pumpkin {
  font-size: 3rem;
  animation: pumpkin-bounce 2s ease-in-out infinite;
  display: inline-block;
}

.pumpkin:nth-child(1) {
  animation-delay: 0s;
}

.pumpkin:nth-child(2) {
  animation-delay: 0.3s;
}

.pumpkin:nth-child(3) {
  animation-delay: 0.6s;
}

@keyframes pumpkin-bounce {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
  }
  25% {
    transform: translateY(-15px) rotate(-10deg);
  }
  75% {
    transform: translateY(-5px) rotate(10deg);
  }
}

.halloween-title {
  font-size: 3rem;
  font-weight: 900;
  color: white;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 51, 0, 0.8);
  margin-bottom: 10px;
  animation: halloween-title-pulse 2s ease-in-out infinite;
  position: relative;
  z-index: 2;
}

@keyframes halloween-title-pulse {
  0%, 100% {
    transform: scale(1);
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 51, 0, 0.8);
  }
  50% {
    transform: scale(1.05);
    text-shadow: 0 6px 12px rgba(0, 0, 0, 0.6), 0 0 30px rgba(255, 51, 0, 1);
  }
}

.halloween-subtitle {
  font-size: 1.8rem;
  font-weight: 600;
  color: #fff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
  margin-bottom: 20px;
  position: relative;
  z-index: 2;
}

.halloween-decoration {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 15px;
}

.halloween-decoration span {
  font-size: 2rem;
  animation: halloween-decoration-spin 4s ease-in-out infinite;
  display: inline-block;
}

.halloween-decoration span:nth-child(1) {
  animation-delay: 0s;
}

.halloween-decoration span:nth-child(2) {
  animation-delay: 0.5s;
}

.halloween-decoration span:nth-child(3) {
  animation-delay: 1s;
}

.halloween-decoration span:nth-child(4) {
  animation-delay: 1.5s;
}

@keyframes halloween-decoration-spin {
  0%, 100% {
    transform: rotate(0deg) scale(1);
  }
  25% {
    transform: rotate(-15deg) scale(1.2);
  }
  75% {
    transform: rotate(15deg) scale(0.9);
  }
}

/* Responsive Halloween */
@media (max-width: 768px) {
  .halloween-message {
    padding: 20px;
    margin: 20px 10px;
  }
  
  .halloween-title {
    font-size: 2.2rem;
  }
  
  .halloween-subtitle {
    font-size: 1.4rem;
  }
  
  .pumpkin {
    font-size: 2.5rem;
  }
  
  .halloween-decoration span {
    font-size: 1.5rem;
  }
}
</style>