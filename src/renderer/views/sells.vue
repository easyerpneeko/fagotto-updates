<template>
  <div class="product-bg d-flex flex-column p-3">
    <div class="mb-4 pl-4 d-flex row w-100">
      <div class="card title-card col-md-5 col-sm-7 col-12">
        <div class="card-body">
          <h5 class="font-weight-bold m-0">Gestion de ventas</h5>
          <span>Detalles de ventas</span>
        </div>
      </div>
    </div>
    <div class="p-3">
      <div class="row mb-4">
        <div class="col-md-3 col-sm-4 col-12">
          <label for="ProductName">Buscar</label>
          <input :disabled="offOn" id="ProductName" v-model="sell" type="text" class="form-control" @keypress.enter="refreshData()" />
        </div>
        <div class="col-md-3 col-sm-4 col-12">
          <label>Rango de fechas</label>
          <date-picker class="widthInput" format="YYYY-MM-DD" type="date" v-model="rangeDate" range placeholder="Fechas" confirm></date-picker>
        </div>
        <div class="col-md-6 col-sm-4 col-12 d-flex align-items-end">
          <a @click="refreshData()" :class="['btn mx-1 mt-1 mb-0 bg-primario btnPersonalice',{'disabled': offOn}]" href="#">
            <span class="">Buscar</span>
          </a>
          <a @click="refreshData(1, true)" :class="['btn mx-1 mt-1 mb-0 bg-primario btnPersonalice',{'disabled': offOn}]" href="#">
            <i class="fas fa-calendar-day"></i>
            <span class="">Hoy</span>
          </a>
          <a @click="refreshData(1, null, true)" :class="['btn mx-1 mt-1 mb-0 bg-primario btnPersonalice',{'disabled': offOn}]" href="#">
            <i class="fas fa-list-alt"></i>
            <span class="">Todas</span>
          </a>
          <a @click="getDeletedSells()" :class="['btn mx-1 mt-1 mb-0 bg-primario btnPersonalice',{'disabled': offOn}]" href="#">
            <i class="fas fa-list-alt"></i>
            <span class="">Canceladas</span>
          </a>
          <a @click="openReportSells()" :class="['btn mx-1 mt-1 mb-0 bg-dark text-white text-capitalize btnPersonalice',{'disabled': offOn}]" href="#">
            <i class="fas fa-file-alt"></i>
            <span class="">Reporte</span>
          </a>
        </div>
      </div>
      <!-- Lista de ventas -->
      <div v-if="sells" ref="loaderSells" class="vld-parent p-0 m-0 bg-light">
        <custom-table  v-model="jsonTable" @orderBy="orderBy" v-slot="props">
          <a @click="openDetails(props.item)" class="py-1 px-2 text-center btn" :class="props.item.trash==0 ? 'bg-secundario' : 'bg-light'" href="#">
            <i class="fas fa-eye"></i>
          </a>
          <a v-if="permissionRemoveSell && (props.item.trash==0) " class="py-1 px-2 text-center btn bg-primario" href="#" @click="openVerify(props.item)">
            <i class="fas fa-trash-alt"></i>
          </a>
        </custom-table>

        <!-- Paginacion -->
        <nav v-if="sells" aria-label="Page navigation example">
          <paginate
            v-if="(sells && sells.pages > 1)"
            v-model="sells"
            :offOn="offOn"
            @getPage="refreshData"
          />
        </nav>
      </div>
      <div v-else ref="loaderSells"  class="vld-parent box-false d-flex flex-center text-center p-2">
        <h2>No existen ventas registradas</h2>
      </div>
    </div>
    <detailSell :dataDetail="dataDetail" @refreshData="refreshData" :clientFromModal="client" />
    <modalClient @sendInfo="editarCliente" :rutUser="rutUser" />
    <Verifymodal :propVerify="propVerify" @refreshData="refreshData" />

    <ReportSells />
  </div>
</template>

