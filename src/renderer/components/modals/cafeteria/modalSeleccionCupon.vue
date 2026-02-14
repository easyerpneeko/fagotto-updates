<template>
  <div class="modal fade" id="modalSeleccionCupon" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content" style="background-color: white;">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="fas fa-utensils mr-2"></i>
            2x1 en Pastas - Segunda GRATIS con Cupón
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
              <strong>Cupón válido:</strong> {{ cuponCodigo }} - 2x1 en Pastas (Segunda GRATIS)
            </div>

            <!-- Indicador de paso -->
            <div class="alert alert-info mb-3">
              <strong>Paso {{ currentStep }}/2:</strong> 
              <span v-if="currentStep === 1">Selecciona la PRIMERA pasta (precio normal)</span>
              <span v-if="currentStep === 2">Selecciona la SEGUNDA pasta (GRATIS)</span>
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
                    :class="{ 
                      'selected': (currentStep === 1 && selectedProducto1 && selectedProducto1.id === producto.id) || 
                                  (currentStep === 2 && selectedProducto2 && selectedProducto2.id === producto.id)
                    }"
                    @click="selectProducto(producto)">
                    <div class="product-name">{{ producto.name }}</div>
                    <div class="product-price">${{ formatPrice(producto.price) }}</div>
                    <i v-if="(currentStep === 1 && selectedProducto1 && selectedProducto1.id === producto.id) || 
                             (currentStep === 2 && selectedProducto2 && selectedProducto2.id === producto.id)" 
                       class="fas fa-check-circle selected-icon"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- Resumen -->
            <div v-if="selectedProducto1 || selectedProducto2" class="alert alert-info">
              <h6 class="font-weight-bold mb-2">📝 Productos seleccionados:</h6>
              
              <!-- Primera pasta -->
              <div v-if="selectedProducto1" class="mb-2 pb-2 border-bottom">
                <p class="mb-1"><strong>1️⃣ {{ selectedProducto1.name }}</strong></p>
                <p class="mb-0"><strong>Precio:</strong> <span class="text-primary font-weight-bold">${{ formatPrice(selectedProducto1.price) }}</span></p>
              </div>
              
              <!-- Segunda pasta -->
              <div v-if="selectedProducto2" class="mb-2">
                <p class="mb-1"><strong>2️⃣ {{ selectedProducto2.name }}</strong></p>
                <p class="mb-0"><strong>Precio:</strong> <span class="text-success font-weight-bold">GRATIS 🎉</span></p>
              </div>
              
              <!-- Total -->
              <div v-if="selectedProducto1" class="mt-3 pt-2 border-top">
                <p class="mb-0 h5"><strong>TOTAL:</strong> <span class="text-primary">${{ formatPrice(selectedProducto1.price) }}</span></p>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">
            <i class="fas fa-times mr-1"></i>
            Cancelar
          </button>
          <button 
            v-if="currentStep === 1"
            type="button" 
            class="btn btn-primary" 
            :disabled="!selectedProducto1 || loading"
            @click="nextStep">
            <i class="fas fa-arrow-right mr-1"></i>
            Continuar (Elegir 2da Pasta)
          </button>
          <button 
            v-if="currentStep === 2"
            type="button" 
            class="btn btn-success" 
            :disabled="!selectedProducto2 || loading"
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
import Connection from '@/helpers/Connection';
import ConfigHelper from '@/helpers/ConfigHelper';
import BaseUrl from '@/helpers/baseUrl';

