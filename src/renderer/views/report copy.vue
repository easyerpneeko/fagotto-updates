<template>
  <div v-if="randToken" class="dashboard-container">
    <!-- Modern Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <div class="header-title">
          <h1 class="dashboard-title">
            <i class="fas fa-chart-line"></i>
            Dashboard de Reportes
          </h1>
          <p class="dashboard-subtitle">Análisis completo de ventas y estadísticas</p>
        </div>
        <div class="header-actions">
          <div class="quick-stats">
            <div class="quick-stat-item">
              <span class="stat-value">{{ listOrder.length }}</span>
              <span class="stat-label">Ventas</span>
            </div>
            <div class="quick-stat-item">
              <span class="stat-value">{{ getWorkshifts ? getWorkshifts.length : 0 }}</span>
              <span class="stat-label">Turnos</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Controls Panel -->
    <div class="controls-panel glass-card">
      <div class="controls-grid">
        <div class="control-group">
          <label class="modern-label">
            <i class="fas fa-calendar-alt"></i>
            Rango de fechas
          </label>
          <div class="input-wrapper">
            <date-picker 
              class="modern-date-picker" 
              format="YYYY-MM-DD" 
              type="date" 
              v-model="rangeDate" 
              range 
              placeholder="Seleccionar fechas" 
              confirm
            ></date-picker>
          </div>
        </div>
        
        <div class="control-group">
          <label class="modern-label">
            <i class="fas fa-search"></i>
            Acción
          </label>
          <button 
            @click="getSells" 
            type="button" 
            :class="['modern-btn primary-btn',{'disabled': offOn}]"
            :disabled="offOn"
          >
            <i class="fas fa-search"></i>
            <span>{{ offOn ? 'Buscando...' : 'Buscar' }}</span>
          </button>
        </div>
        
        <!-- Hidden Time Controls - Maintaining original functionality -->
        <div class="d-none">
          <div class="d-flex flex-column m-1">
            <label>Hora inicial</label>
            <date-picker class="widthInputTime" v-model="startTime" format="HH:mm:ss" value-type="format" type="time" placeholder="Hora" ></date-picker>
          </div>
        </div>
        <div hidden class="d-none">
          <div class="d-flex flex-column m-1">
            <label>Hora final</label>
            <date-picker class="widthInputTime" v-model="endTime" format="HH:mm:ss" value-type="format" type="time" placeholder="Hora" ></date-picker>
          </div>
        </div>

        <!-- Action Buttons Group -->
        <div class="action-buttons-group">
          <div class="button-group">
            <button 
              @click="printSells(false)" 
              type="button" 
              :class="['modern-btn secondary-btn',{'disabled': offOn}]"
              :disabled="offOn"
            >
              <i class="fas fa-print"></i>
              <span>Contadores</span>
            </button>
            <button 
              @click="printSells(true)" 
              type="button" 
              :class="['modern-btn dark-btn',{'disabled': offOn}]"
              :disabled="offOn"
            >
              <i class="fas fa-chart-bar"></i>
              <span>Con Estadísticas</span>
            </button>
          </div>
          
          <div class="shift-controls">
            <button 
              @click="endShift" 
              type="button" 
              :class="['modern-btn warning-btn',{'disabled': offOn}]"
              :disabled="offOn"
            >
              <i class="fas fa-stop-circle"></i>
              <span>Finalizar Turno</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Hidden checkboxes panel - maintaining original functionality -->
    <div class="bg-white col-12 border-radius-4 elevation-1 mt-2 d-none">
      <div class="row p-3">
        <div v-for="(item, index) in ckecks" :key="index" class="d-flex px-3">
          <div class="custom-control custom-checkbox mx-2 my-1">
            <input :disabled="offOn" @change="getSells" type="checkbox" class="custom-control-input" :id="item.key" v-model="item.value">
            <label class="custom-control-label" :for="item.key">{{item.label}}</label>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Dashboard Grid -->
    <div class="dashboard-grid">
      <!-- Summary Cards Section -->
      <div v-if="listOrder.length != 0" class="summary-cards">
        <div class="summary-card gradient-sales">
          <div class="card-background">
            <div class="floating-icon">🛒</div>
          </div>
          <div class="card-content-summary">
            <div class="summary-number">{{ listOrder.length }}</div>
            <div class="summary-text">Total Ventas</div>
            <div class="summary-trend">
              <i class="fas fa-trending-up"></i>
              <span>+12% vs ayer</span>
            </div>
          </div>
        </div>
        
        <div class="summary-card gradient-money" v-if="getCounters && getCounters.length > 0">
          <div class="card-background">
            <div class="floating-icon">�</div>
          </div>
          <div class="card-content-summary">
            <div class="summary-number">{{ getMainTotal }}</div>
            <div class="summary-text">Saldo Total</div>
            <div class="summary-trend">
              <i class="fas fa-chart-line"></i>
              <span>Balance del día</span>
            </div>
          </div>
        </div>
        
        <div class="summary-card gradient-products" v-if="getProductEstadisticas && getProductEstadisticas.length > 0">
          <div class="card-background">
            <div class="floating-icon">📦</div>
          </div>
          <div class="card-content-summary">
            <div class="summary-number">{{ getProductEstadisticas.length }}</div>
            <div class="summary-text">Productos Vendidos</div>
            <div class="summary-trend">
              <i class="fas fa-boxes"></i>
              <span>Tipos únicos</span>
            </div>
          </div>
        </div>
        
        <div class="summary-card gradient-waiters" v-if="getWaiters && getWaiters.length > 0">
          <div class="card-background">
            <div class="floating-icon">👨‍💼</div>
          </div>
          <div class="card-content-summary">
            <div class="summary-number">{{ getWaiters.length }}</div>
            <div class="summary-text">Meseros Activos</div>
            <div class="summary-trend">
              <i class="fas fa-user-check"></i>
              <span>En servicio</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Statistics Cards -->
      <div v-if="listOrder.length != 0" class="stats-section">
        <div class="enhanced-card counters-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-calculator"></i>
              <span>Contadores</span>
            </div>
            <div class="card-actions">
              <button class="icon-btn" @click="getSells">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>
          <div class="card-content">
            <cardTable
              :cardTitle="''"
              :th="titlesCounter"
              :tr="getCounters"
              :idTarget="randToken()"
              :center="true"
            />
          </div>
        </div>

        <div class="enhanced-card products-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-box"></i>
              <span>Estadísticas de Productos</span>
            </div>
            <div class="mini-stats">
              <div class="mini-stat">
                <span class="mini-value">{{ getProductEstadisticas.length }}</span>
                <span class="mini-label">Productos</span>
              </div>
            </div>
          </div>
          <div class="card-content">
            <cardTable
              :cardTitle="''"
              :th="titlesProducts"
              :tr="getProductEstadisticas"
              :idTarget="randToken()"
            />
          </div>
        </div>
      </div>

      <!-- Secondary Information Cards -->
      <div class="secondary-section">
        <!-- Expenses Card -->
        <div v-if="getExpenses && expensesInstalled" class="enhanced-card expenses-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-money-bill-wave"></i>
              <span>Gastos del Día</span>
            </div>
            <div class="expense-indicator">
              <span class="expense-badge">{{ getExpenses.length }} gastos</span>
            </div>
          </div>
          <div class="card-content">
            <cardTable
              cardTitle=""
              :th="['Nombre', 'Total']"
              :tr="getExpenses"
              :idTarget="randToken()"
            />
          </div>
        </div>

        <!-- Workshifts Card -->
        <div class="enhanced-card workshifts-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-clock"></i>
              <span>Turnos del Día</span>
            </div>
            <div class="shift-status">
              <span class="status-indicator active"></span>
              <span class="status-text">Turno Activo</span>
            </div>
          </div>
          <div class="card-content">
            <cardTable
              cardTitle=""
              :th="['Usuario', 'Monto Inicial', 'Total de Ventas', 'Inicio del turno', 'Fin del turno']"
              :tr="getWorkshifts"
              :idTarget="randToken()"
            />
          </div>
        </div>

        <!-- Waiters Card -->
        <div v-if="getWaiters.length > 0 && cafeteriaInstalled && get_cafeteria()" class="enhanced-card waiters-card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-user-tie"></i>
              <span>Meseros</span>
            </div>
            <div class="waiters-count">
              <span class="count-badge">{{ getWaiters.length }} activos</span>
            </div>
          </div>
          <div class="card-content">
            <cardTable
              cardTitle=""
              :th="['Mesero', 'Mesas atendidas', 'Total', 'Propina']"
              :tr="getWaiters"
              :idTarget="randToken()"
            />
          </div>
        </div>
      </div>
      <!-- Additional Services Card (Commented but structure maintained) -->
      <!-- <div v-if="getAddtionWaiters && addtionsWaiterInstalled && get_cafeteria()" class="enhanced-card">
        <cardTable
          cardTitle="Servicios adicionales"
          :th="['Mesero', 'Cantidad', 'Total']"
          :tr="getAddtionWaiters"
          :idTarget="randToken()"
        />
      </div> -->
      
      <!-- Waiter Report Card (Commented but structure maintained) -->
      <!-- <div v-if ="get_cafeteria()" class="enhanced-card">
        <CardReportMesero
          :cardTitle="waiter_name"
          :key="waiter_id"
          :waiter_id="waiter_id"
          :th="getOneWaiterHeaders"
          :tr="getOneWaiter"
          :idTarget="randToken()"
        />
      </div> -->
    </div>
    
    <!-- Enhanced Empty State -->
    <div 
      ref="loaderReport" 
      v-if="listOrder.length == 0 && !getWaiters && !getAddtionWaiters && !getExpenses" 
      class="empty-state"
    >
      <div class="empty-content">
        <div class="empty-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <h2 class="empty-title">Sin resultados</h2>
        <p class="empty-description">
          No se encontraron datos para el rango de fechas seleccionado.
          <br>Intenta ajustar los filtros o seleccionar un período diferente.
        </p>
        <button @click="getSells" class="modern-btn primary-btn">
          <i class="fas fa-search"></i>
          <span>Buscar de nuevo</span>
        </button>
      </div>
    </div>

  </div>
