<template>
  <div class="landing-container">
    <div class="landing-content">
      <!-- Logo Header -->
      <div class="logo-section">
        <div class="logo-container">
          <img src="~@/assets/logo.png" alt="Fagotto ERP" class="fagotto-logo" />
        </div>
        <p class="subtitle">Sincronización de aplicaciones</p>
      </div>

      <!-- Serial Card -->
      <div class="serial-card">
        <div class="card-header">
          <div class="header-icon">🔐</div>
          <h3>Serial de la aplicación</h3>
        </div>
        
        <div class="card-body">
          <!-- Ejemplo del formato -->
          <div class="serial-example">
            <div class="example-header">
              <span class="example-icon">💡</span>
              <span class="example-text">Formato de ejemplo:</span>
            </div>
            <div class="example-serial">
              <span class="serial-part">911C</span>-<span class="serial-part">3451</span>-<span class="serial-part">1123</span>-<span class="serial-part">LJ31</span>-<span class="serial-part">P3N3</span>
            </div>
            <p class="example-note">* Cada grupo contiene 4 caracteres separados por guiones</p>
            
          </div>

          <div class="input-group">
            <input 
              type="text" 
              class="serial-input" 
              v-model="serial"
              @keyup.enter="sendSerial" 
              :disabled="waitResponse" 
              id="serialInput"
              placeholder="Ej: 1A2B-3C4D-5E6F-7G8H-9I0J"
            >
          </div>
          
          <button 
            type="button" 
            class="establish-btn" 
            @click="sendSerial" 
            :disabled="waitResponse || !serial"
            :class="{ 'loading': waitResponse }"
          >
            <span v-if="!waitResponse" class="btn-content">
              <span class="btn-icon">✨</span>
              Establecer Conexión
            </span>
            <span v-else class="btn-content">
              <span class="btn-spinner">⏳</span>
              Conectando...
            </span>
          </button>
        </div>
      </div>

      <!-- Info Card -->
      <div class="info-section">
        <div class="info-card">
          <div class="info-content">
            <div class="info-text">
              <h4>🌐 Sincronización en la Nube</h4>
              <p>Introduzca el serial asignado para sincronizar su cliente local con su aplicación en la nube de forma segura.</p>
            </div>
            <div class="info-visual">
              <div class="cloud-animation">
                <i class="fas fa-cloud"></i>
                <div class="sync-dots">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Version Info -->
      <div class="version-info" v-if="true">
        <span class="env-badge">{{ appInProduction }}</span>
        <span class="version-text">
          <i class="fas fa-code-branch"></i>
          <b id="version">{{ appVersion }}</b>
        </span>
      </div>
      
      <!-- Auto Update Component -->
      <autoUpdate class="auto-update-component"/>
    </div>
    
    <!-- Animated Background -->
    <div class="background-animation">
      <div class="floating-shape shape-1"></div>
      <div class="floating-shape shape-2"></div>
      <div class="floating-shape shape-3"></div>
      <div class="floating-shape shape-4"></div>
    </div>
  </div>
</template>

<script>

import ConfigHelper from '../helpers/ConfigHelper.js';
import Loader from '@/helpers/Loader';
import autoUpdate from '../components/autoUpdate.vue';
import packageJson from '../../../package.json';

const remote = require('electron').remote;
const Inputmask = require('inputmask');
const $ = require('jquery');
// const { ipcRenderer } = require('electron');

