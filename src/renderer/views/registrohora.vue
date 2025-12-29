<template>
  <div class="registro-asistencia-container">
    <!-- Modo: Pantalla de Entrada (PC en la entrada del local) -->
    <div v-if="modo === 'pantalla-entrada'" class="pantalla-entrada">
      <div class="header-time">
        <h1 class="time-display">{{ horaActual }}</h1>
        <p class="date-display">{{ fechaActual }}</p>
      </div>

      <div class="qr-validation-section">
        <div class="qr-header-controls">
          <h2 class="qr-title">
            <i class="fas fa-shield-alt"></i>
            {{ tipoQR === 'checkin' ? 'Código de Validación' : 'Registrar Empleado' }}
            <span class="local-badge">{{ localNombre }}</span>
          </h2>
          <button @click="cambiarTipoQR" class="btn-cambiar-tipo">
            <i :class="tipoQR === 'checkin' ? 'fas fa-user-plus' : 'fas fa-sign-in-alt'"></i>
            {{ tipoQR === 'checkin' ? 'Modo Registro' : 'Modo Check-in' }}
          </button>
        </div>
        <div class="qr-container">
          <canvas ref="qrValidacion" class="qr-code"></canvas>
          <div class="qr-timer">
            <i class="fas fa-clock"></i>
            Expira en {{ tiempoRestante }}s
          </div>
          <div class="qr-tipo-badge" :class="tipoQR">
            {{ tipoQR === 'checkin' ? 'CHECK-IN' : 'REGISTRO' }}
          </div>
        </div>
      </div>

      <div class="instructions">
        <i class="fas fa-mobile-alt"></i>
        <h3>Escanea el QR con tu teléfono</h3>
        <p>1. Abre la cámara de tu celular</p>
        <p>2. Apunta al código QR</p>
        <p>3. Selecciona tu nombre</p>
        <p>4. El teléfono validará tu rostro con Face SDK</p>
        <p class="tech-note">✨ Sin cámara en PC · Todo desde tu móvil · GPS validado</p>
      </div>

      <!-- Últimos registros -->
      <div v-if="ultimosRegistros.length > 0" class="ultimos-registros">
        <h4><i class="fas fa-history"></i> Últimas marcadas</h4>
        <div class="registro-item" v-for="registro in ultimosRegistros" :key="registro.id">
          <img 
            :src="registro.foto" 
            class="registro-foto" 
            @click="abrirFotoModal(registro.foto, registro.nombre)"
            title="Click para agrandar"
          />
          <div class="registro-info">
            <strong>{{ registro.nombre }}</strong>
            <span>{{ registro.hora }}</span>
          </div>
          <i class="fas fa-check-circle text-success"></i>
        </div>
      </div>

      <!-- Input oculto para escaneo de QR -->
      <input 
        ref="scannerInput" 
        v-model="qrScanned" 
        @input="procesarEscaneoQR"
        class="scanner-input"
        autofocus
        autocomplete="off"
      />
    </div>

    <!-- Modo: Captura Facial (después de escanear) -->
    <div v-if="modo === 'captura-facial'" class="captura-facial">
      <div class="facial-header">
        <h2>
          <i class="fas fa-camera"></i>
          ¡Hola {{ empleadoActual.nombre }}!
        </h2>
        <p>Sonríe para la cámara 📸</p>
      </div>

      <div class="video-container">
        <video ref="videoElement" autoplay playsinline class="video-feed"></video>
        <canvas ref="canvasElement" class="canvas-hidden"></canvas>
        
        <div v-if="capturando" class="capture-overlay">
          <div class="capture-circle"></div>
          <p>Capturando...</p>
        </div>

        <div v-if="validando" class="validating-overlay">
          <div class="spinner"></div>
          <p>Validando identidad...</p>
        </div>
      </div>

      <div class="facial-info">
        <div class="info-item">
          <i class="fas fa-user"></i>
          <span>{{ empleadoActual.nombre }}</span>
        </div>
        <div class="info-item">
          <i class="fas fa-briefcase"></i>
          <span>{{ empleadoActual.cargo }}</span>
        </div>
        <div class="info-item">
          <i class="fas fa-clock"></i>
          <span>{{ horaActual }}</span>
        </div>
      </div>
    </div>

    <!-- Modo: Resultado (éxito/error) -->
    <div v-if="modo === 'resultado'" class="resultado-screen">
      <div v-if="registroExitoso" class="resultado-exitoso">
        <i class="fas fa-check-circle"></i>
        <h2>¡Entrada Registrada!</h2>
        <div class="resultado-info">
          <img :src="empleadoActual.fotoCapturada" class="resultado-foto" />
          <div class="resultado-datos">
            <h3>{{ empleadoActual.nombre }}</h3>
            <p><i class="fas fa-clock"></i> {{ horaRegistro }}</p>
            <p><i class="fas fa-map-marker-alt"></i> {{ ubicacion }}</p>
            <p><i class="fas fa-percentage"></i> Coincidencia: {{ coincidenciaFacial }}%</p>
          </div>
        </div>
      </div>

      <div v-else class="resultado-error">
        <i class="fas fa-times-circle"></i>
        <h2>Error de Validación</h2>
        <p>{{ mensajeError }}</p>
        <button @click="reiniciar" class="btn-reintentar">
          <i class="fas fa-redo"></i>
          Reintentar
        </button>
      </div>
    </div>

    <!-- Botón de configuración (esquina) -->
    <button @click="abrirConfiguracion" class="btn-config">
      <i class="fas fa-cog"></i>
    </button>

    <!-- Modal para ver foto ampliada -->
    <div v-if="mostrarFotoModal" class="foto-modal-overlay" @click="cerrarFotoModal">
      <div class="foto-modal-content" @click.stop>
        <button class="foto-modal-close" @click="cerrarFotoModal">
          <i class="fas fa-times"></i>
        </button>
        <img :src="fotoModalSrc" :alt="fotoModalNombre" class="foto-modal-img" />
        <p class="foto-modal-nombre">{{ fotoModalNombre }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import QRCode from 'qrcode';
import ConfigHelper from '@/helpers/ConfigHelper.js';

export default {
  name: 'RegistroHora',
  data() {
    return {
      modo: 'pantalla-entrada', // 'pantalla-entrada', 'captura-facial', 'resultado'
      tipoQR: 'checkin', // 'checkin' o 'registro'
      localNombre: 'Local', // Nombre del local actual
      
      // QR de validación (cambia cada 30s)
      qrValidacionData: '',
      sessionId: null,
      tiempoRestante: 30,
      qrInterval: null,
      timerInterval: null,
      pollingInterval: null,
      
      // Hora y fecha
      horaActual: '',
      fechaActual: '',
      clockInterval: null,
      
      // Escaneo
      qrScanned: '',
      
      // Empleado actual
      empleadoActual: {
        id: null,
        nombre: '',
        cargo: '',
        rut: '',
        fotoRegistrada: '',
        fotoCapturada: ''
      },
      
      // Estados
      capturando: false,
      validando: false,
      registroExitoso: false,
      mensajeError: '',
      horaRegistro: '',
      ubicacion: '',
      coincidenciaFacial: 0,
      
      // Últimos registros
      ultimosRegistros: [],
      
      // Modal de foto
      mostrarFotoModal: false,
      fotoModalSrc: '',
      fotoModalNombre: '',
      
      // Webcam
      videoStream: null,
    }
  },
  
  mounted() {
    console.log('👹 Sistema de Registro de Asistencia iniciado');
    this.iniciarSistema();
  },
  
  beforeDestroy() {
    this.detenerSistema();
  },
  
  methods: {
    cargarConfiguracionLocal() {
      // Obtener nombre del negocio desde ConfigHelper (aplication.json en memoria)
      console.log('🔍 cargarConfiguracionLocal() - Obteniendo nombre del negocio desde ConfigHelper...');
      
      // Valores por defecto
      let negocioNombre = null;
      let localNombre = 'Sin Configuración';
      
      try {
        // Obtener configuración de la aplicación desde ConfigHelper
        const appConfig = ConfigHelper.Config();
        
        if (!appConfig) {
          console.error('❌ No se pudo obtener configuración de la aplicación');
          this.localNombre = '⚠️ ERROR DE CONFIGURACIÓN';
          return { negocioNombre: null, localNombre: this.localNombre };
        }
        
        // Obtener el nombre del negocio (priorizar Name sobre name_public)
        negocioNombre = appConfig.Name || appConfig.name_public;
        
        if (!negocioNombre) {
          console.error('❌ No se encontró el nombre del negocio en la configuración');
          this.localNombre = '⚠️ NOMBRE NO CONFIGURADO';
          return { negocioNombre: null, localNombre: this.localNombre };
        }
        
        localNombre = negocioNombre;
        console.log('✅ Nombre del negocio obtenido desde ConfigHelper:', negocioNombre);
        console.log('✅ Serial de la app:', appConfig.Serial);
        
        console.log('✅ Configuración cargada:');
        console.log('   🏢 Negocio:', negocioNombre);
        console.log('   🔑 Serial:', appConfig.Serial);
        
        // Actualizar título de la ventana
        document.title = 'Asistencia - ' + localNombre;
        this.localNombre = localNombre;
        
        return { negocioNombre, localNombre };
        
      } catch (error) {
        console.error('❌ Error cargando configuración desde ConfigHelper:', error);
        return { negocioNombre: null, localNombre: 'Error' };
      }
    },
    
    iniciarSistema() {
      // Cargar configuración del local
      this.cargarConfiguracionLocal();
      
      // Iniciar reloj
      this.actualizarReloj();
      this.clockInterval = setInterval(this.actualizarReloj, 1000);
      
      // Generar primer QR de validación
      this.generarQRValidacion();
      
      // Cargar últimos registros
      this.cargarUltimosRegistros();
    },
    
    detenerSistema() {
      if (this.qrInterval) clearInterval(this.qrInterval);
      if (this.timerInterval) clearInterval(this.timerInterval);
      if (this.clockInterval) clearInterval(this.clockInterval);
      if (this.pollingInterval) clearInterval(this.pollingInterval);
      if (this.videoStream) {
        this.videoStream.getTracks().forEach(track => track.stop());
      }
    },
    
    actualizarReloj() {
      const now = new Date();
      this.horaActual = now.toLocaleTimeString('es-CL', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
      });
      this.fechaActual = now.toLocaleDateString('es-CL', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    },
    
    async generarQRValidacion() {
      console.log('🔍 generarQRValidacion() - Iniciando...');
      
      // Obtener nombre del negocio desde ConfigHelper
      const { negocioNombre, localNombre } = this.cargarConfiguracionLocal();
      
      // Validar que haya un nombre de negocio
      if (!negocioNombre) {
        console.error('❌ No se puede generar QR: No hay negocio identificado');
        this.$awn.alert('⚠️ Error de configuración: No se pudo obtener el nombre del negocio', {
          labels: { alert: 'ERROR DE CONFIGURACIÓN' }
        });
        return;
      }
      
      console.log('📍 Generando QR de validación para:');
      console.log('   🏢 Negocio:', negocioNombre);
      
      // Generar sesión única
      const timestamp = Date.now();
      const sessionId = `SES${timestamp}`.substring(0, 20);
      const expiresAt = new Date(timestamp + 1800000).toISOString().slice(0, 19).replace('T', ' '); // +30 minutos
      
      // Crear sesión en servidor
      try {
        const axios = require('axios');
        const response = await axios.post('https://asistencia.fagottoerp.cl/api/create-session.php', {
          session_id: sessionId,
          negocio_nombre: negocioNombre,
          expires_at: expiresAt
        });
        
        if (response.data.success) {
          console.log('✅ Sesión de check-in creada:', sessionId);
          console.log('   Negocio:', negocioNombre);
          console.log('   Expira:', expiresAt);
        } else {
          console.error('❌ Error del servidor:', response.data.error);
          alert('Error creando sesión: ' + response.data.error);
        }
      } catch (error) {
        console.error('❌ Error creando sesión:', error);
        console.error('   URL:', 'https://asistencia.fagottoerp.cl/api/create-session.php');
        console.error('   Datos:', { session_id: sessionId, negocio_nombre: negocioNombre, expires_at: expiresAt });
        alert('Error de conexión al crear sesión. Verifica tu internet.');
      }
      
      // URL con nombre del negocio + timestamp para evitar caché
      const cacheBuster = Date.now();
      const negocioSlug = negocioNombre.toUpperCase().replace(/\s+/g, '_');
      const checkUrl = `https://asistencia.fagottoerp.cl/qrcheck.php?session=${sessionId}&negocio=${negocioSlug}&t=${cacheBuster}`;
      
      console.log('🔗 URL generada:', checkUrl);
      console.log('   Session:', sessionId);
      console.log('   🏢 Negocio:', negocioSlug);
      console.log('   Cache buster:', cacheBuster);
      
      this.qrValidacionData = checkUrl;
      this.sessionId = sessionId;
      
      // Renderizar QR con URL móvil
      this.$nextTick(() => {
        const canvas = this.$refs.qrValidacion;
        if (canvas) {
          QRCode.toCanvas(
            canvas,
            checkUrl,
            { 
              width: 300,
              margin: 2,
              color: {
                dark: '#667eea',
                light: '#ffffff'
              },
              errorCorrectionLevel: 'H'
            },
            (error) => {
              if (error) {
                console.error('❌ Error generando QR:', error);
              } else {
                console.log('✅ QR generado:', checkUrl);
              }
            }
          );
        }
      });
      
      // Reiniciar timer
      this.tiempoRestante = 30;
      if (this.timerInterval) clearInterval(this.timerInterval);
      this.timerInterval = setInterval(() => {
        this.tiempoRestante--;
        if (this.tiempoRestante <= 0) {
          this.generarQRValidacion(); // Regenerar QR
        }
      }, 1000);
      
      // Polling: Verificar si el móvil completó el registro
      this.iniciarPollingResultado();
    },
    
    iniciarPollingResultado() {
      // Limpiar polling anterior si existe
      if (this.pollingInterval) clearInterval(this.pollingInterval);
      
      // Verificar cada 2 segundos si el móvil envió el resultado
      this.pollingInterval = setInterval(() => {
        const resultKey = `checkin_result_${this.sessionId}`;
        const resultStr = localStorage.getItem(resultKey);
        
        if (resultStr) {
          try {
            const result = JSON.parse(resultStr);
            
            if (result.success) {
              // El móvil completó exitosamente
              console.log('✅ Registro recibido desde móvil:', result.registro);
              
              // Actualizar datos para mostrar resultado
              this.empleadoActual = {
                id: result.registro.employeeId,
                nombre: result.registro.nombre,
                cargo: result.registro.cargo,
                fotoCapturada: result.registro.foto
              };
              
              this.horaRegistro = new Date(result.registro.timestamp).toLocaleTimeString('es-CL');
              this.ubicacion = result.registro.ubicacion;
              this.coincidenciaFacial = result.registro.coincidenciaFacial;
              this.registroExitoso = true;
              
              // Agregar a últimos registros
              this.ultimosRegistros.unshift({
                id: Date.now(),
                nombre: result.registro.nombre,
                foto: result.registro.foto,
                hora: this.horaRegistro
              });
              
              if (this.ultimosRegistros.length > 3) {
                this.ultimosRegistros.pop();
              }
              
              // Mostrar resultado
              this.modo = 'resultado';
              
              // Limpiar resultado de localStorage
              localStorage.removeItem(resultKey);
              
              // Detener polling
              clearInterval(this.pollingInterval);
              
              // Volver a pantalla de entrada después de 5 segundos
              setTimeout(() => {
                this.reiniciar();
              }, 5000);
            }
          } catch (error) {
            console.error('Error procesando resultado:', error);
          }
        }
      }, 2000);
    },
    
    cambiarTipoQR() {
      this.tipoQR = this.tipoQR === 'checkin' ? 'registro' : 'checkin';
      
      // Regenerar QR con el nuevo tipo
      if (this.tipoQR === 'checkin') {
        this.generarQRValidacion();
      } else {
        this.generarQRRegistro();
      }
    },
    
    async generarQRRegistro() {
      // Obtener configuración desde ConfigHelper
      console.log('🔍 Obteniendo configuración desde ConfigHelper...');
      
      const { negocioNombre, localNombre } = this.cargarConfiguracionLocal();
      
      if (!negocioNombre) {
        console.error('❌ No se pudo obtener configuración del negocio');
        alert('⚠️ Error: No se pudo obtener el nombre del negocio de la configuración');
        return;
      }
      
      console.log('✅ Negocio desde ConfigHelper:', localNombre);
      
      // Generar sesión única de registro
      const timestamp = Date.now();
      const sessionId = `REG${timestamp}`.substring(0, 20);
      const expiresAt = new Date(timestamp + 86400000).toISOString().slice(0, 19).replace('T', ' '); // +24 horas
      
      console.log('📦 Datos a enviar al servidor:');
      console.log('   session_id:', sessionId, '(tipo:', typeof sessionId, ')');
      console.log('   negocio_nombre:', localNombre, '(tipo:', typeof localNombre, ')');
      console.log('   expires_at:', expiresAt, '(tipo:', typeof expiresAt, ')');
      
      // Crear sesión en servidor
      try {
        const axios = require('axios');
        const payload = {
          session_id: sessionId,
          negocio_nombre: localNombre,
          expires_at: expiresAt
        };
        
        console.log('📤 Enviando POST a create-session.php...');
        console.log('   Payload completo:', JSON.stringify(payload));
        
        const response = await axios.post('https://asistencia.fagottoerp.cl/api/create-session.php', payload);
        
        if (response.data.success) {
          console.log('✅ Sesión de registro creada:', sessionId);
          console.log('   Negocio:', localNombre);
          console.log('   Expira en 24 horas:', expiresAt);
        } else {
          console.error('❌ Error del servidor:', response.data.error);
          alert('Error creando sesión de registro: ' + response.data.error);
        }
      } catch (error) {
        console.error('❌ Error creando sesión de registro:', error);
        console.error('   URL:', 'https://asistencia.fagottoerp.cl/api/create-session.php');
        console.error('   Datos:', { session_id: sessionId, negocio_nombre: localNombre, expires_at: expiresAt });
        alert('Error de conexión al crear sesión de registro. Verifica tu internet.');
      }
      
      // URL para registro de empleado + timestamp para evitar caché
      const cacheBuster = Date.now();
      const negocioSlug = localNombre.toUpperCase().replace(/\s+/g, '_');
      const registerUrl = `https://asistencia.fagottoerp.cl/register-employee.php?session=${sessionId}&negocio=${negocioSlug}&t=${cacheBuster}`;
      
      console.log('🔗 URL generada:', registerUrl);
      console.log('   Session:', sessionId);
      console.log('   Negocio:', negocioSlug);
      console.log('   Cache buster:', cacheBuster);
      
      this.qrValidacionData = registerUrl;
      this.sessionId = sessionId;
      
      // Renderizar QR
      this.$nextTick(() => {
        const canvas = this.$refs.qrValidacion;
        if (canvas) {
          QRCode.toCanvas(
            canvas,
            registerUrl,
            { 
              width: 300,
              margin: 2,
              color: {
                dark: '#28a745',
                light: '#ffffff'
              },
              errorCorrectionLevel: 'H'
            },
            (error) => {
              if (error) {
                console.error('❌ Error generando QR registro:', error);
              } else {
                console.log('✅ QR registro generado:', registerUrl);
              }
            }
          );
        }
      });
      
      // Reiniciar timer
      this.tiempoRestante = 30;
      if (this.timerInterval) clearInterval(this.timerInterval);
      this.timerInterval = setInterval(() => {
        this.tiempoRestante--;
        if (this.tiempoRestante <= 0) {
          this.generarQRRegistro(); // Regenerar QR
        }
      }, 1000);
    },
    
    async procesarEscaneoQR() {
      // Esta función ya no se usa porque el empleado escanea el QR de la pantalla
      // con su teléfono, no al revés
      console.log('Método deprecated - ahora se usa app móvil');
    },
    
    async identificarEmpleado(employeeId) {
      console.log('🔍 Identificando empleado:', employeeId);
      
      // TODO: Consultar empleado en la base de datos
      // Por ahora, datos de ejemplo
      this.empleadoActual = {
        id: employeeId,
        nombre: 'Juan Pérez',
        cargo: 'Cajero',
        rut: '12345678-9',
        fotoRegistrada: 'https://via.placeholder.com/150', // Foto del perfil
        fotoCapturada: ''
      };
      
      // Cambiar a modo captura facial
      this.modo = 'captura-facial';
      
      // Iniciar cámara después de un momento
      setTimeout(() => {
        this.iniciarCamara();
      }, 500);
    },
    
    async iniciarCamara() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
          video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'user'
          } 
        });
        
        this.videoStream = stream;
        
        if (this.$refs.videoElement) {
          this.$refs.videoElement.srcObject = stream;
        }
        
        // Capturar foto automáticamente después de 2 segundos
        setTimeout(() => {
          this.capturarFoto();
        }, 2000);
        
      } catch (error) {
        console.error('Error accediendo a la cámara:', error);
        this.mensajeError = 'No se pudo acceder a la cámara';
        this.modo = 'resultado';
        this.registroExitoso = false;
      }
    },
    
    async capturarFoto() {
      this.capturando = true;
      
      const video = this.$refs.videoElement;
      const canvas = this.$refs.canvasElement;
      const context = canvas.getContext('2d');
      
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      
      // Capturar frame del video
      context.drawImage(video, 0, 0, canvas.width, canvas.height);
      
      // Convertir a base64
      const fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);
      this.empleadoActual.fotoCapturada = fotoBase64;
      
      this.capturando = false;
      
      // Validar identidad
      await this.validarIdentidad();
    },
    
    async validarIdentidad() {
      this.validando = true;
      
      // TODO: Implementar reconocimiento facial real con face-api.js
      // Por ahora, simulación
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      // Simular coincidencia
      this.coincidenciaFacial = Math.floor(Math.random() * (95 - 85) + 85);
      
      if (this.coincidenciaFacial >= 70) {
        // Validación exitosa
        await this.registrarEntrada();
      } else {
        // Validación fallida
        this.mensajeError = 'La foto no coincide con el empleado registrado';
        this.registroExitoso = false;
        this.validando = false;
        this.modo = 'resultado';
      }
    },
    
    async registrarEntrada() {
      // Obtener ubicación GPS
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          async (position) => {
            this.ubicacion = `${position.coords.latitude.toFixed(4)}, ${position.coords.longitude.toFixed(4)}`;
            await this.guardarRegistro();
          },
          (error) => {
            console.error('Error obteniendo GPS:', error);
            this.ubicacion = 'No disponible';
            this.guardarRegistro();
          }
        );
      } else {
        this.ubicacion = 'No soportado';
        await this.guardarRegistro();
      }
    },
    
    async guardarRegistro() {
      const ahora = new Date();
      this.horaRegistro = ahora.toLocaleTimeString('es-CL');
      
      const registro = {
        employee_id: this.empleadoActual.id,
        nombre: this.empleadoActual.nombre,
        cargo: this.empleadoActual.cargo,
        timestamp: ahora.toISOString(),
        foto: this.empleadoActual.fotoCapturada,
        ubicacion: this.ubicacion,
        coincidencia_facial: this.coincidenciaFacial,
        tipo: 'entrada'
      };
      
      console.log('💾 Guardando registro:', registro);
      
      // TODO: Guardar en base de datos
      // await Connection.request('post', '/api/attendance/register', registro);
      
      this.registroExitoso = true;
      this.validando = false;
      
      // Detener cámara
      if (this.videoStream) {
        this.videoStream.getTracks().forEach(track => track.stop());
      }
      
      // Mostrar resultado
      this.modo = 'resultado';
      
      // Añadir a últimos registros
      this.ultimosRegistros.unshift({
        id: Date.now(),
        nombre: this.empleadoActual.nombre,
        foto: this.empleadoActual.fotoCapturada,
        hora: this.horaRegistro
      });
      
      if (this.ultimosRegistros.length > 3) {
        this.ultimosRegistros.pop();
      }
      
      // Volver a pantalla de entrada después de 5 segundos
      setTimeout(() => {
        this.reiniciar();
      }, 5000);
    },
    
    reiniciar() {
      this.modo = 'pantalla-entrada';
      this.empleadoActual = {
        id: null,
        nombre: '',
        cargo: '',
        rut: '',
        fotoRegistrada: '',
        fotoCapturada: ''
      };
      this.registroExitoso = false;
      this.mensajeError = '';
      
      // Enfocar input para siguiente escaneo
      this.$nextTick(() => {
        if (this.$refs.scannerInput) {
          this.$refs.scannerInput.focus();
        }
      });
    },
    
    cargarUltimosRegistros() {
      // TODO: Cargar desde BD
      this.ultimosRegistros = [];
    },
    
    abrirConfiguracion() {
      this.$awn.info('Panel de configuración (próximamente)');
    },

    abrirFotoModal(fotoSrc, nombre) {
      this.fotoModalSrc = fotoSrc;
      this.fotoModalNombre = nombre;
      this.mostrarFotoModal = true;
    },

    cerrarFotoModal() {
      this.mostrarFotoModal = false;
      this.fotoModalSrc = '';
      this.fotoModalNombre = '';
    }
  }
}
</script>

