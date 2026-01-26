<template>
  <div v-show="isVisible" class="dias-locos-overlay" @click.self="closeModal">
    <div class="dias-locos-modal">
      
      <!-- Header -->
      <div class="dias-locos-header">
        <div class="header-content">
          <div class="icon-title">
            <span class="emoji-icon">🎉</span>
            <h2>DÍAS LOCOS</h2>
          </div>
          <p class="subtitle">¡Ofertas especiales del día!</p>
        </div>
        <div class="header-actions">
          <button @click="closeModal" class="btn-close-dias-locos">
            <span>✕</span>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="dias-locos-body">
        
        <!-- Selección de Pasta -->
        <div v-if="!selectedPasta" class="selection-section">
          <h3 class="section-title">
            <span class="emoji">🍝</span>
            Selecciona tu Pasta
          </h3>
          <div class="options-grid">
            <button 
              @click="selectPasta('fettuccine')" 
              class="option-card"
            >
              <div class="option-content">
                <span class="option-emoji">🍝</span>
                <span class="option-name">Fettuccine</span>
              </div>
            </button>
            <button 
              @click="selectPasta('bigoli')" 
              class="option-card"
            >
              <div class="option-content">
                <span class="option-emoji">🍝</span>
                <span class="option-name">Bigoli</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Productos Disponibles -->
        <div v-if="selectedPasta" class="products-section">
          <button @click="goBackToPasta" class="btn-back-small">
            <i class="fas fa-arrow-left"></i> Cambiar pasta
          </button>
          <h3 class="section-title">
            <span class="emoji">🛒</span>
            Productos Disponibles
          </h3>
          <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Cargando productos...</p>
          </div>
          <div v-else-if="availableProducts.length === 0" class="empty-state">
            <span class="empty-icon">📦</span>
            <p>No hay productos disponibles en esta categoría</p>
          </div>
          <div v-else class="products-grid">
            <button 
              v-for="product in availableProducts" 
              :key="product.id"
              @click="selectProduct(product)"
              class="product-card"
            >
              <div class="product-content">
                <span class="product-name">{{ product.name }}</span>
                <span class="product-price">${{ formatPrice(product.price) }}</span>
              </div>
            </button>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="dias-locos-footer">
        <button @click="closeModal" class="btn-cancel">
          Cancelar
        </button>
      </div>
      
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiasLocos',
  props: {
    products: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      isVisible: false,
      selectedPasta: null,
      availableProducts: [],
      loading: false,
      categoryId: 56 // Categoría "Días Locos" - confirmado con productos 5118-5127
    };
  },
  computed: {
    canAdd() {
      return this.selectedPasta !== null;
    }
  },
  methods: {
    openModal() {
      console.log('🎉 Modal Días Locos abierto');
      console.log('📦 Productos recibidos del padre:', this.products.length);
      
      // Resetear TODO el estado
      this.selectedPasta = null;
      this.availableProducts = [];
      this.loading = false;
      
      // Forzar actualización del DOM
      this.$nextTick(() => {
        this.isVisible = true;
        console.log('✅ Estado reseteado - mostrando selección de pasta');
      });
    },
    closeModal() {
      console.log('✅ Modal Días Locos cerrado');
      this.isVisible = false;
      
      // Resetear TODO al cerrar
      this.$nextTick(() => {
        this.selectedPasta = null;
        this.availableProducts = [];
        this.loading = false;
        console.log('🧹 Estado completamente limpio');
      });
    },
    selectPasta(pasta) {
      this.selectedPasta = pasta;
      console.log('🍝 Pasta seleccionada:', pasta);
      this.loadProducts(); // Cargar productos al seleccionar pasta
    },
    goBackToPasta() {
      this.selectedPasta = null;
      this.availableProducts = [];
    },
    loadProducts() {
      console.log('📦 Cargando productos de categoría 56 con pasta:', this.selectedPasta);
      console.log('📦 Total productos del padre (productsRequest):', this.products.length);
      
      // Buscar por término más corto para encontrar todas las variantes de escritura
      let searchTerm = this.selectedPasta.toLowerCase();
      
      // Fettuccine/Fetuccine/Fettucine → buscar "fet" para encontrar todas
      if (searchTerm === 'fettuccine') {
        searchTerm = 'fet'; // Encuentra: Fetuccine, Fettucine, Fettuccine
      }
      
      // Filtrar productos del prop (productsRequest completo)
      const filteredProducts = this.products.filter(p => {
        const productName = (p.name || '').toLowerCase();
        return p.category == this.categoryId && productName.includes(searchTerm);
      });
      
      this.$set(this, 'availableProducts', filteredProducts);
      console.log('✅ Productos filtrados:', this.availableProducts.length, this.availableProducts);
    },
    selectProduct(product) {
      if (!this.selectedPasta) {
        console.error('❌ Debe seleccionar pasta');
        return;
      }

      const pastaName = this.selectedPasta === 'fettuccine' ? 'Fettuccine' : 'Bigoli';
      
      // Precio fijo de 2990 para cualquier producto Días Locos
      const precioFijo = 2990;
      
      // Usa el ID real del producto de la categoría 56
      const diasLocosProduct = {
        id: product.id, // ← ID REAL del producto del catálogo
        name: product.name, // Usar el nombre original del producto
        price: precioFijo,
        promo_price: null,
        quantity: 1,
        prices: [{ precio: precioFijo }],
        cecina: product.cecina || false,
        ganancia: product.ganancia || 0,
        category: product.category || null,
        is_dias_locos: true,
        dias_locos_details: {
          pasta: this.selectedPasta,
          pasta_name: pastaName,
          original_product_id: product.id,
          original_product_name: product.name
        }
      };

      console.log('✅ Producto Días Locos creado:', diasLocosProduct);
      this.$emit('addDiasLocos', diasLocosProduct);
      this.closeModal();
    },
    formatPrice(price) {
      return parseFloat(price).toLocaleString('es-CL');
    }
  }
};
</script>

