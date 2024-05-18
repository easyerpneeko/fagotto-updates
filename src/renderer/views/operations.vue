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
                                                    id="contactName" placeholder="Tu nombre" class="form-control"
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

                                            <div class="form-group col-sm-6" :class="{ 'has-error': submitted }">
                                                <div class="help-block with-errors"></div>
                                                <select class="form-control" v-model="category"
                                                    @change="loadSubcategories" :disabled="this.disableForm"
                                                    placeholder="Categorias">
                                                    <!-- <option disabled selected class="text-capitalize">Todas</option> -->
                                                    <option :value="category" v-for="category in categories"
                                                        :key="category.id" class="text-capitalize">
                                                        {{ category.name }}
                                                    </option>
                                                </select>
                                                <div class="input-group-icon"><i class="fas fa-bars"></i></div>
                                            </div><!-- end form-group -->

                                            <div class="form-group col-sm-6" :class="{ 'has-error': submitted }">
                                                <div class="help-block with-errors"></div>
                                                <select class="form-control" v-model="subcategory"
                                                    :disabled="this.disableForm" placeholder="Subcategorias">
                                                    <!-- <option disabled selected class="text-capitalize">Todas</option> -->
                                                    <option :value="subcategory"
                                                        v-for="subcategory in filtersubcategories" :key="subcategory.id"
                                                        class="text-capitalize">
                                                        {{ subcategory.name }}
                                                    </option>
                                                </select>
                                                <div class="input-group-icon"><i class="fas fa-stream"></i></div>
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
            <div class="col-md-2 col-sm-4 col-12">
                <label for="OperationName">Buscar</label>
                <input :disabled="this.disableCategory" id="OperationName" v-model="OperationName" type="text"
                    class="form-control" @keypress.enter="getOperations" />
            </div>
            <!-- v-if="categoriesInstalled && productsGet" -->
            <div class="col-md-4 col-sm-6 col-12">
                <label for="Category">Categoria</label>
                <select class="form-control" v-model="categoryName" @change="getOperations" :disabled="disableCategory">
                    <option :value="null" class="text-capitalize" @click="this.subCategoryName == null">Todas</option>
                    <option :value="category" v-for="category in categories" :key="category.id" class="text-capitalize">
                        {{ category.name }}</option>
                </select>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <label for="Subcategory">Subcategorias</label>
                <select class="form-control" v-model="subCategoryName" @change="getOperations">
                    <option :value="null" class="text-capitalize">Todas</option>
                    <option :value="subcategory" v-for="subcategory in filtersubcategories" :key="subcategory.id"
                        class="text-capitalize">{{ subcategory.name }}</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 col-12 d-flex justify-content-md-end align-items-end">
                <button type="button" data-toggle="modal" data-target="#balancesModal" @click="getBalances"
                    class="m-1 btn-width btn bg-dark text-white text-capitalize">
                    Balances
                </button>
            </div>
        </div>

        <!-- Listado de operaciones-->
        <!-- v-if="(operations && operations.items.length > 0)" -->
        <div ref="loaderOperation" v-if="(jsonTable.items.length > 0)" class="vld-parent px-5 mt-2">
            <!-- tabla -->
            <customTable v-if="operationTable" v-model="jsonTable" @orderBy="orderBy" v-slot="props">
                <a @click="selectOperation(props.item)" class="py-1 px-2 text-center btn bg-secundario" href="#"
                    data-toggle="modal" data-target="#editOperationModal">
                    <i class="fas fa-edit"></i>
                </a>
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
                        @remove="b"></card-product>
                </div>
            </div> -->

            <!-- Paginacion -->
            <paginate v-if="(operations && operations.pages > 1)" v-model="operations" :offOn="offOn"
                @getPage="getOperations" />
        </div>
        <!-- v-else -->
        <div ref="loaderOperation" v-else class="vld-parent px-2 mt-2">
            <div class="box-false d-flex flex-center text-center p-2 w-100">
                <h2>No existen operaciones actualmente</h2>
            </div>
        </div>

        <!-- Modales -->

        <div class="modal fade" id="balancesModal" tabindex="-1" role="dialog" aria-labelledby="balancesModal"
            aria-hidden="true" data-backdrop="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primario">
                        <h5 class="modal-title">Balances</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <!-- title -->
                        <div class="w-100 p-2">
                            <h4 class="text-primario">Lista de totales por categoria</h4>
                            <hr class="borderTitleCafeteria w-100" />
                        </div>
                        <div class="scroll-table px-2">
                            <template>
                                <b class="p-2">Total de operaciones: ${{
                                                    formatNumber(deFormatNumber(this.total_operaciones)) }}</b>
                            </template>
                            <table class="m-0 table table-striped table-bordered table-sm w-100" id="balancesTable">
                                <template v-for="category in balances">
                                    <template v-if="category.operations_count > 0">
                                        <thead>
                                            <tr>
                                                <th class="text-center border-0" colspan="3">{{ category.name }}</th>
                                            </tr>
                                            <tr>
                                                <th class="text-bold th-sm">Subcategoria</th>
                                                <th class="text-bold th-sm"># Operaciones</th>
                                                <th class="text-bold th-sm">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="subcategory in category.subcategories" :key="subcategory.id">
                                                <td>
                                                    <span class="m-0 p-0">{{ subcategory.name }}</span>
                                                </td>
                                                <td>
                                                    <span class="m-0 p-0">{{ subcategory.operations_count }}</span>
                                                </td>
                                                <td>
                                                    <span class="m-0 p-0">
                                                        {{ subcategory.operations.length > 0 ? '$' +
                                                    formatNumber(deFormatNumber(subcategory.operations[0].operations_sum_total))
                                                    : '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="border-0">Total: {{ category.operations.length > 0 ? '$' +
                                                    formatNumber(deFormatNumber(category.operations[0].operations_sum_total))
                                                    : '-' }}</th>
                                            </tr>
                                        </tbody>

                                    </template>
                                </template>
                            </table>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button @click="descargarExcel" type="button" class="btn bg-info text-white"
                            data-dismiss="modal">Exportar a
                            excel</button>
                        <button type="button" class="btn bg-secundario text-white" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="this.operation != null" class="modal fade" id="editOperationModal" tabindex="-1" role="dialog"
            aria-labelledby="balancesModal" aria-hidden="true" data-backdrop="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primario">
                        <h5 class="modal-title">Editar Operacion</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="row">
                            <div class="col-sm-12">
                            </div><!--End col -->
                            <div class="col-md-12">
                                <form id="contactForm2" name="contactform2" data-toggle="validator" class="popup-form"
                                    novalidate="true">
                                    <div class="row">

                                        <div class="form-group col-sm-6"
                                            :class="{ 'has-error': submitted && !isValidName }">
                                            <div class="help-block with-errors"></div>
                                            <input v-model="operation.name" :disabled="this.disableForm" name="name"
                                                id="operationName" placeholder="Tu nombre" class="form-control"
                                                type="text" required="" data-error="Por favor ingresa tu nombre">
                                            <div class="input-group-icon"><i class="fa fa-user"></i></div>

                                        </div><!-- end form-group -->
                                        <div class="form-group col-sm-6"
                                            :class="{ 'has-error': submitted && !isValidRut }">
                                            <div class="help-block with-errors"></div>
                                            <input v-model="operation.rut" name="rut" id="rut" placeholder="RUT"
                                                class="form-control" :disabled="this.disableForm" type="text"
                                                required="" data-error="Por favor ingresa tu número de RUT">
                                            <div class="input-group-icon"><i class="far fa-address-card"></i></div>
                                        </div><!-- end form-group -->

                                        <div class="form-group col-sm-4"
                                            :class="{ 'has-error': submitted && !isValidCompanyName }">
                                            <div class="help-block with-errors"></div>
                                            <input v-model="operation.company_name" name="company_name"
                                                id="company_name" placeholder="Nombre de la compañia"
                                                class="form-control" :disabled="this.disableForm" type="text"
                                                required="" data-error="Por favor ingresa el nombre de la compañia">
                                            <div class="input-group-icon"><i class="fas fa-building"></i></div>
                                        </div><!-- end form-group -->

                                        <div class="form-group col-sm-8"
                                            :class="{ 'has-error': submitted && !isValidFactura }">
                                            <div class="help-block with-errors"></div>
                                            <input v-model="operation.factura" name="factura" id="factura"
                                                placeholder="N° Factura" class="form-control"
                                                :disabled="this.disableForm" type="text" required=""
                                                data-error="Por favor ingresa tu factura">
                                            <div class="input-group-icon"><i class="fas fa-file-invoice"></i></div>
                                        </div><!-- end form-group -->


                                        <div class="form-group col-sm-12"
                                            :class="{ 'has-error': submitted && !isValidTotal }">
                                            <div class="help-block with-errors"></div>
                                            <input v-model="operation.total" name="total" id="total"
                                                placeholder="Total $" class="form-control" :disabled="this.disableForm"
                                                type="number" required="" data-error="Por favor ingresa el total">
                                            <div class="input-group-icon"><i class="fas fa-dollar-sign"></i></div>
                                        </div><!-- end form-group -->
                                        <div class="form-group col-sm-6" :class="{ 'has-error': submitted }">
                                            <div class="help-block with-errors"></div>
                                            <select class="form-control" v-model="operation.operations_categories_id"
                                                @change="loadSubcategories" :disabled="this.disableForm"
                                                placeholder="Categorias">
                                                <option :value="category.id" v-for="category in categories"
                                                    :selected="operation.operations_categories_id.id === category.id"
                                                    :key="category.id" class="text-capitalize">
                                                    {{ category.name }}
                                                </option>
                                            </select>
                                            <div class="input-group-icon"><i class="fas fa-bars"></i></div>
                                        </div><!-- end form-group -->

                                        <div class="form-group col-sm-6" :class="{ 'has-error': submitted }">
                                            <div class="help-block with-errors"></div>
                                            <select class="form-control" v-model="operation.operations_subcategories_id"
                                                :disabled="this.disableForm" placeholder="Subcategorias">
                                                <option :value="subcategory.id"
                                                    v-for="subcategory in filtersubcategories"
                                                    :selected="operation.operations_subcategories_id === subcategory.id"
                                                    :key="subcategory.id" class="text-capitalize">
                                                    {{ subcategory.name }}
                                                </option>
                                            </select>
                                            <div class="input-group-icon"><i class="fas fa-stream"></i></div>
                                        </div><!-- end form-group -->
                                        <!-- <span class="sub-text">* Campos requeridos</span> -->
                                        <div class="clearfix"></div>
                                    </div><!-- end row -->
                                </form><!-- end form -->

                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-secundario text-white" data-dismiss="modal">Cerrar</button>
                        <button type="button" @click="editOperation()" class="btn bg-primario text-white"
                            data-dismiss="modal">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <verify-modal :propVerify="propVerify" @refreshData="getOperations" />
        <categories />
        <sub-categories />
    </div>