export default {
  name: 'landing-page',
  components: {autoUpdate},
  data(){
    return{
      serial: '',
      w: remote.getCurrentWindow(),
      waitResponse: false,
      appInProduction: process.env.NODE_ENV,
      appVersion: packageJson.version
    }
  },
  props:['version'],
  created(){

    ConfigHelper.readAppFile((err, data) => {
      if(err) return;
      //Si el archivo se leyo correctamente, saltar al login
      ConfigHelper.ConfigHandler(false, JSON.parse(data), false);
    });
  },
  async mounted(){
    // Cargar versión oficial del sistema
    await this.getAppVersion();
    
    //Para el auto actualizador
    // const version = document.getElementById('version');
    // ipcRenderer.send('app_version');
    // ipcRenderer.on('app_version', (event, arg) => {
    //   ipcRenderer.removeAllListeners('app_version');
    //   version.innerText = 'Version ' + arg.version;
    // });

    // const notification = document.getElementById('notification');
    // const message = document.getElementById('message');
    // const restartButton = document.getElementById('restart-button');
    // ipcRenderer.on('update_available', () => {
    //   console.log('update available');
    //   ipcRenderer.removeAllListeners('update_available');
    //   message.innerText = 'Una nueva version esta disponible. Descargando...';
    //   notification.classList.remove('hidden');
    // });
    // ipcRenderer.on('update_downloaded', () => {
    //   console.log('update downloaded');
    //   ipcRenderer.removeAllListeners('update_downloaded');
    //   message.innerText = 'Nueva version descargada. Sera instalada al reiniciar el programa. Reiniciar Ahora mismo?';
    //   restartButton.classList.remove('d-none');
    //   notification.classList.remove('hidden');
    // });

    // ipcRenderer.on('message', function(event, text) {
    //   var container = document.getElementById('messages');
    //   var message = document.createElement('div');
    //   message.innerHTML = text;
    //   container.appendChild(message);
    // })

  },
  methods: {
    async getAppVersion() {
      try {
        const BaseUrl = require('@/helpers/baseUrl.js').default;
        const Connection = require('@/helpers/Connection.js').default;
        
        const url = BaseUrl.getUrl('api/versions/official');
        const response = await Connection.request('get', url, {});
        
        if (response.success && response.data && response.data.version) {
          this.appVersion = response.data.version;
          console.log('✅ Versión oficial cargada:', this.appVersion);
        } else {
          this.appVersion = packageJson.version;
        }
      } catch (error) {
        console.log('⚠️ No se pudo obtener la versión oficial, usando fallback:', error);
        this.appVersion = packageJson.version;
      }
    },
    async sendSerial(){
      this.waitResponse = true;
      Loader.fullPage();
      let request = await this.$store.dispatch("main/sendSerial",this.serial);
      console.log(request);
      if (request.success) {
        ConfigHelper.writeFile(request.data, 'aplication.json', (err) => {
          if(err){
            this.$awn.alert(err);
            return;
          }
          // console.log(request.data);
          this.$awn.success('Archivo creado',{labels:{success:'CORRECTO'}});
          this.w.reload();
        });
      }else{
        Loader.hide();
        this.waitResponse = false;
        this.$awn.alert(request.data);
      }
    },
    // closeNotification() {
    //   notification.classList.add('hidden');
    // },
    // restartApp() {
    //   ipcRenderer.send('restart_app');
    // }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Container Principal */
.landing-container {
  min-height: 100vh;
  background: linear-gradient(135deg, 
    #667eea 0%, 
    #764ba2 25%, 
    #f093fb 50%, 
    #f5576c 75%, 
    #4facfe 100%);
  background-size: 300% 300%;
  animation: gradientShift 8s ease infinite;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  position: relative;
  overflow: hidden;
}

.landing-content {
  max-width: 480px;
  width: 90%;
  padding: 40px 0;
  position: relative;
  z-index: 10;
  animation: fadeInUp 1s ease-out;
}

/* Logo Section */
.logo-section {
  text-align: center;
  margin-bottom: 40px;
  animation: slideDown 1s ease-out 0.2s both;
}

.logo-container {
  position: relative;
  display: inline-block;
  margin-bottom: 20px;
}

.fagotto-logo {
  width: 150px;
  height: 100px;
  object-fit: contain;
  filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
  transition: all 0.3s ease;
  animation: logoFloat 3s ease-in-out infinite;
}

.fagotto-logo:hover {
  transform: scale(1.1);
  filter: drop-shadow(0 15px 40px rgba(0, 0, 0, 0.4));
}

.main-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  margin: 0 0 10px 0;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  letter-spacing: -0.02em;
}

.subtitle {
  font-size: 1.1rem;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

/* Serial Card */
.serial-card {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 24px;
  padding: 32px;
  margin-bottom: 30px;
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(255, 255, 255, 0.1) inset;
  transition: all 0.3s ease;
  animation: slideUp 1s ease-out 0.4s both;
}

.serial-card:hover {
  transform: translateY(-5px);
  box-shadow: 
    0 30px 60px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(255, 255, 255, 0.2) inset;
}

.card-header {
  text-align: center;
  margin-bottom: 24px;
}

.header-icon {
  font-size: 2rem;
  margin-bottom: 12px;
  animation: bounce 2s infinite;
}

.card-header h3 {
  font-size: 1.4rem;
  font-weight: 600;
  color: white;
  margin: 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Serial Example */
.serial-example {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  animation: exampleGlow 3s ease-in-out infinite;
}

.example-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 12px;
}

.example-icon {
  font-size: 1.2rem;
  animation: pulse 2s infinite;
}

.example-text {
  font-size: 0.9rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
}

.example-serial {
  font-family: 'Courier New', monospace;
  font-size: 1.1rem;
  font-weight: 600;
  color: #ffd700;
  text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
  margin-bottom: 8px;
  letter-spacing: 1px;
  animation: shimmer 2s ease-in-out infinite;
}

.serial-part {
  background: linear-gradient(45deg, #ffd700, #ffed4e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: none;
  font-weight: 700;
}

.example-note {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-style: italic;
}

.input-group {
  position: relative;
}

.serial-input {
  width: 100%;
  padding: 16px 20px;
  font-size: 1rem;
  font-weight: 500;
  color: #2d3748;
  background: rgba(255, 255, 255, 0.95);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 16px;
  outline: none;
  transition: all 0.3s ease;
  text-align: center;
  letter-spacing: 0.5px;
}

.serial-input::placeholder {
  color: rgba(45, 55, 72, 0.5);
  font-weight: 400;
}

.serial-input:focus {
  border-color: rgba(255, 255, 255, 0.6);
  background: white;
  transform: scale(1.02);
  box-shadow: 
    0 0 0 4px rgba(255, 255, 255, 0.2),
    0 10px 30px rgba(0, 0, 0, 0.1);
}

.serial-input:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Establish Button */
.establish-btn {
  width: 100%;
  padding: 16px 24px;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.establish-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
  background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

.establish-btn:active:not(:disabled) {
  transform: translateY(0);
}

.establish-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.establish-btn.loading {
  background: linear-gradient(135deg, #a0aec0 0%, #718096 100%);
  animation: pulse 2s infinite;
}

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-icon, .btn-spinner {
  font-size: 1.1rem;
}

.btn-spinner {
  animation: spin 1s linear infinite;
}

/* Info Section */
.info-section {
  margin-bottom: 30px;
  animation: slideUp 1s ease-out 0.6s both;
}

.info-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  padding: 24px;
  transition: all 0.3s ease;
}

.info-card:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-3px);
}

.info-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.info-text {
  flex: 1;
}

.info-text h4 {
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
  margin: 0 0 8px 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.info-text p {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.85);
  margin: 0;
  line-height: 1.5;
  text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
}

.info-visual {
  flex-shrink: 0;
}

.cloud-animation {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cloud-animation i {
  font-size: 2.5rem;
  color: rgba(255, 255, 255, 0.9);
  animation: cloudFloat 3s ease-in-out infinite;
}

.sync-dots {
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 4px;
}

.sync-dots span {
  width: 6px;
  height: 6px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 50%;
  animation: syncPulse 1.5s ease-in-out infinite;
}

.sync-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.sync-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

/* Version Info */
.version-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
  animation: fadeIn 1s ease-out 0.8s both;
}

.env-badge {
  padding: 6px 12px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  backdrop-filter: blur(10px);
}

.version-text {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

.version-text i {
  font-size: 0.8rem;
}

/* Auto Update Component */
.auto-update-component {
  animation: fadeIn 1s ease-out 1s both;
}

/* Background Animation */
.background-animation {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.floating-shape {
  position: absolute;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  animation: float 8s ease-in-out infinite;
}

.shape-1 {
  width: 100px;
  height: 100px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 60px;
  height: 60px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 80px;
  height: 80px;
  bottom: 30%;
  left: 5%;
  animation-delay: 4s;
}

.shape-4 {
  width: 120px;
  height: 120px;
  top: 10%;
  right: 5%;
  animation-delay: 6s;
}

/* Keyframe Animations */
@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes logoFloat {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
  60% { transform: translateY(-5px); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes cloudFloat {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-8px) scale(1.05); }
}

@keyframes syncPulse {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.2); }
}

@keyframes shimmer {
  0%, 100% { 
    text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
  }
  50% { 
    text-shadow: 0 2px 20px rgba(255, 215, 0, 0.6);
  }
}

@keyframes exampleGlow {
  0%, 100% { 
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
  }
  50% { 
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
  }
}

@keyframes float {
  0%, 100% { 
    transform: translateY(0px) rotate(0deg); 
    opacity: 0.1;
  }
  50% { 
    transform: translateY(-20px) rotate(180deg); 
    opacity: 0.2;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .landing-content {
    max-width: 350px;
    padding: 20px 0;
  }
  
  .main-title {
    font-size: 2rem;
  }
  
  .serial-card {
    padding: 24px 20px;
  }
  
  .info-content {
    flex-direction: column;
    text-align: center;
    gap: 15px;
  }
  
  .floating-shape {
    display: none;
  }
}

@media (max-width: 480px) {
  .landing-content {
    width: 95%;
  }
  
  .main-title {
    font-size: 1.8rem;
  }
  
  .subtitle {
    font-size: 1rem;
  }
  
  .serial-card {
    padding: 20px 16px;
    margin-bottom: 20px;
  }
}

/* Hidden notification styles (mantener compatibilidad) */
#notification {
  position: fixed;
  bottom: 20px;
  left: 20px;
  width: 350px;
  padding: 20px;
  border-radius: 5px;
  background-color: white;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
}

.hidden {
  display: none;
}
</style>
