<template>
  <div class="bg-home-gradient christmas-dark-home">
    <!-- ❄️ Snow animations with accumulation -->
    <div class="christmas-snow">
      <div class="snow-flake" v-for="n in 50" :key="n" :style="{
        left: (n * 2) + '%',
        animationDelay: (n * 0.2) + 's',
        animationDuration: (4 + Math.random() * 4) + 's'
      }">❄</div>
    </div>

    <!-- Snow accumulation at bottom -->
    <div class="snow-accumulation"></div>

    <!-- Windshield wiper animation -->
    <div class="wiper-container">
      <div class="wiper"></div>
    </div>

    <!-- Christmas Message -->
    <div class="christmas-message-float">
      <div class="christmas-card">
        <div class="card-ornament">🎄</div>
        <h2 class="christmas-greeting-text">¡Feliz Navidad!</h2>
        <p class="christmas-wish">Que esta época navideña llene tu hogar de amor, paz y felicidad. 
          ¡Felices fiestas desde Fagotto ERP! 🎅✨</p>
        <div class="card-ornament bottom">🎁</div>
      </div>
    </div>

    <div class="bg-full-height-gradient">
      <div class="welcome-overlay d-flex flex-column justify-content-center align-items-center py-5">
        
        <!-- Mensaje de Turno Requerido -->
        <div v-if="!turnoActivo" class="turno-required-overlay">
          <div class="turno-required-card text-center p-5">
            <!-- Christmas Title Header -->
            <div class="christmas-header mb-4">
              <h1 class="christmas-main-title">Merry Christmas</h1>
            </div>

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
          
          <div class="welcome-content">
            <!-- Badge de turno activo -->
          </div>

          <!-- Sección de Noticias del Sistema -->
          <div class="news-section mt-5">
            <h2 class="news-title mb-4">
              <i class="fas fa-newspaper me-2"></i>
              Novedades del Sistema
            </h2>
            
            <div class="news-container">
              
              <!-- Noticia 1: Sistema de Ventas Optimizado -->
              <div class="news-panel news-panel-success">
                <div class="news-panel-header">
                  <i class="fas fa-rocket me-2"></i>
                  <strong>Sistema de Ventas Optimizado</strong>
                  <span class="news-panel-badge">NUEVO</span>
                </div>
                <div class="news-panel-body">
                  <p class="news-panel-text">
                    <strong>Modal Fijo:</strong> El catálogo permanece abierto entre ventas • 
                    <strong>Ahorro de tiempo:</strong> Reduce hasta 1 minuto por venta • 
                    <strong>Proceso continuo:</strong> Atiende múltiples clientes sin interrupciones • 
                    <strong>Carrito inteligente:</strong> Se limpia automáticamente • 
                    <strong>Métodos de pago centralizados:</strong> Modal elegante con todas las opciones
                  </p>
                </div>
                <div class="news-panel-footer">
                  <i class="far fa-calendar-alt me-2"></i>26 de Diciembre, 2025 • 
                  <i class="far fa-clock ms-2 me-2"></i>12:00 PM
                </div>
              </div>

              <!-- Noticia 2: Registro Biométrico -->
              <div class="news-panel news-panel-info">
                <div class="news-panel-header">
                  <i class="fas fa-fingerprint me-2"></i>
                  <strong>Registro Biométrico de Asistencia</strong>
                  <span class="news-panel-badge">FUNCIONALIDAD</span>
                </div>
                <div class="news-panel-body">
                  <p class="news-panel-text">
                    Control de asistencia biométrico que registra automáticamente la hora de llegada y salida de cada empleado. 
                    <strong>Registro automático</strong> con captura precisa de horarios • 
                    <strong>Control de personal</strong> con reportes detallados • 
                    <strong>Seguridad</strong> mediante autenticación biométrica confiable • 
                    <strong>Integración total</strong> con nómina y RRHH
                  </p>
                </div>
                <div class="news-panel-footer">
                  <i class="far fa-calendar-alt me-2"></i>26 de Diciembre, 2025 • 
                  <i class="far fa-clock ms-2 me-2"></i>10:30 AM
                </div>
              </div>

              <!-- Noticia 3: Frase Inspiradora -->
              <div class="news-panel news-panel-quote">
                <div class="news-panel-header">
                  <i class="fas fa-quote-left me-2"></i>
                  <strong>Filosofía del Desarrollo</strong>
                  <span class="news-panel-badge">INSPIRACIÓN</span>
                </div>
                <div class="news-panel-body">
                  <blockquote class="news-quote-text">
                    "La programación es el arte de forjar ideas para colmar necesidades"
                  </blockquote>
                  <p class="news-quote-author">
                    <i class="fas fa-user-tie me-2"></i>
                    <strong>Jimmy Arriagada</strong> - Desarrollador Principal • Fagotto ERP
                  </p>
                  <p class="news-panel-text">
                    Cada línea de código que escribimos tiene un propósito: mejorar la vida de quienes usan nuestro sistema. 
                    Desarrollamos con pasión, pensando siempre en las necesidades reales de nuestros usuarios.
                  </p>
                </div>
                <div class="news-panel-footer">
                  <i class="far fa-calendar-alt me-2"></i>26 de Diciembre, 2025 • 
                  <i class="far fa-clock ms-2 me-2"></i>09:00 AM
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

