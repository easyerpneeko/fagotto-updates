<template>
  <div>
    <!-- Modal principal de Gelateria -->
    <div class="modal fade" id="modalVentaCopas" tabindex="-1" role="dialog" aria-labelledby="modalVentaCopas"
      aria-hidden="true" data-backdrop="false">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content modern-gelateria">
          <!-- Header con diseño premium -->
          <div class="gelateria-header">
            <div class="header-bg-pattern"></div>
            <div class="header-content">
              <div class="header-left">
                <div class="logo-container">
                  <div class="logo-circle">
                    <i class="fas fa-ice-cream"></i>
                  </div>
                  <div class="logo-shine"></div>
                </div>
                <div class="header-info">
                  <h3 class="header-title">Gelateria</h3>
                  <p class="header-subtitle">{{ getSubtitle() }}</p>
                </div>
              </div>
              <button type="button" class="btn-close-modern" @click="closeModal" aria-label="Close">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Body con diseño moderno -->
          <div class="modal-body gelateria-body">
          
          <!-- Menú principal con tarjetas premium -->
          <div v-if="!selectedOption" class="main-menu">
            <div class="menu-grid">
              <div @click="selectOption('copas')" class="menu-card copas-card">
                <div class="card-gradient"></div>
                <div class="card-icon">
                  <i class="fas fa-ice-cream"></i>
                </div>
                <div class="card-content">
                  <h4 class="card-title">Copas</h4>
                  <p class="card-description">Deliciosos helados en copa</p>
                </div>
                <div class="card-arrow">
                  <i class="fas fa-arrow-right"></i>
                </div>
              </div>
              
              <div @click="selectOption('barquillos')" class="menu-card barquillos-card">
                <div class="card-gradient"></div>
                <div class="card-icon">
                  <i class="fas fa-cookie"></i>
                </div>
                <div class="card-content">
                  <h4 class="card-title">Barquillos</h4>
                  <p class="card-description">Crujientes barquillos artesanales</p>
                </div>
                <div class="card-arrow">
                  <i class="fas fa-arrow-right"></i>
                </div>
              </div>
              
              <div @click="selectOption('postres')" class="menu-card postres-card">
                <div class="card-gradient"></div>
                <div class="card-icon">
                  <i class="fas fa-birthday-cake"></i>
                </div>
                <div class="card-content">
                  <h4 class="card-title">Postres</h4>
                  <p class="card-description">Dulces y postres especiales</p>
                </div>
                <div class="card-arrow">
                  <i class="fas fa-arrow-right"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Vista de productos con diseño premium -->
          <div v-if="selectedOption === 'copas'" class="products-section">
            <button @click="goBack" class="btn-back-modern">
              <i class="fas fa-arrow-left"></i>
              <span>Volver al menú</span>
            </button>
            
            <div v-if="loading" class="loading-modern">
              <div class="spinner-modern"></div>
              <p>Cargando productos...</p>
            </div>
            
            <div v-else class="products-container">
              <div v-if="copaProducts && copaProducts.length > 0" class="products-grid-modern">
                <div v-for="product in copaProducts" :key="product.id" class="product-card-modern">
                  <div class="product-image">
                    <i class="fas fa-ice-cream"></i>
                  </div>
                  <div class="product-details">
                    <h5 class="product-name">{{ product.name }}</h5>
                    <p class="product-price">${{ formatNumber(product.price) }}</p>
                  </div>
                  <button @click="addProductToCart(product)" class="btn-add-modern">
                    <i class="fas fa-plus"></i>
                    <span>Agregar</span>
                  </button>
                </div>
              </div>
              
              <div v-else class="empty-modern">
                <i class="fas fa-box-open"></i>
                <p>No hay productos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Vista de Barquillos -->
          <div v-if="selectedOption === 'barquillos'" class="products-section">
            <button @click="goBack" class="btn-back-modern">
              <i class="fas fa-arrow-left"></i>
              <span>Volver al menú</span>
            </button>
            
            <div v-if="loading" class="loading-modern">
              <div class="spinner-modern"></div>
              <p>Cargando productos...</p>
            </div>
            
            <div v-else class="products-container">
              <div v-if="barquilloProducts && barquilloProducts.length > 0" class="products-grid-modern">
                <div v-for="product in barquilloProducts" :key="product.id" class="product-card-modern">
                  <div class="product-image">
                    <i class="fas fa-cookie"></i>
                  </div>
                  <div class="product-details">
                    <h5 class="product-name">{{ product.name }}</h5>
                    <p class="product-price">${{ formatNumber(product.price) }}</p>
                  </div>
                  <button @click="addProductToCart(product)" class="btn-add-modern">
                    <i class="fas fa-plus"></i>
                    <span>Agregar</span>
                  </button>
                </div>
              </div>
              
              <div v-else class="empty-modern">
                <i class="fas fa-box-open"></i>
                <p>No hay productos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Vista de Postres -->
          <div v-if="selectedOption === 'postres'" class="products-section">
            <button @click="goBack" class="btn-back-modern">
              <i class="fas fa-arrow-left"></i>
              <span>Volver al menú</span>
            </button>
            
            <div v-if="loading" class="loading-modern">
              <div class="spinner-modern"></div>
              <p>Cargando productos...</p>
            </div>
            
            <div v-else class="products-container">
              <div v-if="postreProducts && postreProducts.length > 0" class="products-grid-modern">
                <div v-for="product in postreProducts" :key="product.id" class="product-card-modern">
                  <div class="product-image">
                    <i class="fas fa-birthday-cake"></i>
                  </div>
                  <div class="product-details">
                    <h5 class="product-name">{{ product.name }}</h5>
                    <p class="product-price">${{ formatNumber(product.price) }}</p>
                  </div>
                  <button @click="addProductToCart(product)" class="btn-add-modern">
                    <i class="fas fa-plus"></i>
                    <span>Agregar</span>
                  </button>
                </div>
              </div>
              
              <div v-else class="empty-modern">
                <i class="fas fa-box-open"></i>
                <p>No hay productos disponibles</p>
              </div>
            </div>
          </div>

          <!-- Vista de Extras -->
          <div v-if="selectedOption === 'extras'" class="products-section">
            <button @click="goBack" class="btn-back-modern">
              <i class="fas fa-arrow-left"></i>
              <span>Volver al menú</span>
            </button>
            
            <div v-if="loading" class="loading-modern">
              <div class="spinner-modern"></div>
              <p>Cargando extras...</p>
            </div>
            
            <div v-else class="products-container">
              <div v-if="extrasProducts && extrasProducts.length > 0" class="products-grid-modern">
                <div v-for="product in extrasProducts" :key="product.id" class="product-card-modern">
                  <div class="product-image">
                    <i class="fas fa-candy-cane"></i>
                  </div>
                  <div class="product-details">
                    <h5 class="product-name">{{ product.name }}</h5>
                    <p class="product-price">${{ formatNumber(product.price) }}</p>
                  </div>
                  <button @click="addProductToCart(product)" class="btn-add-modern">
                    <i class="fas fa-plus"></i>
                    <span>Agregar</span>
                  </button>
                </div>
              </div>
              
              <div v-else class="empty-modern">
                <i class="fas fa-box-open"></i>
                <p>No hay extras disponibles</p>
              </div>
            </div>
          </div>

        </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación moderno -->
    <div v-if="showConfirmDialog" class="confirm-overlay" @click.self="cancelConfirm">
      <div class="confirm-dialog">
        <div class="confirm-icon-wrapper">
          <div class="confirm-icon">
            <i class="fas fa-candy-cane"></i>
          </div>
        </div>
        <h3 class="confirm-title">{{ confirmMessage }}</h3>
        <p class="confirm-description">{{ confirmDescription }}</p>
        <div class="confirm-buttons">
          <button @click="cancelConfirm" class="btn-confirm-cancel">
            <i class="fas fa-times"></i>
            <span>No, gracias</span>
          </button>
          <button @click="acceptConfirm" class="btn-confirm-accept">
            <i class="fas fa-check"></i>
            <span>Sí, agregar</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import FormatNumber from '@/helpers/FormatNumber.js';

