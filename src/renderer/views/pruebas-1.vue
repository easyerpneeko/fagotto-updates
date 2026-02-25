<template>
  <div class="pruebas-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-title">
          <div class="title-with-icon">
            <div class="icon-test">
              <i class="fas fa-id-card"></i>
            </div>
            <div>
              <h1 class="page-title">Validación Convenio Muvify</h1>
              <p class="page-subtitle">Consulta si un RUT está registrado en el convenio Turbus</p>
            </div>
          </div>
        </div>
        <div class="header-badge">
          <span class="badge badge-test">CLUB MUVIFY</span>
        </div>
      </div>
    </div>

    <!-- Content Card -->
    <div class="content-container">
      <div class="test-card">
        <div class="card-header">
          <div class="card-info">
            <h3 class="card-title">
              <i class="fas fa-search"></i>
              Consulta de RUT
            </h3>
            <p class="card-description">
              Ingresa un RUT para verificar si está registrado en el convenio Club Muvify Turbus.
              La API consultará la base de datos y retornará el estado del beneficio.
            </p>
          </div>
        </div>
        
        <div class="card-body">
          <!-- Formulario de consulta -->
          <div class="consulta-form">
            <div class="form-group">
              <label for="rutInput" class="form-label">
                <i class="fas fa-id-card me-2"></i>
                RUT a consultar
              </label>
              <div class="input-group">
                <input 
                  type="text" 
                  id="rutInput"
                  class="form-control form-control-lg"
                  v-model="rutConsulta"
                  @input="formatearRUT"
                  @keyup.enter="consultarRUT"
                  :disabled="consultando"
                  placeholder="Ej: 180589309 o 18058930-9"
                  maxlength="12"
                >
                <button 
                  class="btn btn-primary btn-lg"
                  @click="consultarRUT"
                  :disabled="consultando || !rutConsulta"
                >
                  <i :class="['fas', consultando ? 'fa-spinner fa-spin' : 'fa-search']"></i>
                  {{ consultando ? 'Consultando...' : 'Consultar' }}
                </button>
              </div>
              <small class="form-text text-muted">
                <i class="fas fa-info-circle"></i>
                Acepta con o sin guión: 12345678-9 o 123456789
              </small>
            </div>
          </div>

          <!-- Resultado de la consulta -->
          <div v-if="resultado" class="resultado-container mt-4">
            <div :class="['resultado-card', resultado.success ? 'success' : 'error']">
              <div class="resultado-icon">
                <i :class="['fas', resultado.success ? 'fa-check-circle' : 'fa-times-circle']"></i>
              </div>
              <div class="resultado-content">
                <h4>{{ resultado.success ? '✅ RUT en Convenio' : '❌ RUT no encontrado' }}</h4>
                <p>{{ resultado.mensaje }}</p>
                
                <!-- Alerta para respuesta minimal -->
                <div v-if="resultado.success && resultado.esMinimal" class="alert alert-warning mt-3">
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>Nota:</strong> El endpoint <code>/miembros/minimal/</code> solo confirma la existencia del RUT en el convenio.
                  Los datos personales están protegidos/encriptados por seguridad.
                  Para obtener información completa del cliente, solicita a Turbus el endpoint con datos completos.
                </div>
                
                <div v-if="resultado.success && resultado.data" class="resultado-detalles mt-3">
                  <h5><i class="fas fa-user-check me-2"></i>Información del Cliente</h5>
                  <div class="detalles-grid">
                    <div class="detalle-item" v-if="resultado.data.nombre">
                      <span class="detalle-label">Nombre:</span>
                      <span class="detalle-value">{{ resultado.data.nombre }}</span>
                    </div>
                    <div class="detalle-item" v-if="resultado.data.email">
                      <span class="detalle-label">Email:</span>
                      <span class="detalle-value">{{ resultado.data.email }}</span>
                    </div>
                    <div class="detalle-item" v-if="resultado.data.telefono">
                      <span class="detalle-label">Teléfono:</span>
                      <span class="detalle-value">{{ resultado.data.telefono }}</span>
                    </div>
                    <div class="detalle-item" v-if="resultado.data.estado">
                      <span class="detalle-label">Estado:</span>
                      <span class="detalle-value">{{ resultado.data.estado }}</span>
                    </div>
                  </div>
                  
                  <!-- Respuesta RAW de la API para debugging -->
                  <div v-if="resultado.rawData" class="raw-data-container mt-3">
                    <div class="raw-data-header">
                      <h6>
                        <i class="fas fa-code me-2"></i>
                        Respuesta Completa de la API (Debug)
                      </h6>
                      <button class="btn btn-sm btn-outline-secondary" @click="copiarRawData">
                        <i class="fas fa-copy"></i> Copiar JSON
                      </button>
                    </div>
                    <pre class="raw-data-content">{{ resultado.rawData }}</pre>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Información de la API -->
          <div class="api-info mt-4">
            <h5><i class="fas fa-server me-2"></i>Información de la API</h5>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Servidor:</span>
                <code class="info-value">https://clubmuvify.turbus.cl</code>
              </div>
              <div class="info-item">
                <span class="info-label">Endpoint:</span>
                <code class="info-value">/miembros/minimal/{documentNumber}</code>
              </div>
              <div class="info-item">
                <span class="info-label">RUT de prueba:</span>
                <code class="info-value">18058930-9</code>
                <button class="btn btn-sm btn-outline-secondary ms-2" @click="usarRutPrueba">
                  <i class="fas fa-copy"></i> Usar
                </button>
              </div>
              <div class="info-item">
                <span class="info-label">Estado API:</span>
                <span class="badge badge-warning">⚠️ Endpoint MINIMAL - Solo validación</span>
              </div>
            </div>
            <div class="alert alert-info mt-3">
              <i class="fas fa-lightbulb me-2"></i>
              <strong>Importante:</strong> El endpoint actual <code>/miembros/minimal/{rut}</code> solo retorna un token encriptado 
              confirmando que el RUT existe en el convenio. <strong>NO retorna datos personales</strong> (nombre, email, teléfono).
              <br><br>
              <strong>¿Necesitas los datos del cliente?</strong> Solicita a Turbus/Muvify un endpoint con información completa, 
              por ejemplo: <code>/miembros/completo/{rut}</code> o <code>/miembros/detalle/{rut}</code>
            </div>
          </div>

          <!-- Historial de consultas -->
          <div v-if="historialConsultas.length > 0" class="historial-container mt-4">
            <h5><i class="fas fa-history me-2"></i>Historial de Consultas</h5>
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>Hora</th>
                    <th>RUT</th>
                    <th>Resultado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in historialConsultas" :key="index">
                    <td>{{ item.hora }}</td>
                    <td><code>{{ item.rut }}</code></td>
                    <td>
                      <span :class="['badge', item.encontrado ? 'badge-success' : 'badge-danger']">
                        {{ item.encontrado ? 'En convenio' : 'No encontrado' }}
                      </span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary" @click="rutConsulta = item.rut">
                        <i class="fas fa-redo"></i> Re-consultar
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';

