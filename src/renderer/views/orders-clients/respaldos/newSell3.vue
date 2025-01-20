<template>

  <div class="product-bg d-flex flex-column px-3">
    <div class="row">
      <div class="col-12 p-0 space-between-search-and-create">
        <div class="autocomplete-input-container">
          <autocomplete :search="search" placeholder="Buscar" :getResultValue="getSearchValue" @submit="submitAutocomplete" ref="productAutocomplete" ></autocomplete>
        </div>
        
          <img src="../assets/logo.png" class="img-fluid" alt="FagottoERP" height="10%" width="10%">
        <div class="btns_sell">
          <button @click="newTicket()" type="button" v-if="(ticketInstaller && tickets)" :disabled="editOrder" class="m-1 btn bg-primario text-white text-capitalize">
            Crear ticket
          </button>
          <button type="button" v-if="(permitir_add_producto && newProductSell)" class="m-1 btn bg-secundario text-white text-capitalize" data-toggle="modal" data-target="#newProductModal">
            Añadir Producto
          </button>
        </div>
      </div>
    </div>
  <!-- correccion de error de busqueda de producto por codigo de barra -->
  <div class="row pt-1">
        <div v-if="products" class="" hidden="true">
          <div class="btnProduct d-flex w-100 flex-wrap" :class="(filteredList.length <= 3) ? '' : 'justify-content-around'">
            <div @click="AddProduct(product)" class="m-2" v-for="(product, index) in filteredList" :key="index" v-if="!cecinaInstalled || (cecinaInstalled && product.cecina)">
            </div>
          </div>
      </div>
  </div>
  <div class="modal-footer">

      </div>
    <div class="row pt-1 h-87">
      <div class="col-md-6-5 scroll-h">
          <table v-if="productoSend.length != 0" class="m-0 table table-striped table-bordered table-sm w-100">
            <!-- m-0 table table-striped table-bordered table-sm w-100 -->
            <thead>
                <tr>
                  <th class="text-center">
                    <span class="text-capitalize">Producto</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Cantidad</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Precio</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Total</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">Stock</span>
                  </th>
                  <th class="text-center">
                    <span class="text-capitalize">X</span>
                  </th>
                </tr>
            </thead>
            <tbody>
              <tr class="borderedB" v-for="(product,index) in productoSend" :key="index">
                <td class="py-3 px-2 text-center">
                  <h6 class="text-capitalize">
                    {{product.name}}
                  </h6>
                </td>

                <td class="py-3 px-1" v-if="cantidadDecimalesSubModules">
                  <input class="desingInput" type="number" id="quantity" 
                  @keypress="isFloat($event)"
                  @change="calculatePlus(index, product, (priceUnitaryInstalled && priceUnitary)?true:false)" v-model="product.quantity" name="quantity" min="1">
                </td>
                <td class="py-3 px-1" v-else >
                  <input class="desingInput" type="number" id="quantity" 
                  @keypress="isInteger($event)" 
                  @change="calculatePlus(index, product, (priceUnitaryInstalled && priceUnitary)?true:false)" v-model="product.quantity" name="quantity" min="1">
                </td>
                <td v-if="(priceUnitaryInstalled && priceUnitary)" class="py-3 px-1 text-center">
                  <!-- #fere-warp1 -->
                  <input
                    class="desingInput2"
                    type="number"
                    id="unitary"
                    @keypress="isInteger($event)"
                    @change="calculatePlus(index, product, true)"
                    v-model="product.price" name="unitary" min="1"
                  />
                </td>
                <td class="py-3 px-1 text-center">
                  <h6>{{formatNumber(product.subtotal)}}$</h6>
                </td>
                <td class="py-3 px-1 text-center">
                  <h6>{{product.stock}}</h6>
                </td>
                <td class="py-3 text-right">
                  <a @click="deleteProduct(index)" href="#" class="btn shadow-icon btn-rounded btn-perzon">
                     <i class="fas fa-times"></i>
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="productoSend.length == 0" class="h-70 d-flex flex-center text-center p-2">
            <h4>Añada un producto al carro</h4>
          </div>
      </div>
      <div v-if="!products" class="col-md-12 hv-80 d-flex flex-center text-center p-2">
        <h2>No existen productos actualmente</h2>
      </div>

      <div v-if="productsIsDefined" class="col-md-5-5 p-0 pr-2">
        <div class="h-70">
          <table v-if="productoSend.length != 0" class="table table-borderless"> 
            <tbody>
            </tbody>
          </table>
        </div>
        <div class="bordertop h-30 p-0 d-flex justify-content-between align-items-end">
          <div class="w-60 row">
            <div class="col-12 p-2 row bg-grey justify-content-between">
              <!-- p-0 pl-1 col-6 -->
              <div class="">
                <h5 style="font-size: 1.2rem;" class="m-0">
                  Subtotal:
                </h5>
              </div>
              <div class="">
                <h5 style="font-size: 1.4rem;" class="m-0">
                  {{formatNumber(this.total/1.19)}}$
                </h5>
              </div>
            </div>
            <div class="col-12 p-2 row bg-grey justify-content-between">
              <div class="">
                <h5 style="font-size: 1.2rem;" class="m-0">
                  IVA(19%):
                </h5>
              </div>
              <div class="">
                <h5 style="font-size: 1.4rem;" class="m-0">
                  {{formatNumber(this.total - Math.round(this.total/1.19))}}$
                </h5>
              </div>
            </div>
            <div class="col-12 p-2 row bg-grey justify-content-between">
              <div class="">
                <h5 style="font-size: 1.4rem;" class="m-0">
                  Total:
                </h5>
              </div>
              <div class="">
                <h5 style="font-size: 1.8rem;" class="m-0">
                  {{formatNumber(this.total)}}$
                </h5>
              </div>
            </div>
            <div v-if="sellCreate" class="col-12">
              <div class="row">
                <div v-if="settingBoleta" :class="['p-0 pl-1', (settingFactura) ? 'col-6' : 'col-12']">
                  <button :disabled="offOn" type="button" class="btnRadius w-100 btn bg-secundario btn-r0 m-0 mt-1" @click="verifyClient('boleta')">Crear con boleta</button>
                </div>
                <div v-if="settingFactura" :class="['p-0 pl-1', (settingBoleta) ? 'col-6' : 'col-12']">
                  <button :disabled="offOn" type="button" class="btnRadius bg-secundario w-100 btn btn-r0 m-0 mt-1 text-dark" @click="verifyClient('factura')">Crear con factura</button>
                </div>

                <div :class="['p-0 pl-1', (settingBoletaLocal) ? 'col-6' : 'col-12']">
                  <button :disabled="offOn" @click="lastOrder()" type="button" class="btnRadius bg-primario w-100 btn btn-r0 m-0 mt-1 text-dark">Venta anterior</button>
                </div>
                <div v-if="settingBoletaLocal" class="col-6 p-0 pl-1">
                  <button :disabled="offOn" @click="verifyClient('boleta_local')" type="button" class="btnRadius w-100 btn bg-primario btn-r0 m-0 mt-1" >
                    Efectivo
                  </button>
                </div>
                <div v-if="settingDebito" class="col-12 p-0 pl-1">
                  <button :disabled="offOn" @click="verifyClient('debito')" type="button" class="btnRadius w-100 btn bg-primario btn-r0 m-0 mt-1" >
                    Debito
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <QuantityProduct @quantityAdd="quantityAdd" @closeModal="closeLastOrder" :productQuantity="productQuantity" />
    <detailSell :dataDetail="dataDetail" @closeModal="closeLastOrder"/>
    <modal-client @sendInfo="sendInfo" />
    <modalVerify :propVerify="propVerify" @refreshData="refreshData"/>
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
import $ from 'jquery';

