<template>
  <div>
    <!-- Modal Test Merchise -->
    <div class="modal fade" id="modalTestMerchise" tabindex="-1" role="dialog" aria-labelledby="modalTestMerchise"
      aria-hidden="true" data-backdrop="false">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content test-merchise-modal">
          <!-- Header -->
          <div class="test-header">
            <div class="header-content">
              <div class="header-left">
                <div class="logo-icon">
                  <i class="fas fa-utensils"></i>
                </div>
                <h3 class="header-title">Test Merchise</h3>
              </div>
              <button type="button" class="btn-close-test" @click="closeModal">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Body -->
          <div class="modal-body test-body">
            <!-- Vista de productos -->
            <div v-show="!selectedProduct" class="products-view">
              
              <div v-show="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Cargando productos...</p>
              </div>
              
              <div v-show="!loading && products.length === 0" class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>No hay productos disponibles</p>
              </div>
              
              <div v-show="!loading && products.length > 0" class="products-grid">
                <div 
                  v-for="product in products" 
                  :key="product.id" 
                  @click="selectProduct(product)"
                  class="product-card">
                  <div v-if="product.image" class="product-image">
                    <img :src="product.image" :alt="product.name" />
                  </div>
                  <div v-else class="product-icon">
                    <i class="fas fa-pizza-slice"></i>
                  </div>
                  <div class="product-info">
                    <h5 class="product-name">{{ product.name }}</h5>
                    <p v-if="product.description" class="product-description">{{ product.description }}</p>
                    <p class="product-price">${{ formatNumber(product.price) }}</p>
                  </div>
                  <div class="product-arrow">
                    <i class="fas fa-chevron-right"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Vista de modifiers -->
            <div v-if="selectedProduct && showModifiers" class="modifiers-view">
              <button @click="backToProducts" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver a productos
              </button>

              <div class="product-header-info">
                <h4>{{ selectedProduct.name }}</h4>
                <p class="base-price">Precio base: ${{ formatNumber(selectedProduct.price) }}</p>
              </div>

              <div v-if="loadingModifiers" class="loading-state">
                <div class="spinner"></div>
                <p>Cargando opciones...</p>
              </div>

              <div v-else-if="modifiers.length === 0" class="no-modifiers">
                <i class="fas fa-info-circle"></i>
                <p>Este producto no tiene opciones adicionales</p>
                <button @click="addToCartDirect" class="btn btn-primary">
                  <i class="fas fa-shopping-cart"></i>
                  Agregar al carrito
                </button>
              </div>

              <div v-else class="modifiers-container">
                <div v-for="modifier in modifiers" :key="modifier.id" class="modifier-group">
                  <div class="modifier-header">
                    <h5 class="modifier-name">
                      {{ modifier.name }}
                      <span v-if="modifier.required" class="badge-required">Requerido</span>
                    </h5>
                    <p class="modifier-hint">
                      <span v-if="modifier.min_selections === modifier.max_selections">
                        Elige {{ modifier.min_selections }}
                      </span>
                      <span v-else>
                        Elige entre {{ modifier.min_selections }} y {{ modifier.max_selections }}
                      </span>
                    </p>
                  </div>

                  <div class="options-list">
                    <div 
                      v-for="option in modifier.options" 
                      :key="option.id"
                      @click="toggleOption(modifier, option)"
                      :class="['option-item', { 'selected': isOptionSelected(modifier.id, option.id) }]">
                      <div class="option-check">
                        <i :class="[
                          'fas', 
                          isOptionSelected(modifier.id, option.id) ? 'fa-check-circle' : 'fa-circle'
                        ]"></i>
                      </div>
                      <div class="option-content">
                        <span class="option-name">{{ option.name }}</span>
                        <span v-if="option.price > 0" class="option-price">+${{ formatNumber(option.price) }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="modifiers-footer">
                  <div class="total-price">
                    <span>Total:</span>
                    <strong>${{ formatNumber(calculateTotal()) }}</strong>
                  </div>
                  <button @click="addToCartWithModifiers" class="btn btn-primary btn-add-cart">
                    <i class="fas fa-shopping-cart"></i>
                    Agregar al carrito
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';

export default {
  name: 'TestMerchise',
  props: {
    cart: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      loadingModifiers: false,
      products: [],
      selectedProduct: null,
      showModifiers: false,
      modifiers: [],
      selectedOptions: {}
    };
  },
  methods: {
    formatNumber(value) {
      return FormatNumber.format(value);
    },

    async openModal() {
      console.log('🚀 Abriendo modal Test Merchise');
      $('#modalTestMerchise').modal('show');
      await this.loadProducts();
      console.log('📦 Estado después de cargar:', {
        loading: this.loading,
        productsLength: this.products.length,
        selectedProduct: this.selectedProduct
      });
    },

    closeModal() {
      $('#modalTestMerchise').modal('hide');
      this.resetState();
    },

    resetState() {
      this.selectedProduct = null;
      this.showModifiers = false;
      this.modifiers = [];
      this.selectedOptions = {};
    },

    async loadProducts() {
      try {
        this.loading = true;
        this.products = []; // Limpiar primero
        
        // Usar Connection.request directamente
        const response = await Connection.request(
          'get',
          BaseUrl.getUrl('api/merchise/products')
        );
        
        if (response.success && response.data && response.data.data) {
          // Forzar reactividad con $set
          this.$set(this, 'products', response.data.data);
          console.log('✅ Productos cargados:', this.products.length);
        } else {
          console.error('❌ Error en respuesta:', response);
          if (this.$parent.$awn) {
            this.$parent.$awn.alert('No se pudieron cargar los productos');
          }
        }
      } catch (error) {
        console.error('❌ Error cargando productos:', error);
        
        if (this.$parent.$awn) {
          this.$parent.$awn.alert('No se pudieron cargar los productos');
        }
      } finally {
        this.loading = false;
        // Forzar actualización del componente
        this.$forceUpdate();
      }
    },

    async selectProduct(product) {
      this.selectedProduct = product;
      this.showModifiers = true;
      await this.loadModifiers(product.id);
    },

    async loadModifiers(productId) {
      try {
        this.loadingModifiers = true;
        
        // Usar Connection.request con la nueva ruta
        const response = await Connection.request(
          'get',
          BaseUrl.getUrl(`api/merchise/products/${productId}/modifiers`)
        );
        
        if (response.success && response.data && response.data.data) {
          this.modifiers = response.data.data;
          console.log('✅ Modifiers cargados:', this.modifiers);
          
          // Inicializar selectedOptions
          this.selectedOptions = {};
          this.modifiers.forEach(modifier => {
            this.selectedOptions[modifier.id] = [];
          });
        }
      } catch (error) {
        console.error('❌ Error cargando modifiers:', error);
        this.modifiers = [];
      } finally {
        this.loadingModifiers = false;
      }
    },

    backToProducts() {
      this.resetState();
    },

    toggleOption(modifier, option) {
      const modifierId = modifier.id;
      const optionId = option.id;
      
      if (!this.selectedOptions[modifierId]) {
        this.selectedOptions[modifierId] = [];
      }

      const currentSelections = this.selectedOptions[modifierId];
      const isSelected = currentSelections.find(o => o.id === optionId);

      if (isSelected) {
        // Deseleccionar
        this.selectedOptions[modifierId] = currentSelections.filter(o => o.id !== optionId);
      } else {
        // Verificar max_selections
        if (currentSelections.length >= modifier.max_selections) {
          if (modifier.max_selections === 1) {
            // Comportamiento radio: reemplazar
            this.selectedOptions[modifierId] = [option];
          } else {
            if (this.$parent.$awn) {
              this.$parent.$awn.warning(`Solo puedes seleccionar ${modifier.max_selections} opciones`);
            }
            return;
          }
        } else {
          // Agregar
          this.selectedOptions[modifierId].push(option);
        }
      }

      // Force update
      this.$forceUpdate();
    },

    isOptionSelected(modifierId, optionId) {
      if (!this.selectedOptions[modifierId]) return false;
      return this.selectedOptions[modifierId].some(o => o.id === optionId);
    },

    calculateTotal() {
      let total = parseFloat(this.selectedProduct.price);
      
      for (let modifierId in this.selectedOptions) {
        this.selectedOptions[modifierId].forEach(option => {
          total += parseFloat(option.price);
        });
      }
      
      return total;
    },

    addToCartDirect() {
      console.log('🛒 Agregando producto merchise sin modifiers:', this.selectedProduct);
      
      const productData = {
        id: this.selectedProduct.id,
        name: this.selectedProduct.name,
        price: parseFloat(this.selectedProduct.price),
        quantity: 1,
        type: 'merchise_product',
        description: this.selectedProduct.description || ''
      };
      
      console.log('📦 Datos enviados:', productData);
      
      this.$emit('addMerchise', productData);
      
      if (this.$parent.$awn) {
        this.$parent.$awn.success(`${this.selectedProduct.name} agregado al carrito`);
      }

      this.closeModal();
    },

    addToCartWithModifiers() {
      // Validar modifiers requeridos
      for (let modifier of this.modifiers) {
        if (modifier.required) {
          const selections = this.selectedOptions[modifier.id] || [];
          if (selections.length < modifier.min_selections) {
            if (this.$parent.$awn) {
              this.$parent.$awn.warning(`Debes seleccionar al menos ${modifier.min_selections} opción(es) en "${modifier.name}"`);
            }
            return;
          }
        }
      }

      // Calcular precio total
      const totalPrice = this.calculateTotal();
      
      console.log('🛒 Agregando producto merchise con modifiers:', this.selectedProduct);

      // Construir nombre con opciones seleccionadas
      let fullName = this.selectedProduct.name;
      const modifierNames = [];
      
      for (let modifierId in this.selectedOptions) {
        const selections = this.selectedOptions[modifierId];
        if (selections.length > 0) {
          modifierNames.push(selections.map(s => s.name).join(', '));
        }
      }
      
      if (modifierNames.length > 0) {
        fullName += ` - ${modifierNames.join(' | ')}`;
      }

      // Construir producto con modifiers
      const productData = {
        id: `${this.selectedProduct.id}_${Date.now()}`,
        name: fullName,
        price: parseFloat(totalPrice),
        quantity: 1,
        type: 'merchise_custom',
        base_product: {
          id: this.selectedProduct.id,
          name: this.selectedProduct.name,
          price: parseFloat(this.selectedProduct.price)
        },
        modifiers: []
      };

      // Agregar modifiers seleccionados
      for (let modifierId in this.selectedOptions) {
        const selections = this.selectedOptions[modifierId];
        if (selections.length > 0) {
          const modifier = this.modifiers.find(m => m.id == modifierId);
          productData.modifiers.push({
            id: modifier.id,
            name: modifier.name,
            options: selections
          });
        }
      }
      
      console.log('📦 Datos enviados:', productData);
      
      this.$emit('addMerchise', productData);

      if (this.$parent.$awn) {
        this.$parent.$awn.success('Producto con opciones agregado al carrito');
      }

      this.closeModal();
    }
  }
};
</script>

