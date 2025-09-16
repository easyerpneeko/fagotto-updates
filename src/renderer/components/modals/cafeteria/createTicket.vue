<template>
  <div class="modal fade modalForce" id="createTicket" tabindex="-1" role="dialog" aria-labelledby="createTicket" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primario">
          <h5 class="modal-title">Resumen de la orden</h5>
          <button type="button" class="close text-white" @click="backCatalog()" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0" style="max-height: 300px; overflow: auto;">
          <custom-table v-model="jsonTable" @changeValue="changeValue" />
          <div class="footerTableTicket d-flex justify-content-between">
            <h5 class="">
              TOTAL
            </h5>
            <h5>
              {{formatNumber(value.total)}}$
            </h5>
          </div>
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn bg-secundario text-white" @click="backCatalog()">Volver</button> -->

          <button v-if="!this.value.order && ticket_sell && settingBoletaLocal" type="button" class="btn bg-primario text-white" @click="createTicketSell('boleta_local')">Ticket + efectivo</button>
          <button v-if="!this.value.order && ticket_sell && settingBoleta" type="button" class="btn bg-dark text-white" @click="createTicketSell('boleta')">Ticket + boleta</button>
          <button v-if="!this.value.order && ticket_sell && settingFactura" type="button" class="btn bg-secundario text-white" @click="createTicketSell('factura')">Ticket + factura</button>
          <button v-if="!this.value.order && ticket_sell_close" type="button" class="btn bg-primario text-white" @click="viewTicket('ticket_venta')">
            Ticket + Cerrar venta
          </button>
          <button type="button" class="btn bg-primario text-white" @click="createTicket(false, true)">Crear ticket</button>
        </div>
      </div>
    </div>
    <modal-client @sendInfo="createTicket" />
  </div>
</template>

<script>
import modalClient from '@/components/modals/client1.vue';
import customTable from '@/components/tables/table.vue';
import FormatNumber from '@/helpers/FormatNumber.js';
import Print from '@/helpers/Print.js';
import Loader from '@/helpers/Loader';
import ConfigHelper from '@/helpers/ConfigHelper.js';

