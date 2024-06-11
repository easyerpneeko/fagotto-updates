<template>
  <div class="product-bg d-flex flex-column p-3">
    <div class="mb-4 pl-4 d-flex row w-100">
      <div class="card title-card col-md-5 col-sm-7 col-12">
        <div class="card-body">
          <h5 class="font-weight-bold m-0">Gestion de devoluciones</h5>
          <span>Detalles de devoluciones</span>
        </div>
      </div>
    </div>
    <div class="p-3">
      <div class="row mb-4">
        <div class="col-md-2 col-sm-4 col-12">
          <label for="ProductName">Buscar Producto</label>
          <input :disabled="offOn" id="ProductName" v-model="ProductName" type="text" class="form-control"
            @keypress.enter="refreshData()" />
        </div>
        <div class="col-md-2 col-sm-4 col-12">
          <label for="ReasonName">Buscar Razon</label>
          <input :disabled="offOn" id="ReasonName" v-model="ReasonName" type="text" class="form-control"
            @keypress.enter="refreshData()" />
        </div>
        <div class="col-md-2 col-sm-4 col-12">
          <label>Rango de fechas</label>
          <date-picker class="widthInput" format="YYYY-MM-DD" type="date" v-model="rangeDate" range placeholder="Fechas"
            confirm></date-picker>
        </div>
        <div class="col-md-4 col-sm-4 col-12 d-flex align-items-end">
          <a @click="refreshData()" :class="['btn mx-1 mt-1 mb-0 bg-primario btnPersonalice', { 'disabled': offOn }]"
            href="#">
            <span class="">Buscar</span>
          </a>
          <a @click="refreshData(1, true)"
            :class="['btn mx-1 mt-1 mb-0 bg-primario btnPersonalice', { 'disabled': offOn }]" href="#">
            <i class="fas fa-calendar-day"></i>
            <span class="">Hoy</span>
          </a>
          <a @click="refreshData(1, null, true)"
            :class="['btn mx-1 mt-1 mb-0 bg-primario btnPersonalice', { 'disabled': offOn }]" href="#">
            <i class="fas fa-list-alt"></i>
            <span class="">Todas</span>
          </a>
        </div>
      </div>
      <!-- Lista de ventas -->
      <div v-if="devolutions" ref="loaderDevolutions" class="vld-parent p-0 m-0 bg-light">
        <custom-table v-model="jsonTable" @orderBy="orderBy" v-slot="props">
          <!-- <a @click="printTicket(props.item)" class="py-1 px-2 text-center btn" :class="props.item.trash==0 ? 'bg-secundario' : 'bg-light'" href="#">
                <i class="fas fa-print"></i>
            </a> -->
        </custom-table>

        <!-- Paginacion -->
        <nav v-if="devolutions" aria-label="Page navigation example">
          <paginate v-if="(devolutions && devolutions.pages > 1)" v-model="devolutions" :offOn="offOn"
            @getPage="refreshData" />
        </nav>
      </div>
      <div v-else ref="loaderDevolutions" class="vld-parent box-false d-flex flex-center text-center p-2">
        <h2>No existen devoluciones registradas</h2>
      </div>
    </div>
    <!-- <detailSell :dataDetail="dataDetail" @refreshData="refreshData" :clientFromModal="client" /> -->
    <!-- <modalClient @sendInfo="editarCliente" :rutUser="rutUser" /> -->
    <!-- <Verifymodal :propVerify="propVerify" @refreshData="refreshData" /> -->
  </div>
</template>

<script>
// components
import paginate from '@/components/MPage.vue';
import detailSell from '@/components/modals/detailSell.vue';
// import Verifymodal from '@/components/modals/verifyDelete.vue';
import Verifymodal from '@/components/modals/verifyDeleteSell.vue';
// import modalClient from '@/components/modals/client.vue';
import customTable from '@/components/tables/table.vue';
// helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
// import $ from 'jquery';
// import { getDeletedSells } from '../store/sells/actions';

