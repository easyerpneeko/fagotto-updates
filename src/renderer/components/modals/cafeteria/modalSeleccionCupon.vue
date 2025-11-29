<template>
  <div class="modal fade" id="modalSeleccionCupon" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content" style="background-color: white;">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="fas fa-utensils mr-2"></i>
            Selecciona tu Pasta y Salsa - GRATIS con Cupón
          </h5>
          <button type="button" class="close text-white" @click="closeModal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          <!-- Loading -->
          <div v-if="loading" class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-success"></i>
            <p class="mt-3">Cargando productos...</p>
          </div>

          <!-- Contenido principal -->
          <div v-else>
            <!-- Cupón usado -->
            <div class="alert alert-success mb-4">
              <i class="fas fa-check-circle mr-2"></i>
              <strong>Cupón válido:</strong> {{ cuponCodigo }} - Acceso autorizado
            </div>

            <!-- Selección de Categoría -->
            <div v-if="!selectedCategory" class="mb-4">
              <h6 class="font-weight-bold mb-3">
                <i class="fas fa-th-large mr-2"></i>
                Selecciona una Categoría
              </h6>
              
              <div v-if="categories.length === 0" class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No hay categorías disponibles
              </div>
              
              <div class="row">
                <div v-for="category in categories" :key="category.id" class="col-md-6 mb-3">
                  <div 
                    class="product-card category-card" 
                    @click="selectCategory(category)">
                    <div class="product-name">{{ category.name }}</div>
                    <div class="text-muted small">{{ category.product_count || 0 }} productos</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Selección de Producto -->
            <div v-if="selectedCategory" class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="font-weight-bold mb-0">
                  <i class="fas fa-utensils mr-2"></i>
                  {{ selectedCategory.name }}
                </h6>
                <button class="btn btn-sm btn-outline-secondary" @click="backToCategories">
                  <i class="fas fa-arrow-left mr-1"></i>
                  Cambiar categoría
                </button>
              </div>
              
              <div v-if="productos.length === 0" class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                No hay productos en esta categoría
              </div>
              
              <div class="row">
                <div v-for="producto in productos" :key="producto.id" class="col-md-6 mb-3">
                  <div 
                    class="product-card" 
                    :class="{ 'selected': selectedProducto && selectedProducto.id === producto.id }"
                    @click="selectProducto(producto)">
                    <div class="product-name">{{ producto.name }}</div>
                    <div class="product-price">${{ formatPrice(producto.price) }}</div>
                    <i v-if="selectedProducto && selectedProducto.id === producto.id" class="fas fa-check-circle selected-icon"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Resumen -->
            <div v-if="selectedProducto" class="alert alert-info">
              <h6 class="font-weight-bold mb-2">📝 Producto seleccionado:</h6>
              <p class="mb-1"><strong>{{ selectedProducto.name }}</strong></p>
              <p class="mb-0"><strong>Precio:</strong> <span class="text-primary font-weight-bold">${{ formatPrice(selectedProducto.price) }}</span></p>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">
            <i class="fas fa-times mr-1"></i>
            Cancelar
          </button>
          <button 
            type="button" 
            class="btn btn-success" 
            :disabled="!selectedProducto || loading"
            @click="confirmar">
            <i class="fas fa-check mr-1"></i>
            Agregar al Carrito
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      cuponCodigo: '',
      cuponId: null,
      categories: [],
      productos: [],
      selectedCategory: null,
      selectedProducto: null,
      loading: false
    }
  },
  computed: {
    totalPrice() {
      // Precio fijo de cupón: $2.990
      return 2990;
    }
  },
  methods: {
    async openModal(cuponData) {
      this.cuponCodigo = cuponData.codigo;
      this.cuponId = cuponData.cupon_id;
      this.selectedCategory = null;
      this.selectedProducto = null;
      
      $('#modalSeleccionCupon').modal('show');
      
      await this.loadCategories();
    },
    
    async loadCategories() {
      this.loading = true;
      try {
        console.log('📂 Cargando categorías desde Vuex store...');
        // Las categorías ya están en el store de Vuex
        let allCategories = this.$store.getters['products/categories'] || [];
        
        if (allCategories.length === 0) {
          console.warn('⚠️ No hay categorías, intentando cargar productos primero...');
          await this.$store.dispatch('products/getProductsOfSell2');
          allCategories = this.$store.getters['products/categories'] || [];
        }
        
        // Filtrar solo categorías 1 y 2 (Pastas Bigoli y Pastas Fetuccini)
        this.categories = allCategories.filter(cat => [1, 2].includes(cat.id));
        console.log('✅ Categorías filtradas (1 y 2):', this.categories.length);
        
      } catch (error) {
        console.error('❌ Error cargando categorías:', error);
        this.$awn.alert('Error al cargar las categorías');
      } finally {
        this.loading = false;
      }
    },
    
    async loadProducts(categoryId) {
      this.loading = true;
      try {
        console.log('🛒 Cargando productos de categoría', categoryId);
        const response = await this.$store.dispatch('products/getProductsOfSell2');
        
        if (response.success) {
          // Filtrar productos y aplicar precio de cupón ($2.990)
          this.productos = response.data
            .filter(product => product.category == categoryId)
            .map(product => ({
              ...product,
              precio_original: product.price, // Guardar precio original
              price: 2990 // Precio con cupón
            }));
          console.log('✅ Productos cargados con precio cupón ($2.990):', this.productos.length);
        } else {
          this.$awn.alert('Error al cargar los productos');
        }
      } catch (error) {
        console.error('❌ Error cargando productos:', error);
        this.$awn.alert('Error al cargar los productos');
      } finally {
        this.loading = false;
      }
    },
    
    async selectCategory(category) {
      this.selectedCategory = category;
      this.selectedProducto = null;
      await this.loadProducts(category.id);
    },
    
    backToCategories() {
      this.selectedCategory = null;
      this.selectedProducto = null;
      this.productos = [];
    },
    
    selectProducto(producto) {
      this.selectedProducto = producto;
      console.log('✅ Producto seleccionado:', producto.name);
    },
    
    confirmar() {
      if (!this.selectedProducto) {
        this.$awn.alert('Debes seleccionar un producto');
        return;
      }
      
      // Crear producto para el carrito con precio de cupón
      const producto = {
        id: `cupon_${this.selectedProducto.id}_${this.cuponId}`, // ID único para el frontend (evitar merge)
        product_id_real: this.selectedProducto.id, // ✅ ID real del producto para el backend
        name: `${this.selectedProducto.name} (Cupón ${this.cuponCodigo})`,
        price: '2990', // Precio como STRING para que quantityAdd lo use
        precio_original: this.selectedProducto.precio_original, // Guardar precio original
        quantity: 1,
        has_cupon: true, // ✅ Marca para identificar que este producto tiene cupón
        cupon_codigo: this.cuponCodigo,
        cupon_id: this.cuponId,
        categoria_nombre: this.selectedCategory.name,
        categoria_id: this.selectedCategory.id,
        producto_nombre: this.selectedProducto.name,
        producto_id: this.selectedProducto.id,
        category: this.selectedProducto.category,
        ganancia: 0, // Sin ganancia para productos con cupón
        prices: false, // Sin prices escalonados
        cecina: false,
        promo_price: null,
        LastVariantPrice: null
      };
      
      // Emitir evento al componente padre
      this.$emit('producto-seleccionado', producto);
      
      this.$awn.success(`${this.selectedProducto.name} agregado - $${this.formatPrice(this.totalPrice)}`);
      
      // Cerrar después de un pequeño delay para asegurar que quantityAdd procese
      setTimeout(() => {
        this.closeModal();
      }, 300);
    },
    
    closeModal() {
      $('#modalSeleccionCupon').modal('hide');
      this.selectedCategory = null;
      this.selectedProducto = null;
      this.productos = [];
    },
    
    formatPrice(price) {
      return new Intl.NumberFormat('es-CL').format(price);
    }
  }
}
</script>

<style scoped>
.product-card {
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  background: white;
}

.product-card:hover {
  border-color: #28a745;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
}

.product-card.selected {
  border-color: #28a745;
  background: #f0fdf4;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.product-name {
  font-weight: bold;
  font-size: 1.1rem;
  margin-bottom: 5px;
}

.product-price {
  color: #666;
  font-size: 0.9rem;
}

.selected-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  color: #28a745;
  font-size: 1.5rem;
}
</style>
