<template>
  <div class="mobile-checkin">
    <!-- Paso 1: Validando sesión -->
    <div v-if="paso === 'validando'" class="step-container">
      <div class="spinner-lg"></div>
      <h2>Validando sesión...</h2>
    </div>

    <!-- Paso 2: Seleccionar empleado -->
    <div v-if="paso === 'seleccionar-empleado'" class="step-container">
      <div class="header-mobile">
        <i class="fas fa-user-check"></i>
        <h1>¿Quién eres?</h1>
        <p>Selecciona tu nombre de la lista</p>
      </div>

      <div class="empleados-list">
        <div 
          v-for="empleado in empleados" 
          :key="empleado.id"
          @click="seleccionarEmpleado(empleado)"
          class="empleado-item"
        >
          <div class="empleado-avatar">
            <img v-if="empleado.foto" :src="empleado.foto" />
            <i v-else class="fas fa-user"></i>
          </div>
          <div class="empleado-datos">
            <h3>{{ empleado.nombre }}</h3>
            <p>{{ empleado.cargo }}</p>
          </div>
          <i class="fas fa-chevron-right"></i>
        </div>
      </div>
    </div>

    <!-- Paso 3: Validar GPS -->
    <div v-if="paso === 'validar-gps'" class="step-container">
      <div class="spinner-lg"></div>
      <h2>Validando ubicación...</h2>
      <p>{{ mensajeGPS }}</p>
      <i class="fas fa-map-marker-alt gps-icon"></i>
    </div>

    <!-- Paso 4: Captura facial con Face SDK -->
    <div v-if="paso === 'captura-facial'" class="step-container">
      <div class="header-mobile">
        <i class="fas fa-camera"></i>
        <h1>Hola {{ empleadoSeleccionado.nombre }}</h1>
        <p>Posiciona tu rostro en el óvalo</p>
      </div>

      <!-- Video feed -->
      <div class="video-container-mobile">
        <video ref="videoMobile" autoplay playsinline class="video-mobile"></video>
        <canvas ref="canvasMobile" class="canvas-hidden"></canvas>
        
        <!-- Overlay con guía facial -->
        <div class="face-overlay">
          <div class="face-oval"></div>
        </div>

        <!-- Indicadores -->
        <div v-if="detectandoRostro" class="detection-status">
          <i class="fas fa-spinner fa-spin"></i>
          <span>Detectando rostro...</span>
        </div>

        <div v-if="rostroDetectado" class="detection-status success">
          <i class="fas fa-check-circle"></i>
          <span>¡Rostro detectado!</span>
        </div>
      </div>

      <!-- Instrucciones -->
      <div class="instrucciones-facial">
        <div class="instruccion-item" :class="{ active: iluminacionOK }">
          <i class="fas fa-lightbulb"></i>
          <span>Buena iluminación</span>
        </div>
        <div class="instruccion-item" :class="{ active: distanciaOK }">
          <i class="fas fa-expand-arrows-alt"></i>
          <span>Distancia correcta</span>
        </div>
        <div class="instruccion-item" :class="{ active: rostroDetectado }">
          <i class="fas fa-smile"></i>
          <span>Rostro detectado</span>
        </div>
      </div>

      <button 
        v-if="puedeCapturar" 
        @click="capturarYValidarFacial"
        class="btn-capturar-mobile"
      >
        <i class="fas fa-camera"></i>
        Capturar y Validar
      </button>
    </div>

    <!-- Paso 5: Validando con Face SDK -->
    <div v-if="paso === 'validando-facial'" class="step-container">
      <div class="spinner-lg"></div>
      <h2>Validando identidad...</h2>
      <p>Comparando con Face SDK...</p>
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: progresoValidacion + '%' }"></div>
      </div>
      <p class="progress-text">{{ progresoValidacion }}%</p>
    </div>

    <!-- Paso 6: Resultado exitoso -->
    <div v-if="paso === 'exito'" class="step-container">
      <div class="resultado-exitoso-mobile">
        <i class="fas fa-check-circle"></i>
        <h2>¡Entrada Registrada!</h2>
        <div class="foto-resultado">
          <img :src="fotoCapturada" />
        </div>
        <div class="datos-resultado">
          <div class="dato-item">
            <i class="fas fa-user"></i>
            <span>{{ empleadoSeleccionado.nombre }}</span>
          </div>
          <div class="dato-item">
            <i class="fas fa-clock"></i>
            <span>{{ horaRegistro }}</span>
          </div>
          <div class="dato-item">
            <i class="fas fa-map-marker-alt"></i>
            <span>{{ ubicacionRegistrada }}</span>
          </div>
          <div class="dato-item">
            <i class="fas fa-percentage"></i>
            <span>Coincidencia: {{ coincidenciaFacial }}%</span>
          </div>
        </div>
        <p class="mensaje-exito">Puedes cerrar esta ventana</p>
      </div>
    </div>

    <!-- Paso 7: Error -->
    <div v-if="paso === 'error'" class="step-container">
      <div class="resultado-error-mobile">
        <i class="fas fa-times-circle"></i>
        <h2>Error de Validación</h2>
        <p>{{ mensajeError }}</p>
        <button @click="reintentar" class="btn-reintentar-mobile">
          <i class="fas fa-redo"></i>
          Reintentar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MobileCheckin',
  data() {
    return {
      paso: 'validando', // validando, seleccionar-empleado, validar-gps, captura-facial, validando-facial, exito, error
      sessionId: null,
      sessionData: null,
      
      // Empleados
      empleados: [],
      empleadoSeleccionado: null,
      
      // GPS
      mensajeGPS: 'Obteniendo tu ubicación...',
      ubicacionActual: null,
      ubicacionRegistrada: '',
      
      // Facial
      videoStream: null,
      detectandoRostro: false,
      rostroDetectado: false,
      iluminacionOK: false,
      distanciaOK: false,
      puedeCapturar: false,
      fotoCapturada: null,
      
      // Validación
      progresoValidacion: 0,
      coincidenciaFacial: 0,
      
      // Resultado
      horaRegistro: '',
      mensajeError: ''
    }
  },
  
  async mounted() {
    console.log('📱 Mobile Check-in iniciado');
    
    // Obtener sessionId de la ruta
    this.sessionId = this.$route.params.sessionId;
    
    if (!this.sessionId) {
      this.mensajeError = 'Sesión inválida. Escanea el QR nuevamente.';
      this.paso = 'error';
      return;
    }
    
    await this.validarSesion();
  },
  
  beforeDestroy() {
    if (this.videoStream) {
      this.videoStream.getTracks().forEach(track => track.stop());
    }
  },
  
  methods: {
    async validarSesion() {
      console.log('🔐 Validando sesión:', this.sessionId);
      
      // TODO: En producción, validar con el servidor
      // Por ahora, validar con localStorage (simulación)
      const sessionKey = `checkin_session_${this.sessionId}`;
      const sessionDataStr = localStorage.getItem(sessionKey);
      
      if (!sessionDataStr) {
        this.mensajeError = 'Sesión expirada o inválida. Escanea el QR nuevamente.';
        this.paso = 'error';
        return;
      }
      
      try {
        this.sessionData = JSON.parse(sessionDataStr);
        
        // Verificar expiración
        if (Date.now() > this.sessionData.expires) {
          this.mensajeError = 'La sesión ha expirado (5 min). Escanea el QR nuevamente.';
          this.paso = 'error';
          return;
        }
        
        // Cargar empleados
        await this.cargarEmpleados();
        
        this.paso = 'seleccionar-empleado';
        
      } catch (error) {
        console.error('Error validando sesión:', error);
        this.mensajeError = 'Error al validar sesión';
        this.paso = 'error';
      }
    },
    
    async cargarEmpleados() {
      // TODO: Cargar desde API
      this.empleados = [
        {
          id: 'EMP001',
          nombre: 'Juan Pérez',
          cargo: 'Cajero',
          foto: 'https://via.placeholder.com/100'
        },
        {
          id: 'EMP002',
          nombre: 'María González',
          cargo: 'Supervisor',
          foto: 'https://via.placeholder.com/100'
        },
        {
          id: 'EMP003',
          nombre: 'Pedro Sánchez',
          cargo: 'Bodeguero',
          foto: 'https://via.placeholder.com/100'
        }
      ];
    },
    
    async seleccionarEmpleado(empleado) {
      console.log('👤 Empleado seleccionado:', empleado.nombre);
      this.empleadoSeleccionado = empleado;
      
      // Pasar a validación GPS
      this.paso = 'validar-gps';
      await this.validarGPS();
    },
    
    async validarGPS() {
      console.log('📍 Validando GPS...');
      
      if (!navigator.geolocation) {
        this.mensajeError = 'Tu dispositivo no soporta geolocalización';
        this.paso = 'error';
        return;
      }
      
      try {
        const position = await new Promise((resolve, reject) => {
          navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
          });
        });
        
        this.ubicacionActual = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        
        this.ubicacionRegistrada = `${this.ubicacionActual.lat.toFixed(4)}, ${this.ubicacionActual.lng.toFixed(4)}`;
        
        // Calcular distancia
        const distancia = this.calcularDistancia(
          this.ubicacionActual.lat,
          this.ubicacionActual.lng,
          this.sessionData.localCoords.lat,
          this.sessionData.localCoords.lng
        );
        
        console.log('📏 Distancia al local:', distancia, 'metros');
        
        if (distancia > this.sessionData.localCoords.radius) {
          this.mensajeError = `Estás muy lejos del local (${Math.round(distancia)}m). Debes estar a menos de ${this.sessionData.localCoords.radius}m.`;
          this.paso = 'error';
          return;
        }
        
        // GPS validado, pasar a captura facial
        this.mensajeGPS = '✅ Ubicación validada';
        setTimeout(() => {
          this.paso = 'captura-facial';
          this.iniciarCamara();
        }, 1000);
        
      } catch (error) {
        console.error('Error obteniendo GPS:', error);
        this.mensajeError = 'No se pudo obtener tu ubicación. Activa el GPS.';
        this.paso = 'error';
      }
    },
    
    calcularDistancia(lat1, lon1, lat2, lon2) {
      // Fórmula de Haversine
      const R = 6371e3; // Radio de la Tierra en metros
      const φ1 = lat1 * Math.PI / 180;
      const φ2 = lat2 * Math.PI / 180;
      const Δφ = (lat2 - lat1) * Math.PI / 180;
      const Δλ = (lon2 - lon1) * Math.PI / 180;
      
      const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
      
      return R * c; // Distancia en metros
    },
    
    async iniciarCamara() {
      console.log('📷 Iniciando cámara...');
      
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: 'user',
            width: { ideal: 1280 },
            height: { ideal: 720 }
          }
        });
        
        this.videoStream = stream;
        
        await this.$nextTick();
        
        if (this.$refs.videoMobile) {
          this.$refs.videoMobile.srcObject = stream;
        }
        
        // Simular detección de rostro
        setTimeout(() => {
          this.detectandoRostro = true;
          
          setTimeout(() => {
            this.iluminacionOK = true;
            
            setTimeout(() => {
              this.distanciaOK = true;
              
              setTimeout(() => {
                this.rostroDetectado = true;
                this.detectandoRostro = false;
                this.puedeCapturar = true;
              }, 1000);
            }, 1000);
          }, 1000);
        }, 1000);
        
      } catch (error) {
        console.error('Error accediendo a la cámara:', error);
        this.mensajeError = 'No se pudo acceder a la cámara. Verifica los permisos.';
        this.paso = 'error';
      }
    },
    
    async capturarYValidarFacial() {
      console.log('📸 Capturando foto...');
      
      const video = this.$refs.videoMobile;
      const canvas = this.$refs.canvasMobile;
      const context = canvas.getContext('2d');
      
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      
      // Capturar frame
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      
      this.fotoCapturada = canvas.toDataURL('image/jpeg', 0.9);
      
      // Detener cámara
      if (this.videoStream) {
        this.videoStream.getTracks().forEach(track => track.stop());
      }
      
      // Pasar a validación facial
      this.paso = 'validando-facial';
      await this.validarConFaceSDK();
    },
    
    async validarConFaceSDK() {
      console.log('🤖 Validando con Face SDK...');
      
      // TODO: Integrar Face SDK real (AWS Rekognition, Azure Face API, etc.)
      // Por ahora, simulación
      
      // Simular progreso
      const intervalo = setInterval(() => {
        this.progresoValidacion += 10;
        
        if (this.progresoValidacion >= 100) {
          clearInterval(intervalo);
        }
      }, 200);
      
      await new Promise(resolve => setTimeout(resolve, 2500));
      
      // Simular resultado
      this.coincidenciaFacial = Math.floor(Math.random() * (98 - 85) + 85);
      
      if (this.coincidenciaFacial >= 75) {
        await this.registrarEntrada();
      } else {
        this.mensajeError = `La coincidencia facial es baja (${this.coincidenciaFacial}%). Intenta nuevamente.`;
        this.paso = 'error';
      }
    },
    
    async registrarEntrada() {
      console.log('💾 Registrando entrada...');
      
      const ahora = new Date();
      this.horaRegistro = ahora.toLocaleTimeString('es-CL');
      
      const registro = {
        sessionId: this.sessionId,
        employeeId: this.empleadoSeleccionado.id,
        nombre: this.empleadoSeleccionado.nombre,
        cargo: this.empleadoSeleccionado.cargo,
        timestamp: ahora.toISOString(),
        foto: this.fotoCapturada,
        gps: this.ubicacionActual,
        ubicacion: this.ubicacionRegistrada,
        coincidenciaFacial: this.coincidenciaFacial,
        tipo: 'entrada'
      };
      
      console.log('📊 Registro:', registro);
      
      // TODO: Enviar al servidor
      // await Connection.request('post', '/api/attendance/register', registro);
      
      // Notificar al PC de entrada (vía WebSocket o polling)
      localStorage.setItem(`checkin_result_${this.sessionId}`, JSON.stringify({
        success: true,
        registro: registro
      }));
      
      // Mostrar resultado exitoso
      this.paso = 'exito';
    },
    
    reintentar() {
      // Volver al principio
      this.paso = 'seleccionar-empleado';
      this.empleadoSeleccionado = null;
      this.fotoCapturada = null;
      this.rostroDetectado = false;
      this.puedeCapturar = false;
      this.progresoValidacion = 0;
    }
  }
}
</script>