/* ===== 📋 ESTILOS PARA MENSAJE INFORMATIVO DELIVERY ===== */
.delivery-info-message {
  background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
  border: 3px solid #2c5f8d;
  border-radius: 20px;
  padding: 35px;
  margin: 30px 0;
  box-shadow: 0 10px 30px rgba(74, 144, 226, 0.3);
  position: relative;
  overflow: hidden;
  animation: info-fade-in 1s ease-out;
}

.delivery-info-message::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  animation: info-shine 3s infinite;
}

@keyframes info-fade-in {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes info-shine {
  0% {
    left: -100%;
  }
  100% {
    left: 100%;
  }
}

.info-icon-container {
  text-align: center;
  margin-bottom: 20px;
}

.info-icon-container i {
  font-size: 4rem;
  color: white;
  text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  animation: info-icon-pulse 2s ease-in-out infinite;
}

@keyframes info-icon-pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.info-title {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  text-align: center;
  margin-bottom: 20px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  position: relative;
  z-index: 2;
}

.info-description {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.95);
  text-align: center;
  line-height: 1.8;
  margin-bottom: 25px;
  position: relative;
  z-index: 2;
}

.info-description strong {
  color: white;
  font-weight: 600;
}

.info-benefits {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin: 25px 0;
  position: relative;
  z-index: 2;
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 15px;
  background: rgba(255, 255, 255, 0.1);
  padding: 15px 20px;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.benefit-item:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateX(5px);
}

.benefit-item i {
  font-size: 1.5rem;
  color: #a8d5ff;
  min-width: 30px;
}

.benefit-item span {
  color: white;
  font-size: 1.05rem;
  font-weight: 500;
}

.info-footer {
  text-align: center;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.85);
  font-style: italic;
  margin-top: 25px;
  padding-top: 20px;
  border-top: 2px solid rgba(255, 255, 255, 0.2);
  position: relative;
  z-index: 2;
}

/* Responsive para mensaje delivery */
@media (max-width: 768px) {
  .delivery-info-message {
    padding: 25px 20px;
    margin: 20px 10px;
  }
  
  .info-icon-container i {
    font-size: 3rem;
  }
  
  .info-title {
    font-size: 1.6rem;
  }
  
  .info-description {
    font-size: 1rem;
  }
  
  .benefit-item {
    padding: 12px 15px;
  }
  
  .benefit-item i {
    font-size: 1.3rem;
  }
  
  .benefit-item span {
    font-size: 0.95rem;
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

/* 🎄 ESTILOS NAVIDEÑOS - Dark Christmas Theme (Bedimcode Style) */
@import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Poppins:wght@400;500;600&display=swap');

.christmas-dark-home {
  position: relative;
  overflow: hidden;
  background: hsl(210, 32%, 4%) !important;
  background-image: url('https://github.com/bedimcode/responsive-christmas-website-2/blob/main/preview.png?raw=true');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
  min-height: 100vh;
}

/* Snow animation */
.christmas-snow {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 999;
}

.snow-flake {
  position: absolute;
  top: -10%;
  color: #fff;
  font-size: 1.2rem;
  opacity: 0.8;
  animation: snow-fall linear infinite;
}

@keyframes snow-fall {
  0% {
    transform: translateY(0) scale(0.5);
  }
  50% {
    transform: translateY(50vh) scale(1);
  }
  100% {
    transform: translateY(100vh) scale(0.5);
  }
}

/* Christmas Header */
.christmas-header {
  margin-bottom: 30px;
}

.christmas-main-title {
  font-family: 'Dancing Script', cursive;
  font-size: 4rem;
  font-weight: 700;
  color: hsl(210, 80%, 54%);
  text-shadow: 0 4px 12px rgba(74, 144, 226, 0.5);
  margin: 0;
  animation: title-glow 2s ease-in-out infinite alternate;
}

@keyframes title-glow {
  from {
    text-shadow: 0 4px 12px rgba(74, 144, 226, 0.5);
  }
  to {
    text-shadow: 0 6px 20px rgba(74, 144, 226, 0.8), 0 0 30px rgba(74, 144, 226, 0.4);
  }
}

/* Update turno card for dark theme */
.turno-required-card {
  background: linear-gradient(135deg, hsl(210, 24%, 12%) 0%, hsl(210, 24%, 8%) 100%) !important;
  border: 2px solid hsl(210, 24%, 20%) !important;
  color: hsl(210, 16%, 70%) !important;
}

.turno-title {
  color: hsl(210, 24%, 90%) !important;
}

.turno-description {
  color: hsl(210, 16%, 70%) !important;
}

.admin-message {
  background: rgba(74, 144, 226, 0.1) !important;
  border: 1px solid hsl(210, 80%, 54%) !important;
  color: hsl(210, 24%, 90%) !important;
}

.benefit-item {
  color: hsl(210, 16%, 70%) !important;
  background: rgba(255, 255, 255, 0.05) !important;
}

.turno-note {
  color: hsl(210, 16%, 60%) !important;
}

/* Responsive navideño */
@media (max-width: 768px) {
  .christmas-main-title {
    font-size: 2.5rem;
  }
  
  .snow-flake {
    font-size: 1rem;
  }
}

/* Snow Accumulation Effect */
.snow-accumulation {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 150px;
  background: linear-gradient(to top, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.6) 50%, transparent 100%);
  z-index: 998;
  animation: snow-pile 20s ease-in-out infinite;
  pointer-events: none;
  border-radius: 50% 50% 0 0;
}

@keyframes snow-pile {
  0%, 100% {
    height: 120px;
    opacity: 0.8;
  }
  50% {
    height: 180px;
    opacity: 0.9;
  }
}

/* Windshield Wiper Animation */
.wiper-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1000;
  overflow: hidden;
}