export default {
  components:{ customTable, modalClient },
  props:['value', 'typeCreateTicket'],
  data(){
    return{
      type_sell: null,
      other_type: null,
      jsonTable: {
        btn: false,
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
        ]
      }
    }
  },
  methods:{
    createTicketSell(type_sell = null){
      console.log('MOSTRANDOOOO TYPE SELL',type_sell);
      if(this.ticket_sell){
        this.type_sell = type_sell;
        if(this.clientsInstaller && type_sell == 'factura') $('#clientCreate1').modal('show');
        else this.createTicket(false, false);
      }
    },

    async createTicket(client = false, onlyTicket){
      $('#clientCreate1').modal('hide');

      // 🚨 DEBUG BÁSICO - Ver qué datos tenemos al inicio
      console.log('=== INICIO createTicket ===');
      console.log('this.value:', this.value);
      console.log('this.type_sell:', this.type_sell);
      console.log('this.typeCreateTicket:', this.typeCreateTicket);
      console.log('=== FIN DEBUG BÁSICO ===');

      // Datos basicos
      console.log('createTicket ======1', this.value);
      console.log('createTicket ======2', this.type_sell);
      let data = { //PD: maravillas del state less UwU
        products: JSON.stringify(this.value.products),
        total: this.deFormatNumber(this.value.total, false),
        gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.value.gananciaTotal,false) : 0,
        waiter_id: this.value.waiter_id,
        board_id: this.value.board_id,
        paymode: this.type_sell
      };

      if (this.ticket_description) {      
        data.description = this.value.description;
      }
      if (this.ticket_sell_client) {      
        data.client_ticket = this.value.client_ticket;
      }

      if(!onlyTicket || client){
        if(this.type_sell != 'boleta_local') data.type_sell = this.type_sell;
        data.ticket = true;
        if(this.clientsInstaller && client){
          data.name = client.name;
          data.lastname = client.lastname;
          data.rut = client.rut;
          data.city = client.city;
          data.comuna = client.comuna;
          data.razon_social = client.razon_social;
          data.direction = client.direction;
          data.giro = client.giro;
          data.phone = (this.clientsPhone) ? client.phone : '';
        }
      }

      var thing = new FormData();
      
      // 🚀 DEBUG: Log completo para ver qué type_sell estamos recibiendo
      console.log('🚀 UBER DEBUG - type_sell actual:', this.type_sell);
      console.log('🚀 UBER DEBUG - other_type actual:', this.other_type);
      console.log('🚀 UBER DEBUG - value completo:', this.value);
      console.log('🚀 UBER DEBUG - paymentMethod desde value:', this.value.paymentMethod);
      console.log('🚀 UBER DEBUG - typeCreateTicket:', this.typeCreateTicket);
    
      if(
        this.type_sell != 'boleta_local' 
          && this.type_sell != 'amipass' 
          && this.type_sell != 'rappi'
          && this.type_sell != 'uber' 
          && this.type_sell != 'credito' 
          && this.type_sell != 'boleta' 
          && this.type_sell != 'factura'
          && this.type_sell != 'convenio_empresa'
          && this.type_sell != 'ticket_venta'
          && this.type_sell != 'factura'
          && this.type_sell != 'ticket'
          && this.type_sell != 'edenred'
          && this.type_sell != 'junaeb'
          && this.type_sell != 'banco_chile_20'
          && this.type_sell != 'pluxee'
          && this.type_sell != 'pedidos_ya'
          && this.type_sell != null
        
        ){
            console.log('🚨 UBER DEBUG - Entrando al bloque de asignación other_type');
            this.other_type=this.type_sell
            data.type_sell='other'
            this.type_sell='other';
        }
      
      for (let key in data) if (data[key]) thing.append(key, data[key]);

      
      if(this.other_type != null && this.other_type != ''){
        if( this.type_sell != 'boleta_local' 
          && this.type_sell != 'amipass' 
          && this.type_sell != 'rappi' 
          && this.type_sell != 'uber' 
          && this.type_sell != 'credito' 
          && this.type_sell != 'boleta' 
          && this.type_sell != 'factura'
          && this.type_sell != 'convenio_empresa'
          && this.type_sell != 'ticket_venta'
          && this.type_sell != 'factura'
          && this.type_sell != 'ticket'
          && this.type_sell != 'edenred'
          && this.type_sell != 'junaeb'
          && this.type_sell != 'banco_chile_20'
          && this.type_sell != 'pluxee'
          && this.type_sell != 'pedidos_ya'
          && this.type_sell != null){
        
          thing.append('typeSell', this.type_sell);
          thing.append('other_type', this.other_type);
        } 
      }

      if (this.type_sell == 'amipass') {
        thing.set('other_type', 'amipass');
      }

      if (this.type_sell == 'rappi') {
        thing.set('other_type', 'rappi');
      }
      
      if (this.type_sell == 'junaeb') {
        thing.set('other_type', 'junaeb');
      }

      if (this.type_sell == 'banco_chile_20') {
        thing.set('other_type', 'banco_chile_20');
        // Agregar información especial del pago con 20%
        if (this.value.specialPayment) {
          thing.append('special_payment_info', JSON.stringify(this.value.specialPayment));
        }
      }

      // 🚀 DETECTAR UBER: Si typeCreateTicket es 'boleta' pero viene desde Uber
      if (this.typeCreateTicket === 'boleta' && this.value.paymentMethod === 'uber_eats') {
        console.log('🚀 UBER DETECTADO - Configurando other_type');
        thing.set('other_type', 'uber_eats');
        
        // Información adicional de Uber para reportes
        if (this.value.paymentDescription) {
          thing.append('payment_description', this.value.paymentDescription);
        }
        const uberInfo = {
          paymentMethod: this.value.paymentMethod || 'uber_eats',
          paymentDescription: this.value.paymentDescription || 'Uber Eats - Boleta SII',
          uberEatsOrder: this.value.uberEatsOrder || true,
          specialPaymentType: this.value.specialPaymentType || 'uber_eats',
          source: 'uber_eats',
          reportCategory: 'delivery_platforms'
        };
        thing.append('uber_payment_info', JSON.stringify(uberInfo));
        console.log('🚀 UBER - Datos enviados:', uberInfo);
      }

      // 🚀 DETECTAR UBER POR paymentMethod en lugar de type_sell
      if (this.value.paymentMethod === 'uber_eats' || this.type_sell == 'uber') {
        console.log('✅ UBER DEBUG - Entrando al bloque de Uber!');
        thing.set('other_type', 'uber_eats');
        console.log('✅ UBER DEBUG - Establecido other_type como uber_eats');
        
        // ✅ AGREGAR INFORMACIÓN ESPECIAL DE UBER EATS PARA REPORTES
        if (this.value.paymentMethod) {
          thing.append('payment_method', this.value.paymentMethod);
        }
        if (this.value.paymentDescription) {
          thing.append('payment_description', this.value.paymentDescription);
        }
        if (this.value.uberEatsOrder) {
          thing.append('uber_eats_order', this.value.uberEatsOrder ? 'true' : 'false');
        }
        if (this.value.specialPaymentType) {
          thing.append('special_payment_type', this.value.specialPaymentType);
        }
        
        // Agregar información completa como JSON para el backend
        const uberInfo = {
          paymentMethod: this.value.paymentMethod || 'uber_eats',
          paymentDescription: this.value.paymentDescription || 'Uber Eats - Boleta SII',
          uberEatsOrder: this.value.uberEatsOrder || true,
          specialPaymentType: this.value.specialPaymentType || 'uber_eats',
          source: 'uber_eats',
          reportCategory: 'delivery_platforms'
        };
        thing.append('uber_payment_info', JSON.stringify(uberInfo));
        
        console.log('🚀 UBER EATS - Datos enviados al backend:', uberInfo);
        console.log('🚀 UBER DEBUG - FormData other_type:', thing.get('other_type'));
      }

      if (this.type_sell == 'credito') {
        thing.set('other_type', 'credito');
      }

      if (this.type_sell == 'convenio_empresa') {
        thing.set('other_type', 'convenio_empresa');
      }
      if (this.type_sell == 'edenred') {
        thing.set('other_type', 'edenred');
      }

      if (this.type_sell == 'pluxee') {
        thing.set('other_type', 'pluxee');
      }

      if (this.type_sell == 'pedidos_ya') {
        thing.set('other_type', 'pedidos_ya');
      }
      
      Loader.fullPage();
      // Iniciando peticion
      
      //Si es debito mando la data a otro endpoint
      if(this.type_sell=='other' ||this.type_sell=='transferencia' ||this.type_sell=='rappi' ||this.type_sell=='junaeb' ||this.type_sell=='uber' ||this.type_sell=='credito' || this.type_sell=='amipass' || this.type_sell=='banco_chile_20' || this.type_sell=='pluxee' || this.type_sell=='pedidos_ya'){
        var request = await this.$store.dispatch("sells/newTicket", thing);
        console.log("RESPUESTA DE LA APIII CREARTICKET",request);
      }else{

        if(this.value.order) var request = await this.$store.dispatch("sells/editTicket", {data: thing, id: this.value.order.id});
        else var request = await this.$store.dispatch("sells/newTicket", thing);
      }

      Loader.hide();

      // Verificando respuesta
      if (!request.success) {
        this.$awn.alert(request.data);
        return false;
      }

      if(this.turned_cafeteria && (this.type_sell == 'ticket_venta' || this.type_sell == 'boleta' || this.type_sell == 'factura' || this.type_sell == 'boleta_local' || this.type_sell == 'amipass')){
        $('#modalTurned').modal('show');
        this.sell_total = this.value.total;
      }
      this.$awn.success("Orden creada exitosamente",{labels:{success:'CORRECTO'}});

  
      var ticket = await Print.printBase64(request.data.ticket);
    
      if(request.data.order){
        if(request.data.order.response_folio && request.data.order.response_folio == 'boleta' || request.data.order.response_folio == 'factura') {
          this.$awn.info('El ajuste de '+ request.data.order.response_folio +' se encuentra desactivado');
        }else if (request.data.order.response_folio) {
          setTimeout(async ()=>{
            var order = await Print.printBase64(request.data.order.response_folio);
          }, 1000);
        }else{
          if(typeof(request.data.order[0]) !== 'undefined') {
            this.$awn.info(request.data.order[0].response_folio);
          }
        }
      }

      //reinicio el type_cell
      this.type_sell=null;
      this.$emit('closeModal', true);
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
    backCatalog(){
      $('#createTicket').modal('hide');
      $('#modalCatalog').modal('show');
    },
    changeValue(value){
      this.value.products = value;
      this.value.total = 0;
      this.value.products.map((product)=>{
        product.subtotal = parseFloat(product.price)*parseInt(product.quantity);
        this.value.total += parseFloat(product.subtotal);
      });
      this.$emit('changeValue',this.value);
    }
  },
  watch:{
    value(value){
      this.jsonTable.items = value.products;
    },
    typeCreateTicket(val){
      if(val == 'ticket'){
        this.createTicket(false, true);
      }else if(val != false){
        this.createTicketSell(val);
      }
      $('#createTicket').modal('hide');
    },
  },
  computed:{
    data:{
      get(){ return this.value; },
      set(value){ this.$emit('input', value) }
    },
    ver_ticket:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ver_ticket'); } },
    sell_total:{
      get(){ return this.$store.main.sell_total },
      set(val){ this.$store.commit('main/setProperty', {key:'sell_total', data: val})}
    },
    ticket_sell_close:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell_proccess_close'); } },
    turned_cafeteria:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.turned_cafeteria'); } },
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

    settingAmipass:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.amipass');
    } },

    settingTransferencia:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.transferencia');
    } },
    settingBancoChile20:{ get(){
      if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
      return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco_chile_20');
    } },
    ticket_sell:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell'); } },
    ticket_sell_client:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell_client'); } },
    clientsInstaller:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes'); } },
    clientsPhone:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono'); } },
    gananciaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_ganancia'); } },
    ticket_description:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description'); } },
  },
}
</script>

<style scoped>
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
  .text-uppercase{
    text-transform: uppercase;
  }
</style>
