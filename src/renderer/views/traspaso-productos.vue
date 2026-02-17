<template>
  <div class="traspaso-productos-container">
    <!-- Header -->
    <div class="traspaso-header">
      <div class="row align-items-center">
        <div class="col-lg-8 col-md-6 col-12">
          <div class="page-title-card">
            <h2><i class="fas fa-exchange-alt"></i> Traspaso de Productos</h2>
            <p class="subtitle">Transfiere productos entre locales</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mt-3 mt-md-0 text-right">
          <button @click="mostrarHistorial = !mostrarHistorial" class="btn-secondary-action">
            <i class="fas fa-history"></i>
            {{ mostrarHistorial ? 'Nuevo Traspaso' : 'Ver Historial' }}
            <span v-if="!mostrarHistorial && historialFiltrado.length > 0" class="badge badge-light ml-2">
              {{ historialFiltrado.length }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Vista de Historial -->
    <div v-if="mostrarHistorial" class="historial-section">
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-clipboard-list"></i> Historial de Traspasos</h5>
        </div>
        <div class="card-body">
          <div v-if="cargandoHistorial" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-3">Cargando historial...</p>
          </div>
          <div v-else-if="historialFiltrado.length === 0" class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">No hay traspasos registrados para este local</p>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Tipo</th>
                  <th>Fecha Creación</th>
                  <th>Origen</th>
                  <th>Destino</th>
                  <th>Productos</th>
                  <th>Comentarios</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="traspaso in historialFiltrado" :key="traspaso.id">
                  <td><strong>{{ traspaso.id }}</strong></td>
                  <td>
                    <span v-if="traspaso.local_origen_id === appId" class="badge badge-warning">
                      <i class="fas fa-arrow-up"></i> Enviado
                    </span>
                    <span v-else class="badge badge-info">
                      <i class="fas fa-arrow-down"></i> Recibido
                    </span>
                  </td>
                  <td>
                    <small>{{ formatFecha(traspaso.created_at) }}</small>
                  </td>
                  <td>{{ traspaso.local_origen_nombre }}</td>
                  <td>{{ traspaso.local_destino_nombre }}</td>
                  <td>
                    <span class="badge badge-primary">{{ contarProductos(traspaso.productos) }}</span>
                  </td>
                  <td>
                    <small class="text-muted">{{ traspaso.comentarios || '-' }}</small>
                  </td>
                  <td>
                    <span :class="['badge', getBadgeClass(traspaso.estado)]">
                      {{ traspaso.estado }}
                    </span>
                  </td>
                  <td>
                    <button @click="verDetalle(traspaso)" class="btn btn-sm btn-info">
                      <i class="fas fa-eye"></i> Ver
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detalle de Traspaso -->
    <div v-if="traspasoSeleccionado" class="modal-overlay" @click.self="cerrarDetalle">
      <div class="modal-detalle">
        <div class="modal-header-custom">
          <h4><i class="fas fa-clipboard-list"></i> Detalle Traspaso #{{ traspasoSeleccionado.id }}</h4>
          <button @click="cerrarDetalle" class="btn-close-custom">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body-custom">
          <!-- Info General -->
          <div class="info-section">
            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label><i class="fas fa-store text-danger"></i> Local Origen</label>
                  <p>{{ traspasoSeleccionado.local_origen_nombre }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label><i class="fas fa-store text-success"></i> Local Destino</label>
                  <p>{{ traspasoSeleccionado.local_destino_nombre }}</p>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="info-item">
                  <label><i class="fas fa-calendar"></i> Fecha Creación</label>
                  <p>{{ formatFecha(traspasoSeleccionado.created_at) }}</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-item">
                  <label><i class="fas fa-box"></i> Total Items</label>
                  <p>{{ traspasoSeleccionado.total_items }}</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-item">
                  <label><i class="fas fa-flag"></i> Estado</label>
                  <p>
                    <span :class="['badge', getBadgeClass(traspasoSeleccionado.estado)]">
                      {{ traspasoSeleccionado.estado }}
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Productos -->
          <div class="productos-section">
            <h5><i class="fas fa-boxes"></i> Productos</h5>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(prod, idx) in getProductosArray(traspasoSeleccionado.productos)" :key="idx">
                    <td>{{ prod.name }}</td>
                    <td>{{ prod.cantidad }}</td>
                    <td>{{ prod.unidad_medida }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Comentarios -->
          <div v-if="traspasoSeleccionado.comentarios" class="comentarios-section">
            <h5><i class="fas fa-comment"></i> Comentarios</h5>
            <p class="comentario-text">{{ traspasoSeleccionado.comentarios }}</p>
          </div>

          <!-- Botones de Acción -->
          <!-- Solo el LOCAL ORIGEN puede marcar en tránsito -->
          <div v-if="traspasoSeleccionado.estado === 'pendiente' && traspasoSeleccionado.local_origen_id === appId" class="acciones-section">
            <button @click="cambiarEstado(traspasoSeleccionado.id, 'en_transito')" class="btn btn-info btn-block">
              <i class="fas fa-truck"></i> Marcar En Tránsito
            </button>
          </div>
          
          <!-- Solo el LOCAL DESTINO puede recibir/rechazar -->
          <div v-if="traspasoSeleccionado.estado === 'en_transito' && traspasoSeleccionado.local_destino_id === appId" class="acciones-section">
            <div class="row">
              <div class="col-6">
                <button @click="cambiarEstado(traspasoSeleccionado.id, 'recibido')" class="btn btn-success btn-block">
                  <i class="fas fa-check"></i> Recibido
                </button>
              </div>
              <div class="col-6">
                <button @click="cambiarEstado(traspasoSeleccionado.id, 'rechazado')" class="btn btn-danger btn-block">
                  <i class="fas fa-times"></i> Rechazar
                </button>
              </div>
            </div>
          </div>
          
          <!-- Mensaje informativo cuando no hay acciones disponibles -->
          <div v-if="traspasoSeleccionado.estado === 'recibido' || traspasoSeleccionado.estado === 'rechazado'" class="alert alert-info mb-0">
            <i class="fas fa-info-circle"></i> Este traspaso ya ha sido finalizado
          </div>
          <div v-else-if="traspasoSeleccionado.estado === 'pendiente' && traspasoSeleccionado.local_destino_id === appId" class="alert alert-warning mb-0">
            <i class="fas fa-clock"></i> Esperando que el local de origen envíe el traspaso
          </div>
          <div v-else-if="traspasoSeleccionado.estado === 'en_transito' && traspasoSeleccionado.local_origen_id === appId" class="alert alert-warning mb-0">
            <i class="fas fa-truck"></i> Esperando que el local destino reciba el traspaso
          </div>
        </div>
      </div>
    </div>

    <!-- Vista de Creación de Traspaso -->
    <div v-else class="creacion-section">
      <div class="row">
        <!-- Formulario -->
        <div class="col-lg-7 col-md-6 col-12">
          <div class="card">
            <div class="card-header">
              <h5><i class="fas fa-plus-circle"></i> Nuevo Traspaso</h5>
            </div>
            <div class="card-body">
              <!-- Selector de Locales -->
              <div class="locales-selector mb-4">
                <div class="row">
                  <div class="col-md-5">
                    <label class="form-label">
                      <i class="fas fa-store text-danger"></i> Local Origen
                    </label>
                    <select v-model="localOrigenId" class="form-control">
                      <option :value="null">Seleccionar local...</option>
                      <option v-for="local in locales" :key="local.id" :value="local.id">
                        {{ local.Name || local.name }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-2 text-center">
                    <div class="arrow-container">
                      <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label">
                      <i class="fas fa-store text-success"></i> Local Destino
                    </label>
                    <select v-model="localDestinoId" class="form-control">
                      <option :value="null">Seleccionar local...</option>
                      <option v-for="local in locales" :key="local.id" :value="local.id" :disabled="local.id === localOrigenId">
                        {{ local.Name || local.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Búsqueda de Productos -->
              <div class="busqueda-productos mb-3">
                <label class="form-label">
                  <i class="fas fa-search"></i> Buscar Producto
                </label>
                <div class="input-group">
                  <input 
                    type="text" 
                    v-model="busqueda" 
                    class="form-control" 
                    placeholder="Buscar por nombre, código, categoría..."
                    @input="filtrarProductos"
                    :disabled="!localOrigenId"
                  >
                  <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                  </div>
                </div>
              </div>

              <!-- Lista de Productos Disponibles -->
              <div v-show="localOrigenId" class="productos-disponibles">
                <label class="form-label">
                  <i class="fas fa-box-open"></i> Productos Disponibles
                  <span class="badge badge-info ml-2">{{ productosFiltrados.length }}</span>
                </label>
                
                <div class="productos-list">
                  <div v-if="cargandoProductos" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando productos...</p>
                  </div>
                  <div v-else-if="productosFiltrados.length === 0" class="text-center py-4">
                    <i class="fas fa-box fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No se encontraron productos</p>
                  </div>
                  <div v-else>
                    <div class="producto-item" v-for="producto in productosFiltrados" :key="producto.id">
                      <div class="producto-info">
                        <div class="producto-nombre">{{ producto.name }}</div>
                        <div class="producto-meta">
                          <span class="badge badge-secondary">{{ producto.category }}</span>
                          <span class="text-muted ml-2">{{ producto.unidad_medida }}</span>
                        </div>
                      </div>
                      <button @click="agregarProducto(producto)" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Agregar
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div v-show="!localOrigenId" class="alert alert-info">
                <i class="fas fa-info-circle"></i> Selecciona un local de origen para ver productos disponibles
              </div>
            </div>
          </div>
        </div>

        <!-- Carrito de Traspaso -->
        <div class="col-lg-5 col-md-6 col-12 mt-4 mt-md-0">
          <div class="card sticky-top">
            <div class="card-header">
              <h5><i class="fas fa-shopping-cart"></i> Productos a Traspasar</h5>
            </div>
            <div class="card-body">
              <div v-if="productosSeleccionados.length === 0" class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">No hay productos seleccionados</p>
              </div>
              <div v-else>
                <div class="producto-seleccionado" v-for="(producto, index) in productosSeleccionados" :key="index">
                  <div class="producto-sel-info">
                    <div class="producto-sel-nombre">{{ producto.name }}</div>
                    <div class="producto-sel-meta">
                      <span class="text-muted">{{ producto.unidad_medida }}</span>
                    </div>
                  </div>
                  <div class="producto-sel-cantidad">
                    <div class="input-group input-group-sm">
                      <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary" @click="disminuirCantidad(index)">
                          <i class="fas fa-minus"></i>
                        </button>
                      </div>
                      <input 
                        type="number" 
                        v-model.number="producto.cantidad" 
                        class="form-control text-center" 
                        min="1"
                        @input="validarCantidad(index)"
                      >
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary" @click="aumentarCantidad(index)">
                          <i class="fas fa-plus"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <button @click="eliminarProducto(index)" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>

                <!-- Total de Items -->
                <div class="total-items mt-3">
                  <strong>Total de items:</strong> {{ totalItems }}
                </div>

                <!-- Comentarios -->
                <div class="form-group mt-3">
                  <label>Comentarios (opcional)</label>
                  <textarea 
                    v-model="comentarios" 
                    class="form-control" 
                    rows="3" 
                    placeholder="Agregar notas sobre este traspaso..."
                  ></textarea>
                </div>

                <!-- Botón Enviar -->
                <button 
                  @click="enviarTraspaso" 
                  class="btn btn-success btn-block btn-lg mt-3"
                  :disabled="!puedeEnviar || enviando"
                >
                  <span v-if="enviando">
                    <div class="spinner-border spinner-border-sm mr-2" role="status"></div>
                    Enviando...
                  </span>
                  <span v-else>
                    <i class="fas fa-paper-plane"></i> Enviar Traspaso
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection';
import BaseUrl from '@/helpers/baseUrl';

export default {
  name: 'TraspasoProductos',
  data() {
    return {
      // Estados de vista
      mostrarHistorial: false,
      cargandoHistorial: false,
      cargandoProductos: false,
      enviando: false,

      // Datos
      locales: [],
      productos: [],
      productosFiltrados: [],
      productosSeleccionados: [],
      historial: [],
      traspasoSeleccionado: null,

      // Formulario
      localOrigenId: null,
      localDestinoId: null,
      busqueda: '',
      comentarios: '',
    };
  },
  computed: {
    app() {
      return this.$store.state.main.Aplication;
    },
    appId() {
      return this.app && this.app.Id ? this.app.Id : null;
    },
    totalItems() {
      return this.productosSeleccionados.reduce((sum, p) => sum + p.cantidad, 0);
    },
    puedeEnviar() {
      return this.localOrigenId && 
             this.localDestinoId && 
             this.localOrigenId !== this.localDestinoId &&
             this.productosSeleccionados.length > 0;
    },
    historialFiltrado() {
      // Mostrar traspasos donde este local es origen O destino
      if (!this.appId) return this.historial;
      
      return this.historial.filter(t => 
        t.local_origen_id === this.appId || t.local_destino_id === this.appId
      );
    }
  },
  mounted() {
    this.cargarLocales();
    this.cargarHistorial(); // Cargar historial al inicio para tenerlo disponible
  },
  watch: {
    mostrarHistorial(newVal) {
      if (newVal) {
        this.cargarHistorial();
      }
    },
    localOrigenId(newVal) {
      if (newVal) {
        this.cargarProductosOrigen();
      } else {
        this.productos = [];
        this.productosFiltrados = [];
      }
    }
  },
  methods: {
    async cargarLocales() {
      try {
        const response = await Connection.request('POST', BaseUrl.getUrl('api/getApps'));
        if (response.success && response.data) {
          this.locales = response.data;
        }
      } catch (error) {
        console.error('Error cargando locales:', error);
        this.$awn.alert('Error al cargar locales');
      }
    },

    async cargarProductosOrigen() {
      if (!this.localOrigenId) return;
      
      this.cargandoProductos = true;
      try {
        const response = await Connection.request('GET', BaseUrl.getUrl('api/local/traspasos-productos/productos'));
        console.log('📦 Respuesta productos:', response);
        console.log('📦 response.data:', response.data);
        console.log('📦 Tipo de response.data:', typeof response.data);
        
        // El servidor Laravel devuelve { success: true, data: [...] }
        // Pero Connection.js lo envuelve nuevamente en { success: true, data: {...} }
        // Entonces necesitamos acceder a response.data.data
        let productos = null;
        
        if (response.success && response.data) {
          // Si response.data.data existe (doble encapsulación)
          if (response.data.data && Array.isArray(response.data.data)) {
            productos = response.data.data;
          }
          // Si response.data es directamente el array
          else if (Array.isArray(response.data)) {
            productos = response.data;
          }
          // Si response.data tiene la estructura de Laravel directamente
          else if (response.data.success && Array.isArray(response.data.data)) {
            productos = response.data.data;
          }
        }
        
        if (productos && Array.isArray(productos)) {
          console.log('📦 Productos cargados:', productos.length);
          console.log('📦 Primer producto:', productos[0]);
          
          this.productos = productos;
          this.productosFiltrados = [...productos];
        } else {
          console.error('❌ No se pudo extraer el array de productos');
          this.productos = [];
          this.productosFiltrados = [];
        }
      } catch (error) {
        console.error('Error cargando productos:', error);
        this.$awn.alert('Error al cargar productos');
        this.productos = [];
        this.productosFiltrados = [];
      } finally {
        this.cargandoProductos = false;
      }
    },

    filtrarProductos() {
      if (!Array.isArray(this.productos)) {
        this.productos = [];
        this.productosFiltrados = [];
        return;
      }

      if (!this.busqueda.trim()) {
        this.productosFiltrados = [...this.productos];
        return;
      }

      const busq = this.busqueda.toLowerCase();
      this.productosFiltrados = this.productos.filter(p => 
        p.name.toLowerCase().includes(busq) ||
        p.category.toLowerCase().includes(busq)
      );
    },

    agregarProducto(producto) {
      // Verificar si ya está agregado
      const existe = this.productosSeleccionados.find(p => p.id === producto.id);
      if (existe) {
        this.$awn.warning('Producto ya agregado');
        return;
      }

      this.productosSeleccionados.push({
        ...producto,
        cantidad: 1
      });
    },

    eliminarProducto(index) {
      this.productosSeleccionados.splice(index, 1);
    },

    aumentarCantidad(index) {
      const producto = this.productosSeleccionados[index];
      producto.cantidad++;
    },

    disminuirCantidad(index) {
      const producto = this.productosSeleccionados[index];
      if (producto.cantidad > 1) {
        producto.cantidad--;
      }
    },

    validarCantidad(index) {
      const producto = this.productosSeleccionados[index];
      if (producto.cantidad < 1 || !producto.cantidad) {
        producto.cantidad = 1;
      }
      // Asegurar que sea un número entero positivo
      producto.cantidad = Math.max(1, Math.floor(producto.cantidad));
    },

    async enviarTraspaso() {
      if (!this.puedeEnviar) return;

      this.enviando = true;
      try {
        // Obtener nombres de los locales
        const localOrigen = this.locales.find(l => l.id === this.localOrigenId);
        const localDestino = this.locales.find(l => l.id === this.localDestinoId);

        const data = {
          local_origen_id: this.localOrigenId,
          local_destino_id: this.localDestinoId,
          local_origen_nombre: localOrigen ? (localOrigen.Name || localOrigen.name || 'Desconocido') : 'Desconocido',
          local_destino_nombre: localDestino ? (localDestino.Name || localDestino.name || 'Desconocido') : 'Desconocido',
          solicitante_nombre: null, // TODO: Obtener del usuario actual
          solicitante_telefono: null,
          productos: this.productosSeleccionados.map(p => ({
            id: p.id,
            name: p.name,
            cantidad: p.cantidad,
            unidad_medida: p.unidad_medida || 'unidad'
          })),
          comentarios: this.comentarios,
          total_items: this.totalItems
        };

        const response = await Connection.request('POST', BaseUrl.getUrl('api/local/traspasos-productos'), data);
        
        if (response.success) {
          this.$awn.success('✅ Traspaso creado exitosamente');
          this.limpiarFormulario();
        } else {
          this.$awn.alert('Error: ' + (response.message || 'No se pudo crear el traspaso'));
        }
        
      } catch (error) {
        console.error('Error enviando traspaso:', error);
        this.$awn.alert('Error al crear traspaso');
      } finally {
        this.enviando = false;
      }
    },

    limpiarFormulario() {
      this.localOrigenId = null;
      this.localDestinoId = null;
      this.productosSeleccionados = [];
      this.comentarios = '';
      this.busqueda = '';
      this.productos = [];
      this.productosFiltrados = [];
    },

    async cargarHistorial() {
      this.cargandoHistorial = true;
      try {
        const response = await Connection.request('GET', BaseUrl.getUrl('api/local/traspasos-productos'));
        console.log('📋 Respuesta historial:', response);
        
        // Manejar estructura anidada igual que productos
        let historialData = null;
        
        if (response.success && response.data) {
          if (response.data.data && Array.isArray(response.data.data)) {
            historialData = response.data.data;
          } else if (Array.isArray(response.data)) {
            historialData = response.data;
          } else if (response.data.success && Array.isArray(response.data.data)) {
            historialData = response.data.data;
          }
        }
        
        if (historialData && Array.isArray(historialData)) {
          console.log('📋 Historial cargado:', historialData.length, 'traspasos');
          this.historial = historialData;
        } else {
          console.error('❌ No se pudo extraer el array de historial');
          this.historial = [];
        }
      } catch (error) {
        console.error('Error cargando historial:', error);
        this.$awn.alert('Error al cargar historial');
      } finally {
        this.cargandoHistorial = false;
      }
    },

    formatFecha(fecha) {
      if (!fecha) return '-';
      const date = new Date(fecha);
      return date.toLocaleDateString('es-CL') + ' ' + date.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
    },

    contarProductos(productos) {
      try {
        const items = JSON.parse(productos);
        return items.length + ' items';
      } catch (error) {
        return '-';
      }
    },

    getBadgeClass(estado) {
      const map = {
        'pendiente': 'badge-warning',
        'aprobado': 'badge-success',
        'rechazado': 'badge-danger',
        'en_transito': 'badge-info'
      };
      return map[estado] || 'badge-secondary';
    },

    verDetalle(traspaso) {
      this.traspasoSeleccionado = traspaso;
    },

    cerrarDetalle() {
      this.traspasoSeleccionado = null;
    },

    getProductosArray(productosJson) {
      try {
        return JSON.parse(productosJson);
      } catch (error) {
        console.error('Error parseando productos:', error);
        return [];
      }
    },

    async cambiarEstado(traspasoId, nuevoEstado) {
      try {
        const response = await Connection.request(
          'PUT', 
          BaseUrl.getUrl(`api/local/traspasos-productos/${traspasoId}/estado`),
          { estado: nuevoEstado }
        );
        
        if (response.success) {
          this.$awn.success('✅ Estado actualizado correctamente');
          this.cerrarDetalle();
          this.cargarHistorial(); // Recargar historial
        } else {
          this.$awn.alert('Error: ' + (response.message || 'No se pudo actualizar'));
        }
      } catch (error) {
        console.error('Error cambiando estado:', error);
        this.$awn.alert('Error al actualizar estado');
      }
    }
  }
};
</script>

<style scoped>
.traspaso-productos-container {
  padding: 20px;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.traspaso-header {
  margin-bottom: 30px;
}

.page-title-card {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.page-title-card h2 {
  margin: 0;
  color: #333;
  font-size: 28px;
  font-weight: 600;
}

.page-title-card .subtitle {
  margin: 5px 0 0 0;
  color: #666;
  font-size: 14px;
}

.btn-secondary-action {
  background: linear-gradient(45deg, #6c757d, #495057);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s;
  width: 100%;
}

.btn-secondary-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.card {
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  border: none;
  margin-bottom: 20px;
}

.card-header {
  background: linear-gradient(45deg, #667eea, #764ba2);
  color: white;
  border-radius: 10px 10px 0 0 !important;
  padding: 15px 20px;
}

.card-header h5 {
  margin: 0;
  font-weight: 600;
}

.locales-selector .arrow-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  padding-top: 32px;
}

.form-label {
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
}

.form-control {
  border-radius: 8px;
  border: 2px solid #e0e0e0;
  padding: 10px 15px;
}

.form-control:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.productos-list {
  max-height: 400px;
  overflow-y: auto;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px;
}

.producto-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: white;
  border-radius: 8px;
  margin-bottom: 10px;
  transition: all 0.2s;
}

.producto-item:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transform: translateX(5px);
}

.producto-info {
  flex: 1;
}

.producto-nombre {
  font-weight: 600;
  color: #333;
  margin-bottom: 5px;
}

.producto-meta {
  font-size: 12px;
}

.producto-seleccionado {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 10px;
}

.producto-sel-info {
  flex: 1;
  min-width: 0;
}

.producto-sel-nombre {
  font-weight: 600;
  color: #333;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.producto-sel-meta {
  font-size: 12px;
  color: #666;
}

.producto-sel-cantidad {
  width: 150px;
}

.producto-sel-cantidad input[type="number"] {
  width: 60px !important;
  min-width: 60px;
}

.total-items {
  padding: 15px;
  background: linear-gradient(45deg, #667eea15, #764ba215);
  border-radius: 8px;
  text-align: center;
  font-size: 18px;
  color: #667eea;
}

.sticky-top {
  position: sticky;
  top: 20px;
}

@media (max-width: 768px) {
  .sticky-top {
    position: relative;
    top: 0;
  }
  
  .locales-selector .arrow-container {
    padding-top: 10px;
    padding-bottom: 10px;
  }
  
  .locales-selector .arrow-container i {
    transform: rotate(90deg);
  }
}

/* Modal Detalle */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-detalle {
  background: white;
  border-radius: 12px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-header-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 25px;
  border-bottom: 2px solid #e0e0e0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px 12px 0 0;
}

.modal-header-custom h4 {
  margin: 0;
  font-size: 20px;
}

.btn-close-custom {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 18px;
}

.btn-close-custom:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body-custom {
  padding: 25px;
}

.info-section {
  margin-bottom: 25px;
}

.info-item {
  margin-bottom: 15px;
}

.info-item label {
  font-weight: 600;
  color: #666;
  font-size: 13px;
  margin-bottom: 5px;
  display: block;
}

.info-item p {
  margin: 0;
  font-size: 15px;
  color: #333;
}

.productos-section {
  margin-bottom: 25px;
}

.productos-section h5 {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 15px;
  color: #333;
}

.comentarios-section {
  margin-bottom: 25px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

.comentarios-section h5 {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 10px;
  color: #333;
}

.comentario-text {
  margin: 0;
  color: #666;
  line-height: 1.6;
}

.acciones-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 2px solid #e0e0e0;
}
</style>