export default {
  name: 'VentaCopas',
  data() {
    return {
      selectedOption: null, // 'copas', 'barquillos', 'postres', 'extras'
      loading: false,
      copaProducts: [], // Productos de la categoría correspondiente
      barquilloProducts: [], // Productos de barquillos
      postreProducts: [], // Productos de postres
      extrasProducts: [], // Productos de extras (categoría 58)
      selectedSize: null,
      selectedFlavors: [],
      // Modal de confirmación moderno
      showConfirmDialog: false,
      confirmMessage: '',
      confirmDescription: '',
      confirmResolve: null,
      // Modal de confirmación personalizado
      showConfirmDialog: false,
      confirmMessage: '',
      confirmDescription: '',
      confirmResolve: null,
      copaSizes: [
        {
          id: 1,
          name: 'Copa Pequeña',
          icon: 'fas fa-ice-cream',
          price: 2500,
          flavors: 1
        },
        {
          id: 2,
          name: 'Copa Mediana',
          icon: 'fas fa-ice-cream',
          price: 3500,
          flavors: 2
        },
        {
          id: 3,
          name: 'Copa Grande',
          icon: 'fas fa-ice-cream',
          price: 4500,
          flavors: 3
        }
      ],
      availableFlavors: [
        { id: 1, name: 'Chocolate', color: '#8B4513' },
        { id: 2, name: 'Vainilla', color: '#F5DEB3' },
        { id: 3, name: 'Fresa', color: '#FF69B4' },
        { id: 4, name: 'Menta', color: '#98FB98' },
        { id: 5, name: 'Limón', color: '#FFFF00' },
        { id: 6, name: 'Frambuesa', color: '#E30B5C' },
        { id: 7, name: 'Pistacho', color: '#93C572' },
        { id: 8, name: 'Dulce de Leche', color: '#D2691E' },
        { id: 9, name: 'Cookies & Cream', color: '#F0F0F0' },
        { id: 10, name: 'Mango', color: '#FFA500' },
        { id: 11, name: 'Maracuyá', color: '#FFD700' },
        { id: 12, name: 'Café', color: '#6F4E37' }
      ]
    }
  },
  computed: {
    canAddCopa() {
      return this.selectedSize && this.selectedFlavors.length === this.selectedSize.flavors;
    }
  },
  methods: {
    // Seleccionar opción del menú principal
    selectOption(option) {
      this.selectedOption = option;
      if (option === 'copas') {
        this.loadCopaProducts();
      } else if (option === 'barquillos') {
        this.loadBarquilloProducts();
      } else if (option === 'postres') {
        this.loadPostreProducts();
      }
    },

    // Volver al menú principal
    goBack() {
      this.selectedOption = null;
      this.copaProducts = [];
      this.barquilloProducts = [];
      this.postreProducts = [];
      this.extrasProducts = [];
    },

    // Cargar productos de la categoría ID 55 (Copas)
    async loadCopaProducts() {
      console.log('🚀 Iniciando carga de productos de Copas...');
      this.loading = true;
      try {
        const response = await this.$store.dispatch('products/getProductsOfSell2');
        console.log('📦 Respuesta del store:', response);
        
        if (response.success) {
          // Filtrar productos de la categoría 55 (Copas) - MISMO PATRÓN QUE catalog.vue
          const filteredProducts = response.data.filter(product => {
            if (product.category == 55) {
              return product;
            }
            return false;
          });
          
          // Usar $set para forzar reactividad
          this.$set(this, 'copaProducts', filteredProducts);
          
          console.log('🍦 Productos de Copas (categoría 55):', this.copaProducts);
          console.log('📋 Total productos cargados:', response.data.length);
          console.log('✅ Loading set to false');
        } else {
          console.error('❌ Response.success es false');
        }
      } catch (error) {
        console.error('❌ Error cargando productos de copas:', error);
        this.$awn.alert('Error al cargar los productos');
      } finally {
        this.loading = false;
        console.log('🏁 Finally ejecutado - loading:', this.loading);
        console.log('🏁 copaProducts.length:', this.copaProducts.length);
        // Forzar actualización de Vue
        this.$forceUpdate();
      }
    },

    // Cargar productos de la categoría ID 56 (Barquillos)
    async loadBarquilloProducts() {
      console.log('🚀 Iniciando carga de productos de Barquillos...');
      this.loading = true;
      try {
        const response = await this.$store.dispatch('products/getProductsOfSell2');
        console.log('📦 Respuesta del store:', response);
        
        if (response.success) {
          // Filtrar productos de la categoría 56 (Barquillos)
          const filteredProducts = response.data.filter(product => {
            if (product.category == 56) {
              return product;
            }
            return false;
          });
          
          // Usar $set para forzar reactividad
          this.$set(this, 'barquilloProducts', filteredProducts);
          
          console.log('🍪 Productos de Barquillos (categoría 56):', this.barquilloProducts);
          console.log('📋 Total productos cargados:', response.data.length);
          console.log('✅ Loading set to false');
        } else {
          console.error('❌ Response.success es false');
        }
      } catch (error) {
        console.error('❌ Error cargando productos de barquillos:', error);
        this.$awn.alert('Error al cargar los productos');
      } finally {
        this.loading = false;
        console.log('🏁 Finally ejecutado - loading:', this.loading);
        console.log('🏁 barquilloProducts.length:', this.barquilloProducts.length);
        // Forzar actualización de Vue
        this.$forceUpdate();
      }
    },

    // Cargar productos de la categoría ID 57 (Postres)
    async loadPostreProducts() {
      console.log('🚀 Iniciando carga de productos de Postres...');
      this.loading = true;
      try {
        const response = await this.$store.dispatch('products/getProductsOfSell2');
        console.log('📦 Respuesta del store:', response);
        
        if (response.success) {
          // Filtrar productos de la categoría 57 (Postres)
          const filteredProducts = response.data.filter(product => {
            if (product.category == 57) {
              return product;
            }
            return false;
          });
          
          // Usar $set para forzar reactividad
          this.$set(this, 'postreProducts', filteredProducts);
          
          console.log('🎂 Productos de Postres (categoría 57):', this.postreProducts);
          console.log('📋 Total productos cargados:', response.data.length);
          console.log('✅ Loading set to false');
        } else {
          console.error('❌ Response.success es false');
        }
      } catch (error) {
        console.error('❌ Error cargando productos de postres:', error);
        this.$awn.alert('Error al cargar los productos');
      } finally {
        this.loading = false;
        console.log('🏁 Finally ejecutado - loading:', this.loading);
        console.log('🏁 postreProducts.length:', this.postreProducts.length);
        // Forzar actualización de Vue
        this.$forceUpdate();
      }
    },

    // Agregar producto del catálogo al carrito
    addProductToCart(product) {
      console.log('🛒 Agregando producto al carrito:', product);
      
      const productData = {
        id: product.id,
        name: product.name,
        price: parseFloat(product.price),
        quantity: 1,
        type: 'copa_producto',
        promo_price: product.promo_price ? parseFloat(product.promo_price) : null,
        prices: product.prices || [],
        cecina: product.cecina || false,
        ganancia: product.ganancia || 0,
        category: product.category || null
      };
      
      console.log('📦 Datos enviados:', productData);

      this.$emit('addCopa', productData);
      this.$awn.success(`${product.name} agregado al carrito`);
      
      // Preguntar si quiere agregar extras para Copas, Barquillos y Postres
      if (this.selectedOption === 'copas' || this.selectedOption === 'barquillos' || this.selectedOption === 'postres') {
        this.$nextTick(() => {
          setTimeout(async () => {
            const wantsExtras = await this.showConfirm(
              '¿Deseas agregar extras?',
              'Puedes personalizar tu pedido con deliciosos complementos'
            );
            if (wantsExtras) {
              this.showExtras();
            } else {
              // Si no quiere extras, cerrar el modal
              this.closeModal();
            }
          }, 500);
        });
      } else if (this.selectedOption === 'extras') {
        // Si está agregando extras, preguntar si quiere más extras
        this.$nextTick(() => {
          setTimeout(async () => {
            const wantsMore = await this.showConfirm(
              '¿Deseas agregar más extras?',
              'Puedes seguir agregando complementos a tu pedido'
            );
            if (!wantsMore) {
              // Si no quiere más extras, cerrar el modal
              this.closeModal();
            }
          }, 500);
        });
      } else {
        // Cerrar el modal
        this.closeModal();
      }
    },

    // Mostrar extras de la categoría 58
    showExtras() {
      this.selectedOption = 'extras';
      this.loadExtrasProducts();
    },

    // Cargar productos de la categoría ID 58 (Extras)
    async loadExtrasProducts() {
      this.loading = true;
      try {
        const response = await this.$store.dispatch('products/getProductsOfSell2');
        if (response.success) {
          this.extrasProducts = response.data.filter(product => {
            if (product.category == 58) {
              return product;
            }
            return false;
          });
          console.log('🍓 Productos de Extras (categoría 58):', this.extrasProducts);
        }
      } catch (error) {
        console.error('Error cargando productos de extras:', error);
        this.$awn.alert('Error al cargar los productos');
      } finally {
        this.loading = false;
      }
    },
    
    selectSize(size) {
      this.selectedSize = size;
      this.selectedFlavors = [];
    },
    
    toggleFlavor(flavor) {
      const index = this.selectedFlavors.findIndex(f => f.id === flavor.id);
      if (index > -1) {
        this.selectedFlavors.splice(index, 1);
      } else {
        if (this.selectedFlavors.length < this.selectedSize.flavors) {
          this.selectedFlavors.push(flavor);
        }
      }
    },
    
    isFlavorSelected(flavor) {
      return this.selectedFlavors.some(f => f.id === flavor.id);
    },
    
    isFlavorSelectable(flavor) {
      if (!this.selectedSize) return false;
      if (this.isFlavorSelected(flavor)) return true;
      return this.selectedFlavors.length < this.selectedSize.flavors;
    },
    
    addCopa() {
      if (!this.canAddCopa) return;
      
      const copaProduct = {
        name: `${this.selectedSize.name} - ${this.selectedFlavors.map(f => f.name).join(', ')}`,
        price: this.selectedSize.price,
        quantity: 1,
        type: 'copa_gelato',
        size: this.selectedSize.name,
        flavors: this.selectedFlavors.map(f => f.name)
      };
      
      this.$emit('addCopa', copaProduct);
      this.resetForm();
      this.closeModal();
      $('#modalCatalog').modal('show');
    },
    
    resetForm() {
      this.selectedOption = null;
      this.selectedSize = null;
      this.selectedFlavors = [];
      this.copaProducts = [];
      this.barquilloProducts = [];
      this.postreProducts = [];
      this.extrasProducts = [];
    },
    
    closeModal() {
      this.resetForm();
      $('#modalVentaCopas').modal('hide');
    },
    
    getSubtitle() {
      const subtitles = {
        'copas': 'Deliciosos helados en copa',
        'barquillos': 'Crujientes barquillos artesanales',
        'postres': 'Dulces y postres especiales',
        'extras': 'Complementos y extras'
      };
      return subtitles[this.selectedOption] || 'Selecciona una categoría';
    },
    
    // Métodos del modal de confirmación moderno
    showConfirm(message, description = '') {
      return new Promise((resolve) => {
        this.confirmMessage = message;
        this.confirmDescription = description;
        this.confirmResolve = resolve;
        this.showConfirmDialog = true;
      });
    },

    acceptConfirm() {
      this.showConfirmDialog = false;
      if (this.confirmResolve) {
        this.confirmResolve(true);
        this.confirmResolve = null;
      }
    },

    cancelConfirm() {
      this.showConfirmDialog = false;
      if (this.confirmResolve) {
        this.confirmResolve(false);
        this.confirmResolve = null;
      }
    },

    formatNumber(number) {
      return FormatNumber.format(number);
    }
  }
}
</script>