</template>

<script>
// Components
import paginate from '@/components/MPage.vue';
import verifyModal from '@/components/modals/verifyDelete.vue';
import customTable from '@/components/tables/table.vue';
import categories from '@/components/modals/OperationsCategories.vue';
import subCategories from '@/components/modals/OperationsSubCategories.vue';
// Helpers
import Loader from '@/helpers/Loader';
import FormatNumber from '@/helpers/FormatNumber.js';
import XLSX from 'xlsx';

const { shell } = require('electron');

export default {
    name: 'operations',

    components: {
        verifyModal,
        paginate,
        customTable,
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


            operation: null,

            disableCategory: false,
            disableSubcategory: true,
            disableForm: false,
            submitted: false,
            waitResponse: false,
            propVerify: null,
            oldPage: 1,
            category: null,
            subcategory: null,
            filtersubcategories: [],
            OperationName: '',
            total_operaciones: 0,
            categoryName: null,
            subCategoryName: null,
            // categories: {},

            AllSubcategories: [],
            balances: null,

            operations: {
                items: {}
            },

            jsonTable: {
                btn: true,
                items: {},
                rows: [
                    { key: 'id', class: '', permission: 'default' },
                    { key: 'name', class: '', permission: 'default' },
                    { key: 'company_name', class: '', permission: 'default' },
                    { key: 'rut', class: '', permission: 'default' },
                    { key: 'factura', class: '', permission: 'default' },
                    { key: 'created_at', class: '', permission: 'default' },
                    { key: 'categories', class: '', permission: 'default' },
                    { key: 'subcategories', class: '', permission: 'default' },
                    { key: 'total', class: '', permission: 'default' },
                ],
                titles: [
                    { label: '#', class: 'th-sm', permission: 'default', type: false, orderBy: false },
                    { label: 'Nombre', class: 'th-sm', permission: 'default', type: 'orderBy', orderBy: false },
                    { label: 'Empresa', class: 'th-sm', permission: 'categoriesInstalled', type: 'orderBy' },
                    { label: 'RUT', class: 'th-sm', permission: 'default', type: false },
                    { label: 'Factura', class: 'th-sm', permission: 'default', type: false },
                    { label: 'Fecha', class: 'th-sm', permission: 'default', type: false },
                    { label: 'Categoria', class: 'th-sm', permission: 'default', type: 'orderBy' },
                    { label: 'Subcategoria', class: 'th-sm', permission: 'default', type: 'orderBy' },
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
        await this.getCategories();
        await this.getSubcategories()
        await this.getOperations(this.oldPage, true);

        //Get app
        var request = await this.$store.dispatch('main/refreshData', '?slim');
        console.log('data :', request.data);
        this.phone = request.data.Entorno.stgg_contacto.value;
    },
    computed: {
        // operationTable:{ get(){ return ConfigHelper.ConfStr('modulos.productos.ajustes.productos_tabla'); } },
        operationTable: { get() { return true; } },
        categories: {
            get() {
                return this.$store.getters['operations/categories'];
            }
        },
        offOn: {
            get() { return this.value },
            set(offOn) { this.$emit('input', offOn) }
        },
        isValidName: {
            get() { return this.name.length > 0 }
        },
        isValidRut: {
            get() { return this.rut.length > 9 }
        },
        isValidFactura: {
            get() { return this.factura.length > 0 }
        },
        isValidTotal: {
            get() { return this.total > 0 }
        },
        isValidCompanyName: {
            get() { return this.company_name.length > 0 }
        }

    },
    methods: {
        loadSubcategories() {
            // console.log(this.category);
            // if (categoryEdit) {
            //     this.category = categoryEdit;
            // }
            // Filtrar las subcategorías basándose en la categoría seleccionada
            if (this.category || this.operation.categories || this.categoryName) {
                this.filtersubcategories = this.AllSubcategories.filter(subcategory => subcategory.operations_categories_id === this.category.id);
                this.disableSubcategory = false; // Habilitar el select de subcategorías
            } else {
                this.filtersubcategories = this.AllSubcategories;
                this.disableSubcategory = true; // Mantener el select de subcategorías deshabilitado
            }
            console.log('filtrado:', this.filtersubcategories);
            console.log(this.AllSubcategories);
        },
        async getCategories() {
            this.waitResponse = true;
            await this.$store.dispatch("operations/getCategories");
            this.waitResponse = false;
        },
        async getSubcategories() {
            this.waitResponse = true;
            let request = await this.$store.dispatch("operations/getSubcategories");
            if (request.success) {
                this.AllSubcategories = request.data;
                console.log(request.data);
            }
            this.waitResponse = false;
        },
        async newOperation() {
            this.submitted = true;
            if (!this.validar_form()) {
                this.$awn.alert('Hay errores en el formulario');
                return;
            }
            const data = {
                name: this.name,
                rut: this.rut,
                company_name: this.company_name,
                factura: this.factura,
                total: this.total,
                operations_categories_id: this.category.id,
                operations_subcategories_id: this.subcategory.id
            };

            console.log('data', data);
            //Se construye formdata
            var formData = new FormData();
            for (let key in data) if (data[key]) formData.append(key, data[key]);

            this.waitResponse = true;
            Loader.fullPage();
            let request = await this.$store.dispatch('operations/newOperation', formData);
            Loader.hide();

            if (request.success) {
                this.$awn.success('Operacion creada Exitosamente', { labels: { success: 'CORRECTO' } });

                this.name = "";
                this.rut = null;
                this.company_name = "";
                this.factura = "";
                this.total = 0;
                this.category = null;
                this.subcategory = null;
            } else {
                console.log(request.data);
                this.$awn.alert('Error al enviar el pedido');
            }
            this.waitResponse = false;
            console.log(data);

            //refrescar data
            this.getOperations(false);
            this.submitted = false;

        },
        async getOperations(page = false, isRefresh = true) {
            if (!isRefresh) {
                this.disableCategory = true;
                Loader.containe(this.$refs.loaderOperation);
            }
            // this.loadSubcategories();
            var params = '?params=true';
            // if (page !== false) this.oldPage = '&page=' + page;
            // params += this.oldPage;

            if (this.categoryName != null && this.categoryName != '') params += '&categoryOfProduct=' + this.categoryName.id;

            if (this.subCategoryName != null && this.subCategoryName != '') params += '&subcategoryOfProduct=' + this.subCategoryName.id;

            if (this.OperationName != null && this.OperationName != '') params += '&nameOfOperation=' + this.OperationName;
            // Iniciando peticion
            this.offOn = true;
            Loader.containe(this.$refs.loaderRequests);
            var request = await this.$store.dispatch("operations/getOperations", params);
            console.log('Operaciones: ', request);
            Loader.hide();
            this.offOn = false;
            // Verificando respuesta
            // if (!request.success) return this.$awn.alert(request.data);
            if (!request.success) console.log('Error: ', request.data);

            this.requests = request.data;
            this.jsonTable.items = request.data;
        },
        // Refrescando productos
        async refreshData(loading = false) {
            // Iniciando refrescamiento (carga y botones disabled)
            // this.offOn = true;
            // this.disableCategory = true;
            // if (loading) Loader.containe(this.$refs.loaderProduct);
            // else Loader.dinamic();
            // await this.getOperations(this.oldPage, true);
            // // Culminando la funcion
            // Loader.hide();
            // this.disableCategory = false;
            // this.offOn = false;
        },
        orderBy() {
            this.jsonTable.titles[0].orderBy = !this.jsonTable.titles[0].orderBy;
            // this.refreshData();
        },
        openVerify(operation) {
            this.propVerify = {
                params: operation.id,
                title: 'Eliminar Operacion',
                text: '¿Usted esta seguro de eliminar la operacion #' + operation.id + '?',
                store: 'operations/removeOperation',
                success: 'Operacion eliminada exitosamente'
            };
            $('#verifyDelete').modal('show');
        },
        validar_form() {
            // if (!this.isValidProducts) {
            //     this.$awn.alert("Es necesario agregar algun producto");
            // }
            // if (!this.isValidName) {
            //     return false;
            // }
            return true;
        },
        async getBalances() {
            this.waitResponse = true;
            let request = await this.$store.dispatch("operations/getBalances");
            if (request.success) {
                this.balances = request.data;
                console.log('Balances:', request.data);
                this.balances.forEach(balance => {
                    this.total_operaciones += parseFloat(balance.operations[0].operations_sum_total);
                });
            }
            this.waitResponse = false;
        },
        formatNumber(number) {

            return FormatNumber.format(String(number));
        },
        deFormatNumber(number, backend = true) {
            if (backend) {
                //JC    console.log("--------------------------hola",number)
                return FormatNumber.deFormatBackend(String(number));
            } else {
                return FormatNumber.deFormat(String(number));
            }
        },
        async editOperation() {
            this.submitted = true;
            let fd = new FormData();
            fd.append('name', this.operation.name);
            fd.append('rut', this.operation.rut);
            fd.append('factura', this.operation.factura);
            fd.append('company_name', this.operation.company_name);
            fd.append('total', this.operation.total);
            // fd.append('user', this.operation.user);
            fd.append('operations_categories_id', this.operation.operations_categories_id);
            fd.append('operations_subcategories_id', this.operation.operations_subcategories_id);
            console.log(this.operation.operations_subcategories_id);

            let request = await this.$store.dispatch('operations/editOperation', { id: this.operation.id, data: fd });

            Loader.hide();

            if (request.success) {
                this.$awn.success('Operacion Editada Exitosamente', { labels: { success: 'CORRECTO' } });
                this.submitted = false;
                // this.selectOperation();
                this.getOperations();
                $('#editOperationModal').modal('hide');
            } else {
                console.log(request.data);
                this.submitted = false;
                let allErrors = request.data;
                if (typeof allErrors === 'object') {
                    for (let errorkey in allErrors) {
                        if (allErrors[errorkey]) {
                            for (let error of allErrors[errorkey]) {
                                this.$awn.alert(error);
                            }
                        }
                    }
                } else {
                    this.$awn.alert(allErrors);
                    this.submitted = false;
                }
            }
        },
        selectOperation(operation) {
            console.log(operation);
            this.operation = operation;
            this.category = operation.categories;
        },
        descargarExcel() {
            // Obtener los datos de la tabla
            const table = document.getElementById('balancesTable');
            const workbook = XLSX.utils.table_to_book(table);

            // Generar el archivo Excel
            const filename = 'tabla.xlsx';
            const wbout = XLSX.write(workbook, { bookType: 'xlsx', bookSST: true, type: 'binary' });

            const saveAs = (blob, fileName) => {
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName;

                // Simular un clic en el enlace para iniciar la descarga
                link.dispatchEvent(new MouseEvent('click'));

                // Limpiar el enlace y liberar la URL de objeto
                setTimeout(function () {
                    URL.revokeObjectURL(link.href);
                    link.remove();
                }, 0);
            };

            const s2ab = (s) => {
                const buf = new ArrayBuffer(s.length);
                const view = new Uint8Array(buf);
                for (let i = 0; i < s.length; i++) {
                    view[i] = s.charCodeAt(i) & 0xff;
                }
                return buf;
            };

            const fileData = s2ab(wbout);
            const blob = new Blob([fileData], { type: 'application/octet-stream' });

            // Descargar el archivo Excel con ventana "Guardar como"
            saveAs(blob, filename);
        }
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

.scroll-table {
    overflow: auto;
    height: 100%;
}

.scroll-table::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}

.scroll-table::-webkit-scrollbar-track {
    background: #c0c0c0;
    border-radius: 50px;
    height: 5px;
}

.scroll-table::-webkit-scrollbar-thumb {
    background: #000000;
    border-radius: 50px;
    height: 5px;
}

.footer_class {
    margin-right: 1px;
}

.btnOrderBy {
    cursor: pointer;
}

.fieldEdit {
    border: 2px solid #192b5f;
    border-radius: 5px;
    background: transparent;
    width: 100%;
    text-align: center;
}

.fieldEditTd {
    width: 100px;
}
</style>