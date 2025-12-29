<template>
  <div class="default-bg p-2 d-flex flex-column align-items-end pt-md-5">
    <div class="actionsCafeteria">
      <button :disabled="offOn" @click="getBoards(true)" type="button" class="m-1 btn-width btn bg-secundario text-white text-capitalize">
        <i class="fas fa-sync-alt"></i>
        Refrescar
      </button>
      <button :disabled="offOn" @click="toggleKeepModalOpen()" type="button" 
        :class="['m-1 btn-width btn text-white text-capitalize', keepModalOpen ? 'bg-success' : 'bg-dark']">
        <i :class="keepModalOpen ? 'fas fa-lock' : 'fas fa-lock-open'"></i>
        {{ keepModalOpen ? 'Modal Fijo' : 'Modal Normal' }}
      </button>
      <button @click="showHelpModal()" type="button" class="m-1 btn-width btn bg-info text-white text-capitalize">
        <i class="fas fa-question-circle"></i>
        Ayuda
      </button>
      <button :disabled="offOn" v-if="notification" @click="openNotifyList = !openNotifyList" type="button" class="m-1 btn-width btn bg-light text-white text-capitalize">
        <small class="notification-counter"></small>
        <i class="fas fa-bell"></i>
      </button>
    </div>
    <div class="notifications-list" v-if="openNotifyList">
      <div class="noti-title bg-primario">Notificaciones</div>
      <div class="noti-list bg-light" v-for="board in cafeteria" v-if="tiempo_en_ocupacion(board)>=7">
        <span style="font-weight:bold">{{board.name}}:</span> Tiene <span :class="tiempo_uso_class">{{tiempo_en_ocupacion(board)}}</span> minutos en uso, por favor pedir desocupar.
      </div>
    </div>
    <!-- title -->
    <div class="w-100">
      <h3 v-if="envs" class="text-primario">{{envs.name_panel_tickets.value}}</h3>
      <hr class="borderTitleCafeteria" />
    </div>

    <template v-if="!desactivarMesasByGarzonMode">
      <!--Lista de MESAS-->
      <div class="w-100" v-if="isGarzonModeEnabled">
        <h5 v-if="envs" class="text-primario">Lista de mesas</h5>
      </div>
      <div ref="loaderMesasOrder" v-if="(cafeteria && cafeteria.length > 0)" class="vld-parent w-100 px-0">
        <!-- lista -->
        <div class="row">
          <div class="col-md-3 col-sm-4 col-6" v-for="(board, index) in cafeteria" :key="index">
            <board-card :board="board" @clickEmit="openBoard" />
          </div>
        </div>
      </div>
      <div ref="loaderMesasOrder" v-else class="vld-parent box-false d-flex flex-center text-center p-2 w-100">
        <h2>No existen mesas actualmente</h2>
      </div>
      <!--/Lista de MESAS-->
    </template>

    <!--Lista de MESEROS-->
    <template v-if="isGarzonModeEnabled">
      <div class="w-100" v-if="isGarzonModeEnabled">
        <h5 v-if="envs" class="text-primario">Lista de meseros</h5>
      </div>
      <div ref="loaderMesasOrder" v-if="(waiters && waiters.length > 0)" class="vld-parent w-100 px-0">
        <!-- lista -->
        <div class="row">
          <div class="col-md-3 col-sm-4 col-6" v-for="(waiter, index) in waiters" :key="index">
            <waiter-card :waiter="waiter" @input="openGarzonWaiter" :styleCaffeteria="true" />
          </div>
        </div>
      </div>
      <div ref="loaderMesasOrder" v-else class="vld-parent box-false d-flex flex-center text-center p-2 w-100">
        <h2>No existen mesas actualmente</h2>
      </div>
    </template>
    <!--/Lista de MESEROS-->

    <addtion-waiter></addtion-waiter>
    <modalTurned/>
    
    <!-- Modal de ayuda sobre modos de operación -->
    <div class="modal" id="modalModeHelp" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primario text-white">
            <h5 class="modal-title">
              <i class="fas fa-info-circle me-2"></i>
              Modos de Operación del Catálogo
            </h5>
          </div>
          <div class="modal-body">
            <div class="mode-explanation mb-4">
              <h6 class="text-success">
                <i class="fas fa-lock me-2"></i>
                <strong>MODAL FIJO</strong> (Recomendado para alta demanda)
              </h6>
              <ul class="mt-2">
                <li>✅ El catálogo <strong>permanece abierto</strong> después de cada venta</li>
                <li>✅ Puedes procesar <strong>múltiples ventas seguidas</strong> sin cerrar</li>
                <li>✅ El carrito se <strong>limpia automáticamente</strong> después de cada venta</li>
                <li>✅ <strong>Ahorra tiempo</strong> en situaciones con mucho flujo de clientes</li>
                <li>✅ No necesitas volver a pinchar la mesa para la siguiente orden</li>
              </ul>
            </div>

            <div class="mode-explanation">
              <h6 class="text-dark">
                <i class="fas fa-lock-open me-2"></i>
                <strong>MODAL NORMAL</strong> (Comportamiento tradicional)
              </h6>
              <ul class="mt-2">
                <li>➡️ El catálogo se <strong>cierra</strong> después de cada venta</li>
                <li>➡️ Debes <strong>pinchar la mesa nuevamente</strong> para la siguiente orden</li>
                <li>➡️ Comportamiento <strong>clásico</strong> del sistema</li>
                <li>➡️ Útil cuando trabajas con <strong>una venta a la vez</strong></li>
              </ul>
            </div>

            <div class="alert alert-info mt-3">
              <i class="fas fa-lightbulb me-2"></i>
              <strong>Tip:</strong> Puedes cambiar entre modos en cualquier momento usando el botón <strong>"Modal Fijo"</strong> / <strong>"Modal Normal"</strong> en la parte superior.
            </div>
            
            <div class="text-center mt-3" style="color: #6b7280; font-size: 0.9rem;">
              <code>&lt;/&gt;</code> Saludos Jimmy Arriagada <code>&lt;/&gt;</code>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn bg-primario text-white" data-dismiss="modal" style="width: 100%;">
              <i class="fas fa-check me-2"></i>
              Entendido
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
// components
import modalTurned from '@/components/modals/vuelto/modalTurned.vue';
import boardCard from '@/components/cards/boardCard.vue';
import waiterCard from '@/components/cards/waiterCard.vue';
import addtionWaiter from '@/components/modals/cafeteria/addtionWaiter.vue';
// Helpers
import Loader from '@/helpers/Loader';
import ConfigHelper from '@/helpers/ConfigHelper.js';
import moment from 'moment';

