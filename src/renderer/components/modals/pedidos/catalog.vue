<template>
  <div class="modal fade" id="modalCatalog" tabindex="-1" role="dialog" aria-labelledby="modalCatalog"
    aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog lg-modal modal-dialog-centered" role="document" style="max-width: 1000px; margin: auto;">
      <div class="modal-content">
        <div class="modal-header bg-gradient">
          <h5 class="modal-title fw-bold">🛒 Preparar pedido</h5>
          <button type="button" class="close text-white" @click="closeModal(false)" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0" style="overflow: auto; max-height: 70vh;">
          <div class="row g-3 p-3">
            <div class="col-md-6 p-3">
              <div class="row d-flex">
                <div class="col-md-12 mb-3">
                  <h5 class="modal-title">📋 Información de productos</h5>
                  <hr class="mt-2">
                </div>

                <div class="col-md-12 mb-3">
                  <table class="table table-sm table-striped table-hover text-nowrap">
                    <thead class="table-dark">
                      <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(producto, id) in productosFijos" :key="id">
                        <template
                          v-if="producto.name == 'Queso' || producto.name == 'Harina' || producto.name == 'Vaso' || producto.name == 'Botella de Huevos 1L'">

                          <td>{{ producto.name }}</td>

                          <td v-if="producto.name == 'Queso'">
                            {{ formatearMonto(producto.price) }}gr
                          </td>

                          <td v-else-if="producto.name == 'Botella de Huevos 1L' || producto.name == 'Harina'">
                            {{ formatearMonto(producto.price) }} Vasos
                            <i class="fas fa-info-circle ms-1" data-toggle="tooltip" data-placement="top"
                              :title="`Esto equivale a ${formatearMonto(producto.price)} Vasos`"></i>
                          </td>

                          <td v-else>{{ formatearMonto(producto.price) }}</td>
                        </template>
                      </tr>
                    </tbody>
                  </table>
                </div>
                
                <div class="col-md-12 mb-3">
                  <!-- Mensaje informativo de precios -->
                  <div class="alert alert-info mb-3" style="background-color: #fff3cd; border-color: #ffeaa7; color: #856404;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>💰 Información de precios:</strong><br>
                    <span style="color: #d63384; font-weight: bold;">• ALFREDO y BOLOÑESA: $1.508 por vaso</span><br>
                    <span style="color: #198754; font-weight: bold;">• Otras salsas: $1.875 por vaso</span>
                  </div>
                  
                  <h6 class="text-muted mb-2 fw-bold">🌶️ Salsas disponibles</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <button v-for="(salsa, id) in salsasDisponibles" :key="id" type="button" @click="addSalsa(salsa)"
                      class="btn btn-outline-primary btn-sm rounded-pill product-btn"
                      :disabled="salsa.stock < salsa.min_stock"
                      title="Click para añadir al pedido">
                      🛒 {{ salsa.name }} <i class="fa fa-plus ms-1"></i>
                    </button>
                  </div>
                </div>
                
                <div class="col-md-12 mb-3">
                  <h6 class="text-muted mb-2 fw-bold">🍽️ Productos opcionales</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <button v-for="(producto, id) in opcionalesDisponibles" :key="id" type="button"
                      @click="addOpcional(producto)"
                      class="btn btn-outline-success btn-sm rounded-pill product-btn"
                      title="Click para añadir al pedido">
                      ➕ {{ producto.name }} <i class="fa fa-plus ms-1"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6 p-3">
              <h5 class="modal-title mb-3 fw-bold">📋 Detalle del pedido</h5>
              
              <table class="table table-sm table-striped table-hover">
                <thead class="table-dark">
                  <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Cantidad</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(producto, id) in productosPedido" :key="id">
                    <template v-if="producto.name == 'Botella de Huevos 1L'">
                      <td class="fw-bold">{{ producto.name }}</td>
                      <td class="fw-bold">{{ producto.quantity }} botellas</td>
                      <td class="d-none">${{ producto.costo = formatearMonto(producto.quantity * producto.compra) }}
                      </td>
                    </template>
                    <template v-if="producto.name == 'Queso' || producto.name == 'Harina'">
                      <td class="fw-bold">{{ producto.name }}</td>
                      <td class="fw-bold">{{ producto.name == 'Queso' ? producto.quantity + 'kg' : producto.quantity + ' unidades' }}</td>
                      <td class="d-none">${{ producto.costo = formatearMonto(producto.quantity * producto.compra) }}
                      </td>
                    </template>
                    <template v-if="producto.name == 'Vaso'">
                      <td class="bg-primary text-white fw-bold">{{ producto.name }}</td>
                      <td class="bg-primary text-white fw-bold">{{ producto.quantity }} unidades</td>
                      <td class="bg-primary d-none">${{ producto.costo = formatearMonto(producto.quantity *
                        producto.price )}}</td>
                    </template>
                  </tr>
                </tbody>
              </table>

              <!-- Tabla Salsas -->
              <div v-if="salsasPedido.length > 0" class="mb-3">
                <h6 class="text-muted mb-2 fw-bold">🌶️ Salsas seleccionadas</h6>
                <table class="table table-sm table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th scope="col">Salsa</th>
                      <th scope="col">Cantidad</th>
                      <th scope="col">Vasos</th>
                      <th scope="col">Precio</th>
                      <th scope="col" class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(salsa, index) in salsasPedido" :key="index">
                      <td>{{ salsa.name }}</td>
                      <td>{{ salsa.quantity }}kg</td>
                      <td>
                        <input @input="validateInput()" :min="salsa.min_quantity" @change="calcularVasosSalsas()"
                          :step="salsa.min_quantity" class="form-control form-control-sm" type="number" v-model="salsa.vasos" />
                      </td>
                      <td class="fw-bold text-success">${{ formatearMonto(salsa.costo = salsa.vasos * getPrecioSalsa(salsa)) }}</td>
                      <td class="text-center">
                        <button class="btn btn-danger btn-sm rounded-pill" @click="removeSalsa(index)" title="Eliminar salsa">
                          <i class="fa fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Tabla opcionales -->
              <div v-if="opcionalPedido.length > 0" class="mb-3">
                <h6 class="text-muted mb-2 fw-bold">🍽️ Productos opcionales</h6>
                <table class="table table-sm table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th scope="col">Producto</th>
                      <th scope="col">Cantidad</th>
                      <th scope="col">Precio</th>
                      <th scope="col" class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(producto, index) in opcionalPedido" :key="index">
                      <td>{{ producto.name }}</td>
                      <td>
                        <input min="1" @change="calcularPrecioOpcionales()" class="form-control form-control-sm" type="number"
                          v-model="producto.quantity" />
                      </td>
                      <td class="fw-bold text-success">${{ formatearMonto(producto.costo = producto.price * 1) }}</td>
                      <td class="text-center">
                        <button class="btn btn-danger btn-sm rounded-pill" @click="removeOpcional(index)" title="Eliminar producto">
                          <i class="fa fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <!-- Resumen de montos en card -->
              <div class="card shadow-sm">
                <div class="card-body p-3">
                  <h6 class="card-title text-muted mb-2 fw-bold">💰 Resumen de costos</h6>
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
                        <td class="text-end fw-bold">${{ formatearMonto(this.montoNeto = this.calcularMontoNetoReal()) }}</td>
                      </tr>
                      <tr>
                        <td>Despacho {{ this.despacho * 100 }}%</td>
                        <td class="text-end">${{ formatearMonto(this.montoDespacho = this.montoNeto * this.despacho) }}</td>
                      </tr>
                      <tr>
                        <td>IVA {{ this.iva * 100 }}%</td>
                        <td class="text-end">${{ formatearMonto(this.montoIva = (this.montoNeto * this.iva)) }}</td>
                      </tr>
                      <tr class="table-success">
                        <td class="fw-bold">Total</td>
                        <td class="text-end fw-bold fs-5">${{ formatearMonto(this.montoNeto + this.montoDespacho + this.montoIva) }}</td>
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
            <button type="button" @click="addProducts()" class="btn btn-success btn-lg" title="Confirmar y agregar productos al pedido">
              <i class="fa fa-check-circle me-2"></i>Agregar productos
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>

