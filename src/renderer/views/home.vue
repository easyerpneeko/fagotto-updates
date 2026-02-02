<template>
  <div class="default-bg-color p-2 d-flex flex-column pt-md-5">
    
    <div class="bg-full-height-gradient">
      <div class="welcome-overlay d-flex flex-column justify-content-center align-items-center py-5">
        
        <!-- Mensaje de Turno Requerido -->
        <div v-if="!turnoActivo" class="turno-required-overlay">
          <div class="turno-required-card p-0">
            <!-- Header estilo modal cafetería -->
            <div class="modal-header bg-primario text-white">
              <h5 class="modal-title w-100 text-center">
                <i class="fas fa-cash-register me-2"></i>
                Sistema de Gestión Fagotto
              </h5>
            </div>
            
            <div class="modal-body text-center p-4">
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
        </div>

        <!-- Contenido normal del home (solo se muestra con turno activo) -->
        <div v-else class="welcome-content-only">
          <div class="container mt-5">
            <div class="welcome-modal-card">
              <!-- Header estilo modal cafetería -->
              <div class="modal-header bg-primario text-white">
                <h5 class="modal-title w-100 text-center">
                  <i class="fas fa-home me-2"></i>
                  Bienvenido al Sistema Fagotto ERP
                </h5>
              </div>
              
              <div class="modal-body p-4">
                <div class="text-center mb-4">
                  <img class="home_logo mb-3" src="../assets/logo.png" alt="fagotto-erp" style="max-width: 150px;">
                  <h2 class="mb-2">¡Hola, {{ this.me.fullname }}! 👋</h2>
                  <span class="turno-activo-badge">
                    <i class="fas fa-check-circle me-2"></i>
                    Turno Activo
                  </span>
                </div>

                <div class="mode-explanation mb-4">
                  <h6 class="text-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>SISTEMA LISTO PARA USAR</strong>
                  </h6>
                  <ul class="mt-2">
                    <li>✅ Tu turno de caja está <strong>activo</strong> y funcionando</li>
                    <li>✅ Todas las transacciones se están <strong>registrando automáticamente</strong></li>
                    <li>✅ Acceso completo a <strong>todas las funcionalidades</strong> del sistema</li>
                    <li>✅ Tus ventas se están <strong>contabilizando</strong> en tiempo real</li>
                  </ul>
                </div>

                <div class="alert alert-info">
                  <i class="fas fa-lightbulb me-2"></i>
                  <strong>Tip:</strong> Recuerda cerrar tu turno al finalizar tu jornada desde el menú <strong>"Arqueo de Caja"</strong> para generar el reporte completo de tu día.
                </div>
                
                <div class="text-center mt-3" style="color: #6b7280; font-size: 0.9rem;">
                  <code>&lt;/&gt;</code> Saludos Jimmy Arriagada <code>&lt;/&gt;</code>
                </div>
              </div>
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
  background: var(--primary);
  border: none;
  border-radius: 20px;
  padding: 8px 16px;
  display: inline-flex;
  align-items: center;
  font-weight: 500;
  font-size: 0.9rem;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* ===== CONTENIDO DE BIENVENIDA ===== */
.welcome-content-only {
  min-height: 100vh;
}

