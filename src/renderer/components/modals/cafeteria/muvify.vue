<template>
  <div v-show="isVisible" class="muvify-overlay" @click.self="closeModal">
    <div class="muvify-modal" :key="modalKey">
      <!-- Header -->
      <div class="muvify-header">
        <div class="header-content">
          <div class="header-left">
            <i class="fas fa-bus logo-icon"></i>
            <div>
              <h3 class="header-title">Club Muvify Turbus </h3>
              <p class="header-subtitle">Validación de convenio</p>
            </div>
          </div>
          <button @click="closeModal" class="btn-close-muvify">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="muvify-body">
        
        <!-- PASO 1: Validación de RUT -->
        <div v-if="!rutValidado" class="validation-section">
          <div class="validation-icon">
            <i class="fas fa-id-card"></i>
          </div>
          <h4 class="validation-title">Validar RUT del Cliente</h4>
          <p class="validation-desc">Ingresa el RUT del cliente para verificar su membresía Muvify</p>
          
          <div class="input-group-rut">
            <input 
              v-model="rutInput"
              type="text"
              class="input-rut"
              placeholder="12345678-9"
              @input="formatearRUT"
              @keyup.enter="validarRUT"
              maxlength="12"
            />
            <button 
              @click="validarRUT" 
              :disabled="validando || !rutInput"
              class="btn-validar">
              <i v-if="validando" class="fas fa-spinner fa-spin"></i>
              <i v-else class="fas fa-check"></i>
              {{ validando ? 'Validando...' : 'Validar' }}
            </button>
          </div>

          <div v-if="errorValidacion" class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorValidacion }}
          </div>
        </div>

        <!-- PASO 2: Selección de Pasta (Productos reales de Categorías 1 y 2) -->
        <div v-if="rutValidado" class="products-section">
          <div class="success-banner">
            <i class="fas fa-check-circle"></i>
            <div>
              <strong>RUT Validado</strong>
              <p>{{ rutValidado }}</p>
            </div>
          </div>

          <!-- Selección de Pasta -->
          <div class="selection-group">
            <h4 class="selection-title">
              <i class="fas fa-wheat-awn"></i>
              Paso 1: Elige tu pasta
            </h4>
            
            <!-- Mensaje de carga -->
            <div v-if="productosLoading" class="loading-products">
              <i class="fas fa-spinner fa-spin"></i>
              Cargando pastas...
            </div>

            <!-- Mensaje si no hay productos -->
            <div v-else-if="productosPasta.length === 0" class="no-products">
              <i class="fas fa-box-open"></i>
              No hay pastas disponibles
            </div>

            <!-- Grid de productos de pasta -->
            <div v-else class="pasta-grid">
              <!-- Sección Bigoli (Categoría 1) -->
              <div v-if="productosBigoli.length > 0" class="pasta-section">
                <h5 class="pasta-category-title">
                  <i class="fas fa-utensils"></i>
                  Bigoli
                </h5>
                <div class="pasta-options">
                  <div 
                    v-for="pasta in productosBigoli"
                    :key="pasta.id"
                    @click="selectedPasta = pasta"
                    :class="['pasta-card', { 'selected': selectedPasta && selectedPasta.id === pasta.id }]">
                    <div v-if="selectedPasta && selectedPasta.id === pasta.id" class="pasta-icon">
                      <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="pasta-name">{{ pasta.name }}</div>
                  </div>
                </div>
              </div>

              <!-- Sección Fettuccine (Categoría 2) -->
              <div v-if="productosFettuccine.length > 0" class="pasta-section">
                <h5 class="pasta-category-title">
                  <i class="fas fa-utensils"></i>
                  Fettuccine
                </h5>
                <div class="pasta-options">
                  <div 
                    v-for="pasta in productosFettuccine"
                    :key="pasta.id"
                    @click="selectedPasta = pasta"
                    :class="['pasta-card', { 'selected': selectedPasta && selectedPasta.id === pasta.id }]">
                    <div v-if="selectedPasta && selectedPasta.id === pasta.id" class="pasta-icon">
                      <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="pasta-name">{{ pasta.name }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Selección de Bebida (solo aparece si ya eligió pasta) -->
          <div v-if="selectedPasta" class="selection-group">
            <h4 class="selection-title">
              <i class="fas fa-glass-water"></i>
              Paso 2: Elige tu bebida
            </h4>
            
            <!-- Lista de bebidas -->
            <div v-if="productosLoading" class="loading-products">
              <i class="fas fa-spinner fa-spin"></i>
              Cargando bebidas...
            </div>

            <div v-else-if="bebidasDisponibles.length === 0" class="no-products">
              <i class="fas fa-box-open"></i>
              No hay bebidas disponibles
            </div>

            <div v-else class="products-grid">
              <div 
                v-for="bebida in bebidasDisponibles" 
                :key="bebida.id"
                @click="seleccionarBebida(bebida)"
                class="product-card">
                <div class="product-info">
                  <div class="product-name">{{ bebida.name }}</div>
                  <div class="product-category">Bebida</div>
                </div>
                <div class="product-action">
                  <i class="fas fa-plus-circle"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Resumen del combo y precio -->
          <div v-if="selectedPasta" class="combo-summary">
            <div class="combo-info">
              <i class="fas fa-utensils"></i>
              <div>
                <strong>Combo MUVIFY</strong>
                <p>{{ selectedPasta.name }} + Bebida</p>
              </div>
            </div>
            <div class="combo-price">
              <span class="price-label">Precio:</span>
              <span class="price-value">$5.240</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="muvify-footer">
        <div class="footer-info">
          <i class="fas fa-info-circle"></i>
          <span v-if="!rutValidado">Valida el RUT para continuar</span>
          <span v-else-if="!selectedPasta">Selecciona una pasta para continuar</span>
          <span v-else>Selecciona una bebida para completar tu combo</span>
        </div>
        <div class="developer-credit">
          <i class="fas fa-code"></i>
          Desarrollado por <strong>Jimmy Arriagada</strong> para Turbus
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection.js';

// Configuración de la API Muvify
const MUVIFY_API = {
  baseURL: 'https://clubmuvify.turbus.cl',
  token: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJlbXByZXNhIjoiQ09QUEVMSUEiLCJkZXBhcnRhbWVudG8iOiJWRU5UQVMiLCJhbWJpZW50ZSI6IlFBUyIsImlhdCI6MTc2MDQ0MDc4MywiZXhwIjoyNjI0MzU0MzgzfQ.S2MufbSlY63aG43qKMEXRQL7gyK-S5fKWG5hHH7_ggU',
  endpoint: '/miembros/minimal'
};

export default {
  name: 'Muvify',
  props: {
    allProducts: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isVisible: false,
      modalKey: 0,
      rutInput: '',
      validando: false,
      rutValidado: null,
      errorValidacion: null,
      selectedPasta: null, // Producto completo seleccionado (categoría 1 o 2)
      productosLoading: false,
      productos: []
    };
  },
  computed: {
    // Productos de categoría 1 (Bigoli)
    productosBigoli() {
      if (!this.productos || this.productos.length === 0) return [];
      const bigolis = this.productos.filter(p => p.category === 1);
      console.log('🍝 [MUVIFY] Bigolis encontrados:', bigolis.length);
      return bigolis;
    },
    
    // Productos de categoría 2 (Fettuccine)
    productosFettuccine() {
      if (!this.productos || this.productos.length === 0) return [];
      const fettuccines = this.productos.filter(p => p.category === 2);
      console.log('🍝 [MUVIFY] Fettuccines encontrados:', fettuccines.length);
      return fettuccines;
    },
    
    // Todos los productos de pasta (categorías 1 y 2)
    productosPasta() {
      return [...this.productosBigoli, ...this.productosFettuccine];
    },
    
    // Filtrar solo bebidas (categoría 3)
    bebidasDisponibles() {
      if (!this.productos || this.productos.length === 0) return [];
      
      // Buscar productos con category === 3 (Bebidas)
      const bebidas = this.productos.filter(p => p.category === 3);
      
      console.log('🥤 [MUVIFY] Bebidas encontradas:', bebidas.length);
      return bebidas;
    }
  },
  methods: {
    openModal() {
      console.log('🚌 [MUVIFY] Abriendo modal');
      
      // Incrementar key fuerza recreación completa del DOM (elimina error insertBefore)
      this.modalKey++;
      
      // Resetear estado
      this.resetModal();
      this.cargarProductos();
      
      // Mostrar modal con estado limpio
      this.isVisible = true;
    },
    
    closeModal() {
      console.log('🚌 [MUVIFY] Cerrando modal');
      this.isVisible = false;
      // NO resetear aquí para evitar parpadeo visual
      // El reset se hace al abrir de nuevo
    },

    resetModal() {
      console.log('🔄 [MUVIFY] Reseteando modal');
      this.rutInput = '';
      this.validando = false;
      this.rutValidado = null;
      this.errorValidacion = null;
      this.selectedPasta = null;
      // NO resetear productos, mantener cache
    },

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
     */
    formatearRUT() {
      if (!this.rutInput) return;
      
      let rutLimpio = this.limpiarRUT(this.rutInput);
      
      if (rutLimpio.length > 1) {
        const cuerpo = rutLimpio.slice(0, -1);
        const dv = rutLimpio.slice(-1);
        this.rutInput = `${cuerpo}-${dv}`;
      } else {
        this.rutInput = rutLimpio;
      }
    },

    /**
     * Valida el formato del RUT chileno
     */
    validarFormatoRUT(rut) {
      const rutLimpio = this.limpiarRUT(rut);
      
      if (rutLimpio.length < 2) {
        return { valido: false, mensaje: 'RUT muy corto' };
      }

      const cuerpo = rutLimpio.slice(0, -1);
      const dv = rutLimpio.slice(-1);

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
     * Formatea el RUT para enviarlo a la API
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

    /**
     * Valida el RUT con la API de Muvify
     */
    async validarRUT() {
      if (!this.rutInput || !this.rutInput.trim()) {
        this.errorValidacion = 'Por favor ingresa un RUT';
        return;
      }

      // Validar formato del RUT
      const validacion = this.validarFormatoRUT(this.rutInput);
      if (!validacion.valido) {
        this.errorValidacion = validacion.mensaje;
        console.log('⚠️ [MUVIFY] Validación fallida:', validacion.mensaje);
        return;
      }

      this.validando = true;
      this.errorValidacion = null;

      const rutFormateado = this.formatearRUTParaAPI(this.rutInput);
      
      console.log('🔍 [MUVIFY] Consultando RUT:', rutFormateado);

      try {
        const url = `${MUVIFY_API.baseURL}${MUVIFY_API.endpoint}/${rutFormateado}`;
        console.log('🌐 [MUVIFY] URL:', url);

        const response = await fetch(url, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${MUVIFY_API.token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        console.log('📥 [MUVIFY] Response status:', response.status);

        if (response.ok) {
          const data = await response.json();
          console.log('✅ [MUVIFY] Datos recibidos:', data);
          
          // RUT válido
          this.rutValidado = rutFormateado;
          this.errorValidacion = null;
          
          if (this.$parent.$awn) {
            this.$parent.$awn.success(`RUT ${rutFormateado} validado correctamente`, {
              labels: { success: 'VALIDACIÓN EXITOSA' }
            });
          }
        } else if (response.status === 404) {
          this.errorValidacion = 'RUT no encontrado en el sistema Muvify';
          console.log('❌ [MUVIFY] RUT no encontrado');
        } else {
          this.errorValidacion = 'Error al validar el RUT. Intenta nuevamente';
          console.error('❌ [MUVIFY] Error en la respuesta:', response.status);
        }
      } catch (error) {
        console.error('❌ [MUVIFY] Error de red:', error);
        this.errorValidacion = 'Error de conexión. Verifica tu internet';
      } finally {
        this.validando = false;
      }
    },

    /**
     * Cargar productos desde el padre (catalog.vue)
     */
    cargarProductos() {
      console.log('📦 [MUVIFY] Cargando productos...');
      this.productosLoading = true;
      
      // Obtener productos del padre
      if (this.$parent.products && Array.isArray(this.$parent.products)) {
        this.productos = this.$parent.products;
        console.log('✅ [MUVIFY] Productos cargados:', this.productos.length);
        
        // Log para debug de categorías
        const bigolis = this.productos.filter(p => p.category === 1);
        const fettuccines = this.productos.filter(p => p.category === 2);
        const bebidas = this.productos.filter(p => p.category === 3);
        
        console.log('🍝 [MUVIFY] Bigolis (categoría 1):', bigolis.length);
        if (bigolis.length > 0) {
          console.log('   Ejemplos:', bigolis.slice(0, 3).map(p => p.name));
        }
        
        console.log('🍝 [MUVIFY] Fettuccines (categoría 2):', fettuccines.length);
        if (fettuccines.length > 0) {
          console.log('   Ejemplos:', fettuccines.slice(0, 3).map(p => p.name));
        }
        
        console.log('🥤 [MUVIFY] Bebidas (categoría 3):', bebidas.length);
        if (bebidas.length > 0) {
          console.log('   Ejemplos:', bebidas.slice(0, 3).map(b => b.name));
        }
      } else {
        console.log('⚠️ [MUVIFY] No hay productos disponibles');
        this.productos = [];
      }
      
      this.productosLoading = false;
    },

    /**
     * Seleccionar bebida y agregar combo al carrito
     */
    seleccionarBebida(bebida) {
      if (!this.rutValidado) {
        if (this.$parent.$awn) {
          this.$parent.$awn.warning('Debes validar el RUT primero');
        }
        return;
      }

      if (!this.selectedPasta) {
        if (this.$parent.$awn) {
          this.$parent.$awn.warning('Debes elegir una pasta primero');
        }
        return;
      }

      console.log('➕ [MUVIFY] Creando combo:', {
        pasta: this.selectedPasta.name,
        bebida: bebida.name,
        precio: 5240
      });

      // Construir nombre del combo
      const nombreCombo = `Combo MUVIFY: ${this.selectedPasta.name} + ${bebida.name}`;

      // Crear combo para el carro
      const comboData = {
        id: `muvify_${Date.now()}`, // ID único
        name: nombreCombo,
        price: 5240, // PRECIO FIJO $5.240
        promo_price: null,
        quantity: 1,
        type: 'muvify',
        is_muvify: true,
        rut_cliente: this.rutValidado,
        muvify_details: {
          pasta_id: this.selectedPasta.id,
          pasta_name: this.selectedPasta.name,
          pasta_category: this.selectedPasta.category,
          bebida_id: bebida.id,
          bebida_name: bebida.name
        },
        prices: [{ precio: 5240 }],
        cecina: false,
        ganancia: 0
      };

      // Emitir al padre (catalog.vue)
      this.$emit('addMuvify', comboData);

      if (this.$parent.$awn) {
        this.$parent.$awn.success(`${nombreCombo} agregado`, {
          labels: { success: 'COMBO AGREGADO' }
        });
      }

      // Cerrar el modal automáticamente - el reset ocurrirá al abrir de nuevo
      setTimeout(() => {
        this.closeModal();
      }, 800);
    },

    formatNumber(num) {
      if (!num) return '0';
      return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
  }
};
</script>

<style scoped>
/* Overlay */
.muvify-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

/* Modal */
.muvify-modal {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: slideIn 0.3s ease-out;
  display: flex;
  flex-direction: column;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Header */
.muvify-header {
  background: linear-gradient(135deg, #003399 0%, #0051cc 100%);
  padding: 25px 30px;
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.logo-icon {
  font-size: 2rem;
  opacity: 0.9;
}

.header-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.header-subtitle {
  margin: 5px 0 0 0;
  font-size: 0.9rem;
  opacity: 0.9;
}

.btn-close-muvify {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close-muvify:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

/* Body */
.muvify-body {
  padding: 30px;
  overflow-y: auto;
  flex: 1;
}

/* Validation Section */
.validation-section {
  text-align: center;
}

.validation-icon {
  font-size: 4rem;
  color: #003399;
  margin-bottom: 20px;
}

.validation-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 10px;
}

.validation-desc {
  color: #666;
  margin-bottom: 30px;
}

.input-group-rut {
  display: flex;
  gap: 10px;
  max-width: 400px;
  margin: 0 auto;
}

.input-rut {
  flex: 1;
  padding: 15px 20px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.3s;
}

.input-rut:focus {
  outline: none;
  border-color: #003399;
  box-shadow: 0 0 0 4px rgba(0, 51, 153, 0.1);
}

.btn-validar {
  padding: 15px 30px;
  background: linear-gradient(135deg, #003399 0%, #0051cc 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-validar:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 51, 153, 0.3);
}

.btn-validar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.alert-error {
  margin-top: 20px;
  padding: 15px 20px;
  background: #fee;
  border: 2px solid #fcc;
  border-radius: 12px;
  color: #c33;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
}

/* Products Section */
.products-section {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.success-banner {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 25px;
}

.success-banner i {
  font-size: 2rem;
}

.success-banner strong {
  display: block;
  font-size: 1.1rem;
  margin-bottom: 5px;
}

.success-banner p {
  margin: 0;
  opacity: 0.9;
}

/* Selection Group (Pasta/Bebida) */
.selection-group {
  margin-bottom: 30px;
}

.selection-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Pasta Grid - Nuevo diseño por categorías */
.pasta-grid {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.pasta-section {
  background: #f9f9f9;
  border-radius: 12px;
  padding: 20px;
}

.pasta-category-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #003399;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding-bottom: 10px;
  border-bottom: 2px solid #e0e0e0;
}

.pasta-options {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
}

.pasta-card {
  background: white;
  border: 3px solid #e0e0e0;
  border-radius: 10px;
  padding: 20px 15px;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  text-align: center;
}

.pasta-card:hover {
  border-color: #003399;
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 51, 153, 0.15);
}

.pasta-card.selected {
  border-color: #003399;
  background: linear-gradient(135deg, rgba(0, 51, 153, 0.05) 0%, rgba(0, 81, 204, 0.05) 100%);
  box-shadow: 0 8px 24px rgba(0, 51, 153, 0.2);
}

.pasta-icon {
  position: absolute;
  top: 8px;
  right: 8px;
  color: #10b981;
  font-size: 1.3rem;
}

.pasta-name {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  line-height: 1.4;
}

/* Legacy options grid (por si acaso) */
.options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
}

.option-card {
  background: white;
  border: 3px solid #e0e0e0;
  border-radius: 12px;
  padding: 25px;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  text-align: center;
}

.option-card:hover {
  border-color: #003399;
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 51, 153, 0.15);
}

.option-card.selected {
  border-color: #003399;
  background: linear-gradient(135deg, rgba(0, 51, 153, 0.05) 0%, rgba(0, 81, 204, 0.05) 100%);
  box-shadow: 0 8px 24px rgba(0, 51, 153, 0.2);
}

.option-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  color: #10b981;
  font-size: 1.5rem;
}

.option-name {
  font-size: 1.3rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 5px;
}

.option-desc {
  font-size: 0.85rem;
  color: #999;
}

/* Combo Summary */
.combo-summary {
  background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
  border: 2px solid #ff9800;
  border-radius: 12px;
  padding: 20px;
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.combo-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.combo-info i {
  font-size: 2rem;
  color: #ff9800;
}

.combo-info strong {
  display: block;
  font-size: 1.1rem;
  color: #333;
  margin-bottom: 5px;
}

.combo-info p {
  margin: 0;
  color: #666;
  font-size: 0.95rem;
}

.combo-price {
  text-align: right;
}

.price-label {
  display: block;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 5px;
}

.price-value {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: #ff9800;
}

.products-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #333;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Products Grid */
.loading-products,
.no-products {
  text-align: center;
  padding: 40px 20px;
  color: #999;
  font-size: 1.1rem;
}

.loading-products i,
.no-products i {
  font-size: 3rem;
  display: block;
  margin-bottom: 15px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 15px;
  max-height: 300px;
  overflow-y: auto;
  padding-right: 10px;
}

.product-card {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.product-card:hover {
  border-color: #003399;
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 51, 153, 0.15);
}

.product-info {
  flex: 1;
}

.product-name {
  font-weight: 700;
  color: #333;
  margin-bottom: 5px;
  font-size: 0.95rem;
}

.product-category {
  font-size: 0.75rem;
  color: #999;
  background: #f5f5f5;
  padding: 3px 8px;
  border-radius: 6px;
  display: inline-block;
}

.product-action {
  text-align: center;
  color: #10b981;
  font-size: 1.5rem;
  padding-top: 10px;
  border-top: 1px solid #f0f0f0;
}

/* Footer */
.muvify-footer {
  padding: 20px 30px;
  background: #f9f9f9;
  border-top: 1px solid #e0e0e0;
}

.footer-info {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #666;
  font-size: 0.9rem;
}

.footer-info i {
  color: #003399;
}

.developer-credit {
  text-align: center;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #e0e0e0;
  color: #666;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.developer-credit i {
  color: #003399;
  font-size: 1.1rem;
}

.developer-credit strong {
  color: #003399;
  font-weight: 700;
  font-size: 1.05rem;
}

/* Scrollbar personalizado */
.products-grid::-webkit-scrollbar {
  width: 8px;
}

.products-grid::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.products-grid::-webkit-scrollbar-thumb {
  background: #003399;
  border-radius: 10px;
}

.products-grid::-webkit-scrollbar-thumb:hover {
  background: #0051cc;
}
</style>