export default {
  data: () => {
    return {
      boardChannel: null,
      orderChannel: null,
      notification: false,
      openNotifyList: false,
      tiempo_minutos: null,
      tiempo_uso_class : 'tiempo-uso-flag-0',
      keepModalOpen: false
    }
  },

  components: { boardCard, addtionWaiter, modalTurned, waiterCard },
  created () {
    window.setInterval(() => {
      this.notificationsBoards(); // call any function or end point
    }, 1000*60*7);
  },
  mounted() {
    this.getBoards(true);
    this.pusherMounted();
    this.notificationsBoards();
    this.keepModalOpen = this.$store.getters['cafeteria/getKeepModalOpen'];
    
    // Mostrar modal de ayuda SIEMPRE al entrar
    setTimeout(() => {
      $('#modalModeHelp').modal('show');
    }, 1000);
  },
  unmounted() {
    console.log('unmounted CAFFETERIA');
    this.pusherUnmounted();
  },
  beforeDestroy() {
    console.log('beforeDestroy CAFFETERIA');
    this.pusherUnmounted();
  },
  props:{
    value: {
      type: Boolean,
      default: false
    }
  },
  methods:{
    showHelpModal() {
      setTimeout(() => {
        $('#modalModeHelp').modal('show');
      }, 100);
    },
    toggleKeepModalOpen() {
      this.keepModalOpen = !this.keepModalOpen;
      this.$store.commit('cafeteria/setKeepModalOpen', this.keepModalOpen);
      if (this.keepModalOpen) {
        this.$awn.success('🔒 MODAL FIJO ACTIVADO\n\n✅ El catálogo permanecerá ABIERTO después de cada venta\n✅ Podrás agregar productos inmediatamente sin volver a pinchar la mesa\n✅ Ahorra tiempo procesando múltiples ventas seguidas\n\n💡 El carrito se limpiará automáticamente después de cada venta', {
          durations: { success: 6000 }
        });
      } else {
        this.$awn.info('🔓 MODAL NORMAL ACTIVADO\n\n➡️ El catálogo se CERRARÁ después de cada venta\n➡️ Deberás pinchar la mesa nuevamente para la siguiente venta\n➡️ Comportamiento tradicional del sistema', {
          durations: { info: 5000 }
        });
      }
    },
    notificationsBoards(){
      var board_time = 0;
      for (let cafeteria in this.cafeteria) {
        if(this.cafeteria[cafeteria]['order']) {
          var time = this.cafeteria[cafeteria]['order']['created_at'];
          var tiempo_actual = new Date();
          var tiempo_mesa = new Date(time);
          var fecha1 = moment(tiempo_actual);
          var fecha2 = moment(tiempo_mesa);
          var diffMins = fecha1.diff(fecha2, 'minutes');
          if(diffMins>7) {
            board_time=1;
          }
        }
      }
      if(board_time !== undefined && board_time>0) {
        this.notification = true;
      }
    },
    pusherMounted() {
      if (this.pusherCaffeteria) {
        // BOARDS
        this.boardChannel = this.$echoHelper.private(`cafeteria.boards`);
        this.boardChannel.subscribe();
        this.boardChannel.listen('.board-created', (object) => {
          console.log('[PUSHER] EVENT LISTEN cafeteria.board-created PUSHER', object);
          this.refreshOnPusher();
        });
        this.boardChannel.listen('.board-removed', (object) => {
          console.log('[PUSHER] EVENT LISTEN cafeteria.board-removed PUSHER', object);
          this.refreshOnPusher();
        });
        this.boardChannel.listen('.board-updated', (object) => {
          console.log('[PUSHER] EVENT LISTEN cafeteria.board-updated PUSHER', object);
          this.refreshOnPusher();
        });
        // ORDERS
        this.orderChannel = this.$echoHelper.private(`cafeteria.orders`);
        this.orderChannel.subscribe();
        this.orderChannel.listen('.order-proccesed', (object) => {
          console.log('[PUSHER] EVENT LISTEN cafeteria.order-proccesed PUSHER', object);
          this.$awn.info('ORDEN #'+object.order.id + ' MESA #'+object.order.board_id,{labels:{info:'ORDEN PROCESADA'}});
          this.refreshOnPusher();
        });
        this.orderChannel.listen('.order-created', (object) => {
          console.log('[PUSHER] EVENT LISTEN cafeteria.order-created PUSHER', object);
          this.$awn.info('ORDEN #'+object.order.id + ' MESA #'+object.order.board_id,{labels:{info:'ORDEN CREADA'}});
          this.refreshOnPusher();
        });
        this.orderChannel.listen('.order-removed', (object) => {
          console.log('[PUSHER] EVENT LISTEN cafeteria.order-removed PUSHER', object);
          this.$awn.info('ORDEN #'+object.order.id + ' MESA #'+object.order.board_id,{labels:{info:'ORDEN ELIMINADA'}});
          this.refreshOnPusher();
        });
        this.orderChannel.listen('.order-updated', (object) => {
          console.log('[PUSHER] EVENT LISTEN cafeteria.order-updated PUSHER', object);
          this.$awn.info('ORDEN #'+object.order.id + ' MESA #'+object.order.board_id,{labels:{info:'ORDEN ACTUALIZADA'}});
          this.refreshOnPusher();
        });
      }
    },
    pusherUnmounted() {
      if (this.pusherCaffeteria) {
        this.boardChannel.stopListening('.board-created');
        this.boardChannel.stopListening('.board-removed');
        this.boardChannel.stopListening('.board-updated');

        this.boardChannel.stopListening('.order-proccesed');
        this.boardChannel.stopListening('.order-created');
        this.boardChannel.stopListening('.order-removed');
        this.boardChannel.stopListening('.order-updated');
      }
    },
    async refreshOnPusher(){
 
      if (ConfigHelper.ConfStr('modulos.cafeteria.submodulos.garzon_mode')) {
        await this.$store.dispatch('waiters/getAllWaiters');
        await this.$store.dispatch('cafeteria/getAllOrdersActives');
      }else{
        await this.$store.dispatch('boards/getBoardsActives');
      }

      var request = await this.$store.dispatch("cafeteria/getCafeteria");
      // Verificando la respuesta
      if (!request.success) this.$awn.alert(request.data);

    },
    async getBoards(loading = false){
      if (!this.cafeteriaInstalled) return this.$router.push('/inicio');
      if(this.offOn) return;
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      if(loading){
        Loader.dinamic();
        if (ConfigHelper.ConfStr('modulos.cafeteria.submodulos.garzon_mode')) {
          await this.$store.dispatch('waiters/getAllWaiters');
          await this.$store.dispatch('cafeteria/getAllOrdersActives');
        }else{
          await this.$store.dispatch('boards/getBoardsActives');
        }
      }else{
        Loader.containe(this.$refs.loaderMesasOrder);
      }

      // Iniciando peticion
      var request = await this.$store.dispatch("cafeteria/getCafeteria");
      Loader.hide();
      this.offOn = false;
      // Verificando la respuesta
      if (!request.success) this.$awn.alert(request.data);

    },
    async openBoard(board, waiter_id = false){
      if(this.offOn) return;
      this.offOn = true;

      this.board = board;
      if(this.board && this.board.waiter == null){
        if(!waiter_id){
          // Abriendo modal para asignar mesero
          this.openAssignWaiter(false);
        }else{
          // Abriendo modal de calogo
          this.board.waiter = { id: waiter_id }
          this.openCatalog();
        }
      }else{
        // Ir al modal de orden completa
        Loader.dinamic();
          await this.$store.dispatch("cafeteria/getBoard", board.id);
        Loader.hide();
        //$('#completeOrder').modal('show');
        this.$root.$emit('show-completeOrder')

        this.offOn = false;
      }
    },
    // Abre el mesero (solo el mesero, modo Garzon)
    async openGarzonWaiter(waiter) {
      if(this.offOn && !waiter) return;
      console.log('OPEN GARZON WAITER')
      this.onlyWaiter = waiter;
      // Deseleccionamos la mesa
      this.$store.commit('cafeteria/clearBoard');
      this.openCatalog();
      console.log(waiter);
    },
    async openCatalog() {
      this.offOn = true;
      Loader.fullPage();
      await this.$store.dispatch("products/getCategories");
      Loader.hide();
      this.offOn = false;
      $('#modalCatalog').modal('show');
    },
    async openAssignWaiter(modalAddtion = false){
      this.offOn = true;
      Loader.fullPage();
      await this.$store.dispatch('waiters/getAllWaiters');
      Loader.hide();
      this.offOn = false;
      if(this.waiters && this.waiters.length == 0){
        this.$awn.alert("No existen meseros disponibles");
        return;
      }
      if(modalAddtion) $('#addtionWaiter').modal('show');
      else $('#modalAssignWaiter').modal('show');
    },

    tiempo_en_ocupacion(board) {
      console.log("[BOARD]waiter:",board.waiter)
      if (board.waiter === null) {
        this.tiempo_uso_class = "tiempo-uso-flag-0"
        return;
      }
      let time = board.order.created_at;
      console.log("[BOARD] time:",time)
      var tiempo_actual = new Date();
      var tiempo_mesa = new Date(time);
      var fecha1 = moment(tiempo_actual);
      var fecha2 = moment(tiempo_mesa);

      console.log((fecha1.diff(fecha2, 'minutes')), ' min de diferencia');
      var diffMins = fecha1.diff(fecha2, 'minutes');
      console.log("[BOARD] diffMins:",diffMins)
      this.tiempo_minutos = diffMins > 0 ? diffMins : 0;
      if (diffMins < 15 ) {
        this.tiempo_uso_class = "tiempo-uso-flag-1"
        return diffMins;
      } 

      if (diffMins < 30) {
        this.tiempo_uso_class = "tiempo-uso-flag-2"
        return diffMins;
      } 

       if (diffMins < 45) {
        this.tiempo_uso_class = "tiempo-uso-flag-3"
        return diffMins;
      }

       this.tiempo_uso_class = "tiempo-uso-flag-4"
        return diffMins;
    }
  },
  computed:{
    // v-model
    envs:{ get(){return this.$store.getters['main/getEnvs']} },
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input',offOn) }
    },
    board:{
      get(){ return this.$store.getters['cafeteria/getBoard'] },
      set(value){ this.$store.commit('cafeteria/setProperty', {key: 'board', data: value}) }
    },
    onlyWaiter:{
      get(){ return this.$store.getters['cafeteria/getOnlyWaiter'] },
      set(value){ this.$store.commit('cafeteria/setProperty', {key: 'onlyWaiter', data: value}) }
    },
    // Data cafeteria
    cafeteria:                    { get(){ return this.$store.getters['cafeteria/getCafeteria'] }},
    cafeteriaInstalled:           { get(){ return ConfigHelper.ConfStr('modulos.cafeteria'); } },
    waiters:                      { get(){ return this.$store.getters['waiters/getAllWaiters'] }},
    addtionsWaitersInstalled:     { get(){ return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.additions_waiter'); } },
    cuponInstalled:               { get(){ return true; } }, // Siempre activo - cambiar a false para desactivar
    isGarzonModeEnabled:          { get(){ return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.garzon_mode'); } },
    desactivarMesasByGarzonMode:  { get(){ return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.garzon_mode.ajustes.desactive_boards'); } },
    boardsChangeColor: { get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.change_board_color'); } },
    pusherCaffeteria: { get(){ return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.pusher_caffeteria'); } }
  }
}
</script>

<style>
.notification-counter{
    position: absolute;
    top: 55px;
    width: 10px;
    height: 10px;
    background: chartreuse;
    right: 20px;
    border-radius: 100px;
    color: #fff;
    font-weight: bold;
    font-size: 10px;
}
.notifications-list{
    z-index: 1;
    width: 500px;
    background: #f8f9fa;
    height: auto;
    right: 10px;
    top: 90px;
    position: absolute;
    transition: 0.4s all ease;
    box-shadow: 0 0 11px 2px rgb(0 0 0 / 30%);
    border:2px solid #0b4f6c;
}

.noti-title{
    padding: 10px;
    border: 1px solid #0b4f6c;
    text-align: center;
    font-weight: bold;
}

.noti-list{
    padding: 10px;
    text-align: left;
    font-size:14px;
}

.tiempo-uso-flag-0 {
    color:black;
}
.tiempo-uso-flag-1{
    color:greenyellow;
   
  }
  .tiempo-uso-flag-2{
    color:lightblue;
    
  }
  .tiempo-uso-flag-3{
    color:yellow;
    
  }
  .tiempo-uso-flag-4{
    color:red;
  }

</style>