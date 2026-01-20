<template>
    <div class="pedidos-container">
        <!-- 🎃 Efecto Halloween: Arañas y telarañas cayendo -->
        <div id="halloween-effect" class="halloween-effect"></div>
        
        <div class="pedidos-main">
            <!-- Header Principal -->
            <div class="pedidos-header fade-in-up">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-md-7 col-12">
                        <div class="pedidos-title-card card">
                            <div class="card-body">
                                <h5>📦 Gestión de Pedidos</h5>
                                <span>Administra y realiza pedidos de productos de manera eficiente</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="PedidoUrgenteInstalled" class="col-lg-4 col-md-5 col-12 mt-3 mt-md-0">
                        <button type="button" data-toggle="modal" data-target="#pedidoUrgente"
                            class="btn-urgente w-100 pulse">
                            Pedido Individual
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formulario Principal -->
            <div v-if="true" class="section-card fade-in-up">
                <div class="section-card-header">
                    <h3 class="m-0">📝 Nuevo Pedido</h3>
                </div>
                <div class="section-card-body">
                    <!-- Información del Negocio -->
                    <div class="business-info">
                        <div class="info-item">
                            <div class="info-label">🏢 Datos del negocio</div>
                            <p class="info-value">{{ this.app && this.app.Name ? this.app.Name : 'Cargando...' }}</p>
                        </div>
                        <div class="info-item">
                            <div class="info-label">📅 Fecha del pedido</div>
                            <p class="info-value">{{ this.date }}</p>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <form id="contactForm" name="contactform" class="modern-form" novalidate="true">
                        <!-- Nombre -->
                        <div class="form-group-modern" :class="{ 'has-error': submitted && !isValidName }">
                            <input v-model="name" :disabled="this.disableForm" name="name" id="contactName" 
                                placeholder="Tu nombre completo*" class="form-control-modern" type="text" required>
                            <i class="input-icon fas fa-user"></i>
                        </div>

                        <!-- Teléfono -->
                        <div class="form-group-modern" :class="{ 'has-error': submitted && !isValidPhone }">
                            <input v-model="phone" name="phone" id="contactPhone" placeholder="Número de teléfono*" 
                                class="form-control-modern" :disabled="this.disableForm" type="text" required>
                            <i class="input-icon fas fa-phone"></i>
                        </div>

                        <!-- Método de Pago -->
                        <div class="form-group-modern" :class="{ 'has-error': submitted && !isValidPaymode }">
                            <select class="form-control-modern select-modern" v-model="paymode" :disabled="this.disableForm">
                                <option value="Metodo de pago" disabled>💳 Selecciona método de pago</option>
                                <option v-for="paymode in paymodes" :value="paymode.name" :key="paymode.id">
                                    {{ paymode.name }}
                                </option>
                            </select>
                            <i class="input-icon fas fa-credit-card"></i>
                        </div>

                        <!-- Comentarios -->
                        <div class="form-group-modern col-span-full" :class="{ 'has-error': submitted && !isValidComment }">
                            <textarea v-model="comment" rows="4" name="comment" id="comment" :disabled="this.disableForm"
                                placeholder="Escribe detalles adicionales del pedido*" 
                                class="form-control-modern textarea-modern" required></textarea>
                            <i class="input-icon fas fa-comment-alt"></i>
                        </div>

                        <!-- Indicador de Productos y Botones -->
                        <div class="col-span-full">
                            <div class="products-indicator">
                                <a href="#" :class="['products-status', this.products.length > 0 ? 'has-products' : 'no-products']">
                                    <i class="cart-icon fas fa-shopping-cart"></i>
                                    <span v-if="this.products.length > 0">
                                        {{ this.products.length }} productos en el pedido
                                    </span>
                                    <span v-else>Sin productos en el pedido</span>
                                    <span class="products-badge">{{ this.products.length }}</span>
                                </a>

                                <div class="action-buttons">
                                    <button type="button" data-toggle="modal" data-target="#modalCatalog" @click="openCatalog"
                                        class="btn-modern btn-secondary-modern">
                                        <i class="fas fa-plus"></i>
                                        Añadir Productos
                                    </button>
                                    <button type="button" class="btn-modern btn-success-modern" @click="newRequest">
                                        <i class="fas fa-check"></i>
                                        Crear Pedido
                                    </button>
                                </div>
                            </div>

                            <!-- 🎯 Resumen de Precios con Descuento -->
                            <div v-if="this.products.length > 0" class="price-summary mt-3 p-3" style="background: #f8f9fa; border-radius: 10px; border-left: 4px solid #28a745;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span style="font-weight: 500;">💰 Subtotal:</span>
                                    <span style="font-size: 1.1em;">${{ formatearMonto(totalPrice) }}</span>
                                </div>
                                <div v-if="totalDiscountAmount > 0" class="d-flex justify-content-between align-items-center mb-2 text-success">
                                    <span style="font-weight: 500;"><i class="fas fa-percentage"></i> Descuento:</span>
                                    <span style="font-size: 1.1em;">-${{ formatearMonto(totalDiscountAmount) }}</span>
                                </div>
                                <hr v-if="totalDiscountAmount > 0" style="margin: 0.5rem 0;">
                                <div class="d-flex justify-content-between align-items-center" style="font-weight: 700; font-size: 1.2em;">
                                    <span>💵 Total Final:</span>
                                    <span :class="{'text-success': totalDiscountAmount > 0}">${{ formatearMonto(finalTotal) }}</span>
                                </div>
                                <small v-if="totalDiscountAmount > 0" class="text-muted d-block mt-2 text-center">
                                    <i class="fas fa-info-circle"></i> Has ahorrado ${{ formatearMonto(totalDiscountAmount) }} en este pedido
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mensaje cuando SII no está activado -->
            <div class="section-card fade-in-up" v-else>
                <div class="section-card-body text-center">
                    <h5 class="text-muted">⚠️ El módulo de SII no se encuentra activado</h5>
                </div>
            </div>
            <!-- Listado de pedidos -->
            <!-- <div v-if="requests && requests.items.length > 0" ref="loaderRequests" class="vld-parent px-2 mt-4"> -->
            <div v-if="requests" ref="loaderRequests" class="section-card table-modern fade-in-up">
                <customTable v-model="jsonTable" v-slot="props">
                    <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Los íconos representan estados:
