<template>
  <div class="modal fade modalForce" id="completeOrder" tabindex="-1" role="dialog" aria-labelledby="completeOrder" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog lg-modal modal-dialog-centered" role="document">
      <div v-if="board && board.order && board.waiter" class="modal-content">
        <div class="modal-header bg-primario">
          <h5 class="modal-title text-capitalize">Orden #{{board.order.id}} - {{board.name}} - {{hora_local()}}</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="d-flex flex-wrap boxCompleteOrder">
            <div class="col-md-3 col-12 d-flex flex-column justify-content-between mdDisplay">
              <div class="d-flex flex-column text-center">
                <div :class="tiempo_class+' tiempo-flag-order'"></div>
                <h5 class="text-dark text-bold">Tiempo: {{tiempo_abierto(board.order.created_at)}} minutos</h5>
              </div>
              <div class="d-flex flex-column text-center">
                <h5 class="text-dark text-bold">Mesero actual</h5>
                <h6 class="text-primario">{{board.waiter.name}}</h6>
              </div>
              <div class="d-flex flex-column text-center" v-if="ticket_description">
                <h5 class="text-dark text-bold">Descripcion</h5>
                <h6 class="text-primario">{{board.order.description}}</h6>
              </div>
              <div class="d-flex flex-column w-100">
                <div class="form-group row">
                  <label for="PropinaMesero" class="col-sm-12 col-form-label">$ Propina / %</label>
                  <div class="input-group-prepend">
                    <div class="input-group-text">$</div>
                  </div>
                  <input v-model="propina" type="number" class="form-control col-sm-4" id="PropinaMesero" placeholder="Propina" @input="propina_porCantidad" />
                  <div class="input-group-prepend">
                    <div class="input-group-text">%</div>
                  </div>
                  <input v-model="propina_porcentaje" type="number" class="form-control col-sm-4" id="PropinaPorcentaje" placeholder="%"  @input="propina_porPorcentaje" />
                  
                </div>

                <div class="form-group">
                  <label for="PropinaMesero">Descuento</label>
                  <input v-model="descuento" type="number" class="form-control" id="DescuentoCompra" placeholder="Descuento" >
                </div>
                
                <button @click="printOrderTotal" type="button" class="btn mx-0 my-1 text-center bg-secundario text-white no-caps">Imprimir ticket total</button>
                <button @click="printTicket" type="button" class="btn mx-0 my-1 text-center bg-dark text-white no-caps">Imprimir ticket</button>
                <button @click="addProducts" type="button" class="btn mx-0 my-1 text-center bg-primario text-white no-caps">Agregar más productos</button>
                <button @click="changeWaiter" type="button" class="btn mx-0 my-1 text-center bg-dark text-white no-caps">Cambiar mesero</button>
                <button @click="changeBoard" type="button" class="btn mx-0 my-1 text-center bg-primario text-white no-caps">Cambiar mesa</button>
                <button @click="removeProduct('all')" type="button" class="btn mx-0 my-1 text-center bg-dark text-white no-caps">Eliminar todos</button>
              </div>
            </div>
            <div class="col-md-9 col-12 d-flex flex-column justify-content-between">
              <div class="">
                <div class="d-flex flex-column text-center displaymd">
                  <h5 class="text-dark text-bold">Mesero actual</h5>
                  <h6 class="text-primario">{{board.waiter.name}}</h6>
                </div>
                <custom-table v-model="jsonTable" @changeValue="changeValue" v-slot="props">
                  <a @click="removeProduct(props.index)" class="py-1 px-2 text-center btn bg-primario" href="#">
                    <i class="fas fa-times"></i>
                  </a>
                </custom-table>
                <div class="footerTableTicket d-flex justify-content-between">
                  <h5 class="">
                    SUBTOTAL
                  </h5>
                  <h5 id = "SubTotal-On-CompleteOrder">
                    ${{formatNumber(subtotal)}}
                  </h5>
                </div>
                <div class="footerTableTicket d-flex justify-content-between">
                  <h5 class="">
                    TOTAL
                  </h5>
                  <h5 id = "Total-On-CompleteOrder">
                    ${{formatNumber(total)}}
                  </h5>
                </div>
              </div>
              <div class="d-flex flex-column w-100 displaymd mt-2">
                <button @click="printOrderTotal" type="button" class="btn mx-0 my-1 text-center bg-secundario text-white no-caps">Imprimir ticket total</button>
                <button @click="printTicket" type="button" class="btn mx-0 my-1 text-center bg-dark text-white no-caps">Imprimir ticket</button>
                <button @click="addProducts" type="button" class="btn mx-0 my-1 text-center bg-primario text-white no-caps">Agregar más productos</button>
                <button @click="changeWaiter" type="button" class="btn mx-0 my-1 text-center bg-dark text-white no-caps">Cambiar mesero</button>
                <button @click="changeBoard" type="button" class="btn mx-0 my-1 text-center bg-primario text-white no-caps">Cambiar mesa</button>
              </div>
            </div>
          </div>
        </div>
        <div v-if="sellCreate" class="modal-footer">
          <button v-if="settingBoleta" @click="verifyClient('boleta')" type="button" class="btn bg-dark text-white">
            Boleta (SII)
          </button>
          <button v-if="settingBoletaLocal" @click="verifyClient('boleta_local')" type="button" class="btn bg-secundario text-white">
            Efectivo
          </button>
          <button v-if="settingBank" @click="verifyClient('boleta_local', 'banco')" type="button" class="btn bg-secundario text-white">
            Trasnbank
          </button>
          <button v-if="settingAmipass" @click="verifyClient('amipass')" type="button" class="btn bg-primario text-white">
            AMIPASS
          </button>
          
          <button v-if="settingMulticaja" @click="verifyClient('multicaja')" type="button" class="btn bg-primario text-white">
            Multicaja
          </button>
          
          <button v-if="settingEdenred" @click="verifyClient('edenred')" type="button" class="btn bg-primario text-white">
            Edenred 
          </button> 
          
          <button v-if="settingTransferencia" @click="verifyClient('transferencia')" type="button" class="btn bg-secundario text-white">
            Transferencia
          </button>
          
          <button v-if="settingSodexo" @click="verifyClient('sodexo')" type="button" class="btn bg-primario text-white">
            Sodexo
          </button>
          
          <button v-if="settingConvenio" @click="verifyClient('convenio_empresa')" type="button" class="btn bg-primario text-white">
            Convenio
          </button>

          <button v-if="settingRappi" @click="verifyClient('rappi')" type="button" class="btn bg-primario text-white">
            Rappi
          </button>
          <button v-if="settingUber" @click="verifyClient('uber')" type="button" class="btn bg-primario text-white">
            Uber
          </button>
          <button v-if="settingDebito" @click="verifyClient('debito')" type="button" class="btn bg-primario text-white">
            Debito
          </button>
          
          <button v-if="settingCheque" @click="verifyClient('cheque')" type="button" class="btn bg-dark text-white">
            Cheque
          </button>

          <button v-if="settingCredito" @click="verifyClient('credito')" type="button" class="btn bg-dark text-white">
            Credito
          </button>
          
          <button v-if="settingFactura" @click="verifyClient('factura')" type="button" class="btn bg-primario text-white">
            Factura
          </button>
        </div>
      </div>
    </div>
    <modal-client @sendInfo="sendInfo" />
    <modalVerify :propVerify="propVerify" @refreshData="refreshData"/>
    <password-verify v-model="jsonPassword" @success="removeProduct" />
    <assignBoard />
  </div>
