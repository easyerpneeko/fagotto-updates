<template>
  <div class="modal fade" id="modalCatalog" tabindex="-1" role="dialog" aria-labelledby="modalCatalog" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog lg-modal modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primario">
        <h5 class="modal-title">Catalogo</h5>
        <button type="button" class="close text-white" @click="closeModal(false)" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0" style="overflow: auto; max-height: 70vh;">
        <div class="d-flex flex-wrap">
          <div v-if="(categoriesInstalled && Allcategories && Allcategories.length > 0)" class="col-md-3 boxCategories">
            <h5 class="text-primario my-2 text-center">
              Categorias
            </h5>
            <div @click="changeCategorie(categorie.id)" :class="['btnCategorieSelect text-center', (categorieNow == categorie.id) ? 'btnCategorieSelect_active' : '' ]" v-for="(categorie, index) in Allcategories" :key="index">
              {{categorie.name}}
            </div>
          </div>
          <div :class="['boxProducts mt-2 ', (categoriesInstalled && Allcategories && ((Allcategories.length > 0 && jsonTable.items.length == 0) || (Allcategories.length == 0 && jsonTable.items.length > 0))) ? 'col-md-9' : 'col-md-5' ]">
            <div class="d-flex flex-wrap">
              <div class="col-12">
                <div class="autocomplete-input-container">
                  <autocomplete :search="search" placeholder="Buscar" :getResultValue="getSearchValue" @submit="submitAutocomplete" ref="productAutocomplete" ></autocomplete>
                </div>
              </div>
              <div v-for="(product, index) in filteredList" :key="index" v-if="(products && products.length > 0 && (!cecinaInstalled || (cecinaInstalled && product.cecina)))" class="col-md-4 col-sm-6 col-12">
                <card-product-orders :product="product" @clickEmit="AddProduct" />
              </div>
              <div v-else class="col-12 text-center mt-5 pt-5">
                <h2>Sin resultados</h2>
              </div>
            </div>
          </div>
          <div v-if="jsonTable.items.length > 0" class="col-md-4 boxProductsSend">

            <div class="descriptionTextContainer mb-2" v-if="ticket_sell_client">
              <label>Nombre del cliente</label>
              <textarea v-model="clientTicket"></textarea>
            </div>
            <div>

              <custom-table v-model="jsonTable" v-slot="props" v-on:onBlur="FunctionBlurInputEditable">
                <div style="display: inline-flex;">
                  <a class="btn bg-primario text-black btnRemoveProduct mr-1" @click="openCommentProduct(props.item)">
                    <i class="far fa-comment"></i>
                  </a>  
          
                  <a class="btn bg-danger text-white btnRemoveProduct" @click="removeProduct(props.item)">
                    <i class="fas fa-times"></i>
                  </a>
                </div>
              </custom-table>

              <div class="footerTableTicket d-flex justify-content-between">
                <h6 class="">
                  TOTAL
                </h6>
                <h6>
                  {{formatNumber(total)}}$
                </h6>
              </div>
            </div>
            <div class="descriptionTextContainer" v-on:keyup.enter="addCommentProduct(productComment)" v-if="product_comentario">
              <label>Comentario</label>
              <textarea v-model="product_comentario_text"></textarea>
            </div>
            <div class="descriptionTextContainer" v-if="ticket_description">
              <label>Descripcion</label>
              <textarea v-model="ticketDescription"></textarea>
            </div>

          </div>
        </div>
      </div>

      <div class="modal-footer">

        <button type="button" class="btn bg-dark text-white" @click="closeModal(false)">
          {{ ( board && board.order == null || !board ) ? 'Cerrar' : 'Volver' }}
        </button>

        <!-- Si hay una mesa seleccionada... -->
        <template>
          <span v-if="!ver_ticket" class="m-0 p-0">
            <button v-if="ticket_sell && settingBoletaLocal" type="button" class="btn bg-primario text-white" @click="viewTicket('boleta_local')">
              Ticket + Efectivo
            </button>
            <button v-if="ticket_sell && settingBoleta" type="button" class="btn bg-dark text-white" @click="viewTicket('boleta')">
              Ticket + boleta (SII)
            </button>
            <button v-if="settingDebito" @click="viewTicket('debito')" type="button" class="btn bg-primario text-white">
              Ticket + Debito
            </button>
            <button v-if="settingTransferencia" @click="viewTicket('transferencia')" type="button" class="btn bg-primario text-white">
              Ticket + Transferencia
            </button>
            <button v-if="settingRappi" @click="viewTicket('rappi')" type="button" class="btn bg-primario text-white">
              Ticket + Rappi
            </button>
            <button v-if="settingNotaCredito" @click="viewTicket('credito')" type="button" class="btn bg-primario text-white">
              Ticket + Credito
            </button>
            <button v-if="settingCheque" @click="viewTicket('cheque')" type="button" class="btn bg-primario text-white">
              Ticket + Cheque
            </button>
            <button v-if="settingBanco" @click="viewTicket('banco')" type="button" class="btn bg-primario text-white">
              Ticket + Banco
            </button>
            <button v-if="settingSodexo" @click="viewTicket('sodexo')" type="button" class="btn bg-primario text-white">
              Ticket + Sodexo
            </button>
            <button v-if="settingAmipass" @click="viewTicket('amipass')" type="button" class="btn bg-primario text-white">
              Ticket + Amipass
            </button>
            <button v-if="settingMulticaja" @click="viewTicket('multicaja')" type="button" class="btn bg-primario text-white">
              Ticket + Multicaja
            </button>
            <button v-if="ticket_sell_close" type="button" class="btn bg-primario text-white" @click="viewTicket('ticket_venta')">
              Ticket + Cerrar venta
            </button>

            <button v-if="ticket_sell && settingFactura" type="button" class="btn bg-secundario text-white" @click="viewTicket('factura')">
              Ticket + factura
            </button>
            <button type="button" class="btn bg-primario text-white" @click="viewTicket('ticket')">
              Crear ticket
            </button>


          </span>
          <button v-else @click="viewTicket(false)" type="button" class="btn bg-primario text-white">
            Ver ticket
          </button>
        </template>

        <!-- Si hay un mesero seleccionado y solo eso... (Deshabilitado)-->
        <!-- <template v-if="onlyWaiter && false">
          <button v-if="ticket_sell && settingBoletaLocal" type="button" class="btn bg-primario text-white" @click="viewTicket('boleta_local')">
            Boleta local
          </button>
        </template> -->

      </div>

    </div>
  </div>
  <modalVerify :propVerify="propVerify" @refreshData="refreshData"/>
  <ticket :typeCreateTicket="typeCreateTicket" v-model="ticketData" @changeValue="changeValue" @closeModal="closeModal" />
