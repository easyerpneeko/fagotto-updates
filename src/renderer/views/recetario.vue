<template>
  <div class="recetario-bg-modern p-3">
    <!-- Tarjetas de Gestión -->
    <div class="d-flex w-100 h-100 flex-column justify-content-start">
      <!-- Acciones para el recetario -->
      <div class="pl-2 d-flex row w-100 align-items-start">
        <div class="col-md-3 col-sm-6 col-12 d-flex flex-column">
          <div class="card modern-info-card modern-info-card-static">
            <div class="card-header modern-card-header">
              <i class="fas fa-book-open me-2"></i>
              <div>
                <h5 class="font-weight-bold m-0">Recetario</h5>
                <span class="card-subtitle">Gestiona recetas para tus productos</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12 d-flex flex-column">
          <div class="card modern-info-card-clickable" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" @click="mostrarRecetasConProductos">
            <div class="card-header modern-card-header">
              <i class="fas fa-link me-2"></i>
              <div>
                <h5 class="font-weight-bold m-0">Recetas asociadas</h5>
                <span class="card-subtitle">{{ totalRecetasAsociadas }} recetas con productos</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12 d-flex flex-column">
          <div class="card modern-info-card modern-info-card-clickable" @click="openHistorialRecetas">
            <div class="card-header modern-card-header">
              <i class="fas fa-history me-2"></i>
              <div>
                <h5 class="font-weight-bold m-0">Historial de cambios</h5>
                <span class="card-subtitle">Ver modificaciones realizadas</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 col-sm-6 col-12 d-flex flex-column">
          <div class="top-right-buttons">
            <button type="button" class="modern-action-btn btn-primary-modern" @click="openModalNuevaReceta">
              <i class="fas fa-plus me-2"></i>
              Nueva Receta
            </button>
          </div>
        </div>
      </div>

      <!-- Filtros integrados con Bootstrap Grid -->
      <div class="row px-3 mt-3 mb-2">
        <div class="col-md-6">
          <input 
            type="text" 
            v-model="searchQuery" 
            @keypress.enter="buscarRecetas"
            class="form-control modern-input-clean" 
            placeholder="🔍 Buscar receta..."
          />
        </div>
        <div class="col-md-3">
          <select 
            v-model="filtroAsociacion" 
            @change="buscarRecetas"
            class="form-control modern-select-clean"
          >
            <option value="all">Todas las recetas</option>
            <option value="asociadas">Con productos asociados</option>
            <option value="sin_asociar">Sin asociar</option>
          </select>
        </div>
        <div class="col-md-3">
          <button @click="buscarRecetas" class="btn btn-primary btn-block modern-search-btn">
            <i class="fas fa-search me-2"></i>
            Buscar
          </button>
        </div>
      </div>
    </div>

    <!-- Listado de recetas -->
    <div v-if="recetas && recetas.length > 0" class="vld-parent px-2 mt-2">
      <div class="modern-table-container">
        <table class="table table-hover modern-table">
          <thead>
            <tr>
              <th>Nombre Receta</th>
              <th>Ingredientes</th>
              <th>Productos Asociados</th>
              <th>Costo Total</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="receta in recetasPaginadas" :key="receta.id">
              <td>
                <div class="d-flex align-items-center">
                  <i class="fas fa-utensils me-2 text-primary"></i>
                  <strong>{{ receta.nombre }}</strong>
                </div>
                <small class="text-muted" v-if="receta.descripcion">{{ receta.descripcion }}</small>
              </td>
              <td>
                <div class="d-flex flex-column">
                  <span class="badge badge-light mb-1">
                    <i class="fas fa-list-ul me-1"></i>
                    {{ receta.ingredientes ? receta.ingredientes.length : 0 }} ingredientes
                  </span>
                  <small class="text-muted" v-if="receta.ingredientes && receta.ingredientes.length > 0">
                    {{ receta.ingredientes.slice(0, 2).map(i => i.nombre).join(', ') }}
                    <span v-if="receta.ingredientes.length > 2">...</span>
                  </small>
                </div>
              </td>
              <td>
                <div v-if="receta.productos && receta.productos.length > 0">
                  <span class="badge badge-success mb-1">
                    <i class="fas fa-box me-1"></i>
                    {{ receta.productos.length }} producto(s)
                  </span>
                  <div class="small-products-list">
                    <small v-for="prod in receta.productos.slice(0, 2)" :key="prod.id" class="d-block text-muted">
                      • {{ prod.nombre }}
                    </small>
                    <small v-if="receta.productos.length > 2" class="text-muted">
                      +{{ receta.productos.length - 2 }} más
                    </small>
                  </div>
                </div>
                <div v-else>
                  <span class="badge badge-warning">
                    <i class="fas fa-unlink me-1"></i>
                    Sin asociar
                  </span>
                </div>
              </td>
              <td>
                <strong class="text-success">
                  ${{ calcularCostoReceta(receta).toLocaleString('es-CL') }}
                </strong>
              </td>
              <td class="text-center">
                <span v-if="receta.productos && receta.productos.length > 0" class="badge badge-success">
                  <i class="fas fa-check-circle"></i> Activa
                </span>
                <span v-else class="badge badge-secondary">
                  <i class="fas fa-pause-circle"></i> Inactiva
                </span>
              </td>
              <td class="text-center">
                <div class="action-buttons-group">
                  <button @click="verReceta(receta)" class="btn-icon btn-view" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button @click="asociarProductos(receta)" class="btn-icon btn-link" title="Asociar a productos">
                    <i class="fas fa-link"></i>
                  </button>
                  <button @click="editarReceta(receta)" class="btn-icon btn-edit" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="eliminarReceta(receta)" class="btn-icon btn-delete" title="Eliminar">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="d-flex justify-content-between align-items-center mt-3 px-2">
        <div class="text-muted">
          Mostrando {{ recetas.length }} de {{ totalRecetas }} recetas
        </div>
        <div class="pagination-controls">
          <button 
            @click="paginaAnterior" 
            :disabled="paginaActual === 1"
            class="btn btn-sm btn-outline-primary me-2"
          >
            <i class="fas fa-chevron-left"></i> Anterior
          </button>
          <span class="mx-2">Página {{ paginaActual }} de {{ totalPaginas }}</span>
          <button 
            @click="paginaSiguiente" 
            :disabled="paginaActual === totalPaginas"
            class="btn btn-sm btn-outline-primary ms-2"
          >
            Siguiente <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Estado vacío -->
    <div v-else class="empty-state-container">
      <div class="empty-state-content">
        <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
        <h4>No hay recetas disponibles</h4>
        <p class="text-muted">Comienza agregando tu primera receta</p>
        <button class="btn btn-primary mt-3" @click="openModalNuevaReceta">
          <i class="fas fa-plus me-2"></i>
          Crear Primera Receta
        </button>
      </div>
    </div>

    <!-- Modal: Asociar Productos a Receta -->
    <div v-if="modalAsociarVisible" class="modal-overlay" @click.self="cerrarModalAsociar">
      <div class="modal-dialog modal-lg">
        <div class="modal-content modern-modal">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-link me-2"></i>
              Asociar Productos a: {{ recetaSeleccionada ? recetaSeleccionada.nombre : '' }}
            </h5>
            <button type="button" class="btn-close" @click="cerrarModalAsociar"></button>
          </div>
          
          <div class="modal-body">
            <!-- Buscador de productos -->
            <div class="mb-3">
              <input 
                type="text" 
                class="form-control" 
                v-model="searchProducto"
                placeholder="🔍 Buscar producto..."
              />
            </div>
            
            <!-- Lista de productos -->
            <div class="productos-list" style="max-height: 400px; overflow-y: auto;">
              <div 
                v-for="producto in productosFiltrados" 
                :key="producto.id"
                class="producto-item"
                :class="{ 'producto-asociado': producto.tieneReceta }"
                @click="toggleAsociarProducto(producto)"
              >
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-box me-2"></i>
                    <div>
                      <strong>{{ producto.name }}</strong>
                      <small v-if="producto.tieneReceta" class="d-block text-success">
                        <i class="fas fa-check-circle"></i> Ya tiene receta asociada
                      </small>
                    </div>
                  </div>
                  <button 
                    class="btn btn-sm"
                    :class="producto.tieneReceta ? 'btn-danger' : 'btn-primary'"
                  >
                    <i :class="producto.tieneReceta ? 'fas fa-unlink' : 'fas fa-link'"></i>
                    {{ producto.tieneReceta ? 'Desasociar' : 'Asociar' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cerrarModalAsociar">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import BaseUrl from '@/helpers/baseUrl';
import Connection from '@/helpers/Connection';

export default {
  name: 'Recetario',
  data() {
    return {
      // Datos de recetas
      recetas: [],
      recetaSeleccionada: null,
      
      // Filtros
      searchQuery: '',
      filtroAsociacion: 'all',
      
      // Paginación
      paginaActual: 1,
      totalPaginas: 1,
      totalRecetas: 0,
      itemsPorPagina: 10,
      
      // Estados
      cargando: false,
      
      // Contadores
      totalRecetasAsociadas: 0,
      
      // Ingredientes disponibles
      ingredientesDisponibles: [],
      
      // Productos disponibles
      productosDisponibles: [],
      
      // Modal asociar productos
      modalAsociarVisible: false,
      searchProducto: '',
    };
  },
  
  computed: {
    recetasPaginadas() {
      console.log('🔍 [COMPUTED] recetasPaginadas ejecutándose...');
      console.log('🔍 [COMPUTED] this.recetas:', this.recetas);
      console.log('🔍 [COMPUTED] Array.isArray(this.recetas):', Array.isArray(this.recetas));
      
      if (!Array.isArray(this.recetas)) {
        console.warn('⚠️ [COMPUTED] this.recetas NO es un array!');
        return [];
      }
      
      const inicio = (this.paginaActual - 1) * this.itemsPorPagina;
      const fin = inicio + this.itemsPorPagina;
      const resultado = this.recetas.slice(inicio, fin);
      
      console.log('🔍 [COMPUTED] recetasPaginadas resultado:', {
        totalRecetas: this.recetas.length,
        paginaActual: this.paginaActual,
        itemsPorPagina: this.itemsPorPagina,
        inicio,
        fin,
        resultadoLength: resultado.length,
        primerReceta: resultado[0]
      });
      
      return resultado;
    },
    
    productosFiltrados() {
      if (!this.searchProducto) {
        return this.productosDisponibles;
      }
      const busqueda = this.searchProducto.toLowerCase();
      return this.productosDisponibles.filter(p => 
        p.name.toLowerCase().includes(busqueda)
      );
    }
  },
  
  mounted() {
    this.inicializar();
  },
  
  methods: {
    async inicializar() {
      await this.cargarIngredientes();
      await this.cargarProductos();
      await this.buscarRecetas();
    },
    
    async cargarIngredientes() {
      try {
        // Llamada al API para obtener ingredientes
        const url = BaseUrl.getUrl('api/ingredients');
        const response = await Connection.request('get', url);
        if (response.data) {
          this.ingredientesDisponibles = Array.isArray(response.data) ? response.data : (response.data.data || []);
        }
      } catch (error) {
        console.warn('No se pudieron cargar ingredientes:', error.message);
        // No mostrar error al usuario, ingredientes son opcionales para ver recetas
        this.ingredientesDisponibles = [];
      }
    },
    
    async cargarProductos() {
      try {
        console.log('🛒 [PRODUCTOS] Cargando productos locales...');
        // Usar el método del store que trae todos los productos (sin paginación)
        const request = await this.$store.dispatch('products/getProductsOfSell');
        
        console.log('🛒 [PRODUCTOS] Response completo:', request);
        console.log('🛒 [PRODUCTOS] request.success:', request.success);
        console.log('🛒 [PRODUCTOS] request.data:', request.data);
        
        if (request.success && request.data) {
          // La respuesta es un array directo de productos
          if (Array.isArray(request.data)) {
            this.productosDisponibles = request.data;
            console.log('🛒 [PRODUCTOS] ✅ Cargados (array directo):', this.productosDisponibles.length);
          } else if (request.data.items && Array.isArray(request.data.items)) {
            this.productosDisponibles = request.data.items;
            console.log('🛒 [PRODUCTOS] ✅ Cargados (data.items):', this.productosDisponibles.length);
          } else {
            console.warn('⚠️ [PRODUCTOS] Estructura de respuesta desconocida:', request.data);
            this.productosDisponibles = [];
          }
        } else {
          console.warn('⚠️ [PRODUCTOS] Request no exitoso');
          this.productosDisponibles = [];
        }
      } catch (error) {
        console.error('❌ [PRODUCTOS] Error al cargar productos:', error);
        this.productosDisponibles = [];
      }
    },
    
    async buscarRecetas() {
      try {
        this.cargando = true;
        
        // Llamada al API para obtener recetas globales
        let url = BaseUrl.getUrl('api/recetas');
        const params = [];
        
        if (this.searchQuery) {
          params.push(`search=${encodeURIComponent(this.searchQuery)}`);
        }
        
        params.push('activas=1'); // Solo recetas activas
        
        if (params.length > 0) {
          url += '?' + params.join('&');
        }
        
        const response = await Connection.request('get', url);
        
        console.log('📦 [API] Response completo:', response);
        console.log('📦 [API] Response.ok:', response.ok);
        console.log('📦 [API] Response.success:', response.success);
        console.log('📦 [API] Response.status:', response.status);
        console.log('📦 [API] Response.data:', response.data);
        console.log('📦 [API] Tipo de response.data:', typeof response.data);
        
        // Connection.request() devuelve response.success, NO response.ok
        if (response.success && response.data && response.data.success) {
          let recetas = response.data.data || [];
          console.log('📦 [TRANSFORM] Recetas recibidas:', recetas.length, recetas);
          
          // Transformar estructura para el frontend
          this.recetas = recetas.map(r => ({
            id: r.id,
            nombre: r.nombre,
            descripcion: r.descripcion || '',
            codigo: r.codigo,
            ingredientes: (r.ingredientes || []).map(ing => ({
              id: ing.id,
              nombre: ing.ingrediente_nombre,
              cantidad: parseFloat(ing.cantidad),
              unidad: ing.unidad,
              orden: ing.orden,
              opcional: ing.opcional,
              notas: ing.notas
            })),
            productos: r.productos_asociados || [],
            productosCount: r.productos_asociados_count || 0
          }));
          
          console.log('📦 [TRANSFORM] this.recetas después del map:', this.recetas);
          console.log('📦 [TRANSFORM] this.recetas.length:', this.recetas.length);
          console.log('📦 [TRANSFORM] Primera receta:', this.recetas[0]);
          console.log('📦 [TRANSFORM] Array.isArray(this.recetas):', Array.isArray(this.recetas));
          
          // Filtrar según asociación
          if (this.filtroAsociacion === 'asociadas') {
            this.recetas = this.recetas.filter(r => r.productosCount > 0);
          } else if (this.filtroAsociacion === 'sin_asociar') {
            this.recetas = this.recetas.filter(r => r.productosCount === 0);
          }
          
          this.totalRecetas = this.recetas.length;
          this.totalPaginas = Math.ceil(this.totalRecetas / this.itemsPorPagina);
          
          // Calcular total de recetas asociadas
          this.totalRecetasAsociadas = recetas.filter(r => (r.productos_asociados_count || 0) > 0).length;
          
          console.log('✅ Recetas cargadas en this.recetas:', this.recetas.length);
          console.log('✅ Total páginas:', this.totalPaginas);
          console.log('✅ Página actual:', this.paginaActual);
        }
        
      } catch (error) {
        console.error('Error al buscar recetas:', error);
        this.$awn.alert('Error al cargar las recetas');
        this.recetas = [];
      } finally {
        this.cargando = false;
      }
    },
    
    calcularCostoReceta(receta) {
      if (!receta.ingredientes || receta.ingredientes.length === 0) return 0;
      
      return receta.ingredientes.reduce((total, ing) => {
        // Convertir cantidad a kg/litros para calcular costo
        const cantidadEnUnidadBase = ing.unidad === 'g' || ing.unidad === 'ml' 
          ? ing.cantidad / 1000 
          : ing.cantidad;
        
        return total + (cantidadEnUnidadBase * ing.precioUnitario);
      }, 0);
    },
    
    async asociarProductos(receta) {
      console.log('🔗 [ASOCIAR] Abriendo modal para receta:', receta.nombre);
      this.recetaSeleccionada = receta;
      this.modalAsociarVisible = true;
      this.searchProducto = '';
      
      // Recargar productos por si no se cargaron en mounted
      if (this.productosDisponibles.length === 0) {
        console.log('🔗 [ASOCIAR] No hay productos, recargando...');
        await this.cargarProductos();
      }
      
      console.log('🔗 [ASOCIAR] Productos disponibles:', this.productosDisponibles.length);
      
      // Marcar productos que ya tienen receta
      this.actualizarEstadoProductos();
    },
    
    cerrarModalAsociar() {
      this.modalAsociarVisible = false;
      this.recetaSeleccionada = null;
      this.searchProducto = '';
    },
    
    actualizarEstadoProductos() {
      if (!this.recetaSeleccionada) return;
      
      // Marcar productos que tienen esta receta asociada
      this.productosDisponibles.forEach(producto => {
        const asociado = this.recetaSeleccionada.productos.find(p => p.id === producto.id);
        producto.tieneReceta = !!asociado;
      });
    },
    
    async toggleAsociarProducto(producto) {
      if (!this.recetaSeleccionada) return;
      
      try {
        if (producto.tieneReceta) {
          // Desasociar
          const url = BaseUrl.getUrl(`api/recetas/${this.recetaSeleccionada.id}/desasociar-producto/${producto.id}`);
          const response = await Connection.request('delete', url);
          
          if (response.data && response.data.success) {
            this.$awn.success('Producto desasociado correctamente');
            producto.tieneReceta = false;
            // Actualizar la receta en la lista
            await this.buscarRecetas();
          } else {
            this.$awn.alert(response.data.message || 'Error al desasociar producto');
          }
        } else {
          // Asociar
          const url = BaseUrl.getUrl(`api/recetas/${this.recetaSeleccionada.id}/asociar-producto`);
          const response = await Connection.request('post', url, {
            product_id: producto.id,
            multiplicador: 1.0
          });
          
          if (response.data && response.data.success) {
            this.$awn.success('Producto asociado correctamente');
            producto.tieneReceta = true;
            // Actualizar la receta en la lista
            await this.buscarRecetas();
          } else {
            this.$awn.alert(response.data.message || 'Error al asociar producto');
          }
        }
      } catch (error) {
        console.error('Error al asociar/desasociar producto:', error);
        this.$awn.alert('Error al procesar la operación');
      }
    },
    
    openModalNuevaReceta() {
      // TODO: Implementar modal para crear recetas
      console.log('Abrir modal de nueva receta');
      this.$awn.info('Funcionalidad de crear receta - Por implementar');
    },
    
    mostrarRecetasConProductos() {
      const recetasAsociadas = this.recetas.filter(r => r.productos && r.productos.length > 0);
      
      if (recetasAsociadas.length === 0) {
        this.$awn.info('No hay recetas asociadas a productos aún');
        return;
      }
      
      // TODO: Implementar modal con lista de recetas asociadas
      console.log('Recetas asociadas:', recetasAsociadas);
      this.$awn.info(`Hay ${recetasAsociadas.length} recetas asociadas a productos`);
    },
    
    openHistorialRecetas() {
      this.$awn.info('Historial de cambios - Por implementar');
    },
    
    verReceta(receta) {
      // TODO: Implementar modal con detalles de la receta
      console.log('Ver receta:', receta);
      const costo = this.calcularCostoReceta(receta);
      const numProductos = receta.productos ? receta.productos.length : 0;
      this.$awn.info(`${receta.nombre} - ${receta.ingredientes.length} ingredientes - ${numProductos} productos asociados - Costo: $${costo.toLocaleString('es-CL')}`);
    },
    
    editarReceta(receta) {
      this.$awn.info(`Editar receta: ${receta.nombre} - Por implementar`);
    },
    
    eliminarReceta(receta) {
      // TODO: Implementar confirmación y eliminación real
      const tieneProductos = receta.productos && receta.productos.length > 0;
      const mensaje = tieneProductos 
        ? `La receta "${receta.nombre}" está asociada a ${receta.productos.length} producto(s). ¿Eliminar de todos modos?`
        : `¿Eliminar receta "${receta.nombre}"?`;
      
      if (confirm(mensaje)) {
        console.log('Eliminar receta:', receta.id);
        // TODO: Llamar al API DELETE /api/recetas/{id}
        this.$awn.alert('Funcionalidad de eliminar - Por implementar');
      }
    },
    
    paginaAnterior() {
      if (this.paginaActual > 1) {
        this.paginaActual--;
        this.buscarRecetas();
      }
    },
    
    paginaSiguiente() {
      if (this.paginaActual < this.totalPaginas) {
        this.paginaActual++;
        this.buscarRecetas();
      }
    },
  }
}
</script>

<style scoped>
.recetario-bg-modern {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
  padding: 20px;
}

/* Tarjetas modernas */
.modern-info-card {
  border: none;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  background: white;
  margin-bottom: 15px;
}

.modern-info-card-static {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.modern-info-card-clickable {
  cursor: pointer;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.modern-info-card-clickable:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.modern-card-header {
  padding: 20px;
  display: flex;
  align-items: center;
  border: none;
  background: transparent;
}

.modern-card-header i {
  font-size: 2rem;
  margin-right: 15px;
}

.card-subtitle {
  font-size: 0.85rem;
  opacity: 0.9;
}

/* Botones de acción */
.top-right-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: flex-end;
}

.modern-action-btn {
  padding: 12px 24px;
  border: none;
  border-radius: 25px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  font-size: 14px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.btn-primary-modern {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
}

.btn-accent-modern {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.btn-accent-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(240, 147, 251, 0.4);
}

/* Inputs modernos */
.modern-input-clean,
.modern-select-clean {
  border: 2px solid #e1e8ed;
  border-radius: 10px;
  padding: 12px 20px;
  transition: all 0.3s ease;
  background: white;
  font-size: 14px;
}

.modern-input-clean:focus,
.modern-select-clean:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
  outline: none;
}

/* Tabla moderna */
.modern-table-container {
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  padding: 20px;
  overflow-x: auto;
}

.modern-table {
  margin-bottom: 0;
}

.modern-table thead th {
  border-top: none;
  border-bottom: 2px solid #e1e8ed;
  font-weight: 700;
  color: #667eea;
  text-transform: uppercase;
  font-size: 0.85rem;
  padding: 15px;
}

.modern-table tbody tr {
  transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
  background-color: #f8f9fa;
  transform: scale(1.01);
}

.modern-table tbody td {
  padding: 15px;
  vertical-align: middle;
  border-bottom: 1px solid #f1f3f5;
}

/* Botones de acción en tabla */
.action-buttons-group {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.btn-icon {
  width: 35px;
  height: 35px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  font-size: 14px;
}

.btn-view {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-view:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
}

.btn-edit {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.btn-edit:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(240, 147, 251, 0.3);
}

.btn-delete {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  color: white;
}

.btn-delete:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(250, 112, 154, 0.3);
}

/* Estado vacío */
.empty-state-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  margin: 20px;
}

.empty-state-content {
  text-align: center;
  padding: 40px;
}

/* Paginación */
.pagination-controls {
  display: flex;
  align-items: center;
}

.pagination-controls button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Badges */
.badge {
  padding: 6px 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.8rem;
}

.badge-info {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.badge-success {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  color: white;
}

.badge-warning {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.badge-light {
  background: #f8f9fa;
  color: #495057;
  border: 1px solid #e1e8ed;
}

.badge-secondary {
  background: linear-gradient(135deg, #868f96 0%, #596164 100%);
  color: white;
}

/* Botón de búsqueda */
.modern-search-btn {
  border-radius: 10px;
  padding: 12px;
  font-weight: 600;
  height: 100%;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: all 0.3s ease;
}

.modern-search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(102, 126, 234, 0.4);
}

/* Lista de productos pequeños */
.small-products-list {
  max-height: 60px;
  overflow-y: auto;
}

/* Botón de link */
.btn-link {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  color: white;
}

.btn-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(17, 153, 142, 0.3);
}

/* Alert personalizado ancho */
:global(.wide-alert) {
  width: 600px !important;
  max-width: 90vw !important;
}

/* Responsive */
@media (max-width: 768px) {
  .top-right-buttons {
    justify-content: center;
    width: 100%;
  }
  
  .modern-action-btn {
    width: 100%;
    justify-content: center;
  }
  
  .modern-table-container {
    padding: 10px;
  }
  
  .modern-table {
    font-size: 0.85rem;
  }
  
  .action-buttons-group {
    flex-direction: column;
    gap: 4px;
  }
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modern-modal .modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 15px 15px 0 0;
  padding: 20px;
}

.modern-modal .modal-title {
  margin: 0;
  font-weight: 600;
}

.modern-modal .btn-close {
  background: white;
  opacity: 1;
  border-radius: 50%;
  width: 30px;
  height: 30px;
}

/* Lista de productos */
.productos-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.producto-item {
  padding: 15px;
  border: 2px solid #e1e8ed;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.producto-item:hover {
  border-color: #667eea;
  background: #f8f9fa;
  transform: translateX(5px);
}

.producto-item.producto-asociado {
  border-color: #38ef7d;
  background: #f0fff4;
}
</style>
