<template>
  <div class="product-bg d-flex flex-column px-3">
    <div class="row">

      <div class="col-9 Jfondo-1">
        <div class="row">
          <!--==========================================================================================================================-->
          <!--(html)__( 'menu' )-->
          <!--==========================================================================================================================-->

          <div class="col-12">
            <hr>
          </div>
          <div class="col-12 p-0 space-between-search-and-create">

            <div class="input-group input-group-sm mb-3 ">
              <autocomplete v-if="this.settingEnterBuscar" @keyup.enter="submitAutocomplete(false)" :search="search"
                class="w-50" placeholder="Buscar" :getResultValue="getSearchValue" ref="productAutocomplete">
                <template #result="{ result, props }">
                  <div class="d-flex list-item" :class="result.class ? result.class : ''"
                    @click="submitAutocomplete(result)">
                    <div style="width: 50px;"><b><i class="fab fa-sistrix"></i></b></div>
                    <div style="width: 300px;"><b>{{ result.name }}</b></div>
                    <div v-if="stockInstalled" style="width: 100px;"><b>{{ result.stock }}</b></div>
                    <div style="width: 100px;"><b>{{ result.price != 'PRICE' ?
                formatNumber(deFormatNumber(result.price)) + '$' : 'PRICE' }} </b></div>
                  </div>
                </template>
              </autocomplete>

              <autocomplete v-else :search="search" class="w-50" placeholder="Buscar" :getResultValue="getSearchValue"
                @submit="submitAutocomplete" ref="productAutocomplete">
                <template #result="{ result, props }">
                  <div class="d-flex list-item" :class="result.class ? result.class : ''"
                    @click="submitAutocomplete(result)">
                    <div style="width: 50px;"><b><i class="fab fa-sistrix"></i></b></div>
                    <div style="width: 300px;"><b>{{ result.name }}</b></div>
                    <div v-if="stockInstalled" style="width: 100px;"><b>{{ result.stock }}</b></div>
                    <div style="width: 100px;"><b>{{ result.price != 'PRICE' ?
                formatNumber(deFormatNumber(result.price)) + '$' : 'PRICE' }} </b></div>
                  </div>
                </template>
              </autocomplete>
            </div>
          </div>

          <div v-if="products" class="" hidden="true">
            <div class="btnProduct d-flex w-100 flex-wrap"
              :class="(filteredList.length <= 3) ? '' : 'justify-content-around'">
              <div @click="AddProduct(product)" class="m-2" v-for="(product, index) in filteredList" :key="index"
                v-if="!cecinaInstalled || (cecinaInstalled && product.cecina)">
              </div>
            </div>
          </div>
          <div>
            <hr>
          </div>
          <!--==========================================================================================================================-->
          <!--(html)__( 'table' )-->
          <!--==========================================================================================================================-->

          <div class="col-12 scroll-h  fondo-table">

            <table class="m-0 table table-striped  table-sm table-hover">

              <!-- m-0 table table-striped table-bordered table-sm w-100 -->
              <thead class="table-colour">
                <tr>
                  <th class="text-center">
                    <span class="text-capitalize">Producto</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Stock</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Precio</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Cantidad</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Total</span>
                  </th>

                  <th class="text-center">
                    <span class="text-capitalize"></span>
                  </th>
                </tr>
              </thead>
              <tbody v-if="productoSend.length != 0">
                <tr class="Jtable-tr table-light" v-for="(product, index) in productoSend" :key="index">

                  <td class="py-3 px-1 text-center " :class="{ 'bgVentaMayorClass': activeRows[index] }">
                    <span class="text-capitalize">
                      {{ product.name }}
                    </span>
                  </td>

                  <td class="py-3 px-3 text-center" :class="{ 'bgVentaMayorClass': activeRows[index] }">
                    <span :class="product.stock <= 0 ? 'easy-badge-danger' : 'easy-badge-success'">{{ product.stock
                      }}</span>
                  </td>

                  <!-- <td v-if="(priceUnitaryInstalled && priceUnitary)" class="" :class="{ 'bgVentaMayorClass': activeRows[index] }"> -->
                  <td class="text-center" :class="{ 'bgVentaMayorClass': activeRows[index] }">
                    <!-- #fere-warp1 -->
                    <input class="Jinput-border-none btn shadow-icon m-0" type="number" :id="product.id" autofocus
                      @change="calculatePlus(index, product, true)" v-model="product.price" name="unitary"
                      :min="product.price" @keydown.capture="keydownEvent($event, index)" />
                  </td>


                  <td class="text-left" v-if="cantidadDecimalesSubModules"
                    :class="{ 'bgVentaMayorClass': activeRows[index] }">
                    <div class="btn-group">
                      <button class="m-0 px-3 btn-quantity" @click="decreaseQuantity(index, product)">-</button>

                      <input class="Jinput-border-none btn shadow-icon m-0" type="number" :id="product.id"
                        @keypress="isFloat($event)" @keydown.capture="keydownEvent($event, index)"
                        @change="calculatePlus(index, product, (priceUnitaryInstalled && priceUnitary) ? true : false)"
                        v-model="product.quantity" name="quantity" min="1">

                      <button class="m-0 px-3 btn-quantity" @click="increaseQuantity(index, product)">+</button>
                    </div>

                  </td>
                  <td class="text-left" v-else :class="{ 'bgVentaMayorClass': activeRows[index] }">
                    <div class="btn-group">
                      <button class="m-0 px-3 btn-quantity"
                        @click="decreaseQuantity(index, product), calculatePlus(index, product, (priceUnitaryInstalled && priceUnitary) ? true : false)">-</button>

                      <input class="Jinput-border-none btn shadow-icon  m-0" type="number" :id="product.id"
                        @keypress="isInteger($event)" @keydown.capture="keydownEvent($event, index)"
                        @change="calculatePlus(index, product, (priceUnitaryInstalled && priceUnitary) ? true : false)"
                        v-model="product.quantity" name="quantity" min="1">

                      <button class="m-0 px-3 btn-quantity"
                        @click="increaseQuantity(index, product), calculatePlus(index, product, (priceUnitaryInstalled && priceUnitary) ? true : false)">+</button>
                    </div>
                  </td>


                  <td class="py-3 px-0 text-center " :class="{ 'bgVentaMayorClass': activeRows[index] }">
                    <p class=" fs-4">{{ formatNumber(product.subtotal) }}$</p>
                  </td>





                  <td class=" text-center " :class="{ 'bgVentaMayorClass': activeRows[index] }">
                    <a @click="deleteProduct(index)" href="#" class="btn shadow-icon m-0" style="font-size: 12px;">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>

                </tr>
              </tbody>
            </table>
            <div v-if="productoSend.length == 0" class="h-70 d-flex flex-center text-center p-2">
              <div class="row">
                <div class="col-12"><br><br><br></div>
                <div class="col-12">
                  <img src="https://tivendoapp.defontana.com/static/media/ilustracion_pos.eb1defe4.svg" alt="">
                </div>
                <div class="col-12 text-center">
                  <h6>¡Te deseamos lo mejor en este día!</h6>
                  <p>Es hora de usar FagottoERP</p>
                </div>
              </div>

            </div>
          </div>


          <div v-if="!products" class="col-md-12 hv-80 d-flex flex-center text-center p-2">
            <h2>No existen productos actualmente</h2>
          </div>

          <!--==========================================================================================================================-->
          <!--(html)__( 'ç' )-->
          <!--==========================================================================================================================-->


        </div>
      </div>
      <!--!==========================================================================================================================-->
      <!--!(html)__( 'grid 2' )-->
      <!--!==========================================================================================================================-->

      <div class="col-3 Jfondo-2 Jnavi d-flex align-items-start flex-column">
        <div>
          <hr class="mt-0">
        </div>
        <div class="row d-flex w-100">
          <div class="col-12" style="padding: 15px;padding-top: 0px;font-weight: 900;color: #b1b1b1">
            <p class="m-0"><i class="fas fa-user"></i> {{ this.me.fullname.toUpperCase() }} - {{
                this.appName.toUpperCase() }}
            </p>
            <span>{{ this.fechaActual }}</span>
            <span>{{ this.horaActual }}</span>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <button @click="newTicket()" type="button"
              v-if="(ticketInstaller && tickets && order_kitchen_pending == false)" :disabled="editOrder"
              class="btn-r0 w-100 Jbutton-ticket m-1 btn btn-primary">
              Crear ticket
            </button>
          </div>
          <div class="col-md-12">
            <button type="button" v-if="(permitir_add_producto && newProductSell)" class="btn-r0 w-100 btn btn-danger"
              data-toggle="modal" data-target="#newProductModal">
              Añadir Producto
            </button>
          </div>
          <div class="col-md-12">
            <button v-if="settingFactura" type="button" class="fs-4 btn-r0 w-100 btn btn-warning fw-bold"
              @click="crearGuiaDespacho()">
              GUIA DE DESPACHO
            </button>
          </div>
        </div>
        <div>
          <hr>
        </div>

        <div v-if="settingPermitirDescuento">
          <div class="row d-flex">
            <div class="col-12">
              <h6>DESCUENTOS</h6>
            </div>
          </div>
          <div class="row d-flex justify-content-between ">
            <div v-for="(discount, index) in discounts" :key="discount.id" class="col-md-3">
              <button @click="toDiscount(discount)" type="button" class="text-info w-100 btn btn-white fw-bold">
                {{ discount.text + '%' }}
              </button>
            </div>
          </div>
        </div>

        <div class="mt-auto">
          <div v-if="productsIsDefined" class="">


            <div class="row">
              <div class="col-12">
                <div class="d-flex justify-content-between mb-3 " v-if="settingPermitirDescuento">
                  <p class="m-0 fw-light"><b>Descuento:</b></p>
                  <p class="m-0 fw-light"><b> {{ formatNumber(this.discount) }}$</b></p>
                </div>

                <hr>
              </div>

              <div class="col-12">
                <div class="d-flex justify-content-between mb-3 ">
                  <p class="m-0 fw-light"><b>Subtotal:</b></p>
                  <p class="m-0 fw-light"><b> {{ formatNumber(this.total / 1.19) }}$</b></p>
                </div>

                <hr>
              </div>

              <div class="col-12">
                <div class="d-flex justify-content-between mb-3 ">
                  <p class="m-0 fw-light"><b>IVA(19%):</b></p>
                  <p class="m-0 fw-light"><b> {{ formatNumber(this.total - Math.round(this.total / 1.19)) }}$</b></p>
                </div>

                <hr style="height:1px;color:#333;background-color:#333;" />
              </div>

              <div class="col-12">
                <div class="d-flex justify-content-between mb-3">
                  <h5 class="m-0 Jtext-total">Total:</h5>
                  <h5 class="m-0 Jtext-total "> {{ formatNumber(this.total - this.discount) }}$</h5>
                </div>

              </div>
              <div><br></div>
              <!--(html)__( 'botones' )_____________________________________________________________________________________________________________-->
              <div v-if="sellCreate" class="col-12">
                <!--(html)__( 'pagar' )_____________________________________________________________________________________________________________-->


                <div class="row ">
                  <div hidden><input type="text" value="0" id="newPagoValue"></div>
                  <div class="col-12">
                    <div class="row">
                      <div v-if="settingBoleta" :class="['p-0 pl-1', (settingFactura) ? 'col-6' : 'col-12']">
                        <button :disabled="offOn" type="button" class="btn-r0  btn btn-primary w-100"
                          @click="verifyClient('boleta')">
                          BOLETA (SII)</button>
                      </div>

                      <div v-if="settingFactura" :class="['p-0 pl-1', (settingBoleta) ? 'col-6' : 'col-12']">
                        <button :disabled="offOn" type="button" class="btn-r0  btn btn-primary w-100"
                          @click="verifyClient('factura')">
                          FACTURA</button>
                      </div>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="row">

                      <div v-if="settingBoletaLocal" class="col-12 p-0 pl-1">
                        <button :disabled="offOn" @click="verifyClient('efectivo')" type="button"
                          class="btn-r0 btn btn-success w-100 ">
                          Efectivo
                        </button>
                      </div>

                      <div v-if="settingCredito" class="col-12 p-0 pl-1">
                        <button :disabled="offOn" @click="verifyClient('credito')" type="button"
                          class="btn-r0 btn btn-info w-100 ">
                          Credito
                        </button>
                      </div>
                      <div v-if="settingDebito" class="col-12 p-0 pl-1">
                        <button :disabled="offOn" @click="verifyClient('debito')" type="button"
                          class="btn-r0 btn btn-primary w-100 ">
                          Debito
                        </button>
                      </div>
                    </div>
                  </div>






                </div>
                <div><br><br><br></div>
              </div>


            </div>
          </div>
        </div>
      </div>
      <div class="modal fade " id="modalProductAdd" tabindex="-1" role="dialog" aria-labelledby="modalProductAdd"
        aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-primario">
              <h5 class="modal-title">Producto</h5>
            </div>
            <div class="modal-body">
              <div class="w-100">

                <div class="row">
                  <div class="col-6 text-center">
                    <span class="mt-2"><strong>Nombre</strong></span>
                    <h6 class="mt-2">{{ product_name }}</h6>
                  </div>
                  <div class="col-6 text-center">
                    <span for="cantidad"><strong>Cantidad</strong></span>
                    <input class="Jinput-border-none btn shadow-icon " type="number" ref="counter_product"
                      v-model="product_counter" v-on:keyup.enter="addProductQuantityTable()" name="product_counter"
                      v-on:keydown="handleKeyDown" id="product_counter" min="1" autofocus />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="stockInstalled && productsSelect" class="modal fade " id="modalProductStock" tabindex="-1"
        role="dialog" aria-labelledby="modalProductStock" aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-primario">
              <h5 class="modal-title">Producto Stock</h5>
            </div>
            <div class="modal-body" ref="loaderStockProduct">
              <div class="row">
                <div class="col-12 text-center">
                  <span><strong>Escriba el nombre del producto</strong></span>
                  <v-select v-model="itemSelect" :options="this.productsSelect" @input="thisProductEvent"
                    label="text" />
                  <br>
                  <div v-if="stock_first">
                    <span><strong>{{ stock_first }}</strong></span>
                    <input class="Jinput-border-none btn" type="number" v-model="product_stock"
                      v-on:keyup.enter="productStockAdd()" name="product_stock" ref="product_Stock_counter"
                      v-on:keydown="handleKeyDown2" id="product_stock" min="1" autofocus />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>



    <QuantityProduct @quantityAdd="quantityAdd" @closeModal="closeLastOrder" :productQuantity="productQuantity" />
    <detailSell :dataDetail="dataDetail" @closeModal="closeLastOrder" />
    <modal-client @sendInfo="sendInfo" />
    <modal-guia-despacho @sendInfo="sendGuiaDespacho" />
    <modalVerify :propVerify="propVerify" @refreshData="refreshData" />
    <new-product @refresh="refreshData(true)" ref="newProduct" />
    <ticket :products="productoSend" :total="total" @newTicket="newTicket" />
    <assign-waiter-and-board @newTicket="newTicket" />
    <modal-turned @focusInput="focusInput" />

  </div>