// Helpers y plugins
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
// import Loader from '@/helpers/Loader';
import $ from 'jquery';

export default {
  products: {
    type: Array,
    required: true
  },
  data() {
    return {
      vasosMinimos: 0,
      vasos: 0,
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
        // 1: { name: 'Vasos', quantity: 162, vasos: 1 },
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
      montoIva: 0,
      montoDespacho: 0,
      despacho: 0,
      iva: 0.19,
      montoTotal: 0,
      montoOpcionales: 0,
      isMultiplo: true,

    }
  },
  components: {

  },
  async mounted() {
    //Trae los productos fijo de db 
    await this.cargarProductosFijos();
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
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
      this.opcionalPedido = [];
      this.totalPrice = 0;
      this.vasos = 0;
      this.montoIva = 0;
      this.montoDespacho = 0;
      this.montoNeto = 0;
      this.montoTotal = 0;
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
        salsaCopia.vasos = salsaCopia.min_quantity; // O también puedes usar: const salsaCopia = { ...salsa };
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
    calcularCantidades() {

      for (const key in this.productosPedido) {
        if (this.productosPedido[key].name == "Vaso") {
          //Para mantener el minimo de vasos
          if (this.vasosSalsas > this.vasosMinimos) {
            this.vasos = this.vasosSalsas;
            this.productosPedido[key].quantity = this.vasos;
          } else if (this.vasosSalsas <= this.vasosMinimos) {
            this.vasos = this.vasosMinimos;
            this.productosPedido[key].quantity = this.vasos;
          }
        }
        else {

          if (this.productosPedido[key].name == "Queso") {
            this.productosPedido[key].quantity = (this.productosPedido[key].price * this.vasos) / 1000;

          }
          if (this.productosPedido[key].name == "Harina") {
            this.productosPedido[key].quantity = Math.ceil((this.vasos / this.productosPedido[key].price));
          }
          if (this.productosPedido[key].name == "Botella de Huevos 1L") {
            this.productosPedido[key].quantity = (this.vasos / this.productosPedido[key].price);

            // Para saber si el pedido es multiplo
            if ((this.vasos % this.productosPedido[key].price) != 0) {
              this.isMultiplo = false;
              this.$awn.info("El pedido debe ser de minimo " + this.vasosMinimos + " vasos y multiplo de " + this.productosPedido[key].price);
            } else {
              this.isMultiplo = true;
            }
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
        this.validateInput(salsa);
        salsa.quantity = ((salsa.vasos * salsaDisponible.quantity) / 1000).toFixed(2);
        // Calcular costo con precio específico por salsa
        salsa.costo = salsa.vasos * this.getPrecioSalsa(salsa);
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

      console.log('multiplo:', this.isMultiplo);
      console.log('vasos:', this.vasosSalsas);

      if (this.vasosSalsas < this.vasosMinimos) {
        this.$awn.info("El pedido debe ser de minimo " + this.vasosMinimos + " vasos de salsa");
        return false;
      }

      if (!this.isMultiplo) {
        this.$awn.info("El pedido debe ser multiplo de la cantidad de vasos de la Botella de Huevos");
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

      //El monto neto ya incluye el precio de los opcionales

      this.totalPrice = Math.ceil(this.montoNeto + this.montoDespacho + this.montoIva);

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
      this.opcionalPedido = [];
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

        if (this.productosFijos[producto].name == 'Queso') {
          // const nuevoProductoPedido = {
          // name: this.productosFijos[producto].name,
          // quantity: this.convertirAKilogramosYRedondear(this.productosFijos[producto].price, this.vasos),
          // vasos: 1,
          // price: this.productosFijos[producto].price
          // };

          this.productosFijos[producto].quantity = this.convertirAKilogramosYRedondear(this.productosFijos[producto].price, this.vasos);
          this.productosFijos[producto].vasos = 1;

          this.$set(this.productosPedido, producto, this.productosFijos[producto]);

        } else if (this.productosFijos[producto].name === 'Botella de Huevos 1L' || this.productosFijos[producto].name === 'Harina') {
          // const nuevoProductoPedido = {
          //   name: this.productosFijos[producto].name,
          //   quantity: (this.vasos / this.productosFijos[producto].price),
          //   vasos: 1,
          //   price: this.productosFijos[producto].price
          // };
          this.productosFijos[producto].quantity = (this.vasos / this.productosFijos[producto].price);
          this.productosFijos[producto].vasos = 1;

          this.$set(this.productosPedido, producto, this.productosFijos[producto]);

        } else if (this.productosFijos[producto].name === 'Vaso') {
          // const nuevoProductoPedido = {
          //   name: this.productosFijos[producto].name,
          //   quantity: this.vasos,
          //   vasos: 1,
          //   price: this.productosFijos[producto].price
          // };

          this.productosFijos[producto].quantity = this.vasos;
          this.productosFijos[producto].vasos = 1;

          this.$set(this.productosPedido, producto, this.productosFijos[producto]);
          this.vasosMinimos = Math.ceil(this.productosFijos[producto].min_quantity);
          //para obtener el precio del vaso - CORREGIDO: usar precio fijo 1875 para salsas normales
          this.precioVaso = 1875; // Precio fijo para salsas normales (no ALFREDO/BOLOÑESA)

        } else if (this.productosFijos[producto].category === 2) {
          // const salsaDisponible = {
          //   name: this.productosFijos[producto].name,
          //   quantity: this.productosFijos[producto].price,
          //   vasos: 1,
          //   price: this.productosFijos[producto].price,
          //   min_quantity: this.productosFijos[producto].min_quantity,
          // };

          this.productosFijos[producto].vasos = 1;
          this.productosFijos[producto].quantity = this.productosFijos[producto].price;

          this.$set(this.salsasDisponibles, producto, this.productosFijos[producto]);

          this.productosFijos[producto] = []; // Opcional: Eliminar el elemento de productosFijos

        } else if (this.productosFijos[producto].name === 'Despacho') {
          //Obteniendo el precio de despacho
          if (this.isDespachoGratis) {
            this.despacho = 0;
          } else {
            this.despacho = this.productosFijos[producto].price;
          }

        } else if (this.productosFijos[producto].category === 3) {
          // const opcionalDisponible = {
          //   name: this.productosFijos[producto].name,
          //   quantity: 1,
          //   vasos: 1,
          //   price: this.productosFijos[producto].price
          // };
          this.productosFijos[producto].quantity = 1;
          this.productosFijos[producto].vasos = 1;

          this.$set(this.opcionalesDisponibles, producto, this.productosFijos[producto]);

          this.productosFijos[producto] = []; // Opcional: Eliminar el elemento de productosFijos
        }
        // console.log('salsas : ', this.salsasDisponibles);
      }
    },
    convertirAKilogramosYRedondear(gramos, multiplicador) {
      const kilogramos = (gramos * multiplicador) / 1000;
      return Math.ceil(kilogramos * 1000) / 1000;
    },
    validateInput(salsa) {
      // Redondea al múltiplo más cercano
      salsa.vasos = Math.round(salsa.vasos / salsa.min_quantity) * salsa.min_quantity;
    },
    getPrecioSalsa(salsa) {
      // Precios específicos para ALFREDO y BOLOÑESA
      if (salsa.name === 'ALFREDO' || salsa.name === 'BOLOÑESA') {
        return 1508;
      }
      // Precio normal para las demás salsas
      return this.precioVaso;
    },
    calcularMontoNetoReal() {
      // Calcular el monto neto sumando el costo real de cada salsa
      let montoSalsas = 0;
      this.salsasPedido.forEach((salsa) => {
        montoSalsas += salsa.vasos * this.getPrecioSalsa(salsa);
      });
      
      // Agregar el costo de los productos opcionales
      return montoSalsas + this.montoOpcionales;
    }
  },
  computed: {
    isDespachoGratis: { get() { return ConfigHelper.ConfStr('modulos.pedidos.ajustes.despacho_gratis'); } }
  },
}
</script>

<style scoped media="screen">
/* Estilos personalizados para el modal de catálogo */
.bg-gradient {
  background: linear-gradient(to right, #2196f3, #21cbf3);
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

.btn-outline-success.product-btn:hover {
  background-color: #e8f5e8 !important;
  border-color: #28a745 !important;
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
  box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
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
</style>
