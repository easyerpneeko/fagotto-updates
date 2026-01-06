<template>
  <div class="modern-login-container" :class="{ 'dark-mode': darkMode }">
    <!-- Left Side - Login Form -->
    <div class="login-form-section">
      <div class="form-container">
        <!-- Logo -->
        <div class="brand-section">
          <div class="logo-container">
            <div style="text-align: center; width: 100%;">
              <img src="~@/assets/logo.png" alt="Fagotto ERP" class="brand-logo" />
            </div>
            <button 
              type="button" 
              class="theme-toggle" 
              @click="darkMode = !darkMode"
              :title="darkMode ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
            >
              <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
            </button>
          </div>
        </div>

        <!-- Login Form -->
        <div class="login-form">
          <h1 class="form-title">Iniciar Sesión</h1>
          <p class="form-subtitle">Ingresa tus datos para acceder al sistema</p>

          <!-- Username Input -->
          <div class="input-field">
            <label class="input-label">Nombre de usuario</label>
            <input 
              type="text" 
              class="input-control" 
              placeholder="nombre@ejemplo.com"
              :disabled="waitResponse"
              v-model="username" 
              @keyup.enter="sendLogin"
            >
          </div>

          <!-- Password Input -->
          <div class="input-field">
            <label class="input-label">Contraseña</label>
            <div class="password-wrapper">
              <input 
                :type="showPassword ? 'text' : 'password'"
                class="input-control" 
                placeholder="mínimo 8 caracteres" 
                :disabled="waitResponse"
                v-model="password" 
                @keyup.enter="sendLogin"
              >
              <button 
                type="button"
                class="password-toggle"
                @click="showPassword = !showPassword"
              >
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
          </div>

          <!-- Login Button -->
          <button 
            type="button" 
            class="login-button" 
            @click="sendLogin"
            :disabled="waitResponse || !username || !password"
            :class="{ 'loading': waitResponse }"
          >
            <span v-if="!waitResponse">Iniciar Sesión</span>
            <span v-else class="loading-content">
              <i class="fas fa-spinner fa-spin"></i>
              Ingresando...
            </span>
          </button>

          <!-- Remember Me -->
          <div class="remember-section">
            <label class="checkbox-container">
              <input type="checkbox" v-model="rememberPass">
              <span class="checkbox-checkmark"></span>
              <span class="checkbox-text">Recuérdame</span>
            </label>
          </div>

          <!-- Version Info -->
          <div class="version-section">
            <br>
            <span class="app-version">{{ appVersion }}</span>
          </div>

        </div>
      </div>
    </div>

    <!-- Right Side - Welcome Section -->
    <div class="welcome-section">
      <!-- Fagotto Video Fullscreen -->
      <video 
        autoplay 
        loop 
        muted 
        playsinline 
        class="welcome-video-background"
        @error="handleVideoError"
      >
        <source :src="require('./video.mp4')" type="video/mp4">
        Tu navegador no soporta video HTML5.
      </video>
      
      <!-- Welcome Content Overlay -->
      <div class="welcome-content">
        <div class="welcome-header">
          <h1 class="welcome-title">Bienvenido a Fagotto ERP</h1>
          <p class="welcome-subtitle">Sistema de gestión empresarial completo</p>
          <p class="new-year-message">¡Feliz Año Nuevo 2026! 🎊</p>
          <p class="new-year-submessage">Que este nuevo año traiga éxito y prosperidad</p>
        </div>
      </div>
    </div>

    <!-- Auto Update Component -->
    <autoUpdate class="auto-update-component"/>
  </div>
</template>