export default {
  name: 'newSell',
  data(){
    return{
      products: false,
      productSearch: '',
      productoSend: [],
      totalOfProduct: [],
      productQuantity: null,
      filters:['name','barcode'],
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
    }
  },
  mounted(){
    //HavePermission
    this.inputElement = document.querySelectorAll("[role='combobox']")[0];
    this.refreshData(false, true);
  },
  components:{
    QuantityProduct,
    detailSell,
    modalClient,
    modalVerify,
    newProduct,
    ticket,
    assignWaiterAndBoard,
    modalTurned
  },
  props:{
    value: {
      type: Boolean,
      default: false
    }
  },
  methods:{
    focusInput(){
      this.inputElement.focus();
    },
    // Submodulo de tickets
      // Crear
      async newTicket(type = false, dataBoardAndWaiter = false){
        if(this.productoSend.length == 0){
          this.$awn.alert("Es necesario agregar algun producto");
          return false;
        }
        if(this.gananciaInstalled && (this.gananciaTotal == null || this.gananciaTotal == '')) this.gananciaTotal = 0;

        if(!type && this.watchTicket){
          $('#detailTicket').modal('show');
          return;
        }else{
          if(!dataBoardAndWaiter && this.cafeteriaInstaller){
            Loader.fullPage();
            await this.$store.dispatch('waiters/getAllWaiters');
            await this.$store.dispatch("cafeteria/getCafeteria");
            Loader.hide();
            if(this.boards.length == 0){
              this.$awn.alert('No existen mesas disponibles');
              return;
            }
            if(this.waiters.length == 0){
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
            gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal,false) : 0
          };
          if(this.cafeteriaInstaller){
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
          this.total = 0;
          this.$awn.success("Orden guardada exitosamente",{labels:{success:'CORRECTO'}});
          var printPDF = await Print.printBase64(request.data.ticket);

          Loader.hide();
          this.inputElement.focus();
        }
      },
      // Obtener
      async getSell(input){
        Loader.fullPage();
        // Iniciando peticion
        var request = await this.$store.dispatch("sells/getOrder", input);
        Loader.hide();
        this.$refs.productAutocomplete.setValue('');
        this.productSearch = '';

        if(!request.success){
          this.$awn.alert(request.data);
          return false;
        }
        this.order = request.data;
        this.editOrder = true;
        this.productoSend = JSON.parse(request.data.products);
        this.total = this.deFormatNumber(request.data.total, true);
        this.gananciaTotal = request.data.gananciaTotal;
      },

    verifyClient(type_sell = null){
      this.other_type = false;
      if(this.productoSend.length == 0){
        this.$awn.alert("Agrege un producto valido");

        return false;
      }

      if(type_sell == 'debito') this.other_type = type_sell;
      else this.type_sell = type_sell;

      if(this.clientsInstaller && type_sell == 'factura'){
        $('#clientCreate').modal('show');
      }else{
        this.sendInfo(false);
      }
    },
    // Realizando la venta
    async sendInfo(client = false){
      $('#clientCreate').modal('hide');

      var dataError = false;
      for (var i = 0; i < this.productoSend.length; i++) {
        if(this.productoSend[i].quantity == null || this.productoSend[i].quantity == ''){
          dataError = true;
        }
      }
      if(dataError){
        this.$awn.alert("Rellene los campos de cantidad");
        return false;
      }
      if(this.gananciaInstalled && (this.gananciaTotal == null || this.gananciaTotal == '')) this.gananciaTotal = 0;


      this.offOn = true;
      Loader.fullPage();
      if(this.clientsInstaller && client){
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
          phone: (this.clientsPhone) ? client.phone : '',
          gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal,false) : 0
        };
        if(this.order) data.order = this.order.id;
        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);
      }else{

        var data = {
          products: JSON.stringify(this.productoSend),
          total: this.deFormatNumber(this.total, false),
          gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal,false) : 0
        };
        if(this.order) data.order = this.order.id;
        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);
      }
      if(this.other_type){
        thing.append('typeSell', 'other');
        thing.append('other_type', this.other_type);
      }else if(this.type_sell != 'boleta_local'){
        thing.append('type_sell', this.type_sell);
      }

      var verifyTotal = this.total;
      if(verifyTotal >= 3862000) this.openVerify(thing);
      else{
        // Iniciando peticion
        var request = await this.$store.dispatch("sells/newSell", thing);
        // Verificando respuesta
        if (request.success) {
          this.productoSend = [];
          // this.total = 0;
          this.order = false;
          this.editOrder = false;
          this.$awn.success("Venta realizada con exito",{labels:{success:'CORRECTO'}});
          if(request.data.response_folio == 'boleta' || request.data.response_folio == 'factura') {
            this.$awn.info('El ajuste de '+ request.data.response_folio+' se encuentra desactivado');
          }else if (request.data.response_folio) {
            console.log('Ejecutando impresion...');
            var printPDF = await Print.printBase64(request.data.response_folio);
          }
          if(this.turned_ventas && !this.other_type && (this.type_sell == 'boleta' || this.type_sell == 'factura' || this.type_sell == 'boleta_local')){
            $('#modalTurned').modal('show');
            this.sell_total = this.total;
          }
          this.total = 0;

        }else{
          if(request.data.id){
            this.productoSend = [];
            this.total = 0;
            this.order = false;
            this.editOrder = false;
            this.$awn.success("Venta realizada con exito",{labels:{success:'CORRECTO'}});
            this.$awn.info(request.data.response_folio);
          }else{
            this.$awn.alert(request.data);
          }
        }
      }
      Loader.hide();
      this.offOn = false;
      this.refreshData();
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
    formatNumber(number){
      return FormatNumber.format(number);
    },
    deFormatNumber(number,backend = true){
      if (backend) {
        return FormatNumber.deFormatBackend(number);
      }else{
        return FormatNumber.deFormat(number);
      }
    },

    // Refrescando nueva venta y obteniendo productos
    async refreshData(notRefreshCart = false, mounted = false){
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      Loader.dinamic();

      if (!notRefreshCart)
        this.productoSend = [];

      this.total = 0;
      if ((this.sellCreate || this.tickets) && this.productsGet) {
        // Iniciando peticion
        var request = await this.$store.dispatch("products/getProductsOfSell");
        // Verificando respuesta
        if (request.success) this.products = (request.data.length == 0) ? false : request.data;
        else this.$awn.alert('Error al obtener los productos');
      }else{
        this.$router.push('/inicio');
      }
      this.inputElement.focus();
      // Culminando la funcion
      Loader.hide();
      this.offOn = false;
      if(mounted) this.$refs.productAutocomplete.$refs.input.focus();
    },
    search(input) {
      //console.log('SEARCH EXECUTES');
      //console.log('search input', input);

      // Ahorramos la primera busqueda
      if (input == null || input == '') return [];
      // Ahorramos una busqueda cuando sea menor que 1
      if (input.length < 1) return [];

      clearTimeout(this.timeoutT2);
      this.timeoutT2 = setTimeout(() => {
        // Establecemos la busqueda en segundo plano
        this.productSearch = input;
      }, 150);

      const inputLower = input.toLowerCase();
      const maxProductFindLength = 100;

      const productsFind = this.products.filter(product => {
        if (product.cecina == true) return false;

        var index = 0;
        const productNameLower = product.name.toLowerCase();
        for (var i = 0; i < productNameLower.length; i++) {
          if (productNameLower.startsWith(inputLower, i)) {
            index = i;
            break;
          };
        }

        return productNameLower.startsWith(inputLower, index);
      });

      return productsFind.splice(0, maxProductFindLength);
    },

    getSearchValue(result) {
      return result.name + '';
    },

    submitAutocomplete(result) {
      this.quantityAdd({
        id: result.id,
        name: result.name,
        price: result.price,
        quantity: parseInt(1),
        prices: result.prices,
        cecina: (result.cecina)?true:false,
             stock: result.stock
      });

      this.$refs.productAutocomplete.setValue('');

    },

    // Orden anterior
    async lastOrder(){
      this.offOn = true;
      if(!this.lastSell){
        this.$awn.alert('No existe una venta anterior');
        return false;
      }
      Loader.dinamic();
      var request = await this.$store.dispatch('sells/getSell', this.lastSell);
      Loader.hide();
      if(!request.success){
        this.$awn.alert(request.data);
        this.inputElement.focus();
        return false;
      }else{
        this.dataDetail = request.data;
      }
      $('#detailSell').modal('show');
      this.offOn = false;
    },

    // Modal para verificar monto mayor a USD 5000
    openVerify(thing){
      this.propVerify = {
        params: thing,
        title: 'Confirmacion de venta',
        text: '¿Usted esta seguro de realizar esta venta por un monto de '+ this.total + ' pesos chilenos?',
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
      if(this.gananciaInstalled){
        if (!data.ganancia) data.ganancia = 0; //Esto antes era (this.gananciaInstalled && data.ganancia); pero creo asi es mas correcto -feredev
        data.ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
      }
      this.productoSend.push(data);
      // Ejecuto #calculatePlus ✅
      this.calculatePlus(i,data,(this.priceUnitaryInstalled && this.priceUnitary)?true:false);
    },

    quantityAdd(data) { //#fere-warp1

      // Precio Variante Anterior
      if (!data.LastVariantPrice) data.LastVariantPrice = null;
      //#if (!data.LastVariantPrice) data.LastVariantPrice = 0;

      // Si hay mas de un producto agregado
      if(this.productoSend.length != 0){

          var encontrado = false;
          // Recorro el arreglo de productos
          for (var i = 0; i < this.productoSend.length; i++) {

            // Si encuentra el producto
            if(data.id == this.productoSend[i].id){
              // La cantidad es la antigua cantidad + la nueva cantidad ✅
              this.productoSend[i].quantity = parseFloat(this.productoSend[i].quantity) + parseFloat(data.quantity);
              // El subtotal es la cantidad actual por el nuevo precio que le envio (#subtotal)
              this.productoSend[i].subtotal = this.productoSend[i].quantity * parseFloat(data.price);
              // Si la ganancia esta instalada, agrego la ganancia ✅
              if(this.gananciaInstalled){
                if (!data.ganancia) data.ganancia = 0; //🤔
                this.productoSend[i].ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
              }
              // Ejecuto #calculatePlus ✅
              this.calculatePlus(i,this.productoSend[i],(this.priceUnitaryInstalled && this.priceUnitary)?true:false);
              encontrado = true;
            }

          }
          // Si no ha encontrado el producto
          if(!encontrado)
            this.addProductQuantity(i, data);

      }else{ // Si no hay mas de un producto
        this.addProductQuantity(0, data);
      }

      $('#quantityProduct').modal('hide');
      this.inputElement.focus();
    },

    AddProduct(data){
      this.productQuantity = data;
      $('#quantityProduct').modal('show');
    },

    //Agregar Ganancia y SubTotal en precio variante
    addGainSubTotalVariantPrice(index, isCecina = false, precioActual, cantidad, gananciaActual) { //No tengo creatividad para nombres bonitos justo ahora...

      //addGainSubTotalVariantPrice(index, data.cecina, precioActual, cantidad, gananciaActual);
      //index ---> this.productoSend[index]
      //isCecina
      //precioActual
      //cantidad
      //gananciaActual

      if (this.cecinaInstalled && isCecina){
        this.productoSend[index].subtotal = (precioActual / 1000) * cantidad;
        if(this.gananciaInstalled){
          this.productoSend[index].ganancia = (parseFloat(gananciaActual) / 1000) * cantidad;
        }
      }else{
        this.productoSend[index].subtotal = precioActual * cantidad;
        if(this.gananciaInstalled){
          this.productoSend[index].ganancia = parseFloat(gananciaActual) * cantidad;
        }
      }


      //PD: hay una forma de reducir esto mas todavia pero no me quiero arriesgar

    },

    // Calculate plus //#fere-warp1
    calculatePlus(index, data, unitary_price = false) {
      // Obtengo el producto
      var productActual = Object.assign({},this.products.find(element => element.id == data.id));

      var precioDeEntrada = this.productoSend[index].price;
      var precioVarianteDiferenteDeUnitario = false;

      if(unitary_price) productActual.price = this.productoSend[index].price;

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

            if (precios[p+1]) {
              var pCantidad = precios[p].cantidad;
              var pNextCantidad = precios[p+1].cantidad;

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
            }else{

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
      }else{ // No recorro los precios variantes si no que uso un solo precio...
        this.addGainSubTotalVariantPrice(index, data.cecina, productActual.price, cantidad, productActual.ganancia);
      }

      this.productoSend[index].precioAnterior = this.productoSend[index].price;

      this.productoSend[index].quantity = cantidad;
      this.calculateTotal();

    },
    calculateTotal(){
      this.total = 0;
      this.gananciaTotal = 0;
      for (var i = 0; i < this.productoSend.length; i++) {
        var price = 0;
        var ganancia = 0;
        price = this.productoSend[i].subtotal;
        this.total = parseFloat(this.total) + parseFloat(price);

        if(this.gananciaInstalled){
          ganancia = this.productoSend[i].ganancia;
          this.gananciaTotal = this.gananciaTotal + parseFloat(ganancia);
        }
      }
      this.inputElement.focus(); //<---- Hacer focus aqui es lo que causa el verdadero problema
    },
    deleteProduct(index){
      this.productoSend.splice(index, 1);
      if(this.productoSend.length == 0) {
        this.order = false;
        this.editOrder = false;
      }
      this.calculateTotal();
    },
    getImage(image){
      return BaseUrl.getUrl('images/'+image);
    },
    closeLastOrder(){
      $('#quantityProduct').modal('hide');
      $('#detailSell').modal('hide');
      this.inputElement.focus();
    },
  },
  computed:{
    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input',offOn) }
    },
    sell_total:{
      get(){ return this.$store.main.sell_total },
      set(val){ this.$store.commit('main/setProperty', {key:'sell_total', data: val})}
    },
    // Permisos para las ventas
    settingBoleta:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta');
    } },
    settingBoletaLocal:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local');
    } },
    settingFactura:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.factura');
    } },
    settingDebito:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.debito');
    } },
    cantidadDecimalesSubModules:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.cantidades_float');
    } },
    lastSell:{ get(){ return this.$store.getters['sells/sellPast']; } },
    productsGet:{ get(){ return ConfigHelper.HavePermission('productos_obtener'); } },
    sellCreate:{ get(){ return ConfigHelper.HavePermission('crear_venta'); } },
    tickets:{ get(){ return ConfigHelper.HavePermission('getionar_tickets'); } },
    priceUnitary:{ get(){ return ConfigHelper.HavePermission('precio_unitario'); } },
    newProductSell:{ get(){ return ConfigHelper.HavePermission('crear_productos_nueva_venta'); } },
    ticketInstaller:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket'); } },
    turned_ventas:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.turned_ventas'); } },
    cafeteriaInstaller:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria'); } },
    watchTicket:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ver_ticket'); } },
    clientsInstaller:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes'); } },
    cecinaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_cecina'); } },
    gananciaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_ganancia'); } },
    //permitir_add_producto
    permitir_add_producto:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_crear_producto_en_venta'); } },
    priceUnitaryInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_precio_unitario'); } },
    clientsPhone:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono'); } },
    clientsObligatorio:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_obligatorio'); } },
    boards:{ get(){ return this.$store.getters['cafeteria/getBoardsFree'] } },
    waiters:{ get(){ return this.$store.getters['waiters/getAllWaiters'] } },
    filteredList: {
      get(){
        clearTimeout(this.timeoutT);

        const lowerCaseProductSearch = this.productSearch.toLowerCase();
        const _this = this;
        // Bsucador de codigo de barras y abridor de ordenes directo
        this.timeoutT = setTimeout(function () {
          var findProduct = (_this.products) ? _this.products.find(element => element.barcode == _this.productSearch) : null;

          // 12 es creo el numero de serial de un ticket o algo asi
          if (_this.productSearch.length == 12 && findProduct == undefined) _this.getSell(_this.productSearch);

          if (findProduct) {
            _this.quantityAdd({
              id: findProduct.id,
              name: findProduct.name,
              price: findProduct.price,
              quantity: parseInt(1),
              prices: findProduct.prices,
              cecina: (findProduct.cecina)?true:false,
              stock: findProduct.stock
            });
            _this.productSearch = '';
            _this.$refs.productAutocomplete.setValue('');
          }

          if (findProduct == undefined && _this.filteredList.length == 0){
            _this.productSearch = '';
            _this.$refs.productAutocomplete.setValue('');
            _this.$awn.info('Sin resultados');
          }

        }, 50);

        const filterFields = this.filters;

        return this.products.filter(product => {
          for (const filter of filterFields) {
            // console.log('Esta buscando por, ',filter,' -> ',product[filter]);
            if (product[filter]) {
              const response = product[filter].toLowerCase().includes(lowerCaseProductSearch);
              if (response) return response;
            }
          }
          return false;
        });
      }
    },
    productsIsDefined: {
      get(){ return this.products; }
    },
    haveProducts: {
      get(){ return this.productsIsDefined && this.products.length; }
    }
  }
}
</script>