<style scoped>
.mobile-checkin {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.step-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
}

/* Spinner */
.spinner-lg {
  width: 80px;
  height: 80px;
  border: 6px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 2rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Header */
.header-mobile {
  text-align: center;
  margin-bottom: 2rem;
}

.header-mobile i {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.header-mobile h1 {
  font-size: 2rem;
  margin: 0 0 0.5rem 0;
}

.header-mobile p {
  opacity: 0.9;
  font-size: 1.1rem;
}

/* Lista de empleados */
.empleados-list {
  width: 100%;
  max-width: 500px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.empleado-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-bottom: 1px solid #ecf0f1;
  cursor: pointer;
  transition: background 0.3s;
}

.empleado-item:hover {
  background: #f8f9fa;
}

.empleado-item:last-child {
  border-bottom: none;
}

.empleado-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #667eea;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.empleado-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.empleado-avatar i {
  font-size: 2rem;
  color: white;
}

.empleado-datos {
  flex: 1;
}

.empleado-datos h3 {
  margin: 0 0 0.25rem 0;
  color: #2c3e50;
  font-size: 1.2rem;
}

.empleado-datos p {
  margin: 0;
  color: #7f8c8d;
}

.empleado-item > i {
  color: #bdc3c7;
  font-size: 1.5rem;
}

/* GPS */
.gps-icon {
  font-size: 5rem;
  margin-top: 2rem;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.1); }
}

