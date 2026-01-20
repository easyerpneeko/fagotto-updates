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

              <!-- Buscador -->
              <div v-show="!loading && products.length > 0" class="search-container">
                <div class="search-box">
                  <i class="fas fa-search search-icon"></i>
                  <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Buscar productos..."
                    class="search-input"
                  />
                  <button 
                    v-if="searchQuery" 
                    @click="searchQuery = ''"
                    class="clear-search">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div v-if="searchQuery && filteredProducts.length === 0" class="no-results">
                  <i class="fas fa-search-minus"></i>
                  <p>No se encontraron productos con "{{ searchQuery }}"</p>
                </div>
              </div>
              
              <div v-show="!loading && products.length === 0" class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>No hay productos disponibles</p>
              </div>
              
              <div v-show="!loading && products.length > 0 && filteredProducts.length > 0" class="products-grid">
                <!-- Agrupar por secciones -->
                <div v-for="sectionName in sectionNames" :key="sectionName" class="section-group">
                  <div 
                    class="section-header" 
                    :style="{ 
                      borderColor: getSectionColor(sectionName),
                      color: getSectionColor(sectionName)
                    }">
                    <i :class="getSectionIcon(sectionName)" class="section-icon"></i>
                    <span class="section-title">{{ sectionName }}</span>
                    <span class="section-count">({{ productsBySection[sectionName].length }})</span>
                  </div>
                  <div class="section-products">
                    <div 
                      v-for="product in productsBySection[sectionName]" 
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
                <div v-for="modifier in modifiers" :key="modifier.id + '-' + reactivityKey" class="modifier-group">
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
                      :class="['option-item', { 'selected': getOptionQuantity(modifier.id, option.id) > 0 }]">
                      <div class="option-content" @click="toggleOption(modifier, option)">
                        <div class="option-check">
                          <i :class="[
                            'fas', 
                            getOptionQuantity(modifier.id, option.id) > 0 ? 'fa-check-circle' : 'fa-circle'
                          ]"></i>
                          <span v-if="getOptionQuantity(modifier.id, option.id) > 1" class="qty-badge">
                            x{{ getOptionQuantity(modifier.id, option.id) }}
                          </span>
                        </div>
                        <div class="option-info">
                          <span class="option-name">
                            {{ option.name }}
                            <span v-if="getOptionQuantity(modifier.id, option.id) > 1" class="qty-inline">
                              (x{{ getOptionQuantity(modifier.id, option.id) }})
                            </span>
                          </span>
                          <span v-if="option.price > 0" class="option-price">+${{ formatNumber(option.price) }}</span>
                        </div>
                      </div>
                      
                      <!-- Selector de cantidad solo para max_selections > 1 -->
                      <div 
                        v-if="modifier.max_selections > 1 && getOptionQuantity(modifier.id, option.id) > 0" 
                        class="quantity-selector">
                        <button 
                          @click.stop="decreaseQuantity(modifier, option)" 
                          class="btn-qty"
                          :disabled="getOptionQuantity(modifier.id, option.id) <= 1">
                          <i class="fas fa-minus"></i>
                        </button>
                        <span class="qty-value">{{ getOptionQuantity(modifier.id, option.id) }}</span>
                        <button 
                          @click.stop="increaseQuantity(modifier, option)" 
                          class="btn-qty"
                          :disabled="getTotalSelections(modifier.id) >= modifier.max_selections">
                          <i class="fas fa-plus"></i>
                        </button>
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
      selectedOptions: {}, // { modifierId: { optionId: quantity } }
      reactivityKey: 0,
      searchQuery: ''
    };
  },
  computed: {
    // Filtrar productos por búsqueda
    filteredProducts() {
      if (!this.searchQuery) {
        return this.products;
      }
      const query = this.searchQuery.toLowerCase();
      return this.products.filter(product => {
        return product.name.toLowerCase().includes(query) ||
               (product.description && product.description.toLowerCase().includes(query));
      });
    },
    // Agrupar productos por sección
    productsBySection() {
      const grouped = {};
      this.filteredProducts.forEach(product => {
        // Intentar obtener section_name, si no existe, mapear por section_id
        let sectionName = product.section_name;
        
        if (!sectionName) {
          // Fallback: mapear por section_id
          const sectionMap = {
            1: 'Pasta',
            2: 'Combos',
            3: 'Para compartir',
            4: 'Sandwichis',
            5: 'Family Party'
          };
          sectionName = sectionMap[product.section_id] || 'Sin categoría';
        }
        
        if (!grouped[sectionName]) {
          grouped[sectionName] = [];
        }
        grouped[sectionName].push(product);
      });
      return grouped;
    },
    // Obtener nombres de secciones ordenadas
    sectionNames() {
      return Object.keys(this.productsBySection).sort((a, b) => {
        // Orden personalizado
        const order = ['Pasta', 'Combos', 'Para compartir', 'Sandwichis', 'Family Party'];
        return order.indexOf(a) - order.indexOf(b);
      });
    },
    // Obtener icono según categoría
    getSectionIcon() {
      return (sectionName) => {
        const icons = {
          'Pasta': 'fas fa-pizza-slice',
          'Combos': 'fas fa-box',
          'Para compartir': 'fas fa-users',
          'Sandwichis': 'fas fa-bread-slice',
          'Family Party': 'fas fa-gift'
        };
        return icons[sectionName] || 'fas fa-utensils';
      };
    },
    // Obtener color según categoría
    getSectionColor() {
      return (sectionName) => {
        const colors = {
          'Pasta': '#667eea',
          'Combos': '#f093fb',
          'Para compartir': '#4facfe',
          'Sandwichis': '#fa709a',
          'Family Party': '#feca57'
        };
        return colors[sectionName] || '#667eea';
      };
    }
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
        selectedProduct: this.selectedProduct,
        firstProduct: this.products[0]
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
          
          // Inicializar selectedOptions con estructura de cantidad
          this.selectedOptions = {};
          this.modifiers.forEach(modifier => {
            this.selectedOptions[modifier.id] = {}; // { optionId: quantity }
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
      console.log('🔄 toggleOption llamado:', { modifier: modifier.name, option: option.name });
      
      const modifierId = modifier.id;
      const optionId = option.id;
      
      if (!this.selectedOptions[modifierId]) {
        this.$set(this.selectedOptions, modifierId, {});
      }

      const currentQty = this.selectedOptions[modifierId][optionId] || 0;
      console.log('📊 Current qty:', currentQty);

      if (currentQty > 0) {
        // Si max_selections > 1, incrementar en vez de deseleccionar
        if (modifier.max_selections > 1) {
          const totalSelections = this.getTotalSelections(modifierId);
          const canIncrease = totalSelections < modifier.max_selections;
          
          if (canIncrease) {
            // Incrementar la cantidad de esta misma opción
            this.$set(this.selectedOptions[modifierId], optionId, currentQty + 1);
            this.reactivityKey++; // Forzar reactividad
            console.log('➕ Incrementado a:', currentQty + 1);
          } else {
            if (this.$parent.$awn) {
              this.$parent.$awn.warning(`Ya alcanzaste el máximo de ${modifier.max_selections} selecciones`);
            }
            console.log('⚠️ Máximo alcanzado, no se puede incrementar más');
          }
        } else {
          // Para max_selections = 1, deseleccionar
          this.$set(this.selectedOptions[modifierId], optionId, 0);
          this.reactivityKey++; // Forzar reactividad
          console.log('❌ Deseleccionado');
        }
      } else {
        // Seleccionar con cantidad 1
        const totalSelections = this.getTotalSelections(modifierId);
        console.log('📈 Total selections:', totalSelections, 'max:', modifier.max_selections);
        
        if (totalSelections >= modifier.max_selections) {
          if (modifier.max_selections === 1) {
            // Comportamiento radio: limpiar todas las opciones primero
            Object.keys(this.selectedOptions[modifierId]).forEach(key => {
              this.$set(this.selectedOptions[modifierId], key, 0);
            });
            // Luego seleccionar esta
            this.$set(this.selectedOptions[modifierId], optionId, 1);
            this.reactivityKey++; // Forzar reactividad
            console.log('✅ Seleccionado (radio mode)');
          } else {
            if (this.$parent.$awn) {
              this.$parent.$awn.warning(`Ya alcanzaste el máximo de ${modifier.max_selections} selecciones`);
            }
            console.log('⚠️ Máximo alcanzado');
            return;
          }
        } else {
          // Agregar con cantidad 1
          this.$set(this.selectedOptions[modifierId], optionId, 1);
          this.reactivityKey++; // Forzar reactividad
          console.log('✅ Seleccionado con cantidad 1');
        }
      }
      
      console.log('🔍 Selected options:', JSON.stringify(this.selectedOptions));
    },

    increaseQuantity(modifier, option) {
      const modifierId = modifier.id;
      const optionId = option.id;
      const currentQty = this.selectedOptions[modifierId][optionId] || 0;
      const totalSelections = this.getTotalSelections(modifierId);
      
      if (totalSelections >= modifier.max_selections) {
        if (this.$parent.$awn) {
          this.$parent.$awn.warning(`Máximo ${modifier.max_selections} selecciones`);
        }
        return;
      }
      
      this.$set(this.selectedOptions[modifierId], optionId, currentQty + 1);
      this.reactivityKey++; // Forzar reactividad
    },

    decreaseQuantity(modifier, option) {
      const modifierId = modifier.id;
      const optionId = option.id;
      const currentQty = this.selectedOptions[modifierId][optionId] || 0;
      
      if (currentQty > 1) {
        this.$set(this.selectedOptions[modifierId], optionId, currentQty - 1);
      } else {
        this.$set(this.selectedOptions[modifierId], optionId, 0);
      }
      this.reactivityKey++; // Forzar reactividad
    },

    getOptionQuantity(modifierId, optionId) {
      if (!this.selectedOptions[modifierId]) return 0;
      return this.selectedOptions[modifierId][optionId] || 0;
    },

    getTotalSelections(modifierId) {
      if (!this.selectedOptions[modifierId]) return 0;
      return Object.values(this.selectedOptions[modifierId]).reduce((sum, qty) => sum + qty, 0);
    },

    isOptionSelected(modifierId, optionId) {
      return this.getOptionQuantity(modifierId, optionId) > 0;
    },

    calculateTotal() {
      let total = parseFloat(this.selectedProduct.price);
      
      // Recorrer cada modifier y sumar precio * cantidad
      for (let modifierId in this.selectedOptions) {
        const modifier = this.modifiers.find(m => m.id == modifierId);
        if (!modifier) continue;
        
        for (let optionId in this.selectedOptions[modifierId]) {
          const quantity = this.selectedOptions[modifierId][optionId];
          if (quantity > 0) {
            const option = modifier.options.find(o => o.id == optionId);
            if (option) {
              total += parseFloat(option.price) * quantity;
            }
          }
        }
      }
      
      return total;
    },

    addToCartDirect() {
      console.log('🛒 Agregando producto merchise sin modifiers:', this.selectedProduct);
      
      const productData = {
        id: Date.now().toString(), // ID simple con timestamp
        name: this.selectedProduct.name,
        price: parseFloat(this.selectedProduct.price),
        quantity: 1,
        type: 'merchise_product',
        sku: this.selectedProduct.id, // Guardar SKU original
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
          const totalSelections = this.getTotalSelections(modifier.id);
          if (totalSelections < modifier.min_selections) {
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
        const modifier = this.modifiers.find(m => m.id == modifierId);
        if (!modifier) continue;
        
        const optionNames = [];
        for (let optionId in this.selectedOptions[modifierId]) {
          const quantity = this.selectedOptions[modifierId][optionId];
          if (quantity > 0) {
            const option = modifier.options.find(o => o.id == optionId);
            if (option) {
              if (quantity > 1) {
                optionNames.push(`${option.name} x${quantity}`);
              } else {
                optionNames.push(option.name);
              }
            }
          }
        }
        
        if (optionNames.length > 0) {
          modifierNames.push(optionNames.join(', '));
        }
      }
      
      if (modifierNames.length > 0) {
        fullName += ` - ${modifierNames.join(' | ')}`;
      }

      // Construir producto con modifiers
      const productData = {
        id: Date.now().toString(), // ID simple con timestamp
        name: fullName,
        price: parseFloat(totalPrice),
        quantity: 1,
        type: 'merchise_custom',
        sku: this.selectedProduct.id, // Guardar SKU original aquí
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
  padding: 20px 30px 150px 30px;
  min-height: 400px;
  max-height: 65vh;
  overflow-y: scroll !important;
  background: #f8f9fa;
}

/* Scrollbar personalizado */
.test-body::-webkit-scrollbar {
  width: 10px;
}

.test-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.test-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.test-body::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Products View */
.products-view {
  width: 100%;
  height: 100%;
}

/* Search Container */
.search-container {
  margin-bottom: 25px;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 15px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.search-box:focus-within {
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
  border-color: #667eea;
  transform: translateY(-2px);
}

.search-icon {
  position: absolute;
  left: 18px;
  color: #667eea;
  font-size: 18px;
  pointer-events: none;
  z-index: 1;
}

.search-input {
  width: 100%;
  padding: 15px 50px 15px 50px;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  background: transparent;
  outline: none;
  color: #333;
}

.search-input::placeholder {
  color: #999;
}

.clear-search {
  position: absolute;
  right: 15px;
  background: #f0f0f0;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #666;
}

.clear-search:hover {
  background: #667eea;
  color: white;
  transform: rotate(90deg);
}

.no-results {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.no-results i {
  font-size: 48px;
  margin-bottom: 15px;
  color: #ddd;
}

.no-results p {
  font-size: 16px;
  margin: 0;
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
  display: flex;
  flex-direction: column;
  gap: 30px;
}

/* Section Group */
.section-group {
  width: 100%;
  margin-bottom: 40px;
}

.section-header {
  font-size: 22px;
  font-weight: 700;
  margin: 0 0 20px 0;
  padding: 15px 20px;
  border-left: 5px solid;
  background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.6) 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
}

.section-header:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.section-icon {
  font-size: 26px;
  width: 45px;
  height: 45px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.8);
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
  flex: 1;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.section-count {
  font-size: 16px;
  font-weight: 600;
  opacity: 0.7;
  background: rgba(255,255,255,0.6);
  padding: 4px 12px;
  border-radius: 20px;
}

.section-products {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
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
  justify-content: space-between;
  gap: 12px;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
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

.option-content {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.option-check {
  font-size: 20px;
  color: #ccc;
  position: relative;
  display: flex;
  align-items: center;
  gap: 5px;
}

.qty-badge {
  background: #667eea;
  color: white;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  position: absolute;
  top: -8px;
  right: -12px;
}

.qty-inline {
  color: #667eea;
  font-weight: 700;
  font-size: 14px;
  margin-left: 5px;
}

.option-item.selected .option-check {
  color: #667eea;
}

.option-info {
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

/* Quantity Selector */
.quantity-selector {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f5f5f5;
  padding: 5px 10px;
  border-radius: 8px;
}

.btn-qty {
  width: 30px;
  height: 30px;
  border: none;
  background: #667eea;
  color: white;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  font-size: 12px;
}

.btn-qty:hover:not(:disabled) {
  background: #5568d3;
  transform: scale(1.05);
}

.btn-qty:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.5;
}

.qty-value {
  min-width: 25px;
  text-align: center;
  font-weight: 600;
  color: #667eea;
  font-size: 16px;
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
