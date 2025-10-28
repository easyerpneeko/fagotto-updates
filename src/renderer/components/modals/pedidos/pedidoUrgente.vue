<template>
    <div class="modal fade" id="pedidoUrgente" tabindex="-1" role="dialog" aria-labelledby="pedidoUrgente"
        aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog lg-modal modal-dialog-centered" role="document" style="max-width: 1000px; margin: auto;">
            <div class="modal-content">
                <div class="modal-header bg-gradient">
                    <h5 class="modal-title fw-bold">🚨 Preparar pedido urgente</h5>
                    <button type="button" class="close text-white" @click="closeModal(false)" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <!-- 🎃 Banner de descuentos activos -->
                <div v-if="Array.isArray(activeDiscounts) && activeDiscounts.length > 0" class="alert alert-warning mb-0" style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a00 100%); border: none; border-radius: 0; color: white; text-align: center; padding: 15px;">
                    <h5 class="mb-2" style="font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                        🎃 ¡FELICIDADES! HAY DESCUENTOS ACTIVOS 🎃
                    </h5>
                    <p class="mb-1" style="font-size: 1.1rem;">
                        <strong>{{ activeDiscounts.length }}</strong> producto(s) con descuento especial
                    </p>
                    <p class="mb-0" style="font-size: 0.9rem; opacity: 0.95;">
                        ⏰ Válido hasta: <strong>{{ formatFecha(activeDiscounts[0].end_date) }}</strong>
                    </p>
                </div>
                
                <div class="modal-body p-0" style="overflow: auto; max-height: 70vh;">
                    <div class="row g-3 p-3">
                        <div class="col-md-5 p-3">
                            <div class="row d-flex">
                                <div class="col-md-12 mb-3">
                                    <h5 class="modal-title fw-bold">📋 Seleccionar productos</h5>
                                    <hr class="mt-2">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <h6 class="text-muted mb-2 fw-bold">🍽️ Productos disponibles</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <template v-for="(producto, index) in productos">
                                            <button v-if="(producto.name != 'Despacho' && producto.name != 'Queso')" type="button"
                                                @click="addProduct(producto)"
                                                class="btn btn-sm rounded-pill product-btn"
                                                :class="checkProductDiscount(producto) ? 'btn-warning' : 'btn-outline-primary'"
                                                :title="checkProductDiscount(producto) ? '🎃 ¡Producto con descuento!' : 'Click para añadir al pedido urgente'">
                                                <span v-if="checkProductDiscount(producto)" class="discount-badge">🎃 -{{ checkProductDiscount(producto).discount_value }}%</span>
                                                🛒 {{ producto.name }} <i class="fa fa-plus ms-1"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 p-3">
                            <h5 class="modal-title mb-3 fw-bold">📋 Detalle del pedido urgente</h5>
                            
                            <table class="table table-sm table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col"></th>
                                        <th scope="col">Precio Unit.</th>
                                        <th scope="col">Precio Total</th>
                                        <th scope="col" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="productosPedido.length > 0">
                                        <tr v-for="(producto, index) in productosPedido" :key="index">
                                            <td class="fw-bold">{{ producto.name }}</td>

                                            <!-- Cantidad -->
                                           
                                            <td v-if="(producto.category == 4)">
                                                <input min="1" class="form-control form-control-sm" type="number" v-model="producto.quantity"
                                                    @change="calcularMontos()" />
                                                <small class="text-muted">Kg</small>
                                            </td>
                                            <!-- POR UNIDAD -->
                                            <td v-if="(producto.category == 1 && producto.name != 'Vaso' && producto.name != 'Bolsa de Queso')">
                                                <input min="1" class="form-control form-control-sm" type="number" v-model="producto.quantity" @change="calcularMontos()" />
                                                <small class="text-muted">Unidad(es)</small>
                                            </td>
                                            <td v-if="(producto.name == 'Vaso')">
                                                <input min="27" step="27" class="form-control form-control-sm" type="number" v-model="producto.quantity" @change="calcularMontos()" />
                                                <small class="text-muted">Unidad(es)</small>
                                            </td>
                                            <td v-if="(producto.name == 'Bolsa de Queso')">
                                                <input min="1" class="form-control form-control-sm" type="number" v-model="producto.quantity" @change="calcularMontos()" />
                                                <small class="text-muted">{{ producto.price + " gr" }}</small>
                                            </td>
                                            <!-- OPCIONALES -->
                                            <td v-if="(producto.category == 3)">
                                                <input min="1" class="form-control form-control-sm" type="number" v-model="producto.quantity"
                                                    @change="calcularMontos()" />
                                                <small class="text-muted">{{ (producto.name == 'Botella de Huevos 1L' ? 'Botellas(s)' : 'Unidad(es)') }}</small>
                                            </td>
                                             <!-- SALSAS -->
                                            <td v-if="(producto.category == 2)">
                                                <input class="form-control form-control-sm" :min="producto.min_quantity" :step="producto.min_quantity" type="number" v-model="producto.vasos" @change="calcularMontos()" />
                                                <small class="text-muted">Vasos</small>
                                            </td>

                                            <!-- Kilos de salsa -->
                                            <td v-if="(producto.category == 2)">
                                                <span class="text-success fw-bold">{{ (producto.quantity = (producto.vasos * producto.price) / 1000) }} kg</span>
                                            </td>
                                            <td v-if="(producto.category != 2)">

                                            </td>

                                            <!-- Precio unitario -->
                                            <td class="fw-bold">
                                                ${{ (producto.compra * 1) }}
                                            </td>
                                            <!-- Precio Total -->
                                            <td v-if="(producto.category == 2)" class="fw-bold text-success">
                                                ${{ (formatNumber(producto.vasos * producto.compra)) }}
                                            </td>
                                            <td v-else class="fw-bold text-success">
                                                ${{ (formatNumber(producto.quantity * producto.compra)) }}
                                            </td>
                                            <!-- Remover -->
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm rounded-pill" @click="removeProduct(index)" title="Eliminar producto">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-if="productosPedido.length === 0">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa fa-shopping-cart fa-2x mb-2"></i>
                                            <br>No hay productos añadidos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Resumen de montos en card -->
                            <div class="card shadow-sm">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-muted mb-2 fw-bold">💰 Resumen de costos urgente</h6>
                                    <table class="table table-sm mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th scope="col">Descripción</th>
                                                <th scope="col" class="text-end">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Monto neto</td>
                                                <td class="text-end fw-bold">${{ formatNumber(this.montoNeto) }}</td>
                                            </tr>
                                            <tr v-if="totalDescuento > 0" class="table-warning">
                                                <td class="fw-bold">🎃 Descuento Halloween</td>
                                                <td class="text-end fw-bold text-success">-${{ formatNumber(totalDescuento) }}</td>
                                            </tr>
                                            <tr>
                                                <td v-if="emergency">Despacho urgente</td>
                                                <td v-else>Despacho {{ this.despacho * 100 }}%</td>
                                                <td class="text-end">${{ formatNumber(this.montoDespacho) }}</td>
                                            </tr>
                                            <tr>
                                                <td>IVA {{ this.iva * 100 }}%</td>
                                                <td class="text-end">${{ formatNumber(this.montoIva) }}</td>
                                            </tr>
                                            <tr v-if="this.emergency" class="table-warning">
                                                <td class="fw-bold">🚨 Cargo de emergencia (+{{this.emergencia * 100}}%)</td>
                                                <td class="text-end fw-bold">${{ formatNumber(this.montoEmergencia) }}</td>
                                            </tr>
                                            <tr class="table-danger">
                                                <td class="fw-bold">Total</td>
                                                <td class="text-end fw-bold fs-5">${{ formatNumber(this.totalPrice) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer p-3">
                    <div class="d-flex justify-content-end gap-3 w-100 mt-4">
                        <button type="button" class="btn btn-outline-dark btn-lg" @click="closeModal()" title="Cerrar sin guardar cambios">
                            <i class="fa fa-times-circle me-2"></i>Cerrar
                        </button>
                        <button type="button" @click="addProducts()" class="btn btn-danger btn-lg" title="Confirmar pedido urgente">
                            <i class="fa fa-exclamation-triangle me-2"></i>Agregar productos urgente
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
// Componentes
// import customTable from '@/components/tables/table.vue';
// import modalVerify from '@/components/modals/verifyDelete.vue';

// Helpers y plugins
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';
// import Loader from '@/helpers/Loader';

export default {
    products: {
        type: Array,
        required: true
    },
    data() {
        return {
            totalPrice: 0,
            productosPedido: [
                // 1: { name: 'Vasos', quantity: 160, vasos: 1 },
            ],
            salsasPedido: [],
            opcionalPedido: [],
            productos: {
                // //Producto | quantity | Vasos
                // 1: { name: 'Huevos', quantity: 1, vasos: 1 },
            },

            productoSend: [],
            montoNeto: 0,
            despacho: 0,
            iva: 0.19,
            emergencia:0.1,
            montoDespacho: 0,
            montoIva: 0,
            montoTotal: 0,
            montoEmergencia:0,
            montoOpcionales: 0,
            vasoxsalsa: 1,
            emergency: true,
            
            // 🎃 SISTEMA DE DESCUENTOS
            activeDiscounts: [],
            totalDescuento: 0,
            productosConDescuento: [],
        }
    },
    components: {

    },
    async mounted() {
        console.log('🔥🔥🔥 PEDIDO URGENTE MOUNTED - INICIANDO CARGA DE DESCUENTOS 🔥🔥🔥');
        //Trae los productos fijo de db 
        await this.getProducts();
        // 🎃 Cargar descuentos activos
        await this.loadActiveDiscounts();
        console.log('🎃 Descuentos después de cargar:', this.activeDiscounts);
    },
    methods: {
        async closeModal(refresh = false) {
            //Volvemos los arreglos al estado inicial

            this.montoDespacho = 0;
            this.montoIva = 0;
            this.montoEmergencia = 0;
            this.montoTotal = 0;
            this.montoNeto = 0;
            this.productosPedido = [];
            this.productoSend = [];
            this.totalPrice = 0;
            this.totalDescuento = 0;
            this.productosConDescuento = [];

            $('#pedidoUrgente').modal('hide');
        },
        
        // 🎃 MÉTODOS DE DESCUENTOS
        async loadActiveDiscounts() {
            console.log('🎃 [1/5] Iniciando loadActiveDiscounts en pedido urgente...');
            try {
                // Obtener el ID de la aplicación desde el store
                let applicationId = this.$store.state.main.app && this.$store.state.main.app.id;
                
                // 🔥 FALLBACK: Si no está en el store, intentar obtener desde ConfigHelper
                if (!applicationId) {
                    console.warn('⚠️ App no en store, intentando ConfigHelper.Config()...');
                    const appData = ConfigHelper.Config();
                    if (appData && appData.Id) {
                        applicationId = appData.Id;
                        console.log('✅ ID obtenido exitosamente de ConfigHelper:', applicationId);
                    }
                }
                
                console.log('🎃 [3/5] Application ID obtenido:', applicationId);
                
                if (!applicationId) {
                    console.warn('⚠️ No se pudo obtener el ID de la sucursal');
                    return;
                }
                
                const url = BaseUrl.getUrl(`api/local/discount/branch/${applicationId}`);
                console.log('🎃 [4/5] URL a consultar:', url);
                
                const response = await Connection.request('get', url);
                console.log('🎃 [4.5/5] Response recibido:', response);
                
                if (response && response.success) {
                    // El servidor devuelve los descuentos en response.data.discounts
                    const discountsData = response.data.discounts || response.data.data || response.data;
                    
                    // Asegurarse de que activeDiscounts sea siempre un array
                    this.activeDiscounts = Array.isArray(discountsData) ? discountsData : [];
                    console.log('✅ Descuentos cargados en pedido urgente:', this.activeDiscounts);
                    console.log('✅ Cantidad de descuentos:', this.activeDiscounts.length);
                    
                    if (this.activeDiscounts.length > 0) {
                        this.$awn.success(`🎃 ¡Hay ${this.activeDiscounts.length} descuentos activos para pedidos urgentes!`);
                    }
                } else {
                    console.warn('🎃 Response sin success o sin data:', response);
                    this.activeDiscounts = [];
                }
            } catch (error) {
                console.error('❌ Error cargando descuentos:', error);
                this.activeDiscounts = [];
            }
            console.log('🎃 [5/5] loadActiveDiscounts finalizado. activeDiscounts:', this.activeDiscounts);
        },
        
        checkProductDiscount(producto) {
            // Validar que activeDiscounts sea un array
            if (!Array.isArray(this.activeDiscounts) || this.activeDiscounts.length === 0) {
                return null;
            }
            
            // Buscar si el producto tiene descuento activo
            const descuentoActivo = this.activeDiscounts.find(discount => {
                const productoId = Number(producto.id);
                const discountProductId = Number(discount.product_id);
                return discountProductId === productoId;
            });
            
            if (descuentoActivo) {
                console.log('✅ ¡DESCUENTO ENCONTRADO en urgente!', producto.name, 'ID:', producto.id, '→', descuentoActivo.discount_percentage + '%');
                // Normalizar el formato del descuento para compatibilidad
                return {
                    ...descuentoActivo,
                    discount_type: 'percentage',
                    discount_value: parseFloat(descuentoActivo.discount_percentage)
                };
            }
            
            return null;
        },
        
        aplicarDescuento(producto, descuento) {
            let montoDescuento = 0;
            
            if (descuento.discount_type === 'percentage') {
                montoDescuento = (producto.costo * descuento.discount_value) / 100;
            } else if (descuento.discount_type === 'fixed') {
                montoDescuento = descuento.discount_value;
            }
            
            return montoDescuento;
        },
        
        formatFecha(fecha) {
            if (!fecha) return '';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-CL', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric' 
            });
        },
        
        addProduct(producto) {
            const existingProduct = this.productosPedido.find((p) => p.name === producto.name);
            if (existingProduct) {
                console.log(`Producto ${producto.name} ya existente.`);
            } else {
                const productoCopia = Object.assign({}, producto);
                
                // 🎃 Verificar si tiene descuento
                const descuento = this.checkProductDiscount(productoCopia);
                
                if (descuento) {
                    productoCopia.descuento = descuento;
                    this.productosConDescuento.push({
                        nombre: productoCopia.name,
                        descuento: descuento.discount_value,
                        tipo: descuento.discount_type
                    });
                    
                    this.$awn.success(
                        `🎃 ¡${productoCopia.name} tiene ${descuento.discount_value}% de descuento hasta el ${this.formatFecha(descuento.end_date)}!`,
                        { durations: { success: 5000 } }
                    );
                }
                
                if (productoCopia.category == 2) {
                    productoCopia.vasos = productoCopia.min_quantity;
                }
                if (productoCopia.name == 'Vaso') {
                    productoCopia.quantity = 27;
                }else{
                    productoCopia.quantity = 1;
                }
                this.productosPedido.push(productoCopia);
            }
            this.calcularMontos();
        },
        removeProduct(index) {
            if (index >= 0 && index < this.productosPedido.length) {
                this.productosPedido.splice(index, 1);
            }
            this.calcularMontos();
        },
        formatNumber(number) {
            return FormatNumber.format(number);
        },
        calcularMontos() {
            //monto neto
            this.montoNeto = 0;
            this.totalDescuento = 0;
            
            //Corregimos cantidades no validas de las salsas
            this.validateInput();

            for (var index in this.productosPedido) {
                if (this.productosPedido[index].category == 2) {
                    this.productosPedido[index].costo = this.productosPedido[index].vasos * this.productosPedido[index].compra;
                } else {
                    this.productosPedido[index].costo = this.productosPedido[index].quantity * this.productosPedido[index].compra;
                }
                
                // 🎃 Aplicar descuento si existe
                if (this.productosPedido[index].descuento) {
                    const descuentoMonto = this.aplicarDescuento(
                        { costo: this.productosPedido[index].costo }, 
                        this.productosPedido[index].descuento
                    );
                    this.totalDescuento += descuentoMonto;
                    this.productosPedido[index].costo -= descuentoMonto;
                }

                this.montoNeto += parseFloat(this.productosPedido[index].costo);
            }
            
            if (this.totalDescuento > 0) {
                console.log('💰 TOTAL DESCUENTO HALLOWEEN (URGENTE): $' + this.totalDescuento.toFixed(0));
            }
            
            this.montoIva = Math.ceil(this.iva * this.montoNeto);
            //Si el check emergency is active            
            if(this.emergency){
                //sumamos el 10% y 5000 fijos de despacho
                this.montoEmergencia = Math.ceil(this.emergencia * this.montoNeto);
                this.montoDespacho = 5000;
            }else{
                this.montoDespacho = Math.ceil(this.despacho * this.montoNeto);
                this.montoEmergencia = 0;
            }

            this.totalPrice = Math.ceil(this.montoNeto + this.montoDespacho + this.montoIva + this.montoEmergencia);
        },
        addProducts() {
            console.log('Productos pedido: ', this.productosPedido);

            this.productoSend = this.productosPedido;

            if (this.productoSend.length == 0) {
                this.$awn.alert("Es necesario agregar algun producto");
                return false;
            }


            this.totalPrice = this.montoNeto + this.montoDespacho + this.montoIva + this.montoEmergencia;

            console.log('Total price', this.totalPrice);

            this.$emit('update-products', this.productoSend);
            this.$emit('update-total',(this.totalPrice));
            this.$emit('update-subtotal', (this.montoNeto));
            this.$emit('update-monto-iva', (this.montoIva));
            this.$emit('update-monto-emergencia', (this.montoEmergencia));
            this.$emit('update-monto-despacho',(this.montoDespacho));
            this.$emit('update-montoOpcionales',(this.montoOpcionales));

            this.productoSend = [];
            this.productosPedido = [];
            this.montoOpcionales = 0;
            this.montoDespacho = 0;
            this.montoIva = 0;
            this.montoEmergencia = 0;
            this.totalPrice = 0;
            this.montoNeto = 0;
            $('#pedidoUrgente').modal('hide');
        },
        async getProducts() {
            // Iniciando peticion
            // Loader.dinamic();
            var request = await this.$store.dispatch("products/getProductsOfIndex");

            // Loader.hide();
            // Verificando respuesta
            if (request.success) {
                this.productos = request.data;

                for (const producto in this.productos) {
                    if (this.productos[producto].name === 'Despacho') {
                        if (this.isDespachoGratis) {
                            this.despacho = 0;
                        } else {
                            this.despacho = this.productos[producto].compra;
                        }
                    }
                    //Para agregar la variable costo en cada producto
                    this.productos[producto].costo = 0;
                }
            }
            else this.$awn.alert('Error al obtener los productos');

        },
        convertirAKilogramosYRedondear(gramos, multiplicador) {
            const kilogramos = (gramos * multiplicador) / 1000;
            return Math.ceil(kilogramos * 1000) / 1000;
        },
        validateInput() {
            // Redondea al múltiplo más cercano
            for (var index in this.productosPedido) {
                if(this.productosPedido[index].category == 2){
                    this.productosPedido[index].vasos = Math.round(this.productosPedido[index].vasos / this.productosPedido[index].min_quantity) * this.productosPedido[index].min_quantity; 
                }
                if(this.productosPedido[index].name == 'Vaso'){
                    this.productosPedido[index].quantity = Math.round(this.productosPedido[index].quantity / 27) * 27; 
                }
                
            }
        }
    },
    computed: {
        isDespachoGratis: { get() { return ConfigHelper.ConfStr('modulos.pedidos.ajustes.despacho_gratis'); } }
    },
}
</script>