<style scoped>
/* Overlay */
.dias-locos-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
  animation: fadeIn 0.3s ease-out;
  backdrop-filter: blur(5px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Modal */
.dias-locos-modal {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 24px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  overflow: hidden;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(50px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Header */
.dias-locos-header {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
  position: relative;
  overflow: hidden;
}

.dias-locos-header::before {
  content: '🎉🎊✨';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 120px;
  opacity: 0.1;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translate(-50%, -50%) translateY(0px); }
  50% { transform: translate(-50%, -50%) translateY(-20px); }
}

.header-content {
  flex: 1;
  z-index: 1;
}

.icon-title {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.emoji-icon {
  font-size: 32px;
  animation: bounce 1s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.dias-locos-header h2 {
  margin: 0;
  font-size: 28px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.subtitle {
  margin: 0;
  font-size: 16px;
  opacity: 0.95;
  font-weight: 500;
}

.header-actions {
  z-index: 1;
}

.btn-close-dias-locos {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: white;
  font-size: 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.btn-close-dias-locos:hover {
  background: white;
  color: #f5576c;
  transform: rotate(90deg) scale(1.1);
}

/* Body */
.dias-locos-body {
  padding: 24px;
  overflow-y: auto;
  background: white;
  flex: 1;
}

.selection-section,
.products-section {
  margin-bottom: 24px;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
  font-size: 20px;
  font-weight: 700;
  color: #2c3e50;
}

.section-title .emoji {
  font-size: 24px;
}

/* Options Grid */
.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.options-grid-3 {
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

/* Botón de volver pequeño */
.btn-back-small {
  background: rgba(102, 126, 234, 0.1);
  border: 2px solid #667eea;
  color: #667eea;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}

.btn-back-small:hover {
  background: #667eea;
  color: white;
  transform: translateX(-5px);
}

.option-card {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border: 3px solid transparent;
  border-radius: 16px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  overflow: hidden;
}

.option-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.option-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.option-card.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transform: scale(1.05);
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.option-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  position: relative;
  z-index: 1;
}

.option-emoji {
  font-size: 48px;
}

.option-name {
  font-size: 18px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.check-icon {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #10b981;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: bold;
  animation: checkPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes checkPop {
  0% { transform: scale(0); }
  100% { transform: scale(1); }
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
}

.product-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.product-card:hover {
  border-color: #667eea;
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
}

.product-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.product-name {
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
}

.product-price {
  font-size: 18px;
  font-weight: 700;
  color: #667eea;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 40px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f4f6;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-icon {
  font-size: 64px;
  display: block;
  margin-bottom: 16px;
}

/* Footer */
.dias-locos-footer {
  padding: 20px 24px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-cancel {
  background: #6b7280;
  color: white;
  border: none;
  padding: 12px 32px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  background: #4b5563;
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
  .dias-locos-modal {
    width: 95%;
    max-height: 95vh;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
  }
}
</style>
