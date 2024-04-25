<template>
    <div class="product-bg p-3">
        <div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center">
            <div class="pl-2 d-flex row w-100">
                <div class="card title-card col-md-5 col-sm-7 col-12">
                    <div class="card-body">
                        <h5 class="font-weight-bold m-0">Gestion de Pedidos</h5>
                        <span>Realizar pedidos de productos</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5 pb-3 px-3 px-sm-5">
            <!-- v-if -->
            <div class="row" v-if="true">
                <div class="tab-content">
                    <div class="col-sm-12">
                        <div class="item-wrap">
                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="item-content colBottomMargin">
                                        <div class="item-info">
                                            <h2 class="item-title">Nuevo pedido</h2>

                                        </div><!--End item-info -->
                                        <div class="row d-flex justify-content-start">
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <label for="app">Datos del negocio</label>
                                                <p>{{ this.app.Name }}</p>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-12">
                                                <label for="date">Fecha del pedido</label>
                                                <p>{{ this.date }}</p>
                                            </div>
                                        </div>
                                    </div><!--End item-content -->
                                </div><!--End col -->
                                <div class="col-md-12">


                                    <form id="contactForm" name="contactform" data-toggle="validator" class="popup-form"
                                        novalidate="true">
                                        <div class="row">
                                            <div id="msgContactSubmit" class="hidden"></div>

                                            <div class="form-group col-sm-6"
                                                :class="{ 'has-error': submitted && !isValidName }">
                                                <div class="help-block with-errors"></div>
                                                <input v-model="name" :disabled="this.disableForm" name="name"
                                                    id="contactName" placeholder="Tu nombre*" class="form-control"
                                                    type="text" required="" data-error="Por favor ingresa tu nombre">
                                                <div class="input-group-icon"><i class="fa fa-user"></i></div>

                                            </div><!-- end form-group -->
                                            <div class="form-group col-sm-6"
                                                :class="{ 'has-error': submitted && !isValidPhone }">
                                                <div class="help-block with-errors"></div>
                                                <input v-model="phone" name="phone" id="contactPhone"
                                                    placeholder="Teléfono*" class="form-control"
                                                    :disabled="this.disableForm" type="text" required=""
                                                    data-error="Por favor ingresa tu número de teléfono">
                                                <div class="input-group-icon"><i class="fa fa-phone"></i></div>
                                            </div><!-- end form-group -->

                                            <!-- <div class="form-group col-sm-6">
                                                <div class="help-block with-errors"></div>
                                                <input name="email" id="email" placeholder="Tu E-mail*"
                                                    pattern=".*@\w{2,}\.\w{2,}" class="form-control" type="email"
                                                    required="" data-error="Por favor ingresa un correo electrónico válido">
                                                <div class="input-group-icon"><i class="fa fa-envelope"></i></div>
                                            </div>end form-group -->

                                            <div class="form-group col-sm-12"
                                                :class="{ 'has-error': submitted && !isValidPaymode }">
                                                <div class="help-block with-errors"></div>
                                                <select class="form-control" v-model="paymode"
                                                    :disabled="this.disableForm" placeholder="Metodo de pago">
                                                    <option disabled selected class="text-capitalize">Todas</option>
                                                    <option :value="paymode.name" v-for="paymode in paymodes"
                                                        :key="paymode.id" class="text-capitalize">
                                                        {{ paymode.name }}
                                                    </option>
                                                </select>
                                                <div class="input-group-icon"><i class="fa fa-book"></i></div>
                                            </div><!-- end form-group -->
                                            <!-- <div class="form-group col-sm-12 has-error has-danger"></div> -->
                                            <div class="form-group col-sm-12"
                                                :class="{ 'has-error': submitted && !isValidComment }">
                                                <!-- <div class="help-block">
                                                    <ul class="list-unstyled">
                                                        <li>Por favor ingresa un mensaje</li>
                                                    </ul>
                                                </div> -->
                                                <textarea v-model="comment" rows="3" name="comment" id="comment"
                                                    :disabled="this.disableForm"
                                                    placeholder="Escribe tu comentario aquí*" class="form-control"
                                                    required=""></textarea>
                                                <!-- <textarea rows="3" name="message" id="message"
                                                    placeholder="Escribe tu comentario aquí*" class="form-control"
                                                    required="" data-error="Por favor ingresa un mensaje"></textarea> -->
                                                <div class="textarea input-group-icon"><i class="fas fa-pencil-alt"></i>
                                                </div>
                                            </div><!-- end form-group -->

                                            <div class="form-group last col-sm-12">
                                                <!-- <button type="submit" id="submit" class="btn btn-custom disabled"><i
                                                        class="fa fa-envelope"></i> Enviar</button> -->
                                                <button type="button" data-toggle="modal" data-target="#modalCatalog"
                                                    @click="openCatalog"
                                                    class="m-1 btn-width btn bg-secundario  text-white text-capitalize">
                                                    Añadir Productos
                                                </button>
                                                <button type="button"
                                                    class="m-1 btn-width btn bg-primario text-white text-capitalize"
                                                    @click="newRequest">
                                                    <i class="fas fa-check"></i> Crear Pedido
                                                </button>
                                            </div><!-- end form-group -->

                                            <!-- <span class="sub-text">* Campos requeridos</span> -->
                                            <div class="clearfix"></div>
                                        </div><!-- end row -->
                                    </form><!-- end form -->
                                </div>
                            </div><!--End row -->

                            <!-- Popup end -->

                        </div><!-- end item-wrap -->
                    </div><!--End col -->
                </div><!--End tab-content -->
            </div>

            <!-- v-else -->
            <div class="m-0 my-2 text-center w-100" v-else>
                <h5>El modulo de SII no se encuentra activado</h5>
            </div>
            <!-- Listado de pedidos -->
            <!-- <div v-if="requests && requests.items.length > 0" ref="loaderRequests" class="vld-parent px-2 mt-4"> -->
            <div v-if="requests" ref="loaderRequests" class="vld-parent px-2 mt-4">
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
                <!-- <paginate v-if="(requests && requests.pages > 1)" v-model="requests" :offOn="offOn" @getPage="getRequests" /> -->
            </div>
            <div ref="loaderProduct" v-else class="vld-parent px-2 mt-2">
                <div class="box-false d-flex flex-center text-center p-2 w-100">
                    <h2>No existen pedidos actualmente</h2>
                </div>
            </div>
        </div>





        <!-- modals -->
        <!-- @refresh="refreshData" -->
        <catalog :products="products" @update-products="updateProducts" @update-total="updateTotal" />
        <verify-modal :propVerify="propVerify" @refreshData="getRequests" />
        <!-- modal productsOrders -->
        <div class="modal fade modalForce" id="productsOrder" tabindex="-1" role="dialog"
            aria-labelledby="productsOrder" aria-hidden="true" data-backdrop="false">
            <div class="modal-dialog lg-modal modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primario">
                        <h5 class="modal-title text-capitalize">Pedido # - {{ this.idRequest }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap boxCompleteOrder">
                            <div class="p-3 col-md-6 col-12 d-flex flex-column justify-content-between">
                                <div class="">
                                    <img src="../assets/shop.png" alt="carrito" class="img-shop">
                                    <h3>Productos</h3>
                                    <p class="text">Este sistema está especialmente diseñado para brindar al
                                        franquiciado la
                                        emocionante oportunidad de solicitar una amplia gama de productos de fagotto de
                                        manera virtual, todo gracias al vibrante carrito de compras en línea. Una vez
                                        solicitado, el pedido estará sujeto a aprobación, agregando un toque de
                                        expectativa
                                        y dinamismo al proceso.</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-12 d-flex flex-column justify-content-between">
                                <div class="">
                                    <!-- <customTable v-model="jsonTableProducts" @changeValue="changeValue" v-slot="props">

                                    </customTable> -->

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
                                                <td>{{ (producto.name != 'Vaso' && producto.name != 'Huevo') ?
                producto.quantity + 'kg' : producto.quantity }}</td>
                                                <!-- <td>{{ }}</td> -->
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="footerTableTicket d-flex justify-content-between">
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
                                                    <td>${{ formatearMonto((this.vasos * this.precioVaso)) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Despacho {{ this.despacho * 100 }}%</td>
                                                    <td>${{ formatearMonto((this.vasos * this.precioVaso) *
                this.despacho) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>IVA 19%</td>
                                                    <td>${{ formatearMonto((this.vasos * this.precioVaso) * this.iva) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Total</td>
                                                    <td>${{ formatearMonto(
                this.vasos * this.precioVaso
                + ((this.vasos * this.precioVaso) * this.iva)
                + ((this.vasos * this.precioVaso) * this.despacho)
            ) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a v-if="this.status_payment != 'pagado'" href="#" class="py-1 px-2 text-center btn bg-primario"
                            @click="openURL()">
                            Pagar <i class="fas fa-dollar-sign"></i>
                        </a>

                        <button @click="closeProductsOrder()" type="button" class="ml-5 btn bg-primario text-white">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
            <!-- <modal-client @sendInfo="sendInfo" /> -->
            <!-- <modalVerify :propVerify="propVerify" @refreshData="refreshData" /> -->
            <!-- <password-verify v-model="jsonPassword" @success="removeProduct" /> -->
            <!-- <assignBoard /> -->
        </div>

        <!-- modal comentario -->
        <div class="modal fade" id="modalReview" tabindex="-1" role="dialog" aria-labelledby="modalReview"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primario">
                        <h5 class="modal-title">Reseñar Pedido</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex row flex-wrap">
                            <div class="form-group col-12">
                                <label for="review">Reseña: </label>
                                <textarea v-model="review" @keyup.enter="sendReview" id="review" type="text"
                                    class="form-control" placeholder="Comentario sobre el estado del pedido"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-secundario text-white" data-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="button" class="btn bg-primario text-white" @click="sendReview">
                            Reseñar
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
// Helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
import FormatNumber from '@/helpers/FormatNumber.js';
import Print from '@/helpers/Print.js';

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
        catalog
    },
    data() {
        return {
            products: [],
            //formdata
            name: '',
            phone: '',
            comment: '',
            paymode: 'Metodo de pago',
            review: '',
            // transaccion: '',
            voucherFile: null,
            url_linkify: 'https://app.linkify.cl/pay/QXyLMKgplXOzBJl/remote/',
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
                { id: 1, name: 'Transferencia' },
                { id: 2, name: 'Credito' },
                { id: 3, name: 'Efectivo' },
                // Agrega más objetos de paymodes según sea necesario
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
                    // { key: 'price', class: '', permission: 'default' },
                    { key: 'quantity', class: '', permission: 'default' },
                    // { key: 'subtotal', class: '', permission: 'default' },
                ],
                titles: [
                    { label: 'Nombre', class: '', permission: 'default', type: false },
                    // { label: 'Precio', class: '', permission: 'default', type: false },
                    { label: 'Cantidad', class: '', permission: 'default', type: false },
                    // { label: 'Subtotal', class: '', permission: 'default', type: false },
                    // { label: '', class: '', permission: 'default', type: false },
                ]
            },
            subtotal: this.total,
            total: 0,
            propVerify: null,
            productosFijos: {},
            despacho: 0,
            iva:0.19,
            precioVaso:0,
            vasos:0
        }
    },
    async beforeCreate() {
        var request = await this.$store.dispatch('main/refreshData', '?slim');
        this.app = request.data;
    },
    async mounted() {
        
        this.getRequests(false);
        this.getDespacho();
        // this.user = this.me;
        console.log("user:", this.isAdmin);
        // Para los toltips
        $('[data-toggle="tooltip"]').tooltip()
    },
    computed: {
        offOn: {
            get() { return this.value },
            set(offOn) { this.$emit('input', offOn) }
        },
        isValidName: {
            get() { return this.name.length > 0 }
        },
        isValidPhone: {
            get() { return this.phone.length > 9 }
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
    },
    watch: {

    },
    methods: {
        async getApp() {
            var request = await this.$store.dispatch('main/refreshData', '?slim');
            return request.data;
        },
        async getRequests(page = false) {

            var params = '?params=true';
            if (page !== false) this.oldPage = '&page=' + page;
            params += this.oldPage;

            // Iniciando peticion
            this.offOn = true;
            Loader.containe(this.$refs.loaderRequests);
            var request = await this.$store.dispatch("requests/getRequests", params);
            console.log('Pedidos: ', request);
            Loader.hide();
            this.offOn = false;
            // Verificando respuesta
            // if (!request.success) return this.$awn.alert(request.data);
            if (!request.success) console.log('Error: ', request.data);

            this.requests = request.data;
            // this.jsonTable.items = request.data.items;
            this.jsonTable.items = request.data;
        },
        getTotal(products) {
            // this.total = 0;
            // products.map((product) => {
            //     this.total += parseFloat(product.subtotal);
            // });
            // this.subtotal = this.total;
        },
        openProductsOrder(request) {
            this.jsonTableProducts.items = JSON.parse(request.products);
            // this.getTotal(this.jsonTableProducts.items);
            this.idRequest = request.id;
            this.total = request.price;
            this.payment = request.payment;
            this.status_payment = request.status_payment;
            this.url_payment = this.url_linkify + this.app.Id + 'i' + this.payment.id

            for (const producto in this.jsonTableProducts.items) {
                if (this.jsonTableProducts.items[producto].name === 'Vaso') {
                    //Obteniendo el precio de despacho
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
        async newRequest() {
            this.submitted = true;
            if (!this.validar_form()) {
                this.$awn.alert('Hay errores en el formulario');
                return;
            }
            if (this.products.length > 0) {
                //Tranformar a Json
                this.products = JSON.stringify(this.products);
                const data = {
                    contact_name: this.name,
                    contact_phone: this.phone,
                    paymode: this.paymode,
                    voucher: this.voucherFile,
                    status: 'impagado',
                    products: this.products,
                    comment: this.comment,
                    price: this.totalPrice,
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
                } else {
                    console.log(request.data);
                    this.$awn.alert('Error al enviar el pedido');
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
            }
            if (!this.isValidName || !this.isValidPhone || !this.isValidComment || !this.isValidPaymode) {
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
                success: 'Gasto eliminado exitosamente'
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
                    this.despacho = this.productosFijos[producto].price;
                }
                if (this.productosFijos[producto].name === 'Vaso') {
                    //Obteniendo el precio de despacho
                    this.precioVaso = this.productosFijos[producto].price;
                }
            }
            console.log(this.precioVaso);
            console.log(this.despacho);
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
.invalid-input {
    border-color: red;
}

.boxCompleteOrder {
    min-height: 450px;
    justify-content: center;
}

.img-shop {
    max-width: 250px;
}

.text {
    font-size: 14px;
    color: rgb(116, 116, 116);
}

.form-control {
    background-color: #ffffff;
    border-radius: 5px;
    box-shadow: inset -1px 1px 20px 11px rgb(193 193 193 / 28%);
}


@import url('https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i');

.form-content-wrap {
    font-family: 'Montserrat', sans-serif;
    font-size: 16px;
    line-height: 1.48;
}

.form-content-wrap:first-child {
    padding-top: 60px;
}

.form-content-wrap img {
    width: 100%;
}

/* .item-wrap {
    border: 2px solid #ccc;
    border-radius: 5px;
    margin: 20px auto;
    padding: 30px 15px 0;
    width: 100%;
} */

.item-image img {
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
}

.item-title {
    /* color: #0097e6; */
    font-size: 32px;
    font-weight: bold;
    margin: 0 auto 30px;
}

.offer-main-buttons {
    border-radius: 5px;
    font-weight: bold;
    max-width: 210px;
}

.offer-main-button-btn {
    background: #0097e6;
    border-radius: 5px;
    padding: 10px 0;
    margin: 30px 0 0;
    cursor: pointer;
    transition: background 0.4s ease 0s;
}

.offer-main-button-btn-txt {
    font-size: 20px;
    color: #fff;
    display: block;
    text-align: center;
}

.offer-main-button-bet {
    background: #397ab9;
    border-radius: 5px;
    margin-top: 5px;
    padding: 20px 22px;
    cursor: pointer;
    transition: background 0.4s ease 0s;
    text-align: center;
    max-width: 210px;
}

.offer-main-button-bet-icon {
    display: inline-block;
    margin-right: 5px;
    width: 22px;
}

.offer-main-button-bet-txt {
    color: #fff;
    display: inline-block;
    padding-top: 5px;
    text-align: center;
}

.colBottomMargin {
    margin-bottom: 30px
}

.scrollup {
    bottom: 40px;
    color: #fd810f;
    font-size: 42px;
    height: 40px;
    position: fixed;
    right: 22px;
    text-align: center;
    width: 40px;
    z-index: 999;
}

.footer {
    padding-top: 20px;
    position: fixed;
    bottom: 0;
    background-color: #fff;
    width: 100%;
}

.modal-dialog {
    max-width: 700px;
    margin: 50px auto;
}

.modal-header {
    border: none;
    /* padding: 15px 5px 0; */
    text-align: center;
}

.modal-header .close {
    margin: 0;
    padding: 0 15px;
    color: #0097e6;
    opacity: 1;
}

.popup-title {
    color: #0097e6;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin: 0 auto 20px;
}

.modal-body {
    padding: 0 20px 15px;
}

.popup-desc {
    color: #808080;
    font-size: 24px;
    margin: 30px auto;
    text-align: center;
}

.popup-app-mercy {
    background: #0097e6;
    border-radius: 5px;
    height: 71px;
    padding: 20px;
    text-align: center;
}

.popup-app-mercy-icon {
    /* background: rgba(0, 0, 0, 0) url("../images/sprite.html") repeat scroll -40px -75px;  */
    display: inline-block;
    float: none;
    height: 32px;
    vertical-align: middle;
    width: 32px;
}

.popup-app-mercy-txt {
    color: #fff;
    display: inline-block;
    font-size: 15px;
}

.popup-desc-email {
    color: #808080;
    font-size: 17px;
    margin: 30px auto 15px;
    text-align: center;
}

.popup-input-mail {
    border: 1px solid #cccccc;
    border-radius: 5px;
    font-size: 18px;
    height: 50px;
    text-align: center;
    width: 100%;
}

.popup-form select {
    background-color: white;
    display: inline-block;
    box-sizing: border-box;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: linear-gradient(45deg, transparent 50%, #304f6f 50%), linear-gradient(135deg, #304f6f 50%, transparent 50%), radial-gradient(transparent 66%, transparent 66%);
    background-position: calc(100% - 18px) calc(1em + 2px), calc(100% - 13px) calc(1em + 2px), calc(100% - .5em) .5em;
    background-size: 5px 6px, 6px 5px, 1.5em 1.5em;
    background-repeat: no-repeat;
}

.popup-form select:focus {
    background-image: linear-gradient(45deg, transparent 50%, #f58e03 50%), linear-gradient(135deg, #f58e03 50%, transparent 50%), radial-gradient(transparent 66%, transparent 66%);
}

.btn {
    font-size: 16px;
    overflow: hidden;
    padding: 6px 20px;
    text-transform: uppercase;
}

.btn-custom {
    background-color: #304f6f;
    border: 1px solid #3e3e3e;
    color: #fff;
    -webkit-box-shadow: 0 0 1px transparent;
    box-shadow: 0 0 1px transparent;
    display: inline-block;
    position: relative;
    -moz-transform: perspective(1px) translateZ(0px);
    -webkit-transform: perspective(1px) translateZ(0px);
    -o-transform: perspective(1px) translateZ(0px);
    -ms-transform: perspective(1px) translateZ(0px);
    transform: perspective(1px) translateZ(0px);
    -webkit-transition-duration: 0.3s;
    transition-duration: 0.3s;
    -webkit-transition-property: color;
    transition-property: color;
    vertical-align: middle;
}

.btn-custom::before {
    background-color: #fb9902;
    bottom: 0;
    content: "";
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    -moz-transform: scaleX(0);
    -webkit-transform: scaleX(0);
    -o-transform: scaleX(0);
    -ms-transform: scaleX(0);
    transform: scaleX(0);
    -webkit-transform-origin: 50%;
    transform-origin: 50%;
    -webkit-transition-property: transform;
    transition-property: transform;
    -webkit-transition-duration: 0.3s;
    transition-duration: 0.3s;
    -webkit-transition-timing-function: ease-out;
    transition-timing-function: ease-out;
    z-index: -1;
}

.btn-custom:hover,
.btn-custom:focus,
.btn-custom:active {
    border-color: #f58e03;
}

.btn-custom:hover::before,
.btn-custom:focus::before,
.btn-custom:active::before {
    -moz-transform: scaleX(1);
    -webkit-transform: scaleX(1);
    -o-transform: scaleX(1);
    -ms-transform: scaleX(1);
    transform: scaleX(1);
}

.btn.btn-custom.disabled {
    opacity: 1;
}

.btn.focus,
.btn:focus,
.btn:hover {
    color: #fff;
}

.popup-form .form-group {
    position: relative;
    margin: 0 0 30px;
}

.popup-form .input-group-icon {
    position: absolute;
    top: 0;
}

.popup-form .form-group .input-group-icon {
    background-color: #304f6f;
    border: none;
    border-radius: 4px;
    border-bottom-right-radius: 0;
    border-top-right-radius: 0;
    color: #fff;
    display: table-cell;
    font-size: 14px;
    height: 100%;
    padding: 5px 7px 7px;
    text-align: center;
    vertical-align: top;
    white-space: nowrap;
    width: 40px;
}

.popup-form .form-control {
    background-color: transparent;
    border: 1px solid #304f6f;
    padding-left: 50px;
}

.popup-form .form-control:focus,
.popup-form .has-error .form-control:focus {
    border-color: #f58e03;
    box-shadow: 0 1px 1px rgba(245, 142, 3, 0.075) inset, 0 0 8px rgba(245, 142, 3, 0.6);
}

.popup-form .form-control:focus+.input-group-icon,
#resetPassForm .has-error .form-control:focus+.input-group-icon {
    background-color: #f58e03;
    color: #fff;
}

.popup-form .btn.dropdown-toggle.btn-default {
    background: transparent none repeat scroll 0 0;
    border: 0 none;
    border-radius: 0;
    box-shadow: none;
    font-size: 14px;
    color: #555;
    padding: 6px 0;
    text-shadow: none;
}

.popup-form span.sub-text {
    bottom: 50px;
    color: #ce0606;
    font-size: 14px;
    position: absolute;
    right: 50px;
}

.popup-form .has-error .form-control {
    border-color: #ce0606;
}

.popup-form .has-error .input-group-icon {
    background-color: #ce0606;
    color: #ffffff;
}

.popup-form .help-block {
    color: #ce0606;
    font-size: 14px;
    margin: 0;
    padding-left: 42px;
    position: absolute;
    top: -20px;
}

.popup-form .text-success {
    color: #37a000;
}

.popup-form .text-danger,
.text-danger {
    color: #ce0606;
}

.popup-form .btn.disabled {
    opacity: 1;
}

.popup-form .form-group .checkbox {
    margin: 0;
}

.popup-form input[type="checkbox"] {
    margin-top: 4px;
}

.popup-form .btn.btn-custom {
    transition: all 0.5s ease 0s;
    width: 200px;
    border-radius: 6px;
}

.popup-form .btn.btn-custom::after {
    content: "\f0a9";
    font-family: fontawesome;
    font-size: 22px;
    color: #fff;
    opacity: 0;
    position: absolute;
    right: 50px;
    transition: all 0.3s ease 0s;
}

.popup-form .btn.btn-custom:hover::after {
    opacity: 1;
    right: 10px;
    top: 2px;
}

.h3.text-success,
.h3.text-danger {
    margin: 0 auto 30px;
}

.popup-form .form-group h5 {
    margin-top: 0;
}

#humanCheckCaptchaBox,
#humanCheckCaptchaInput,
#firstDigit,
#secondDigit,
#mathfirstnum,
#mathsecondnum {
    display: inline;
}

#humanCheckCaptchaInput.form-control {
    height: 30px;
    margin-left: 10px;
    padding: 4px;
    text-align: center;
    width: 45px;
}

#firstDigit #mathfirstnum,
#secondDigit #mathsecondnum {
    border: none;
    box-shadow: none;
    width: 30px;
    height: 30px;
    padding: 0;
    pointer-events: none;
    text-align: center;
}

.table td {
    padding: 0.6rem;
}

@media screen and (max-width: 420px) {

    #firstDigit #mathfirstnum,
    #secondDigit #mathsecondnum {
        width: 20px;
    }
}

@media screen and (max-width: 360px) {
    #contactForm {
        padding: 50px 30px 20px;
    }

    #contactForm span.sub-text {
        right: 30px;
    }
}
</style>