<style scoped media="screen">
/* Estilos personalizados para el modal de pedido urgente */
.bg-gradient {
    background: linear-gradient(to right, #dc3545, #fd7e14);
    color: white;
}

.table td,
.table th {
    padding: 0.5rem;
    vertical-align: middle;
}

.btn-m {
    margin: 0rem;
    padding: 0.3rem 0.5rem;
    font-size: 0.8rem;
}

.conteoVasos {
    padding: 10px;
    font-size: 14px;
    border-radius: 5px;
    box-shadow: 0px 2px 4px rgb(0 0 0 / 20%), 0px 4px 8px rgb(0 0 0 / 10%);
    background-color: #343a40;
    color: white;
}

.btn-outline-info {
    padding-top: 0.3rem !important;
    padding-bottom: 0.3rem !important;
}

.fieldEdit {
    width: 80px;
}

/* Mejoras para el espaciado y visual */
.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 1rem;
}

.rounded-pill {
    border-radius: 50rem !important;
}

.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
}

.fw-bold {
    font-weight: 700 !important;
}

.fs-5 {
    font-size: 1.25rem !important;
}

/* Estilo para botones de productos */
.product-btn {
    font-size: 0.85rem;
    padding: 0.3rem 0.8rem;
    transition: all 0.2s ease;
}