</template>

<script>
//import cardReportMesero from '../components/cards/cardReportMesero.vue';
import cardTable from '@/components/cards/cardTable.vue';
import ConfigHelper from '@/helpers/ConfigHelper.js';
import Print from '@/helpers/Print.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import AllErrors from '@/helpers/AllErrors.js';
// import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
// import { get } from 'request';
import CardReportMesero from '../components/cards/cardReportMesero.vue';
import BarChart from '../components/charts/BarChart.vue';
import AreaChart from '../components/charts/AreaChart.vue';
import PieChart from '../components/charts/PieChart.vue';
import ColumnChart from '../components/charts/ColumnChart.vue';
const fs = require('fs');
export default {
  name:'report',
  data(){
    return{
      // topSellsChartData: [],
      // sellsByhourChartData: [],
      // countersChartData:[],
      waiterChartData:[],
      rangeDate: [new Date(), new Date()],
      startTime: '00:00:00',
      endTime: '23:59:59',

      titlesOrders: ["ID","Monto"],
      titlesCounter: ["Tipos (Metodos/pagos/ganancias/gastos)","Totales"],
      titlesProducts: [],
      // titlesDevoluciones: ["Venta(#)","Nombre del Producto", "Stock", "Valor del Producto",'Motivo'],
      listOrder: [],
      listCounter: [],
      listTr: [],
      // mesero report
      waiterCounter: [],
      waiter_id:1,
      waiter_name:"nop",
      sells: null,
      oldPage: 1,
      // checks
      ckecks:[
        {key:'factura',label:'Facturas', value:true},
        {key:'boleta',label:'Boletas', value:true},
        {key:'guia_despacho',label:'Guia despacho', value:true},
        {key:'fastSell',label:'Ventas rapidas', value:true},
        {key:'noSii',label:'No SII', value:true},
        {key:'amipass',label:'Amipass', value:true},
        {key:'credito',label:'Credito', value:true},
        {key:'rappi',label:'Rappi', value:true},
        {key:'junaeb',label:'Junaeb', value:true},
        {key:'uber',label:'Uber', value:true},
        {key:'convenio_empresa',label:'Convenio Empresa', value:true},
        {key:'pedidos_ya',label:'Pedidos Ya', value:true},
        {key:'pluxee',label:'Pluxee', value:true},
        {key:'banco_chile_20',label:'Banco De Chile 20%', value:true}
      ]
    }
  },
  mounted(){
    console.log('=============REPORTS================')
    //HavePermission
    //this.$store.commit('reports/clearOneWaiter');
    this.getSells();
    // this.getTopSells();
    this.getWaiter(1);
    this.$root.$on('getOneWaiter', (ev) => {
      if (this.waiter_id == $('#select-report-mesero').find(":selected").val()) return;
      this.$store.commit('reports/clearOneWaiter');
      // console.log(ev)
      let waiter_id = $('#select-report-mesero').find(":selected").val();
      this.getWaiter(waiter_id);
      
    
    })
    // console.log('List order mounted: ', this.listOrder);
  },
  components:{
    cardTable,
    CardReportMesero,
    BarChart,
    AreaChart,
    PieChart,
    ColumnChart
},
  props:{
    value: {
      type: Boolean,
      default: false
    }
  },

  methods:{
    async logout(){
      await fs.unlink('authorization.json',(error)=>{
        if (error) {
          // console.log(error);
        }
        this.$router.push('/login');
      });
    },
    formatNumber(number){
      var number=  Math.round(number/10)*10;
      return FormatNumber.format(String(number));
    },
    deFormatNumber(number,backend = true){
      if (backend) {
     //JC   console.log("-------------------------hola",number)

        return FormatNumber.deFormatBackend(String(number));
      }else{
        return FormatNumber.deFormat(String(number));
      }
    },
    startShift(){     
    },
    async endShift(){
      //guardar la hora y fecha de fin de turno en un local storage
      let start_workshift = localStorage.getItem('start_workshift');
      localStorage.setItem('end_workshift', moment().format('YYYY-MM-DD HH:mm:ss'));
      let end_workshift = localStorage.getItem('end_workshift');
      let init_money = localStorage.getItem('init_money');
      let final_money = 0;
      let user = this.$store.getters['main/user'];
      // this.getSell_ext();
      const data = {
        start_workshift: start_workshift,
        end_workshift: end_workshift,
        init_money: init_money,
        final_money: final_money,
        user_id: user.id
      };
      // Crear un objeto FormData
      const formData = new FormData();
      
      for (let key in data) {
        formData.append(key, data[key]);
      }

      this.waitResponse = true;
      Loader.fullPage();
      let request = await this.$store.dispatch('main/newWorkshift', formData);
      Loader.hide();

      if (request.success) {
        this.$awn.success('Turno finalizado Exitosamente', { labels: { success: 'CORRECTO' } });
        localStorage.clear();
        this.logout();
      }else{
        console.log(request.data);
        this.$awn.alert('Error al finalizar el turno');
      }
      this.waitResponse = false;  
      // console.log(data);
      this.printSells(false);    
    },

    async getSell_ext(ref = 'loaderReport'){    // Iniciando refrescamiento (carga y botones disabled)
 
      var startDate = (localStorage.getItem('iniciarTurno'))
      var endDate = (localStorage.getItem('finalizarTurno'))
      const data = {
        startDate: startDate,
        endDate: endDate,
      };
      // console.log("turno",data)

      var thing = new FormData();
      for (let key in data) if (data[key]) thing.append(key, data[key]);

      // Parametros para los contadores
      var params = '?params=true';
      this.ckecks.map((key)=>{
        
        if(key.value) params += '&'+key.key+'=' + key.value;
      });

      // Iniciando peticion
      var request = await this.$store.dispatch("reports/getSells", {data:thing, params});
      // Verificando respuesta
      if(!request){
        this.$awn.info('Ventas no encontradas');
      }else{
        this.sells = request.data;
        this.titlesOrders = [];
        this.listOrder = [];
        if(this.gananciaInstalled) this.titlesOrders = ["ID","Monto","Ganancia"];
        else this.titlesOrders = ["ID","Monto"];
        for (var i = 0; i < this.sells.length; i++) {
          if(this.gananciaInstalled){
            this.listOrder.push([
              this.sells[i].id,
              '$'+this.formatNumber(this.deFormatNumber(String(this.sells[i].total))),
              (this.sells[i].gananciaTotal) ? '$'+this.formatNumber(String(this.deFormatNumber(String(this.sells[i].gananciaTotal)))) : '$0',
            ]);
          }else{
            this.listOrder.push([
              this.sells[i].id,
              '$'+this.formatNumber(String(this.deFormatNumber(String(this.sells[i].total)))),
            ]);
          }
        }
      }
      // console.log('List order ', this.listOrder);

      // Culminando la funcion
      Loader.hide();
      this.offOn = false;

    },


    // Obteniendo reporte de ventas
    async getSells(ref = 'loaderReport'){
      // Iniciando refrescamiento (carga y botones disabled)
      // this.getTopSells();
      // this.getSellsByHour();
      this.offOn = true;
      Loader.containe(this.$refs[ref]);

      // Estableciendo rango de fecha y hora
      if(this.rangeDate.length == 0) return this.$awn.alert('Porfavor inserte un rango de fechas');

      //JC BOTONES DE REPORTE

      var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
      var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

      var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + startTime;
      var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + endTime;
      const data = {
        startDate: startDate,
        endDate: endDate,
      };
      // console.log("original",data)


      var thing = new FormData();
      for (let key in data) if (data[key]) thing.append(key, data[key]);

      // Parametros para los contadores
      var params = '?params=true';
      this.ckecks.map((key)=>{
        
        if(key.value) params += '&'+key.key+'=' + key.value;
      });

      // Iniciando peticion
      var request = await this.$store.dispatch("reports/getSells", {data:thing, params});
      // Verificando respuesta
      if(!request){
        this.$awn.info('Ventas no encontradas');
      }else{
        this.sells = request.data;

        this.titlesOrders = [];
        // console.log('Ganancia installed: ' ,this.gananciaInstalled);
        this.listOrder = [];
        if(this.gananciaInstalled) this.titlesOrders = ["ID","Monto","Ganancia"];
        else this.titlesOrders = ["ID","Monto"];
        for (var i = 0; i < this.sells.length; i++) {
          if(this.gananciaInstalled){
            this.listOrder.push([
              this.sells[i].id,
              '$'+this.formatNumber(this.deFormatNumber(String(this.sells[i].total))),
              (this.sells[i].gananciaTotal) ? '$'+this.formatNumber(String(this.deFormatNumber(String(this.sells[i].gananciaTotal)))) : '$0',
            ]);
          }else{
            this.listOrder.push([
              this.sells[i].id,
              '$'+this.formatNumber(String(this.deFormatNumber(String(this.sells[i].total)))),
            ]);
          }
        }
      }

      // Culminando la funcion
      Loader.hide();
      this.offOn = false;
    },
    async getWaiter(waiter_id) {
      var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
      var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

      var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + startTime;
      var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + endTime;

      const data = {
        startDate: startDate,
        endDate: endDate,
        waiter_id: waiter_id
      };

      var thing = new FormData();
      for (let key in data) if (data[key]) thing.append(key, data[key]);

      // Parametros para los contadores
      var params = '?params=true';
      this.ckecks.map((key)=>{
        if(key.value) params += '&'+key.key+'=' + key.value;
      });
      await this.$store.dispatch("reports/getOneWaiter", {data:thing, params});
      this.waiter_id = waiter_id;
      // console.log("oneWaiter waiter_id:",this.waiter_id)
    },
    // Imprimir en reporte
    async printSells(type = false){
      if(this.rangeDate.length == 0) return this.$awn.alert('Porfavor inserte un rango de fechas');
      if(this.listOrder.length == 0) return this.$awn.alert('No existe un reporte para esta fecha');
      var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
      var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

      var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + startTime;
      var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + endTime;
      const data = {
        startDate: startDate,
        endDate: endDate,
      };

      // Parametros para los contadores

      if(!type) var params = '?params=reporte';
      else var params = '?params=reporte-products';

      this.ckecks.map((key)=>{
        if(key.value) params += '&'+key.key+'=' + key.value;
      });

      var thing = new FormData();
      for (let key in data) if (data[key]) thing.append(key, data[key]);
      // console.log('checks:',this.ckecks);
      Loader.fullPage();
      var request = await this.$store.dispatch("reports/printPDF", {data:thing, params});
      // console.log(request);

      if (request.success) {
        var printPDF = await Print.printBase64(request.data);
        if (printPDF) {
          this.$awn.success('Impresion realizada exitosamente',{labels:{success:'CORRECTO'}});
        }
      }else{
        AllErrors.getError(request.data);
      }

      Loader.hide();
    },
    // Obteniendo top de ventas
    // async getTopSells(){
    //   // Iniciando refrescamiento (carga y botones disabled)
    //   this.offOn = true;
    //   // Loader.containe(this.$refs[ref]);

    //   // Estableciendo rango de fecha y hora
    //   if(this.rangeDate.length == 0) return this.$awn.alert('Por favor inserte un rango de fechas');

    //   //JC BOTONES DE REPORTE

    //   var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
    //   var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

    //   var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD');
    //   var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD');
    //   const data = {
    //     startDate: startDate,
    //     endDate: endDate,
    //   };

    //   var thing = new FormData();
    //   for (let key in data) if (data[key]) thing.append(key, data[key]);

    //   // Parametros para los contadores
    //   var params = '?params=true';
    //   this.ckecks.map((key)=>{
    //     if(key.value) params += '&'+key.key+'=' + key.value;
    //   });

    //   // Iniciando peticion
    //   var request = await this.$store.dispatch("reports/getTopSells", {data:thing, params});
    //   // Verificando respuesta
    //   if(!request){
    //     this.$awn.info('Top Ventas no encontradas');
    //   }else{
    //     let topVentas = request.data;
    //     // console.log('Top ventas: ', topVentas);
    //     let topVentasArray = Object.values(topVentas);
    //     this.topSellsChartData = topVentasArray.map(item => [item.product_name, item.total_quantity]);
    //   }

    //   // Culminando la funcion
    //   // Loader.hide();
    //   this.offOn = false;
    // },
    // async getSellsByHour(){
    //   // Iniciando refrescamiento (carga y botones disabled)
    //   this.offOn = true;
    //   // Loader.containe(this.$refs[ref]);

    //   // Estableciendo rango de fecha y hora
    //   if(this.rangeDate.length == 0) return this.$awn.alert('Por favor inserte un rango de fechas');

    //   //JC BOTONES DE REPORTE

    //   var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
    //   var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

    //   var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD');
    //   var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD')+' 23:59:59';
    //   const data = {
    //     startDate: startDate,
    //     endDate: endDate,
    //   };

    //   var thing = new FormData();
    //   for (let key in data) if (data[key]) thing.append(key, data[key]);

    //   // Parametros para los contadores
    //   var params = '?params=true';
    //   this.ckecks.map((key)=>{
    //     if(key.value) params += '&'+key.key+'=' + key.value;
    //   });

    //   // Iniciando peticion
    //   var request = await this.$store.dispatch("reports/getSellsByHour", {data:thing, params});
    //   // Verificando respuesta
    //   if(!request){
    //     this.$awn.info('Ventas por hora no encontradas');
    //   }else{
    //     this.sellsByhourChartData = request.data;
    //     // console.log('Ventas por hora:', request.data);
    //   }

    //   // Culminando la funcion
    //   // Loader.hide();
    //   this.offOn = false;
    // },
    // async getWorkshifts(){
      
    // },
    get_cafeteria(){// esto detecta si cafeteria esta activo
      let __a = false
      let cafeterria = ConfigHelper.Config().Modules
      for(let i = 0; i < cafeterria.length; i++){
        if(cafeterria[i].key == 'cafeteria'){
          __a = true
          break;
        }
      }
    return __a
    },
    replacePrice(val) {
      var price = String(val);
      price = price.replace('.', ',');
      return price;
    },
    randToken(){
      var token = "CL" + String(parseInt(Math.random() * 9999999999));
      return token;
    },
  },
  computed:{
    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input',offOn) }
    },
    cafeteriaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria'); } },
    dataOptional:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.reporte.ajustes.datos_opcionales') } },
    gananciaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_ganancia') } },
    fastSellInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sell_fast') } },
    siiInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii'); } },
    expensesInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.expenses_day') }},
    addtionsWaiterInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.additions_waiter') }},
    settingBoleta:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta') } },
    settingFactura:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.factura') } },
    settingBoletaLocal:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local') } },
    settingConvenioEmpresa:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.convenio_empresa') } },
    settingDebito:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.debito') } },
    settingTransferencia:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.transferencia') } },
    settingCheque:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.cheque') } },
    settingStateBank:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco') } },
    settingRappi:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.rappi') } },
    settingJunaeb:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.junaeb') } },
    settingUber:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.uber') } },
    settingSodexo:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.sodexo') } },
    settingNotaCredito:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.nota_de_credito') } },
    settingCredito:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.credito') } },
    settingAmipass:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.amipass') } },
    settingMulticaja:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.multicaja') } },
    settingEdenred:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.edenred') } },
    settingPedidosYa:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.pedidos_ya') } },
    settingPluxee:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.pluxee') } },
    settingBancoChile20:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco_chile_20') } },

    getCounters: {
      get(){
        var request = this.$store.getters['reports/getterCounters'];
        if(request){
          this.listCounter = [];
          
          if(this.dataOptional){
            this.listCounter.push(
              ['Ordenes totales',' '+request.orders],
              ['Unidades totales',' '+request.quantityTotal],
              ['Tipos de productos',' '+request.typeProducts]
            );
          }
          
          if(this.siiInstalled){
            if(this.settingFactura) this.listCounter.push( ['Facturas', '$'+this.formatNumber(String(request.factura))]);
            // if(this.settingBoleta) this.listCounter.push( ['Boletas', '$'+this.formatNumber(String(request.boletas))] );
            if(this.settingBoleta) this.listCounter.push( ['Boleta', '$'+this.formatNumber(String(request.boleta))] );
            if(this.settingFactura) this.listCounter.push( ['Guia Despacho', '$'+this.formatNumber(String(request.guia_despacho))]);

            // if(this.settingBoletaLocal) this.listCounter.push( ['Efectivo', '$'+this.formatNumber(String(request.noSii))]);
            if(this.settingBoletaLocal) this.listCounter.push( ['Efectivo', '$'+this.formatNumber(String(request.efectivo))]);
            // if(this.settingBoletaLocal) this.listCounter.push( ['Boleta Local', '$'+this.formatNumber(String(request.boleta_local))]);

            if(this.settingDebito) this.listCounter.push( ['Debito', '$'+this.formatNumber(String(request.debito))]);
            if(this.settingAmipass) this.listCounter.push(['Amipass', '$'+this.formatNumber(String(request.amipass))]);
            if(this.settingRappi) this.listCounter.push(['Rappi', '$'+this.formatNumber(String(request.rappi))]);
            if(this.settingJunaeb) this.listCounter.push(['Junaeb', '$'+this.formatNumber(String(request.junaeb))]);
            if(this.settingUber) this.listCounter.push(['Uber', '$'+this.formatNumber(String(request.uber))]);
            if(this.settingSodexo) this.listCounter.push(['Sodexo', '$'+this.formatNumber(String(request.sodexo))]);
            if(this.settingCredito) this.listCounter.push(['Credito', '$'+this.formatNumber(String(request.credito))]);
            // if(this.settingNotaCredito) this.listCounter.push(['Nota de Credito', '$'+this.formatNumber(String(request.credito))]);
            if(this.settingMulticaja) this.listCounter.push(['Multicaja', '$'+this.formatNumber(String(request.multicaja))]);
            if(this.settingConvenioEmpresa) this.listCounter.push(['Convenio Empresa', '$'+this.formatNumber(String(request.convenio_empresa))]);
            if(this.settingTransferencia) this.listCounter.push( ['Transferencia', '$'+this.formatNumber(String(request.transferencia))]);
            if(this.settingCheque) this.listCounter.push( ['Cheque', '$'+this.formatNumber(String(request.cheque))]);
            if(this.settingStateBank) this.listCounter.push( ['Trasnbank', '$'+this.formatNumber(String(request.banco))]);
            if(this.settingEdenred) this.listCounter.push( ['Edenred', '$'+this.formatNumber(String(request.edenred))]);
            if(this.settingPedidosYa) this.listCounter.push( ['Pedidos Ya', '$'+this.formatNumber(String(request.pedidos_ya))]);
            if(this.settingPluxee) this.listCounter.push( ['Pluxee', '$'+this.formatNumber(String(request.pluxee))]);
            if(this.settingBancoChile20) this.listCounter.push( ['Banco De Chile 20%', '$'+this.formatNumber(String(request.banco_chile_20))]);
            
          }
          if(this.fastSellInstalled){
            this.listCounter.push(
              ['Ventas rapidas', '$'+this.formatNumber(String(request.fastSells))],
            );
          }

          if(this.gananciaInstalled){
            this.listCounter.push(['Ganancia total', '$'+this.formatNumber(String(request.gananciaTotal))]);
          }
          this.listCounter.push(
            ['Saldo total', '$'+this.formatNumber(request.balanceTotal)],
            // ['Monto inicial', '$'+ this.formatNumber(String(this.deFormatNumber(String(request.init_money))))],
          );
          
          if(this.expensesInstalled){
            this.listCounter.push(
              ['Gastos del día', '$'+this.formatNumber(String(request.expenses_day))],
              ['Resumen total', '$'+this.formatNumber(String(request.totalToExpenses))],
            );
          }
        }
        // this.countersChartData = request;
        // this.countersChartData = this.listCounter.map(item => [
        //   item[0], // Mantener el primer elemento sin cambios
        //   Number(item[1].substring(1)) // Eliminar el símbolo "$" y convertir el valor a número
        // ]);
        return this.listCounter;
      }
    },
    getOneWaiterHeaders: {
      get() {
        return ['Mesa', 'Productos', 'X', 'Totales'];
      }
    },
    getOneWaiter:{
      get(){
        
        // console.log("reportes.vue GetOneWaiter")
        //await this.$store.commit('reports/clearOneWaiter');
        var request = this.$store.getters['reports/getterOneWaiter'];
        if(request){
          // mesa producto total
          this.waiterCounter = []
          this.waiterCounter.push([
              "Sumario",
              "Atendidos:"+request.report.length ,
              "",
              ""
            ])
          request.report.map((mesa) => {

            // validaciones
            if (mesa.discount === null) mesa.discount = 0;
            // conversions y calculos
            mesa.subtotal = parseFloat(mesa.total);
            mesa.ntotal = (mesa.subtotal + mesa.tip) - mesa.discount;

            // formateos

            mesa.fsubtotal = FormatNumber.format(mesa.subtotal)
            mesa.ftotal = FormatNumber.format(mesa.ntotal)
            mesa.fdiscount = FormatNumber.format(mesa.discount)
            mesa.ftip = FormatNumber.format(mesa.tip)

            
            

            this.waiterCounter.push([
              mesa.board_name,
              "",
              //"Total: "+mesa.total+" | Propina: "+mesa.tip+" | Descuento: "+mesa.discount,
              "",
              ""
            ])

            

            mesa.products.map((product) => {
              product.fprice = FormatNumber.deFormatBackendN(String(product.price))
              this.waiterCounter.push([
              "",
              product.name,
              product.quantity+"x",
              "$"+product.fprice
            ])
            })

            this.waiterCounter.push([
              "",
              "Subtotal: $"+mesa.fsubtotal+" | Propina: $"+mesa.ftip,
              "",
              ""
            ])

            this.waiterCounter.push([
              "",
              "Descuento: $"+mesa.fdiscount +" | Total $"+ mesa.ftotal,
              "",
              ""
            ])
            
          }) // out of bound
          
          
          
          // console.log("reporte de mesero->",request)

        }
        this.waiter_name = request.name;
        return this.waiterCounter;
      }
    },
    getProductEstadisticas:{
      get(){
        var request = this.$store.getters['reports/getterProductEstadisticas'];
        
        if(request){
          this.listTr = [];
          for (var i = 0; i < request.length; i++) {
            if(this.gananciaInstalled) {
              this.listTr.push(
                [
                  request[i].name,                                                //Nombre
                  request[i].quantity,                                            //Stock vendido
                  '$'+this.formatNumber(request[i].price),                        //Valor del producto
                  '$'+FormatNumber.format(request[i].totalProfit),                //Ganancia (Ganancia_total)
                  '$'+this.formatNumber(request[i].quantity*request[i].price)     //Total Vendido (Monto_total)
                ],
              );
            } else {
              this.listTr.push(
                [
                  request[i].name,                                                //Nombre
                  request[i].quantity,                                            //Stock vendido
                  '$'+this.formatNumber(request[i].price),                        //Valor del producto
                  // '$'+FormatNumber.format(request[i].totalProfit),                //Ganancia (Ganancia_total)
                  '$'+this.formatNumber(request[i].quantity*request[i].price)     //Total Vendido (Monto_total)
                ],
              );
            }
          }
        }
        this.titlesProducts = [];
        if(this.gananciaInstalled) this.titlesProducts.push("Nombre del Producto", "Stock Vendido", "Valor del Producto", "Ganancia", 'Total Vendido');
        else this.titlesProducts.push("Nombre del Producto", "Stock Vendido", "Valor del Producto", 'Total Vendido');
        Loader.hide();
        this.offOn = false;
        
        return this.listTr;
      }
    },
    // getProductDevolutions:{
    //   get(){
    //     var request = this.$store.getters['reports/getterProductEstadisticas'];
    //     if(request){
    //       this.listTr = [];
    //       for (var i = 0; i < request.length; i++) {
    //         this.listTr.push(
    //           [
    //             1,
    //             'Producto A',
    //             2,
    //             '$'+2000,
    //             'Motivo de la devolucion'
    //           ],
    //         );
    //       }
    //     }

    //     return this.listTr;
    //   }
    // },
    getWaiters:{
      get(){
        let request = this.$store.getters['reports/getterWaiters'];
        if(request && request.length > 0){
          let myList = [];
          request.map((waiter)=>{
            myList.push([
              waiter.waiter,
              waiter.orders,
              '$' + this.formatNumber(waiter.total),
              '$' + this.formatNumber(waiter.propina)
            ]);
          });
          return myList;
        }else return false;
      }
    },
    getWorkshifts:{
      get(){
        let request = this.$store.getters['reports/getterWorkshifts'];
        // console.log('Turno',request);
        let user = this.$store.getters['main/user'];
        if(request && request.length > 0){
          let myList = [];
          request.map((workshift)=>{
            myList.push([
              workshift.user.fullname,
              '$' + this.formatNumber(workshift.init_money),
              '$' + this.formatNumber(workshift.final_money),
              workshift.start_workshift,
              workshift.end_workshift,
            ]);
          });
          myList.push([
              user.username,
              '$' + localStorage.getItem('init_money'),
              '$0',
              localStorage.getItem('start_workshift'),
              'No finalizado',
            ]);
          return myList;
        }else return false;
      }
    },
    getExpenses:{
      get(){
        let request = this.$store.getters['reports/getterExpenses'];
        if(request && request.length > 0){
          let myList = [];
          request.map((expense)=>{
            myList.push([
              expense.name,
              '$' + this.formatNumber(String(expense.balance))
            ]);
          });
          return myList;
        }else return false;
      }
    },
    getAddtionWaiters:{
      get(){
        let request = this.$store.getters['reports/getterWaiters'];
        if(request && request.length > 0){
          let myList = [];
          request.map((waiter)=>{
            myList.push([
              waiter.waiter,
              waiter.addtions.quantity,
              '$' + this.formatNumber(String(waiter.addtions.balanceTotal))
            ]);
          });
          return myList;
        }else return false;
      }
    },
    getMainTotal:{
      get(){
        var request = this.$store.getters['reports/getterCounters'];
        if(request && request.balanceTotal){
          return '$' + this.formatNumber(request.balanceTotal);
        }
        return '$0';
      }
    },
  }
}
</script>

