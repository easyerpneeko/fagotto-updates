<template>
  <div class="bg-home-gradient">
    <div class="bg-full-height-gradient">
      <div class="welcome-overlay d-flex flex-column justify-content-center align-items-center py-5">
        
        <!-- Hero Welcome Card - Profesional y Atractivo -->
        <div class="welcome-content-only text-center p-5 my-4">
          <div class="welcome-logo-container mb-4">
            <img class="home_logo" src="../assets/logo.png" alt="fagotto-erp">
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
    }
  },
  async mounted() {
    console.log('USUARIO LOGUEADO', this.me);
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
</style>