</template>

<script>


// Components
import QuantityProduct from '@/components/modals/quantityProduct.vue';
import modalTurned from '@/components/modals/vuelto/modalTurned.vue';
import detailSell from '@/components/modals/detailSell.vue';
import modalClient from '@/components/modals/client.vue';
import modalGuiaDespacho from '@/components/modals/guiaDespacho.vue';
import ticket from '@/components/modals/ticket.vue';
import modalVerify from '@/components/modals/verifyDelete.vue';
import assignWaiterAndBoard from '@/components/modals/assignWaiterAndBoard.vue';

import newProduct from '@/components/modals/newProduct.vue';

// Helpers y plugins

import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';
import Print from '@/helpers/Print.js';
import $ from 'jquery'; window.jQuery = window.$ = $;



export default {
  name: 'newSell',
  data() {
    return {
      stock_first: '',
      product_stock: '',
      product_id: '',
      product_name: '',
      product_counter: 1,
      product_modal: {},
      products: false,
      productSearch: '',
      productoSend: [],
      totalOfProduct: [],
      productQuantity: null,
      filters: ['name', 'barcode'],
      total: 0,
      timeoutT: null,
      timeoutT2: null,
      dataDetail: null,
      gananciaTotal: null,
      propVerify: null,
      type_sell: null,
      order: false,
      editOrder: false,
      inputElement: null,
      other_type: false,
      stock: null,
      itemSelect: '',
      productsSelect: [],
      inputElement: '',
      activeRows: [],
      app: null,
      discount: 0,
      discounts: {
        1: { id: 1, percentage: 0.05, text: '5' },
        2: { id: 2, percentage: 0.1, text: '10' },
        3: { id: 3, percentage: 0.15, text: '15' },
        4: { id: 4, percentage: 0.2, text: '20' },
      },
      showHeader: true,
      app: null,
      fechaActual: '',
      horaActual: '',
      appName: ''
    }
  },




  async mounted() {
    //Get app
    var request = await this.$store.dispatch('main/refreshData', '?slim');
    this.app = request.data;
    this.appName = this.app.Name;
    //Para actualizar la hora y fecha
    setInterval(() => {
      this.obtenerFechaHoraActual();
    }, 1000);

    console.log("[newSell] mounted.")


    this.inputElement = document.querySelectorAll("[role='combobox']")[0];

    this.inputElement.focus();
    console.log("Componente montadoooo", this.inputElement);
    this.refreshData(false, true);

    document.addEventListener("keydown", (e) => {
      if (e.keyCode == 27) {
        this.productSearch = '';
        this.focusInput();
        this.getSearchValue(this.productSearch);
        this.$refs.productAutocomplete.setValue('');
        this.$emit('close');
      }
      if (e.keyCode == 115) {
        this.modalProductStock();
      }
    });
  },

  components: {
    QuantityProduct,
    detailSell,
    modalClient,
    modalGuiaDespacho,
    modalVerify,
    newProduct,
    ticket,
    assignWaiterAndBoard,
    modalTurned,
    //  Select2
  },
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },

  methods: {
    handleKeyDown(event) {
      if (/^\d$/.test(event.key)) {

      } else if (event.key === '+' || event.key === '-' || event.key === 'ArrowUp' || event.key === 'ArrowDown') {
        // Ejecutar las funciones mas() o menos() aquí
        if (event.key === '+' || event.key === 'ArrowUp') {
          this.increaseProduct_counter();
        } else if (event.key === '-' || event.key === 'ArrowDown') {
          this.decreaseProduct_counter();
        }
        event.preventDefault();
      }
    },
    increaseProduct_counter() {
      this.product_counter++
    },
    decreaseProduct_counter() {
      if (this.product_counter > 1) {
        this.product_counter--
      }
    },
    async getApp() {
      var request = await this.$store.dispatch('main/refreshData', '?slim');
      return request.data;
    },
    keydownEvent(event, index) {
      if (event.key === 'F10') {
        //Obtenemos ID del elemento resaltado = Input de precio con el foco
        let id = event.target.id;
        // Buscamos el producto en la lista de productos de la venta
        let filtradoSend = this.productoSend.find(producto => producto.id == id);
        //Buscamos el producto en la lista de productos completa para obtener el precio al mayor
        let filtradoProducts = this.products.find(producto => producto.id == id);

        //Comprobamos si el producto ya esta establecido para venta al mayor
        if (filtradoSend.isMayor !== null && filtradoSend.isMayor === true) {
          filtradoSend.price = filtradoProducts.price;
          filtradoSend.ganancia = filtradoProducts.ganancia;
          filtradoSend.subtotal = (filtradoSend.price * filtradoSend.quantity);
          filtradoSend.isMayor = false;
          this.calculateTotal();
          this.activeRows[index] = !this.activeRows[index];
          this.$awn.info('Producto establecido para venta al Detal');
        } else {
          //Comprobamos si el producto tiene el precio al mayor guardado
          if (filtradoProducts.mayor === null || filtradoProducts.mayor == 0) {
            this.$awn.alert('El producto no tiene precio al mayor registrado');
          } else {
            //Cambiamos los precios y demas valores
            filtradoSend.price = filtradoProducts.mayor;
            filtradoSend.ganancia = filtradoProducts.ganancia_mayor;
            filtradoSend.subtotal = (filtradoSend.price * filtradoSend.quantity);

            //Marcamos el producto como venta para comprobar su tipo luego
            filtradoSend.isMayor = true;
            this.calculateTotal();

            //Activar el fondo de la fila
            this.activeRows[index] = !this.activeRows[index];
            this.focusInput();
            this.$awn.info('Producto establecido para precio al mayor');
          }
        }

      }
      if (event.key === "Enter") {
        this.focusInput();
      }
    },
    focusInput() {
      console.log("focus 1 borrar-focus");
      this.inputElement.focus();
    },
    thisProduct(val) {
      console.log(val);
    },
    thisProductEvent({ id, text }) {
      text = text.replace('-', '\n\n');
      this.stock_first = text;
      this.product_id = id;
      console.log("focus 2 borrar-focus");
      setTimeout(() => { this.$refs.product_Stock_counter.focus(); }, 500);
    },
    async modalProductStock() {
      if (this.productsGet) {
        // Iniciando peticion
        var request = await this.$store.dispatch("products/getProductsOfSell");
        // Verificando respuesta
        if (request.success) {
          let productsSelect = [];
          var productos = (request.data.length == 0) ? false : request.data;
          console.log(productos);
          if (this.barcodeInstalled) {
            $.each(productos, function (key, val) {
              productsSelect.push({ "id": val.id, "text": val.barcode + ' ' + val.name + ' - stock ' + val.stock });
              // productsSelect.push({ "id": val.id, "text": val.name + ' - stock ' + val.stock });
            });
          }
          if (!this.barcodeInstalled) {
            $.each(productos, function (key, val) {
              productsSelect.push({ "id": val.id, "text": val.name + ' - stock ' + val.stock });
            });
          }
          this.productsSelect = productsSelect;
          $('#modalProductStock').modal('show');
        } else {
          this.$awn.alert('Error al obtener los productos');
        }
      }
    },
    handleKeyDown2(event) {
      if (/^\d$/.test(event.key)) {

      } else if (event.key === '+' || event.key === '-' || event.key === 'ArrowUp' || event.key === 'ArrowDown') {
        // Ejecutar las funciones mas() o menos() aquí
        if (event.key === '+' || event.key === 'ArrowUp') {
          this.increaseProduct_stock();
        } else if (event.key === '-' || event.key === 'ArrowDown') {
          this.decreaseProduct_stock();
        }
        event.preventDefault();
      }
    },
    increaseProduct_stock() {
      this.product_stock++
    },
    decreaseProduct_stock() {
      if (this.product_stock > 1) {
        this.product_stock--
      }
    },
    async productStockAdd() {
      if (this.product_id > 0) {
        Loader.fullPage();
        let data = new FormData();
        data.append('id', this.product_id);
        data.append('stock', this.product_stock);
        var request = await this.$store.dispatch("products/editStock", data);
        if (request.success) {
          this.$awn.success(request.data, { labels: { success: 'CORRECTO' } });
          $('#modalProductStock').modal('hide');
          setTimeout(() => {
            this.productSearch = '';
            this.focusInput();
            this.getSearchValue(this.productSearch);
            this.$refs.productAutocomplete.setValue('');
          }, 500);
        } else {
          this.$awn.alert('Error al actualizar el stock');
        }
      }
      Loader.hide();
    },
    // Submodulo de tickets
    // Crear
    async newTicket(type = false, dataBoardAndWaiter = false) {
      if (this.productoSend.length == 0) {
        this.$awn.alert("Es necesario agregar algun producto");
        return false;
      }
      if (this.gananciaInstalled && (this.gananciaTotal == null || this.gananciaTotal == '')) this.gananciaTotal = 0;

      if (!type && this.watchTicket) {
        $('#detailTicket').modal('show');
        return;
      } else {
        if (!dataBoardAndWaiter && this.cafeteriaInstaller) {
          Loader.fullPage();
          await this.$store.dispatch('waiters/getAllWaiters');
          await this.$store.dispatch("cafeteria/getCafeteria");
          Loader.hide();
          if (this.boards.length == 0) {
            this.$awn.alert('No existen mesas disponibles');
            return;
          }
          if (this.waiters.length == 0) {
            this.$awn.alert('No existen meseros actualmente');
            return;
          }
          $('#assignWaiterAndBoard').modal('show');
          return;
        }
        Loader.fullPage();
        var data = {
          products: JSON.stringify(this.productoSend),
          total: this.deFormatNumber(this.total, false),
          discount: this.deFormatNumber(this.discount, false),
          gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal, false) : 0
        };
        if (this.cafeteriaInstaller) {
          data.waiter_id = dataBoardAndWaiter.waiter_id;
          data.board_id = dataBoardAndWaiter.board_id;
        }
        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);

        // Iniciando peticion
        // Si no quieren editar
        var request = await this.$store.dispatch("sells/newTicket", thing);
        // Si quieren editar
        // if(this.editOrder) var request = await this.$store.dispatch("sells/editTicket", {data: thing, id: this.order.id});
        // else var request = await this.$store.dispatch("sells/newTicket", thing);

        // Verificando respuesta
        if (!request.success) {
          this.$awn.alert(request.data);
          Loader.hide();
          return false;
        }

        this.order = false;
        this.editOrder = false;
        this.productoSend = [];
        this.discount = 0,
          this.total = 0;
        this.$awn.success("Orden guardada exitosamente", { labels: { success: 'CORRECTO' } });
        
        // 🖨️ IMPRIMIR MÚLTIPLES TICKETS
        const tickets = [];
        if (request.data.ticket) tickets.push(request.data.ticket); // Ticket normal
        if (request.data.ticket_qr) tickets.push(request.data.ticket_qr); // Ticket con QR
        
        if (tickets.length > 0) {
          await this.imprimirMultiple(tickets);
        }

        Loader.hide();
        console.log("focus 3 borrar-focus");
        this.inputElement.focus();
      }
    },
    // Obtener
    async getSell(input) {
      Loader.fullPage();
      // Iniciando peticion
      var request = await this.$store.dispatch("sells/getOrder", input);
      Loader.hide();
      this.$refs.productAutocomplete.setValue('');
      this.productSearch = '';

      if (!request.success) {
        this.$awn.alert(request.data);
        return false;
      }
      this.order = request.data;
      this.editOrder = true;
      this.productoSend = JSON.parse(request.data.products);
      this.total = this.deFormatNumber(request.data.total, true);
      this.discount = this.deFormatNumber(request.data.discount, true);
      this.gananciaTotal = request.data.gananciaTotal;
    },
    crearGuiaDespacho() {
      if (!this.hayProductos()) {
        this.$awn.alert("Agrege un producto valido");
        return false;
      }
      $("#newGuiaDespachoModal").modal('show');
    },
    hayProductos() {
      if (this.productoSend.length == 0) {
        return false;
      }
      return true;
    },
    verifyClient(type_sell = null) {
      this.other_type = false;

      if (!this.hayProductos()) {
        this.$awn.alert("Agrege un producto valido");
        return false;
      }
      this.btnPago(false);
      if (type_sell == 'debito') this.other_type = type_sell;
      else this.type_sell = type_sell;

      if (type_sell == 'efectivo') this.other_type = type_sell;
      else this.type_sell = type_sell;

      if (type_sell == 'credito') this.other_type = type_sell;
      else this.type_sell = type_sell;

      if (this.clientsInstaller && type_sell == 'factura') {
        $('#clientCreate').modal('show');
      } else {
        this.sendInfo(false);
      }
    },
    hayProductoCantidad() {
      var dataError = false;
      for (var i = 0; i < this.productoSend.length; i++) {
        if (this.productoSend[i].quantity == null || this.productoSend[i].quantity == '') {
          dataError = true;
        }
      }
      return dataError;
    },
    //  JnewPago:true,

    btnPago(metho) {
      if (metho) {
        $("#newPagoValue").val("1");
        $("#btnNewPago").hide(100)
        $("#newPago").show(300);
      } else {
        $("#newPagoValue").val("0");
        $("#newPago").hide(100);
        $("#btnNewPago").show(300)

      }

    },
    newPago() {
      if ($("#newPagoValue").val() == "0") {
        this.btnPago(true)

      } else {
        this.btnPago(false);
      }

    },
    reiniciarNewSell() {
      /*
        Reiniciar el componente
        para su posterior uso.
      */
      this.productoSend = [];
      this.discount = 0;
      this.total = 0;
      this.order = false;
      this.editOrder = false;
    },
    async imprimir(pdf) {
      await Print.printBase64(pdf);
    },
    async imprimirMultiple(pdfs) {
      // 🖨️ Imprime múltiples PDFs en secuencia
      for (const pdf of pdfs) {
        if (pdf) {
          await Print.printBase64(pdf);
          // Pequeña pausa entre impresiones para evitar problemas
          await new Promise(resolve => setTimeout(resolve, 500));
        }
      }
    },
    prepararInformacion(data) {
      /*
          funcion que prepara los datos 
          para ser enviados al servidor 
          en un FormData
      */
      var formData = new FormData();
      for (let key in data) if (data[key]) formData.append(key, data[key]);
      return formData;
    },
    // Realizando la Guia de despacho
    async sendGuiaDespacho(client) { // despachito
      $("#newGuiaDespachoModal").modal('hide');

      // inicio del efecto de carga
      this.offOn = true;
      Loader.fullPage();

      if (this.hayProductoCantidad()) {
        this.$awn.alert("Rellene los campos de cantidad");
        return false;
      }

      var data = {
        products: JSON.stringify(this.productoSend),
        total: this.deFormatNumber(this.total, false),
        name: client.name,
        lastname: client.lastname,
        rut: client.rut,
        city: client.city,
        comuna: client.comuna,
        razon_social: client.razon_social,
        direction: client.direction,
        giro: client.giro,
        observacion: client.observacion,
        phone: (this.clientsPhone) ? client.phone : '',
        gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal, false) : 0,
        comment: client.comment,
        despacho: client.despacho,
        translado: client.translado
      };

      var formData = this.prepararInformacion(data);
      console.log("newSell->sendGuiadespacho->formData:", data)
      var request = await this.$store.dispatch("guia/newGuia", formData);

      if (request.success) {

        this.reiniciarNewSell();

        this.$awn.success("Guia Despacho CREADA, imprimiendo...", { labels: { success: 'CORRECTO' } });
        console.log("folio:", request.data.response_folio)
        //this.$awn.info(request.data.response_folio);
        if (request.data.response_folio) {
          console.log('[IMPRIMIENDO] Guia de despacho');
          this.imprimir(request.data.response_folio);
        }

      } else {
        this.$awn.alert(request.data);
        console.log("[ERROR]".request.data)
      }

      // final del efecto de carga
      Loader.hide();
      this.offOn = false;
      this.refreshData();

    },
    // Realizando la venta
    // llamado desde client.vue en el emit sendInfo
    async sendInfo(client = false) {
      $('#clientCreate').modal('hide');

      /*var dataError = false;
      for (var i = 0; i < this.productoSend.length; i++) {
        if(this.productoSend[i].quantity == null || this.productoSend[i].quantity == ''){
          dataError = true;
        }
      }*/
      if (this.hayProductoCantidad()) {
        this.$awn.alert("Rellene los campos de cantidad");
        return false;
      }
      if (this.gananciaInstalled && (this.gananciaTotal == null || this.gananciaTotal == '')) this.gananciaTotal = 0;


      this.offOn = true;
      Loader.fullPage();
      if (this.clientsInstaller && client) {
        // se llama al crear una factura desde newSell.vue
        var data = {
          products: JSON.stringify(this.productoSend),
          total: this.deFormatNumber(this.total, false),
          discount: this.deFormatNumber(this.discount, false),
          name: client.name,
          lastname: client.lastname,
          rut: client.rut,
          city: client.city,
          comuna: client.comuna,
          razon_social: client.razon_social,
          direction: client.direction,
          giro: client.giro,
          observacion: client.observacion,
          phone: (this.clientsPhone) ? client.phone : '',
          gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal, false) : 0
        };
        if (this.order) data.order = this.order.id;
        var thing = this.prepararInformacion(data);
      } else {

        var data = {
          products: JSON.stringify(this.productoSend),
          total: this.deFormatNumber(this.total, false),
          discount: this.deFormatNumber(this.discount, false),
          gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal, false) : 0
        };
        if (this.order) data.order = this.order.id;
        var thing = this.prepararInformacion(data);
      }
      if (this.other_type) {
        thing.append('typeSell', 'other');
        thing.append('other_type', this.other_type);
      } else if (this.type_sell != 'boleta_local') {
        thing.append('type_sell', this.type_sell);
      }
      // forma = forma de pago
      thing.append('forma', client.forma);
      // comment = Comentario
      thing.append('comment', client.comment);
      thing.append('fecha_emision', client.fecha_emision)
      thing.append('fecha_vencimiento', client.fecha_vencimiento)
      thing.append('nro_transaccion', client.nro_transaccion);
      thing.append('documento_referencia', client.documento_referencia)
      
      // Verificación de monto alto removida - ahora procesa directamente
      // Iniciando peticion
      /*
        // api/local/sell
        en el servidor
        SellsController@newSell
        SIIController@processFactura
        ServicesSII@processFactura_con_Data
        StringXML@facturaXML_con_Data
      */
      var request = await this.$store.dispatch("sells/newSell", thing);
      // Verificando respuesta
      if (request.success) {

        this.$awn.success("Venta realizada con exito", { labels: { success: 'CORRECTO' } });

        if (request.data.response_folio == 'boleta' || request.data.response_folio == 'factura') {
          this.$awn.info('El ajuste de ' + request.data.response_folio + ' se encuentra desactivado');
        } else if (request.data.response_folio) {
          console.log('Ejecutando impresion...');
          //var printPDF = await Print.printBase64(request.data.response_folio);
          this.imprimir(request.data.response_folio);
        }
        if (this.turned_ventas && !this.other_type && (this.type_sell == 'boleta' || this.type_sell == 'factura' || this.type_sell == 'boleta_local')) {
          this.sell_total = this.total;
          $('#modalTurned').modal('show');

        }
        this.reiniciarNewSell();

      } else {
        if (request.data.id) {

          this.reiniciarNewSell();

          this.$awn.success("Venta realizada con exito", { labels: { success: 'CORRECTO' } });
          this.$awn.info(request.data.response_folio);
        } else {
          this.$awn.alert(request.data);
        }
      }
      Loader.hide();
      this.offOn = false;
      this.refreshData();
      console.log("focus 4 borrar-focus");
      this.inputElement.focus();
    },
    //Revisar que no metan decimales en las cantidades
    isInteger(evt) {
      evt = (evt) ? evt : window.event;
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if ((charCode > 31 && (charCode < 48 || charCode > 57))) {
        evt.preventDefault();
      } else {
        return true;
      }
    },
    isFloat(evt) {
      evt = (evt) ? evt : window.event;
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 44) {
        evt.preventDefault();
      } else {
        return true;
      }
    },
    formatNumber(number) {
      return FormatNumber.format(number);
    },
    deFormatNumber(number, backend = true) {
      if (backend) {
        return FormatNumber.deFormatBackend(number);
      } else {
        return FormatNumber.deFormat(number);
      }
    },

    // Refrescando nueva venta y obteniendo productos
    async refreshData(notRefreshCart = false, mounted = false) {
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      Loader.dinamic();

      if (!notRefreshCart)
        this.productoSend = [];
      this.discount = 0;
      this.total = 0;
      if ((this.sellCreate || this.tickets) && this.productsGet) {
        // Iniciando peticion
        var request = await this.$store.dispatch("products/getProductsOfSell");
        // Verificando respuesta
        if (request.success) this.products = (request.data.length == 0) ? false : request.data;
        else this.$awn.alert('Error al obtener los productos');
      } else {
        this.$router.push('/inicio');
      }

      if (this.inputElement) {
        console.log("focus 5 borrar-focus");
        this.inputElement.focus();
      }

      // Culminando la funcion
      Loader.hide();
      this.offOn = false;
      //     if(mounted) this.$refs.productAutocomplete.$refs.input.focus();
    },
    search(input) {
      //console.log('SEARCH EXECUTES');
      //  console.log('search input', input);

      // Ahorramos la primera busqueda
      if (input == null || input == '') return [];
      // Ahorramos una busqueda cuando sea menor que 2
      if (input.length < 2) return [];

      clearTimeout(this.timeoutT2);
      if (!this.settingEnterBuscar) {
        this.timeoutT2 = setTimeout(() => {
          // Establecemos la busqueda en segundo plano
          this.productSearch = input;
        }, 500);
      }

      const inputLower = input.toLowerCase();
      const maxProductFindLength = 50;

      const productsFind = this.products
        .filter(product => !product.cecina)
        .filter(product => product.name.toLowerCase().includes(inputLower))
        .splice(0, maxProductFindLength);

      productsFind.unshift({ name: 'NOMBRE', stock: 'STOCK', price: 'PRICE', class: 'header-list' });

      return productsFind.splice(0, maxProductFindLength);

    },

    getSearchValue(result) {
      //  console.log("Llamando getSearchhh", result);
      return result.name + '';
    },

    submitAutocomplete(result) {

      if (this.settingEnterBuscar) {
        if (result === false) {
          result = this.products.find(element => {
            const nameLower = element.name.toLowerCase();
            const barcode = element.barcode;
            const inputLower = this.inputElement.value.toLowerCase();
            return nameLower === inputLower || barcode === inputLower;
          });

          if (!result) {
            return this.$awn.info("Sin resultados");
          }
        }
      }
      console.log("ENVIADO AUTOCOMPLETEEE", this.inputElement);
      if (this.product_modal_init) {
        this.product_counter = 1;
        this.product_name = result.name;
        this.product_modal = result;
        $('#modalProductAdd').modal('show');
        setTimeout(() => { this.$refs.counter_product.focus(); }, 500);
        return;
      }

      if (result.stock != null || this.settingVenderSinStock) {
        if (result.stock > 0 || this.settingVenderSinStock) {
          if (result.stock <= 10) {
            this.$awn.alert("Stock crítico de " + result.name + ", quedan " + result.stock);
          }

          this.quantityAdd({
            id: result.id,
            name: result.name,
            price: result.price,
            quantity: parseInt(this.product_counter),
            prices: result.prices,
            cecina: (result.cecina) ? true : false,
            stock: result.stock
          });
        } else {
          this.$awn.alert("Producto " + result.name + " sin stock");
          this.$refs.productAutocomplete.setValue('');
        }
      }

      this.$refs.productAutocomplete.setValue('');
      //para borrar focus
      this.inputElement.blur();
    },
    //agregar productos a tabla
    addProductQuantityTable() {
      var result = this.product_modal;
      console.log(result);
      if (result.stock != null || this.settingVenderSinStock) {
        if (result.stock > 0 || this.settingVenderSinStock) {
          if (result.stock <= 10) {
            this.$awn.alert("Stock critico de " + result.name + ", quedan " + result.stock)
          }
          if (this.product_counter > 0 && result) {
            $('#modalProductAdd').modal('hide');
            this.quantityAdd({
              id: result.id,
              name: result.name,
              price: result.price,
              quantity: parseInt(this.product_counter),
              prices: result.prices,
              cecina: (result.cecina) ? true : false,
              stock: result.stock
            });
          }
        } else {
          this.$awn.alert("Producto " + result.name + ", sin stock ");
          this.$refs.productAutocomplete.setValue('');
        }

        this.$refs.productAutocomplete.setValue('');
        return;
      }
      this.$awn.alert("ha ocurrido un error verifique por favor, la cantidad del producto ingresa");
    },

    // Orden anterior
    async lastOrder() {
      this.offOn = true;
      if (!this.lastSell) {
        this.$awn.alert('No existe una venta anterior');
        return false;
      }
      Loader.dinamic();
      var request = await this.$store.dispatch('sells/getSell', this.lastSell);
      Loader.hide();
      if (!request.success) {
        this.$awn.alert(request.data);
        console.log("focus 6 borrar-focus");
        this.inputElement.focus();
        return false;
      } else {
        this.dataDetail = request.data;
      }
      this.newPago(false)
      $('#detailSell').modal('show');
      this.offOn = false;
    },

    // Modal para verificar monto mayor a USD 5000
    openVerify(thing) {
      this.propVerify = {
        params: thing,
        title: 'Confirmacion de venta',
        text: '¿Usted esta seguro de realizar esta venta por un monto de ' + this.total + ' pesos chilenos?',
        store: 'sells/newSell',
        success: 'Venta realizada con exito'
      };
      $('#verifyDelete').modal('show');
    },

    //Agregar producto
    addProductQuantity(i, data) {
      // El subtotal es la cantidad actual por el nuevo precio que le envio (#subtotal)
      data.subtotal = parseFloat(data.quantity) * parseFloat(data.price);
      // Si la ganancia esta instalada, agrego la ganancia ✅
      if (this.gananciaInstalled) {
        if (!data.ganancia) data.ganancia = 0; //Esto antes era (this.gananciaInstalled && data.ganancia); pero creo asi es mas correcto -feredev
        data.ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
      }
      this.productoSend.push(data);
      // Ejecuto #calculatePlus ✅
      this.calculatePlus(i, data, (this.priceUnitaryInstalled && this.priceUnitary) ? true : false);
    },

    quantityAdd(data) { //#fere-warp1

      // Precio Variante Anterior
      if (!data.LastVariantPrice) data.LastVariantPrice = null;
      //#if (!data.LastVariantPrice) data.LastVariantPrice = 0;

      // Si hay mas de un producto agregado
      if (this.productoSend.length != 0) {

        var encontrado = false;
        // Recorro el arreglo de productos
        for (var i = 0; i < this.productoSend.length; i++) {

          // Si encuentra el producto
          if (data.id == this.productoSend[i].id) {
            // La cantidad es la antigua cantidad + la nueva cantidad ✅
            this.productoSend[i].quantity = parseFloat(this.productoSend[i].quantity) + parseFloat(data.quantity);
            // El subtotal es la cantidad actual por el nuevo precio que le envio (#subtotal)
            this.productoSend[i].subtotal = this.productoSend[i].quantity * parseFloat(data.price);
            // Si la ganancia esta instalada, agrego la ganancia ✅
            if (this.gananciaInstalled) {
              if (!data.ganancia) data.ganancia = 0; //🤔
              this.productoSend[i].ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
            }
            // Ejecuto #calculatePlus ✅
            this.calculatePlus(i, this.productoSend[i], (this.priceUnitaryInstalled && this.priceUnitary) ? true : false);
            encontrado = true;
          }

        }
        // Si no ha encontrado el producto
        if (!encontrado)
          this.addProductQuantity(i, data);

      } else { // Si no hay mas de un producto
        this.addProductQuantity(0, data);
      }

      $('#quantityProduct').modal('hide');
      console.log("focus 7 borrar-focus", this.inputElement);
      this.inputElement.focus();
    },

    AddProduct(data) {
      console.log("[newSell] AddProduct");
      this.productQuantity = data;
      $('#quantityProduct').modal('show');

    },

    //Agregar Ganancia y SubTotal en precio variante
    addGainSubTotalVariantPrice(index, isCecina = false, precioActual, cantidad, gananciaActual) { //No tengo creatividad para nombres bonitos justo ahora...

      //addGainSubTotalVariantPrice(index, data.cecina, precioActual, cantidad, gananciaActual);
      //index ---> this.productoSend[index]
      //isCecina>=
      //precioActual
      //cantidad
      //gananciaActual

      if (this.cecinaInstalled && isCecina) {
        this.productoSend[index].subtotal = (precioActual / 1000) * cantidad;
        if (this.gananciaInstalled) {
          this.productoSend[index].ganancia = (parseFloat(gananciaActual) / 1000) * cantidad;
        }
      } else {
        this.productoSend[index].subtotal = precioActual * cantidad;
        if (this.gananciaInstalled) {
          this.productoSend[index].ganancia = parseFloat(gananciaActual) * cantidad;
        }
      }


      //PD: hay una forma de reducir esto mas todavia pero no me quiero arriesgar

    },
    //JC
    // Calculate plus //#fere-warp1
    calculatePlus(index, data, unitary_price = false) {
      // Obtengo el producto
      data.price
      var productActual = Object.assign({}, this.products.find(element => element.id == data.id));

      var precioDeEntrada = this.productoSend[index].price;
      var precioVarianteDiferenteDeUnitario = false;

      if (unitary_price) productActual.price = this.productoSend[index].price;

      // Obtenemos la CANTIDAD
      var cantidad = parseFloat(data.quantity);

      if (this.productoSend[index].LastVariantPrice)
        if (parseFloat(precioDeEntrada) !== parseFloat(this.productoSend[index].LastVariantPrice)) {
          precioVarianteDiferenteDeUnitario = true;
        }

      // Si tiene precios variantes
      if (productActual.prices && productActual.prices.length) {

        var precios = productActual.prices;
        // <INICIO DE RECORRIDO DE LOS PRECIOS VARIANTES>
        for (var p = 0; p < precios.length; p++) {

          var precioActual = parseFloat(precios[p].precio);//Precio actual pero del recorrido
          var gananciaActual = parseFloat(precios[p].ganancia);//Ganancia actual pero del recorrido

          if (precios[p + 1]) {
            var pCantidad = precios[p].cantidad;
            var pNextCantidad = precios[p + 1].cantidad;

            //Aqui es cuando hay mas precios
            if (cantidad >= parseFloat(pCantidad) && cantidad < parseFloat(pNextCantidad)) {

              var precioVarianteActual = precioActual;
              // Si hay un PRECIO UNITARIO PERSONALIZADO por encima de un PRECIO VARIANTE ACTUAL
              if (precioVarianteDiferenteDeUnitario) {
                //! Detectamos si el PRECIO VARIANTE ACTUAL es el mismo que el PRECIO VARIANTE NUEVO
                if (parseFloat(this.productoSend[index].LastVariantPrice) === parseFloat(precioVarianteActual)) {
                  // Si PVA == PVN; entonces hacemos que el PRECIO UNITARIO PERSONALIZADO prevalezca
                  precioActual = precioDeEntrada; //<--- Precio de entrada viene desde arriba al principio de la funcion
                }
              }

              //Aqui es cuando encontro lo que buscaba, sale del ciclo al final
              this.addGainSubTotalVariantPrice(index, data.cecina, precioActual, cantidad, gananciaActual);
              this.productoSend[index].price = precioActual;
              //ESTABLECEMOS EL ELEGIDO DEL PRECIO VARIANTE:
              this.productoSend[index].LastVariantPrice = precioVarianteActual;
              break;

            }
          } else {

            var precioVarianteActual = precioActual;
            // Si hay un PRECIO UNITARIO PERSONALIZADO por encima de un PRECIO VARIANTE ACTUAL
            if (precioVarianteDiferenteDeUnitario) {
              //! Detectamos si el PRECIO VARIANTE ACTUAL es el mismo que el PRECIO VARIANTE NUEVO
              if (parseFloat(this.productoSend[index].LastVariantPrice) === parseFloat(precioVarianteActual)) {
                // Si PVA == PVN; entonces hacemos que el PRECIO UNITARIO PERSONALIZADO prevalezca
                precioActual = precioDeEntrada; //<--- Precio de entrada viene desde arriba al principio de la funcion
              }
            }

            //Aqui es cuando llego al ultimo precio
            this.addGainSubTotalVariantPrice(index, data.cecina, precioActual, cantidad, gananciaActual);
            this.productoSend[index].price = precioActual;
            //ESTABLECEMOS EL ELEGIDO DEL PRECIO VARIANTE:
            this.productoSend[index].LastVariantPrice = precioVarianteActual;

          }

        }
        // <FINALIZACION DE RECORRIDO DE LOS PRECIOS VARIANTES>
      } else { // No recorro los precios variantes si no que uso un solo precio...
        this.addGainSubTotalVariantPrice(index, data.cecina, productActual.price, cantidad, productActual.ganancia);
      }

      this.productoSend[index].precioAnterior = this.productoSend[index].price;

      this.productoSend[index].quantity = cantidad;
      this.calculateTotal();

    },
    calculateTotal() {
      this.discount = 0;
      this.total = 0;
      this.gananciaTotal = 0;
      for (var i = 0; i < this.productoSend.length; i++) {
        var price = 0;
        var ganancia = 0;
        price = this.productoSend[i].subtotal;
        this.total = parseFloat(this.total) + parseFloat(price);

        if (this.gananciaInstalled) {
          ganancia = this.productoSend[i].ganancia;
          this.gananciaTotal = this.gananciaTotal + parseFloat(ganancia);
        }
      }
      console.log("focus 9 borrar-focus");
      //this.inputElement.focus(); //<---- Hacer focus aqui es lo que causa el verdadero problema
    },
    deleteProduct(index) {
      this.productoSend.splice(index, 1);
      if (this.productoSend.length == 0) {
        this.order = false;
        this.editOrder = false;
      }
      this.calculateTotal();
    },
    getImage(image) {
      return BaseUrl.getUrl('images/' + image);
    },
    closeLastOrder() {
      $('#quantityProduct').modal('hide');
      $('#detailSell').modal('hide');
      console.log("focus 10 borrar-focus");
      this.inputElement.focus();
    },
    increaseQuantity(index, product) {
      this.productoSend[index].quantity++;
    },
    decreaseQuantity(index, product) {
      this.productoSend[index].quantity;
      if (this.productoSend[index].quantity > 1) {
        this.productoSend[index].quantity--;
      }
    },
    toDiscount(discount) {
      this.discount = this.total * discount.percentage;
    },
    obtenerFechaHoraActual() {
      const fecha = new Date();
      this.fechaActual = fecha.toLocaleDateString();
      this.horaActual = fecha.toLocaleTimeString();
    }
  },

  computed: {
    //ocultar div de #newPago en vue
    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input', offOn) }
    },
    sell_total: {
      get() { return this.$store.main.sell_total },
      set(val) { this.$store.commit('main/setProperty', { key: 'sell_total', data: val }) }
    },
    // Permisos para las ventas
    settingBoleta: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta');
      }
    },
    settingBoletaLocal: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local');
      }
    },
    settingFactura: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.factura');
      }
    },
    settingDebito: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.debito');
      }
    },
    settingCredito: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.credito') } },
    cantidadDecimalesSubModules: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.cantidades_float');
      }
    },
    settingVenderSinStock: {
      get() {
        return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_venta_sin_stock');
      }
    },
    settingPermitirDescuento: {
      get() {
        return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_descuento');
      }
    },
    lastSell: { get() { return this.$store.getters['sells/sellPast']; } },
    order_kitchen_pending: { get() { return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.order_kitchen_pending'); } },
    productsGet: {
      get() {

        if (ConfigHelper.HavePermission('productos_obtener')) {
          return ConfigHelper.HavePermission('productos_obtener');
        } else {
          return false;
        }

      }
    },

    stockInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_stock'); } },

    settingEnterBuscar: {
      get() {
        return ConfigHelper.ConfStr('modulos.ventas.ajustes.enter_para_buscar');
      }
    },

    sellCreate: {
      get() {
        if (ConfigHelper.HavePermission('crear_venta')) {
          return ConfigHelper.HavePermission('crear_venta');
        } else {
          return false;
        }
      }
    },

    tickets: { get() { return ConfigHelper.HavePermission('getionar_tickets'); } },
    priceUnitary: { get() { return ConfigHelper.HavePermission('precio_unitario'); } },
    newProductSell: { get() { return ConfigHelper.HavePermission('crear_productos_nueva_venta'); } },
    ticketInstaller: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket'); } },
    turned_ventas: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.turned_ventas'); } },

    product_modal_init: { get() { return ConfigHelper.ConfStr('modulos.ventas.ajustes.product_modal'); } },
    cafeteriaInstaller: { get() { return ConfigHelper.ConfStr('modulos.cafeteria'); } },
    watchTicket: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ver_ticket'); } },
    clientsInstaller: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes'); } },
    cecinaInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_cecina'); } },
    gananciaInstalled: { get() { return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_ganancia'); } },

    //permitir_add_producto
    permitir_add_producto: { get() { return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_crear_producto_en_venta'); } },
    barcodeInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_barcode'); } },
    priceUnitaryInstalled: { get() { return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_precio_unitario'); } },
    clientsPhone: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono'); } },
    clientsObligatorio: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio'); } },
    boards: { get() { return this.$store.getters['cafeteria/getBoardsFree'] } },
    waiters: { get() { return this.$store.getters['waiters/getAllWaiters'] } },

    filteredList: {
      get() {
        clearTimeout(this.timeoutT);

        const lowerCaseProductSearch = this.productSearch.toLowerCase();
        const filteredProducts = [];

        this.timeoutT = setTimeout(() => {
          const findProduct = this.products.find(element => element.barcode == this.productSearch);

          if (this.productSearch.length == 12 && !findProduct) {
            this.getSell(this.productSearch);
          }

          if (findProduct) {
            if (findProduct.stock != null || this.settingVenderSinStock) {
              if (findProduct.stock > 0 || this.settingVenderSinStock) {
                if (findProduct.stock <= 10) {
                  this.$awn.alert("Stock crítico de " + findProduct.name + ", quedan " + findProduct.stock);
                }
                if (this.product_modal_init) {
                  this.product_counter = 1;
                  this.product_name = findProduct.name;
                  this.product_modal = findProduct;
                  $('#modalProductAdd').modal('show');
                  setTimeout(() => { this.$refs.counter_product.focus(); }, 500);
                  return;
                }
                this.quantityAdd({
                  id: findProduct.id,
                  name: findProduct.name,
                  price: findProduct.price,
                  quantity: parseInt(1),
                  prices: findProduct.prices,
                  cecina: findProduct.cecina ? true : false,
                  stock: findProduct.stock
                });
                this.productSearch = '';
                this.$refs.productAutocomplete.setValue('');
              } else {
                this.$awn.info('Producto, ' + findProduct.name + ' sin stock C');
                this.$refs.productAutocomplete.setValue('');
              }
            }
          }

          if (!findProduct && filteredProducts.length === 0) {
            this.productSearch = '';
            this.$refs.productAutocomplete.setValue('');
            this.$awn.info('Sin resultados');
          }
        }, 50);

        const filterFields = this.filters;
        const filterFieldsLength = filterFields.length;
        const productsLength = this.products.length;

        for (let i = 0; i < productsLength; i++) {
          const product = this.products[i];
          let found = false;

          for (let j = 0; j < filterFieldsLength; j++) {
            const filter = filterFields[j];
            const fieldValue = product[filter];

            if (fieldValue && fieldValue.toLowerCase().includes(lowerCaseProductSearch)) {
              filteredProducts.push(product);
              found = true;
              break;
            }
          }

          if (found) {
            break;
          }
        }

        return filteredProducts;
      },
    },

    productsIsDefined: {
      get() { return this.products; }
    },
    haveProducts: {
      get() { return this.productsIsDefined && this.products.length; }
    },
    me: { get() { return this.$store.getters['main/user']; } },

  }
}
</script>

<style scoped>
.select2 {
  width: 100% !important;
}

.Jfondo-1 {
  background-color: #f5f9fc;
}

.Jfondo-2 {
  background-color: #f5f9fcd7;
}

.table-colour {
  background-color: #e5eefa;
  color: #5e6f92;
}

.Jnavi {
  width: 100vw;
  height: 100vh;
}

.Jtable-border-name {
  border-right-color: rgba(40, 59, 50, 0.068);
  border-right-style: solid;
}

.Jtable-tr {
  height: 48px;
  border-top-width: 4px;
  border-top-color: #f5f9fc;
  border-top-style: solid;

  box-shadow: 0 2px 4px 0 rgb(17 32 66 / 20%);
  border: 0 solid #000;
  background-color: #fff;

}

.Jinput-border-none {
  width: 64px;
  min-width: 100%;
  height: 32px;
  text-align: center;
  border-radius: 10px;
  background-color: #ffffff;
  /* #c3e6cb;*/
  border: none;
  outline: none;
}

.Jinput-border-none:hover {
  background-color: #5e6f9223;
  color: #000000;
  border: 2px solid #2d42324d;
  ;

}

.Jinput-border-none:focus {
  background-color: #5e6f9231;
  color: #000000;
  border: 2px solid #4a6851bb;
  ;

}

.Jbutton-guia {

  color: #2a3f6e;


}




.Jbutton-ticket {
  background-color: #dd5d5b;
  color: #ffff;

}

.Jtext-total {
  color: #2a3f6e;
  font-size: 1.4rem;
  font-weight: bold;
}




.btns_sell {
  display: flex;
  align-items: center;
  padding: 5px 0px 0px 0px;
}

.hv-80 {
  height: 80vh;
}

.btnProduct {
  cursor: pointer;
}

.borderedB {
  border-bottom: 1px solid #ccc;
}

.bordertop {
  border-top: 1px solid #ccc;
}

.desingInput {
  width: 70px;
  min-width: 100%;
  background: none;
  border-radius: 3px;
  border: 1px solid #0B4F6C;
  padding: 4px 2px;
  text-align: center;
}

.desingInput2 {
  width: 100px;
  min-width: 100%;
  background: none;
  border-radius: 3px;
  border: 1px solid #0B4F6C;
  padding: 4px 2px;
}

.btn-perzon {
  font-size: 14px;
  width: 23.5px !important;
  height: 23.5px !important;
  padding: 0px;
  border-radius: 50px;
  border: 1px solid #0B4F6C;
  color: #0B4F6C !important;
  box-shadow: none !important;
}

.btnRadius {
  font-size: 18px;
  border-radius: 2px !important;
}

.bg-grey {
  border-radius: 2px;
  margin-left: 1.5px;
  background: #dedada;
}

.h-87 {
  min-height: 87vh !important;
}

.scroll-h {
  height: 90vh !important;
  overflow-y: scroll;
  overflow: auto;
}

::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-track {
  background: #c0c0c0;
  border-radius: 50px;
}

::-webkit-scrollbar-thumb {
  background: #000000;
  border-radius: 50px;
}

.h-70 {
  height: 45vh;
  overflow: auto;
}

.h-30 {
  height: 45vh;
}

.btn-r0 {
  padding: 10px 5px;
  border-radius: 0px;
}

.col-md-6-5 {
  -ms-flex: 0 0 67% !important;
  flex: 0 0 67% !important;
  max-width: 67% !important;
  margin-right: 1% !important;
}

.col-md-5-5 {
  -ms-flex: 0 0 32% !important;
  flex: 0 0 32% !important;
  max-width: 32% !important;
}

@media (max-width: 1366px) {
  .col-md-6-5 {
    -ms-flex: 0 0 61% !important;
    flex: 0 0 61% !important;
    max-width: 61% !important;
    margin-right: 1% !important;
  }

  .col-md-5-5 {
    -ms-flex: 0 0 38% !important;
    flex: 0 0 38% !important;
    max-width: 38% !important;
  }
}

@media (max-width: 1120px) {
  .col-md-6-5 {
    -ms-flex: 0 0 49% !important;
    flex: 0 0 49% !important;
    max-width: 49% !important;
    margin-right: 1% !important;
  }

  .col-md-5-5 {
    -ms-flex: 0 0 50% !important;
    flex: 0 0 50% !important;
    max-width: 50% !important;
  }
}

@media (max-width: 850px) {
  .col-md-6-5 {
    -ms-flex: 0 0 100% !important;
    flex: 0 0 100% !important;
    max-width: 100% !important;
    margin-right: 0% !important;
  }

  .col-md-5-5 {
    -ms-flex: 0 0 100% !important;
    flex: 0 0 100% !important;
    max-width: 100% !important;
  }
}

.autocomplete-input-container {
  flex: 1;
  max-width: 550px;
}

.space-between-search-and-create {
  display: flex;
  justify-content: space-between;
}

.newPago-hiden {
  display: none;
}

.bgVentaMayorClass {
  background-color: #e5eefa;
}

.easy-badge-success {
  background-color: palegreen;
  padding: 4px;
  border-radius: 5px;
  color: green;
}

.easy-badge-danger {
  background-color: #ff6e6e;
  padding: 4px;
  border-radius: 5px;
  color: #770d17;
}

.btn-quantity {
  background-color: #e5eefa;
  border: none;
  color: #4285f4;
  font-weight: bold;
  font-size: 18px;
  box-shadow: 0 2px 5px 0 rgb(0 0 0 / 16%), 0 2px 10px 0 rgb(0 0 0 / 12%);
}

.header-list {
  padding: 10px;
  margin-left: 20px;
  background-color: #dee2e6;
  color: #5e6fad
}

.list-item {
  padding: 10px;
  margin-left: 20px
}
</style>