.wiper {
  position: absolute;
  bottom: -10px;
  left: 50%;
  width: 2px;
  height: 100%;
  background: linear-gradient(to top, rgba(100, 150, 200, 0.6), transparent);
  transform-origin: bottom center;
  animation: wiper-sweep 8s ease-in-out infinite;
  box-shadow: -2px 0 20px rgba(255, 255, 255, 0.6), 2px 0 20px rgba(255, 255, 255, 0.6);
}

.wiper::before {
  content: '';
  position: absolute;
  top: 0;
  left: -30px;
  width: 60px;
  height: 100%;
  background: linear-gradient(90deg, transparent 0%, rgba(200, 230, 255, 0.4) 20%, rgba(200, 230, 255, 0.6) 50%, rgba(200, 230, 255, 0.4) 80%, transparent 100%);
  filter: blur(10px);
}

@keyframes wiper-sweep {
  0%, 10% {
    transform: translateX(-50%) rotate(-60deg);
    opacity: 0;
  }
  15% {
    opacity: 1;
  }
  45% {
    transform: translateX(-50%) rotate(60deg);
  }
  50%, 100% {
    transform: translateX(-50%) rotate(60deg);
    opacity: 0;
  }
}

/* Christmas Floating Message */
.christmas-message-float {
  position: fixed;
  top: 80px;
  right: 30px;
  z-index: 1001;
  animation: float-message 6s ease-in-out infinite;
}

@keyframes float-message {
  0%, 100% {
    transform: translateY(0) rotate(-2deg);
  }
  50% {
    transform: translateY(-15px) rotate(2deg);
  }
}

.christmas-card {
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.95), rgba(185, 28, 28, 0.95));
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 25px 30px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.3);
  border: 3px solid rgba(255, 255, 255, 0.4);
  position: relative;
  max-width: 350px;
  animation: card-glow 2s ease-in-out infinite alternate;
}

@keyframes card-glow {
  from {
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.3), 0 0 20px rgba(220, 38, 38, 0.4);
  }
  to {
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 0 30px rgba(220, 38, 38, 0.6);
  }
}

.card-ornament {
  font-size: 2.5rem;
  text-align: center;
  margin-bottom: 10px;
  animation: ornament-spin 4s ease-in-out infinite;
}

.card-ornament.bottom {
  margin-top: 10px;
  margin-bottom: 0;
  animation-delay: 2s;
}

@keyframes ornament-spin {
  0%, 100% {
    transform: rotate(-10deg) scale(1);
  }
  50% {
    transform: rotate(10deg) scale(1.1);
  }
}

.christmas-greeting-text {
  font-family: 'Dancing Script', cursive;
  font-size: 2.2rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 12px 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5), 0 0 10px rgba(255, 255, 255, 0.5);
  text-align: center;
}

.christmas-wish {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.95);
  line-height: 1.6;
  margin: 0;
  text-align: center;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

/* Responsive additions */
@media (max-width: 768px) {
  .christmas-message-float {
    top: 60px;
    right: 15px;
    left: 15px;
  }

  .christmas-card {
    max-width: 100%;
    padding: 20px;
  }

  .christmas-greeting-text {
    font-size: 1.8rem;
  }

  .christmas-wish {
    font-size: 0.9rem;
  }

  .card-ornament {
    font-size: 2rem;
  }

  .snow-accumulation {
    height: 100px;
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
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-left: 5px solid;
  border-radius: 0;
  padding: 0;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  overflow: hidden;
  width: 100%;
}

.news-panel:hover {
  transform: translateX(5px);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
}

/* Variantes de color */
.news-panel-success {
  border-left-color: #22c55e;
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%);
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
</style>