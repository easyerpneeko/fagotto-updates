/**
 * Helper para gestionar la configuración y conexión con Uber Eats API
 * Actualizado para usar la nueva implementación con UberEatsService
 */

import axios from 'axios'
import baseUrl from './baseUrl'

export default {
  
  /**
   * Verificar si la conexión con Uber Eats está configurada
   */
  async isConfigured() {
    try {
      const response = await axios.get(`${baseUrl}/api/uber-eats/test-connection`)
      return response.data.success || false
    } catch (error) {
      console.error('Error checking Uber Eats configuration:', error)
      return false
    }
  },

  /**
   * Obtener pedidos pendientes de Uber Eats
   */
  async getPendingOrders() {
    try {
      const response = await axios.get(`${baseUrl}/api/uber-eats/orders`)
      if (response.data.success && response.data.data && response.data.data.orders) {
        // Filtrar solo pedidos pendientes (estado OFFERED)
        return response.data.data.orders.filter(order => 
          order.current_state === 'OFFERED' || order.current_state === 'CREATED'
        )
      }
      return []
    } catch (error) {
      console.error('Error getting pending orders:', error)
      return []
    }
  },

  /**
   * Obtener pedidos activos de Uber Eats
   */
  async getActiveOrders() {
    try {
      const response = await axios.get(`${baseUrl}/api/uber-eats/orders`)
      if (response.data.success && response.data.data && response.data.data.orders) {
        // Filtrar pedidos activos (aceptados, en preparación, listos)
        return response.data.data.orders.filter(order => 
          ['ACCEPTED', 'PREPARING', 'READY_FOR_HANDOFF'].includes(order.current_state)
        )
      }
      return []
    } catch (error) {
      console.error('Error getting active orders:', error)
      return []
    }
  },

  /**
   * Aceptar un pedido de Uber Eats
   */
  async acceptOrder(orderId, options = {}) {
    try {
      const data = {
        accepted_by: options.acceptedBy || 'Fagotto POS',
        pickup_time: options.pickupTime || null,
        external_id: options.externalId || null
      }
      
      const response = await axios.post(`${baseUrl}/api/uber-eats/orders/${orderId}/accept`, data)
      return response.data
    } catch (error) {
      console.error('Error accepting order:', error)
      throw error
    }
  },

  /**
   * Rechazar un pedido de Uber Eats
   */
  async rejectOrder(orderId, reason = 'RESTAURANT_TOO_BUSY', reasonInfo = 'Unable to fulfill order') {
    try {
      const data = {
        reason_type: reason,
        reason_info: reasonInfo
      }
      
      const response = await axios.post(`${baseUrl}/api/uber-eats/orders/${orderId}/deny`, data)
      return response.data
    } catch (error) {
      console.error('Error rejecting order:', error)
      throw error
    }
  },

  /**
   * Actualizar estado de un pedido
   */
  async updateOrderStatus(orderId, status) {
    try {
      let response
      
      switch (status) {
        case 'ready_for_pickup':
        case 'ready':
          response = await axios.post(`${baseUrl}/api/uber-eats/orders/${orderId}/ready`)
          break
        case 'update_ready_time':
          response = await axios.post(`${baseUrl}/api/uber-eats/orders/${orderId}/ready-time`, {
            ready_time: new Date(Date.now() + 15 * 60 * 1000).toISOString() // 15 minutos desde ahora
          })
          break
        default:
          // Para otros estados, usar endpoint genérico si existe
          response = await axios.post(`${baseUrl}/api/uber-eats/orders/${orderId}/status`, {
            status: status
          })
      }
      
      return response.data
    } catch (error) {
      console.error('Error updating order status:', error)
      throw error
    }
  },

  /**
   * Cancelar un pedido activo
   */
  async cancelOrder(orderId, reason = 'RESTAURANT_TOO_BUSY', reasonInfo = 'Unable to fulfill order') {
    try {
      const data = {
        reason_type: reason,
        reason_info: reasonInfo
      }
      
      const response = await axios.post(`${baseUrl}/api/uber-eats/orders/${orderId}/cancel`, data)
      return response.data
    } catch (error) {
      console.error('Error canceling order:', error)
      throw error
    }
  },

  /**
   * Formatear precio en formato chileno
   */
  formatPrice(price) {
    const amount = price / 100 // Uber Eats maneja precios en centavos
    return amount.toLocaleString('es-CL', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    })
  },

  /**
   * Formatear tiempo relativo
   */
  formatTimeAgo(timestamp) {
    const now = new Date()
    const orderTime = new Date(timestamp)
    const diffMs = now - orderTime
    const diffMins = Math.floor(diffMs / (1000 * 60))
    
    if (diffMins < 1) return 'Ahora'
    if (diffMins === 1) return 'Hace 1 minuto'
    if (diffMins < 60) return `Hace ${diffMins} minutos`
    
    const diffHours = Math.floor(diffMins / 60)
    if (diffHours === 1) return 'Hace 1 hora'
    if (diffHours < 24) return `Hace ${diffHours} horas`
    
    return orderTime.toLocaleDateString('es-CL')
  },

  /**
   * Obtener configuración de la tienda
   */
  async getStoreConfig() {
    try {
      const response = await axios.get(`${baseUrl}/api/uber-eats/store/config`)
      return response.data
    } catch (error) {
      console.error('Error getting store config:', error)
      return null
    }
  },

  /**
   * Estados de pedidos con sus colores correspondientes (actualizados para nueva API)
   */
  orderStatuses: {
    'CREATED': { 
      label: 'Nuevo', 
      color: '#ff6b6b',
      bgColor: '#fff5f5'
    },
    'OFFERED': { 
      label: 'Ofrecido', 
      color: '#ff9800',
      bgColor: '#fff8e1'
    },
    'ACCEPTED': { 
      label: 'Aceptado', 
      color: '#4caf50',
      bgColor: '#f1f8e9'
    },
    'PREPARING': { 
      label: 'En Preparación', 
      color: '#ff9800',
      bgColor: '#fff8e1'
    },
    'READY_FOR_HANDOFF': { 
      label: 'Listo para Recoger', 
      color: '#2196f3',
      bgColor: '#e3f2fd'
    },
    'HANDED_OFF': { 
      label: 'Entregado a Repartidor', 
      color: '#9c27b0',
      bgColor: '#fce4ec'
    },
    'SUCCEEDED': { 
      label: 'Entregado', 
      color: '#4caf50',
      bgColor: '#e8f5e8'
    },
    'FAILED': { 
      label: 'Fallido', 
      color: '#f44336',
      bgColor: '#ffebee'
    },
    'UNKNOWN': { 
      label: 'Estado Desconocido', 
      color: '#795548',
      bgColor: '#efebe9'
    }
  },

  /**
   * Obtener información de estado
   */
  getStatusInfo(status) {
    return this.orderStatuses[status] || {
      label: status,
      color: '#666',
      bgColor: '#f5f5f5'
    }
  },

  /**
   * Reproducir sonido de notificación
   */
  playNotificationSound() {
    try {
      const audio = new Audio('/static/notification-sound.mp3')
      audio.volume = 0.7
      audio.play().catch(e => {
        console.log('Could not play notification sound:', e)
      })
    } catch (error) {
      console.log('Notification sound not available:', error)
    }
  },

  /**
   * Mostrar notificación del sistema
   */
  showSystemNotification(title, body, icon = '/static/uber-eats-icon.png') {
    if ('Notification' in window) {
      if (Notification.permission === 'granted') {
        new Notification(title, {
          body: body,
          icon: icon
        })
      } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
          if (permission === 'granted') {
            new Notification(title, {
              body: body,
              icon: icon
            })
          }
        })
      }
    }
  },

  /**
   * Validar si un pedido es nuevo basado en timestamp
   */
  isNewOrder(orderTimestamp, thresholdMinutes = 2) {
    const now = new Date()
    const orderTime = new Date(orderTimestamp)
    const diffMs = now - orderTime
    const diffMins = diffMs / (1000 * 60)
    
    return diffMins <= thresholdMinutes
  },

  /**
   * Formatear timestamp de Uber Eats
   */
  formatOrderTime(timestamp) {
    try {
      const date = new Date(timestamp)
      return date.toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit'
      })
    } catch (e) {
      return 'Hora inválida'
    }
  },

  /**
   * Obtener detalles de un pedido específico
   */
  async getOrderDetails(orderId) {
    try {
      const response = await axios.get(`${baseUrl}/api/uber-eats/orders/${orderId}`)
      return response.data
    } catch (error) {
      console.error('Error getting order details:', error)
      throw error
    }
  },

  /**
   * Marcar pedido como listo para recoger
   */
  async markOrderReady(orderId) {
    try {
      const response = await axios.post(`${baseUrl}/api/uber-eats/orders/${orderId}/ready`)
      return response.data
    } catch (error) {
      console.error('Error marking order as ready:', error)
      throw error
    }
  },

  /**
   * Obtener menú de la tienda
   */
  async getStoreMenu() {
    try {
      const response = await axios.get(`${baseUrl}/api/uber-eats/menu`)
      return response.data
    } catch (error) {
      console.error('Error getting store menu:', error)
      throw error
    }
  },

  /**
   * Procesar total de precio de Uber Eats
   */
  processOrderTotal(orderData) {
    if (orderData.payment && orderData.payment.charges) {
      const total = orderData.payment.charges.total
      return total || 0
    }
    return 0
  },

  /**
   * Obtener información del cliente
   */
  getCustomerInfo(orderData) {
    if (orderData.eater) {
      return {
        name: orderData.eater.first_name || 'Cliente Uber Eats',
        phone: orderData.eater.phone_number || null
      }
    }
    return {
      name: 'Cliente Uber Eats',
      phone: null
    }
  }
}
