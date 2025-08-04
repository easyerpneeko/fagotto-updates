<template>
  <div v-if="showModal" class="modal-overlay" @click="closeModal">
    <div class="modal-dialog modal-lg" @click.stop>
      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-motorcycle"></i>
            Configurar Uber Eats
          </h5>
          <button type="button" class="btn-close" @click="closeModal">×</button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <!-- Estado actual -->
          <div v-if="connectionStatus" class="status-card" :class="getStatusClass()">
            <div class="status-content">
              <div class="status-icon">
                <i :class="getStatusIcon()"></i>
              </div>
              <div class="status-text">
                <h6>{{ getStatusTitle() }}</h6>
                <p>{{ getStatusMessage() }}</p>
              </div>
            </div>
          </div>

          <!-- Tutorial simple -->
          <div class="info-section">
            <h6><i class="fas fa-info-circle"></i> ¿Cómo obtener tu Store UUID?</h6>
            <div class="info-content">
              <p>Para conectar tu restaurante con Uber Eats necesitas tu <strong>Store UUID</strong>.</p>
              <p>Este código lo puedes obtener:</p>
              <ul>
                <li>Contactando directamente a Uber Eats</li>
                <li>Desde tu panel de restaurante en Uber for Business</li>
                <li>Pidiendo apoyo técnico a tu representante de Uber Eats</li>
              </ul>
              <div class="example-box">
                <strong>Ejemplo:</strong> e244a540-4071-56ac-875d-c0fc02aed530
              </div>
            </div>
          </div>

          <!-- Formulario simple -->
          <div class="config-form">
            <form @submit.prevent="testConnection">
              <div class="form-group">
                <label>Store UUID de Uber Eats *</label>
                <input 
                  type="text" 
                  v-model="formData.store_uuid" 
                  class="form-control"
                  placeholder="e244a540-4071-56ac-875d-c0fc02aed530"
                  pattern="[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}"
                  required
                >
                <small class="form-text">Formato: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</small>
              </div>

              <div class="form-group">
                <label>Nombre del restaurante</label>
                <input 
                  type="text" 
                  v-model="formData.restaurant_name" 
                  class="form-control"
                  placeholder="Fagotto Terminal TurBus"
                >
              </div>

              <div class="form-check">
                <input 
                  type="checkbox" 
                  v-model="formData.enable_notifications" 
                  class="form-check-input"
                  id="enableNotifications"
                >
                <label class="form-check-label" for="enableNotifications">
                  Recibir notificaciones de nuevos pedidos
                </label>
              </div>
            </form>
          </div>

          <!-- Resultados de la prueba -->
          <div v-if="testResults" class="test-results">
            <div class="alert" :class="testResults.success ? 'alert-success' : 'alert-danger'">
              <h6>
                <i :class="testResults.success ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'"></i>
                {{ testResults.title }}
              </h6>
              <p>{{ testResults.message }}</p>
              
              <div v-if="testResults.success && testResults.orders" class="orders-preview">
                <h6>Pedidos encontrados ({{ testResults.orders.length }}):</h6>
                <div class="order-item" v-for="order in testResults.orders.slice(0, 3)" :key="order.id">
                  <strong>#{{ order.display_id || order.id }}</strong> - ${{ formatNumber(order.total) }}
                  <span class="order-status">{{ order.status }}</span>
                </div>
                <p v-if="testResults.orders.length > 3" class="text-muted">
                  ... y {{ testResults.orders.length - 3 }} pedidos más
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" @click="closeModal" class="btn btn-secondary">
            Cancelar
          </button>
          
          <button 
            @click="testConnection" 
            class="btn btn-primary"
            :disabled="loading || !isFormValid"
          >
            <i class="fas fa-satellite-dish" :class="{ 'fa-spin': loading }"></i>
            {{ loading ? 'Probando...' : 'Probar Conexión' }}
          </button>
          
          <button 
            v-if="testResults && testResults.success"
            @click="saveConfiguration" 
            class="btn btn-success"
            :disabled="saving"
          >
            <i class="fas fa-save" :class="{ 'fa-spin': saving }"></i>
            {{ saving ? 'Guardando...' : 'Guardar y Activar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'UberEatsConfigModal',
  props: {
    show: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      showModal: false,
      loading: false,
      saving: false,
      connectionStatus: null,
      testResults: null,
      formData: {
        store_uuid: '',
        restaurant_name: '',
        enable_notifications: true
      }
    }
  },
  computed: {
    isFormValid() {
      const uuidPattern = /^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i
      return this.formData.store_uuid && uuidPattern.test(this.formData.store_uuid)
    }
  },
  watch: {
    show(newVal) {
      this.showModal = newVal
      if (newVal) {
        this.loadCurrentConfig()
      }
    }
  },
  methods: {
    async loadCurrentConfig() {
      try {
        const response = await axios.get('/api/uber-eats/config')
        if (response.data.success && response.data.data) {
          this.connectionStatus = response.data.data
          this.formData.store_uuid = response.data.data.store_uuid || ''
          this.formData.restaurant_name = response.data.data.restaurant_name || ''
          this.formData.enable_notifications = response.data.data.enable_notifications !== false
        }
      } catch (error) {
        console.log('Primera configuración de Uber Eats')
      }
    },

    async testConnection() {
      if (!this.isFormValid) {
        this.$awn.warning('Por favor ingresa un Store UUID válido')
        return
      }

      this.loading = true
      this.testResults = null

      try {
        const response = await axios.post('/api/uber-eats/test-connection', {
          store_uuid: this.formData.store_uuid,
          restaurant_name: this.formData.restaurant_name
        })

        if (response.data.success) {
          this.testResults = {
            success: true,
            title: '¡Conexión exitosa!',
            message: `Se conectó correctamente con ${this.formData.restaurant_name || 'el restaurante'}`,
            orders: response.data.orders || []
          }
          this.$awn.success('¡Conexión exitosa con Uber Eats!')
        } else {
          throw new Error(response.data.message || 'Error en la conexión')
        }
      } catch (error) {
        console.error('Error probando conexión:', error)
        this.testResults = {
          success: false,
          title: 'Error de conexión',
          message: error.response && error.response.data && error.response.data.message 
            ? error.response.data.message 
            : 'No se pudo conectar con Uber Eats. Verifica tu Store UUID.'
        }
        this.$awn.alert('Error de conexión: ' + this.testResults.message)
      } finally {
        this.loading = false
      }
    },

    async saveConfiguration() {
      this.saving = true
      try {
        const response = await axios.post('/api/uber-eats/config', {
          store_uuid: this.formData.store_uuid,
          restaurant_name: this.formData.restaurant_name,
          enable_notifications: this.formData.enable_notifications,
          is_active: true
        })

        if (response.data.success) {
          this.$awn.success('Configuración guardada y activada exitosamente')
          this.connectionStatus = response.data.data
          this.$emit('saved')
        } else {
          throw new Error(response.data.message)
        }
      } catch (error) {
        console.error('Error guardando configuración:', error)
        this.$awn.alert('Error guardando configuración: ' + (error.response && error.response.data && error.response.data.message ? error.response.data.message : error.message))
      } finally {
        this.saving = false
      }
    },

    closeModal() {
      this.showModal = false
      this.testResults = null
      this.$emit('close')
    },

    getStatusClass() {
      if (!this.connectionStatus) return 'status-unknown'
      
      if (this.connectionStatus.is_active && this.connectionStatus.last_test_success) {
        return 'status-success'
      } else if (this.connectionStatus.store_uuid) {
        return 'status-warning'
      }
      
      return 'status-info'
    },

    getStatusIcon() {
      if (!this.connectionStatus) return 'fas fa-question-circle'
      
      if (this.connectionStatus.is_active && this.connectionStatus.last_test_success) {
        return 'fas fa-check-circle'
      } else if (this.connectionStatus.store_uuid) {
        return 'fas fa-clock'
      }
      
      return 'fas fa-info-circle'
    },

    getStatusTitle() {
      if (!this.connectionStatus) return 'Sin configurar'
      
      if (this.connectionStatus.is_active && this.connectionStatus.last_test_success) {
        return 'Activo y funcionando'
      } else if (this.connectionStatus.store_uuid) {
        return 'Configurado pero inactivo'
      }
      
      return 'Sin configurar'
    },

    getStatusMessage() {
      if (!this.connectionStatus) return 'Configura tu Store UUID para comenzar'
      
      if (this.connectionStatus.is_active && this.connectionStatus.last_test_success) {
        return `Conectado correctamente a ${this.connectionStatus.restaurant_name || 'Uber Eats'}`
      } else if (this.connectionStatus.store_uuid) {
        return 'Store UUID configurado, prueba la conexión para activar'
      }
      
      return 'Configura tu Store UUID para comenzar'
    },

    formatNumber(number) {
      return new Intl.NumberFormat('es-CL').format(number)
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.modal-dialog {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  border-bottom: 1px solid #e2e8f0;
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 20px;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-close {
  background: transparent;
  border: none;
  font-size: 24px;
  color: #718096;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f7fafc;
  color: #2d3748;
}

.modal-body {
  padding: 24px;
}

/* Status Card */
.status-card {
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 24px;
  border-left: 4px solid;
}

.status-card.status-success {
  background: #f0fff4;
  border-left-color: #38a169;
}

.status-card.status-warning {
  background: #fffbeb;
  border-left-color: #ed8936;
}

.status-card.status-info {
  background: #ebf8ff;
  border-left-color: #4299e1;
}

.status-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-icon {
  font-size: 20px;
}

.status-text h6 {
  margin: 0;
  font-weight: 600;
}

.status-text p {
  margin: 4px 0 0 0;
  font-size: 14px;
}

/* Info Section */
.info-section {
  margin-bottom: 24px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
}

.info-section h6 {
  margin: 0 0 12px 0;
  color: #2d3748;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-content ul {
  margin: 8px 0;
  padding-left: 20px;
}

.example-box {
  background: #e2e8f0;
  padding: 8px 12px;
  border-radius: 6px;
  font-family: monospace;
  margin-top: 8px;
}

/* Form */
.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
  color: #4a5568;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
}

.form-control:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.form-text {
  font-size: 12px;
  color: #718096;
  margin-top: 4px;
}

.form-check {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
}

/* Test Results */
.test-results {
  margin-top: 20px;
}

.alert {
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.alert-success {
  background: #f0fff4;
  border: 1px solid #9ae6b4;
  color: #22543d;
}

.alert-danger {
  background: #fed7d7;
  border: 1px solid #feb2b2;
  color: #742a2a;
}

.orders-preview {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(255,255,255,0.2);
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
  font-size: 14px;
}

.order-status {
  background: rgba(255,255,255,0.3);
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
}

/* Modal Footer */
.modal-footer {
  border-top: 1px solid #e2e8f0;
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

/* Buttons */
.btn {
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-primary {
  background: #4299e1;
  color: white;
}

.btn-primary:hover {
  background: #3182ce;
}

.btn-success {
  background: #38a169;
  color: white;
}

.btn-success:hover {
  background: #2f855a;
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #cbd5e0;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