<style scoped>
/* Import Google Font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Modern Dashboard Styles - Refreshed */
.dashboard-container {
  background: #f8fafc;
  min-height: 100vh;
  padding: 2rem;
  font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #1a202c;
}

/* Dashboard Header with subtle gradient */
.dashboard-header {
  margin-bottom: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.header-title {
  color: white;
}

.dashboard-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 1rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.dashboard-title i {
  color: #ffd700;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.dashboard-subtitle {
  font-size: 1.1rem;
  margin: 0.5rem 0 0 0;
  opacity: 0.95;
  font-weight: 400;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.quick-stats {
  display: flex;
  gap: 1rem;
}

.quick-stat-item {
  text-align: center;
  color: white;
  background: rgba(255,255,255,0.15);
  padding: 1rem 1.5rem;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.2);
  min-width: 80px;
}

.stat-value {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: #ffd700;
}

.stat-label {
  display: block;
  font-size: 0.85rem;
  opacity: 0.9;
  margin-top: 0.25rem;
  font-weight: 500;
}

/* Glass Card Effect - More subtle */
.glass-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
}

/* Controls Panel */
.controls-panel {
  margin-bottom: 2rem;
}

.controls-grid {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 1.5rem;
  align-items: end;
}

.control-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.modern-label {
  color: #4a5568;
  font-weight: 600;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.modern-label i {
  color: #667eea;
  font-size: 1rem;
}

.input-wrapper {
  position: relative;
}

.modern-date-picker {
  width: 100% !important;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 2px solid #e2e8f0;
  background: white;
  color: #2d3748;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  font-family: 'Inter', sans-serif;
}

.modern-date-picker:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  outline: none;
}