export default {
  data() {
    return {
      cuponCodigo: '',
      cuponId: null,
      categories: [],
      productos: [],
      selectedCategory: null,
      selectedProducto1: null, // Primera pasta (con precio)
      selectedProducto2: null, // Segunda pasta (gratis)
      currentStep: 1, // Paso actual (1 o 2)
      loading: false
    }
  },
  computed: {
    totalPrice() {
      // Total = precio de la primera pasta solamente
      return this.selectedProducto1 ? this.selectedProducto1.price : 0;
    }
  },
  methods: {
    async openModal(cuponData) {
      this.cuponCodigo = cuponData.codigo;
      this.cuponId = cuponData.cupon_id;
      this.selectedCategory = null;
      this.selectedProducto1 = null;
      this.selectedProducto2 = null;
      this.currentStep = 1;
      
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
        console.log('🛒 Cargando productos de categoría', categoryId, '- Paso:', this.currentStep);
        const response = await this.$store.dispatch('products/getProductsOfSell2');
        
        if (response.success) {
          // Filtrar productos de la categoría
          let productosFiltrados = response.data.filter(product => product.category == categoryId);
          
          // ✅ FILTRO SOLO EN PASO 2 (segunda pasta gratis): Solo Boloñesa, Alfredo y Cheddar
          if (this.currentStep === 2) {
            const permitidos = ['boloñesa', 'bolonesa', 'alfredo', 'cheddar'];
            
            productosFiltrados = productosFiltrados.filter(product => {
              const nombreLower = product.name.toLowerCase();
              return permitidos.some(tipo => nombreLower.includes(tipo));
            });
            
            console.log('✅ Paso 2 - Productos filtrados (Boloñesa/Alfredo/Cheddar):', productosFiltrados.length);
          } else {
            console.log('✅ Paso 1 - Todos los productos de la categoría:', productosFiltrados.length);
          }
          
          this.productos = productosFiltrados;
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
      // No resetear productos ya seleccionados al cambiar categoría
      await this.loadProducts(category.id);
    },
    
    backToCategories() {
      this.selectedCategory = null;
      this.productos = [];
    },
    
    selectProducto(producto) {
      if (this.currentStep === 1) {
        this.selectedProducto1 = producto;
        console.log('✅ Primera pasta seleccionada:', producto.name, '- Precio:', producto.price);
      } else if (this.currentStep === 2) {
        this.selectedProducto2 = producto;
        console.log('✅ Segunda pasta seleccionada (GRATIS):', producto.name);
      }
    },
    
    nextStep() {
      if (!this.selectedProducto1) {
        this.$awn.alert('Debes seleccionar la primera pasta');
        return;
      }
      
      // Avanzar al paso 2
      this.currentStep = 2;
      this.selectedCategory = null;
      this.productos = [];
      
      console.log('➡️ Paso 2: Selecciona segunda pasta GRATIS');
    },
    
    async confirmar() {
      if (!this.selectedProducto1 || !this.selectedProducto2) {
        this.$awn.alert('Debes seleccionar ambas pastas');
        return;
      }

      // Marcar el cupón como usado ANTES de agregar al carrito
      const cuponMarcado = await this.marcarCuponUsado();
      if (!cuponMarcado) {
        this.$awn.alert('❌ Error al marcar el cupón como usado. Intenta nuevamente.');
        return;
      }
      
      // ✅ PRIMERA PASTA - Precio normal
      const producto1 = {
        id: `cupon_${this.selectedProducto1.id}_${this.cuponId}_1`, // ID único
        product_id_real: this.selectedProducto1.id, // ID real para backend
        name: `${this.selectedProducto1.name} (Cupón ${this.cuponCodigo} - 1/2)`,
        price: this.selectedProducto1.price.toString(), // Precio como STRING
        quantity: 1,
        has_cupon: true, // Marca que tiene cupón
        cupon_codigo: this.cuponCodigo,
        cupon_id: this.cuponId,
        category: this.selectedProducto1.category,
        ganancia: 0,
        prices: false,
        cecina: false,
        promo_price: null,
        LastVariantPrice: null
      };
      
      // ✅ SEGUNDA PASTA - GRATIS ($0)
      const producto2 = {
        id: `cupon_${this.selectedProducto2.id}_${this.cuponId}_2`, // ID único
        product_id_real: this.selectedProducto2.id, // ID real para backend
        name: `${this.selectedProducto2.name} (Cupón ${this.cuponCodigo} - 2/2 GRATIS)`,
        price: '0', // ✅ PRECIO $0
        precio_original: this.selectedProducto2.price, // Guardar precio original
        quantity: 1,
        has_cupon: true,
        cupon_codigo: this.cuponCodigo,
        cupon_id: this.cuponId,
        category: this.selectedProducto2.category,
        ganancia: 0,
        prices: false,
        cecina: false,
        promo_price: null,
        LastVariantPrice: null
      };
      
      // Emitir ambos productos al componente padre
      this.$emit('producto-seleccionado', producto1);
      this.$emit('producto-seleccionado', producto2);
      
      this.$awn.success(`2x1 Agregado - Total: $${this.formatPrice(this.selectedProducto1.price)}`);
      
      // Cerrar después de un pequeño delay
      setTimeout(() => {
        this.closeModal();
      }, 300);
    },

    // Marcar cupón como usado
    async marcarCuponUsado() {
      try {
        const appData = this.getAppData();
        
        // Obtener datos de usuario de forma segura
        let usuarioId = 0;
        let usuarioNombre = 'Usuario';
        try {
          const authData = JSON.parse(localStorage.getItem('authorization') || '{}');
          usuarioId = authData.id || 0;
          usuarioNombre = authData.name || 'Usuario';
        } catch (e) {
          console.warn('No se pudo obtener datos de autorización:', e);
        }
        
        const cuponData = {
          codigo: this.cuponCodigo,
          cupon_id: this.cuponId,
          sucursal_id: appData.negocio_id || 1,
          sucursal_nombre: appData.negocio_nombre || 'Sucursal Principal',
          usuario_id: usuarioId,
          usuario_nombre: usuarioNombre,
          producto_id: this.selectedProducto1.id,
          producto_nombre: this.selectedProducto1.name,
          producto2_id: this.selectedProducto2.id,
          producto2_nombre: this.selectedProducto2.name,
          precio_original: parseFloat(this.selectedProducto1.price),
          precio_con_cupon: 0,
          usado_en: new Date().toISOString()
        };

        // 🔥 SOLO SQL REAL
        console.log('🌐 Marcando cupón en SQL real...', cuponData);
        const url = BaseUrl.getUrl('api/cupones/aplicar');
        console.log('📡 URL:', url);
        
        const response = await Connection.request('POST', url, cuponData);
        
        if (response && response.success) {
          console.log('✅ Cupón marcado en SQL real exitosamente:', response);
          return true;
        } else {
          console.error('❌ Error al marcar cupón en SQL real:', response);
          return false;
        }
      } catch (error) {
        console.error('❌ Error crítico al marcar cupón:', error);
        return false;
      }
    },

    // Obtener datos de la aplicación
    getAppData() {
      try {
        const appData = ConfigHelper.readAppFile();
        return {
          negocio_id: appData.id_negocio || 1,
          negocio_nombre: appData.nombre_negocio || 'Sucursal Principal'
        };
      } catch (error) {
        console.error('Error al leer aplication.json:', error);
        return {
          negocio_id: 1,
          negocio_nombre: 'Sucursal Principal'
        };
      }
    },

    closeModal() {
      $('#modalSeleccionCupon').modal('hide');
      this.selectedCategory = null;
      this.selectedProducto1 = null;
      this.selectedProducto2 = null;
      this.currentStep = 1;
      this.productos = [];
    },
    
    formatPrice(price) {
      return new Intl.NumberFormat('es-CL').format(price);
    }
  }
}
</script>

<style scoped>
/* Modal dialog */
.modal-dialog {
  max-width: 900px;
  margin: 30px auto;
}

.modal-content {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Header del modal */
.modal-header.bg-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.modal-title {
  font-weight: 700;
  font-size: 1.3rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Alertas mejoradas */
.alert {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  animation: slideDown 0.4s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-success {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  border-left: 4px solid #10b981;
  color: #065f46;
}

.alert-info {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  border-left: 4px solid #3b82f6;
  color: #1e40af;
  font-weight: 600;
}

/* Cards de categorías */
.category-card {
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
  border: 2px solid #e5e7eb;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.category-card:hover {
  border-color: #10b981;
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.25);
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.category-card:active {
  transform: translateY(-2px) scale(1.01);
}

/* Cards de productos */
.product-card {
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  overflow: hidden;
}

.product-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, #10b981 0%, #059669 100%);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.product-card:hover::before {
  transform: scaleX(1);
}

.product-card:hover {
  border-color: #10b981;
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(16, 185, 129, 0.2);
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
}

.product-card.selected {
  border-color: #10b981;
  border-width: 3px;
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
  transform: translateY(-4px) scale(1.03);
}

.product-card.selected::before {
  transform: scaleX(1);
}

.product-name {
  font-weight: 600;
  font-size: 1.15rem;
  margin-bottom: 8px;
  color: #111827;
}

.product-card.selected .product-name {
  color: #065f46;
  font-weight: 700;
}

.product-price {
  color: #6b7280;
  font-size: 1rem;
  font-weight: 600;
}

.product-card.selected .product-price {
  color: #059669;
  font-size: 1.1rem;
}

/* Iconos */
.selected-icon {
  position: absolute;
  top: 15px;
  right: 15px;
  color: #10b981;
  font-size: 2rem;
  animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  filter: drop-shadow(0 2px 4px rgba(16, 185, 129, 0.3));
}

@keyframes popIn {
  0% {
    transform: scale(0) rotate(-180deg);
    opacity: 0;
  }
  50% {
    transform: scale(1.2) rotate(10deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

/* Resumen de productos */
.alert-info h6 {
  color: #1e40af;
  font-size: 1.1rem;
  margin-bottom: 15px;
}

.alert-info .border-bottom {
  border-color: #93c5fd !important;
  padding-bottom: 12px;
  margin-bottom: 12px;
}

.alert-info .border-top {
  border-color: #93c5fd !important;
  padding-top: 12px;
}

.alert-info p {
  font-size: 1.05rem;
  margin-bottom: 8px;
}

.alert-info .h5 {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e3a8a;
}

/* Botones */
.modal-footer {
  border-top: 2px solid #e5e7eb;
  padding: 20px 24px;
  background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
}

.btn {
  padding: 12px 28px;
  font-size: 1.05rem;
  font-weight: 600;
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-secondary {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
}

.btn-secondary:hover {
  background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(107, 114, 128, 0.4);
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(59, 130, 246, 0.4);
}

.btn-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  font-size: 1.1rem;
  padding: 14px 32px;
}

.btn-success:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn:active {
  transform: translateY(0);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Botón volver a categorías */
.btn-outline-secondary {
  border: 2px solid #6b7280;
  color: #6b7280;
  background: transparent;
  font-size: 0.9rem;
  padding: 8px 16px;
}

.btn-outline-secondary:hover {
  background: #6b7280;
  color: white;
  border-color: #6b7280;
}

/* Loading spinner */
.fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Modal body */
.modal-body {
  max-height: 65vh;
  overflow-y: auto;
  padding: 24px;
}

/* Scrollbar personalizado */
.modal-body::-webkit-scrollbar {
  width: 10px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

/* Responsive */
@media (max-width: 768px) {
  .product-card {
    padding: 16px;
  }
  
  .product-name {
    font-size: 1rem;
  }
  
  .btn {
    padding: 10px 20px;
    font-size: 0.95rem;
  }
}
</style>
