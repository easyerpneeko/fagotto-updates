<template>
    <div class="modal fade" id="pedidoUrgente" tabindex="-1" role="dialog" aria-labelledby="pedidoUrgente"
        aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog lg-modal modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primario">
                    <h5 class="modal-title">Preparar pedido</h5>
                    <button type="button" class="close text-white" @click="closeModal(false)" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="overflow: auto; max-height: 70vh;">
                    <div class="d-flex flex-wrap justify-content-space-beetwen">
                        <div class="col-md-5">
                            <div class="row d-flex">
                                <div class="col-md-12 mb-3">
                                    <div class="row d-flex justify-content-start">
                                        <h5 class="modal-title p-2 m-1">Detallado del pedido</h5>
                                        <hr>
                                    </div>
                                </div>

                                <div class="col-md-12 p-2 m-1">
                                    <div class="row">
                                        <span class="m-0 p-0">
                                            <template v-for="(producto, index) in productos">
                                                <button v-if="producto.name != 'Despacho'" type="button"
                                                    @click="addProduct(producto)"
                                                    class="m-1 btn-width btn bg-primario text-white text-capitalize col-md-5">
                                                    {{ producto.name.toUpperCase() }} <i class="fa fa-plus"></i>
                                                </button>
                                            </template>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <h5 class="modal-title p-2 m-1">Detallado del pedido</h5>
                            <hr>
                            <table class="table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col"></th>
                                        <th scope="col">Precio Unitario</th>
                                        <th scope="col">Precio Total</th>
                                        <th scope="col">Remover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="productosPedido.length > 0" v-for="(producto, index) in productosPedido"
                                        :key="index">
                                        <td>{{ producto.name }}</td>

                                        <!-- Cantidad -->
                                        <td v-if="(producto.category == 4)">
                                            <input min="1" class="fieldEdit" type="number" v-model="producto.quantity"
                                                @change="calcularMontos()" />
                                            Kg
                                        </td>
                                        <td
                                            v-if="(producto.category == 3 || producto.name == 'Botella de Huevos 1L' || producto.name == 'Vaso')">
                                            <input min="1" class="fieldEdit" type="number" v-model="producto.quantity"
                                                @change="calcularMontos()" />
                                            {{ (producto.name == 'Botella de Huevos 1L' ? 'Botellas(s)' : 'Unidad(es)') }}
                                        </td>
                                        <td v-if="(producto.category == 2)">
                                            <input min="10" class="fieldEdit" type="number" v-model="producto.vasos"
                                                @change="calcularMontos()" />
                                            Vasos
                                        </td>

                                        <!-- Kilos de salsa -->
                                        <td v-if="(producto.category == 2)">
                                            {{ (producto.quantity = (producto.vasos * producto.price) / 1000) }} kg
                                        </td>
                                        <td v-if="(producto.category != 2)">

                                        </td>

                                        <!-- Precio unitario -->
                                        <td>
                                            ${{ (producto.compra * 1) }}
                                        </td>
                                        <!-- Precio Total -->
                                        <td v-if="(producto.category == 2)">
                                            ${{ (formatNumber(producto.vasos * producto.compra)) }}
                                        </td>
                                        <td v-else>
                                            ${{ (formatNumber(producto.quantity * producto.compra)) }}
                                        </td>
                                        <!-- Remover -->
                                        <td>
                                            <button class="btn btn-danger btn-m" @click="removeProduct(index)">
                                                <i class="fa fa-times-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-else>
                                        <td colspan="5">
                                            No hay productos añadidos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

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
                                        <td>${{ formatNumber(this.montoNeto) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Despacho {{ this.despacho * 100 }}%</td>

                                        <td>${{ formatNumber(this.montoDespacho) }}</td>
                                    </tr>
                                    <tr>
                                        <td>IVA {{ this.iva * 100 }}%</td>

                                        <td>${{ formatNumber(this.montoIva) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td>${{ formatNumber(this.totalPrice) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-end ">
                    <div>
                        <button type="button" class="btn bg-dark text-white" @click="closeModal()">
                            Cerrar
                        </button>
                        <button type="button" @click="addProducts()" class="btn bg-primario text-white">
                            Agregar Productos
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
            montoDespacho: 0,
            montoIva: 0,
            montoTotal: 0,
            montoOpcionales: 0,
            vasoxsalsa: 1

        }
    },
    components: {

    },
    async mounted() {
        //Trae los productos fijo de db 
        await this.getProducts();

    },
    methods: {
        async closeModal(refresh = false) {
            //Volvemos los arreglos al estado inicial

            this.montoDespacho = 0;
            this.montoIva = 0;
            this.montoTotal = 0;
            this.montoNeto = 0;
            this.productosPedido = [];
            this.productoSend = [];
            this.totalPrice = 0;

            $('#pedidoUrgente').modal('hide');
        },
        addProduct(producto) {
            const existingProduct = this.productosPedido.find((p) => p.name === producto.name);
            if (existingProduct) {
                console.log(`Producto ${producto.name} ya existente.`);
            } else {
                const productoCopia = Object.assign({}, producto);
                productoCopia.quantity = 1;
                if (productoCopia.category == 2) {
                    productoCopia.vasos = 10;
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
            for (var index in this.productosPedido) {
                if (this.productosPedido[index].category == 2) {
                    this.productosPedido[index].costo = this.productosPedido[index].vasos * this.productosPedido[index].compra;
                } else {
                    this.productosPedido[index].costo = this.productosPedido[index].quantity * this.productosPedido[index].compra;
                }

                this.montoNeto += parseFloat(this.productosPedido[index].costo);
            }

            this.montoDespacho = Math.ceil(this.despacho * this.montoNeto);
            this.montoIva = Math.ceil(this.iva * this.montoNeto);
            this.totalPrice = Math.ceil(this.montoNeto + this.montoDespacho + this.montoIva);

        },
        addProducts() {
            console.log('Productos pedido: ', this.productosPedido);

            this.productoSend = this.productosPedido;

            if (this.productoSend.length == 0) {
                this.$awn.alert("Es necesario agregar algun producto");
                return false;
            }

            // this.montoDespacho = this.despacho * this.montoNeto;
            // this.montoIva = this.iva * this.montoNeto;

            this.totalPrice = this.montoNeto + this.montoDespacho + this.montoIva;

            console.log('Total price', this.totalPrice);

            this.$emit('update-products', this.productoSend);
            this.$emit('update-total',(this.totalPrice));
            this.$emit('update-subtotal', (this.montoNeto));
            this.$emit('update-monto-iva', (this.montoIva));
            this.$emit('update-monto-despacho',(this.montoDespacho));
            this.$emit('update-montoOpcionales',(this.montoOpcionales));

            this.productoSend = [];
            this.productosPedido = [];
            this.montoOpcionales = 0;
            this.montoDespacho = 0;
            this.montoIva = 0;
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
        }
    },
    computed: {
        isDespachoGratis: { get() { return ConfigHelper.ConfStr('modulos.pedidos.ajustes.despacho_gratis'); } }
    },
}
</script>

<style scoped media="screen">
.table td,
.table th {
    padding: 0.5rem;
}

.btn-m {
    margin: 0rem;
    padding: 0.3rem 0.5rem;
    font-size: 0.8rem;
}

.conteoVasos {
    padding: 10px;
    /* font-weight: bold; */
    font-size: 14px;
    border-radius: 5px;
    box-shadow: 0px 2px 4px rgb(0 0 0 / 20%), 0px 4px 8px rgb(0 0 0 / 10%);
    background-color: #343a40;
    color: white;
}

table thead th {
    vertical-align: bottom !important;
    border-bottom: none !important;
}

.btn-outline-info {
    padding-top: 0.3rem !important;
    padding-bottom: 0.3rem !important;
}

.fieldEdit {
    width: 80px;
}
</style>