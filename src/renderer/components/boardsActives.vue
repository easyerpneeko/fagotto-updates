<template>
  <div class="panel_father">
    <div class="panel_title bg-primario">
      Ordenes activas
    </div>
    <template v-if="isGarzonModeEnabled">
      <!-- Ordenes activas -->
      <div v-if="(allOrdersActives && allOrdersActives.length > 0)" class="p-0" style="margin-top:46px;">
        <div @click="openOrderActive(item)" :class="['panel_items', (!item.board) ? 'no_hover' : '']" v-for="(item, index) in allOrdersActives" :key="index">
          <div>
            <span>#{{ item.id }}</span>
            <template v-if="!item.board">
              <br />
              <span style="cursor:default!important;user-select: all!important;">
                {{ item.barcode }}
              </span>
              <br />
            </template>
            <span class="panel_items_title text-bold text-capitalize">{{ item.waiter.name }}</span>
          </div>
          <div class="text-bold">
            ${{formatNumber(deFormatNumber( item.total ))}}
          </div>
        </div>
      </div>
      <div v-else style="margin-top:46px; padding: 15px 0px; text-align: center;">
        <h6>No existen ordenes activas</h6>
      </div>
    </template>
    <template v-else>
      <!-- SOLO Ordenes con MESA activas -->
      <div v-if="(boardsActives && boardsActives.length > 0)" class="p-0" style="margin-top:46px;">
        <div @click="openBoardActive(item)" :class="['panel_items']" v-for="(item, index) in boardsActives" :key="index">
          <div>
            <span>#{{item.order.id}}</span>
            <span class="panel_items_title text-bold text-capitalize">{{item.waiter.name}}</span>
          </div>
          <div class="text-bold">
            ${{ formatNumber(deFormatNumber(item.order.total)) }}
          </div>
        </div>
      </div>
      <div v-else style="margin-top:46px; padding: 15px 0px; text-align: center;">
        <h6>No existen ordenes activas</h6>
      </div>
    </template>

    <!-- Footer con Meta Diaria -->
    <div v-if="currentMeta && currentMeta.meta_diaria" class="panel_footer">
      <!-- Contenedor de corazones flotantes -->
      <div class="hearts-container">
        <div v-for="heart in floatingHearts" :key="heart.id" class="floating-heart" :style="heart.style">
          ❤️
        </div>
      </div>
      
      <div class="meta-container">
        <div class="fecha-dia">
          <span class="fecha-label">📅 {{ fechaHoy }}</span>
        </div>
        <div class="meta-info">
          <span class="meta-label">Meta del Día:</span>
          <span class="meta-value">${{ formatNumber(deFormatNumber(currentMeta.meta_diaria)) }}</span>
        </div>
        <div class="meta-progress-info">
          <span class="ventas-label">Ventas:</span>
          <span class="ventas-value">${{ formatNumber(ventasHoy) }}</span>
          <span class="porcentaje-badge" :class="porcentajeClass">{{ porcentajeCumplimiento }}%</span>
        </div>
      </div>
      <div class="progress-bar-container">
        <div class="progress-bar" :style="{ width: porcentajeCumplimiento + '%' }" :class="porcentajeClass"></div>
      </div>
    </div>
    <div v-else-if="currentMeta === null" class="panel_footer">
      <div class="meta-container">
        <span class="meta-label-empty">⚠️ Sin meta configurada</span>
      </div>
    </div>

    <complete-order @refresh="refreshData" />
    <assing-waiter  @refresh="openBoard" @success="openBoard"/>
    <catalog        @refresh="refreshData"/>

  </div>
</template>

<script>
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';
// Modals
import assingWaiter from '@/components/modals/cafeteria/assignWaiter.vue';
import catalog from '@/components/modals/cafeteria/catalog.vue';
import completeOrder from '@/components/modals/cafeteria/completeOrder.vue';

