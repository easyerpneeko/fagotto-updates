<template>
  <div class="modal fade show" style="display: block;" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">
            <i class="fas fa-ban me-2"></i>
            Generar Nota de Crédito
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="cerrar"></button>
        </div>
        
        <div class="modal-body">
          <!-- Información de la Factura -->
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>¡Atención!</strong> Esta acción generará una Nota de Crédito que anulará la factura original.
            Esta operación no se puede deshacer.
          </div>

          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0">Información de la Factura a Cancelar</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <strong>Folio:</strong> {{ factura.folio }}
                </div>
                <div class="col-md-4">
                  <strong>Fecha:</strong> {{ formatDate(factura.fecha) }}
                </div>
                <div class="col-md-3">
                  <strong>Total:</strong> ${{ formatNumber(factura.total) }}
                </div>
                <div class="col-md-2">
                  <span class="badge bg-success">VIGENTE</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Formulario para Nota de Crédito -->
          <div class="card">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0">Datos de la Nota de Crédito</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Fecha de Cancelación:</label>
                  <input 
                    type="date" 
                    class="form-control" 
                    v-model="fechaCancelacion"
                    :max="fechaMaxima"
                    required
                  >
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Motivo de Cancelación:</label>
                  <select class="form-select" v-model="motivoCancelacion">
                    <option value="">Seleccionar motivo...</option>
                    <option value="error_emision">Error en emisión</option>
                    <option value="devolucion">Devolución de productos</option>
                    <option value="anulacion_cliente">Anulación solicitada por cliente</option>
                    <option value="error_datos">Error en datos del cliente</option>
                    <option value="otro">Otro motivo</option>
                  </select>
                </div>
              </div>

              <div class="row" v-if="motivoCancelacion === 'otro'">
                <div class="col-12 mb-3">
                  <label class="form-label">Especifica el motivo:</label>
                  <textarea 
                    class="form-control" 
                    v-model="motivoPersonalizado"
                    rows="3"
                    placeholder="Describe el motivo de la cancelación..."
                  ></textarea>
                </div>
              </div>

              <!-- Información adicional -->
              <div class="row">
                <div class="col-12">
                  <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Información:</strong> Se generará una Nota de Crédito con el mismo monto de la factura original.
                    El folio de la Nota de Crédito será asignado automáticamente.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="cerrar">
            <i class="fas fa-times me-1"></i>
            Cancelar
          </button>
          <button 
            type="button" 
            class="btn btn-danger" 
            @click="generarNotaCredito"
            :disabled="!fechaCancelacion || processing"
          >
            <span v-if="processing">
              <i class="fas fa-spinner fa-spin me-1"></i>
              Procesando...
            </span>
            <span v-else>
              <i class="fas fa-ban me-1"></i>
              Generar Nota de Crédito
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';
import FormatNumber from '@/helpers/FormatNumber.js';

export default {
  name: 'ModalNotaCredito',
  props: {
    factura: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      fechaCancelacion: '',
      motivoCancelacion: '',
      motivoPersonalizado: '',
      processing: false,
      fechaMaxima: new Date().toISOString().split('T')[0]
    };
  },
  mounted() {
    // Establecer fecha actual por defecto
    this.fechaCancelacion = this.fechaMaxima;
  },
  methods: {
    async generarNotaCredito() {
      if (!this.fechaCancelacion) {
        this.$awn.alert('Debes seleccionar una fecha de cancelación');
        return;
      }

      // Confirmar la acción
      const confirmacion = confirm(
        `¿Estás seguro de que deseas cancelar la factura ${this.factura.folio}?\n\n` +
        `Se generará una Nota de Crédito por $${this.formatNumber(this.factura.total)}`
      );

      if (!confirmacion) return;

      this.processing = true;
      try {
        const url = BaseUrl.getUrl(`api/local/sell/nota_de_credito/factura/${this.factura.sell_id}`);
        const response = await Connection.request('post', url, {
          cancelDate: this.fechaCancelacion,
          motivo: this.motivoCancelacion,
          motivo_personalizado: this.motivoPersonalizado
        });

        if (response.success) {
          this.$awn.success('Nota de Crédito generada exitosamente');
          
          // Emitir evento al componente padre
          this.$emit('notaCreditoGenerada', {
            ...this.factura,
            cancelada: true,
            nota_credito: response.data
          });
          
          // Si viene PDF en la respuesta, abrirlo
          if (response.data && response.data.pdf) {
            this.abrirPDF(response.data.pdf);
          }
          
        } else {
          this.$awn.alert('Error: ' + (response.message || 'No se pudo generar la nota de crédito'));
        }
      } catch (error) {
        console.error('Error al generar nota de crédito:', error);
        this.$awn.alert('Error de conexión al generar la nota de crédito');
      } finally {
        this.processing = false;
      }
    },

    abrirPDF(pdfBase64) {
      try {
        const blob = new Blob([Uint8Array.from(atob(pdfBase64), c => c.charCodeAt(0))], {
          type: 'application/pdf'
        });
        const url = URL.createObjectURL(blob);
        window.open(url, '_blank');
      } catch (error) {
        console.error('Error al abrir PDF:', error);
      }
    },

    cerrar() {
      this.$emit('cerrar');
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('es-CL');
    },

    formatNumber(number) {
      return FormatNumber.formatNumber(number);
    }
  }
};
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.required::after {
  content: " *";
  color: red;
}

.alert {
  border-left: 4px solid;
}

.alert-warning {
  border-left-color: #ffc107;
}

.alert-info {
  border-left-color: #0dcaf0;
}
</style>