// Configuración de la API Muvify
const MUVIFY_API = {
  baseURL: 'https://clubmuvify.turbus.cl',
  token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlbXByZXNhIjoiQ09QUEVMSUEiLCJkZXBhcnRhbWVudG8iOiJWRU5UQVMiLCJhbWJpZW50ZSI6IlFBUyIsImlhdCI6MTc2MDQ0MDc4MywiZXhwIjoyNjI0MzU0MzgzfQ.S2MufbSlY63aG43qKMEXRQL7gyK-S5fKWG5hHH7_ggU',
  endpoint: '/miembros/minimal'
};

export default {
  name: 'Pruebas1',
  data() {
    return {
      rutConsulta: '',
      consultando: false,
      resultado: null,
      historialConsultas: []
    };
  },
  mounted() {
    console.log('🧪 [MUVIFY API] Vista cargada');
    // Cargar historial del localStorage
    this.cargarHistorial();
  },
  methods: {
    /**
     * Limpia el RUT (quita puntos, espacios y convierte a mayúsculas)
     */
    limpiarRUT(rut) {
      if (!rut) return '';
      return rut.toString()
        .replace(/\./g, '')
        .replace(/\s/g, '')
        .replace(/-/g, '')
        .toUpperCase()
        .trim();
    },

    /**
     * Formatea el RUT mientras el usuario escribe
     * Agrega el guión automáticamente
     */
    formatearRUT() {
      if (!this.rutConsulta) return;
      
      // Limpiar el RUT
      let rutLimpio = this.limpiarRUT(this.rutConsulta);
      
      // Si tiene más de 1 carácter, agregar el guión
      if (rutLimpio.length > 1) {
        const cuerpo = rutLimpio.slice(0, -1);
        const dv = rutLimpio.slice(-1);
        this.rutConsulta = `${cuerpo}-${dv}`;
      } else {
        this.rutConsulta = rutLimpio;
      }
    },

    /**
     * Valida el formato del RUT chileno
     */
    validarRUT(rut) {
      // Limpiar RUT
      const rutLimpio = this.limpiarRUT(rut);
      
      // Validar largo mínimo
      if (rutLimpio.length < 2) {
        return { valido: false, mensaje: 'RUT muy corto' };
      }

      // Separar cuerpo y dígito verificador
      const cuerpo = rutLimpio.slice(0, -1);
      const dv = rutLimpio.slice(-1);

      // Validar que el cuerpo sea numérico
      if (!/^\d+$/.test(cuerpo)) {
        return { valido: false, mensaje: 'RUT debe contener solo números' };
      }

      // Calcular dígito verificador
      let suma = 0;
      let multiplo = 2;

      for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += multiplo * parseInt(cuerpo.charAt(i));
        multiplo = multiplo < 7 ? multiplo + 1 : 2;
      }

      const dvEsperado = 11 - (suma % 11);
      const dvCalculado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();

      if (dv !== dvCalculado) {
        return { 
          valido: false, 
          mensaje: `Dígito verificador incorrecto. Debería ser: ${cuerpo}-${dvCalculado}` 
        };
      }

      return { valido: true, mensaje: 'RUT válido' };
    },

    /**
     * Formatea el RUT con guión para enviarlo a la API
     */
    formatearRUTParaAPI(rut) {
      const rutLimpio = this.limpiarRUT(rut);
      if (rutLimpio.length > 1) {
        const cuerpo = rutLimpio.slice(0, -1);
        const dv = rutLimpio.slice(-1);
        return `${cuerpo}-${dv}`;
      }
      return rutLimpio;
    },

    async consultarRUT() {
      if (!this.rutConsulta || !this.rutConsulta.trim()) {
        this.$awn.warning('Por favor ingresa un RUT válido');
        return;
      }

      // Validar formato del RUT
      const validacion = this.validarRUT(this.rutConsulta);
      if (!validacion.valido) {
        this.$awn.warning(`❌ ${validacion.mensaje}`);
        console.log('⚠️ [MUVIFY API] Validación fallida:', validacion.mensaje);
        return;
      }

      this.consultando = true;
      this.resultado = null;

      // Limpiar y formatear el RUT para la API (con guión)
      const rutFormateado = this.formatearRUTParaAPI(this.rutConsulta);
      
      console.log('🔍 [MUVIFY API] Consultando RUT:', rutFormateado);
      console.log('✅ [MUVIFY API] RUT válido según algoritmo chileno');

      try {
        // Construir URL
        const url = `${MUVIFY_API.baseURL}${MUVIFY_API.endpoint}/${rutFormateado}`;
        console.log('🌐 [MUVIFY API] URL:', url);

        // Hacer la petición
        const response = await fetch(url, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${MUVIFY_API.token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        console.log('📥 [MUVIFY API] Response status:', response.status);

        if (response.ok) {
          const data = await response.json();
          console.log('✅ [MUVIFY API] Datos recibidos:', data);
          console.log('✅ [MUVIFY API] Tipo de respuesta:', typeof data);
          console.log('✅ [MUVIFY API] Keys disponibles:', Object.keys(data));
          
          // Verificar si la respuesta es mínima (solo token member)
          const esRespuestaMinimal = data.member && typeof data.member === 'string' && data.member.includes(':');
          
          if (esRespuestaMinimal) {
            console.log('ℹ️ [MUVIFY API] Respuesta MINIMAL detectada - solo token de confirmación');
            
            this.resultado = {
              success: true,
              mensaje: '✅ RUT CONFIRMADO en el convenio Club Muvify',
              esMinimal: true,
              data: {
                nombre: '✓ Registrado (datos protegidos)',
                email: '✓ Registrado (datos protegidos)',
                telefono: '✓ Registrado (datos protegidos)',
                estado: '✓ Activo en convenio',
                memberToken: data.member.substring(0, 50) + '...' // Mostrar solo inicio del token
              },
              rawData: JSON.stringify(data, null, 2)
            };

            this.$awn.success(`✅ RUT confirmado en convenio Club Muvify`);
          } else {
            // Intentar extraer datos con diferentes posibles nombres de campos
            const extraerCampo = (obj, ...posiblesNombres) => {
              for (const nombre of posiblesNombres) {
                if (obj && obj[nombre] !== undefined && obj[nombre] !== null && obj[nombre] !== '') {
                  return obj[nombre];
                }
              }
              return 'N/A';
            };

            const nombre = extraerCampo(data, 'nombre', 'fullName', 'name', 'nombres', 'nombreCompleto', 'full_name', 'firstName', 'lastName');
            const email = extraerCampo(data, 'email', 'correo', 'mail', 'emailAddress', 'e_mail');
            const telefono = extraerCampo(data, 'telefono', 'phone', 'celular', 'movil', 'phoneNumber', 'telephone');
            const estado = extraerCampo(data, 'estado', 'status', 'active', 'activo', 'estatus');

            console.log('📋 [MUVIFY API] Datos extraídos:', { nombre, email, telefono, estado });

            this.resultado = {
              success: true,
              mensaje: 'RUT encontrado en el convenio Club Muvify',
              esMinimal: false,
              data: {
                nombre: nombre,
                email: email,
                telefono: telefono,
                estado: estado
              },
              rawData: JSON.stringify(data, null, 2)
            };

            this.$awn.success(`✅ RUT encontrado en el convenio`);
          }
          
          // Agregar al historial
          this.agregarAlHistorial(rutFormateado, true);
        } else if (response.status === 404) {
          console.log('⚠️ [MUVIFY API] RUT no encontrado');
          
          this.resultado = {
            success: false,
            mensaje: 'El RUT consultado no está registrado en el convenio Club Muvify',
            data: null
          };

          this.$awn.warning('❌ RUT no encontrado en el convenio');
          
          // Agregar al historial
          this.agregarAlHistorial(rutFormateado, false);
        } else {
          throw new Error(`Error HTTP: ${response.status}`);
        }
      } catch (error) {
        console.error('❌ [MUVIFY API] Error:', error);
        
        this.resultado = {
          success: false,
          mensaje: `Error al consultar la API: ${error.message}`,
          data: null
        };

        this.$awn.alert('❌ Error al consultar la API. Verifica tu conexión.');
      } finally {
        this.consultando = false;
      }
    },

    usarRutPrueba() {
      this.rutConsulta = '18058930-9';
      this.$awn.info('RUT de prueba copiado');
    },

    copiarRawData() {
      if (this.resultado && this.resultado.rawData) {
        // Copiar al clipboard
        navigator.clipboard.writeText(this.resultado.rawData).then(() => {
          this.$awn.success('JSON copiado al portapapeles');
        }).catch(err => {
          console.error('Error copiando:', err);
          this.$awn.warning('No se pudo copiar automáticamente');
        });
      }
    },

    agregarAlHistorial(rut, encontrado) {
      const item = {
        rut: rut,
        encontrado: encontrado,
        hora: moment().format('HH:mm:ss')
      };

      // Agregar al inicio del array
      this.historialConsultas.unshift(item);

      // Limitar a 10 items
      if (this.historialConsultas.length > 10) {
        this.historialConsultas = this.historialConsultas.slice(0, 10);
      }

      // Guardar en localStorage
      this.guardarHistorial();
    },

    guardarHistorial() {
      try {
        localStorage.setItem('muvify_historial', JSON.stringify(this.historialConsultas));
      } catch (error) {
        console.error('Error guardando historial:', error);
      }
    },

    cargarHistorial() {
      try {
        const historial = localStorage.getItem('muvify_historial');
        if (historial) {
          this.historialConsultas = JSON.parse(historial);
        }
      } catch (error) {
        console.error('Error cargando historial:', error);
        this.historialConsultas = [];
      }
    }
  }
};
</script>

<style scoped>
/* Page Layout */
.pruebas-page {
  min-height: 100vh;
  background: #f5f7fa;
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
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.icon-test {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.page-title {
  color: #2d3748;
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.page-subtitle {
  color: #4a5568;
  margin: 0.25rem 0 0;
  font-size: 1rem;
}

.header-badge .badge-test {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

/* Content Container */
.content-container {
  animation: fadeInUp 0.6s ease-out;
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

/* Test Card */
.test-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
}

.card-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-description {
  margin: 0;
  opacity: 0.95;
  line-height: 1.6;
}

.card-body {
  padding: 2rem;
}

/* Formulario de consulta */
.consulta-form {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  margin-bottom: 1rem;
}

.form-label {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  font-size: 1.1rem;
}

.input-group {
  display: flex;
  gap: 0.5rem;
}

.form-control-lg {
  padding: 0.75rem 1rem;
  font-size: 1.1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.form-control-lg:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  outline: none;
}

.form-control-lg:disabled {
  background-color: #e2e8f0;
  cursor: not-allowed;
}

.btn-lg {
  padding: 0.75rem 2rem;
  font-size: 1.1rem;
  font-weight: 600;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-text {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.9rem;
}

/* Resultado de la consulta */
.resultado-container {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.resultado-card {
  padding: 2rem;
  border-radius: 12px;
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
}

.resultado-card.success {
  background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
  border: 2px solid #28a745;
}

.resultado-card.error {
  background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
  border: 2px solid #dc3545;
}

.resultado-icon {
  font-size: 3rem;
  flex-shrink: 0;
}

.resultado-card.success .resultado-icon {
  color: #28a745;
}

.resultado-card.error .resultado-icon {
  color: #dc3545;
}

.resultado-content {
  flex: 1;
}

.resultado-content h4 {
  margin: 0 0 0.5rem;
  font-size: 1.5rem;
  font-weight: 700;
}

.resultado-content p {
  margin: 0;
  font-size: 1.1rem;
  opacity: 0.9;
}

.resultado-detalles {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.resultado-detalles h5 {
  margin: 0 0 1rem;
  font-size: 1.2rem;
  color: #2d3748;
}

.detalles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.detalle-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detalle-label {
  font-size: 0.85rem;
  color: #718096;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detalle-value {
  font-size: 1rem;
  color: #2d3748;
  font-weight: 500;
}

/* Raw Data Debug Container */
.raw-data-container {
  background: #1e293b;
  border-radius: 8px;
  padding: 1rem;
  border: 2px solid #334155;
}

.raw-data-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #334155;
}

.raw-data-header h6 {
  color: #38bdf8;
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
}

.raw-data-content {
  background: #0f172a;
  color: #a5f3fc;
  padding: 1rem;
  border-radius: 6px;
  font-family: 'Courier New', Consolas, monospace;
  font-size: 0.85rem;
  line-height: 1.5;
  overflow-x: auto;
  margin: 0;
  max-height: 400px;
  overflow-y: auto;
}

.raw-data-content::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.raw-data-content::-webkit-scrollbar-track {
  background: #1e293b;
  border-radius: 4px;
}

.raw-data-content::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 4px;
}

.raw-data-content::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

/* Información de la API */
.api-info {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 12px;
  border-left: 4px solid #667eea;
}

.api-info h5 {
  margin: 0 0 1rem;
  font-size: 1.1rem;
  color: #2d3748;
}

.info-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.info-label {
  font-weight: 600;
  color: #4a5568;
  min-width: 120px;
}

.info-value {
  background: #2d3748;
  color: #a0aec0;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-family: 'Courier New', monospace;
  font-size: 0.9rem;
}

.btn-sm {
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background: white;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-outline-secondary:hover {
  background: #e2e8f0;
  border-color: #a0aec0;
}

/* Historial de consultas */
.historial-container {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.historial-container h5 {
  margin: 0 0 1rem;
  font-size: 1.1rem;
  color: #2d3748;
}

.table {
  margin: 0;
}

.table thead th {
  background: #f8f9fa;
  font-weight: 600;
  color: #4a5568;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.75rem;
}

.table tbody td {
  padding: 0.75rem;
  vertical-align: middle;
}

.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}

code {
  background: #2d3748;
  color: #a0aec0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 0.9rem;
}

.badge {
  padding: 0.35rem 0.75rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.8rem;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.badge-warning {
  background-color: #ffc107;
  color: #212529;
  font-weight: 600;
}

.alert {
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid transparent;
}

.alert-info {
  background-color: #d1ecf1;
  border-color: #bee5eb;
  color: #0c5460;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffeaa7;
  color: #856404;
}

.alert code {
  background: rgba(0, 0, 0, 0.1);
  padding: 0.2rem 0.4rem;
  border-radius: 3px;
  font-size: 0.9em;
}

.btn-outline-primary {
  border: 1px solid #667eea;
  color: #667eea;
  background: white;
}

.btn-outline-primary:hover {
  background: #667eea;
  color: white;
}

/* Responsive */
@media (max-width: 768px) {
  .pruebas-page {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .input-group {
    flex-direction: column;
  }
  
  .btn-lg {
    width: 100%;
    justify-content: center;
  }
  
  .resultado-card {
    flex-direction: column;
    text-align: center;
  }
  
  .detalles-grid {
    grid-template-columns: 1fr;
  }
  
  .info-item {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .info-value {
    width: 100%;
    word-break: break-all;
  }
}
</style>