</div>
</template>

<script>
// Componentes
import customTable from '@/components/tables/table.vue';
import cardProductOrders from '@/components/cards/card_product_orders.vue';
import ticket from '@/components/modals/cafeteria/createTicket.vue';
import modalVerify from '@/components/modals/verifyDelete.vue';

// Helpers y plugins
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';

export default {
  data(){
    return{
      product_comentario: false,
      product_comentario_text: '',
      product_comentario_id: '',
      typeCreateTicket: false,
      categorieNow: null,
      productsRequest: [],
      productComment: {},
      ticketData: {},
      ticketDescription: '',
      clientTicket: '',
      // data de nueva venta
      products: [],
      productSearch: '',
      productoSend: [],
      filters:['name','barcode'],
      total: 0,
      timeoutT: null,
      gananciaTotal: null,
      propVerify: null,
      order: false,
      editOrder: false,
      type_sell: null,
      jsonTable: {
        btn: true,
        items: [],
        rows:[
          {key:'name', class:'', permission:'default'},
          {key:'quantity', class:'text-center', permission:'default', edit:true},
          {key:'comment', class:'text-center', permission:'default'},
        ],
        titles:[
          {label:'Nombre', class:'', permission:'default', type:false},
          {label:'Cantidad', class:'text-center w-30', permission:'default', type:false},
          {label:'Cometarios', class:'text-center', permission:'default', type:false},
          {label:'Acciones', class:'text-center', permission:'default', type:false},
        ]
      }
    }
  },
  components:{
    cardProductOrders,

    // Componentes de nueva venta
    modalVerify,
    ticket,
    customTable,
  },
  mounted(){
    //HavePermission
    this.refreshData(false, true);
    this.product_comentario = false;

  },
  methods:{
    // tickets (require board)

    //JC FECHA 2022-12-14
    viewTicket(val = false){
      if(this.productoSend.length == 0){
        this.$awn.alert("Es necesario agregar algun producto");
        return false;
      }
      if(this.gananciaInstalled && (this.gananciaTotal == null || this.gananciaTotal == '')) this.gananciaTotal = 0;

      // Datos basicos
      this.ticketData = {
        products: this.productoSend,
        total: this.total,
        gananciaTotal: this.gananciaTotal
      }
      if (this.ticket_description) {
        this.ticketData.description = this.ticketDescription;
      }
      if (this.ticket_sell_client) {
        this.ticketData.client_ticket = this.clientTicket;
      }

      console.log('SEARCH ticketData', this.ticketData);

      if (this.board && this.board.waiter && this.board.waiter.id) {
        // Si hay una mesa seleccionada y un mesero en dicha mesa
        this.ticketData.board_id = this.board.id;
        this.ticketData.waiter_id = this.board.waiter.id;
      }else if (this.onlyWaiter) {
        // Si solo contamos con un mesero pero no una mesa
        this.ticketData.board_id  = null;
        this.ticketData.waiter_id = this.onlyWaiter;
      }else {
        this.$awn.alert("Error ni mesero ni mesa seleccionada.");
        return false;
      }

      if (this.board && this.board.order) this.ticketData.order = this.board.order;
      else this.ticketData.order = false;

      if(val) this.typeCreateTicket = val;
      $('#createTicket').modal('show');

    },

    closeModal(refresh = false){

      // Borramos el Mesero actual en caso de estar seleccionado un OnlyWaiter (para el modo garzon)
      this.$store.commit('cafeteria/clearOnlyWaiter');

      this.typeCreateTicket = false;

      
      if (this.board && this.board.order == null && refresh == false) {

        this.board.waiter = null;
        $('#modalCatalog').modal('hide');

        // Deseleccionamos la mesa
        this.$store.commit('cafeteria/clearBoard');

        //Cuando cierre el modal sin tener ninguna mesa con orden activa limpio las 2 variables que almacena productos @jesus
        this.productoSend = [];
        this.jsonTable.items = [];

        return;
      }

      // Deseleccionamos la mesa
      this.$store.commit('cafeteria/clearBoard');

      this.productoSend = [];
      this.ticketDescription = '';
      this.clientTicket = '',
      this.total = null;
      this.gananciaTotal = null;
      $('#createTicket').modal('hide');
      $('#modalCatalog').modal('hide');
      if(refresh != false){
        this.jsonTable.items = [];
        $('#completeOrder').modal('hide');
        this.$emit('refresh', true);
      }

    },
    changeValue(values){
      this.productoSend = values.products;
      this.total = values.total;
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
    async refreshData(notRefreshCart = false, loaderTrue = false){
      // Iniciando refrescamiento (carga y botones disabled)
      this.product_comentario = false;
      if (!notRefreshCart)
        this.productoSend = [];

      this.ticketDescription = '';
      this.clientTicket = '';
      this.total = 0;
      if (this.tickets && this.productsGet) {
        // Iniciando peticion
        if(!loaderTrue) Loader.dinamic();
        var request = await this.$store.dispatch("products/getProductsOfSell");
        if(!loaderTrue) Loader.hide();
        // Verificando respuesta
        if (request.success){
          this.products = (request.data.length == 0) ? false : request.data;
          this.productsRequest = request.data;
        }
        else this.$awn.alert('Error al obtener los productos');
      }else{
        this.$router.push('/inicio');
      }
    },

    // Cambiar de categoria
    changeCategorie(id){
      if(this.categorieNow != id) this.categorieNow = id;
      else this.categorieNow = null;

      if(this.categorieNow != null){
        var productsFind = [];
        productsFind = this.productsRequest.filter(product => {
          if(product.category == this.categorieNow){
            return product;
          }
          return false;
        });
        this.products = productsFind;
      }else{
        this.products = this.productsRequest
      }
    },

    search(input) {
      this.productSearch = input;
      if (input != null && input != '' && input.length < 1) { return [] }
      var productsFind = this.products.filter(product => {
        var index = 0;
        for (var i = 0; i < product.name.length; i++) {
          if(product.name.toLowerCase().startsWith(input.toLowerCase(), i)){
            index = i;
          };
        }
        return product.name.toLowerCase().startsWith(input.toLowerCase(), index);
      })
      return productsFind.filter(product => product.cecina == false);
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
      });

      this.$refs.productAutocomplete.setValue('');
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
      this.jsonTable.items = this.productoSend;
      // Ejecuto #calculatePlus ✅
      this.calculatePlus(i,data,(this.priceUnitaryInstalled)?true:false);
    },

    quantityAdd(data) { //#fere-warp1

      // Precio Variante Anterior
      if (!data.LastVariantPrice) data.LastVariantPrice = null;
      //#if (!data.LastVariantPrice) data.LastVariantPrice = 0;

      // Si hay mas de un producto agregado
      if(this.productoSend.length != 0){
          var encontrado = false;
          // Recorro el arreglo de productos
          let conteo=0;
          for (var i = 0; i < this.productoSend.length; i++) {

            // Si encuentra el producto
            if(data.id == this.productoSend[i].id){
              // La cantidad es la antigua cantidad + la nueva cantidad ✅

              if(data.desde=="inputedit"){
                this.productoSend[i].quantity = parseFloat(data.quantity);
              }else{
                this.productoSend[i].quantity = parseFloat(this.productoSend[i].quantity) + parseFloat(data.quantity);

              }

              // El subtotal es la cantidad actual por el nuevo precio que le envio (#subtotal)
              this.productoSend[i].subtotal = this.productoSend[i].quantity * parseFloat(data.price);
              // Si la ganancia esta instalada, agrego la ganancia ✅
              if(this.gananciaInstalled){
                if (!data.ganancia) data.ganancia = 0; //🤔
                this.productoSend[i].ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
              }
              // Ejecuto #calculatePlus ✅
              this.calculatePlus(i,this.productoSend[i],(this.priceUnitaryInstalled)?true:false);
              encontrado = true;
              conteo++;
            }

          }


          // Si no ha encontrado el producto
          if(!encontrado){
            this.addProductQuantity(this.productoSend.length, data);
          }

      }else{ // Si no hay mas de un producto
        this.addProductQuantity(0, data);
      }

    },

    AddProduct(data){
      let price = 0;
      if(data.prices){
        if (data.prices.length) {
          var precios = data.prices;
          for (var i = 0; i < precios.length; i++) {
            if (precios[i+1]) {
              if (1 >= parseFloat(precios[i].cantidad) && 1 < parseFloat(precios[i+1].cantidad)) {
                price = precios[i].precio;
                break;
              }
            }else{
              price = precios[i].precio;
            }
          }
        }else{
          price = data.price;
        }
      }else{
        price = data.price;
      }
      var product = {
        id: data.id,
        name: data.name,
        price: price,
        quantity: 1,
        prices: data.prices,
        cecina: (data.cecina)?true:false,
        ganancia: data.ganancia,
        comment: null
      };
      this.quantityAdd(product);
    },

    removeProduct(item){
      console.log("Producto a removeeerrr", item);
      for (var i = 0; i < this.productoSend.length; i++) {
        if(this.productoSend[i].id == item.id) this.productoSend.splice(i, 1);
      }
      this.product_comentario = false;
      this.jsonTable.items = this.productoSend;
      this.calculateTotal();
    },
    openCommentProduct(item){
      this.product_comentario_text = item.comment;
      this.product_comentario_text != null ? item.comment : null;
      this.product_comentario_id = item.id;
      this.product_comentario = this.product_comentario = !this.product_comentario;

      if (this.product_comentario) this.productComment = {  id: item.id }
      return;
    },
    addCommentProduct(item){
      this.product_comentario = this.product_comentario = !this.product_comentario;
      console.log(item.id);
      console.log(this.product_comentario_text);
      console.log(this.productoSend);
      
      for (var i = 0; i < this.productoSend.length; i++) {
        if(this.productoSend[i].id == item.id) {
          this.productoSend[i].comment = this.product_comentario_text;
        }
      }
      this.jsonTable.items = this.productoSend;
      
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
      console.log("calculandoo totalll");
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
    },

    FunctionBlurInputEditable(prod){
        prod.desde="inputedit";
        this.quantityAdd(prod);
    }
    
  },
  computed:{

    board:{
      get(){ return this.$store.getters['cafeteria/getBoard'] },
      set(value){ this.$store.commit('cafeteria/setProperty', {key: 'board', data: value}) }
    },
    onlyWaiter:{
      get(){ return this.$store.getters['cafeteria/getOnlyWaiter'] },
      set(value){ this.$store.commit('cafeteria/setProperty', {key: 'onlyWaiter', data: value}) }
    },//onlyWaiter

    categoriesInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.submodulos.categorias'); } },
    Allcategories:{ get() { return this.$store.getters['products/categories'];} },

    // Computeds de nueva venta
    productsGet:{ get(){ return ConfigHelper.HavePermission('productos_obtener'); } },
    tickets:{ get(){ return ConfigHelper.HavePermission('getionar_tickets'); } },
    cecinaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_cecina'); } },
    gananciaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_ganancia'); } },
    priceUnitaryInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_precio_unitario'); } },
    // Ajuste de procesar venta con ticket
    ticket_sell:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell'); } },
    ticket_sell_close:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell_proccess_close'); } },
    // Previsualizacion de la orden
    ver_ticket:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ver_ticket'); } },

    // Permitir descripcion en ticket
    ticket_description:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description'); } },
    //permitir cliente en ticket
    ticket_sell_client:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell_client'); } },


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
    settingRappi:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.rappi');
    } },
    settingNotaCredito:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.nota_de_credito');
    } },
    settingEfectivo:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.efectivo');
    } },
    
    settingDebito:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.debito');
    } },

    settingTransferencia:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.transferencia');
    } },

    settingCheque:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.cheque');
    } },

    settingBanco:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco');
    } },

    settingSodexo:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.sodexo');
    } },

    settingAmipass:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.amipass');
    } },
    settingMulticaja:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.multicaja');
    } },


    filteredList:{
      get(){
        clearTimeout(this.timeoutT);

        const _this = this;
        this.timeoutT = setTimeout(function () {
          var findProduct = (_this.products) ? _this.products.find(element => element.barcode == _this.productSearch) : null;
          if (findProduct) {
            _this.quantityAdd({
              id: findProduct.id,
              name: findProduct.name,
              price: findProduct.price,
              quantity: parseInt(1),
              prices: findProduct.prices,
              cecina: (findProduct.cecina)?true:false,
            });
            _this.productSearch = '';
            _this.$refs.productAutocomplete.setValue('');
          }
          if(findProduct == undefined && _this.filteredList.length == 0){
            _this.productSearch = '';
            _this.$refs.productAutocomplete.setValue('');
            _this.$awn.info('Sin resultados');
          }
        }, 200);
        if(!this.products || this.products.length == 0 || this.products == undefined) return false;
        return this.products.filter(product => {

          for (var filter of this.filters) {
            if (product[filter]) {
              var response = product[filter].toLowerCase().includes(this.productSearch.toLowerCase());
              if (response) return response;
            }
          }
          return false;

        });
      },
    }
  },
}
</script>