<style scoped>
.registro-asistencia-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

/* ===== PANTALLA DE ENTRADA ===== */
.pantalla-entrada {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}

.header-time {
  text-align: center;
  color: white;
  margin-bottom: 2rem;
}

.time-display {
  font-size: 5rem;
  font-weight: 800;
  margin: 0;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.1em;
}

.date-display {
  font-size: 1.5rem;
  opacity: 0.9;
  text-transform: capitalize;
}

.qr-validation-section {
  background: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
}

.qr-header-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.qr-title {
  color: #667eea;
  margin: 0;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.qr-title .local-badge {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(245, 87, 108, 0.3);
}

.btn-cambiar-tipo {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-cambiar-tipo:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.btn-cambiar-tipo i {
  margin-right: 0.5rem;
}

.qr-container {
  position: relative;
}

.qr-tipo-badge {
  position: absolute;
  top: -10px;
  right: -10px;
  background: #667eea;
  color: white;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.qr-tipo-badge.registro {
  background: #28a745;
}

.qr-code {
  display: inline-block;
}

.qr-timer {
  margin-top: 1rem;
  font-size: 1.1rem;
  color: #764ba2;
  font-weight: 600;
}

.instructions {
  margin-top: 3rem;
  text-align: center;
  color: white;
}

.instructions i {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.instructions h3 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.instructions p {
  font-size: 1.1rem;
  margin: 0.5rem 0;
}

.tech-note {
  margin-top: 1.5rem;
  font-size: 0.9rem;
  opacity: 0.8;
  font-style: italic;
}

.scanner-input {
  position: absolute;
  top: -9999px;
  left: -9999px;
}

/* ===== ÚLTIMOS REGISTROS ===== */
.ultimos-registros {
  position: absolute;
  bottom: 2rem;
  right: 2rem;
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem;
  border-radius: 15px;
  min-width: 300px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.ultimos-registros h4 {
  margin-top: 0;
  color: #667eea;
  margin-bottom: 1rem;
}

.registro-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border-bottom: 1px solid #eee;
}

.registro-item:last-child {
  border-bottom: none;
}

.registro-foto {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.registro-foto:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.registro-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.registro-info strong {
  color: #2c3e50;
}

.registro-info span {
  font-size: 0.9rem;
  color: #7f8c8d;
}

/* ===== CAPTURA FACIAL ===== */
.captura-facial {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}

.facial-header {
  text-align: center;
  color: white;
  margin-bottom: 2rem;
}

.facial-header h2 {
  font-size: 3rem;
  margin: 0 0 0.5rem 0;
}

.video-container {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.video-feed {
  display: block;
  width: 640px;
  height: 480px;
  object-fit: cover;
}

.canvas-hidden {
  display: none;
}

.capture-overlay,
.validating-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
}

.capture-circle {
  width: 100px;
  height: 100px;
  border: 5px solid white;
  border-radius: 50%;
  animation: pulse 1s infinite;
}

.spinner {
  width: 60px;
  height: 60px;
  border: 5px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.7; }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.facial-info {
  margin-top: 2rem;
  display: flex;
  gap: 2rem;
  background: rgba(255, 255, 255, 0.95);
  padding: 1.5rem 2rem;
  border-radius: 15px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.1rem;
  color: #2c3e50;
}

.info-item i {
  color: #667eea;
  font-size: 1.5rem;
}

/* ===== RESULTADO ===== */
.resultado-screen {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}

.resultado-exitoso,
.resultado-error {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  text-align: center;
  max-width: 600px;
}

.resultado-exitoso i {
  font-size: 5rem;
  color: #10b981;
  margin-bottom: 1rem;
}

.resultado-error i {
  font-size: 5rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.resultado-info {
  display: flex;
  gap: 2rem;
  margin-top: 2rem;
  align-items: center;
  text-align: left;
}

.resultado-foto {
  width: 150px;
  height: 150px;
  border-radius: 15px;
  object-fit: cover;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.resultado-datos {
  flex: 1;
}

.resultado-datos h3 {
  margin-top: 0;
  color: #2c3e50;
  font-size: 1.8rem;
}

.resultado-datos p {
  margin: 0.5rem 0;
  color: #7f8c8d;
  font-size: 1.1rem;
}

.btn-reintentar {
  margin-top: 2rem;
  padding: 1rem 2rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-reintentar:hover {
  background: #764ba2;
  transform: translateY(-2px);
}

/* ===== BOTÓN CONFIGURACIÓN ===== */
.btn-config {
  position: fixed;
  top: 2rem;
  right: 2rem;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.3s;
  backdrop-filter: blur(10px);
}

.btn-config:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.text-success {
  color: #10b981;
}

/* ===== MODAL DE FOTO ===== */
.foto-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.2s;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.foto-modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  animation: zoomIn 0.3s;
}

@keyframes zoomIn {
  from { 
    transform: scale(0.8);
    opacity: 0;
  }
  to { 
    transform: scale(1);
    opacity: 1;
  }
}

.foto-modal-close {
  position: absolute;
  top: -50px;
  right: 0;
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid white;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s;
  backdrop-filter: blur(10px);
}

.foto-modal-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.foto-modal-img {
  max-width: 100%;
  max-height: 80vh;
  border-radius: 15px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  object-fit: contain;
}

.foto-modal-nombre {
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
  text-align: center;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  margin: 0;
}
</style>