<script>
import ConfigHelper from '@/helpers/ConfigHelper';
import Loader from '@/helpers/Loader';
import autoUpdate from '../../components/autoUpdate.vue';
const fs = require('fs');
const { remote } = require('electron');
export default {
  components: { autoUpdate },
  data() {
    return {
      waitResponse: false,
      username: '',
      password: '',
      rememberPass: false,
      showPassword: false,
      name: null,
      appInProduction: process.env.NODE_ENV,
      darkMode: true, // Modo oscuro por defecto
      appVersion: process.env.npm_package_version || 'dev'
    }
  },

  async mounted() {
    this.name = ConfigHelper.ConfStr('name');
    await this.getAppVersion();
  },
  props: {
    version: {
      type: String,
      default: '1.0.0'
    }
  },
  methods: {
    async getAppVersion() {
      try {
        // Usar el mismo sistema de versiones que ya establecimos
        const BaseUrl = require('@/helpers/baseUrl.js').default;
        const Connection = require('@/helpers/Connection.js').default;
        
        const url = BaseUrl.getUrl('api/versions/official');
        const response = await Connection.request('get', url, {});
        
        if (response.success && response.data && response.data.version) {
          this.appVersion = response.data.version;
          console.log('✅ Versión oficial cargada:', this.appVersion);
        } else {
          // Fallback
          this.appVersion = '1.11.50';
        }
      } catch (error) {
        console.log('⚠️ No se pudo obtener la versión oficial, usando fallback:', error);
        this.appVersion = '1.11.50';
      }
    },
    async sendLogin() {
      this.waitResponse = true;
      Loader.fullPage()
      let fd = new FormData();
      fd.append('username', this.username);
      fd.append('password', this.password);
      let request = await this.$store.dispatch("main/sendLogin", fd);
      console.log(request);
      if (request.success) {
        if (this.rememberPass) {
          fs.writeFile('authorization.json', JSON.stringify(request.data.token), (err) => {
            if (err) {
              this.$awn.alert(err);
              return;
            }
            console.log('Authorization token set');
          });
        }
        this.$router.push('/inicio');
        this.$awn.success('Logueo Exitoso', { labels: { success: 'CORRECTO' } });
      } else {
        this.waitResponse = false;
        console.log(request.data);
        let allErrors = request.data;
        if (typeof (allErrors) == 'object') {
          for (var errorkey in allErrors) {
            if (allErrors[errorkey]) {
              for (var error of allErrors[errorkey]) {
                this.$awn.alert(error);
              }
            }
          }
        } else {
          this.$awn.alert(allErrors);
        }

      }
      Loader.hide();
    },
    handleVideoError(e) {
      console.error('Error al cargar el video:', e);
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Container Principal */
.modern-login-container {
  min-height: 100vh;
  display: flex;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Left Side - Login Form */
.login-form-section {
  flex: 1;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  min-height: 100vh;
}

.form-container {
  width: 100%;
  max-width: 400px;
  animation: slideInLeft 0.8s ease-out;
}

/* Brand Section */
.brand-section {
  margin-bottom: 48px;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.theme-toggle {
  margin-left: auto;
  background: none;
  border: 2px solid #E5E7EB;
  border-radius: 8px;
  padding: 8px;
  cursor: pointer;
  color: #6B7280;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.theme-toggle:hover {
  border-color: #7C3AED;
  color: #7C3AED;
  transform: scale(1.05);
}

.theme-toggle i {
  font-size: 1rem;
}

.brand-logo {
  width: 230px;
  height: 80px;
  object-fit: contain;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #7C3AED;
}

/* Login Form */
.login-form {
  width: 100%;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1F2937;
  margin: 0 0 8px 0;
}

.form-subtitle {
  font-size: 1rem;
  color: #6B7280;
  margin: 0 0 32px 0;
  line-height: 1.5;
}

/* Input Fields */
.input-field {
  margin-bottom: 24px;
}

.input-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.input-control {
  width: 100%;
  padding: 12px 16px;
  font-size: 1rem;
  color: #1F2937;
  background: white;
  border: 2px solid #E5E7EB;
  border-radius: 8px;
  outline: none;
  transition: all 0.2s ease;
}

.input-control::placeholder {
  color: #9CA3AF;
}

.input-control:focus {
  border-color: #7C3AED;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.input-control:disabled {
  background: #F9FAFB;
  border-color: #E5E7EB;
  color: #9CA3AF;
  cursor: not-allowed;
}

/* Password Field */
.password-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
  padding: 4px;
  transition: color 0.2s ease;
}

.password-toggle:hover {
  color: #7C3AED;
}

.forgot-password {
  display: block;
  font-size: 0.875rem;
  color: #7C3AED;
  text-decoration: none;
  margin-top: 8px;
  transition: color 0.2s ease;
}

.forgot-password:hover {
  color: #5B21B6;
  text-decoration: underline;
}

/* Login Button */
.login-button {
  width: 100%;
  padding: 12px 24px;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  background: #7C3AED;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 20px;
}

.login-button:hover:not(:disabled) {
  background: #6D28D9;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.4);
}

.login-button:active:not(:disabled) {
  transform: translateY(0);
}

.login-button:disabled {
  background: #9CA3AF;
  cursor: not-allowed;
  transform: none;
}

.login-button.loading {
  background: #6D28D9;
}

.loading-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

/* Remember Me */
.remember-section {
  margin-bottom: 24px;
  display: flex;
  justify-content: center;
}

.checkbox-container {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  user-select: none;
}

/* Version Section */
.version-section {
  text-align: center;
  margin-top: 16px;
}

.app-version {
  font-size: 0.8rem;
  color: #9CA3AF;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.checkbox-container input[type="checkbox"] {
  display: none;
}

.checkbox-checkmark {
  width: 18px;
  height: 18px;
  border: 2px solid #D1D5DB;
  border-radius: 4px;
  position: relative;
  transition: all 0.2s ease;
}

.checkbox-container input[type="checkbox"]:checked + .checkbox-checkmark {
  background: #7C3AED;
  border-color: #7C3AED;
}

.checkbox-container input[type="checkbox"]:checked + .checkbox-checkmark::after {
  content: "✓";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 0.75rem;
  font-weight: 700;
}

.checkbox-text {
  font-size: 0.875rem;
  color: #6B7280;
}

/* Alternative Login */
.alternative-login {
  margin-bottom: 24px;
}

.divider {
  position: relative;
  text-align: center;
  margin: 24px 0;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #E5E7EB;
}

.divider span {
  background: white;
  color: #9CA3AF;
  padding: 0 16px;
  font-size: 0.875rem;
}

.google-login {
  width: 100%;
  padding: 12px 24px;
  font-size: 1rem;
  font-weight: 500;
  color: #374151;
  background: white;
  border: 2px solid #E5E7EB;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.google-login:hover {
  background: #F9FAFB;
  border-color: #D1D5DB;
}

.google-login i {
  color: #4285f4;
}

/* Register Link */
.register-link {
  text-align: center;
  font-size: 0.875rem;
  color: #6B7280;
}

.register-link a {
  color: #7C3AED;
  text-decoration: none;
  font-weight: 600;
}

.register-link a:hover {
  text-decoration: underline;
}

/* Right Side - Welcome Section */
.welcome-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  position: relative;
  overflow: hidden;
  min-height: 100vh;
}

/* Video Background Full */
.welcome-video-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 1;
  filter: brightness(0.7) contrast(1.1);
}

.welcome-content {
  position: relative;
  z-index: 2;
  text-align: center;
  animation: slideInRight 0.8s ease-out;
}

.welcome-content::before {
  content: '';
  position: absolute;
  top: -20px;
  left: -20px;
  right: -20px;
  bottom: -20px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 20px;
  z-index: -1;
  backdrop-filter: blur(5px);
}

.welcome-header {
  margin-bottom: 40px;
}

.welcome-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 16px;
  backdrop-filter: blur(10px);
}

.welcome-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.welcome-subtitle {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.9);
  margin: 10px 0 0 0;
  font-weight: 400;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.new-year-message {
  font-size: 2rem;
  font-weight: 700;
  color: #FFD700;
  margin: 30px 0 5px 0;
  text-shadow: 0 2px 15px rgba(255, 215, 0, 0.5);
  animation: glow 2s ease-in-out infinite;
}

.new-year-submessage {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.95);
  margin: 5px 0 0 0;
  font-weight: 500;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

@keyframes glow {
  0%, 100% { 
    text-shadow: 0 2px 15px rgba(255, 215, 0, 0.5);
  }
  50% { 
    text-shadow: 0 2px 25px rgba(255, 215, 0, 0.8), 0 0 30px rgba(255, 215, 0, 0.6);
  }
}

/* Illustration */
.illustration {
  position: relative;
  width: 300px;
  height: 300px;
  margin: 0 auto 40px;
}

/* Video Container */
.video-container {
  position: relative;
  width: 100%;
  max-width: 500px;
  height: 350px;
  margin: 0 auto 40px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.welcome-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 20px;
  filter: brightness(0.9) contrast(1.1);
}

.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 20px;
  pointer-events: none;
}

