<template>
  <div class="modal fade" id="modalCupon" tabindex="-1" role="dialog" aria-labelledby="modalCuponLabel" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="background-color: white;">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="modalCuponLabel">
            <i class="fas fa-ticket-alt mr-2"></i>
            Ingresar Cupón de Descuento
          </h5>
          <button type="button" class="close text-white" @click="closeModal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <div class="cupon-icon">
              <i class="fas fa-ticket-alt"></i>
            </div>
            <p class="text-muted mt-2">Ingresa tu código de cupón para aplicar el descuento</p>
          </div>
          
          <div class="form-group">
            <label for="codigoCupon">Código del Cupón</label>
            <input 
              type="text" 
              class="form-control form-control-lg text-center text-uppercase" 
              id="codigoCupon" 
              v-model="codigoCupon"
              placeholder="Ej: 100"
              @keyup.enter="validarCupon"
              ref="inputCupon"
              maxlength="20"
              style="letter-spacing: 2px; font-weight: bold;">
          </div>

          <div v-if="cuponValidado" class="alert alert-success" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <strong>¡Cupón válido!</strong> Se aplicará un descuento del {{ descuento }}%
          </div>

          <div v-if="errorMensaje" class="cupon-error-card">
            <div class="error-icon">
              <i class="fas fa-times-circle"></i>
            </div>
            <div class="error-content">
              <h6 class="error-title">Cupón No Disponible</h6>
              <p class="error-message">{{ errorMensaje }}</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">
            <i class="fas fa-times mr-1"></i>
            Cancelar
          </button>
          <button type="button" class="btn btn-success" @click="validarCupon" :disabled="!codigoCupon || loading">
            <i class="fas fa-check mr-1" v-if="!loading"></i>
            <i class="fas fa-spinner fa-spin mr-1" v-else></i>
            {{ loading ? 'Validando...' : 'Aplicar Cupón' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl';

export default {
  data() {
    return {
      codigoCupon: '',
      cuponValidado: false,
      descuento: 0,
      errorMensaje: '',
      loading: false
    }
  },
  mounted() {
    // 🧹 Limpiar simulaciones locales - solo usar SQL real
    localStorage.removeItem('cuponesSQL');
    localStorage.removeItem('cuponesUsados');
    console.log('🧹 Limpiando datos locales - solo usar SQL real');
  },
  methods: {
    async validarCupon() {
      if (!this.codigoCupon || this.codigoCupon.trim() === '') {
        this.errorMensaje = 'Por favor ingresa un código de cupón';
        return;
      }

      this.loading = true;
      this.errorMensaje = '';
      this.cuponValidado = false;

      try {
        const codigo = this.codigoCupon.trim().toUpperCase();
        
        console.log('🔍 Iniciando validación de cupón:', codigo);
        
        // 🔥 SOLO SQL REAL - SIN SIMULACIÓN
        console.log('🌐 Validando cupón en SQL real...');
        const url = BaseUrl.getUrl('api/cupones/validar');
        console.log('📡 URL completa:', url);
        
        const response = await Connection.request('POST', url, { codigo });
        console.log('🔍 Respuesta completa del backend:', response);
        
        // ✅ Cupón válido (status 200 + valido: true)
        if (response && response.success && response.data && response.data.valido) {
          console.log('✅ Cupón válido desde SQL real:', response.data);
          this.descuento = response.data.cupon.valor_descuento || 100;
          this.cuponValidado = true;
          
          this.$awn.success(`Cupón "${codigo}" válido: 2x1 en Pastas`);
          
          setTimeout(() => {
            this.closeModal();
            this.$emit('abrir-seleccion', {
              codigo: codigo,
              descuento: this.descuento,
              tipo_descuento: response.data.cupon.tipo_descuento || 'porcentaje',
              cupon_id: response.data.cupon.id || parseInt(codigo)
            });
          }, 800);
          
          this.loading = false;
          return;
        }
        
        // ❌ Cupón inválido/usado (status 400 o success=false pero con data.mensaje)
        if (response && response.data && response.data.mensaje) {
          console.log('❌ Cupón inválido/usado:', response.data.mensaje);
          this.errorMensaje = response.data.mensaje;
          this.loading = false;
          return;
        }
        
        // ❌ Error de conexión real
        console.error('❌ Error de backend o conexión:', response);
        this.errorMensaje = 'Error al validar cupón. Verifica que el backend esté funcionando.';
        this.loading = false;
        
      } catch (error) {
        console.error('Error al validar cupón:', error);
        this.errorMensaje = 'Error de conexión. Verifica tu internet.';
      } finally {
        this.loading = false;
      }
    },
    closeModal() {
      this.codigoCupon = '';
      this.cuponValidado = false;
      this.descuento = 0;
      this.errorMensaje = '';
      this.loading = false;
      $('#modalCupon').modal('hide');
    },
    openModal() {
      this.codigoCupon = '';
      this.cuponValidado = false;
      this.descuento = 0;
      this.errorMensaje = '';
      $('#modalCupon').modal('show');
      
      // Enfocar el input después de que el modal se abra
      setTimeout(() => {
        if (this.$refs.inputCupon) {
          this.$refs.inputCupon.focus();
        }
      }, 500);
    }
  }
}
</script>

<style scoped>
/* Header del modal */
.modal-header.bg-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  border-bottom: none;
}

.modal-title {
  font-weight: 700;
  font-size: 1.3rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal content */
.modal-content {
  border-radius: 15px;
  border: none;
  box-shadow: 0 15px 50px rgba(0,0,0,0.2);
  overflow: hidden;
}

/* Icono de cupón animado */
.cupon-icon {
  font-size: 72px;
  color: #10b981;
  animation: pulse 2s ease-in-out infinite;
  filter: drop-shadow(0 4px 8px rgba(16, 185, 129, 0.3));
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.15);
  }
}

