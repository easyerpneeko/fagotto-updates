<template>
    <div class="pedido-final-container">
        <!-- Pantalla de Bienvenida -->
        <div v-if="!inicioSesion" class="welcome-screen">
            <div class="welcome-content">
                <div class="welcome-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h1 class="welcome-title">Bienvenido al Nuevo Sistema de Pedidos</h1>
                <p class="welcome-subtitle">Sistema de precios centralizados</p>
                <p class="welcome-description">
                    Realiza tus pedidos de forma rápida y sencilla.<br>
                    Selecciona los productos que necesitas y completa tu pedido en minutos.
                </p>
                <button @click="iniciarPedido" class="btn-iniciar">
                    <i class="fas fa-rocket"></i> Comenzar Pedido
                </button>
                <div class="welcome-info">
                    <div class="info-badge">
                        <i class="fas fa-check-circle"></i> Precios actualizados
                    </div>
                    <div class="info-badge">
                        <i class="fas fa-bolt"></i> Proceso rápido
                    </div>
                    <div class="info-badge">
                        <i class="fas fa-shield-alt"></i> Seguro
                    </div>
                </div>
            </div>
        </div>

        <!-- Interfaz Principal de Pedidos -->
        <div v-else class="pedidos-container">
            <div class="pedidos-main">
                <!-- Header Responsivo -->
                <div class="pedidos-header fade-in-up">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-md-6 col-12">
                            <div class="pedidos-title-card card">
                                <div class="card-body">
                                    <h5>🛒 Tu Pedido</h5>
                                    <span>{{ this.app && this.app.Name ? this.app.Name : 'Cargando...' }} • {{ this.date }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-6 mt-3 mt-md-0">
                            <button @click="mostrarHistorial = !mostrarHistorial" class="btn-urgente w-100">
                                <i class="fas fa-history"></i>
                                {{ mostrarHistorial ? 'Pedido' : 'Historial' }}
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-6 col-6 mt-3 mt-md-0" style="display: none;">
                            <button @click="abrirAdminProductos" class="btn-urgente w-100" style="background: linear-gradient(45deg, #6c757d, #495057);">
                                <i class="fas fa-cog"></i>
                                Admin
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 mt-3 mt-lg-0">
                            <div class="total-badge-responsive">
                                <span class="total-label">Total:</span>
                                <span class="total-value">${{ formatNumber(totalPedido) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Pedidos -->
                <div v-if="mostrarHistorial" class="section-card fade-in-up">
                    <div class="section-card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <h3 class="m-0"><i class="fas fa-clock"></i> Historial de Pedidos ({{ historialPedidos.length }})</h3>
                            <button @click="cargarHistorial" class="btn-refresh-small" :disabled="loadingHistorial" style="background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">
                                <i class="fas fa-sync-alt" :class="{ 'fa-spin': loadingHistorial }"></i>
                            </button>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <div v-if="loadingHistorial" class="loading-modern">
                            <i class="fas fa-spinner fa-spin"></i> Cargando historial...
                        </div>

                        <div v-else-if="!historialPedidos || historialPedidos.length === 0" class="empty-state fade-in-up">
                            <i class="fas fa-inbox fa-3x"></i>
                            <p>No hay pedidos registrados</p>
                        </div>

                        <div v-else class="historial-grid">
                            <div v-for="pedido in historialPedidos" :key="pedido.id" class="historial-card">
                        <div class="historial-card-header">
                            <div class="pedido-numero">
                                <i class="fas fa-hashtag"></i> {{ pedido.id }}
                            </div>
                            <div class="pedido-status" :class="'status-' + pedido.status">
                                {{ pedido.status }}
                            </div>
                        </div>
                        <div class="historial-card-body">
                            <div class="historial-info">
                                <div class="info-row">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ formatDate(pedido.created_at) }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-user"></i>
                                    <span>{{ pedido.contact_name }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-credit-card"></i>
                                    <span>{{ pedido.paymode }}</span>
                                </div>
                                <div class="info-row" v-if="pedido.comment">
                                    <i class="fas fa-comment"></i>
                                    <span>{{ pedido.comment }}</span>
                                </div>
                            </div>
                            <div class="historial-productos">
                                <h5>Productos:</h5>
                                <div v-for="(producto, index) in parseProducts(pedido.products)" :key="index" class="producto-item">
                                    <span class="producto-qty">{{ producto.quantity }}x</span>
                                    <span class="producto-name">{{ producto.name }}</span>
                                    <span class="producto-price">${{ formatNumber(parseFloat(producto.price) * parseInt(producto.quantity)) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="historial-card-footer">
                            <div class="pedido-total">
                                <span>Total:</span>
                                <strong>${{ formatNumber(parseFloat(pedido.price)) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>

                <!-- Formulario Compacto -->
                <div v-if="!mostrarHistorial" class="section-card fade-in-up">
                    <div class="section-card-header">
                        <h3 class="m-0">📝 Datos del Pedido</h3>
                    </div>
                    <div class="section-card-body">
                        <!-- Información del Negocio -->
                        <div class="business-info">
                            <div class="info-item">
                                <div class="info-label">🏢 Local</div>
                                <p class="info-value">{{ this.app && this.app.Name ? this.app.Name : 'Cargando...' }}</p>
                            </div>
                            <div class="info-item">
                                <div class="info-label">📅 Fecha</div>
                                <p class="info-value">{{ this.date }}</p>
                            </div>
                        </div>

                        <!-- Formulario Moderno -->
                        <form class="modern-form">
                            <div class="form-group-modern">
                                <input v-model="name" type="text" placeholder="Tu nombre completo*" class="form-control-modern">
                                <i class="input-icon fas fa-user"></i>
                            </div>
                            <div class="form-group-modern">
                                <input v-model="phone" type="text" placeholder="Número de teléfono*" class="form-control-modern">
                                <i class="input-icon fas fa-phone"></i>
                            </div>
                            <div class="form-group-modern">
                                <select v-model="paymode" class="form-control-modern select-modern">
                                    <option value="Metodo de pago" disabled>💳 Selecciona método de pago</option>
                                    <option v-for="paymode in paymodes" :value="paymode.name" :key="paymode.id">
                                        {{ paymode.name }}
                                    </option>
                                </select>
                                <i class="input-icon fas fa-credit-card"></i>
                            </div>
                            <div class="form-group-modern col-span-full">
                                <textarea v-model="comment" rows="3" placeholder="Detalles adicionales del pedido*" class="form-control-modern textarea-modern"></textarea>
                                <i class="input-icon fas fa-comment-alt"></i>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Productos en Cards -->
                <div v-if="!mostrarHistorial" class="section-card fade-in-up">
                    <div class="section-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="m-0">📦 Selecciona tus Productos ({{ cantidadProductos }})</h3>
                        <button @click="cargarPreciosCentralizados" class="btn-refresh" style="background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="section-card-body">
                        <div v-if="loadingProducts" class="loading-modern">
                            <i class="fas fa-spinner fa-spin"></i> Cargando productos...
                        </div>

                        <!-- Grid de Productos por Categoría -->
                        <div v-else>
                            <div v-for="categoria in categorias" :key="categoria" class="categoria-group">
                                <h4 class="categoria-title">{{ getCategoriaEmoji(categoria) }} {{ categoria.toUpperCase() }}</h4>
                                <div class="productos-grid-responsive">
                                    <div v-for="producto in getProductosByCategoria(categoria)" :key="producto.id" 
                                        class="producto-card-modern"
                                        :class="{ 
                                            'selected': producto.cantidad > 0,
                                            'producto-sin-stock': producto.stock !== null && producto.stock <= 0
                                        }">
                                        <div v-if="producto.stock !== null && producto.stock <= 0" class="stock-badge-bloqueado">
                                            🚫 Sin Stock
                                        </div>
                                        <div v-else-if="producto.stock !== null && producto.stock > 0" class="stock-badge-disponible">
                                            � {{ producto.stock }} {{ producto.unidad_medida }}
                                        </div>
                                        <div class="card-header-modern">
                                            <h5 class="producto-nombre">{{ producto.producto }}</h5>
                                            <span class="producto-unidad">{{ producto.unidad_medida }}</span>
                                        </div>
                                        <div class="card-precio-modern">
                                            <span class="precio-label">Precio:</span>
                                            <span class="precio-valor">${{ formatNumber(producto.precio_por_unidad) }}</span>
                                        </div>
                                        <div class="card-controls-modern">
                                            <button @click="decrementar(producto)" class="btn-qty-modern" :disabled="producto.cantidad === 0 || (producto.stock !== null && producto.stock <= 0)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                v-model.number="producto.cantidad" 
                                                min="0"
                                                class="input-qty-modern"
                                                :disabled="producto.stock !== null && producto.stock <= 0"
                                                @input="calcularTotal">
                                            <button @click="incrementar(producto)" class="btn-qty-modern" :disabled="producto.stock !== null && producto.stock <= 0">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div v-if="producto.cantidad > 0" class="card-subtotal-modern">
                                            Subtotal: <strong>${{ formatNumber(producto.cantidad * producto.precio_por_unidad) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer con botón fijo -->
                <div class="section-card fade-in-up" v-if="!mostrarHistorial">
                    <div class="section-card-body" style="padding: 1.5rem;">
                        <div class="products-indicator">
                            <div class="products-status" :class="totalPedido > 0 ? 'has-products' : 'no-products'">
                                <i class="cart-icon fas fa-shopping-cart"></i>
                                <span v-if="cantidadProductos > 0">
                                    {{ cantidadProductos }} productos seleccionados
                                </span>
                                <span v-else>Sin productos seleccionados</span>
                                <span class="products-badge">{{ cantidadProductos }}</span>
                            </div>
                            <div class="action-buttons">
                                <button @click="newRequest" class="btn-modern btn-success-modern" :disabled="waitResponse || totalPedido === 0">
                                    <i class="fas fa-check-circle"></i>
                                    {{ waitResponse ? 'Enviando...' : 'Finalizar Pedido' }}
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="cantidadProductos > 0" class="price-summary mt-3 p-3" style="background: #f8f9fa; border-radius: 10px; border-left: 4px solid #28a745;">
                            <div class="d-flex justify-content-between align-items-center" style="font-weight: 700; font-size: 1.2em;">
                                <span>💵 Total Final:</span>
                                <span class="text-success">${{ formatNumber(totalPedido) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Administración de Productos -->
        <div class="modal fade" id="modalAdminProductos" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-cog"></i> Administración de Productos
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <!-- Botón para agregar nuevo producto -->
                        <div class="mb-4">
                            <button @click="nuevoProducto" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> Agregar Nuevo Producto
                            </button>
                        </div>

                        <!-- Tabla de productos -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Unidad Venta</th>
                                        <th>Unidad Medida</th>
                                        <th>Precio</th>
                                        <th>Categoría</th>
                                        <th>Activo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="producto in productosCentralizados" :key="producto.id">
                                        <td>{{ producto.id }}</td>
                                        <td>{{ producto.producto }}</td>
                                        <td>{{ producto.unidad_venta }}</td>
                                        <td>{{ producto.unidad_medida }}</td>
                                        <td>${{ formatNumber(producto.precio_por_unidad) }}</td>
                                        <td>
                                            <span class="badge badge-primary">{{ producto.categoria }}</span>
                                        </td>
                                        <td>
                                            <span :class="producto.activo ? 'badge badge-success' : 'badge badge-secondary'">
                                                {{ producto.activo ? 'Sí' : 'No' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button @click="editarProducto(producto)" class="btn btn-sm btn-warning mr-1">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="confirmarEliminar(producto)" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Editar/Crear Producto -->
        <div class="modal fade" id="modalEditarProducto" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-box"></i> {{ productoEditando.id ? 'Editar' : 'Nuevo' }} Producto
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <form @submit.prevent="guardarProducto">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-tag"></i> Nombre del Producto *</label>
                                        <input v-model="productoEditando.producto" type="text" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-weight"></i> Unidad Venta *</label>
                                        <input v-model.number="productoEditando.unidad_venta" type="number" step="0.01" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-ruler"></i> Unidad Medida *</label>
                                        <select v-model="productoEditando.unidad_medida" class="form-control" required>
                                            <option value="kg">kg</option>
                                            <option value="unidad">unidad</option>
                                            <option value="litro">litro</option>
                                            <option value="gramo">gramo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-dollar-sign"></i> Precio por Unidad *</label>
                                        <input v-model.number="productoEditando.precio_por_unidad" type="number" step="1" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-list"></i> Categoría *</label>
                                        <select v-model="productoEditando.categoria" class="form-control" required>
                                            <option value="insumos">Insumos</option>
                                            <option value="salsas">Salsas</option>
                                            <option value="ciabatta">Ciabatta</option>
                                            <option value="pastas">Pastas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="switchActivo" v-model="productoEditando.activo">
                                            <label class="custom-control-label" for="switchActivo">
                                                <i class="fas fa-toggle-on"></i> Producto Activo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" @click="guardarProducto" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Loader from '@/helpers/Loader';
import FormatNumber from '@/helpers/FormatNumber.js';
import moment from 'moment';

export default {
    name: 'PedidoFinal',
    components: {
        
    },
    data() {
        return {
            // Control de pantallas
            inicioSesion: false,
            mostrarHistorial: false,
            loadingHistorial: false,
            historialPedidos: [],
            
            // Productos centralizados desde DB maestra
            productosCentralizados: [],
            loadingProducts: false,
            stockRefreshInterval: null, // Timer para actualizar stock
            
            // Administración de productos
            productoEditando: {
                id: null,
                producto: '',
                unidad_venta: 1,
                unidad_medida: 'kg',
                precio_por_unidad: 0,
                categoria: 'insumos',
                activo: 1
            },
            
            // Formulario
            name: '',
            phone: '+56',
            comment: '',
            paymode: 'Transferencia',
            
            // Datos de negocio
            app: null,
            date: moment().format('YYYY-MM-DD HH:mm:ss'),
            
            // Control
            submitted: false,
            waitResponse: false,
            
            // Totales
            totalPedido: 0,
            
            // Opciones
            paymodes: [
                { id: 1, name: 'Transferencia' },
                { id: 2, name: 'Efectivo' },
                { id: 3, name: 'Tarjeta' }
            ]
        }
    },
    async beforeCreate() {
        var request = await this.$store.dispatch('main/refreshData', '?slim');
        this.app = request.data;
    },
    async mounted() {
        this.name = this.me.fullname;
        // Cargar productos al iniciar
        await this.cargarPreciosCentralizados();
        // Si ya inició sesión, cargar historial
        if (this.inicioSesion && this.app && this.app.Id) {
            this.cargarHistorial();
        }
        // Iniciar actualización automática de stock cada 10 segundos
        this.iniciarActualizacionStock();
    },
    beforeDestroy() {
        // Limpiar el intervalo cuando se destruye el componente
        if (this.stockRefreshInterval) {
            clearInterval(this.stockRefreshInterval);
        }
    },
    computed: {
        me: { get() { return this.$store.getters['main/user']; } },
        
        // Categorías únicas de productos ordenadas
        categorias() {
            const cats = [...new Set(this.productosCentralizados.map(p => p.categoria))];
            // Orden específico: insumos, salsas, ciabatta, pastas
            const orden = { 'insumos': 1, 'salsas': 2, 'ciabatta': 3, 'pastas': 4 };
            return cats.sort((a, b) => {
                const ordenA = orden[a.toLowerCase()] || 999;
                const ordenB = orden[b.toLowerCase()] || 999;
                return ordenA - ordenB;
            });
        },
        
        // Cantidad de productos seleccionados
        cantidadProductos() {
            return this.productosCentralizados.filter(p => p.cantidad > 0).length;
        },
        
        isValidName: {
            get() { return this.name.length > 0 }
        },
        isValidPhone: {
            get() { 
                const regex = /^\+56\d{9}$/;
                return regex.test(this.phone);
            }
        },
        isValidComment: {
            get() { return this.comment.length > 0 }
        },
        isValidPaymode: {
            get() { return this.paymode !== 'Metodo de pago' }
        }
    },
    methods: {
        formatNumber(value) {
            return FormatNumber.format(value);
        },
        
        formatDate(date) {
            return moment(date).format('DD/MM/YYYY HH:mm');
        },
        
        parseProducts(productsJson) {
            try {
                const productos = JSON.parse(productsJson);
                console.log('🛒 Productos parseados:', productos);
                return productos;
            } catch (e) {
                console.error('❌ Error parseando productos:', e);
                return [];
            }
        },
        
        iniciarPedido() {
            this.inicioSesion = true;
            // Cargar historial solo si app está disponible
            if (this.app && this.app.Id) {
                this.cargarHistorial();
            }
        },
        
        incrementar(producto) {
            producto.cantidad++;
            this.calcularTotal();
        },
        
        decrementar(producto) {
            if (producto.cantidad > 0) {
                producto.cantidad--;
                this.calcularTotal();
            }
        },
        
        getProductosByCategoria(categoria) {
            return this.productosCentralizados
                .filter(p => p.categoria === categoria)
                .sort((a, b) => a.producto.localeCompare(b.producto));
        },
        
        getCategoriaEmoji(categoria) {
            const emojis = {
                'insumos': '📦',
                'salsas': '🍝',
                'ciabatta': '🥖',
                'pastas': '🍝',
                'general': '🛒'
            };
            return emojis[categoria.toLowerCase()] || '📌';
        },
        
        iniciarActualizacionStock() {
            // Actualizar stock cada 10 segundos
            this.stockRefreshInterval = setInterval(async () => {
                if (!this.mostrarHistorial && this.inicioSesion) {
                    await this.actualizarStockSilencioso();
                }
            }, 10000); // 10 segundos
            
            console.log('🔄 Actualización automática de stock iniciada (cada 10s)');
        },
        
        async actualizarStockSilencioso() {
            try {
                // Guardar las cantidades actuales del usuario
                const cantidadesActuales = {};
                this.productosCentralizados.forEach(p => {
                    if (p.cantidad > 0) {
                        cantidadesActuales[p.id] = p.cantidad;
                    }
                });
                
                // Obtener stock actualizado
                const response = await this.$store.dispatch('products/getPreciosCentralizados');
                
                let productos = null;
                if (response.success && response.data) {
                    if (response.data.data && Array.isArray(response.data.data)) {
                        productos = response.data.data;
                    } else if (Array.isArray(response.data)) {
                        productos = response.data;
                    } else if (response.data.success && Array.isArray(response.data.data)) {
                        productos = response.data.data;
                    }
                }
                
                if (productos && Array.isArray(productos)) {
                    // Detectar productos que se quedaron sin stock
                    const productosAgotados = [];
                    
                    this.productosCentralizados = productos.map(p => {
                        const producto = {
                            ...p,
                            precio_por_unidad: parseFloat(p.precio_por_unidad),
                            cantidad: cantidadesActuales[p.id] || 0 // Mantener cantidad del carrito
                        };
                        
                        // Verificar si el producto se quedó sin stock y el usuario tenía cantidad
                        if (producto.stock !== null && producto.stock <= 0 && cantidadesActuales[p.id] > 0) {
                            productosAgotados.push(producto.producto);
                            producto.cantidad = 0; // Quitar del carrito
                        }
                        
                        return producto;
                    });
                    
                    // Alertar si algún producto se agotó
                    if (productosAgotados.length > 0) {
                        this.$awn.alert(`⚠️ Se agotó: ${productosAgotados.join(', ')}. Fue removido de tu pedido.`);
                        this.calcularTotal();
                    }
                    
                    console.log('🔄 Stock actualizado en tiempo real');
                }
            } catch (error) {
                console.error('❌ Error actualizando stock:', error);
            }
        },
        
        async cargarPreciosCentralizados() {
            try {
                this.loadingProducts = true;
                console.log('📡 Cargando precios centralizados desde DB maestra via Server app...');
                
                // Llamar al Server app que consulta easyerp.pedidofinal_precios
                const response = await this.$store.dispatch('products/getPreciosCentralizados');
                
                console.log('🔍 Respuesta completa:', JSON.stringify(response, null, 2));
                console.log('🔍 response.data:', response.data);
                console.log('🔍 Tipo de response.data:', typeof response.data);
                console.log('🔍 Es array?', Array.isArray(response.data));
                
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
                    // Agregar campo 'cantidad' a cada producto
                    this.productosCentralizados = productos.map(p => {
                        console.log(`💰 Producto: ${p.producto} - Precio DB: ${p.precio_por_unidad} (tipo: ${typeof p.precio_por_unidad})`);
                        return {
                            ...p,
                            precio_por_unidad: parseFloat(p.precio_por_unidad),
                            cantidad: 0
                        };
                    });
                    
                    console.log('✅ Precios centralizados cargados:', this.productosCentralizados.length, 'productos');
                    console.log('📦 Productos:', this.productosCentralizados);
                } else {
                    console.error('❌ No se pudo encontrar el array de productos en la respuesta');
                    throw new Error('La respuesta del servidor no contiene un array de productos');
                }
            } catch (error) {
                console.error('❌ Error cargando precios centralizados:', error.message);
                console.error('❌ Error completo:', error);
                this.$awn.alert('Error al cargar precios desde servidor');
            } finally {
                this.loadingProducts = false;
            }
        },
        
        calcularTotal() {
            this.totalPedido = this.productosCentralizados.reduce((total, producto) => {
                return total + (producto.cantidad * producto.precio_por_unidad);
            }, 0);
        },
        
        validar_form() {
            if (!this.isValidName) {
                this.$awn.alert("Ingresa tu nombre completo");
                return false;
            }
            if (!this.isValidPhone) {
                this.$awn.alert("Ingresa un teléfono válido (+569XXXXXXXX)");
                return false;
            }
            if (!this.isValidComment) {
                this.$awn.alert("Agrega comentarios al pedido");
                return false;
            }
            if (!this.isValidPaymode) {
                this.$awn.alert("Selecciona un método de pago");
                return false;
            }
            if (this.totalPedido === 0) {
                this.$awn.alert("Debes seleccionar al menos un producto");
                return false;
            }
            return true;
        },
        
        async newRequest() {
            this.submitted = true;
            if (!this.validar_form()) {
                return;
            }
            
            // Actualizar stock una última vez antes de enviar
            await this.actualizarStockSilencioso();
            
            // Filtrar solo productos con cantidad > 0
            const productosSeleccionados = this.productosCentralizados
                .filter(p => p.cantidad > 0)
                .map(p => ({
                    id: p.id,
                    name: p.producto,
                    quantity: p.cantidad,
                    price: p.precio_por_unidad,
                    unidad_medida: p.unidad_medida,
                    unidad_venta: p.unidad_venta,
                    vasos: 0 // Campo requerido por RequestsController
                }));
            
            // Validar que no haya productos sin stock
            const productosSinStock = productosSeleccionados.filter(p => {
                const productoDB = this.productosCentralizados.find(prod => prod.id === p.id);
                return productoDB && productoDB.stock !== null && productoDB.stock <= 0;
            });
            
            if (productosSinStock.length > 0) {
                const nombres = productosSinStock.map(p => p.name).join(', ');
                this.$awn.alert(`⚠️ Los siguientes productos se agotaron: ${nombres}. Actualiza tu pedido.`);
                // Remover productos sin stock del carrito
                productosSinStock.forEach(p => {
                    const producto = this.productosCentralizados.find(prod => prod.id === p.id);
                    if (producto) producto.cantidad = 0;
                });
                this.calcularTotal();
                return;
            }
            
            const data = {
                contact_name: this.name,
                contact_phone: this.phone,
                paymode: this.paymode,
                status: 'nuevo',
                products: JSON.stringify(productosSeleccionados),
                comment: this.comment,
                price: this.totalPedido,
                subtotal: this.totalPedido,
                iva: 0,
                emergency: 0,
                despacho: 0,
                app_id: this.app.Id
            };
            
            console.log('📤 Datos a enviar:', data);
            console.log('🔑 app_id tipo:', typeof this.app.Id, 'valor:', this.app.Id);
            
            var formData = new FormData();
            for (let key in data) if (data[key]) formData.append(key, data[key]);
            
            this.waitResponse = true;
            Loader.fullPage();
            let request = await this.$store.dispatch('requests/newRequestPedidoFinal', formData);
            Loader.hide();
            
            if (request.success) {
                this.$awn.success('Pedido enviado exitosamente', { labels: { success: 'CORRECTO' } });
                
                // Limpiar formulario
                this.name = this.me.fullname;
                this.phone = "+56";
                this.paymode = 'Transferencia';
                this.comment = "";
                this.totalPedido = 0;
                
                // Resetear cantidades
                this.productosCentralizados.forEach(p => p.cantidad = 0);
                
                this.submitted = false;
                
                // Recargar historial y cambiar a vista de historial
                await this.cargarHistorial();
                this.mostrarHistorial = true;
            } else {
                console.log(request.data);
                this.$awn.alert(request.data.message || 'Error al crear pedido');
            }
            this.waitResponse = false;
        },
        
        async cargarHistorial() {
            try {
                // Validar que app esté disponible
                if (!this.app || !this.app.Id) {
                    console.warn('⚠️ App no disponible aún, esperando...');
                    return;
                }
                
                this.loadingHistorial = true;
                
                // Usar la misma estructura que pedidos.vue
                var params = '?params=true&appId=' + this.app.Id;
                
                var request = await this.$store.dispatch('requests/getRequests', params);
                console.log('📦 Historial pedidos:', request);
                
                if (!request.success) {
                    this.$awn.alert(request.data);
                    this.historialPedidos = [];
                    return;
                }
                
                // Misma estructura que pedidos.vue: request.data.items
                if (request.data && request.data.items) {
                    // Ordenar por fecha más reciente primero
                    this.historialPedidos = request.data.items.sort((a, b) => {
                        return new Date(b.created_at) - new Date(a.created_at);
                    });
                    console.log('✅ Historial cargado:', this.historialPedidos.length, 'pedidos');
                } else {
                    this.historialPedidos = [];
                }
            } catch (error) {
                console.error('❌ Error cargando historial:', error);
                this.$awn.alert('Error al cargar historial de pedidos');
                this.historialPedidos = [];
            } finally {
                this.loadingHistorial = false;
            }
        },
        
        // ==================== MÉTODOS DE ADMINISTRACIÓN DE PRODUCTOS ====================
        
        abrirAdminProductos() {
            $('#modalAdminProductos').modal('show');
        },
        
        nuevoProducto() {
            this.productoEditando = {
                id: null,
                producto: '',
                unidad_venta: 1,
                unidad_medida: 'kg',
                precio_por_unidad: 0,
                categoria: 'insumos',
                activo: 1
            };
            $('#modalEditarProducto').modal('show');
        },
        
        editarProducto(producto) {
            this.productoEditando = {
                ...producto,
                activo: producto.activo ? 1 : 0
            };
            $('#modalEditarProducto').modal('show');
        },
        
        async guardarProducto() {
            try {
                // Validar campos requeridos
                if (!this.productoEditando.producto || !this.productoEditando.unidad_venta || 
                    !this.productoEditando.precio_por_unidad || !this.productoEditando.categoria) {
                    this.$awn.alert('Por favor completa todos los campos requeridos');
                    return;
                }
                
                Loader.fullPage();
                
                const datos = {
                    producto: this.productoEditando.producto,
                    unidad_venta: parseFloat(this.productoEditando.unidad_venta),
                    unidad_medida: this.productoEditando.unidad_medida,
                    precio_por_unidad: parseFloat(this.productoEditando.precio_por_unidad),
                    categoria: this.productoEditando.categoria.toLowerCase(),
                    activo: this.productoEditando.activo ? 1 : 0
                };
                
                let request;
                
                if (this.productoEditando.id) {
                    // Actualizar producto existente
                    request = await this.$store.dispatch('products/updatePedidoFinalProduct', {
                        id: this.productoEditando.id,
                        data: datos
                    });
                } else {
                    // Crear nuevo producto
                    request = await this.$store.dispatch('products/createPedidoFinalProduct', datos);
                }
                
                Loader.hide();
                
                if (request.success) {
                    this.$awn.success(this.productoEditando.id ? 'Producto actualizado correctamente' : 'Producto creado correctamente');
                    $('#modalEditarProducto').modal('hide');
                    
                    // Recargar productos
                    await this.cargarPreciosCentralizados();
                } else {
                    this.$awn.alert(request.data || 'Error al guardar el producto');
                }
            } catch (error) {
                Loader.hide();
                console.error('❌ Error guardando producto:', error);
                this.$awn.alert('Error al guardar el producto');
            }
        },
        
        confirmarEliminar(producto) {
            if (confirm(`¿Estás seguro de eliminar el producto "${producto.producto}"?\n\nEsta acción no se puede deshacer.`)) {
                this.eliminarProducto(producto.id);
            }
        },
        
        async eliminarProducto(id) {
            try {
                Loader.fullPage();
                
                const request = await this.$store.dispatch('products/deletePedidoFinalProduct', id);
                
                Loader.hide();
                
                if (request.success) {
                    this.$awn.success('Producto eliminado correctamente');
                    
                    // Recargar productos
                    await this.cargarPreciosCentralizados();
                } else {
                    this.$awn.alert(request.data || 'Error al eliminar el producto');
                }
            } catch (error) {
                Loader.hide();
                console.error('❌ Error eliminando producto:', error);
                this.$awn.alert('Error al eliminar el producto');
            }
        },
        
        // ==================== FIN MÉTODOS DE ADMINISTRACIÓN ====================
        
        formatDate(date) {
            return moment(date).format('DD/MM/YYYY HH:mm');
        },
        
        formatNumber(number) {
            return FormatNumber.format(number);
        }
    }
}
</script>

<style scoped>
/* Importar estilos base responsivos de pedidos.css */
@import '../assets/css/pedidos.css';

/* ==================== PANTALLA DE BIENVENIDA ==================== */
.welcome-screen {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.welcome-content {
    text-align: center;
    max-width: 600px;
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-icon {
    width: 120px;
    height: 120px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.welcome-icon i {
    font-size: 60px;
    color: #667eea;
}

.welcome-title {
    font-size: 42px;
    color: white;
    margin: 0 0 15px 0;
    font-weight: 700;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.welcome-subtitle {
    font-size: 20px;
    color: rgba(255,255,255,0.9);
    margin: 0 0 20px 0;
    font-weight: 300;
}

.welcome-description {
    font-size: 16px;
    color: rgba(255,255,255,0.8);
    line-height: 1.6;
    margin-bottom: 40px;
}

.btn-iniciar {
    padding: 18px 50px;
    background: white;
    color: #667eea;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.btn-iniciar:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.3);
}

.btn-iniciar i {
    margin-right: 10px;
}

.welcome-info {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.info-badge {
    background: rgba(255,255,255,0.2);
    padding: 10px 20px;
    border-radius: 20px;
    color: white;
    font-size: 14px;
    backdrop-filter: blur(10px);
}

.info-badge i {
    margin-right: 8px;
}

/* ==================== INTERFAZ PRINCIPAL ==================== */
.pedido-final-container {
    background: #f5f7fa;
    min-height: 100vh;
}

.pedido-final-main {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px 20px 100px 20px;
}

/* Header Moderno */
.modern-header {
    background: white;
    border-radius: 12px;
    padding: 25px 30px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.modern-header h2 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 28px;
}

.header-subtitle {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
}

.header-right {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-historial {
    padding: 12px 24px;
    background: white;
    border: 2px solid #667eea;
    color: #667eea;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-historial:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.total-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 15px 30px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.total-label {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
}

.total-value {
    color: white;
    font-size: 24px;
    font-weight: 700;
}

/* ==================== HISTORIAL DE PEDIDOS ==================== */
.historial-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}

.historial-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.historial-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 22px;
}

.historial-header h3 i {
    margin-right: 10px;
    color: #667eea;
}

.btn-refresh-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: #f5f7fa;
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-refresh-small:hover:not(:disabled) {
    background: #667eea;
    color: white;
    transform: rotate(180deg);
}

.btn-refresh-small:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #95a5a6;
}

.empty-state i {
    color: #e0e6ed;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 18px;
    margin: 0;
}

.historial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.historial-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid #e0e6ed;
    transition: all 0.3s;
}

.historial-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    border-color: #667eea;
}

.historial-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pedido-numero {
    color: white;
    font-weight: 700;
    font-size: 18px;
}

.pedido-status {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-nuevo {
    background: #3498db;
    color: white;
}

.status-preparando {
    background: #f39c12;
    color: white;
}

.status-listo {
    background: #27ae60;
    color: white;
}

.status-entregado {
    background: #95a5a6;
    color: white;
}

.status-cancelado {
    background: #e74c3c;
    color: white;
}

.historial-card-body {
    padding: 20px;
}

.historial-info {
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    color: #2c3e50;
}

.info-row i {
    color: #667eea;
    width: 20px;
}

.historial-productos {
    background: #f5f7fa;
    padding: 15px;
    border-radius: 8px;
}

.historial-productos h5 {
    margin: 0 0 12px 0;
    color: #2c3e50;
    font-size: 14px;
    font-weight: 600;
}

.producto-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #e0e6ed;
}

.producto-item:last-child {
    border-bottom: none;
}

.producto-qty {
    background: #667eea;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    min-width: 40px;
    text-align: center;
}

.producto-name {
    flex: 1;
    color: #2c3e50;
    font-size: 14px;
}

.producto-price {
    color: #27ae60;
    font-weight: 600;
    font-size: 14px;
}

.historial-card-footer {
    background: #f8f9fa;
    padding: 15px 20px;
    border-top: 2px solid #e0e6ed;
}

.pedido-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 16px;
    color: #2c3e50;
}

.pedido-total strong {
    color: #27ae60;
    font-size: 20px;
}

/* Formulario Compacto */
.form-compact {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.form-field label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 500;
    font-size: 14px;
}

.form-field label i {
    margin-right: 8px;
    color: #667eea;
}

.form-field input,
.form-field select,
.form-field textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.form-field.full-width {
    grid-column: 1 / -1;
}

/* Sección de Productos */
.productos-section {
    margin-bottom: 25px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 22px;
}

.btn-refresh {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    border: 2px solid #e0e6ed;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-refresh:hover {
    border-color: #667eea;
    color: #667eea;
    transform: rotate(180deg);
}

.loading-modern {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 12px;
    color: #7f8c8d;
    font-size: 16px;
}

.loading-modern i {
    font-size: 24px;
    margin-right: 10px;
}

/* Categorías */
.categoria-group {
    margin-bottom: 30px;
}

.categoria-title {
    color: #2c3e50;
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 20px 0;
    padding: 12px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(102,126,234,0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Grid de Productos */
.productos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.producto-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 3px solid transparent;
    position: relative;
    overflow: hidden;
}

.producto-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.3s;
}

.producto-card:hover::before {
    opacity: 1;
}

.producto-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 35px rgba(102,126,234,0.3), 0 5px 15px rgba(0,0,0,0.1);
    border-color: rgba(102,126,234,0.2);
}

.producto-card.selected {
    border-color: #667eea;
    background: linear-gradient(145deg, rgba(102,126,234,0.08) 0%, rgba(118,75,162,0.08) 100%);
    box-shadow: 0 8px 25px rgba(102,126,234,0.25);
}

.card-header {
    margin-bottom: 15px;
    position: relative;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
}

.producto-nombre {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.3;
}

.producto-unidad {
    display: inline-block;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(102,126,234,0.3);
}

.card-precio {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 15px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
    border-radius: 12px;
    border: 2px solid #e0e6ed;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.precio-label {
    color: #7f8c8d;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.precio-valor {
    color: #27ae60;
    font-size: 22px;
    font-weight: 800;
    text-shadow: 0 2px 4px rgba(39,174,96,0.2);
}

.card-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 12px;
}

.btn-qty {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.btn-qty:hover:not(:disabled) {
    transform: scale(1.15) rotate(5deg);
    box-shadow: 0 6px 20px rgba(102,126,234,0.5);
}

.btn-qty:active:not(:disabled) {
    transform: scale(0.95);
}

.btn-qty:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    background: #e0e6ed;
    box-shadow: none;
}

.input-qty {
    flex: 1;
    max-width: 100px;
    text-align: center;
    padding: 12px;
    border: 3px solid #e0e6ed;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    background: white;
    transition: all 0.3s;
}

.input-qty:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
    transform: scale(1.05);
}

.card-subtotal {
    text-align: center;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102,126,234,0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 4px 15px rgba(102,126,234,0.4);
    }
    50% {
        box-shadow: 0 6px 25px rgba(102,126,234,0.6);
    }
}

.card-subtotal strong {
    font-size: 18px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Footer Fijo */
.footer-fixed {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 2px solid #e0e6ed;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    z-index: 1000;
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.footer-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.items-count {
    color: #7f8c8d;
    font-size: 13px;
}

.footer-total {
    color: #2c3e50;
    font-size: 16px;
}

.footer-total strong {
    color: #27ae60;
    font-size: 24px;
    margin-left: 10px;
}

.btn-finalizar {
    padding: 15px 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(102,126,234,0.4);
}

.btn-finalizar:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102,126,234,0.5);
}

.btn-finalizar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-finalizar i {
    margin-right: 10px;
}

/* Responsive */
@media (max-width: 1400px) {
    .productos-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .productos-grid-responsive {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 1024px) {
    .productos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .productos-grid-responsive {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .modern-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .productos-grid {
        grid-template-columns: 1fr;
    }
    
    .productos-grid-responsive {
        grid-template-columns: 1fr;
    }
    
    .footer-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-finalizar {
        width: 100%;
    }
}

/* Estilos adicionales específicos para pedidofinal */
.total-badge-responsive {
    background: linear-gradient(45deg, #28a745, #20c997);
    padding: 1rem 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: white;
}

.total-badge-responsive .total-label {
    display: block;
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 0.25rem;
}

.total-badge-responsive .total-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}

.categoria-group {
    margin-bottom: 2rem;
}

.categoria-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--primary-color, #007bff);
    padding-bottom: 0.5rem;
    border-bottom: 3px solid var(--primary-color, #007bff);
}

.productos-grid-responsive {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.producto-card-modern {
    background: white;
    border: 2px solid #e0e6ed;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.producto-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.producto-card-modern.selected {
    border-color: #28a745;
    background: linear-gradient(135deg, #ffffff 0%, #f0fff4 100%);
}

.producto-card-modern.producto-sin-stock {
    opacity: 0.6;
    background: #f8f9fa;
    border-color: #dc3545;
    position: relative;
}

.producto-card-modern.producto-sin-stock::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(220, 53, 69, 0.05) 10px,
        rgba(220, 53, 69, 0.05) 20px
    );
    pointer-events: none;
    border-radius: 12px;
}

.stock-badge-bloqueado {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.stock-badge-disponible {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.card-header-modern {
    margin-bottom: 1rem;
}

.card-header-modern .producto-nombre {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #212529;
}

.card-header-modern .producto-unidad {
    font-size: 0.85rem;
    color: #6c757d;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
}

.card-precio-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.card-precio-modern .precio-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.card-precio-modern .precio-valor {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
}

.card-controls-modern {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.btn-qty-modern {
    width: 40px;
    height: 40px;
    border: 2px solid #007bff;
    background: white;
    color: #007bff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-qty-modern:hover:not(:disabled) {
    background: #007bff;
    color: white;
}

.btn-qty-modern:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.input-qty-modern {
    flex: 1;
    height: 40px;
    text-align: center;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
}

.card-subtotal-modern {
    text-align: right;
    padding: 0.75rem;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 8px;
    font-size: 0.95rem;
}

.card-subtotal-modern strong {
    font-size: 1.1rem;
}

/* Historial grid responsivo */
.historial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

@media (max-width: 576px) {
    .historial-grid {
        grid-template-columns: 1fr;
    }
    
    .total-badge-responsive .total-value {
        font-size: 1.2rem;
    }
    
    .categoria-title {
        font-size: 1.1rem;
    }
    
    .productos-grid-responsive {
        grid-template-columns: 1fr;
    }
}

/* Clase col-span-full para el textarea */
.col-span-full {
    grid-column: 1 / -1;
}
</style>

