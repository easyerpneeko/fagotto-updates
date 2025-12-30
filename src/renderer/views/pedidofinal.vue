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
        <div v-else class="pedido-final-main">
            <!-- Header Moderno -->
            <div class="modern-header">
                <div class="header-left">
                    <h2>🛒 Tu Pedido</h2>
                    <p class="header-subtitle">{{ this.app && this.app.Name ? this.app.Name : 'Cargando...' }} • {{ this.date }}</p>
                </div>
                <div class="header-right">
                    <button @click="mostrarHistorial = !mostrarHistorial" class="btn-historial">
                        <i class="fas fa-history"></i>
                        {{ mostrarHistorial ? 'Nuevo Pedido' : 'Historial' }}
                    </button>
                    <div class="total-badge">
                        <span class="total-label">Total:</span>
                        <span class="total-value">${{ formatNumber(totalPedido) }}</span>
                    </div>
                </div>
            </div>

            <!-- Historial de Pedidos -->
            <div v-if="mostrarHistorial" class="historial-container">
                <div class="historial-header">
                    <h3><i class="fas fa-clock"></i> Historial de Pedidos ({{ historialPedidos.length }})</h3>
                    <button @click="cargarHistorial" class="btn-refresh-small" :disabled="loadingHistorial">
                        <i class="fas fa-sync-alt" :class="{ 'fa-spin': loadingHistorial }"></i>
                    </button>
                </div>

                <div v-if="loadingHistorial" class="loading-modern">
                    <i class="fas fa-spinner fa-spin"></i> Cargando historial...
                </div>

                <div v-else-if="!historialPedidos || historialPedidos.length === 0" class="empty-state">
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

            <!-- Formulario Compacto -->
            <div v-if="!mostrarHistorial" class="form-compact">
                <div class="form-grid">
                    <div class="form-field">
                        <label><i class="fas fa-user"></i> Nombre</label>
                        <input v-model="name" type="text" placeholder="Tu nombre completo">
                    </div>
                    <div class="form-field">
                        <label><i class="fas fa-phone"></i> Teléfono</label>
                        <input v-model="phone" type="text" placeholder="+56 9 XXXX XXXX">
                    </div>
                    <div class="form-field">
                        <label><i class="fas fa-credit-card"></i> Método de Pago</label>
                        <select v-model="paymode">
                            <option value="Metodo de pago" disabled>Selecciona</option>
                            <option v-for="paymode in paymodes" :value="paymode.name" :key="paymode.id">
                                {{ paymode.name }}
                            </option>
                        </select>
                    </div>
                    <div class="form-field full-width">
                        <label><i class="fas fa-comment"></i> Comentarios</label>
                        <textarea v-model="comment" rows="2" placeholder="Detalles adicionales"></textarea>
                    </div>
                </div>
            </div>

            <!-- Productos en Cards -->
            <div v-if="!mostrarHistorial" class="productos-section">
                <div class="section-header">
                    <h3>📦 Selecciona tus Productos</h3>
                    <button @click="cargarPreciosCentralizados" class="btn-refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <div v-if="loadingProducts" class="loading-modern">
                    <i class="fas fa-spinner fa-spin"></i> Cargando productos...
                </div>

                <!-- Grid de Productos por Categoría -->
                <div v-else>
                    <div v-for="categoria in categorias" :key="categoria" class="categoria-group">
                        <h4 class="categoria-title">{{ getCategoriaEmoji(categoria) }} {{ categoria.toUpperCase() }}</h4>
                        <div class="productos-grid">
                            <div v-for="producto in getProductosByCategoria(categoria)" :key="producto.id" 
                                class="producto-card"
                                :class="{ 'selected': producto.cantidad > 0 }">
                                <div class="card-header">
                                    <h5 class="producto-nombre">{{ producto.producto }}</h5>
                                    <span class="producto-unidad">{{ producto.unidad_medida }}</span>
                                </div>
                                <div class="card-precio">
                                    <span class="precio-label">Precio:</span>
                                    <span class="precio-valor">${{ formatNumber(producto.precio_por_unidad) }}</span>
                                </div>
                                <div class="card-controls">
                                    <button @click="decrementar(producto)" class="btn-qty" :disabled="producto.cantidad === 0">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                        v-model.number="producto.cantidad" 
                                        min="0"
                                        class="input-qty"
                                        @input="calcularTotal">
                                    <button @click="incrementar(producto)" class="btn-qty">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div v-if="producto.cantidad > 0" class="card-subtotal">
                                    Subtotal: <strong>${{ formatNumber(producto.cantidad * producto.precio_por_unidad) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con botón fijo -->
            <div class="footer-fixed">
                <div class="footer-content">
                    <div class="footer-info">
                        <span class="items-count">{{ cantidadProductos }} productos seleccionados</span>
                        <span class="footer-total">Total: <strong>${{ formatNumber(totalPedido) }}</strong></span>
                    </div>
                    <button @click="newRequest" class="btn-finalizar" :disabled="waitResponse || totalPedido === 0">
                        <i class="fas fa-check-circle"></i>
                        {{ waitResponse ? 'Enviando...' : 'Finalizar Pedido' }}
                    </button>
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
    },
    computed: {
        me: { get() { return this.$store.getters['main/user']; } },
        
        // Categorías únicas de productos
        categorias() {
            const cats = [...new Set(this.productosCentralizados.map(p => p.categoria))];
            return cats.sort();
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
            return this.productosCentralizados.filter(p => p.categoria === categoria);
        },
        
        getCategoriaEmoji(categoria) {
            const emojis = {
                'empaque': '📦',
                'salsas': '🍝',
                'ciabatta': '🥖',
                'general': '🛒'
            };
            return emojis[categoria] || '📌';
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
        }
    }
}
</script>

<style scoped>
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
}

@media (max-width: 1024px) {
    .productos-grid {
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
    
    .footer-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-finalizar {
        width: 100%;
    }
}
</style>
