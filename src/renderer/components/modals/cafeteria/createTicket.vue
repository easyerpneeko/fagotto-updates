<template>
  <div class="modal fade modalForce" id="createTicket" tabindex="-1" role="dialog" aria-labelledby="createTicket" aria-hidden="true" data-backdrop="false" data-focus="false">
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

          <button v-if="!this.value.order && ticket_sell && settingBoletaLocal" type="button" class="btn bg-primario text-white" @click="createTicketSell('boleta_local')" :disabled="isProcessing">Ticket + efectivo</button>
          <button v-if="!this.value.order && ticket_sell && settingBoleta" type="button" class="btn bg-dark text-white" @click="createTicketSell('boleta')" :disabled="isProcessing">Ticket + boleta</button>
          <button v-if="!this.value.order && ticket_sell && settingFactura" type="button" class="btn bg-secundario text-white" @click="createTicketSell('factura')" :disabled="isProcessing">Ticket + factura</button>
          <button v-if="!this.value.order && ticket_sell_close" type="button" class="btn bg-primario text-white" @click="viewTicket('ticket_venta')" :disabled="isProcessing">
            Ticket + Cerrar venta
          </button>
          <button type="button" class="btn bg-primario text-white" @click="createTicket(false, true)" :disabled="isProcessing">
            <span v-if="isProcessing">🔄 Procesando...</span>
            <span v-else>Crear ticket</span>
          </button>
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
import Connection from '@/helpers/Connection.js';

