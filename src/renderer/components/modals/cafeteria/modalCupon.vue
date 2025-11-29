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
        
        // Llamada a la API para validar el cupón usando Connection.fetch()
        const url = 'https://posfagotto.cl/api/cupones/validar';
        const response = await Connection.fetch(url, 'POST', { codigo }, null, false);

        console.log('🎫 Response completa del backend:', response);
        console.log('🎫 Response.data:', JSON.stringify(response.data, null, 2));

        if (response && response.ok && response.data) {
          if (response.data.valido) {
            const cupon = response.data.cupon;
            
            this.descuento = cupon.valor_descuento;
            this.cuponValidado = true;
            
            const textoDescuento = cupon.tipo_descuento === 'porcentaje' 
              ? `${this.descuento}% de descuento` 
              : `$${this.descuento} de descuento`;
            
            this.$awn.success(`Cupón "${codigo}" válido: ${textoDescuento}`);
            
            // NO marcar como usado aquí, se marcará al finalizar la venta
            
            // Cerrar este modal y abrir el modal de selección
            setTimeout(() => {
              this.closeModal();
              this.$emit('abrir-seleccion', {
                codigo: codigo,
                descuento: this.descuento,
                tipo_descuento: cupon.tipo_descuento,
                cupon_id: cupon.id
              });
            }, 800);
          } else {
            console.log('❌ Cupón inválido - Mensaje del backend:', response.data.mensaje);
            this.errorMensaje = response.data.mensaje || 'Cupón inválido o expirado';
          }
        } else {
          console.error('❌ Response NO OK:', response);
          const mensaje = (response && response.data && response.data.mensaje) || 'Error de conexión con el servidor';
          console.log('❌ Mensaje de error extraído:', mensaje);
          this.errorMensaje = mensaje;
        }
      } catch (error) {
        console.error('Error al validar cupón:', error);
        this.errorMensaje = 'Error de conexión. Verifica que el servidor esté activo.';
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
.cupon-icon {
  font-size: 64px;
  color: #28a745;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.modal-content {
  border-radius: 15px;
  border: none;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
  border-bottom: none;
}

.form-control:focus {
  border-color: #28a745;
  box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

/* Card de error mejorado */
.cupon-error-card {
  background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
  border: 2px solid #dc3545;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  box-shadow: 0 4px 15px rgba(220, 53, 69, 0.15);
  animation: slideIn 0.3s ease-out;
}

.error-icon {
  font-size: 42px;
  color: #dc3545;
  flex-shrink: 0;
  animation: shake 0.5s ease-in-out;
}

.error-content {
  flex: 1;
}

.error-title {
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