/* Video móvil */
.video-container-mobile {
  position: relative;
  width: 100%;
  max-width: 500px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}

.video-mobile {
  width: 100%;
  display: block;
}

.canvas-hidden {
  display: none;
}

.face-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
}

.face-oval {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 60%;
  height: 80%;
  border: 4px solid rgba(255, 255, 255, 0.7);
  border-radius: 50%;
  box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
}

.detection-status {
  position: absolute;
  top: 1rem;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 50px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 600;
}

.detection-status.success {
  background: rgba(16, 185, 129, 0.9);
}

/* Instrucciones */
.instrucciones-facial {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
  justify-content: center;
}

.instruccion-item {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.75rem 1.25rem;
  border-radius: 50px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s;
}

.instruccion-item.active {
  background: rgba(16, 185, 129, 0.9);
}

.instruccion-item i {
  font-size: 1.25rem;
}

/* Botón capturar */
.btn-capturar-mobile {
  margin-top: 2rem;
  padding: 1.25rem 3rem;
  background: white;
  color: #667eea;
  border: none;
  border-radius: 50px;
  font-size: 1.25rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  transition: all 0.3s;
}

.btn-capturar-mobile:active {
  transform: scale(0.95);
}

/* Progress bar */
.progress-bar {
  width: 80%;
  max-width: 400px;
  height: 8px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 10px;
  overflow: hidden;
  margin-top: 1.5rem;
}