.product-btn:hover {
    background-color: #e3f2fd !important;
    transform: scale(1.03);
    border-color: #1976d2 !important;
}

/* Modal más centrado */
.modal-dialog {
    max-width: 1000px;
    margin: auto;
}

/* Para compatibilidad con gap en flex */
@supports not (gap: 0.5rem) {
    .d-flex.gap-2 > * + * {
        margin-left: 0.5rem;
    }
    .d-flex.gap-3 > * + * {
        margin-left: 1rem;
    }
}

/* Mejoras adicionales para accesibilidad */
.btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.me-2 {
    margin-right: 0.5rem;
}

.ms-1 {
    margin-left: 0.25rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

/* Estilos específicos para pedido urgente */
.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.text-success {
    color: #198754 !important;
}

/* 🎃 Estilos para sistema de descuentos */
.discount-badge {
    background: #ff6a00;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: bold;
    margin-right: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.btn-warning.product-btn {
    background: linear-gradient(135deg, #ffd93d 0%, #ff9a56 100%);
    border: 2px solid #ff6a00;
    color: #000;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(255, 106, 0, 0.3);
}

.btn-warning.product-btn:hover {
    background: linear-gradient(135deg, #ff9a56 0%, #ff6a00 100%);
    transform: scale(1.08);
    box-shadow: 0 6px 12px rgba(255, 106, 0, 0.5);
}

.table-success {
    background-color: #d1f2dd !important;
}
</style>