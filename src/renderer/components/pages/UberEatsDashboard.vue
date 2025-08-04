<template>
  <div class="uber-eats-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <div class="title-section">
          <h2><i class="fas fa-motorcycle"></i> Uber Eats Dashboard</h2>
          <p class="subtitle">Gestiona tus pedidos de Uber Eats</p>
        </div>
        <div class="status-section">
          <div class="status-indicator" :class="connectionStatus.class">
            <div class="status-dot"></div>
            <span>{{ connectionStatus.text }}</span>
          </div>
          <div class="header-actions">
            <button 
              v-if="canConfigureUberEats" 
              @click="openConfigModal" 
              class="btn btn-config"
            >
              <i class="fas fa-cog"></i>
              Configurar API
            </button>
            <button @click="refreshOrders" class="btn btn-refresh" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Actualizar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.pending }}</h3>
          <p>Pedidos Pendientes</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon processing">
          <i class="fas fa-utensils"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.processing }}</h3>
          <p>En Preparación</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon ready">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.ready }}</h3>
          <p>Listos</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon today">
          <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-content">
          <h3>{{ stats.today }}</h3>
          <p>Hoy</p>
        </div>
      </div>
    </div>

    <!-- Orders Section -->
    <div class="orders-section">
      <div class="section-header">
        <h3><i class="fas fa-list"></i> Pedidos Activos</h3>
        <div class="filters">
          <!-- Selector de Local (solo para managers) -->
          <select v-if="canConfigureUberEats" v-model="selectedStore" @change="onStoreChange" class="form-select mr-3">
            <option value="">Todos los locales</option>
            <option v-for="store in stores" :key="store.id" :value="store.id">
              {{ store.name }}
            </option>
          </select>
          
          <!-- Filtro de Estado -->
          <select v-model="statusFilter" class="form-select">
            <option value="">Todos los estados</option>
            <option value="created">Nuevos</option>
            <option value="accepted">Aceptados</option>
            <option value="in_progress">En progreso</option>
            <option value="ready_for_pickup">Listos</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && orders.length === 0" class="loading-state">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Cargando pedidos...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading && filteredOrders.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h4>No hay pedidos</h4>
        <p>No hay pedidos activos en este momento</p>
        <button @click="refreshOrders" class="btn btn-primary">
          <i class="fas fa-sync-alt"></i>
          Actualizar pedidos
        </button>
      </div>

      <!-- Orders Grid -->
      <div v-else class="orders-grid">
        <div v-for="order in filteredOrders" :key="order.id" class="order-card" :class="getOrderStatusClass(order.status)">
          <!-- Order Header -->
          <div class="order-header">
            <div class="order-info">
              <h4 class="order-id">#{{ order.display_id || order.id }}</h4>
              <div class="order-time">
                <i class="fas fa-clock"></i>
                {{ formatTime(order.created_at) }}
              </div>
            </div>
            <div class="order-status">
              <span class="status-badge" :class="getOrderStatusClass(order.status)">
                {{ getStatusText(order.status) }}
              </span>
            </div>
          </div>

          <!-- Customer Info -->
          <div class="customer-info">
            <div class="customer-details">
              <i class="fas fa-user"></i>
              <span>{{ order.customer_name }}</span>
            </div>
            <div v-if="order.customer_phone" class="customer-phone">
              <i class="fas fa-phone"></i>
              <span>{{ order.customer_phone }}</span>
            </div>
          </div>

          <!-- Order Items -->
          <div class="order-items">
            <h5><i class="fas fa-utensils"></i> Productos ({{ order.items.length }})</h5>
            <div class="items-list">
              <div v-for="item in order.items" :key="item.id" class="item-row">
                <div class="item-info">
                  <span class="item-quantity">{{ item.quantity }}x</span>
                  <span class="item-name">{{ item.name }}</span>
                </div>
                <div class="item-price">${{ formatNumber(item.price) }}</div>
              </div>
            </div>
          </div>

          <!-- Total -->
          <div class="order-total">
            <div class="total-row">
              <span>TOTAL</span>
              <span class="total-amount">${{ formatNumber(order.total_amount) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="order-actions">
            <button v-if="order.status === 'created' && canManageOrderStatus" 
              @click="acceptOrder(order)" 
              class="btn btn-success"
              :disabled="processingOrder === order.id">
              <i class="fas fa-check"></i>
              Aceptar Pedido
            </button>
            <button v-if="order.status === 'accepted' && canManageOrderStatus" 
              @click="startPreparation(order)" 
              class="btn btn-warning"
              :disabled="processingOrder === order.id">
              <i class="fas fa-play"></i>
              Iniciar Preparación
            </button>
            <button v-if="order.status === 'in_progress' && canManageOrderStatus" 
              @click="markReady(order)" 
              class="btn btn-info"
              :disabled="processingOrder === order.id">
              <i class="fas fa-check-circle"></i>
              Marcar Listo
            </button>
            <button v-if="canPrintOrders" 
              @click="createTicketFromOrder(order)" 
              class="btn btn-primary"
              :disabled="processingOrder === order.id">
              <i class="fas fa-receipt"></i>
              Crear Ticket
            </button>
            <button v-if="canViewOrders" 
              @click="viewOrderDetails(order)" 
              class="btn btn-outline">
              <i class="fas fa-eye"></i>
              Ver Detalles
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="modal-overlay" @click="closeOrderDetails">
      <div class="modal-dialog modal-lg" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalles del Pedido #{{ selectedOrder.display_id }}</h5>
            <button type="button" class="btn-close" @click="closeOrderDetails">×</button>
          </div>
          <div class="modal-body">
            <div class="order-details">
              <!-- Customer Details -->
              <div class="detail-section">
                <h6><i class="fas fa-user"></i> Cliente</h6>
                <p><strong>{{ selectedOrder.customer_name }}</strong></p>
                <p v-if="selectedOrder.customer_phone">
                  <i class="fas fa-phone"></i> {{ selectedOrder.customer_phone }}
                </p>
              </div>

              <!-- Delivery Info -->
              <div v-if="selectedOrder.delivery_info" class="detail-section">
                <h6><i class="fas fa-map-marker-alt"></i> Entrega</h6>
                <p v-if="selectedOrder.delivery_info.address">
                  {{ selectedOrder.delivery_info.address }}
                </p>
                <p v-if="selectedOrder.delivery_info.notes">
                  <strong>Notas:</strong> {{ selectedOrder.delivery_info.notes }}
                </p>
              </div>

              <!-- Items -->
              <div class="detail-section">
                <h6><i class="fas fa-list"></i> Productos</h6>
                <div class="items-detail">
                  <div v-for="item in selectedOrder.items" :key="item.id" class="item-detail">
                    <div class="item-header">
                      <span class="quantity">{{ item.quantity }}x</span>
                      <span class="name">{{ item.name }}</span>
                      <span class="price">${{ formatNumber(item.price) }}</span>
                    </div>
                    <div v-if="item.special_instructions" class="item-instructions">
                      <i class="fas fa-comment"></i>
                      {{ item.special_instructions }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeOrderDetails">
              Cerrar
            </button>
            <button type="button" class="btn btn-primary" @click="createTicketFromOrder(selectedOrder)">
              <i class="fas fa-receipt"></i>
              Crear Ticket
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Configuración Uber Eats -->
    <UberEatsConfigModal 
      :show="showConfigModal"
      :franchise-id="selectedStore || 'default'"
      :franchise-name="getStoreName(selectedStore)"
      @close="closeConfigModal"
    />
  </div>
</template>

<script>
import axios from 'axios'
import FormatNumber from '@/helpers/FormatNumber.js'
import ConfigHelper from '@/helpers/ConfigHelper.js'
import UberEatsConfigModal from '@/components/modals/UberEatsConfigModal.vue'

export default {
  name: 'UberEatsDashboard',
  components: {
    UberEatsConfigModal
  },
  data() {
    return {
      orders: [],
      loading: false,
      statusFilter: '',
      selectedStore: '',
      selectedOrder: null,
      processingOrder: null,
      showConfigModal: false,
      stores: [
        { id: 'fagotto-agustinas', name: 'Fagotto Agustinas' },
        { id: 'fagotto-providencia', name: 'Fagotto Providencia' },
        { id: 'fagotto-las-condes', name: 'Fagotto Las Condes' },
        // Agregar los 22 locales aquí
      ],
      connectionStatus: {
        class: 'connected',
        text: 'Conectado'
      },
      pollInterval: null
    }
  },
  computed: {
    filteredOrders() {
      let filtered = this.orders
      
      // Filtrar por local
      if (this.selectedStore) {
        filtered = filtered.filter(order => order.store_id === this.selectedStore)
      }
      
      // Filtrar por estado
      if (this.statusFilter) {
        filtered = filtered.filter(order => order.status === this.statusFilter)
      }
      
      return filtered
    },
    stats() {
      const filtered = this.filteredOrders
      return {
        pending: filtered.filter(o => o.status === 'created').length,
        processing: filtered.filter(o => o.status === 'in_progress').length,
        ready: filtered.filter(o => o.status === 'ready_for_pickup').length,
        today: filtered.length
      }
    },
    
    // Permisos para Uber Eats
    canConfigureUberEats() {
      return ConfigHelper.HavePermission('uber_eats_configurar') || 
             ConfigHelper.HavePermission('uber_eats_gestionar') || 
             this.isAdmin
    },
    canManageOrderStatus() {
      return ConfigHelper.HavePermission('uber_eats_actualizar_estado') || 
             ConfigHelper.HavePermission('uber_eats_gestionar') || 
             this.isAdmin
    },
    canViewOrders() {
      return ConfigHelper.HavePermission('uber_eats_ver_pedidos') || 
             ConfigHelper.HavePermission('uber_eats_gestionar') || 
             this.isAdmin
    },
    canPrintOrders() {
      return ConfigHelper.HavePermission('uber_eats_imprimir') || 
             ConfigHelper.HavePermission('uber_eats_gestionar') || 
             this.isAdmin
    },
    canViewReports() {
      return ConfigHelper.HavePermission('uber_eats_reportes') || 
             ConfigHelper.HavePermission('uber_eats_gestionar') || 
             this.isAdmin
    },
    isAdmin() {
      return this.$store.getters['main/user'].role == 1
    }
  },
  mounted() {
    // Verificar permisos de acceso
    if (!this.canViewOrders) {
      this.$awn.alert('No tienes permisos para acceder a Uber Eats')
      this.$router.push('/inicio')
      return
    }
    
    this.refreshOrders()
    this.startPolling()
  },
  beforeDestroy() {
    this.stopPolling()
  },
  methods: {
    async refreshOrders() {
      this.loading = true
      try {
        // Si hay un local seleccionado, incluirlo en la petición
        const params = this.selectedStore ? { store_id: this.selectedStore } : {}
        const response = await axios.get('/api/uber-eats/orders', { params })
        
        if (response.data.success) {
          this.orders = response.data.data
          this.connectionStatus = {
            class: 'connected',
            text: 'Conectado'
          }
        } else {
          throw new Error(response.data.message || 'Error al cargar pedidos')
        }
      } catch (error) {
        console.error('Error al cargar pedidos:', error)
        this.connectionStatus = {
          class: 'disconnected',
          text: 'Error de conexión'
        }
        this.$awn.alert('Error al cargar pedidos de Uber Eats')
      } finally {
        this.loading = false
      }
    },

    onStoreChange() {
      // Actualizar pedidos cuando cambie el local
      this.refreshOrders()
    },

    async acceptOrder(order) {
      this.processingOrder = order.id
      try {
        const response = await axios.post(`/api/uber-eats/orders/${order.id}/accept`, {
          ready_for_pickup_time: new Date(Date.now() + 15 * 60000).toISOString(), // 15 minutos
          external_id: `FAGOTTO-${order.display_id}`
        })

        if (response.data.success) {
          this.$awn.success('Pedido aceptado exitosamente')
          await this.refreshOrders()
        } else {
          throw new Error(response.data.message)
        }
      } catch (error) {
        console.error('Error al aceptar pedido:', error)
        this.$awn.alert('Error al aceptar el pedido')
      } finally {
        this.processingOrder = null
      }
    },

    async startPreparation(order) {
      this.processingOrder = order.id
      try {
        const response = await axios.post(`/api/uber-eats/orders/${order.id}/start-preparation`)

        if (response.data.success) {
          this.$awn.success('Preparación iniciada')
          await this.refreshOrders()
        } else {
          throw new Error(response.data.message)
        }
      } catch (error) {
        console.error('Error al iniciar preparación:', error)
        this.$awn.alert('Error al iniciar la preparación')
      } finally {
        this.processingOrder = null
      }
    },

    async markReady(order) {
      this.processingOrder = order.id
      try {
        const response = await axios.post(`/api/uber-eats/orders/${order.id}/mark-ready`)

        if (response.data.success) {
          this.$awn.success('Pedido marcado como listo')
          await this.refreshOrders()
        } else {
          throw new Error(response.data.message)
        }
      } catch (error) {
        console.error('Error al marcar como listo:', error)
        this.$awn.alert('Error al marcar el pedido como listo')
      } finally {
        this.processingOrder = null
      }
    },

    createTicketFromOrder(order) {
      // Convertir orden de Uber Eats a formato de ticket
      const ticketData = {
        customer_name: order.customer_name,
        customer_phone: order.customer_phone,
        delivery_address: order.delivery_info && order.delivery_info.address,
        delivery_notes: order.delivery_info && order.delivery_info.notes,
        uber_order_id: order.id,
        uber_display_id: order.display_id,
        payment_method: 'uber',
        payment_description: `Uber Eats - Pedido #${order.display_id}`,
        items: order.items.map(item => ({
          name: item.name,
          quantity: item.quantity,
          price: item.price,
          special_instructions: item.special_instructions
        })),
        total: order.total_amount,
        source: 'uber_eats'
      }

      // Emitir evento para abrir el modal de ticket con los datos
      this.$emit('create-ticket', ticketData)
      
      // Mostrar confirmación
      this.$awn.success('Datos del pedido transferidos al sistema de tickets')
    },

    viewOrderDetails(order) {
      this.selectedOrder = order
      // Usar v-if en lugar de modal de Bootstrap para mejor compatibilidad
      // $('#orderDetailsModal').modal('show')
    },

    closeOrderDetails() {
      this.selectedOrder = null
      // $('#orderDetailsModal').modal('hide')
    },

    startPolling() {
      // Actualizar cada 30 segundos
      this.pollInterval = setInterval(() => {
        this.refreshOrders()
      }, 30000)
    },

    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval)
        this.pollInterval = null
      }
    },

    getOrderStatusClass(status) {
      const statusClasses = {
        'created': 'status-new',
        'accepted': 'status-accepted',
        'in_progress': 'status-processing',
        'ready_for_pickup': 'status-ready',
        'picked_up': 'status-completed',
        'delivered': 'status-completed',
        'cancelled': 'status-cancelled'
      }
      return statusClasses[status] || 'status-unknown'
    },

    getStatusText(status) {
      const statusTexts = {
        'created': 'Nuevo',
        'accepted': 'Aceptado',
        'in_progress': 'En preparación',
        'ready_for_pickup': 'Listo para recoger',
        'picked_up': 'Recogido',
        'delivered': 'Entregado',
        'cancelled': 'Cancelado'
      }
      return statusTexts[status] || status
    },

    formatTime(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleTimeString('es-CL', { 
        hour: '2-digit', 
        minute: '2-digit' 
      })
    },

    formatNumber(number) {
      return FormatNumber.format(number)
    },

    openConfigModal() {
      this.showConfigModal = true
    },

    closeConfigModal() {
      this.showConfigModal = false
      // Recargar datos después de cerrar el modal por si hubo cambios
      this.refreshOrders()
    },

    getStoreName(storeId) {
      if (!storeId) return 'Mi Restaurante'
      const store = this.stores.find(s => s.id === storeId)
      return store ? store.name : 'Mi Restaurante'
    }
  }
}
</script>