.progress-fill {
  height: 100%;
  background: white;
  transition: width 0.3s;
}

.progress-text {
  font-size: 1.5rem;
  font-weight: 700;
  margin-top: 1rem;
}

/* Resultado exitoso */
.resultado-exitoso-mobile {
  text-align: center;
  background: rgba(255, 255, 255, 0.95);
  padding: 2rem;
  border-radius: 20px;
  max-width: 500px;
  color: #2c3e50;
}

.resultado-exitoso-mobile i {
  font-size: 5rem;
  color: #10b981;
  margin-bottom: 1rem;
}

.resultado-exitoso-mobile h2 {
  margin: 0 0 1.5rem 0;
  font-size: 2rem;
}

.foto-resultado {
  width: 200px;
  height: 200px;
  margin: 0 auto 1.5rem;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.foto-resultado img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.datos-resultado {
  text-align: left;
}

.dato-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #ecf0f1;
}

.dato-item:last-child {
  border-bottom: none;
}

.dato-item i {
  width: 30px;
  color: #667eea;
  font-size: 1.25rem;
}

.mensaje-exito {
  margin-top: 1.5rem;
  color: #7f8c8d;
  font-style: italic;
}

/* Error */
.resultado-error-mobile {
  text-align: center;
  background: rgba(255, 255, 255, 0.95);
  padding: 2rem;
  border-radius: 20px;
  max-width: 500px;
  color: #2c3e50;
}

.resultado-error-mobile i {
  font-size: 5rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.resultado-error-mobile h2 {
  margin: 0 0 1rem 0;
}

.btn-reintentar-mobile {
  margin-top: 2rem;
  padding: 1rem 2rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
}
</style>