/* Modern Buttons - Refined */
.modern-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  position: relative;
  font-family: 'Inter', sans-serif;
}

.primary-btn {
  background: #667eea;
  color: white;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
}

.primary-btn:hover {
  background: #5a67d8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.secondary-btn {
  background: #718096;
  color: white;
  box-shadow: 0 2px 8px rgba(113, 128, 150, 0.2);
}

.secondary-btn:hover {
  background: #4a5568;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(113, 128, 150, 0.3);
}

.dark-btn {
  background: #2d3748;
  color: white;
  box-shadow: 0 2px 8px rgba(45, 55, 72, 0.2);
}

.dark-btn:hover {
  background: #1a202c;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(45, 55, 72, 0.3);
}

.warning-btn {
  background: #ed8936;
  color: white;
  box-shadow: 0 2px 8px rgba(237, 137, 54, 0.2);
}

.warning-btn:hover {
  background: #dd6b20;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(237, 137, 54, 0.3);
}

.modern-btn.disabled,
.modern-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

/* Action Buttons */
.action-buttons-group {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.button-group {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.shift-controls {
  display: flex;
  gap: 0.75rem;
}

.icon-btn {
  background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.icon-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
  transform: translate(-50%, -50%);
  transition: all 0.3s ease;
  border-radius: 50%;
}

.icon-btn:hover::before {
  width: 60px;
  height: 60px;
}

.icon-btn:hover {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.icon-btn i {
  font-size: 1rem;
  transition: all 0.3s ease;
  position: relative;
  z-index: 2;
}

.icon-btn:hover i {
  transform: scale(1.1);
}

/* Dashboard Grid - Better spacing */
.dashboard-grid {
  display: grid;
  gap: 2rem;
  grid-template-columns: 1fr;
}

.stats-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.secondary-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
}

/* Enhanced Cards - Clean design UPGRADED */
.enhanced-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.enhanced-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.enhanced-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.enhanced-card:hover::before {
  transform: scaleX(1);
}

.card-header {
  padding: 1.75rem 1.5rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
  position: relative;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.2rem;
  font-weight: 700;
  color: #2d3748;
  position: relative;
}

.card-title i {
  color: #667eea;
  font-size: 1.3rem;
  padding: 0.5rem;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.enhanced-card:hover .card-title i {
  background: rgba(102, 126, 234, 0.2);
  transform: scale(1.05);
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.card-content {
  padding: 1.75rem 1.5rem;
  background: white;
}

/* Mini Stats - Improved design */
.mini-stats {
  display: flex;
  gap: 1rem;
}

.mini-stat {
  text-align: center;
  background: linear-gradient(135deg, #edf2f7 0%, #f7fafc 100%);
  padding: 0.875rem 1.125rem;
  border-radius: 12px;
  min-width: 70px;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.mini-stat:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
  border-color: #667eea;
}

.mini-value {
  display: block;
  font-weight: 800;
  color: #667eea;
  font-size: 1.3rem;
  line-height: 1;
}

.mini-label {
  display: block;
  font-size: 0.75rem;
  color: #718096;
  margin-top: 0.35rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Status Indicators - IMPROVED */
.expense-indicator,
.shift-status,
.waiters-count {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.expense-badge,
.count-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.expense-badge:hover,
.count-badge:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: linear-gradient(135deg, #48bb78, #38f9d7);
  animation: statusPulse 2s infinite;
  box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7);
}

@keyframes statusPulse {
  0% { 
    box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7);
    transform: scale(1);
  }
  50% { 
    transform: scale(1.1);
  }
  70% { 
    box-shadow: 0 0 0 8px rgba(72, 187, 120, 0);
  }
  100% { 
    box-shadow: 0 0 0 0 rgba(72, 187, 120, 0);
    transform: scale(1);
  }
}

.status-text {
  color: #48bb78;
  font-weight: 700;
  font-size: 0.9rem;
  text-shadow: 0 1px 2px rgba(72, 187, 120, 0.1);
}

/* Enhanced Empty State */
.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  padding: 3rem 1.5rem;
}

.empty-content {
  text-align: center;
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  padding: 3rem 2rem;
  max-width: 500px;
}

.empty-icon {
  margin-bottom: 1.5rem;
}

.empty-icon i {
  font-size: 3.5rem;
  color: #cbd5e0;
}

.empty-title {
  color: #2d3748;
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
}

.empty-description {
  color: #718096;
  font-size: 1rem;
  line-height: 1.6;
  margin: 0 0 2rem 0;
}

/* Table Enhancements */
.enhanced-card :deep(.table) {
  margin-bottom: 0;
}

.enhanced-card :deep(.table thead th) {
  background-color: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  color: #4a5568;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 1rem 0.75rem;
  position: sticky;
  top: 0;
  z-index: 10;
}

.enhanced-card :deep(.table tbody tr) {
  transition: background-color 0.15s ease;
}

.enhanced-card :deep(.table tbody tr:nth-child(even)) {
  background-color: #f9fafb;
}

.enhanced-card :deep(.table tbody tr:hover) {
  background-color: #f1f5f9;
}

.enhanced-card :deep(.table tbody td) {
  padding: 0.875rem 0.75rem;
  font-size: 0.9rem;
  color: #2d3748;
  border-top: 1px solid #f1f5f9;
}

/* Summary Cards for key metrics - REDESIGNED */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-card {
  position: relative;
  border-radius: 20px;
  padding: 0;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
  min-height: 140px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.summary-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

/* Gradientes específicos para cada tarjeta */
.gradient-sales {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-money {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.gradient-products {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.gradient-waiters {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.card-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow: hidden;
}

.card-background::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20px;
  width: 120px;
  height: 120px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  transition: all 0.4s ease;
}

.summary-card:hover .card-background::before {
  transform: scale(1.2);
  opacity: 0.2;
}

.floating-icon {
  position: absolute;
  top: 15px;
  right: 20px;
  font-size: 2.5rem;
  opacity: 0.3;
  transition: all 0.4s ease;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.summary-card:hover .floating-icon {
  transform: scale(1.1) rotate(5deg);
  opacity: 0.5;
}

.card-content-summary {
  position: relative;
  z-index: 2;
  padding: 1.5rem;
  color: white;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.summary-number {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  line-height: 1;
}

.summary-text {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  opacity: 0.95;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.summary-trend {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  opacity: 0.9;
  font-weight: 500;
}

.summary-trend i {
  font-size: 0.9rem;
  opacity: 0.8;
}

/* Animación de pulso para números */
@keyframes numberPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.summary-card:hover .summary-number {
  animation: numberPulse 0.6s ease;
}

/* Efecto de ondas en hover */
.summary-card::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: all 0.6s ease;
}

.summary-card:hover::after {
  width: 300px;
  height: 300px;
}

/* Responsive para las nuevas tarjetas */
@media (max-width: 1200px) {
  .summary-cards {
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  }
}

@media (max-width: 768px) {
  .summary-cards {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
  }
  
  .summary-card {
    min-height: 120px;
  }
  
  .summary-number {
    font-size: 2rem;
  }
  
  .floating-icon {
    font-size: 2rem;
    top: 10px;
    right: 15px;
  }
  
  .card-content-summary {
    padding: 1.25rem;
  }
}

@media (max-width: 480px) {
  .summary-cards {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .summary-card {
    min-height: 100px;
  }
  
  .summary-number {
    font-size: 1.8rem;
  }
  
  .summary-text {
    font-size: 0.9rem;
  }
}

/* Responsive Design - Improved */
@media (max-width: 1200px) {
  .stats-section {
    grid-template-columns: 1fr;
  }
  
  .secondary-section {
    grid-template-columns: 1fr;
  }
  
  .summary-cards {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }
  
  .dashboard-header {
    padding: 1.5rem;
  }
  
  .dashboard-title {
    font-size: 1.8rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .controls-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .action-buttons-group {
    width: 100%;
  }
  
  .button-group,
  .shift-controls {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .quick-stats {
    flex-direction: column;
    gap: 0.75rem;
    width: 100%;
  }
  
  .card-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .mini-stats {
    justify-content: center;
  }
  
  .summary-cards {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .enhanced-card {
    border-radius: 8px;
  }
  
  .card-header,
  .card-content {
    padding: 1rem;
  }
  
  .dashboard-title {
    font-size: 1.5rem;
  }
  
  .empty-content {
    padding: 2rem 1.5rem;
  }
  
  .empty-icon i {
    font-size: 3rem;
  }
  
  .empty-title {
    font-size: 1.5rem;
  }
  
  .enhanced-card :deep(.table thead th),
  .enhanced-card :deep(.table tbody td) {
    padding: 0.5rem 0.5rem;
    font-size: 0.8rem;
  }
}

/* Legacy styles compatibility */
.widthInput{
  width: 210px !important;
}
.widthInputTime{
  width: 160px !important;
}
.hv-80{
  height: 80vh !important;
}

/* Maintain old responsive breakpoints for compatibility */
@media (max-width: 875px){
  .widthInputTime{
    width: 100% !important;
  }
  .widthInput{
    width: 100% !important;
  }
  .mediaWidth{
    width: 100% !important;
    margin: 5px 0px !important;
  }
  .mediaWidth2{
    width: 50% !important;
    margin: 5px 0px !important;
  }
  .ml-auto{
    margin-top: 5px !important;
  }
}
</style>