<style media="screen">
  .descriptionTextContainer {
    display: flex;
    flex-direction: column;
  }

  .descriptionTextContainer label {
    flex: 1;
  }

  .descriptionTextContainer textarea {
    flex: 1;
  }
   
  .btnRemoveProduct{
    padding: 0px !important;
    border-radius: 50% !important;
    height: 20px !important;
    width: 20px !important;
    font-size: 14px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
  }
  .modalForce{
    background: rgba(0,0,0,.8) !important;
  }
  .inputSearch{
    margin: 10px 0px;
    width: 100%;
    max-width: 500px;
  }
  .autocomplete-input-container {
      flex: 1;
      max-width: 550px;
  }
  .boxCategories{
    transition: .4s all ease !important;
    height: 68vh;
    overflow: overlay;
    padding: 0px !important;
  }
  .boxProductsSend{
    transition: .4s all ease !important;
    height: 68vh;
    overflow: overlay;
    padding: 0px !important;
  }
  .boxProducts{
    transition: .4s all ease !important;
    height: 68vh;
    overflow: overlay;
  }
  .btnCategorieSelect{
    padding: 10px 5px;
    width: 100%;
    border: 1px solid var(--primary);
    color: var(--primary);
    background: transparent;
    font-weight: bold;
    break-after: always;
    user-select: none;
    cursor: pointer;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }
  .btnCategorieSelect_active{
    background: var(--primary);
    color: #fff;
  }
  .btnCategorieSelect:hover{
    background: var(--primary);
    color: #fff;
  }
  @media (max-width: 767px){
    .boxCategories{
      display: none !important;
    }
    .boxProductsSend{
      height: 165px;
    }
    .boxProducts{
      height: 320px;
    }
  }
</style>
