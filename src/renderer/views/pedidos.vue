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
            <div class="row m-0 mb-2" v-if="true">
                <div class="col-md-12">
                    <h2><span class="fa fa-edit"></span> Nuevo Pedido</h2>
                    <hr>
                    <form class="form-horizontal" role="form" id="datos_pedido">
                        <div class="d-flex row w-100 pt-4">
                            <div class="col-md-2 col-sm-4 col-12">
                                <label for="app">Datos del negocio</label>
                                <p>{{ this.app.Name }}</p>
                            </div>
                            <div class="col-md-2 col-sm-4 col-12">
                                <label for="date">Fecha del pedido</label>
                                <p>{{ this.date }}</p>
                            </div>

                            <div class="col-md-2 col-sm-4 col-12">
                                <label for="paymode">Metodo de pago</label>
                                <select class="form-control" v-model="paymode" :disabled="this.disableForm"
                                    :class="{ 'invalid-input': submitted && !isValidPaymode }">
                                    <option :value="null" class="text-capitalize">Todas</option>
                                    <option :value="paymode.name" v-for="paymode in paymodes" :key="paymode.id"
                                        class="text-capitalize">
                                        {{ paymode.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-4 col-12">
                                <label for="contactName">Nombre</label>
                                <input :disabled="this.disableForm" id="contactName" v-model="name" type="text"
                                    class="form-control" :class="{ 'invalid-input': submitted && !isValidName }" />
                            </div>
                            <div class="col-md-2 col-sm-4 col-12">
                                <label for="contactPhone">Telefono</label>
                                <input :disabled="this.disableForm" id="contactPhone" v-model="phone" type="text"
                                    class="form-control" :class="{ 'invalid-input': submitted && !isValidPhone }" />
                            </div>
                            <div class="col-md-2 col-sm-4 col-12">
                                <label for="comment">Comentario</label>
                                <input :disabled="this.disableForm" id="comment" v-model="comment" type="text"
                                    class="form-control" :class="{ 'invalid-input': submitted && !isValidComment }" />
                            </div>
                            <label for="voucher" class="m-1 btn bg-primario text-white text-capitalize">
                                Cargar Comprobante
                                <input type="file" id="voucher" style="display: none;"
                                    accept="image/jpeg, image/png, application/pdf" @change="uploadVoucher">
                            </label>
                        </div>
                        <div class="d-flex row w-100 pt-4">
                            <div class="col-12">
                                <div class="actionsCafeteria">
                                    <button type="button" data-toggle="modal" data-target="#modalCatalog"
                                        @click="openCatalog"
                                        class="m-1 btn-width btn bg-secundario  text-white text-capitalize">
                                        Añadir Productos
                                    </button>
                                    <button type="button" class="m-1 btn-width btn bg-primario text-white text-capitalize"
                                        @click="newRequest">
                                        <i class="fas fa-check"></i> Crear Pedido
                                    </button>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="col-md-12">

                        </div>
                    </form>
                    <br><br>

                </div>

            </div>
            <!-- v-else -->
            <div class="m-0 my-2 text-center w-100" v-else>
                <h5>El modulo de SII no se encuentra activado</h5>
            </div>
        </div>
        <!-- Listado de pedidos -->
        <!-- <div v-if="requests && requests.items.length > 0" ref="loaderRequests" class="vld-parent px-2 mt-4"> -->
        <div v-if="requests" ref="loaderRequests" class="vld-parent px-2 mt-4">
            <customTable v-model="jsonTable" v-slot="props">
                <a @click="openProductsOrder(props.item)" class="py-1 px-2 text-center btn bg-primario" href="#">
                    <i class="fa fa-eye"></i>
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


        <!-- modals -->
        <!-- @refresh="refreshData" -->
        <catalog :products="products" @update-products="updateProducts" />

        <!-- modal productsOrders -->
        <div class="modal fade modalForce" id="productsOrder" tabindex="-1" role="dialog" aria-labelledby="productsOrder"
            aria-hidden="true" data-backdrop="false">
            <div class="modal-dialog lg-modal modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primario">
                        <h5 class="modal-title text-capitalize">Pedido # - XXX</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap boxCompleteOrder">
                            <div class="col-md-9 col-12 d-flex flex-column justify-content-between">
                                <div class="">
                                    <customTable v-model="jsonTableProducts" @changeValue="changeValue" v-slot="props">
                                        <a @click="removeProduct(props.index)" class="py-1 px-2 text-center btn bg-primario"
                                            href="#">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </customTable>
                                    <!-- <div class="footerTableTicket d-flex justify-content-between">
                                        <h5 class="">
                                            SUBTOTAL
                                        </h5>
                                        <h5 id="SubTotal-On-CompleteOrder">
                                            ${{ formatNumber(subtotal) }}
                                        </h5>
                                    </div> -->
                                    <div class="footerTableTicket d-flex justify-content-between">
                                        <h5 class="">
                                            TOTAL
                                        </h5>
                                        <h5 id="Total-On-CompleteOrder">
                                            ${{ formatNumber(total) }}
                                        </h5>
                                    </div>
                                </div>
                                <!-- <div class="d-flex flex-column w-100 displaymd mt-2">
                                    <button @click="printOrderTotal" type="button"
                                        class="btn mx-0 my-1 text-center bg-secundario text-white no-caps">Imprimir ticket
                                        total</button>
                                    <button @click="printTicket" type="button"
                                        class="btn mx-0 my-1 text-center bg-dark text-white no-caps">Imprimir
                                        ticket</button>
                                    <button @click="addProducts" type="button"
                                        class="btn mx-0 my-1 text-center bg-primario text-white no-caps">Agregar más
                                        productos</button>
                                    <button @click="changeWaiter" type="button"
                                        class="btn mx-0 my-1 text-center bg-dark text-white no-caps">Cambiar mesero</button>
                                    <button @click="changeBoard" type="button"
                                        class="btn mx-0 my-1 text-center bg-primario text-white no-caps">Cambiar
                                        mesa</button>
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeProductsOrder()" type="button" class="btn bg-primario text-white">
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
            paymode: '',
            voucherFile: null,

            appName: 'Negocio de prueba',
            date: moment().format('YYYY-MM-DD HH:mm:ss'),

            disableForm: false,
            submitted: false,

            oldPage: '&page=1',
            requests: null,
            app: null,

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
                    { key: 'status', class: '', permission: 'default' },
                    { key: 'comment', class: '', permission: 'default' },
                ],
                titles: [
                    { label: 'Nombre', class: '', permission: 'default', type: false },
                    { label: 'Telefono', class: '', permission: 'default', type: false },
                    { label: 'Metodo de pago', class: 'default', permission: 'default', type: false },
                    { label: 'Estado', class: 'default', permission: 'default', type: false },
                    { label: 'Comentario', class: 'default', permission: 'default', type: false },
                    { label: 'Detalles', class: 'th-sm text-center', permission: 'default', type: false },
                ]
            },
            jsonTableProducts: {
                btn: false,
                items: null,
                rows: [
                    { key: 'name', class: '', permission: 'default' },
                    { key: 'price', class: '', permission: 'default' },
                    { key: 'quantity', class: '', permission: 'default' },
                    { key: 'subtotal', class: '', permission: 'default' },
                ],
                titles: [
                    { label: 'Nombre', class: '', permission: 'default', type: false },
                    { label: 'Precio', class: '', permission: 'default', type: false },
                    { label: 'Cantidad', class: '', permission: 'default', type: false },
                    { label: 'Subtotal', class: '', permission: 'default', type: false },
                    // { label: '', class: '', permission: 'default', type: false },
                ]
            },
            subtotal: this.total,
            total: 0,
        }
    },
    async mounted() {
        this.app = await this.getApp();
        // console.log(this.app.data.Name);
        this.getRequests(false);
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
            this.total = 0;
            products.map((product) => {
                this.total += parseFloat(product.subtotal);
            });
            // this.subtotal = this.total;
        },
        openProductsOrder(request) {
            this.jsonTableProducts.items = JSON.parse(request.products);
            this.getTotal(this.jsonTableProducts.items);
            $('#productsOrder').modal('show');

        },
        closeProductsOrder() {

            $('#productsOrder').modal('hide');
        },
        async uploadVoucher(event) {
            const file = event.target.files[0];
            this.voucherFile = file;
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
        async newRequest() {
            this.submitted = true;
            if (!this.validar_form()) {
                this.$awn.alert('Hay errores en el formulario');
                return;
            }
            //Tranformar a Json
            this.products = JSON.stringify(this.products);
            const data = {
                contact_name: this.name,
                contact_phone: this.phone,
                paymode: this.paymode,
                voucher: this.voucherFile,
                status: 'enviado',
                products: this.products,
                comment: this.comment,
                app_id: '1',
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
                localStorage.clear();
                this.logout();
            } else {
                console.log(request.data);
                this.$awn.alert('Error al enviar el pedido');
            }
            this.waitResponse = false;
            console.log(data);

            //refrescar data
            this.getRequests(false);
            this.submitted = false;
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
        }
    }
}
</script>
<style scoped>
.invalid-input {
    border-color: red;
}
.boxCompleteOrder{
  min-height: 450px;
  justify-content: center;
}
</style>
  