<style scoped>
/* Modal Base */
.test-merchise-modal {
  border-radius: 15px;
  border: none;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  background: #ffffff;
  overflow: hidden;
}

/* Header */
.test-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px 30px;
  border-radius: 15px 15px 0 0;
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
  width: 50px;
  height: 50px;
  background: rgba(255,255,255,0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.header-title {
  color: white;
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.btn-close-test {
  background: rgba(255,255,255,0.2);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  color: white;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-close-test:hover {
  background: rgba(255,255,255,0.3);
  transform: scale(1.1);
}

/* Body */
.test-body {
  padding: 20px 30px 100px 30px;
  min-height: 500px;
  max-height: 70vh;
  overflow-y: auto;
  background: #f8f9fa;
}

/* Products View */
.products-view {
  width: 100%;
  height: 100%;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 50px;
  color: #999;
}

.empty-state i {
  font-size: 60px;
  margin-bottom: 20px;
  opacity: 0.5;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
}

.product-card {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  min-height: 90px;
}

.product-card:hover {
  border-color: #667eea;
  box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
  transform: translateY(-2px);
}

.product-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  flex-shrink: 0;
}

.product-image {
  width: 60px;
  height: 60px;
  border-radius: 10px;
  overflow: hidden;
  flex-shrink: 0;
  background: #f5f5f5;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.product-name {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.product-description {
  margin: 0;
  font-size: 12px;
  color: #666;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
}

.product-price {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #667eea;
}

.product-arrow {
  color: #ccc;
  font-size: 18px;
}

/* Modifiers View */
.modifiers-view {
  width: 100%;
  height: 100%;
}

.btn-back {
  background: #f5f5f5;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  margin-bottom: 20px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.btn-back:hover {
  background: #e0e0e0;
}

.product-header-info {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 25px;
  color: white;
}

.product-header-info h4 {
  margin: 0 0 5px 0;
  font-size: 22px;
  font-weight: 600;
}

.base-price {
  margin: 0;
  opacity: 0.9;
  font-size: 16px;
}

.no-modifiers {
  text-align: center;
  padding: 50px;
}

.no-modifiers i {
  font-size: 50px;
  color: #999;
  margin-bottom: 15px;
}

/* Modifiers Container */
.modifiers-container {
  margin-bottom: 20px;
}

.modifier-group {
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.modifier-header {
  margin-bottom: 15px;
}

.modifier-name {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 5px 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.badge-required {
  background: #ff4757;
  color: white;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 5px;
  font-weight: 500;
}

.modifier-hint {
  margin: 0;
  color: #666;
  font-size: 14px;
}

/* Options List */
.options-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.option-item:hover {
  border-color: #667eea;
  background: #f8f9ff;
}

.option-item.selected {
  border-color: #667eea;
  background: #f0f3ff;
}

.option-check {
  font-size: 20px;
  color: #ccc;
}

.option-item.selected .option-check {
  color: #667eea;
}

.option-content {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.option-name {
  font-weight: 500;
  color: #333;
}

.option-price {
  color: #667eea;
  font-weight: 600;
}

.option-price-included {
  color: #666;
  font-weight: 600;
  font-size: 14px;
}

/* Footer */
.modifiers-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  padding: 20px 30px;
  border-top: 2px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 10;
  box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

.total-price {
  font-size: 20px;
}

.total-price strong {
  color: #667eea;
  font-size: 24px;
  margin-left: 10px;
}

.btn-add-cart {
  padding: 12px 30px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-add-cart:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}

/* Scrollbar */
.test-body::-webkit-scrollbar {
  width: 8px;
}

.test-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.test-body::-webkit-scrollbar-thumb {
  background: #667eea;
  border-radius: 10px;
}

.test-body::-webkit-scrollbar-thumb:hover {
  background: #5568d3;
}
</style>