<style scoped>
.uber-eats-dashboard {
  padding: 20px;
  background: #f8f9fa;
  min-height: 100vh;
}

/* Header */
.dashboard-header {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  margin-bottom: 24px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title-section h2 {
  color: #2d3748;
  margin: 0;
  font-size: 28px;
  font-weight: 600;
}

.title-section .subtitle {
  color: #718096;
  margin: 4px 0 0 0;
}

.status-section {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
}

.status-indicator.connected {
  background: #f0fff4;
  color: #38a169;
}

.status-indicator.disconnected {
  background: #fed7d7;
  color: #e53e3e;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.btn-config {
  background: #805ad5;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-config:hover {
  background: #6b46c1;
}

.btn-refresh {
  background: #4299e1;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-refresh:hover {
  background: #3182ce;
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: white;
}

.stat-icon.pending { background: #ed8936; }
.stat-icon.processing { background: #4299e1; }
.stat-icon.ready { background: #38a169; }
.stat-icon.today { background: #805ad5; }

.stat-content h3 {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
  color: #2d3748;
}

.stat-content p {
  margin: 4px 0 0 0;
  color: #718096;
  font-size: 14px;
}

/* Orders Section */
.orders-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
  overflow: hidden;
}

.section-header {
  padding: 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-header h3 {
  margin: 0;
  color: #2d3748;
  font-size: 20px;
  font-weight: 600;
}

.form-select {
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  padding: 48px 24px;
  text-align: center;
  color: #718096;
}

.loading-spinner {
  font-size: 24px;
  margin-bottom: 16px;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  color: #cbd5e0;
}

/* Orders Grid */
.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
  padding: 24px;
}

.order-card {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  background: white;
  transition: all 0.2s;
}

.order-card:hover {
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.order-card.status-new {
  border-left-color: #ed8936;
  border-left-width: 4px;
}

.order-card.status-accepted {
  border-left-color: #4299e1;
  border-left-width: 4px;
}

.order-card.status-processing {
  border-left-color: #9f7aea;
  border-left-width: 4px;
}

.order-card.status-ready {
  border-left-color: #38a169;
  border-left-width: 4px;
}

/* Order Card Content */
.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.order-id {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #2d3748;
}

.order-time {
  display: flex;
  align-items: center;
  gap: 4px;
  color: #718096;
  font-size: 14px;
  margin-top: 4px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.status-new {
  background: #fed7ab;
  color: #c05621;
}

.status-badge.status-accepted {
  background: #bee3f8;
  color: #2c5282;
}

.status-badge.status-processing {
  background: #e9d8fd;
  color: #553c9a;
}

.status-badge.status-ready {
  background: #c6f6d5;
  color: #22543d;
}

.customer-info {
  margin-bottom: 16px;
  padding: 12px;
  background: #f7fafc;
  border-radius: 8px;
}

.customer-details,
.customer-phone {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}

.customer-phone {
  margin-bottom: 0;
  font-size: 14px;
  color: #718096;
}

.order-items {
  margin-bottom: 16px;
}

.order-items h5 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #4a5568;
  display: flex;
  align-items: center;
  gap: 8px;
}



.item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f1f5f9;
}

.item-row:last-child {
  border-bottom: none;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.item-quantity {
  font-weight: 600;
  color: #4a5568;
  min-width: 24px;
}

.item-name {
  color: #2d3748;
}

.item-price {
  font-weight: 600;
  color: #2d3748;
}

.order-total {
  margin-bottom: 16px;
  padding: 12px;
  background: #f7fafc;
  border-radius: 8px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  color: #2d3748;
}

.total-amount {
  font-size: 18px;
  color: #38a169;
}

/* Order Actions */
.order-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.order-actions .btn {
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

.btn-success {
  background: #38a169;
  color: white;
}

.btn-success:hover {
  background: #2f855a;
}

.btn-warning {
  background: #ed8936;
  color: white;
}

.btn-warning:hover {
  background: #dd6b20;
}

.btn-info {
  background: #4299e1;
  color: white;
}

.btn-info:hover {
  background: #3182ce;
}

.btn-primary {
  background: #805ad5;
  color: white;
}

.btn-primary:hover {
  background: #6b46c1;
}

.btn-outline {
  background: transparent;
  color: #4a5568;
  border: 1px solid #e2e8f0;
}

.btn-outline:hover {
  background: #f7fafc;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Modal Styles */
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
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content {
  border-radius: 12px;
  border: none;
  box-shadow: none;
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

.detail-section {
  margin-bottom: 24px;
}

.detail-section h6 {
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 600;
  color: #2d3748;
  display: flex;
  align-items: center;
  gap: 8px;
}

.items-detail > .item-detail:not(:last-child) {
  margin-bottom: 12px;
}

.item-detail {
  padding: 12px;
  background: #f7fafc;
  border-radius: 8px;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-instructions {
  margin-top: 8px;
  font-size: 14px;
  color: #718096;
  display: flex;
  align-items: center;
  gap: 6px;
}

.modal-footer {
  border-top: 1px solid #e2e8f0;
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
</style>
