<template>
  <div class="modal fade" id="modalCatalog" tabindex="-1" role="dialog" aria-labelledby="modalCatalog"
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
            <div class="col-md-6">
              <!-- <h5 class="modal-title">Elegir productos</h5> -->
              <!-- <hr> -->
              <div class="row d-flex">
                <div class="col-md-12 mb-3">
                  <div class="row d-flex justify-content-start">
                    <h5 class="modal-title p-2 m-1">Detallado del pedido</h5>
                    <hr>
                    <!-- <label class="pr-2 pl-3 d-flex align-items-center">Productos disponibles: </label> -->
                    <!-- <div class="btn-group btn-group-toggle d-flex align-items-center" role="group"
                      data-toggle="buttons">
                      <label class="btn btn-primary ">
                        <input type="radio" name="options" id="option1" v-bind:checked="opcionSeleccionada"
                          @click="calcularCantidades(1)">
                        320
                      </label>
                      <label class="btn btn-primary active">
                        <input type="radio" name="options" id="option2" v-bind:checked="opcionSeleccionada"
                          @click="calcularCantidades(2)">
                        160
                      </label>
                    </div> -->
                  </div>

                </div>

                <div class="col-md-12 p-2 m-1">
                  <table class="table table-borderless">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad</th>
                        <!-- <th scope="col">Vasos</th> -->
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(producto, id) in productosFijos" :key="id">
                        <template
                          v-if="producto.name == 'Queso' || producto.name == 'Harina' || producto.name == 'Vaso' || producto.name == 'Huevo'">
                          <td>{{ producto.name }}</td>
                          <td v-if="producto.name == 'Queso' || producto.name == 'Harina'">{{
            formatearMonto(producto.price) }}gr</td>
                          <td v-else>{{ formatearMonto(producto.price) }}</td>
                        </template>
                        <!-- <td>{{ producto.vasos }}</td> -->
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="col-md-12 p-2 m-1">
                  <div class="row">
                    <span class="m-0 p-0">
                      <button v-for="(salsa, id) in salsasDisponibles" :key="id" type="button" @click="addSalsa(salsa)"
                        class="m-1 btn-width btn bg-primario text-white text-capitalize col-md-3">
                        {{ salsa.name }} <i class="fa fa-plus"></i>
                      </button>
                    </span>

                  </div>
                </div>
                <div class="col-md-12 p-2 m-1">
                  <div class="row">
                    <span class="m-0 p-0">
                      <button v-for="(producto, id) in opcionalesDisponibles" :key="id" type="button"
                        @click="addOpcional(producto)"
                        class="m-1 btn-width btn bg-primario text-white text-capitalize col-md-4">
                        {{ producto.name }} <i class="fa fa-plus"></i>
                      </button>
                    </span>

                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <h5 class="modal-title p-2 m-1">Detallado del pedido</h5>
              <hr>
              <table class="table">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Cantidad</th>

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

              <!-- Tabla Salsas -->
              <table class="table table-bordered" v-if="salsasPedido.length > 0">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Salsa</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Vasos</th>
                    <th scope="col">Eliminar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(salsa, index) in salsasPedido" :key="index">
                    <td>{{ salsa.name }}</td>
                    <td>{{ salsa.quantity }}kg</td>
                    <td>
                      <input min="10" @change="calcularVasosSalsas()" class="fieldEdit" type="number"
                        v-model="salsa.vasos" />
                    </td>
                    <!-- <td>{{ salsa.vasos }}</td> -->
                    <td>
                      <button class="btn btn-danger btn-m" @click="removeSalsa(index)">
                        <i class="fa fa-times-circle"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Tabla opcionale -->
              <table class="table table-bordered" v-if="opcionalPedido.length > 0">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Precio</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Eliminar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(producto, index) in opcionalPedido" :key="index">
                    <td>{{ producto.name }}</td>
                    <td>{{ formatearMonto(producto.price) }}</td>
                    <td>
                      <input min="1" @change="calcularPrecioOpcionales()" class="fieldEdit" type="number"
                        v-model="producto.quantity" />
                    </td>
                    <!-- <td>{{ salsa.vasos }}</td> -->
                    <td>
                      <button class="btn btn-danger btn-m" @click="removeOpcional(index)">
                        <i class="fa fa-times-circle"></i>
                      </button>
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
                    <td>${{ formatearMonto(this.montoNeto = (this.vasos * this.precioVaso) + (this.montoOpcionales)) }}</td>
                  </tr>
                  <tr>
                    <td>Despacho {{ this.despacho * 100 }}%</td>
                    <td>${{ formatearMonto(this.montoDespacho = (this.vasos * this.precioVaso) * this.despacho) }}</td>
                  </tr>
                  <tr>
                    <td>IVA 19%</td>
                    <td>${{ formatearMonto(this.montoIva = (this.montoNeto * this.iva)) }}</td>
                  </tr>
                  <tr>
                    <td>Total</td>
                    <td>${{ formatearMonto(
            this.vasos * this.precioVaso
            + ((this.vasos * this.precioVaso) * this.iva)
            + ((this.vasos * this.precioVaso) * this.despacho)
            + (this.montoOpcionales)
          ) }}</td>
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
// import cardProductOrders from '@/components/cards/card_product_orders.vue';
// import ticket from '@/components/modals/cafeteria/createTicket.vue';
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
      vasos: 160,
      // queso: 4,
      // harina: 22,
      // huevo: 180,
      precioVaso: 0,
      kiloAgg: false,
      //Para saber si el pedido es completo o medio 1= completo, 2 = medio
      tipoPedido: 2,
      vasosSalsas: 0,
      totalVasos: 0,
      totalPrice: 0,
      productosPedido: {
        // 1: { name: 'Vasos', quantity: 160, vasos: 1 },
      },
      salsasPedido: [],
      opcionalPedido: [],
      productosFijos: {
        // //Producto | quantity | Vasos
        // 1: { name: 'Huevos', quantity: 1, vasos: 1 },
      },

      salsasDisponibles: {
        // 1: { name: 'Alfredo', quantity: 70, vasos: 1 },
      },
      opcionalesDisponibles: {

      },
      vasosSalsas: 0,
      kiloSalsas: 0,
      productoSend: [],
      montoNeto: 0,
      montoIva:0,
      montoDespacho:0,
      despacho: 0,
      iva: 0.19,
      montoTotal: 0,
      montoOpcionales: 0

    }
  },
  components: {

  },
  async mounted() {
    //Trae los productos fijo de db 
    await this.cargarProductosFijos();

  },
  methods: {
    async closeModal(refresh = false) {
      //Volvemos los arreglos al estado inicial
      await this.cargarProductosFijos();
      this.tipoPedido = 1;
      this.vasosSalsas = 0;
      this.totalVasos = 0;
      this.kiloSalsas = 0;
      this.salsasPedido = [];
      this.opcionalPedidoPedido = [];
      this.totalPrice = 0;
      this.vasos = 0;
      $('#modalCatalog').modal('hide');
    },
    addSalsa(salsa) {
      const existingSalsa = this.salsasPedido.find((s) => s.name === salsa.name);
      if (existingSalsa) {
        // existingSalsa.quantity += 100;
        // existingSalsa.vasos += 1;
        console.log(`Salsa ${salsa.name} ya existente.`);
      } else {
        const salsaCopia = Object.assign({}, salsa);
        salsaCopia.vasos = 10; // O también puedes usar: const salsaCopia = { ...salsa };
        this.salsasPedido.push(salsaCopia);
        // console.log(this.salsasPedido);
      }
      this.calcularVasosSalsas()
    },
    removeSalsa(index) {
      if (index >= 0 && index < this.salsasPedido.length) {
        this.salsasPedido.splice(index, 1);
      }
      this.calcularVasosSalsas()
    },
    removeOpcional(index) {
      if (index >= 0 && index < this.opcionalPedido.length) {
        this.opcionalPedido.splice(index, 1);
      }
      this.calcularPrecioOpcionales();
    },
    addOpcional(opcional) {
      const existingOpcional = this.opcionalPedido.find((o) => o.name === opcional.name);
      if (existingOpcional) {
        // existingSalsa.quantity += 100;
        // existingSalsa.vasos += 1;
        console.log(`Opcional ${opcional.name} ya existente.`);
      } else {
        const opcionalCopia = Object.assign({}, opcional);
        opcionalCopia.vasos = 1; // O también puedes usar: const opcionalCopia = { ...salsa };
        this.opcionalPedido.push(opcionalCopia);
        // console.log(this.opcionalPedido);
      }
      this.calcularPrecioOpcionales();
    },
    removeSalsa(index) {
      if (index >= 0 && index < this.salsasPedido.length) {
        this.salsasPedido.splice(index, 1);
      }
      this.calcularVasosSalsas();
    },
    calcularCantidades() {

      if (this.vasosSalsas > 160) {
        this.vasos = this.vasosSalsas;
        this.productosPedido[1].quantity = this.vasos;
      } else if (this.vasosSalsas <= 160) {
        this.vasos = 160;
        this.productosPedido[1].quantity = this.vasos;
      }

      for (const key in this.productosPedido) {
        if (this.productosPedido[key].name != "Vaso") {
          if (this.productosPedido[key].name != "Huevo") {
            this.productosPedido[key].quantity = (this.productosPedido[key].price * this.vasos) / 1000;
          }else if(this.productosPedido[key].name == "Huevo"){
            this.productosPedido[key].quantity = Math.ceil((this.productosPedido[key].price * this.vasos));
          }
        }

      }


    },
    formatNumber(number) {
      return FormatNumber.format(number);
    },
    calcularVasosSalsas() {

      //Contamos los vasos 
      this.vasosSalsas = 0;
      this.kiloSalsas = 0;

      this.salsasPedido.forEach((salsa) => {
        //Contamos los vasos de salsa
        this.vasosSalsas += +salsa.vasos;
        let salsaDisponible;
        for (var key in this.salsasDisponibles) {
          if (this.salsasDisponibles[key].name == salsa.name) {
            salsaDisponible = this.salsasDisponibles[key];
          }
        }

        salsa.quantity = (salsa.vasos * salsaDisponible.quantity) / 1000;
      });

      this.calcularCantidades();
    },
    calcularPrecioOpcionales() {
      this.opcionalPedido.forEach((producto) => {
        //Contamos los vasos de salsa
        // this.vasosSalsas += +salsa.vasos;
        let productoDisponible;
        for (var key in this.opcionalesDisponibles) {
          if (this.opcionalesDisponibles[key].name == producto.name) {
            productoDisponible = this.opcionalesDisponibles[key];
          }
        }

        producto.price = (producto.quantity * productoDisponible.price);
        this.montoOpcionales = 0;
        for (var key in this.opcionalPedido) {
          this.montoOpcionales += this.opcionalPedido[key].price;
        }

      });
    },
    addProducts() {
      console.log('Productos pedido: ', this.productosPedido);
      console.log('Salsas: ', this.salsasPedido);
      console.log('kilos Salsas:', this.kiloSalsas);
      console.log('Opcionales:', this.opcionalPedido);

      if (this.vasosSalsas < 160) {
        this.$awn.info("El pedido debe ser de minimo 160 vasos");
        return false;
      }

      for (var key in this.productosPedido) {
        this.salsasPedido.push(this.productosPedido[key]);
      }
      for (var key in this.opcionalPedido) {
        this.salsasPedido.push(this.opcionalPedido[key]);
      }
      this.productoSend = this.salsasPedido
      // console.log('Pedido: ', this.salsasPedido);

      if (this.productoSend.length == 0) {
        this.$awn.alert("Es necesario agregar algun producto");
        return false;
      }

      this.totalPrice = this.vasos * this.precioVaso
        + ((this.vasos * this.precioVaso) * this.iva)
        + ((this.vasos * this.precioVaso) * this.despacho)
        + (this.montoOpcionales);

      this.$emit('update-products', this.productoSend);
      this.$emit('update-total', Math.ceil((this.totalPrice)));

      this.$emit('update-subtotal', Math.ceil((this.montoNeto)));
      this.$emit('update-monto-iva', Math.ceil((this.montoIva)));
      this.$emit('update-monto-despacho', Math.ceil((this.montoDespacho)));

      this.$emit('update-montoOpcionales', Math.ceil((this.montoOpcionales)));

      $('#modalCatalog').modal('hide');

      this.productoSend = [];
      // this.productosPedido = {};
      this.salsasPedido = [];
      this.opcionalPedido;
      this.totalPrice = 0;
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
    async cargarProductosFijos() {

      await this.getProducts();
      for (const producto in this.productosFijos) {

        if (this.productosFijos[producto].name == 'Queso' || this.productosFijos[producto].name == 'Harina') {
          const nuevoProductoPedido = {
            name: this.productosFijos[producto].name,
            quantity: this.convertirAKilogramosYRedondear(this.productosFijos[producto].price, this.vasos),
            vasos: 1,
            price: this.productosFijos[producto].price
          };
          this.$set(this.productosPedido, producto, nuevoProductoPedido);

        } else if (this.productosFijos[producto].name === 'Huevo') {
          const nuevoProductoPedido = {
            name: this.productosFijos[producto].name,
            quantity: (this.productosFijos[producto].price * this.vasos),
            vasos: 1,
            price: this.productosFijos[producto].price
          };
          this.$set(this.productosPedido, producto, nuevoProductoPedido);

        } else if (this.productosFijos[producto].name === 'Vaso') {
          const nuevoProductoPedido = {
            name: this.productosFijos[producto].name,
            quantity: this.vasos,
            vasos: 1,
            price: this.productosFijos[producto].price
          };
          this.$set(this.productosPedido, producto, nuevoProductoPedido);

          //para obtener el precio del vaso
          this.precioVaso = this.productosFijos[producto].price;

        } else if (this.productosFijos[producto].category === 2) {
          const salsaDisponible = {
            name: this.productosFijos[producto].name,
            quantity: this.productosFijos[producto].price,
            vasos: 1,
            price: this.productosFijos[producto].price
          };

          this.$set(this.salsasDisponibles, producto, salsaDisponible);

          this.productosFijos[producto] = []; // Opcional: Eliminar el elemento de productosFijos

        } else if (this.productosFijos[producto].name === 'Despacho') {
          //Obteniendo el precio de despacho
          if(this.isDespachoGratis){
            this.despacho = 0;
          }else{
            this.despacho = this.productosFijos[producto].price;
          }
          
        } else if (this.productosFijos[producto].category === 3) {
          const opcionalDisponible = {
            name: this.productosFijos[producto].name,
            quantity: 1,
            vasos: 1,
            price: this.productosFijos[producto].price
          };

          this.$set(this.opcionalesDisponibles, producto, opcionalDisponible);

          this.productosFijos[producto] = []; // Opcional: Eliminar el elemento de productosFijos
        }
        // console.log('salsas : ', this.salsasDisponibles);
      }
    },
    convertirAKilogramosYRedondear(gramos, multiplicador) {
      const kilogramos = (gramos * multiplicador) / 1000;
      return Math.ceil(kilogramos * 1000) / 1000;
    }
  },
  computed: {
    isDespachoGratis: { get(){ return ConfigHelper.ConfStr('modulos.pedidos.ajustes.despacho_gratis'); }}
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
</style>
