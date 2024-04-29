<template>
    <div class="product-bg p-3">
        <div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center">
            <div class="pl-2 d-flex row w-100">
                <div class="col-md-4 col-sm-4 col-12 d-flex flex-column">
                    <div class="card title-card">
                        <div class="card-body">
                            <h5 class="font-weight-bold m-0">Gestion de Operaciones</h5>
                            <span>Crear operaciones y sus categorias</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 col-12 d-flex flex-column">
                    <button type="button" data-toggle="modal" data-target="#categoriesModal"
                        class="m-1 btn-width btn bg-primario text-white text-capitalize">
                        Categorias
                    </button>
                    <button type="button" data-toggle="modal" data-target="#subCategoriesModal"
                        class="m-1 btn-width btn bg-secundario text-white text-capitalize">
                        SubCategorias
                    </button>
                </div>
            </div>
        </div>
        <!-- Formulario de operaciones -->
        <div class="pt-5 pb-3 px-3 px-sm-5">
            <div class="row">
                <div class="tab-content">
                    <div class="col-sm-12">
                        <div class="item-wrap">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="item-content colBottomMargin">
                                        <div class="item-info">
                                            <h2 class="item-title">Operaciones</h2>
                                        </div><!--End item-info -->
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
                                                :class="{ 'has-error': submitted && !isValidRut }">
                                                <div class="help-block with-errors"></div>
                                                <input v-model="rut" name="rut" id="rut" placeholder="RUT"
                                                    class="form-control" :disabled="this.disableForm" type="text"
                                                    required="" data-error="Por favor ingresa tu número de RUT">
                                                <div class="input-group-icon"><i class="far fa-address-card"></i></div>
                                            </div><!-- end form-group -->

                                            <div class="form-group col-sm-4"
                                                :class="{ 'has-error': submitted && !isValidCompanyName }">
                                                <div class="help-block with-errors"></div>
                                                <input v-model="company_name" name="company_name" id="company_name"
                                                    placeholder="Nombre de la compañia" class="form-control"
                                                    :disabled="this.disableForm" type="text" required=""
                                                    data-error="Por favor ingresa el nombre de la compañia">
                                                <div class="input-group-icon"><i class="fas fa-building"></i></div>
                                            </div><!-- end form-group -->

                                            <div class="form-group col-sm-8"
                                                :class="{ 'has-error': submitted && !isValidFactura }">
                                                <div class="help-block with-errors"></div>
                                                <input v-model="factura" name="factura" id="factura"
                                                    placeholder="N° Factura" class="form-control"
                                                    :disabled="this.disableForm" type="text" required=""
                                                    data-error="Por favor ingresa tu factura">
                                                <div class="input-group-icon"><i class="fas fa-file-invoice"></i></div>
                                            </div><!-- end form-group -->


                                            <div class="form-group col-sm-12"
                                                :class="{ 'has-error': submitted && !isValidTotal }">
                                                <div class="help-block with-errors"></div>
                                                <input v-model="total" name="total" id="total" placeholder="Total $"
                                                    class="form-control" :disabled="this.disableForm" type="number"
                                                    required="" data-error="Por favor ingresa el total">
                                                <div class="input-group-icon"><i class="fas fa-dollar-sign"></i></div>
                                            </div><!-- end form-group -->

                                            <div class="form-group last col-sm-12">
                                                <button type="button"
                                                    class="m-1 btn-width btn bg-primario text-white text-capitalize"
                                                    @click="newOperation">
                                                    <i class="fas fa-check"></i> Crear Operacion
                                                </button>
                                            </div><!-- end form-group -->

                                            <!-- <span class="sub-text">* Campos requeridos</span> -->
                                            <div class="clearfix"></div>
                                        </div><!-- end row -->
                                    </form><!-- end form -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Filtros de operaciones -->
        <!-- v-if="productsGet" -->
        <div class="d-flex row w-100 pt-4 px-5">
            <div class="col-md-4 col-sm-6 col-12">
                <label for="ProductName">Buscar</label>
                <input :disabled="this.disableCategory" id="ProductName" v-model="OperationName" type="text"
                    class="form-control" @keypress.enter="getOperations" />
            </div>
            <!-- v-if="categoriesInstalled && productsGet" -->
            <div class="col-md-4 col-sm-6 col-12">
                <label for="Category">Categoria</label>
                <select class="form-control" v-model="category" @change="getOperations" :disabled="disableCategory">
                    <option :value="null" class="text-capitalize">Todas</option>
                    <option :value="category.id" v-for="category in categories" v-if="category.status === 0"
                        :key="category.id" class="text-capitalize">{{ category.name }}</option>
                </select>
            </div>
            <!-- <div v-if="downloadExcelInstaller"
                class="col-lg-2 col-md-4 col-sm-6 col-12 d-flex justify-content-md-end align-items-end">
                <a @click="downloadExcel"
                    :class="['mt-2 mb-0 mx-0 w-100 btn bg-primario text-white text-capitalize', { 'disabled': offOn }]">
                    Inventario
                </a>
            </div> -->
        </div>

        <!-- Listado de operaciones-->
        <!-- v-if="(operations && operations.items.length > 0)" -->
        <div ref="loaderProduct" class="vld-parent px-5 mt-2">
            <!-- tabla -->
            <customTable v-if="operationTable" v-model="jsonTable" @orderBy="orderBy" v-slot="props">
                <!-- <a @click="selectOperation(props.item)" class="py-1 px-2 text-center btn bg-secundario" href="#"
                    data-toggle="modal" data-target="#newProductModal">
                    <i class="fas fa-edit"></i>
                </a> -->
                <a @click="openVerify(props.item)" class="py-1 px-2 text-center btn bg-primario" href="#">
                    <i class="fas fa-trash-alt"></i>
                </a>
                <!-- <a @click="openHistoryProduct(props.item)" class="py-1 px-2 text-center btn bg-dark" href="#">
                    <i class="fas fa-solid fa-eye"></i>
                </a> -->
            </customTable>
            <!-- lista
            <div v-if="!operationTable" class="row">
                <div v-for="(product, index) in operations.items" :key="'operation-' + index" class="products__col">
                    <card-product v-if="operation.category_status === 0" :operation="operation" @edit="selectOperation"
                        @remove="openVerify"></card-product>
                </div>
            </div> -->

            <!-- Paginacion -->
            <paginate v-if="(operations && operations.pages > 1)" v-model="operations" :offOn="offOn"
                @getPage="getOperations" />
        </div>
        <!-- v-else -->
        <div ref="loaderProduct" class="vld-parent px-2 mt-2">
            <div class="box-false d-flex flex-center text-center p-2 w-100">
                <h2>No existen operaciones actualmente</h2>
            </div>
        </div>

        <!-- Modales -->
        <categories />
        <sub-categories />
    </div>