export default {
  name: 'devolutionsList',
  data() {
    return {
      dataDetail: null,

      ProductName: null,
      ReasonName: null,

      oldPage: 1,

      devolutions: false,

      propVerify: '',
      valAnterior: '',

      today: null,
      rangeDate: [],

      jsonTable: {
        btn: true,
        items: null,
        rows: [
          { key: 'id', class: 'text-center', permission: 'default' },
          { key: 'created_at', class: '', permission: 'default' },
          { key: 'sell_id', class: '', permission: 'default' },
          { key: 'name', class: '', permission: 'default' },
          { key: 'price', class: '', permission: 'default' },
          { key: 'ganancia', class: '', permission: 'default' },
          { key: 'stock', class: 'text-capitalize', permission: 'default' },
          { key: 'reason', class: 'd-none-01', permission: 'default' },
          { key: 'user', class: 'd-none-01', permission: 'displayUser' },
        ],
        titles: [
          { label: '#', class: 'th-xs text-center', permission: 'default', type: false },
          { label: 'Fecha', class: 'th-sm', permission: 'default', type: 'orderBy', orderBy: false },
          { label: 'Venta #', class: 'th-xs', permission: 'default', type: false },
          { label: 'Producto', class: 'th-md', permission: 'default', type: false },
          { label: 'Precio', class: 'th-xs', permission: 'default', type: false },
          { label: 'Ganancia', class: 'th-xs', permission: 'default', type: false },
          { label: 'Stock', class: 'th-xs', permission: 'default', type: false },
          { label: 'Razon', class: 'th-md', permission: 'default', type: false },
          { label: 'Usuario', class: 'th-xs', permission: 'displayUser', type: false },
          // { label: 'Detalles', class: 'th-sm text-center', permission: 'default', type: false },
        ]
      }
    }
  },
  mounted() {
    console.log('=============Devolutions================')
    //HavePermission
    this.refreshData();
  },
  components: {
    detailSell,
    Verifymodal,
    paginate,
    customTable,
    // modalClient
  },
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    // formatNumber(number){
    //   return FormatNumber.format(number);
    // },
    // deFormatNumber(number,backend = true){
    //   if (backend) {
    //     return FormatNumber.deFormatBackend(number);
    //   }else{
    //     return FormatNumber.deFormat(number);
    //   }
    // },
    // Refrescando ventas
    async refreshData(page = false, today = null, allDevolutions = false, isLoader = true, id = false) {
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      if (isLoader) Loader.containe(this.$refs.loaderDevolutions);

      if (this.sellsGet) {
        // Parametros para la ruta
        var params = '?params=true';
        if (page && this.oldPage != page) this.oldPage = page;
        params += '&page=' + this.oldPage;

        if (allDevolutions === false) {
          // Busqueda especifica
          if (this.devolution != null && this.devolution != '') {
            params += '&searchInDevolution=' + this.devolution;
            this.oldPage = 1;
            page = false;
          }

          if (this.ProductName != null && this.ProductName != '') params += '&ProductName=' + this.ProductName;

          if (this.ReasonName != null && this.ReasonName != '') params += '&ReasonName=' + this.ReasonName;

          // Fechas
          if (this.rangeDate && this.rangeDate.length > 0) {
            // Rango de fechas
            var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + '00:00:00';
            var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + '23:59:59';
            params += '&startDate=' + startDate;
            params += '&endDate=' + endDate;
          } else {
            // Ventas del dia
            this.today = today;
            if (this.today != null) params += '&todayDevolutions=' + this.today;
          }
        } else {
          this.ProductName = null;
          this.ReasonName = null;
          this.today = null;
          this.rangeDate = [];
        }
        // Paginacion
        // if (page !== false) this.oldPage = page;
        // params += '&page=' + this.oldPage;

        // Ordenamiento
        // var orderBy = (this.jsonTable.titles[2].orderBy) ? 'asc' : 'desc';
        // params += '&orderBy_date=' + orderBy;


        console.log("///////params///////// ", params);
        // Iniciando peticion
        var request = await this.$store.dispatch("devolutions/getDevolutions", params);
        // Verificando la respuesta
        if (!request.success) this.$awn.alert('Error al obtener las ventas');
        else {
          console.log('Devoluciones', request.data);

          this.devolutions = (request.data.items.length == 0) ? false : request.data;
          this.jsonTable.items = this.devolutions.items;
        }

      }
      // Culminando la funcion
      if (isLoader) Loader.hide();
      this.offOn = false;
    },
    // Ordenamiento de asc/desc
    orderBy() {
      this.jsonTable.titles[2].orderBy = !this.jsonTable.titles[2].orderBy;
      this.refreshData();
    },

  },
  computed: {
    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input', offOn) }
    },
    // Permisos para obtener las ventas
    sellsGet: { get() { return ConfigHelper.HavePermission('gestionar_ventas'); } },
    // permissionRemoveSell:{ get(){ return ConfigHelper.HavePermission('eliminar_venta'); }}
  }
}
</script>

<style scoped>
.bg-light tr {
  background-color: #ffffff !important;
}

.widthInput {
  width: 100% !important;
}

.mx-input {
  height: 38px !important;
}

.mx-input-wrapper {
  height: 38px !important;
}

.page-link {
  font-size: 15px !important;
}

.btnPersonalice {
  border: none !important;
  text-transform: none !important;
  padding: 7px 20px;
  margin-bottom: 0px;
  display: inline !important;
}

.btnOrderBy {
  cursor: pointer;
}

@media (max-width: 885px) {
  .d-none-01 {
    display: none;
  }
}

@media (max-width: 768px) {
  .d-none-0 {
    display: none;
  }
}

@media (max-width: 660px) {
  .d-none-1 {
    display: none;
  }
}

@media (max-width: 575px) {
  .btnPersonalice {
    display: block;
    width: 100%;
  }
}

@media (max-width: 520px) {
  .d-none-2 {
    display: none !important;
  }
}
</style>