<style scoped>
  .btns_sell{
    display: flex;
    align-items: center;
    padding: 5px 0px 0px 0px;
  }
  .hv-80{
    height: 80vh;
  }
  .btnProduct{
    cursor: pointer;
  }
  .borderedB{
    border-bottom: 1px solid #ccc;
  }
  .bordertop{
    border-top: 1px solid #ccc;
  }
  .desingInput{
    width: 70px;
    min-width: 100%;
    background: none;
    border-radius: 3px;
    border: 1px solid #0B4F6C;
    padding: 4px 2px;
    text-align: center;
  }
  .desingInput2{
    width: 100px;
    min-width: 100%;
    background: none;
    border-radius: 3px;
    border: 1px solid #0B4F6C;
    padding: 4px 2px;
  }
  .btn-perzon{
    font-size: 14px;
    width: 23.5px !important;
    height: 23.5px !important;
    padding: 0px;
    border-radius: 50px;
    border: 1px solid #0B4F6C;
    color: #0B4F6C !important;
    box-shadow: none !important;
  }
  .btnRadius{
    font-size: 18px;
    border-radius: 2px !important;
  }
  .bg-grey{
    border-radius: 2px;
    margin-left: 1.5px;
    background: #dedada;
  }
  .h-87{
    min-height: 87vh !important;
  }
  .scroll-h{
    height: 90vh !important;
    overflow-y: scroll;
    overflow: auto;
  }
  ::-webkit-scrollbar{
    width:4px;
  }
  ::-webkit-scrollbar-track{
    background:#c0c0c0;
    border-radius:50px;
  }
  ::-webkit-scrollbar-thumb{
    background:#000000;
    border-radius:50px;
  }
  .h-70{
    height: 45vh;
    overflow: auto;
  }
  .h-30{
    height: 45vh;
  }
  .btn-r0{
    padding: 20px 10px;
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
</style>