.illustration-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.floating-element {
  position: absolute;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

.element-1 {
  width: 60px;
  height: 60px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.element-2 {
  width: 40px;
  height: 40px;
  top: 60%;
  right: 20%;
  animation-delay: 2s;
}

.element-3 {
  width: 80px;
  height: 80px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

.character-illustration {
  position: relative;
  z-index: 2;
}

.character {
  position: relative;
  margin: 0 auto 30px;
}

.character-head {
  width: 60px;
  height: 60px;
  background: #FED7AA;
  border-radius: 50%;
  margin: 0 auto 10px;
  position: relative;
}

.character-head::before {
  content: '';
  position: absolute;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 20px;
  background: #9CA3AF;
  border-radius: 10px;
}

.character-body {
  width: 80px;
  height: 100px;
  background: #7C3AED;
  border-radius: 40px 40px 10px 10px;
  margin: 0 auto;
  position: relative;
}

.laptop {
  margin: 20px auto;
  position: relative;
}

.laptop-screen {
  width: 100px;
  height: 70px;
  background: #1F2937;
  border-radius: 8px 8px 0 0;
  margin: 0 auto;
  position: relative;
}

.laptop-screen::before {
  content: '';
  position: absolute;
  top: 10px;
  left: 10px;
  right: 10px;
  bottom: 10px;
  background: #3B82F6;
  border-radius: 4px;
}

.laptop-base {
  width: 120px;
  height: 10px;
  background: #6B7280;
  border-radius: 0 0 60px 60px;
  margin: 0 auto;
}

.gift-boxes {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 20px;
}

.gift-box {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  position: relative;
  animation: bounce 2s ease-in-out infinite;
}

.box-1 {
  background: #EF4444;
  animation-delay: 0s;
}

.box-2 {
  background: #10B981;
  animation-delay: 0.2s;
}

.box-3 {
  background: #F59E0B;
  animation-delay: 0.4s;
}

.gift-box::before {
  content: '';
  position: absolute;
  top: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 4px;
  background: #FEF3C7;
  border-radius: 2px;
}

/* Version Info */
.version-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.env-badge {
  padding: 4px 8px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.version-text {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 500;
}

/* Auto Update Component */
.auto-update-component {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

/* Dark Mode Styles */
.modern-login-container.dark-mode .login-form-section {
  background: #1F2937;
}

.modern-login-container.dark-mode .brand-name {
  color: #A78BFA;
}

.modern-login-container.dark-mode .theme-toggle {
  border-color: #374151;
  color: #9CA3AF;
  background: #374151;
}

.modern-login-container.dark-mode .theme-toggle:hover {
  border-color: #A78BFA;
  color: #A78BFA;
  background: #4B5563;
}

.modern-login-container.dark-mode .form-title {
  color: #F9FAFB;
}

.modern-login-container.dark-mode .form-subtitle {
  color: #D1D5DB;
}

.modern-login-container.dark-mode .input-label {
  color: #E5E7EB;
}

.modern-login-container.dark-mode .input-control {
  background: #374151;
  border-color: #4B5563;
  color: #F9FAFB;
}

.modern-login-container.dark-mode .input-control::placeholder {
  color: #6B7280;
}

.modern-login-container.dark-mode .input-control:focus {
  border-color: #A78BFA;
  box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.1);
  background: #4B5563;
}

.modern-login-container.dark-mode .input-control:disabled {
  background: #2D3748;
  border-color: #4A5568;
  color: #718096;
}

.modern-login-container.dark-mode .password-toggle {
  color: #9CA3AF;
}

.modern-login-container.dark-mode .password-toggle:hover {
  color: #A78BFA;
}

.modern-login-container.dark-mode .forgot-password {
  color: #A78BFA;
}

.modern-login-container.dark-mode .forgot-password:hover {
  color: #C4B5FD;
}

.modern-login-container.dark-mode .login-button {
  background: #A78BFA;
}

.modern-login-container.dark-mode .login-button:hover:not(:disabled) {
  background: #8B5CF6;
  box-shadow: 0 4px 12px rgba(167, 139, 250, 0.4);
}

.modern-login-container.dark-mode .login-button.loading {
  background: #8B5CF6;
}

.modern-login-container.dark-mode .checkbox-checkmark {
  border-color: #4B5563;
  background: #374151;
}

.modern-login-container.dark-mode .checkbox-container input[type="checkbox"]:checked + .checkbox-checkmark {
  background: #A78BFA;
  border-color: #A78BFA;
}

.modern-login-container.dark-mode .checkbox-text {
  color: #D1D5DB;
}

.modern-login-container.dark-mode .app-version {
  color: #6B7280;
}

.modern-login-container.dark-mode .divider::before {
  background: #4B5563;
}

.modern-login-container.dark-mode .divider span {
  background: #1F2937;
  color: #6B7280;
}

.modern-login-container.dark-mode .google-login {
  background: #374151;
  border-color: #4B5563;
  color: #E5E7EB;
}

.modern-login-container.dark-mode .google-login:hover {
  background: #4B5563;
  border-color: #6B7280;
}

.modern-login-container.dark-mode .register-link {
  color: #9CA3AF;
}

.modern-login-container.dark-mode .register-link a {
  color: #A78BFA;
}

.modern-login-container.dark-mode .register-link a:hover {
  color: #C4B5FD;
}

/* Animations */
@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes float {
  0%, 100% { 
    transform: translateY(0px); 
  }
  50% { 
    transform: translateY(-20px); 
  }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { 
    transform: translateY(0); 
  }
  40% { 
    transform: translateY(-10px); 
  }
  60% { 
    transform: translateY(-5px); 
  }
}

/* Responsive Design */
@media (max-width: 1024px) {
  .modern-login-container {
    flex-direction: column;
  }
  
  .welcome-section {
    order: -1;
    min-height: 300px;
  }
  
  .welcome-title {
    font-size: 2rem;
  }
}

@media (max-width: 768px) {
  .login-form-section {
    padding: 20px;
  }
  
  .welcome-section {
    padding: 20px;
    min-height: 250px;
  }
  
  .form-container {
    max-width: 100%;
  }
  
  .welcome-title {
    font-size: 1.8rem;
  }
}

@media (max-width: 480px) {
  .form-title {
    font-size: 1.75rem;
  }
  
  .welcome-title {
    font-size: 1.5rem;
  }
}

/* 🎄 CHRISTMAS STYLES FOR LOGIN */
@import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Pacifico&display=swap');

.christmas-welcome {
  background: linear-gradient(180deg, #e8f4f8 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
}

.christmas-trees {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 200px;
  z-index: 1;
  overflow: hidden;
}

.tree {
  position: absolute;
  bottom: 0;
  width: 0;
  height: 0;
  border-left: 60px solid transparent;
  border-right: 60px solid transparent;
  border-bottom: 180px solid #d4e8d4;
  opacity: 0.3;
  animation: tree-sway 4s ease-in-out infinite;
}

.tree:nth-child(1) { left: 5%; animation-delay: 0s; }
.tree:nth-child(2) { left: 15%; animation-delay: 0.5s; opacity: 0.2; }
.tree:nth-child(3) { left: 28%; animation-delay: 1s; }
.tree:nth-child(4) { left: 42%; animation-delay: 1.5s; opacity: 0.25; }
.tree:nth-child(5) { left: 58%; animation-delay: 2s; }
.tree:nth-child(6) { left: 72%; animation-delay: 2.5s; opacity: 0.2; }
.tree:nth-child(7) { left: 85%; animation-delay: 3s; }
.tree:nth-child(8) { left: 95%; animation-delay: 3.5s; opacity: 0.15; }

@keyframes tree-sway {
  0%, 100% { transform: translateX(0) rotate(0deg); }
  50% { transform: translateX(5px) rotate(2deg); }
}

.christmas-snow-login {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 999;
}

.snow-flake-login {
  position: absolute;
  top: -10%;
  color: #b8d4e0;
  font-size: 1.2rem;
  opacity: 0.6;
  animation: snow-fall-login linear infinite;
}

@keyframes snow-fall-login {
  0% { transform: translateY(0) scale(0.5); }
  50% { transform: translateY(50vh) scale(1); }
  100% { transform: translateY(100vh) scale(0.5); }
}

.christmas-content {
  position: relative;
  z-index: 10;
  padding: 40px 20px;
  animation: slideInRight 0.8s ease-out;
}

.christmas-welcome-title {
  font-family: 'Pacifico', cursive;
  font-size: 4rem;
  color: #e74c3c;
  text-shadow: 2px 2px 0 #fff, 4px 4px 10px rgba(231, 76, 60, 0.3);
  margin: 0 0 30px 0;
  animation: title-float 3s ease-in-out infinite;
}

@keyframes title-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.santa-container {
  position: relative;
  margin: 40px auto;
  width: 100%;
  max-width: 500px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.santa-image {
  width: 90%;
  max-width: 450px;
  height: auto;
  filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
  animation: santa-wave 4s ease-in-out infinite;
}

@keyframes santa-wave {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  25% { transform: translateY(-10px) rotate(-2deg); }
  50% { transform: translateY(0) rotate(0deg); }
  75% { transform: translateY(-10px) rotate(2deg); }
}

.christmas-greeting {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 30px;
  max-width: 500px;
  margin: 0 auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.5);
}

.greeting-icon {
  font-size: 3rem;
  color: #3498db;
  margin-bottom: 15px;
  animation: icon-bounce 2s ease-in-out infinite;
}

@keyframes icon-bounce {
  0%, 100% { transform: translateY(0) scale(1); }
  50% { transform: translateY(-10px) scale(1.1); }
}

.greeting-title {
  font-family: 'Dancing Script', cursive;
  font-size: 2.5rem;
  color: #27ae60;
  margin: 0 0 15px 0;
  font-weight: 700;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
}

.greeting-text {
  font-size: 1.1rem;
  color: #555;
  line-height: 1.6;
  margin: 0;
}
</style>