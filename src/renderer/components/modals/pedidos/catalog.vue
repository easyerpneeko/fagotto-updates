<template>
  <div class="modal fade" id="modalCatalog" tabindex="-1" role="dialog" aria-labelledby="modalCatalog" aria-hidden="true"
    data-backdrop="false">
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
            <div class="col-md-6">
              <h5 class="modal-title">Elegir productos</h5>
              <hr>
              <div class="row d-flex">
                <div class="col-md-12 mb-3">
                  <div class="row d-flex justify-content-start">
                    <label class="pr-2 pl-3 d-flex align-items-center"> quantity de Vasos: </label>
                    <div class="btn-group btn-group-toggle d-flex align-items-center" role="group" data-toggle="buttons">
                      <label class="btn btn-primary active">
                        <input type="radio" name="options" id="option1" checked @click="calcularCantidades(1)">
                        320
                      </label>
                      <label class="btn btn-primary">
                        <input type="radio" name="options" id="option2" @click="calcularCantidades(2)">
                        160
                      </label>
                    </div>
                  </div>

                </div>

                <div class="col-md-12">
                  <table class="table table-borderless">
                    <thead>
                      <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">quantity</th>
                        <!-- <th scope="col">Vasos</th> -->
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(producto, id) in productosFijos" :key="id">
                        <td>{{ producto.name }}</td>
                        <td v-if="producto.name == 'Queso' || producto.name == 'Harina'">{{ producto.price }}G</td>
                        <td v-else>{{ producto.price }}</td>
                        <!-- <td>{{ producto.vasos }}</td> -->
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="col-md-12">
                  <table class="table table-borderless">
                    <thead>
                      <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">quantity</th>
                        <th scope="col">Vasos</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(salsa, id) in salsasDisponibles" :key="id">
                        <td>
                          <button type="button" @click="addSalsa(salsa)" class="btn btn-m btn-outline-info">
                            {{ salsa.name }} <i class="fa fa-plus"></i>
                          </button>
                        </td>
                        <td>{{ salsa.quantity }}kg</td>
                        <td>{{ salsa.vasos }}</td>
                      </tr>
                    </tbody>
                  </table>

                </div>
              </div>
            </div>

            <div class="col-md-6">
              <h5 class="modal-title">Detallado del pedido</h5>
              <hr>
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">quantity</th>

                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(producto, id) in productosPedido" :key="id">
                    <td>{{ producto.name }}</td>
                    <td v-if="(producto.name == 'Queso' || producto.name == 'Harina')">{{ producto.quantity }}kg</td>
                    <td v-else>{{ producto.quantity }}</td>
                  </tr>
                </tbody>
              </table>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th scope="col">Salsa</th>
                    <th scope="col">quantity</th>
                    <th scope="col">Vasos</th>
                    <th scope="col">Eliminar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(salsa, index) in salsasPedido" :key="index">
                    <td>{{ salsa.name }}</td>
                    <td>{{ salsa.quantity }} kg</td>
                    <td>{{ salsa.vasos }}</td>
                    <td>
                      <button class="btn btn-danger btn-m" @click="removeSalsa(index)">
                        <i class="fa fa-times-circle"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="modal-footer justify-content-between ">
          <div class="conteoVasos">quantity de Vasos {{ this.vasos }} - {{ this.vasosSalsas }} = {{ this.totalVasos }}
          </div>
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
// import cardProductOrders from '@/components/cards/card_product_orders.vue';
// import ticket from '@/components/modals/cafeteria/createTicket.vue';
// import modalVerify from '@/components/modals/verifyDelete.vue';

// Helpers y plugins
// import ConfigHelper from '@/helpers/ConfigHelper.js';
// import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';