<script>
// components
import paginate from  '@/components/MPage.vue';
import detailSell from '@/components/modals/detailSell.vue';
import Verifymodal from '@/components/modals/verifyDeleteSell.vue';
import modalClient from '@/components/modals/client.vue';
import customTable from '@/components/tables/table.vue';
import ReportSells from '@/components/modals/sells/reportSells.vue';
// helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
import $ from 'jquery';
import { getDeletedSells } from '../store/sells/actions';

export default {
  name: 'sellsList',
  data(){
    return{
      dataDetail: null,
      rutUser: null,
      client: null,
      sell: null,
      oldPage: 1,
      today: null,
      sells: false,
      propVerify: '',
      valAnterior: '',
      rangeDate: [],
      jsonTable: {
        btn: true,
        items: null,
        rows:[
          {key:'id', class:'text-center d-none-1', permission:'default'},
          {key:'created_at', class:'', permission:'default'},
          {key:'client_name', class:'', permission:'clientsInstaller'},
          {key:'client_rut', class:'', permission:'clientsInstaller'},
          {key:'type', class:'text-capitalize', permission:'default'},
          {key:'sell_folio', class:'d-none-01', permission:'siiInstaller'},
          {key:'fullname', class:'d-none-01', permission:'displayUser'},
          {key:'total', class:'d-none-2', permission:'default'},
        ],
        titles:[
          {label:'Id', class:'th-xs text-center d-none-1', permission:'default', type:false},
          {label:'Fecha', class:'th-sm', permission:'default', type:'orderBy', orderBy: false},
          {label:'Cliente', class:'th-sm', permission:'clientsInstaller', type:false},
          {label:'Rut de cliente', class:'th-md', permission:'clientsInstaller', type:false},
          {label:'Método de Pago', class:'th-sm', permission:'default',type:false},
          {label:'Folio', class:'d-none-01', permission:'siiInstaller',type:false},
          {label:'Usuario', class:'th-sm d-none-01', permission:'displayUser', type:false},
          {label:'Total', class:'d-none-2', permission:'default', type:false},
          {label:'Detalles', class:'th-sm text-center', permission:'default', type:false},
        ]
      }
    }
  },
  mounted(){
    console.log('=============SELLS================')
    //HavePermission
    this.refreshData();
  },
  components:{
    detailSell,
    Verifymodal,
    paginate,
    customTable,
    modalClient,
    ReportSells
  },
  props:{
    value: {
      type: Boolean,
      default: false
    }
  },
  methods:{
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
    // Refrescando ventas
    async refreshData(page = false, today = null, allSells = false, isLoader = true, id = false){
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      if(isLoader) Loader.containe(this.$refs.loaderSells);

      if (this.sellsGet) {
        // Parametros para la ruta
        var params = '?params=true';
        if(allSells === false){
          // Busqueda especifica
          if(this.sell != null && this.sell != ''){
            params += '&searchInSell=' + this.sell;
            this.oldPage = 1;
            page = false;
          }

          // Fechas
          if (this.rangeDate && this.rangeDate.length > 0) {
            // Rango de fechas
            var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + '00:00:00';
            var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + '23:59:59';
            params += '&startDate=' + startDate;
            params += '&endDate=' + endDate;
          }else{
            // Ventas del dia
            this.today = today;
            if(this.today != null) params += '&todaySells='+this.today;
          }
        }else{
          this.sell = null;
          this.today = null;
          this.rangeDate = [];
        }
        // Paginacion
        if(page !== false) this.oldPage = page;
        params += '&page=' + this.oldPage;

        // Ordenamiento
        var orderBy = (this.jsonTable.titles[2].orderBy) ? 'asc' : 'desc';
        params += '&orderBy_date=' + orderBy;


        console.log("/////////////////// ",params);
        // Iniciando peticion
        var request = await this.$store.dispatch("sells/getSells", params);
        // Verificando la respuesta
        if (!request.success) this.$awn.alert('Error al obtener las ventas');
        else{
          this.sells = (request.data.items.length == 0) ? false : request.data;
          this.jsonTable.items = this.sells.items;
          
          // DEBUG: Verificar qué campos llegan del backend
          console.log('=== DEBUG SELLS DATA ===');
          console.log('First item:', this.sells.items[0]);
          console.log('Type field:', this.sells.items[0] && this.sells.items[0].type);
          console.log('Other_type field:', this.sells.items[0] && this.sells.items[0].other_type);
          console.log('jsonTable.rows:', this.jsonTable.rows);
          console.log('jsonTable.titles:', this.jsonTable.titles);
          console.log('========================');
          
          if(!isLoader && id){
            this.dataDetail = this.jsonTable.items.find((item) => item.id == id);
          }
        }

      }
      // Culminando la funcion
      if(isLoader) Loader.hide();
      this.offOn = false;
    },
    async getDeletedSells(){
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      Loader.containe(this.$refs.loaderSells);

        // Iniciando peticion
      var request = await this.$store.dispatch("sells/getDeletedSells");
      // Verificando la respuesta
      if (!request.success) this.$awn.alert('Error al obtener las ventas eliminadas');
      else{
        this.sells = (request.data.items.length == 0) ? false : request.data;
        this.jsonTable.items = this.sells.items;
        // if(!isLoader && id){
        //   this.dataDetail = this.jsonTable.items.find((item) => item.id == id);
        // }
      }
      // Culminando la funcion
      Loader.hide();
      this.offOn = false;
    },
    // Modal de verificacion
    openVerify(sell){
      this.propVerify = {
        params: sell.id,
        title: 'Eliminar venta',
        text: '¿Usted esta seguro de querer eliminar la venta #'+ sell.id +'?',
        store: 'sells/removeSell',
        success: 'Venta eliminada exitosamente'
      };
      $('#verifyDeleteSell').modal('show');
    },
    // Abriendo modal de detalle
    openDetails(data){
      if(data && data.client) this.rutUser = data.client.rut;
      else this.rutUser = null;
      this.dataDetail = data;
      console.log(this.dataDetail);
      $('#detailSell').modal('show');
    },
    // Ordenamiento de asc/desc
    orderBy(){
      this.jsonTable.titles[2].orderBy = !this.jsonTable.titles[2].orderBy;
      this.refreshData();
    },
    async editarCliente(client){
      this.client = client;
    },
    openReportSells(){
      $('#reportSellsModal').modal('show');
    }
  },
  computed:{
    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input',offOn) }
    },
    // Permisos para obtener las ventas
    sellsGet:{ get(){ return ConfigHelper.HavePermission('gestionar_ventas'); } },
    permissionRemoveSell:{ get(){ return ConfigHelper.HavePermission('eliminar_venta'); }}
  }
}
</script>

<style scoped>
  .bg-light tr{
    background-color: #ffffff !important;
  }
  .widthInput{
    width: 100% !important;
  }
  .mx-input {
    height: 38px !important;
  }
  .mx-input-wrapper {
    height: 38px !important;
  }
  .page-link{
    font-size: 15px !important;
  }
  .btnPersonalice{
    border: none !important;
    text-transform: none !important;
    padding: 7px 20px;
    margin-bottom: 0px;
    display: inline !important;
  }
  .btnOrderBy{
    cursor: pointer;
  }
  @media (max-width: 885px) {
    .d-none-01{
      display: none;
    }
  }
  @media (max-width: 768px) {
    .d-none-0{
      display: none;
    }
  }
  @media (max-width: 660px) {
    .d-none-1{
      display: none;
    }
  }
  @media (max-width: 575px) {
    .btnPersonalice{
      display: block;
      width: 100%;
    }
  }
  @media (max-width: 520px) {
    .d-none-2{
      display: none !important;
    }
  }
</style>
