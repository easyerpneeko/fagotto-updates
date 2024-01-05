<template>
  <div v-if="randToken" class="product-bg p-3">
    <!-- Header de inputs date -->
    <div class="bg-white col-12 border-radius-4 elevation-1">
      <div class="row p-3">
        <div class="mediaWidth">
          <div class="d-flex flex-column m-1">
            <label>Rango de fechas</label>
            <date-picker class="widthInput" format="YYYY-MM-DD" type="date" v-model="rangeDate" range placeholder="Fechas" confirm></date-picker>
          </div>
        </div>
       
        <div class= "d-none">
          <div class  ="d-flex flex-column m-1">
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

        
        <!-- <div class="d-flex flex-column mediaWidth2">
          <div class="d-flex flex-column m-1">
            <label>Iniciar turno</label>
           <button @click="startShift" type="button" :class="['fontSizeBtn btn bg-secundario m-0 mx-1',{'disabled': offOn}]">Iniciar turno</button>
          </div>
        </div> -->

        <div class="d-flex flex-column mediaWidth2">
          <div class="d-flex flex-column m-1">
            <label>Finalizar turno</label>
           <button @click="endShift" type="button" :class="['fontSizeBtn btn bg-secundario m-0 mx-1',{'disabled': offOn}]">Finalizar turno</button>
          </div>
        </div>

        <div class="m-1 d-flex align-items-end ml-auto">
          



          <button @click="getSells" type="button" :class="['fontSizeBtn btn bg-secundario m-0 mx-1',{'disabled': offOn}]">Buscar</button>
          <button @click="printSells(false)" type="button" :class="['fontSizeBtn btn bg-primario m-0 mx-1',{'disabled': offOn}]">Contadores</button>
          <button @click="printSells(true)" type="button" :class="['fontSizeBtn btn bg-dark m-0 mx-1',{'disabled': offOn}]">Contadores + Estadisticas</button>
        </div>
      </div>
    </div>
    <!-- <div class="bg-white col-12 border-radius-4 elevation-1 mt-2">
      <div class="row p-3">
        <div v-for="(item, index) in ckecks" :key="index" class="d-flex px-3">
          <div class="custom-control custom-checkbox mx-2 my-1">
            <input :disabled="offOn" @change="getSells" type="checkbox" class="custom-control-input" :id="item.key" v-model="item.value">
            <label class="custom-control-label" :for="item.key">{{item.label}}</label>
          </div>
        </div>
      </div>
    </div> -->

    <!-- Cards de resultados -->
    <div ref="loaderReport" class="vld-parent row mt-2">
      
      <div v-if="listOrder.length != 0" class="col-md-6 col-12 my-2">
        <cardTable
          :cardTitle="'Contadores'"
          :th="titlesCounter"
          :tr="getCounters"
          :idTarget="randToken()"
          :center="true"
        />
      </div>

      <div v-if="listOrder.length != 0" class="col-lg-6 col-12 my-2">
        <cardTable
          :cardTitle="'Estadisticas de productos'"
          :th="titlesProducts"
          :tr="getProductEstadisticas"
          :idTarget="randToken()"
        />
      </div>

      <div v-if="listOrder.length != 0" class="col-lg-6 col-12 my-2" >
        <bar-chart :chart-data="topSellsChartData" :idTarget="randToken()" cardTitle="Top Ventas"></bar-chart>
      </div>
      <div v-if="listOrder.length != 0" class="col-lg-6 col-12 my-2" >
        <area-chart :chart-data="sellsByhourChartData" :idTarget="randToken()" cardTitle="Ventas por hora"></area-chart>
      </div>
      <div v-if="listOrder.length != 0" class="col-lg-6 col-12 my-2" >
        <pie-chart :chart-data="countersChartData" :idTarget="randToken()" cardTitle="Contadores"></pie-chart>
      </div>
      <div v-if="listOrder.length != 0" class="col-lg-6 col-12 my-2" >
        <column-chart :chart-data="getWaiters" :idTarget="randToken()" cardTitle="Mesas atendidas"></column-chart>
      </div>

      <div v-if="getExpenses && expensesInstalled" class="col-md-6 col-12 my-2">
        <cardTable
          cardTitle="Gastos del día"
          :th="['Nombre', 'Total']"
          :tr="getExpenses"
          :idTarget="randToken()"
        />
      </div>
      <div v-if="getWorkshifts" class="col-lg-6 col-12 my-2">
        <cardTable
          cardTitle="Turnos del dia"
          :th="['Usuario', 'Monto Inicial', 'Total de Ventas', 'Inicio del turno', 'Fin del turno']"
          :tr="getWorkshifts"
          :idTarget="randToken()"
        />
      </div>
      <div v-if="getWaiters && cafeteriaInstalled && get_cafeteria()" class="col-lg-6 col-12 my-2">
        <cardTable
          cardTitle="Meseros"
          :th="['Mesero', 'Mesas atendidas', 'Total', 'Propina']"
          :tr="getWaiters"
          :idTarget="randToken()"
        />
      </div>
      <div v-if="getAddtionWaiters && addtionsWaiterInstalled && get_cafeteria()" class="col-md-6 col-12 my-2">
        <cardTable
          cardTitle="Servicios adicionales"
          :th="['Mesero', 'Cantidad', 'Total']"
          :tr="getAddtionWaiters"
          :idTarget="randToken()"
        />
      </div>
      
        <div v-if ="get_cafeteria()" class="col-md-6 col-12 my-2">

          <CardReportMesero
            :cardTitle="waiter_name"
            :key="waiter_id"
            :waiter_id="waiter_id"
            :th="getOneWaiterHeaders"
            :tr="getOneWaiter"
            :idTarget="randToken()"
          />
        </div>
      
    </div>
    <div ref="loaderReport" v-if="listOrder.length == 0 && !getWaiters && !getAddtionWaiters && !getExpenses" class="vld-parent hv-80 d-flex flex-center text-center px-2 mt-2">
      <h2>Sin resultados</h2>
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
import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
import { get } from 'request';
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
      topSellsChartData: [],
      sellsByhourChartData: [],
      countersChartData:[],
      waiterChartData:[],
      rangeDate: [new Date(), new Date()],
      startTime: '06:00:00',
      endTime: '23:59:59',

      titlesOrders: ["ID","Monto"],
      titlesCounter: ["Tipos (Metodos/pagos/ganancias/gastos)","Totales"],
      titlesProducts: [],
      listOrder: false,
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
        // {key:'factura',label:'Facturas', value:true},
        // {key:'boleta',label:'Boletas', value:true},
        // {key:'guia_despacho',label:'Guia despacho', value:true},
        // {key:'fastSell',label:'Ventas rapidas', value:true},
        // {key:'noSii',label:'No SII', value:true},
        // {key:'amipass',label:'Amipass', value:true},
        // {key:'credito',label:'Credito', value:true},
        // {key:'rappi',label:'Rappi', value:true},
        // {key:'convenio_empresa',label:'Convenio Empresa', value:true}
      ]
    }
  },
  mounted(){
    console.log('=============REPORTS================')
    //HavePermission
    //this.$store.commit('reports/clearOneWaiter');
    this.getSells();
    this.getTopSells();
    this.getWaiter(1);
    this.$root.$on('getOneWaiter', (ev) => {
      if (this.waiter_id == $('#select-report-mesero').find(":selected").val()) return;
      this.$store.commit('reports/clearOneWaiter');
      console.log(ev)
      let waiter_id = $('#select-report-mesero').find(":selected").val();
      this.getWaiter(waiter_id);
      
    
    })
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
      console.log(data);      
    },

    async getSell_ext(ref = 'loaderReport'){    // Iniciando refrescamiento (carga y botones disabled)
 
      var startDate = (localStorage.getItem('iniciarTurno'))
      var endDate = (localStorage.getItem('finalizarTurno'))
      const data = {
        startDate: startDate,
        endDate: endDate,
      };
      console.log("turno",data)

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

      // Culminando la funcion
      Loader.hide();
      this.offOn = false;

    },


    // Obteniendo reporte de ventas
    async getSells(ref = 'loaderReport'){
      // Iniciando refrescamiento (carga y botones disabled)
      this.getTopSells();
      this.getSellsByHour();
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
      console.log("original",data)


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
      console.log("oneWaiter waiter_id:",this.waiter_id)
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

      Loader.fullPage();
      var request = await this.$store.dispatch("reports/printPDF", {data:thing, params});
      console.log(request);

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
    async getTopSells(){
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      // Loader.containe(this.$refs[ref]);

      // Estableciendo rango de fecha y hora
      if(this.rangeDate.length == 0) return this.$awn.alert('Por favor inserte un rango de fechas');

      //JC BOTONES DE REPORTE

      var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
      var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

      var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + startTime;
      var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + endTime;
      const data = {
        startDate: startDate,
        endDate: endDate,
      };

      var thing = new FormData();
      for (let key in data) if (data[key]) thing.append(key, data[key]);

      // Parametros para los contadores
      var params = '?params=true';
      this.ckecks.map((key)=>{
        if(key.value) params += '&'+key.key+'=' + key.value;
      });

      // Iniciando peticion
      var request = await this.$store.dispatch("reports/getTopSells", {data:thing, params});
      // Verificando respuesta
      if(!request){
        this.$awn.info('Top Ventas no encontradas');
      }else{
        let topVentas = request.data;
        console.log('Top ventas: ',typeof topVentas);
        let topVentasArray = Object.values(topVentas);
        this.topSellsChartData = topVentasArray.map(item => [item.product_name, item.total_quantity]);
      }

      // Culminando la funcion
      // Loader.hide();
      this.offOn = false;
    },
    async getSellsByHour(){
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      // Loader.containe(this.$refs[ref]);

      // Estableciendo rango de fecha y hora
      if(this.rangeDate.length == 0) return this.$awn.alert('Por favor inserte un rango de fechas');

      //JC BOTONES DE REPORTE

      var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
      var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

      var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + startTime;
      var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + endTime;
      const data = {
        startDate: startDate,
        endDate: endDate,
      };

      var thing = new FormData();
      for (let key in data) if (data[key]) thing.append(key, data[key]);

      // Parametros para los contadores
      var params = '?params=true';
      this.ckecks.map((key)=>{
        if(key.value) params += '&'+key.key+'=' + key.value;
      });

      // Iniciando peticion
      var request = await this.$store.dispatch("reports/getSellsByHour", {data:thing, params});
      // Verificando respuesta
      if(!request){
        this.$awn.info('Ventas por hora no encontradas');
      }else{
        this.sellsByhourChartData = request.data;
        console.log('Ventas por hora:', request.data);
      }

      // Culminando la funcion
      // Loader.hide();
      this.offOn = false;
    },
    async getWorkshifts(){
      
    },
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

    settingDebito:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.debito') } },
    settingTransferencia:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.transferencia') } },
    settingCheque:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.cheque') } },
    settingStateBank:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco') } },
    getCounters: {
      get(){
        var request = this.$store.getters['reports/getterCounters'];
        if(request){
          this.listCounter = [];

          if(this.dataOptional){
            this.listCounter.push(
              ['Ordenes totales',request.orders],
              ['Unidades totales',request.quantityTotal],
              ['Tipos de productos',request.typeProducts]
            );
          }
          if(this.gananciaInstalled){
            this.listCounter.push(['Ganancia total', '$'+this.formatNumber(String(request.gananciaTotal))]);
          }
          if(this.siiInstalled){
            if(this.settingFactura) this.listCounter.push( ['Facturas', '$'+this.formatNumber(String(request.facturas))]);
            if(this.settingBoleta) this.listCounter.push( ['Boletas', '$'+this.formatNumber(String(request.boletas))] );
            if(this.settingFactura) this.listCounter.push( ['Guia Despacho', '$'+this.formatNumber(String(request.guia_despacho))]);

            if(this.settingBoletaLocal) this.listCounter.push( ['Efectivo', '$'+this.formatNumber(String(request.noSii))]);

            if(this.settingDebito) this.listCounter.push( ['Debito', '$'+this.formatNumber(String(request.debito))]);
            if (this.settingDebito) this.listCounter.push(['Amipass', '$'+this.formatNumber(String(request.amipass))]);
            if (this.settingDebito) this.listCounter.push(['Rappi', '$'+this.formatNumber(String(request.rappi))]);
            if (this.settingDebito) this.listCounter.push(['Credito', '$'+this.formatNumber(String(request.credito))]);
            if (this.settingDebito) this.listCounter.push(['Convenio Empresa', '$'+this.formatNumber(String(request.convenio_empresa))]);
            if(this.settingTransferencia) this.listCounter.push( ['Transferencia', '$'+this.formatNumber(String(request.transferencia))]);
            if(this.settingCheque) this.listCounter.push( ['Cheque', '$'+this.formatNumber(String(request.cheque))]);
            if(this.settingStateBank) this.listCounter.push( ['Trasnbank', '$'+this.formatNumber(String(request.banco))]);
            
          }
          if(this.fastSellInstalled){
            this.listCounter.push(
              ['Ventas rapidas', '$'+this.formatNumber(String(request.fastSells))],
            );
          }

          this.listCounter.push(
            ['Saldo total', '$'+this.formatNumber(request.balanceTotal)],
            ['Monto inicial', '$'+ this.formatNumber(String(this.deFormatNumber(String(request.init_money))))],
          );
          if(this.expensesInstalled){
            this.listCounter.push(
              ['Gastos del día', '$'+this.formatNumber(String(request.expenses_day))],
              ['Total - Gastos', '$'+this.formatNumber(String(request.totalToExpenses))],
            );
          }
        }
        this.countersChartData = this.listCounter.map(item => [
          item[0], // Mantener el primer elemento sin cambios
          // Number(item[1].substring(1)) // Eliminar el símbolo "$" y convertir el valor a número
        ]);
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
        
        console.log("reportes.vue GetOneWaiter")
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
          
          
          
          console.log("reporte de mesero->",request)

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
                  request[i][1],
                  request[i][2],
                  '$'+this.formatNumber(request[i][3]),
                  '$'+this.formatNumber(request[i][4]),
                  '$'+this.formatNumber(request[i][5])
                ],
              );
            } else {
              this.listTr.push(
                [
                  request[i][1],
                  request[i][2],
                  '$'+this.formatNumber(request[i][3]),
                  '$'+this.formatNumber(request[i][5])
                ],
              );
            }
          }
        }
        this.titlesProducts = [];
        if(this.gananciaInstalled) this.titlesProducts.push("Nombre del Producto", "Stock Vendido", "Valor del Producto", "Ganancia", 'Total Vendido');
        else this.titlesProducts.push("Nombre del Producto", "Stock Vendido", "Valor del Producto", 'Total Vendido');
        return this.listTr;
      }
    },
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
        console.log('Turno',request);
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
        if(request && request.length > 0 && this.addtionsWaiterInstalled){
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
  }
}
</script>

<style scoped>
  .widthInput{
    width: 210px !important;
  }
  .widthInputTime{
    width: 160px !important;
  }
  .hv-80{
    height: 80vh !important;
  }
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