export default {
  components:{ customTable, modalClient },
  props:['value', 'typeCreateTicket'],
  data(){
    return{
      type_sell: null,
      other_type: null,
      isProcessing: false,
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
      
      // 🎫 PREPARAR PRODUCTOS: Reemplazar IDs únicos con IDs reales para productos con cupón
      const productsForBackend = this.value.products.map(product => {
        if (product.has_cupon && product.product_id_real) {
          // Producto con cupón: usar ID real para que el backend lo encuentre en la BD
          console.log(`🎫 Reemplazando ID único "${product.id}" con ID real "${product.product_id_real}"`);
          return {
            ...product,
            id: product.product_id_real // Reemplazar ID único con ID real
          };
        }
        return product; // Productos normales sin cambios
      });
      
      let data = { //PD: maravillas del state less UwU
        products: JSON.stringify(productsForBackend),
        total: this.deFormatNumber(this.value.total, false),
        gananciaTotal: (this.gananciaInstalled) ? this.deFormatNumber(this.value.gananciaTotal,false) : 0,
        paymode: this.type_sell
      };

      // ✅ Para ventas merchise sin mesa: enviar 0 en vez de null
      if (this.value.waiter_id !== null && this.value.waiter_id !== undefined) {
        data.waiter_id = this.value.waiter_id;
      } else {
        data.waiter_id = 0; // Venta directa sin mesero
      }
      
      if (this.value.board_id !== null && this.value.board_id !== undefined) {
        data.board_id = this.value.board_id;
      } else {
        data.board_id = 0; // Venta directa sin mesa
      }

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
      } else {
        // 🎫 Cuando es solo ticket (onlyTicket=true), también marcar data.ticket=true
        // para que el backend imprima correctamente
        data.ticket = true;
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
          && this.type_sell != 'fagotto_10'
          && this.type_sell != 'turbus_10'
          && this.type_sell != 'halloween_20'
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
      
      // ✅ Solo agregar board_id y waiter_id si tienen valores reales (no null)
      // Para ventas merchise directas, NO se agregan (el backend los maneja como opcionales)
      if (this.value.waiter_id !== null && this.value.waiter_id !== undefined && this.value.waiter_id !== 0) {
        thing.append('waiter_id', this.value.waiter_id);
      }
      
      if (this.value.board_id !== null && this.value.board_id !== undefined && this.value.board_id !== 0) {
        thing.append('board_id', this.value.board_id);
      }

      
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
          && this.type_sell != 'fagotto_10'
          && this.type_sell != 'turbus_10'
          && this.type_sell != 'halloween_20'
          && this.type_sell != 'pluxee'
          && this.type_sell != 'pedidos_ya'
          && this.type_sell != null){
        
          thing.append('typeSell', this.type_sell);
          thing.append('other_type', this.other_type);
        } 
      }

      if (this.type_sell == 'amipass') {
        thing.set('other_type', 'amipass');
        thing.set('type_sell', 'boleta'); // 🎫 GENERAR BOLETA SII OFICIAL (no boleta local)
      }

      if (this.type_sell == 'rappi') {
        thing.set('other_type', 'rappi');
      }
      
      if (this.type_sell == 'junaeb') {
        thing.set('other_type', 'junaeb');
      }

      if (this.type_sell == 'banco_chile_20') {
        thing.set('other_type', 'banco_chile_20');
        thing.set('type_sell', 'boleta'); // 🏦 GENERAR BOLETA SII OFICIAL (no boleta local)
        // Agregar información especial del pago con 20%
        if (this.value.specialPayment) {
          thing.append('special_payment_info', JSON.stringify(this.value.specialPayment));
        }
      }

      if (this.type_sell == 'fagotto_10') {
        thing.set('other_type', 'fagotto_10');
        // Agregar información especial del pago Fagotto 10%
        if (this.value.specialPayment) {
          thing.append('special_payment_info', JSON.stringify(this.value.specialPayment));
        }
        console.log('✅ FAGOTTO 10% - Datos enviados:', this.value.specialPayment);
      }

      if (this.type_sell == 'turbus_10') {
        thing.set('other_type', 'turbus_10');
        // Agregar información especial del pago Turbus 10%
        if (this.value.specialPayment) {
          thing.append('special_payment_info', JSON.stringify(this.value.specialPayment));
        }
        console.log('✅ TURBUS 10% - Datos enviados:', this.value.specialPayment);
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

      // 🍕 DETECTAR PEDIDOS YA: Si typeCreateTicket es 'boleta' pero viene desde Pedidos Ya
      if (this.typeCreateTicket === 'boleta' && this.value.paymentMethod === 'pedidos_ya') {
        console.log('🍕 PEDIDOS YA DETECTADO - Configurando other_type');
        thing.set('other_type', 'pedidos_ya');
        
        // Información adicional de Pedidos Ya para reportes
        if (this.value.paymentDescription) {
          thing.append('payment_description', this.value.paymentDescription);
        }
        const pedidosYaInfo = {
          paymentMethod: this.value.paymentMethod || 'pedidos_ya',
          paymentDescription: this.value.paymentDescription || 'Pedidos Ya - Boleta SII',
          pedidosYaOrder: this.value.pedidosYaOrder || true,
          specialPaymentType: this.value.specialPaymentType || 'pedidos_ya',
          source: 'pedidos_ya',
          reportCategory: 'delivery_platforms'
        };
        thing.append('pedidos_ya_payment_info', JSON.stringify(pedidosYaInfo));
        console.log('🍕 PEDIDOS YA - Datos enviados:', pedidosYaInfo);
      }

      // 🛵 DETECTAR RAPPI: Si typeCreateTicket es 'boleta' pero viene desde Rappi
      if (this.typeCreateTicket === 'boleta' && this.value.paymentMethod === 'rappi') {
        console.log('🛵 RAPPI DETECTADO - Configurando other_type');
        thing.set('other_type', 'rappi');
        
        // Información adicional de Rappi para reportes
        if (this.value.paymentDescription) {
          thing.append('payment_description', this.value.paymentDescription);
        }
        const rappiInfo = {
          paymentMethod: this.value.paymentMethod || 'rappi',
          paymentDescription: this.value.paymentDescription || 'Rappi - Boleta SII',
          rappiOrder: this.value.rappiOrder || true,
          specialPaymentType: this.value.specialPaymentType || 'rappi',
          source: 'rappi',
          reportCategory: 'delivery_platforms'
        };
        thing.append('rappi_payment_info', JSON.stringify(rappiInfo));
        console.log('🛵 RAPPI - Datos enviados:', rappiInfo);
      }

      // 🎯 DETECTAR FAGOTTO 10%: Si typeCreateTicket es 'boleta' y tiene specialPayment con descuento
      if (this.typeCreateTicket === 'boleta' && this.value.specialPayment && this.value.specialPayment.paymentType === 'fagotto_10') {
        console.log('🎯 FAGOTTO 10% DETECTADO - Configurando other_type');
        thing.set('other_type', 'fagotto_10');
        
        // Agregar información especial del descuento
        thing.append('special_payment_info', JSON.stringify(this.value.specialPayment));
        console.log('🎯 FAGOTTO 10% - Datos enviados:', this.value.specialPayment);
      }

      // 🎃 DETECTAR HALLOWEEN 20%: Si typeCreateTicket es 'boleta' y tiene specialPayment con halloween_20
      if (this.typeCreateTicket === 'boleta' && this.value.specialPayment && this.value.specialPayment.paymentType === 'halloween_20') {
        console.log('🎃 HALLOWEEN 20% DETECTADO - Configurando other_type');
        thing.set('other_type', 'halloween_20');
        
        // Agregar información especial del descuento Halloween
        const halloweenInfo = {
          paymentMethod: 'halloween_20',
          paymentDescription: '🎃 Halloween 20% - Boleta SII con descuento',
          paymentType: 'halloween_20',
          specialPaymentType: 'halloween_20',
          source: 'halloween_20',
          reportCategory: 'special_discounts',
          ...this.value.specialPayment
        };
        thing.append('special_payment_info', JSON.stringify(halloweenInfo));
        thing.append('payment_method', 'halloween_20');
        thing.append('payment_description', '🎃 Halloween 20% - Descuento especial');
        thing.append('special_payment_type', 'halloween_20');
        
        console.log('🎃 HALLOWEEN 20% - Datos enviados al backend:', halloweenInfo);
        console.log('🎃 HALLOWEEN DEBUG - FormData other_type:', thing.get('other_type'));
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

      // 🍕 DETECTAR PEDIDOS YA POR paymentMethod
      if (this.value.paymentMethod === 'pedidos_ya' || this.type_sell == 'pedidos_ya') {
        console.log('✅ PEDIDOS YA DEBUG - Estableciendo other_type');
        thing.set('other_type', 'pedidos_ya');
        
        if (this.value.paymentMethod) {
          thing.append('payment_method', this.value.paymentMethod);
        }
        if (this.value.paymentDescription) {
          thing.append('payment_description', this.value.paymentDescription);
        }
        if (this.value.pedidosYaOrder) {
          thing.append('pedidos_ya_order', this.value.pedidosYaOrder ? 'true' : 'false');
        }
        if (this.value.specialPaymentType) {
          thing.append('special_payment_type', this.value.specialPaymentType);
        }
        
        const pedidosYaInfo = {
          paymentMethod: this.value.paymentMethod || 'pedidos_ya',
          paymentDescription: this.value.paymentDescription || 'Pedidos Ya - Boleta SII',
          pedidosYaOrder: this.value.pedidosYaOrder || true,
          specialPaymentType: this.value.specialPaymentType || 'pedidos_ya',
          source: 'pedidos_ya',
          reportCategory: 'delivery_platforms'
        };
        thing.append('pedidos_ya_payment_info', JSON.stringify(pedidosYaInfo));
        
        console.log('🍕 PEDIDOS YA - Datos enviados al backend:', pedidosYaInfo);
        console.log('🍕 PEDIDOS YA DEBUG - FormData other_type:', thing.get('other_type'));
      }

      // 🛵 DETECTAR RAPPI POR paymentMethod
      if (this.value.paymentMethod === 'rappi' || this.type_sell == 'rappi') {
        console.log('✅ RAPPI DEBUG - Estableciendo other_type');
        thing.set('other_type', 'rappi');
        
        if (this.value.paymentMethod) {
          thing.append('payment_method', this.value.paymentMethod);
        }
        if (this.value.paymentDescription) {
          thing.append('payment_description', this.value.paymentDescription);
        }
        if (this.value.rappiOrder) {
          thing.append('rappi_order', this.value.rappiOrder ? 'true' : 'false');
        }
        if (this.value.specialPaymentType) {
          thing.append('special_payment_type', this.value.specialPaymentType);
        }
        
        const rappiInfo = {
          paymentMethod: this.value.paymentMethod || 'rappi',
          paymentDescription: this.value.paymentDescription || 'Rappi - Boleta SII',
          rappiOrder: this.value.rappiOrder || true,
          specialPaymentType: this.value.specialPaymentType || 'rappi',
          source: 'rappi',
          reportCategory: 'delivery_platforms'
        };
        thing.append('rappi_payment_info', JSON.stringify(rappiInfo));
        
        console.log('🛵 RAPPI - Datos enviados al backend:', rappiInfo);
        console.log('🛵 RAPPI DEBUG - FormData other_type:', thing.get('other_type'));
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
      
      // 🔧 Marcar como procesando
      this.isProcessing = true;
      
      // Iniciando peticion
      try {
        //Si es debito mando la data a otro endpoint
        if(this.type_sell=='other' ||this.type_sell=='transferencia' ||this.type_sell=='rappi' ||this.type_sell=='junaeb' ||this.type_sell=='uber' ||this.type_sell=='credito' || this.type_sell=='amipass' || this.type_sell=='banco_chile_20' || this.type_sell=='fagotto_10' || this.type_sell=='turbus_10' || this.type_sell=='pluxee' || this.type_sell=='pedidos_ya'){
          var request = await this.$store.dispatch("sells/newTicket", thing);
          console.log("RESPUESTA DE LA APIII CREARTICKET",request);
        }else{

          if(this.value.order) var request = await this.$store.dispatch("sells/editTicket", {data: thing, id: this.value.order.id});
          else var request = await this.$store.dispatch("sells/newTicket", thing);
        }
      } finally {
        this.isProcessing = false;
      }

      // Verificando respuesta
      if (!request.success) {
        // 🔍 Mostrar error como string para evitar problema con vue-awesome-notifications
        const errorMsg = typeof request.data === 'string' ? request.data : JSON.stringify(request.data);
        console.error('❌ ERROR COMPLETO:', errorMsg);
        this.$awn.alert(errorMsg);
        return false;
      }

      // ✅ MARCAR CUPONES COMO USADOS DESPUÉS DE VENTA EXITOSA
      const cuponesUsados = this.value.products.filter(p => p.has_cupon && p.cupon_codigo);
      if (cuponesUsados.length > 0) {
        console.log('🎫 Detectados cupones en la venta:', cuponesUsados);
        
        // ✅ Obtener datos ANTES del loop (una sola vez)
        const currentUser = this.$store.getters['main/user'];
        let appData = null;
        try {
          const appRequest = await this.$store.dispatch('main/refreshData', '?slim');
          appData = appRequest.data;
          console.log('🔍 DEBUG - appData:', appData);
        } catch (e) {
          console.error('❌ Error obteniendo appData:', e);
        }
        
        console.log('🔍 DEBUG - currentUser:', currentUser);
        
        // Extraer datos una vez
        const sucursalId = (appData && appData.Id) || null;
        const sucursalNombre = (appData && (appData.name_public || appData.Name)) || 'Sin sucursal';
        const usuarioId = (currentUser && currentUser.id) || null;
        const usuarioNombre = (currentUser && currentUser.username) || 'Sin usuario';
        
        console.log('🔍 DEBUG - Datos extraídos:', { sucursalId, sucursalNombre, usuarioId, usuarioNombre });
        
        for (const producto of cuponesUsados) {
          try {
            
            const cuponData = {
              codigo: producto.cupon_codigo,
              cupon_id: producto.cupon_id,
              orden_id: request.data.order ? request.data.order.id : null,
              
              // ✅ Sucursal = App actual
              sucursal_id: sucursalId,
              sucursal_nombre: sucursalNombre,
              
              // ✅ Usuario = Usuario logueado
              usuario_id: usuarioId,
              usuario_nombre: usuarioNombre,
              
              categoria_id: producto.categoria_id,
              categoria_nombre: producto.categoria_nombre,
              producto_id: producto.producto_id,
              producto_nombre: producto.producto_nombre,
              precio_original: producto.precio_original,
              precio_con_cupon: producto.price
            };
            
            console.log('🎫 Marcando cupón como usado:', cuponData);
            
            // Llamar al backend para marcar como usado
            try {
              const url = 'https://posfagotto.cl/api/cupones/aplicar';
              console.log('🎫 URL del endpoint:', url);
              
              const response = await Connection.fetch(url, 'POST', cuponData, null, false);
              console.log('✅ Cupón marcado como usado en backend - Response completa:', response);
              
              if (response && response.ok && response.data && response.data.success) {
                console.log('✅✅✅ CUPÓN ACTUALIZADO CORRECTAMENTE EN BD');
                console.log('✅ Detalles:', response.data);
              } else {
                console.error('❌ Backend respondió pero sin success:', response);
                console.error('❌ Mensaje de error:', response.data ? response.data.mensaje : 'Sin mensaje');
                console.error('❌ Errores completos:', response.data);
              }
            } catch (err) {
              console.error('⚠️ Error COMPLETO al marcar cupón:', err);
            }
            
            console.log('✅ Cupón procesado (request enviado):', producto.cupon_codigo);
          } catch (error) {
            console.error('❌ Error al procesar cupón:', error);
          }
        }
      }

      if(this.turned_cafeteria && (this.type_sell == 'ticket_venta' || this.type_sell == 'boleta' || this.type_sell == 'factura' || this.type_sell == 'boleta_local' || this.type_sell == 'amipass' || this.type_sell == 'fagotto_10')){
        $('#modalTurned').modal('show');
        this.sell_total = this.value.total;
      }
      this.$awn.success("Orden creada exitosamente",{labels:{success:'CORRECTO'}});

  
      // 🖨️ IMPRESIÓN SECUENCIAL CON DELAYS
      let printErrors = [];
      
      // 1. Imprimir TICKET
      try {
        console.log('🖨️ Imprimiendo ticket...');
        await Print.printBase64(request.data.ticket);
        console.log('✅ Ticket impreso correctamente');
      } catch (error) {
        console.error('❌ Error al imprimir ticket:', error);
        printErrors.push('ticket');
        this.$awn.warning('Error al imprimir ticket. Puede reimprimir desde el historial.');
      }
      
      // ⏳ Esperar 2 segundos antes de la siguiente impresión
      if (request.data.order && request.data.order.response_folio) {
        await new Promise(resolve => setTimeout(resolve, 2000));
      }
    
      // 2. Imprimir BOLETA/FACTURA (si existe)
      console.log('🔍 DEBUG BOLETA - request.data:', request.data);
      console.log('🔍 DEBUG BOLETA - request.data.order:', request.data.order);
      if (request.data.order) {
        console.log('🔍 DEBUG BOLETA - response_folio:', request.data.order.response_folio);
        console.log('🔍 DEBUG BOLETA - response_folio type:', typeof request.data.order.response_folio);
      }
      
      if(request.data.order){
        if(request.data.order.response_folio && request.data.order.response_folio == 'boleta' || request.data.order.response_folio == 'factura') {
          this.$awn.info('El ajuste de '+ request.data.order.response_folio +' se encuentra desactivado');
        }else if (request.data.order.response_folio) {
          try {
            console.log('🖨️ Imprimiendo boleta/factura...');
            await Print.printBase64(request.data.order.response_folio);
            console.log('✅ Boleta/Factura impresa correctamente');
          } catch (error) {
            console.error('❌ Error al imprimir boleta/factura:', error);
            printErrors.push('boleta/factura');
            this.$awn.warning('Error al imprimir boleta/factura. Puede reimprimir desde el historial.');
          }
        }else{
          if(typeof(request.data.order[0]) !== 'undefined') {
            this.$awn.info(request.data.order[0].response_folio);
          }
        }
      }

      // 📊 Resumen de impresión
      if (printErrors.length > 0) {
        console.warn(`⚠️ Errores en impresión de: ${printErrors.join(', ')}`);
      } else {
        console.log('✅ Todos los documentos impresos correctamente');
      }

      // 3. Ya no se imprime QR (ticket_qr) - Solo ticket y boleta

      //reinicio el type_sell y other_type
      this.type_sell = null;
      this.other_type = null;
      this.isProcessing = false;
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
      // 🧹 LIMPIEZA: Remover backdrops residuales antes de cambiar modales
      $('.modal-backdrop').not(':last').remove();
      
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
      
      // 🎯 AUTO-DETECTAR MÉTODOS DE PAGO ESPECIALES CON DESCUENTO
      if (value.specialPayment && value.specialPayment.paymentType) {
        const paymentType = value.specialPayment.paymentType;
        
        // Si es un pago con descuento (banco_chile_20, fagotto_10, turbus_10)
        if (['banco_chile_20', 'fagotto_10', 'turbus_10'].includes(paymentType)) {
          this.type_sell = paymentType;
          console.log(`✅ AUTO-DETECTADO - type_sell establecido a: ${paymentType}`);
        }
      }
    },
    typeCreateTicket(val){
      if(val == 'ticket'){
        this.createTicket(false, true);
      }else if(val != false){
        // 🎯 NO sobrescribir type_sell si ya está establecido por specialPayment
        if (!this.type_sell || !['banco_chile_20', 'fagotto_10', 'turbus_10'].includes(this.type_sell)) {
          this.createTicketSell(val);
        } else {
          console.log(`⚠️ type_sell YA establecido a ${this.type_sell}, no sobrescribir con ${val}`);
          // Si ya tiene type_sell especial, solo llamar directamente a createTicket
          if(this.ticket_sell){
            if(this.clientsInstaller && val == 'factura') $('#clientCreate1').modal('show');
            else this.createTicket(false, false);
          }
        }
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