</template>

<script>
// Components
import paginate from '@/components/MPage.vue';
import verifyModal from '@/components/modals/verifyDelete.vue';
import customTable from '@/components/tables/table.vue';
import catalog from '@/components/modals/pedidos/catalog.vue';
import cardProduct from '@/components/cards/card_product.vue';
import categories from '@/components/modals/OperationsCategories.vue';
import subCategories from '@/components/modals/OperationsSubCategories.vue';
// Helpers
import Loader from '@/helpers/Loader';
import moment from 'moment';
import FormatNumber from '@/helpers/FormatNumber.js';

const { shell } = require('electron');

export default {
    name: 'operations',

    components: {
        verifyModal,
        paginate,
        customTable,
        catalog,
        categories,
        subCategories
    },
    data() {
        return {
            name: '',
            rut: '',
            factura: '',
            company_name: '',
            total: 0,

            disableCategory: false,
            disableForm: false,
            submitted: false,

            category: null,

            OperationName: '',

            categories: {
                0: { id: '1', status: 0, name: 'Cat 1' },
                1: { id: '2', status: 0, name: 'Cat 2' },
                2: { id: '3', status: 0, name: 'Cat 3' },
            },

            operations: {
                items: {
                    0: { name: '1', company_name: 'Company Name A', rut: 'Cat A', factura: '1', created_at: '1', total: '1', },
                }
            },

            jsonTable: {
                btn: true,
                items: {
                    0: { name: 'Orlando', company_name: 'Company Name A', rut: '12345678-0', factura: '1', created_at: '2024-04-25 17:52:37', total: '100', },
                    1: { name: 'Jimmy', company_name: 'Company Name B', rut: '12345678-0', factura: '2', created_at: '2024-04-25 17:52:37', total: '2000', },
                    2: { name: 'Maria', company_name: 'Company Name C', rut: '12345678-0', factura: '3', created_at: '2024-04-25 17:52:37', total: '3000', },
                },
                rows: [
                    { key: 'name', class: '', permission: 'default' },
                    { key: 'company_name', class: '', permission: 'default', subKey: 'cecina', subPermission: 'cecinaInstalled', },
                    { key: 'rut', class: '', permission: 'categoriesInstalled' },
                    { key: 'factura', class: '', permission: 'stockInstalled' },
                    { key: 'created_at', class: '', permission: 'stockInstalled' },
                    { key: 'total', class: '', permission: 'stockInstalled' },
                ],
                titles: [
                    { label: 'Nombre', class: 'th-sm', permission: 'default', type: 'orderBy', orderBy: false },
                    { label: 'Empresa', class: 'th-sm', permission: 'categoriesInstalled', type: false },
                    { label: 'RUT', class: 'th-sm', permission: 'default', type: false },
                    { label: 'Factura', class: 'th-sm', permission: 'default', type: false },
                    { label: 'Fecha', class: 'th-sm', permission: 'default', type: false },
                    { label: 'Total', class: 'th-sm', permission: 'default', type: false },
                    { label: 'Acciones', class: 'th-sm text-center', permission: 'default', type: false },
                ]
            },
        }
    },
    async beforeCreate() {
        var request = await this.$store.dispatch('main/refreshData', '?slim');
        this.app = request.data;
    },
    async mounted() {

    },
    computed: {
        // operationTable:{ get(){ return ConfigHelper.ConfStr('modulos.productos.ajustes.productos_tabla'); } },
        operationTable: { get() { return true; } },
    },
    methods: {
        newOperation() {

        },
        async getOperations(page = false, isRefresh = false) {

        },
        orderBy() {
            this.jsonTable.titles[0].orderBy = !this.jsonTable.titles[0].orderBy;
            // this.refreshData();
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
    /* -webkit-appearance: none; */
    /* -moz-appearance: none; */
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