export default {
  components:{ completeOrder, assingWaiter, catalog },
  props:{
    value: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      floatingHearts: [],
      heartIdCounter: 0,
      previousVentas: 0
    }
  },
  methods:{
    async openBoardActive(board){
  
      if(this.offOn) return;
      this.offOn = true;
      Loader.dinamic();
        await this.$store.dispatch("cafeteria/getBoard", board.id);
      Loader.hide();
      this.offOn = false;
      
      $('#completeOrder').modal('show');
  //    completeOrder.show();
    },
    async openOrderActive(order){
      if (order.board) // Si tiene una mesa...
        this.openBoardActive(order.board);
      else {
        // Si no tiene una mesa
        console.log('Abriendo orden...', order)
        /*this.openBoardActive(null);*/
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

    // Traer todos los meseros
    async refreshData(loading = false){
      console.log('Refresh data boards actives')
      if (!this.cafeteriaInstalled) return this.$router.push('/inicio');
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      if(loading){
        Loader.dinamic();

        console.log('get boards, garzon mode:', this.isGarzonModeEnabled)

        if (this.isGarzonModeEnabled) //Ordenes activas
          await this.$store.dispatch('cafeteria/getAllOrdersActives');
        else //Mesas activas
          await this.$store.dispatch('boards/getBoardsActives');

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

    // Abrir mesa clickeada
    openBoard(board, waiter_id = false){
      this.board = board;
      if(this.board && (this.board.waiter == null || this.board.waiter.id == null)){
        if(!waiter_id){
          // Abriendo modal para asignar mesero
          this.openAssignWaiter();
        }else{
          // Abriendo modal de calogo
          this.board.waiter = { id: waiter_id }
          this.openCatalog();
        }
      }else{
        // Ir al modal de orden completa
        this.openCatalog();
      }
    },
    async openCatalog(){
      Loader.fullPage();
      await this.$store.dispatch("products/getCategories");
      Loader.hide();
      $('#modalCatalog').modal('show');
    },
    async openAssignWaiter(){
      Loader.fullPage();
      await this.$store.dispatch('waiters/getAllWaiters');
      Loader.hide();
      if(this.waiters && this.waiters.length == 0){
        this.$awn.alert("No existen meseros disponibles");
        return;
      }
      $('#modalAssignWaiter').modal('show');
    },
    lanzarCorazones() {
      // Crear 3-5 corazones aleatorios
      const cantidad = Math.floor(Math.random() * 3) + 3;
      for (let i = 0; i < cantidad; i++) {
        setTimeout(() => {
          const heart = {
            id: this.heartIdCounter++,
            style: {
              left: Math.random() * 80 + 10 + '%',
              animationDelay: Math.random() * 0.5 + 's',
              fontSize: Math.random() * 10 + 20 + 'px'
            }
          };
          this.floatingHearts.push(heart);
          
          // Eliminar corazón después de la animación (3 segundos)
          setTimeout(() => {
            const index = this.floatingHearts.findIndex(h => h.id === heart.id);
            if (index !== -1) {
              this.floatingHearts.splice(index, 1);
            }
          }, 3000);
        }, i * 200);
      }
    }
  },
  computed:{
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input',offOn) }
    },
    boardsActives:    { get(){ return this.$store.getters['boards/getBoardsActives'] } },
    allOrdersActives: { get(){ return this.$store.getters['cafeteria/getAllOrdersActives'] } },
    board:{
      get(){ return this.$store.getters['cafeteria/getBoard'] },
      set(value){ this.$store.commit('cafeteria/setProperty', {key: 'board', data: value}) }
    },
    currentMeta: { get(){ return this.$store.getters['metas/getCurrentMeta'] } },
    reportCounters: { get(){ return this.$store.getters['reports/getterCounters'] } },
    ventasHoy() {
      // Usar el balanceTotal del reporte del día actual
      if (this.reportCounters && this.reportCounters.balanceTotal) {
        return parseFloat(this.reportCounters.balanceTotal);
      }
      return 0;
    },
    porcentajeCumplimiento() {
      if (!this.currentMeta || !this.currentMeta.meta_diaria) return 0;
      const meta = parseFloat(this.currentMeta.meta_diaria);
      const ventas = parseFloat(this.ventasHoy);
      if (meta === 0) return 0;
      return Math.min(Math.round((ventas / meta) * 100), 100);
    },
    porcentajeClass() {
      const porcentaje = this.porcentajeCumplimiento;
      if (porcentaje >= 100) return 'completado';
      if (porcentaje >= 75) return 'alto';
      if (porcentaje >= 50) return 'medio';
      return 'bajo';
    },
    fechaHoy() {
      const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
      const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
      const hoy = new Date();
      return `${dias[hoy.getDay()]}, ${hoy.getDate()} de ${meses[hoy.getMonth()]}`;
    },
    // HavePermission
    cafeteriaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria'); } },
    waiters:{ get(){ return this.$store.getters['waiters/getAllWaiters'] } },
    isGarzonModeEnabled:      { get(){ return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.garzon_mode'); }}
  },
  watch: {
    ventasHoy(newValue, oldValue) {
      // Detectar cuando hay una venta nueva
      if (newValue > oldValue && oldValue > 0) {
        this.lanzarCorazones();
      }
    }
  },
  async mounted() {
    // Cargar la meta diaria del local
    await this.$store.dispatch('metas/fetchCurrentMeta');
    
    // Cargar los counters del día actual (ventas del día)
    const hoy = new Date();
    const params = {
      startDate: hoy.toISOString().split('T')[0] + ' 00:00:00',
      endDate: hoy.toISOString().split('T')[0] + ' 23:59:59',
      params: true,
      factura: true,
      boleta: true,
      fastSell: true,
      noSii: true,
      credito: true,
      debito: true,
      efectivo: true
    };
    await this.$store.dispatch('reports/GetCounters', params);
    
    // Guardar ventas iniciales
    this.previousVentas = this.ventasHoy;
  }
}
</script>

<style lang="scss">
  .panel_father{
    width: 100%;
    display: flex;
    flex-direction: column;
    padding: 0px;
    margin: 0px;
    border: 1px solid var(--primary);
    border-collapse: collapse;
    height: 100%;
    overflow: auto;
  }
  .panel_title{
    position:absolute;
    width: calc(100% - 17px);
    padding: 10px;
    border: 1px solid var(--primary);
    border: 1px solid var(--primary);
    text-align: center;
    font-weight: bold;
  }
  .panel_items{
    width: 100%;
    padding: 0px 10px;
    border: 1px solid var(--primary);
    text-align: center;
    cursor: auto;
    transition: .2s all ease;
    user-select: none;
    &_title{
      transition: .2s all ease;
      color: var(--primary);
    }
  }

  .panel_items:hover{
    cursor: pointer;
    color: #fff !important;
    background: var(--primary);
    padding: 10px;
    .panel_items_title{
      color: #fff;
    }
    &.no_hover {
      cursor: auto;
      padding: 0px 10px;
      color: inherit !important;
      .panel_items_title{
        color: inherit !important;
      }
      background: none;
    }
  }

  .panel_items_active{
    color: #fff !important;
    background: var(--primary);
    padding: 10px;
    .panel_items_title{
      color: #fff;
    }
  }

  .panel_footer{
    margin-top: auto;
    padding: 15px 12px;
    border-top: 2px solid var(--primary);
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    position: relative;
    overflow: hidden;
  }

  .hearts-container{
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 10;
  }

  .floating-heart{
    position: absolute;
    bottom: 0;
    animation: floatUp 3s ease-out forwards;
    opacity: 0;
    user-select: none;
  }

  @keyframes floatUp {
    0% {
      bottom: 0;
      opacity: 1;
      transform: translateY(0) scale(1) rotate(0deg);
    }
    50% {
      opacity: 1;
      transform: translateY(-50px) scale(1.2) rotate(15deg);
    }
    100% {
      bottom: 100%;
      opacity: 0;
      transform: translateY(-100px) scale(0.8) rotate(-15deg);
    }
  }

  .meta-container{
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
  }

  .fecha-dia{
    text-align: center;
    padding: 4px 0;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 6px;
  }

  .fecha-label{
    font-weight: 600;
    color: #495057;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
  }

  .meta-info{
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .meta-progress-info{
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 15px;
  }

  .meta-label{
    font-weight: 700;
    color: #495057;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .ventas-label{
    font-weight: 600;
    color: #6c757d;
    font-size: 15px;
  }

  .ventas-value{
    font-weight: 700;
    color: #495057;
    font-size: 16px;
  }

  .porcentaje-badge{
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: bold;
    font-size: 14px;
    
    &.bajo{
      background: #ffc107;
      color: #000;
    }
    
    &.medio{
      background: #17a2b8;
      color: #fff;
    }
    
    &.alto{
      background: #28a745;
      color: #fff;
    }
    
    &.completado{
      background: #20c997;
      color: #fff;
    }
  }

  .meta-label-empty{
    font-weight: 500;
    color: #6c757d;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
    width: 100%;
    justify-content: center;
  }

  .meta-value{
    font-weight: bold;
    color: var(--primary);
    font-size: 20px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
  }

  .progress-bar-container{
    width: 100%;
    height: 10px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    position: relative;
    z-index: 1;
  }

  .progress-bar{
    height: 100%;
    transition: width 0.5s ease, background 0.3s ease;
    border-radius: 10px;
    
    &.bajo{
      background: linear-gradient(90deg, #ffc107 0%, #ffca28 100%);
    }
    
    &.medio{
      background: linear-gradient(90deg, #17a2b8 0%, #20c1db 100%);
    }
    
    &.alto{
      background: linear-gradient(90deg, #28a745 0%, #34ce57 100%);
    }
    
    &.completado{
      background: linear-gradient(90deg, #20c997 0%, #29e6b3 100%);
    }
  }
</style>