Azul ($): Pagado.
Gris ($): Impagado.
Verde (Check): Aprobado.
Rojo (Restricción): Rechazado.
Reloj de arena: En espera.">
                    </i>
                    <!-- Estado del pago -->
                    <a v-if="props.item.status_payment == 'impagado'"
                        class="py-1 px-2 text-center rounded-pill bg-secondary" href="#">
                        <i class="fas fa-dollar-sign"></i>
                    </a>
                    <a v-else="props.item.status_payment == 'pagado'" class="py-1 px-2 text-center rounded-pill bg-info"
                        href="#">
                        <i class="fas fa-dollar-sign"></i>
                    </a>
                    <!-- Estado del pedido -->
                    <a v-if="props.item.status == 'aprobado'" class="py-1 px-2 text-center rounded-pill bg-success"
                        href="#">
                        <i class="fa fa-check"></i>
                    </a>
                    <a v-else-if="props.item.status == 'rechazado'" class="py-1 px-2 text-center rounded-pill bg-danger"
                        href="#">
                        <i class="fa fa-ban"></i>
                    </a>
                    <a v-else class="py-1 px-2 text-center rounded-pill" href="#">
                        <i class="fas fa-hourglass-start"></i>
                    </a>
                    <a @click="openReview(props.item)" class="py-1 px-2 text-center btn bg-info" href="#">
                        <i class="fa fa-edit"></i>
                    </a>
                    <a @click="openProductsOrder(props.item)" class="py-1 px-2 text-center btn bg-primario" href="#">
                        <i class="fa fa-eye"></i>
                    </a>
                    <!-- Condicion solo si es admin -->
                    <a v-if="isAdmin" @click="openVerify(props.item)" class="py-1 px-2 text-center btn bg-danger"
                        href="#">
                        <i class="fa fa-trash"></i>
                    </a>
                </customTable>
                <!-- Paginacion -->
                <paginate v-if="(requests && requests.pages > 1)" v-model="requests" :offOn="offOn"
                    @getPage="getRequests" />
            </div>
            <div v-else ref="loaderProduct" class="empty-state fade-in-up">
                <h2>📦 No existen pedidos actualmente</h2>
                <p class="text-muted mt-3">Los pedidos que realices aparecerán aquí</p>
            </div>
        </div>





        <!-- modals -->
        <!-- @refresh="refreshData" -->
        <catalog :products="products" @update-products="updateProducts" @update-total="updateTotal"
            @update-subtotal="updateSubtotal" @update-monto-iva="updateMontoIva"
            @update-monto-despacho="updateMontoDespacho" @update-montoOpcionales="updateMontoOpcionales" />

        <pedido-urgente :products="products" @update-products="updateProducts" @update-total="updateTotal"
            @update-subtotal="updateSubtotal" @update-monto-iva="updateMontoIva"
            @update-monto-emergencia="updateMontoEmergencia" @update-monto-despacho="updateMontoDespacho"
            @update-montoOpcionales="updateMontoOpcionales" />
        <verify-modal :propVerify="propVerify" @refreshData="getRequests" />
        <!-- Modal de Productos (Modernizado) -->
        <div class="modal fade modal-modern" id="productsOrder" tabindex="-1" role="dialog"
            aria-labelledby="productsOrder" aria-hidden="true" data-backdrop="false">
            <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 850px; width: 90%;" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">📦 Pedido #{{ this.idRequest }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="order-products">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="../assets/shop.png" alt="carrito" class="img-shop me-3" style="max-width: 60px;">
                                        <div>
                                            <h4 class="mb-1">🛍️ Productos del Pedido</h4>
                                            <p class="text-muted mb-0">Detalles de los productos solicitados</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="">
                                    <!-- <customTable v-model="jsonTableProducts" @changeValue="changeValue" v-slot="props">

                                    </customTable> -->

                                    <div class="products-table">
                                        <table class="table table-borderless">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Producto</th>
                                                    <th scope="col">Cantidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(producto, id) in jsonTableProducts.items" :key="id">
                                                    <td>{{ producto.name }}</td>
                                                    <td>{{ 
                                                        // Casos específicos con sus unidades
                                                        producto.name == 'Botella de Huevos 1L' ? producto.quantity + ' botellas' :
                                                        producto.name == 'Aceite Vegetal 5L' ? producto.quantity + ' unidades' :
                                                        producto.name == 'Pliego (124 stickers)' ? producto.quantity + ' Pliego' :
                                                        // Productos que van en kg
                                                        (producto.name != 'Vaso'
                                                        && producto.name != 'Sandwich'
                                                        && producto.name != 'Aceite de oliva 5kg'
                                                        && producto.name != 'Harina'
                                                        && producto.name != 'Bolsa'
                                                        && producto.name != 'Focaccia Salame'
                                                        && producto.name != 'Focaccia Pesto'
                                                        && producto.name != 'Pliego (124 stickers)'
                                                        && producto.name != 'Focaccia alleato'
                                                        && producto.name != 'Focaccia Pollo Pimenton'
                                                        && producto.name != 'papel mantequilla (Focaccia)'
                                                        && producto.name != 'Papel Mantequilla (Bandeja)') ?
                                                        producto.quantity + 'kg' : producto.quantity + ' unidades' }}</td>
                                                    <!-- <td>{{ }}</td> -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="order-totals">
                                        <table class="table table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th scope="col">Descripcion</th>
                                                    <th scope="col">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Monto neto</td>
                                                    <td>${{ formatearMonto(this.requestSubtotal) }}</td>
                                                </tr>
                                                <tr>
                                                    <td v-if="this.requestEmergencia > 0"> Despacho</td>
                                                    <td v-else="this.requestEmergencia > 0"> Despacho {{
                                                        this.porcentajeDespacho * 100 }}%</td>

                                                    <td>${{ formatearMonto(this.requestDespacho) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>IVA 19%</td>
                                                    <td>${{ formatearMonto(this.requestIva) }}
                                                    </td>
                                                </tr>
                                                <tr v-if="this.requestEmergencia > 0">
                                                    <td>Cargo de emergencia {{ this.porcentajeEmergencia * 100 }}%</td>
                                                    <td>${{ formatearMonto(this.requestEmergencia) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Total</td>
                                                    <td>${{ formatearMonto(this.requestPrice) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a v-if="this.status_payment != 'pagado'" href="#" class="btn-modern btn-primary-modern"
                            @click="openURL()">
                            💳 Pagar <i class="fas fa-dollar-sign"></i>
                        </a>

                        <button @click="closeProductsOrder()" type="button" class="btn-modern btn-secondary-modern">
                            ✖️ Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Reseña (Modernizado) -->
        <div class="modal fade modal-modern" id="modalReview" tabindex="-1" role="dialog" aria-labelledby="modalReview"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">✍️ Reseñar Pedido</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group-modern">
                            <label for="review" class="info-label">📝 Escribe tu reseña:</label>
                            <textarea v-model="review" @keyup.enter="sendReview" id="review" 
                                class="form-control-modern textarea-modern" 
                                placeholder="Comentario sobre el estado del pedido..."></textarea>
                            <i class="input-icon fas fa-comment-alt"></i>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modern btn-secondary-modern" data-dismiss="modal">
                            ✖️ Cerrar
                        </button>
                        <button type="button" class="btn-modern btn-primary-modern" @click="sendReview">
                            💬 Reseñar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// Components
import paginate from '@/components/MPage.vue';
import verifyModal from '@/components/modals/verifyDelete.vue';
import customTable from '@/components/tables/table.vue';
import catalog from '@/components/modals/pedidos/catalog.vue';
import pedidoUrgente from '@/components/modals/pedidos/pedidoUrgente.vue';
// Helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
// import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
import FormatNumber from '@/helpers/FormatNumber.js';
// import Print from '@/helpers/Print.js';

const { shell } = require('electron');

export default {
    name: 'pedidos',
    // props: {
    //     products: {
    //         type: Array,
    //         required: true
    //     }
    // },
    components: {
        verifyModal,
        paginate,
        customTable,
        catalog,
        pedidoUrgente
    },
    data() {
        return {
            products: [],
            //formdata
            name: '',
            phone: '+56',
            comment: '',
            paymode: 'Transferencia',
            review: '',
            // transaccion: '',
            voucherFile: null,
            url_linkify: 'https://app.linkify.cl/pay/0GEVx3vk2gmqe9r/remote/',
            url_payment: '',
            totalPrice: 0,
            date: moment().format('YYYY-MM-DD HH:mm:ss'),

            disableForm: false,
            submitted: false,

            oldPage: '&page=1',
            requests: null,
            app: null,
            status_payment: '',
            paymodes: [
                { id: 1, name: 'Transferencia' }
            ],

            jsonTable: {
                btn: true,
                items: null,
                rows: [
                    { key: 'contact_name', class: '', permission: 'default' },
                    { key: 'contact_phone', class: '', permission: 'default' },
                    { key: 'paymode', class: '', permission: 'default' },
                    // { key: 'transaccion', class: '', permission: 'default' },
                    // { key: 'status', class: '', permission: 'default' },
                    { key: 'comment', class: '', permission: 'default' },
                ],
                titles: [
                    { label: 'Nombre', class: '', permission: 'default', type: false },
                    { label: 'Telefono', class: '', permission: 'default', type: false },
                    { label: 'Metodo de pago', class: 'default', permission: 'default', type: false },
                    // { label: 'Nro Transaccion', class: 'default', permission: 'default', type: false },
                    // { label: 'Estado', class: 'default', permission: 'default', type: false },
                    { label: 'Comentario', class: 'default', permission: 'default', type: false },
                    { label: 'Detalles', class: 'th-sm text-center', permission: 'default', type: false },
                ]
            },
            idRequest: 0,
            jsonTableProducts: {
                btn: false,
                items: null,
                rows: [
                    { key: 'name', class: '', permission: 'default' },
                    { key: 'quantity', class: '', permission: 'default' },

                ],
                titles: [
                    { label: 'Nombre', class: '', permission: 'default', type: false },
                    { label: 'Cantidad', class: '', permission: 'default', type: false },

                ]
            },
            subtotal: 0,
            total: 0,
            montoIva: 0,
            montoEmergencia: 0,
            montoDespacho: 0,
            propVerify: null,
            productosFijos: {},
            porcentajeDespacho: 0,
            porcentajeEmergencia: 0.1,
            iva: 0.19,
            precioVaso: 0,
            montoOpcionales: 0,
            vasos: 0,

            request: null,
            requestPrice: 0,
            requestSubtotal: 0,
            requestIva: 0,
            requestDespacho: 0,
            requestEmergencia: 0,
        }
    },
    async beforeCreate() {
        var request = await this.$store.dispatch('main/refreshData', '?slim');
        this.app = request.data;
    },
    async mounted() {

        this.getRequests();
        this.getDespacho();
        // this.user = this.me;
        console.log("user:", this.isAdmin);
        // Para los toltips
        $('[data-toggle="tooltip"]').tooltip();

        this.name = this.me.fullname;
    },
    computed: {
        me: { get() { return this.$store.getters['main/user']; } },
        offOn: {
            get() { return this.value },
            set(offOn) { this.$emit('input', offOn) }
        },
        isValidName: {
            get() { return this.name.length > 0 }
        },
        isValidPhone: {
            get() { 
                // Validar formato chileno: +56XXXXXXXXX (13 caracteres: +56 + 9 dígitos)
                const regex = /^\+56\d{9}$/;
                return regex.test(this.phone);
            }
        },
        isValidComment: {
            get() { return this.comment.length > 0 }
        },
        isValidPaymode: {
            get() { return this.paymode.length > 0 }
        },
        isValidProducts: {
            get() { return this.products.length > 0 }
        },

        isAdmin: { get() { return (this.$store.getters['main/user'].role == 1) } },

        PedidoUrgenteInstalled: { get() { return ConfigHelper.ConfStr('modulos.pedidos.ajustes.pedidos_urgentes'); } },

        // 🎯 Calcular descuento total basado en productos con discount_percentage
        totalDiscountAmount: {
            get() {
                if (!this.products || !Array.isArray(this.products)) return 0;
                
                let discountTotal = 0;
                this.products.forEach(product => {
                    if (product.discount_percentage && product.discount_percentage > 0) {
                        const productTotal = parseFloat(product.price || 0) * parseFloat(product.quantity || 1);
                        const discount = (productTotal * parseFloat(product.discount_percentage)) / 100;
                        discountTotal += discount;
                    }
                });
                
                return discountTotal;
            }
        },

        // Total final con descuento aplicado
        finalTotal: {
            get() {
                const total = parseFloat(this.totalPrice || 0);
                const discount = parseFloat(this.totalDiscountAmount || 0);
                return total - discount;
            }
        },
    },
    watch: {

    },
    methods: {
        async getApp() {
            var request = await this.$store.dispatch('main/refreshData', '?slim');
            return request.data;
        },
        async getRequests(page = false) {
            // Parametros para la ruta
            var params = '?params=true';
            if (page && this.oldPage != page) this.oldPage = page;
            params += '&page=' + this.oldPage;

            // Iniciando peticion
            this.offOn = true;
            Loader.containe(this.$refs.loaderRequests);
            var request = await this.$store.dispatch("requests/getRequests", params);
            console.log('Pedidos: ', request);
            Loader.hide();
            this.offOn = false;
            // Verificando respuesta
            if (!request.success) return this.$awn.alert(request.data);
            // if (!request.success) console.log('Error: ', request.data);

            this.requests = (request.data.items.length == 0) ? false : request.data;
            this.jsonTable.items = this.requests.items;
        },
        openProductsOrder(request) {
            this.jsonTableProducts.items = JSON.parse(request.products);
            // this.getTotal(this.jsonTableProducts.items);
            this.idRequest = request.id;
            this.requestPrice = request.price;
            this.requestSubtotal = request.subtotal;
            this.requestIva = request.iva;
            this.requestDespacho = request.despacho;

            if (request.emergency) {
                this.requestEmergencia = request.emergency;
            }

            this.payment = request.payment;
            this.status_payment = request.status_payment;
            this.url_payment = this.url_linkify + this.app.Id + 'i' + this.payment.id

            for (const producto in this.jsonTableProducts.items) {
                if (this.jsonTableProducts.items[producto].name === 'Vaso') {
                    //Obteniendo el precio del vaso
                    this.vasos = this.jsonTableProducts.items[producto].quantity;
                }
            }

            $('#productsOrder').modal('show');

        },
        closeProductsOrder() {

            $('#productsOrder').modal('hide');
        },
        async uploadVoucher(event) {
            const file = event.target.files[0];
            this.voucherFile = file;
            const data = {
                voucher: this.voucherFile,
                // app_id: '1',
            };

            //Se construye formdata
            var formData = new FormData();
            for (let key in data) if (data[key]) formData.append(key, data[key]);

            this.waitResponse = true;
            Loader.fullPage();
            let request = await this.$store.dispatch('requests/voucher', { id: this.idRequest, formData });
            Loader.hide();

            if (request.success) {
                this.$awn.success('Pedido actualizado Exitosamente', { labels: { success: 'CORRECTO' } });
            } else {
                console.log(request.data);
                this.$awn.alert('Error al enviar el pedido');
            }
            this.waitResponse = false;
            console.log(data);
        },
        async openCatalog() {
            Loader.fullPage();
            await this.$store.dispatch("products/getCategories");
            Loader.hide();
            $('#modalCatalog').modal('show');
        },
        updateProducts(newProducts) {
            this.products = newProducts;
            console.log('Productos pedidos:', this.products);
        },
        updateTotal(newTotal) {
            this.totalPrice = newTotal;
            console.log('Total Price:', this.totalPrice);
        },
        updateSubtotal(newSubtotal) {
            this.subtotal = newSubtotal;
            console.log('Sub total Price:', this.subtotal);
        },
        updateMontoIva(newMontoIva) {
            this.montoIva = newMontoIva;
        },
        updateMontoEmergencia(newMontoEmergencia) {
            this.montoEmergencia = newMontoEmergencia;
        },
        updateMontoDespacho(newMontoDespacho) {
            this.montoDespacho = newMontoDespacho;
        },
        updateMontoOpcionales(newMonto) {
            this.montoOpcionales = newMonto;
            console.log('montoOpcionales Price:', this.montoOpcionales);
        },
        async newRequest() {
            this.submitted = true;
            if (!this.validar_form()) {
                this.$awn.alert('Hay errores en el formulario');
                return;
            }
            if (this.products.length > 0) {
                //Tranformar a Json
                try {
                    JSON.parse(this.products);
                    // Si llega aquí, significa que ya es un JSON válido
                    console.log('El texto ya es un JSON');
                } catch (e) {
                    // Si hay un error en el parseo, entonces no es un JSON válido
                    this.products = JSON.stringify(this.products);
                    console.log('Texto convertido a JSON');
                }
                const data = {
                    contact_name: this.name,
                    contact_phone: this.phone,
                    paymode: this.paymode,
                    voucher: this.voucherFile,
                    status_payment: 'impagado',
                    status: 'nuevo',
                    products: this.products,
                    comment: this.comment,
                    price: this.finalTotal,  // 🎯 Usar finalTotal con descuento aplicado
                    subtotal: this.subtotal,
                    iva: this.montoIva,
                    emergency: this.montoEmergencia,
                    despacho: this.montoDespacho,
                    discount_amount: this.totalDiscountAmount,  // 🎯 Guardar monto de descuento
                    // transaccion: this.transaccion,
                    app_id: this.app.Id,
                };
                //Se construye formdata
                var formData = new FormData();
                for (let key in data) if (data[key]) formData.append(key, data[key]);

                this.waitResponse = true;
                Loader.fullPage();
                let request = await this.$store.dispatch('requests/newRequest', formData);
                Loader.hide();

                if (request.success) {
                    this.$awn.success('Pedido enviado Exitosamente', { labels: { success: 'CORRECTO' } });

                    // 🎃 Si hay descuento de Halloween, activar efecto de arañas
                    if (this.totalDiscountAmount > 0) {
                        this.activarEfectoHalloween();
                    }

                    this.name = this.me.fullname;
                    this.phone = "+56";
                    this.paymode = 'Transferencia';
                    // this.status = null;
                    this.products = [];
                    this.comment = "";
                    this.totalPrice = 0;
                    this.subtotal = 0;
                    this.montoIva = 0;
                    this.montoDespacho = 0;
                    this.montoEmergencia = 0;
                } else {
                    console.log(request.data);
                    this.$awn.alert(request.data.message);
                }
                this.waitResponse = false;
                console.log(data);

                //refrescar data
                this.getRequests(false);
                this.submitted = false;
            } else {
                this.$awn.alert('Error debes agregar productos al pedido');
            }

        },
        validar_form() {
            if (!this.isValidProducts) {
                this.$awn.alert("Es necesario agregar algun producto");
                return false;
            }
            if (!this.isValidName) {
                this.$awn.alert("Ingresa tu nombre completo");
                return false;
            }
            if (!this.isValidPhone) {
                this.$awn.alert("Ingresa bien el telefono (formato: +56912345678)");
                return false;
            }
            if (!this.isValidComment) {
                this.$awn.alert("Ingresa un comentario");
                return false;
            }
            if (!this.isValidPaymode) {
                this.$awn.alert("Selecciona un método de pago");
                return false;
            }
            return true;
        },
        changeValue(value) {
            // this.total = 0;
            // value.map((product) => {
            //     product.subtotal = parseFloat(product.price) * parseInt(product.quantity);
            //     this.total += parseFloat(product.subtotal);
            // });
            // this.board.order.products = value;
            console.log('Change value');

        },
        formatNumber(number) {
            return FormatNumber.format(number);
        },
        openURL() {
            const url = this.url_payment;
            shell.openExternal(url);
        },
        formatearMonto(monto) {
            const montoSinDecimales = Math.floor(monto);
            const parteDecimal = monto.toFixed(2).split(".")[1];

            // Eliminar "00" si son los dos últimos decimales
            if (parteDecimal === "00") {
                return montoSinDecimales.toLocaleString();
            }

            // Formatear con miles y decimales
            return `${montoSinDecimales.toLocaleString()}.${parteDecimal}`;
        },
        openVerify(pedido) {
            this.propVerify = {
                params: pedido.id,
                title: 'Eliminar Pedido',
                text: '¿Usted esta seguro de eliminar el pedido #' + pedido.id + '?',
                store: 'requests/removeRequest',
                success: 'Pedido eliminado exitosamente'
            };
            $('#verifyDelete').modal('show');
        },
        openReview(request) {
            this.idRequest = request.id;
            $('#modalReview').modal('show');
        },
        async sendReview() {
            console.log(this.idRequest, this.review);
            if (this.review.length > 0) {
                let data = new FormData();
                data.append('review', this.review);
                if (this.review != null) var request = await this.$store.dispatch("requests/update", { id: this.idRequest, data });

                if (request.success) {
                    this.$awn.success("Reseña creada correctamente");
                }
                $('#modalReview').modal('hide');
            } else {
                this.$awn.alert("Es necesario escribir un comentario");
            }

        },
        async getDespacho() {
            // Iniciando peticion
            // Loader.dinamic();
            var request2 = await this.$store.dispatch("products/getProductsOfIndex");
            // Loader.hide();
            // Verificando respuesta
            if (request2.success) {
                this.productosFijos = request2.data;
            }
            else this.$awn.alert('Error al obtener los productos');

            for (const producto in this.productosFijos) {
                if (this.productosFijos[producto].name === 'Despacho') {
                    //Obteniendo el precio de despacho
                    this.porcentajeDespacho = this.productosFijos[producto].price;
                }
                if (this.productosFijos[producto].name === 'Vaso') {
                    //Obteniendo el precio de despacho
                    this.precioVaso = this.productosFijos[producto].price;
                }
            }
            console.log(this.precioVaso);
            console.log(this.porcentajeDespacho);
        },
        formatearMonto(monto) {
            const montoSinDecimales = Math.ceil(monto);
            // const parteDecimal = monto.toFixed(2).split(".")[1];

            // // Eliminar "00" si son los dos últimos decimales
            // if (parteDecimal === "00") {
            //   return montoSinDecimales.toLocaleString();
            // }

            // Formatear con miles y decimales
            return `${montoSinDecimales.toLocaleString()}`;
        },
    }
}
</script>
<style scoped>
@import '../assets/css/pedidos.css';

/* Estilos adicionales específicos para este componente */
.img-shop {
    max-width: 60px;
    height: auto;
}

.col-span-full {
    grid-column: 1 / -1;
}

/* Compatibilidad con clases de Bootstrap existentes */
.bg-primario {
    background: var(--primary-color) !important;
}

.bg-secundario {
    background: var(--info-color) !important;
}

.bg-success {
    background: var(--success-color) !important;
}

.bg-danger {
    background: var(--danger-color) !important;
}

.bg-info {
    background: var(--info-color) !important;
}
</style>