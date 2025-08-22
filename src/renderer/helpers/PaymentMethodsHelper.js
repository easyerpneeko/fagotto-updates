// PaymentMethodsHelper.js - Helper para manejar métodos de pago dinámicos

export default class PaymentMethodsHelper {
  
  // Obtener todos los métodos de pago disponibles con sus configuraciones
  static getAvailablePaymentMethods() {
    return [
      {
        key: 'efectivo',
        name: 'Efectivo',
        icon: 'fas fa-money-bill-wave',
        color: 'success',
        emoji: '💵',
        category: 'physical'
      },
      {
        key: 'debito',
        name: 'Tarjeta Débito',
        icon: 'fas fa-credit-card',
        color: 'primary',
        emoji: '💳',
        category: 'card'
      },
      {
        key: 'credito',
        name: 'Tarjeta Crédito',
        icon: 'fas fa-credit-card',
        color: 'info',
        emoji: '💳',
        category: 'card'
      },
      {
        key: 'transferencia',
        name: 'Transferencia',
        icon: 'fas fa-mobile-alt',
        color: 'warning',
        emoji: '🏦',
        category: 'digital'
      },
      {
        key: 'cheque',
        name: 'Cheque',
        icon: 'fas fa-file-invoice',
        color: 'secondary',
        emoji: '📄',
        category: 'physical'
      },
      {
        key: 'banco',
        name: 'Banco',
        icon: 'fas fa-university',
        color: 'dark',
        emoji: '🏛️',
        category: 'digital'
      },
      {
        key: 'amipass',
        name: 'Amipass',
        icon: 'fas fa-id-card',
        color: 'success',
        emoji: '🎫',
        category: 'digital'
      },
      {
        key: 'multicaja',
        name: 'Multicaja',
        icon: 'fas fa-wallet',
        color: 'warning',
        emoji: '💰',
        category: 'digital'
      },
      {
        key: 'edenred',
        name: 'Edenred',
        icon: 'fas fa-credit-card',
        color: 'danger',
        emoji: '🍽️',
        category: 'card'
      },
      {
        key: 'convenio_empresa',
        name: 'Convenio Empresa',
        icon: 'fas fa-building',
        color: 'primary',
        emoji: '🏢',
        category: 'corporate'
      },
      {
        key: 'sodexo',
        name: 'Sodexo',
        icon: 'fas fa-utensils',
        color: 'success',
        emoji: '🍴',
        category: 'card'
      },
      {
        key: 'rappi',
        name: 'Rappi',
        icon: 'fas fa-motorcycle',
        color: 'danger',
        emoji: '🛵',
        category: 'delivery'
      },
      {
        key: 'junaeb',
        name: 'Junaeb',
        icon: 'fas fa-graduation-cap',
        color: 'info',
        emoji: '🎓',
        category: 'government'
      },
      {
        key: 'uber',
        name: 'Uber Eats',
        icon: 'fas fa-car',
        color: 'dark',
        emoji: '🚗',
        category: 'delivery'
      },
      {
        key: 'pedidos_ya',
        name: 'PedidosYa',
        icon: 'fas fa-pizza-slice',
        color: 'warning',
        emoji: '🍕',
        category: 'delivery'
      },
      {
        key: 'pluxee',
        name: 'Pluxee',
        icon: 'fas fa-credit-card',
        color: 'primary',
        emoji: '💼',
        category: 'card'
      },
      {
        key: 'banco_chile_20',
        name: 'Banco Chile 20%',
        icon: 'fas fa-percentage',
        color: 'info',
        emoji: '🏦',
        category: 'bank'
      },
      {
        key: 'fluxi',
        name: 'Fluxi',
        icon: 'fas fa-mobile-alt',
        color: 'success',
        emoji: '📱',
        category: 'digital'
      }
    ];
  }

  // Obtener métodos de pago activos basado en configuración
  static getActivePaymentMethods(config = null) {
    const allMethods = PaymentMethodsHelper.getAvailablePaymentMethods();
    
    // Si no hay configuración, devolver los métodos básicos
    if (!config) {
      return allMethods.filter(method => 
        ['efectivo', 'debito', 'credito', 'transferencia', 'cheque'].includes(method.key)
      );
    }

    // Aquí puedes agregar lógica para filtrar basado en configuración
    // Por ejemplo, verificar qué módulos están activos
    const activeMethods = allMethods.filter(method => {
      // Lógica para determinar si el método está activo
      // basado en la configuración del sistema
      return true; // Por ahora devolver todos
    });

    return activeMethods;
  }

  // Obtener configuración para el display de un método de pago
  static getPaymentMethodDisplay(methodKey) {
    const method = PaymentMethodsHelper.getAvailablePaymentMethods()
      .find(m => m.key === methodKey);
    
    if (!method) {
      return {
        name: 'Desconocido',
        icon: 'fas fa-question',
        color: 'secondary',
        emoji: '❓'
      };
    }

    return method;
  }

  // Generar objeto para inicializar conteo de medios de pago
  static initializePaymentCounts(activeMethods = null) {
    if (!activeMethods) {
      activeMethods = PaymentMethodsHelper.getActivePaymentMethods();
    }

    const counts = {};
    activeMethods.forEach(method => {
      counts[method.key] = 0;
    });

    return counts;
  }

  // Obtener métodos de pago por categoría
  static getPaymentMethodsByCategory(category) {
    return PaymentMethodsHelper.getAvailablePaymentMethods()
      .filter(method => method.category === category);
  }

  // Mapear keys antiguos a nuevos (para compatibilidad)
  static mapLegacyKeys(oldKey) {
    const mapping = {
      'tarjeta_debito': 'debito',
      'tarjeta_credito': 'credito',
      'vale_vista': 'cheque', // O crear vale_vista si es necesario
      'otro': 'efectivo' // Mapear por defecto a efectivo
    };

    return mapping[oldKey] || oldKey;
  }

  // Obtener color de badge para método de pago
  static getBadgeClass(methodKey) {
    const method = PaymentMethodsHelper.getPaymentMethodDisplay(methodKey);
    return `badge bg-${method.color} text-white`;
  }

  // Obtener texto de display completo
  static getDisplayText(methodKey) {
    const method = PaymentMethodsHelper.getPaymentMethodDisplay(methodKey);
    return `${method.emoji} ${method.name}`;
  }
}