<style scoped>
/* ============================================
   DISEÑO MODERNO PREMIUM - GELATERIA 2025
   ============================================ */

/* Modal Container */
.modern-gelateria {
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: none;
  background: #ffffff;
}

.modal-xl {
  max-width: 1200px;
}

/* ============ HEADER PREMIUM ============ */
.gelateria-header {
  position: relative;
  background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 50%, #EC4899 100%);
  padding: 45px 50px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.header-bg-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    radial-gradient(circle at 25% 35%, rgba(255, 255, 255, 0.2) 0%, transparent 55%),
    radial-gradient(circle at 75% 70%, rgba(255, 255, 255, 0.15) 0%, transparent 60%),
    url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M0 0h40v40H0V0zm40 40h40v40H40V40z'/%3E%3C/g%3E%3C/svg%3E");
  animation: patternMove 30s ease-in-out infinite;
}

@keyframes patternMove {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(-15px, -15px) scale(1.05); }
}

.header-content {
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 2;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 24px;
}

.logo-container {
  position: relative;
}

.logo-circle {
  width: 90px;
  height: 90px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(20px);
  border-radius: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid rgba(255, 255, 255, 0.35);
  position: relative;
  z-index: 2;
  box-shadow: 
    0 15px 35px rgba(0, 0, 0, 0.2),
    inset 0 2px 4px rgba(255, 255, 255, 0.3);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.logo-circle:hover {
  transform: scale(1.08) rotate(-6deg);
}

.logo-circle i {
  font-size: 42px;
  color: #ffffff;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.25));
}

.logo-shine {
  position: absolute;
  top: -10px;
  left: -10px;
  right: -10px;
  bottom: -10px;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  border-radius: 32px;
  animation: shine 4s ease-in-out infinite;
  z-index: 1;
  pointer-events: none;
}

@keyframes shine {
  0%, 100% { opacity: 0; transform: translateX(-120%) rotate(45deg); }
  50% { opacity: 1; transform: translateX(120%) rotate(45deg); }
}

.header-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.header-title {
  font-size: 38px;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  letter-spacing: -1.2px;
  text-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
  line-height: 1.1;
}

.header-subtitle {
  font-size: 18px;
  color: rgba(255, 255, 255, 0.95);
  margin: 0;
  font-weight: 600;
  letter-spacing: 0.3px;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.btn-close-modern {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 20px;
}

.btn-close-modern:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.05) rotate(90deg);
}