export default {
  products: {
    type: Array,
    required: true
  },
  data() {
    return {
      vasos: 320,
      queso: 8,
      harina: 44,
      huevo: 360,

      kiloAgg: false,
      //Para saber si el pedido es completo o medio 1= completo, 2 = medio
      tipoPedido: 1,
      vasosSalsas: 0,
      totalVasos: 0,

      productosPedido: {
        1: { name: 'Vasos', quantity: 320, vasos: 1 },
        2: { name: 'Huevos', quantity:360,vasos: 1 },
        3: { name: 'Harina', quantity:44, vasos: 1 },
        4: { name: 'Queso', quantity: 8,   vasos:1},
      },
      salsasPedido: [],

      productosFijos: {
        // //Producto | quantity | Vasos
        // 1: { name: 'Huevos', quantity: 1, vasos: 1 },
        // 2: { name: 'Harina', quantity: 122.222222, vasos: 1 },
        // 3: { name: 'Queso', quantity: 22.222222, vasos: 1 },
      },

      salsasDisponibles: {
        1: { name: 'Alfredo', quantity: 5, vasos: 70 },
        2: { name: 'boloñesa', quantity: 5, vasos: 70 },
        3: { name: 'camaron', quantity: 5, vasos: 50 },
        4: { name: 'champiñon', quantity: 5, vasos: 50 },
        5: { name: 'pesto', quantity: 5, vasos: 70 }
      },
      vasosSalsas: 0,
      kiloSalsas: 0,
      productoSend: []
    }
  },
  components: {

  },
  mounted() {
    this.getProducts();

  },
  methods: {
    closeModal(refresh = false) {
      //Volvemos los arreglos al estado inicial
      this.productosPedido = {
        1: { name: 'Vasos', quantity: 320 },
        2: { name: 'Huevos', quantity: 360 },
        3: { name: 'Harina', quantity: 44 },
        4: { name: 'Queso', quantity: 8 },
      },
        this.tipoPedido = 1,
        this.vasosSalsas = 0,
        this.totalVasos = 0,
        this.kiloSalsas = 0;
      this.salsasPedido = [],
        $('#modalCatalog').modal('hide');
    },
    addSalsa(salsa) {
      const existingSalsa = this.salsasPedido.find((s) => s.name === salsa.name);
      if (existingSalsa) {
        existingSalsa.quantity *= 2;
        existingSalsa.vasos *= 2;
        console.log(`Salsa ${salsa.name} ya existente. Se ha duplicado la quantity.`);
      } else {
        const salsaCopia = Object.assign({}, salsa); // O también puedes usar: const salsaCopia = { ...salsa };
        this.salsasPedido.push(salsaCopia);
        console.log(this.salsasPedido);
      }
      this.calcularVasosSalsas()
    },
    removeSalsa(index) {
      if (index >= 0 && index < this.salsasPedido.length) {
        this.salsasPedido.splice(index, 1);
      }
      this.calcularVasosSalsas()
    },
    calcularCantidades(opcion) {
      if (!(this.tipoPedido === opcion)) {
        if (this.tipoPedido === 2 && opcion === 1) {
          this.vasos = 360;
          for (const key in this.productosPedido) {
            this.productosPedido[key].quantity *= 2;
          }
          this.tipoPedido = opcion;
        } else if (this.tipoPedido === 1 && opcion === 2) {
          this.vasos = 180;
          for (const key in this.productosPedido) {
            this.productosPedido[key].quantity /= 2;
          }
          this.tipoPedido = opcion;
          console.log("Tipo de pedido actualizado a 2");
        }
      }

    },
    calcularVasosSalsas() {

      //Contamos los vasos 
      this.vasosSalsas = 0;
      this.kiloSalsas = 0;

      this.salsasPedido.forEach((salsa) => {
        this.vasosSalsas += salsa.vasos;
        //Contamos los kilos de salsa
        this.kiloSalsas += salsa.quantity;
      });
      console.log(this.kiloSalsas);
      if (this.kiloSalsas > 25 && !this.kiloAgg) {
        this.kiloAgg = true;
        //vasos
        this.productosPedido[1].quantity += 160;
        //huevos
        this.productosPedido[2].quantity += 180;

      } else if (this.kiloSalsas < 25 && this.kiloAgg) {
        this.kiloAgg = false;
        //vasos
        this.productosPedido[1].quantity -= 160;
        //huevos
        this.productosPedido[2].quantity -= 180;
      }

      this.totalVasos = this.vasos - this.vasosSalsas;

      const rangoInferior = this.vasos - 50;
      const rangoSuperior = this.vasos + 50;

      if (this.totalVasos >= rangoInferior && this.totalVasos <= rangoSuperior) {
        console.log("El valor de totalVasos está dentro del intervalo de ±50 unidades con respecto a this.vasos.");
      } else {
        console.log("El valor de totalVasos está fuera del intervalo de ±50 unidades con respecto a this.vasos.");
      }
    },
    addProducts() {
      console.log('Productos pedido: ', this.productosPedido);
      console.log('Salsas: ', this.salsasPedido);

      for (var key in this.productosPedido) {
        this.salsasPedido.push(this.productosPedido[key]);
      }
      this.productoSend = this.salsasPedido
      // console.log('Pedido: ', this.salsasPedido);

      if (this.productoSend.length == 0) {
        this.$awn.alert("Es necesario agregar algun producto");
        return false;
      }
      this.$emit('update-products', this.productoSend);
      $('#modalCatalog').modal('hide');
    },
    async getProducts() {
      // Iniciando peticion
      // Loader.dinamic();
      var request = await this.$store.dispatch("products/getProductsOfIndex");
      // Loader.hide();
      // Verificando respuesta
      if (request.success) {
        this.productosFijos = request.data;
      }
      else this.$awn.alert('Error al obtener los productos');

    },
  },
  computed: {

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
  font-weight: bold;
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
</style>