.welcome-modal-card {
  background: #ffffff;
  border-radius: 8px;
  max-width: 700px;
  margin: 0 auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.welcome-modal-card .modal-body ul {
  list-style: none;
  padding-left: 0;
  text-align: left;
}

.welcome-modal-card .modal-body ul li {
  padding: 0.5rem 0;
  font-size: 1rem;
  color: #495057;
}

.welcome-modal-card .alert-info {
  background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
  border: 2px solid #00bcd4;
  border-radius: 8px;
  padding: 1rem;
  color: #006064;
  font-size: 0.95rem;
}

.mode-explanation h6 {
  font-size: 1.1rem;
  margin-bottom: 0.75rem;
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
  background: var(--secondary-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.turno-required-card {
  background: #ffffff;
  border-radius: 8px;
  max-width: 600px;
  width: 100%;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: none;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.modal-body {
  padding: 2rem;
}

.turno-icon-container {
  text-align: center;
  margin-bottom: 1.5rem;
}

.turno-icon {
  font-size: 3rem;
  color: var(--primary);
}

.turno-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 1rem;
}

.admin-message {
  background: var(--primary);
  color: white;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: center;
}

.turno-description {
  font-size: 1rem;
  line-height: 1.6;
  color: #495057;
  margin-bottom: 1.5rem;
}

.turno-benefits {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.benefit-item {
  padding: 0.75rem;
  margin: 0.5rem 0;
  background: #ffffff;
  border-left: 3px solid var(--primary);
  border-radius: 4px;
  display: flex;
  align-items: center;
}

.benefit-item i {
  margin-right: 0.75rem;
}

.turno-btn {
  background: var(--primary);
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  font-size: 1rem;
  font-weight: 500;
  color: white;
  transition: all 0.3s ease;
}

.turno-btn:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.turno-note {
  font-size: 0.85rem;
  color: #6c757d;
  margin-top: 1rem;
}

.turno-activo-badge {
  background: var(--primary);
  border-radius: 20px;
  padding: 8px 16px;
  display: inline-flex;
  align-items: center;
  font-weight: 500;
  font-size: 0.9rem;
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Responsive */
@media (max-width: 768px) {
  .turno-required-card {
    margin: 10px;
    padding: 1.5rem;
  }
  
  .turno-title {
    font-size: 1.5rem;
  }
  
  .turno-icon {
    font-size: 2.5rem;
  }
}

/* ========================================
   ESTILOS DE NOTICIAS DEL SISTEMA
   ======================================== */

.news-section {
  max-width: 100%;
  width: 100%;
  margin: 0;
  padding: 2rem 0;
}

.news-title {
  font-size: 2rem;
  font-weight: 700;
  color: hsl(210, 24%, 90%);
  text-align: center;
  margin-bottom: 2rem;
  text-shadow: 0 2px 10px rgba(74, 144, 226, 0.3);
}

.news-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  width: 200%;
  max-width: none;
  margin: 0 auto;
  margin-left: -50%;
  padding: 0;
}

/* Panel de noticia estilo w3.css mejorado */
.news-panel {
  background: linear-gradient(135deg, rgba(80, 80, 80, 0.85) 0%, rgba(60, 60, 60, 0.9) 100%);
  backdrop-filter: blur(10px);
  border-left: 5px solid;
  border-radius: 12px;
  padding: 0;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
  overflow: hidden;
  width: 100%;
}

.news-panel:hover {
  transform: translateX(5px);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
  background: linear-gradient(135deg, rgba(90, 90, 90, 0.9) 0%, rgba(70, 70, 70, 0.95) 100%);
}

/* Variantes de color */
.news-panel-primary {
  border-left-color: #667eea;
  background: linear-gradient(135deg, rgba(80, 80, 80, 0.85) 0%, rgba(60, 60, 60, 0.9) 100%);
}

.news-panel-success {
  border-left-color: #22c55e;
  background: linear-gradient(135deg, rgba(80, 80, 80, 0.85) 0%, rgba(60, 60, 60, 0.9) 100%);
}

.news-panel-info {
  border-left-color: #3b82f6;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%);
}

.news-panel-quote {
  border-left-color: #a855f7;
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.15) 0%, rgba(168, 85, 247, 0.05) 100%);
}

/* Header del panel */
.news-panel-header {
  background: rgba(0, 0, 0, 0.2);
  padding: 1.2rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: hsl(210, 24%, 95%);
  font-size: 1.3rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.news-panel-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.3rem 0.8rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

/* Body del panel */
.news-panel-body {
  padding: 2rem 2rem;
}

.news-panel-text {
  color: hsl(210, 16%, 85%);
  font-size: 1rem;
  line-height: 1.8;
  margin: 0;
}

/* Quote específico */
.news-quote-text {
  font-size: 1.3rem;
  font-style: italic;
  color: hsl(266, 85%, 75%);
  text-align: center;
  margin: 1rem 0;
  padding: 1rem;
  background: rgba(168, 85, 247, 0.1);
  border-radius: 8px;
  font-weight: 500;
}

.news-quote-author {
  color: hsl(210, 16%, 75%);
  font-size: 0.95rem;
  margin: 1rem 0;
  text-align: center;
}

/* Footer del panel */
.news-panel-footer {
  background: rgba(0, 0, 0, 0.15);
  padding: 0.8rem 1.5rem;
  color: hsl(210, 16%, 70%);
  font-size: 0.85rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

/* Responsive */
@media (max-width: 768px) {
  .news-section {
    padding: 1.5rem 0.5rem;
  }

  .news-title {
    font-size: 1.5rem;
  }

  .news-panel-header {
    font-size: 1rem;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }

  .news-panel-body {
    padding: 1rem;
  }

  .news-panel-text {
    font-size: 0.95rem;
  }

  .news-quote-text {
    font-size: 1.1rem;
  }
}

/* ===== ESTILOS PREMIUM PARA PANEL DE COMISIONES ===== */
.news-panel-premium {
  border-left: 6px solid #f59e0b;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.news-panel-header-premium {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  padding: 2rem 2.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.premium-icon-wrapper {
  width: 64px;
  height: 64px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  animation: pulse-premium 2s ease-in-out infinite;
}

@keyframes pulse-premium {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.premium-title-section {
  flex: 1;
}

.premium-title {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: white;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.premium-subtitle {
  display: inline-block;
  background: rgba(255, 255, 255, 0.25);
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: white;
  margin-top: 0.5rem;
}

.news-panel-body-premium {
  padding: 2.5rem 2.5rem 1.5rem 2.5rem;
}

.premium-feature {
  display: flex;
  gap: 1.5rem;
  align-items: start;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.premium-feature:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(8px);
}

.feature-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.feature-content h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: hsl(210, 24%, 95%);
}

.feature-content p {
  margin: 0;
  font-size: 1rem;
  line-height: 1.6;
  color: hsl(210, 16%, 80%);
}

.premium-cta {
  margin-top: 2rem;
  padding: 2rem;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.1) 100%);
  border-radius: 12px;
  border: 2px solid rgba(245, 158, 11, 0.3);
}

.cta-content {
  text-align: center;
}

.cta-text {
  font-size: 1.1rem;
  color: hsl(210, 24%, 95%);
  margin: 0 0 1rem 0;
  font-weight: 500;
  font-style: italic;
}

.cta-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  padding: 0.8rem 1.5rem;
  border-radius: 25px;
  font-weight: 600;
  color: white;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
  animation: glow-badge 2s ease-in-out infinite;
}

@keyframes glow-badge {
  0%, 100% { box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4); }
  50% { box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6); }
}

.news-panel-footer-premium {
  background: rgba(0, 0, 0, 0.2);
  padding: 1.5rem 2.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-signature {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.8rem;
  color: hsl(210, 16%, 70%);
  font-size: 0.95rem;
}

.footer-signature code {
  color: #f59e0b;
  font-weight: 700;
  font-size: 1rem;
}

.signature-text {
  font-style: italic;
  font-weight: 500;
}

/* Responsive para panel premium */
@media (max-width: 768px) {
  .news-panel-header-premium {
    flex-direction: column;
    text-align: center;
    padding: 1.5rem;
  }

  .premium-icon-wrapper {
    width: 56px;
    height: 56px;
    font-size: 1.5rem;
  }

  .premium-title {
    font-size: 1.4rem;
  }

  .news-panel-body-premium {
    padding: 1.5rem;
  }

  .premium-feature {
    flex-direction: column;
    text-align: center;
    padding: 1rem;
  }

  .feature-icon {
    font-size: 2rem;
  }

  .feature-content h4 {
    font-size: 1.1rem;
  }

  .premium-cta {
    padding: 1.5rem;
  }

  .footer-signature {
    flex-direction: column;
    gap: 0.4rem;
  }
}
</style>