/* ============ BODY ============ */
.gelateria-body {
  padding: 40px;
  background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
  min-height: 500px;
}

/* ============ MENU PRINCIPAL - TARJETAS PREMIUM ============ */
.main-menu {
  width: 100%;
}

.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
  margin-top: 20px;
}

.menu-card {
  position: relative;
  background: #ffffff;
  border-radius: 20px;
  padding: 32px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  overflow: hidden;
  border: 2px solid transparent;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.menu-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border-color: var(--card-color);
}

.card-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: var(--card-gradient);
  opacity: 0.8;
  transition: height 0.3s ease;
}

.menu-card:hover .card-gradient {
  height: 100%;
  opacity: 0.15;
}

.copas-card {
  --card-color: #6366F1;
  --card-color-light: rgba(99, 102, 241, 0.15);
  --card-gradient: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
}

.barquillos-card {
  --card-color: #EC4899;
  --card-color-light: rgba(236, 72, 153, 0.15);
  --card-gradient: linear-gradient(135deg, #EC4899 0%, #F59E0B 100%);
}

.postres-card {
  --card-color: #10B981;
  --card-color-light: rgba(16, 185, 129, 0.15);
  --card-gradient: linear-gradient(135deg, #10B981 0%, #06B6D4 100%);
}

.card-icon {
  width: 96px;
  height: 96px;
  background: var(--card-gradient);
  border-radius: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
  box-shadow: 
    0 12px 32px rgba(0, 0, 0, 0.18),
    inset 0 2px 4px rgba(255, 255, 255, 0.25);
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.card-icon::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 28px;
  padding: 2px;
  background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask-composite: exclude;
}

.menu-card:hover .card-icon {
  transform: scale(1.2) rotate(-10deg);
  box-shadow: 
    0 20px 48px rgba(0, 0, 0, 0.25),
    inset 0 2px 4px rgba(255, 255, 255, 0.35);
}

.card-icon i {
  font-size: 48px;
  color: #ffffff;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
}

.card-content {
  margin-bottom: 16px;
}

.card-title {
  font-size: 28px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 10px 0;
  letter-spacing: -0.5px;
  line-height: 1.2;
}

.card-description {
  font-size: 16px;
  color: #64748B;
  margin: 0;
  line-height: 1.6;
  font-weight: 500;
}

.card-arrow {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  color: var(--card-color);
  font-size: 20px;
  opacity: 0;
  transform: translateX(-10px);
  transition: all 0.3s ease;
}

.menu-card:hover .card-arrow {
  opacity: 1;
  transform: translateX(0);
}

/* ============ PRODUCTOS - DISEÑO MODERNO ============ */
.products-section {
  animation: fadeIn 0.4s ease;
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

.btn-back-modern {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 24px;
  background: #ffffff;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  color: #4a5568;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 32px;
}

.btn-back-modern:hover {
  background: #667eea;
  border-color: #667eea;
  color: #ffffff;
  transform: translateX(-4px);
}

.btn-back-modern i {
  transition: transform 0.3s ease;
}

.btn-back-modern:hover i {
  transform: translateX(-4px);
}

/* Loading moderno */
.loading-modern {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 24px;
}

.spinner-modern {
  width: 70px;
  height: 70px;
  border: 5px solid #e2e8f0;
  border-top-color: #6366F1;
  border-right-color: #8B5CF6;
  border-radius: 50%;
  animation: spin 0.9s cubic-bezier(0.6, 0, 0.4, 1) infinite;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.2);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-modern p {
  font-size: 16px;
  color: #718096;
  font-weight: 500;
}

/* Grid de productos moderno */
.products-container {
  width: 100%;
}

.products-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  animation: fadeIn 0.5s ease;
}

.product-card-modern {
  background: #ffffff;
  border-radius: 24px;
  padding: 32px;
  border: 2px solid #f1f5f9;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  gap: 20px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.product-card-modern::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, #6366F1 0%, #8B5CF6 50%, #EC4899 100%);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-card-modern:hover {
  border-color: #6366F1;
  box-shadow: 
    0 20px 50px rgba(99, 102, 241, 0.2),
    0 0 0 3px rgba(99, 102, 241, 0.1);
  transform: translateY(-10px) scale(1.02);
}

.product-card-modern:hover::before {
  transform: scaleX(1);
}

.product-image {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
  transition: transform 0.3s ease;
}

.product-card-modern:hover .product-image {
  transform: scale(1.1) rotate(-5deg);
}

.product-image i {
  font-size: 40px;
  color: #ffffff;
}

.product-details {
  text-align: center;
  flex: 1;
}

.product-name {
  font-size: 18px;
  font-weight: 700;
  color: #1a202c;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.product-price {
  font-size: 24px;
  font-weight: 800;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.btn-add-modern {
  width: 100%;
  padding: 14px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 12px;
  color: #ffffff;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-add-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-add-modern:active {
  transform: translateY(0);
}

.btn-add-modern i {
  font-size: 16px;
}

/* Empty state moderno */
.empty-modern {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 20px;
}

.empty-modern i {
  font-size: 80px;
  color: #cbd5e0;
}

.empty-modern p {
  font-size: 18px;
  color: #718096;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .gelateria-header {
    padding: 24px 20px;
  }
  
  .header-title {
    font-size: 24px;
  }
  
  .header-subtitle {
    font-size: 13px;
  }
  
  .logo-circle {
    width: 56px;
    height: 56px;
  }
  
  .logo-circle i {
    font-size: 28px;
  }
  
  .gelateria-body {
    padding: 24px 16px;
  }
  
  .menu-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .products-grid-modern {
    grid-template-columns: 1fr;
  }
  
  .modal-xl {
    max-width: 100%;
    margin: 10px;
  }
}

/* Animaciones suaves */
* {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* ============================================
   MODAL DE CONFIRMACIÓN MODERNO
   ============================================ */
.confirm-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  animation: fadeIn 0.25s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.confirm-dialog {
  background: #ffffff;
  border-radius: 32px;
  padding: 48px 40px;
  max-width: 480px;
  width: 90%;
  box-shadow: 
    0 30px 80px rgba(0, 0, 0, 0.3),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  animation: slideUp 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(40px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.confirm-icon-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 28px;
}

.confirm-icon {
  width: 96px;
  height: 96px;
  background: linear-gradient(135deg, #EC4899 0%, #F59E0B 100%);
  border-radius: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 
    0 12px 40px rgba(236, 72, 153, 0.35),
    inset 0 2px 4px rgba(255, 255, 255, 0.25);
  animation: iconBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes iconBounce {
  0% {
    transform: scale(0) rotate(-45deg);
    opacity: 0;
  }
  50% {
    transform: scale(1.15) rotate(5deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

.confirm-icon i {
  font-size: 48px;
  color: #ffffff;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.25));
}

.confirm-title {
  font-size: 28px;
  font-weight: 800;
  color: #0F172A;
  text-align: center;
  margin: 0 0 16px 0;
  letter-spacing: -0.5px;
  line-height: 1.2;
}

.confirm-description {
  font-size: 17px;
  color: #64748B;
  text-align: center;
  margin: 0 0 36px 0;
  line-height: 1.6;
  font-weight: 500;
}

.confirm-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.btn-confirm-cancel,
.btn-confirm-accept {
  padding: 18px 24px;
  border-radius: 16px;
  border: none;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  letter-spacing: 0.2px;
}

.btn-confirm-cancel {
  background: #F1F5F9;
  color: #475569;
  border: 2px solid #E2E8F0;
}

.btn-confirm-cancel:hover {
  background: #E2E8F0;
  border-color: #CBD5E1;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.btn-confirm-cancel:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.btn-confirm-accept {
  background: linear-gradient(135deg, #EC4899 0%, #F59E0B 100%);
  color: #ffffff;
  box-shadow: 
    0 6px 20px rgba(236, 72, 153, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.25);
  position: relative;
  overflow: hidden;
}

.btn-confirm-accept::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
  transition: left 0.6s;
}

.btn-confirm-accept:hover::before {
  left: 100%;
}

.btn-confirm-accept:hover {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 
    0 12px 32px rgba(236, 72, 153, 0.45),
    inset 0 1px 0 rgba(255, 255, 255, 0.35);
}

.btn-confirm-accept:active {
  transform: translateY(0) scale(0.98);
  box-shadow: 
    0 4px 16px rgba(236, 72, 153, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.25);
}

.btn-confirm-cancel i,
.btn-confirm-accept i {
  font-size: 18px;
}

/* Responsive para modal de confirmación */
@media (max-width: 768px) {
  .confirm-dialog {
    padding: 36px 24px;
    max-width: 90%;
  }

  .confirm-icon {
    width: 80px;
    height: 80px;
  }

  .confirm-icon i {
    font-size: 40px;
  }

  .confirm-title {
    font-size: 24px;
  }

  .confirm-description {
    font-size: 15px;
  }

  .confirm-buttons {
    grid-template-columns: 1fr;
  }

  .btn-confirm-cancel,
  .btn-confirm-accept {
    padding: 16px 20px;
  }
}
</style>