/* Input de código */
.form-control-lg {
  border: 2px solid #d1d5db;
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 1.5rem;
  padding: 18px;
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
}

.form-control-lg:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
  background: white;
  transform: scale(1.02);
}

.form-control-lg:hover {
  border-color: #10b981;
}

/* Alerta de éxito */
.alert-success {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  border: 2px solid #10b981;
  border-radius: 12px;
  color: #065f46;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
  animation: slideIn 0.4s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Card de error mejorado */
.cupon-error-card {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border: 2px solid #ef4444;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
  animation: slideIn 0.4s ease-out, shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.error-icon {
  font-size: 42px;
  color: #ef4444;
  flex-shrink: 0;
  animation: errorPulse 1.5s ease-in-out infinite;
}

@keyframes errorPulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.8; }
}

.error-content {
  flex: 1;
}

.error-title {
  color: #991b1b;
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 8px;
}

.error-message {
  color: #b91c1c;
  font-size: 0.95rem;
  margin: 0;
  line-height: 1.5;
}

/* Botones mejorados */
.modal-footer {
  border-top: 2px solid #e5e7eb;
  padding: 20px 24px;
  background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
}

.btn {
  padding: 12px 28px;
  font-size: 1.05rem;
  font-weight: 600;
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-secondary {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
}

.btn-secondary:hover {
  background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(107, 114, 128, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  padding: 14px 32px;
}

.btn-success:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn:active {
  transform: translateY(0);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Spinner de carga */
.fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Text muted mejorado */
.text-muted {
  color: #6b7280 !important;
  font-size: 1rem;
}

/* Label mejorado */
label {
  font-weight: 600;
  color: #374151;
  font-size: 1rem;
  margin-bottom: 8px;
}

/* Modal body */
.modal-body {
  padding: 28px;
}
</style>
  color: #721c24;
  font-weight: bold;
  font-size: 16px;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.error-title::before {
  content: "⚠️";
  font-size: 18px;
}

.error-message {
  color: #721c24;
  font-size: 14px;
  line-height: 1.6;
  margin: 0;
  white-space: pre-line;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-5deg); }
  75% { transform: rotate(5deg); }
}
</style>