</template>

<script>
// Componentes
import assignBoard from '@/components/modals/cafeteria/assignBoard.vue';
import passwordVerify from '@/components/modals/passwordVerify.vue';
import modalClient from '@/components/modals/client.vue';
import customTable from '@/components/tables/table.vue';
import modalVerify from '@/components/modals/verifyDelete.vue';
// helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';
import Print from '@/helpers/Print.js';
import moment from 'moment';

/*
    Este componente es llamado desde el boardCard al ser presionado, que se encuentra en cafeteria, linea 213
*/
export default {
  components:{ customTable, modalClient, modalVerify, passwordVerify, assignBoard },
  data(){
    return{
      showDropdown: false,
      subtotal:this.total,
      total: 0,
      gananciaTotal: 0,
      propina:0,
      propina_porcentaje:parseFloat(this.propina_porcentaje_default),
      descuento:0,
      modo_de_pago:'',
      type_sell: '',
      other_type: null,
      propVerify: null,
      tiempo_class:"tiempo-flag-0",
      jsonPassword:{},
      jsonTable: {
        btn: true,
        items: null,
        rows:[
          {key:'name', class:'', permission:'default'},
          {key:'price', class:'', permission:'default'},
          {key:'quantity', class:'', permission:'default', edit: true},
          {key:'subtotal', class:'', permission:'default'},
        ],
        titles:[
          {label:'Nombre', class:'', permission:'default', type:false},
          {label:'Precio', class:'', permission:'default', type:false},
          {label:'Cantidad', class:'', permission:'default', type:false},
          {label:'Subtotal', class:'', permission:'default', type:false},
          {label:'', class:'', permission:'default', type:false},
        ]
      }
    }
  },
  mounted() {

    this.$root.$on('show-completeOrder', () => {
            // your code goes here
            this.show()
        })
    

    this.$root.$on('hide-completeOrder', () => {
            // your code goes here
            this.hide()
        })
    

  },
  methods:{
    
    show() {
      /*
        Method to replace $('#completeOrder').modal('show');
        when called from outside/inside this component

        Metodo para reemplazar $('#completeOrder').modal('show');
        cuando llamado desde afuera/adentro de este componente
      */

     // cosas que ocurren al abrir este modal
     //if ($("#PropinaMesero").val() == "") this.calcularPropina()
      //this.calcularDescuento()
      this.descuento = 0;
      this.propina = -1;

      // esta linea es la mas importante y la que le da sentido a este metodo
     $('#completeOrder').modal('show');

    },
    hide() {
      /*
        Method to replace $('#completeOrder').modal('hide');
        when called outside or inside this component

        Metodo para reemplazar $('#completeOrder').modal('hide');
        desde adentro o afuera de este componente.
      */
     $('#completeOrder').modal('hide');
     this.descuento = 0;
     this.propina = 0;
    },
    calcularTip() {
      
    },
    tiempo_abierto(time) {
      
      var tiempo_actual = new Date();
      var tiempo_mesa = new Date(time);
      var fecha1 = moment(tiempo_actual);
      var fecha2 = moment(tiempo_mesa);

      console.log((fecha1.diff(fecha2, 'minutes')), ' min de diferencia');
      var diffMins = fecha1.diff(fecha2, 'minutes') > 0 ? fecha1.diff(fecha2, 'minutes'): 0;
      if (diffMins < 15) {
        this.tiempo_class = "tiempo-flag-1"
        return diffMins;
      } 

      if (diffMins < 30) {
        this.tiempo_class = "tiempo-flag-2"
        return diffMins;
      } 

       if (diffMins < 45) {
        this.tiempo_class = "tiempo-flag-3"
        return diffMins;
      } 

       this.tiempo_class = "tiempo-flag-4"
        return diffMins;

    },
    hora_local(){
      const date = new Date();          
      var hora =  moment(date).format('YYYY-MM-DD hh:mm a');
      return hora; 
    },
    //jc buscar
    verifyClient(type_sell = null, other_type = false){
      console.log("debugger call.")
      this.modo_de_pago = type_sell;
      this.showDropdown = false;
      this.other_type = null;
      this.type_sell = null;

      if(this.jsonTable.items.length == 0){
        this.$awn.alert("Agrege un producto valido");
        return false;
      }

      if(type_sell == 'boleta' || type_sell == 'factura' || type_sell == 'boleta_local' || type_sell == 'amipass' || type_sell == 'edenred' || type_sell == 'multicaja' || type_sell == 'convenio_empresa'){
        if(other_type){
          this.type_sell = type_sell;
          this.other_type = other_type;
          this.sendInfo(false);
        }else{
          this.type_sell = type_sell;
          if(this.clientsInstaller && type_sell != 'boleta' && type_sell != 'boleta_local' && type_sell != 'amipass' && type_sell != 'edenred' && type_sell != 'multicaja' && type_sell != 'convenio_empresa') $('#clientCreate').modal('show');
          else this.sendInfo(false);
        }
      }else{
        this.type_sell = 'other';
        this.other_type = type_sell;
        this.sendInfo(false);
      }
    },
    calcularPropina() {
      
        console.log(":::::::calcularPropina::::::::")
        this.propina = this.total*0.1;
        this.board.order.tip = this.propina;
        $("#PropinaMesero").val(this.propina);
        $("#PropinaMesero").on("keyup", () => {
            //$("#Total-On-CompleteOrder").text(this.total+parseInt($("#PropinaMesero").val()))
            this.propina = ($("#PropinaMesero").val() == "") ? 0 : parseInt($("#PropinaMesero").val());
            this.board.order.tip = this.propina;
            
        })
    },
    calcularDescuento() {
        console.log(":::::::calcularDescuento::::::::")
        $("#DescuentoCompra").on("keyup", () => {
          this.descuento = ($("#DescuentoCompra").val() == "") ? 0 : parseInt($("#DescuentoCompra").val());
          this.board.order.discount = this.descuento;
          //this.$store.commit('cafeteria/setProperty', {key: 'board-discount', data: this.descuento})
          
        })
    },
    propina_porCantidad() {
      setTimeout(() => {
        this.propina_porcentaje = 100 * this.propina / this.subtotal
        this.total = this.propina + this.subtotal - this.descuento;
      },1)
    },
    propina_porPorcentaje() {
      setTimeout(() => {
        this.propina = this.subtotal * this.propina_porcentaje / 100
        this.total = this.propina + this.subtotal - this.descuento;
      },1)
    },
    async sendInfo(client = false){
      $('#clientCreate').modal('hide');

      var dataError = false;
      for (var i = 0; i < this.jsonTable.items.length; i++) {
        if(this.jsonTable.items[i].quantity == null || this.jsonTable.items[i].quantity == ''){
          dataError = true;
        }
      }
      if(dataError){
        this.$awn.alert("Rellene los campos de cantidad");
        return false;
      }
      if(this.gananciaInstalled && (this.gananciaTotal == null || this.gananciaTotal == '')) this.gananciaTotal = 0;

      if(this.clientsInstaller && client){
        
        // api/local/sell
        var data = {
          products: JSON.stringify(this.jsonTable.items),
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
          tip:this.propina,
          discount:this.descuento,
          paymode:this.modo_de_pago,
          //order_id:this.board.order.id,
          gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal,false) : 0
        };
        if(this.board.order) data.order = this.board.order.id;
        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);
      }else{
        // el valor de la boleta local (ticket) que se imprime
        /*
          Esta informacion es la enviada a api/local/sell
          para crear la venta (sell)
        */
        
        var data = {
          products: JSON.stringify(this.jsonTable.items),
          total: this.deFormatNumber(this.total, false),
          tip:this.propina,
          discount:this.descuento,
          paymode:this.modo_de_pago,
          order_id:this.board.order.id,
          gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.gananciaTotal,false) : 0
        };
        if(this.board.order) data.order = this.board.order.id;
        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);
      }



      if(this.type_sell != 'boleta_local' && this.type_sell != 'amipass' && this.type_sell != 'multicaja' && this.type_sell != 'edenred' && this.type_sell != 'convenio_empresa') thing.append('type_sell', this.type_sell);
      if(this.other_type != null && this.other_type != ''){
        if(this.type_sell != 'boleta_local' && this.type_sell != 'amipass' && this.type_sell != 'multicaja' && this.type_sell != 'edenred' && this.type_sell != 'convenio_empresa') thing.append('typeSell', this.type_sell);
        thing.append('other_type', this.other_type);
      }

      if (this.type_sell == 'amipass') {
        thing.set('other_type', 'amipass');
      }

      if (this.type_sell == 'multicaja') {
        thing.set('other_type', 'multicaja');
      }

      if (this.type_sell == 'edenred') {
        thing.set('other_type', 'edenred');
      }

      if (this.type_sell == 'convenio_empresa') {
        thing.set('other_type', 'convenio_empresa');
      }

      if(this.total >= 3862000) this.openVerify(thing); // silocal/ticket la boleta es posiblemente erronea debido a su precio exageradamente alto
      else{
        console.log("TYPE_SELL ENVIADA", this.type_sell);
        // Iniciando peticion
        Loader.fullPage();
        var request = await this.$store.dispatch("sells/newSell", thing);
        console.log("RESPUESTA DE LA APIII COMPLETE ORDEN",request);
        Loader.hide();
        // Verificando respuesta

        if (request.success) {
          if(this.turned_cafeteria && !this.other_type && (this.type_sell == 'boleta' || this.type_sell == 'factura' || this.type_sell == 'boleta_local' || this.type_sell == 'amipass')){
            //this.sell_total = this.total-this.descuento+this.propina;
            this.sell_total = this.total;
            $('#modalTurned').modal('show');
            // aqui va lo que estoy hacinedo xd
             //this.$root.$emit('open-modalTurned', this.total)

            //this.sell_total = this.total-this.descuento+this.propina;
            
            
          }
          this.jsonTable.items = [];
          this.total = 0;
          this.board = null;
          this.$awn.success("Venta realizada con exito",{labels:{success:'CORRECTO'}});

          if(request.data.response_folio == 'boleta' || request.data.response_folio == 'factura') {
            this.$awn.info('El ajuste de '+ request.data.response_folio +' se encuentra desactivado');
          }else if (request.data.response_folio) {
            console.log('Ejecutando impresion...');
            var printPDF = await Print.printBase64(request.data.response_folio);
            this.$emit('refresh', true);
            this.hide(); //$('#completeOrder').modal('hide');
          }else{
            this.hide(); //$('#completeOrder').modal('hide');
            this.$emit('refresh', true);
          }
        }else{
          if(request.data.id){
            if(this.turned_cafeteria && !this.other_type && (this.type_sell == 'boleta' || this.type_sell == 'factura' || this.type_sell == 'boleta_local' || this.type_sell == 'amipass')){
              $('#modalTurned').modal('show');
              this.sell_total = this.total;
            }
            this.jsonTable.items = [];
            this.total = 0;
            this.board = null;
            this.$awn.success("Venta realizada con exito",{labels:{success:'CORRECTO'}});
            this.$awn.info(request.data.response_folio);
            this.$emit('refresh', true);
            this.hide(); //$('#completeOrder').modal('hide');
          }else{
            this.$awn.alert(request.data);
          }
        }
      }
    },

    async printOrderTotal(){
      Loader.fullPage();
      var request = await this.$store.dispatch("cafeteria/printOrderTotal", {
        id:this.board.order.id,
        tip:this.propina,
        discount:this.descuento
      });
      console.log(request);
      Loader.hide();
      if(request.success) var printPDF = await Print.printBase64(request.data);
      else this.$awn.alert(request.data);

    },

    async printTicket(){
      Loader.fullPage();
      var request = await this.$store.dispatch("cafeteria/printOrderTicket", {id:this.board.order.id});
      console.log(request);
      Loader.hide();
      if(request.success) var printPDF = await Print.printBase64(request.data);
      else this.$awn.alert(request.data);
    },

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
    refreshData(){
      this.hide(); //$('#completeOrder').modal('hide');
      this.$emit('refresh', true);
    },

    async changeBoard(){
      Loader.fullPage();
      await this.$store.dispatch('boards/getAllBoards');
      Loader.hide();
      if(this.boards && this.boards.length == 0){
        this.$awn.alert("No existen mesas disponibles");
        return;
      }
      $('#modalAssignBoard').modal('show');
    },
    async changeWaiter(){
      Loader.fullPage();
      await this.$store.dispatch('waiters/getAllWaiters');
      Loader.hide();
      if(this.waiters && this.waiters.length == 0){
        this.$awn.alert("No existen meseros disponibles");
        return;
      }
      $('#modalAssignWaiter').modal('show');
    },
    async addProducts(){
      Loader.fullPage();
      await this.$store.dispatch("cafeteria/getBoard", this.board.id);
      await this.$store.dispatch("products/getCategories");
      Loader.hide();
      $('#modalCatalog').modal('show');
    },
    changeValue(value){
      this.total = 0;
      value.map((product)=>{
        product.subtotal = parseFloat(product.price)*parseInt(product.quantity);
        this.total += parseFloat(product.subtotal);
      });
      this.board.order.products = value;
      
    },

    /** Remove Product
      * @param index, if string === 'all', delete all products, other are number (index)
      * @param adminPassword,
    ***/
    async removeProduct(index = false, adminPassword = false){

      // Authorization
      if(!adminPassword || !index){
        this.jsonPassword = {
          title: 'Confirmacion de administrador',
          verify: this.adminPassword,
          message: 'Contraseña incorrecta, permiso denegado',
          data: index
        },
        $('#passwordVerify').modal('show');
        return;
      }

      if (typeof this.jsonPassword.data === 'string') {
        // If have a all products

        if (this.jsonPassword.data !== 'all') {
          this.hide(); //$('#completeOrder').modal('hide');
          return this.$awn.alert('Error al eliminar producto de ticket, es un string pero no es "all"');
        }

        return await this.deleteAllProducts();
      }else{
        // If have a only product
        return await this.deleteAOnlyProduct();
      }

    },
    async deleteAOnlyProduct() {

      // Eliminar el producto de ese index
      this.board.order.products = this.jsonTable.items;
      this.jsonTable.items.splice(this.jsonPassword.data, 1);
      this.board.order.products = this.jsonTable.items;

      // Enviar...
      const data = new FormData();
      data.append('products', JSON.stringify(this.jsonTable.items));

      Loader.fullPage();
      const request = await this.$store.dispatch("cafeteria/removeProduct", {
        id:this.board.order.id, data
      });
      Loader.hide();

      if (!request.success) return this.$awn.alert(request.data);
      this.hide(); //$('#completeOrder').modal('hide');
      this.$emit('refresh', true);

    },
    async deleteAllProducts() {

      // Eliminar el producto de ese index
      this.board.order.products = [];

      // Enviar...
      const data = new FormData();
      data.append('products', JSON.stringify([]));

      Loader.fullPage();
      const request = await this.$store.dispatch("cafeteria/removeProduct", {
        id:this.board.order.id, data
      });
      Loader.hide();

      if (!request.success) return this.$awn.alert(request.data);
      this.hide(); //$('#completeOrder').modal('hide');
      this.$emit('refresh', true);

    },
    getTotal(products){
      
      this.total = 0;
      this.gananciaTotal = 0;
      products.map((product)=>{
        this.total += parseFloat(product.subtotal);
        if(this.gananciaInstalled){
          this.gananciaTotal += parseFloat(product.ganancia);
        }
      });
      this.subtotal = this.total;
      if (this.propina == -1) {
        this.propina = this.subtotal*(parseFloat(this.propina_porcentaje_default)/100);
        this.propina_porcentaje = this.propina_porcentaje_default;
      }
      //this.board.order.tip = this.propina;
      //console.log("tip:",this.board.order.tip)
      this.total = (this.subtotal + parseFloat(this.propina)) - this.descuento;
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
  },
  computed:{
    propina_porcentaje_default:{ get(){ return ConfigHelper.ConfEnv('propina_porcentaje_default'); } },

    adminPassword:{ get(){ return ConfigHelper.ConfEnv('stgg_password_remove_product_ticket'); } },
    board:{
      get(){
        // CADA VEZ QUE UN MODAL ES EJECUTADO LLAMA ESTA FUNCION 2 VECES. INCLUSO 3 con el MOUNT
        
        var req = this.$store.getters['cafeteria/getBoard'];
        if(req && req.order) {
          if(typeof req.order.products == "string") this.jsonTable.items = JSON.parse(req.order.products);
          else this.jsonTable.items = req.order.products;
          this.getTotal(this.jsonTable.items);
        }
        // comentador para ser movido hacia metodo SHOW
        //if ($("#PropinaMesero").val() == "") this.calcularPropina()
        //this.calcularDescuento()
        //this.descuento = 0;
        
        return req;
      },
      set(value){ this.$store.commit('cafeteria/setProperty', {key: 'board', data: value}) }
    },
    sell_total:{
      get(){ return this.$store.main.sell_total },
      set(val){ this.$store.commit('main/setProperty', {key:'sell_total', data: val})}
    },
    turned_cafeteria:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.turned_cafeteria'); } },
    clientsInstaller:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes'); } },
    gananciaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_ganancia'); } },
    ticket_description:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description'); } },
    clientsPhone:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono'); } },
    sellCreate:{ get(){ return ConfigHelper.HavePermission('crear_venta'); } },
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
    settingTransferencia:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.transferencia');
    } },
    settingCheque:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.cheque');
    } },
    settingBank:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco');
    } },
    settingEdenred:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.edenred');
    } },
    settingNotaCredito:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.nota_de_credito');
    } },
    settingCredito:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.credito');
    } },
    settingMulticaja:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.multicaja');
    } },
    settingSodexo:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.sodexo');
    } },
    settingAmipass:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.amipass');
    } },
    settingRappi:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.rappi');
    } },
    settingUber:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.uber');
    } },
    settingConvenio:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.convenio_empresa');
    } },
  },
}
</script>

<style media="screen">
.tiempo-flag-order {
  position: absolute;
  top: 7px;
  left: 20px;
  height: 10px;
  width: 10px;
}
.tiempo-flag-0 {
  background-color: black;
    color:black;
    
}
.tiempo-flag-1{
    background-color: greenyellow;
    color:greenyellow;
   
  }
  .tiempo-flag-2{
    background-color: lightblue;
    color:lightblue;
    
  }
  .tiempo-flag-3{
    background-color: yellow;
    color:yellow;
    
  }
  .tiempo-flag-4{
    background-color: red;
    color:red;
    
  }

.dropdown-menu{
  left: 5px !important;
  margin: 0px !important;
  background: var(--primary) !important;
  color: #fff !important;
}
.dropdown-menu li a:hover, .dropdown-menu li:hover{
  background: var(--secondary) !important;
  color: #fff !important;
}
.footerTableTicket{
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 10px 5px 10px;
  border-bottom: 1px solid #dee2e6;
  border-left: 1px solid #dee2e6;
  border-right: 1px solid #dee2e6;
  margin: 0px;
}
.boxCompleteOrder{
  min-height: 450px;
}
</style>
