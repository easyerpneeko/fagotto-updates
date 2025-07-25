<template>
  <div class="product-bg d-flex flex-column p-3">
    <!-- Header Principal -->
    <div class="mb-4 pl-4 d-flex row w-100">
      <div class="card title-card col-md-5 col-sm-7 col-12 shadow-sm border-0">
        <div class="card-body">
          <h5 class="font-weight-bold m-0">💰 Gestión de ventas</h5>
          <span class="text-muted">Detalles y control de ventas</span>
        </div>
      </div>
    </div>

    <!-- Card principal que envuelve todo -->
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <!-- Barra de filtros elegante -->
        <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
          <div class="flex-grow-1" style="min-width: 200px;">
            <input 
              :disabled="offOn" 
              id="ProductName" 
              v-model="sell" 
              type="text" 
              class="form-control" 
              placeholder="🔍 Buscar cliente o folio..." 
              @keypress.enter="refreshData()" 
            />
          </div>
          
          <div style="min-width: 200px;">
            <date-picker 
              class="widthInput" 
              format="YYYY-MM-DD" 
              type="date" 
              v-model="rangeDate" 
              range 
              placeholder="📅 Seleccionar fechas" 
              confirm
            ></date-picker>
          </div>
          
          <div class="d-flex flex-wrap gap-1">
            <button 
              @click="refreshData()" 
              :class="['btn btn-outline-primary btn-action',{'disabled': offOn}]"
              title="Buscar"
            >
              <i class="fas fa-search"></i> Buscar
            </button>
            
            <button 
              @click="refreshData(1, true)" 
              :class="['btn btn-outline-secondary btn-action',{'disabled': offOn}]"
              title="Ventas de hoy"
            >
              <i class="fas fa-calendar-day"></i> Hoy
            </button>
            
            <button 
              @click="refreshData(1, null, true)" 
              :class="['btn btn-outline-info btn-action',{'disabled': offOn}]"
              title="Todas las ventas"
            >
              <i class="fas fa-list-alt"></i> Todas
            </button>
            
            <button 
              @click="getDeletedSells()" 
              :class="['btn btn-outline-danger btn-action',{'disabled': offOn}]"
              title="Ventas canceladas"
            >
              <i class="fas fa-ban"></i> Canceladas
            </button>
            
            <button 
              @click="openReportSells()" 
              :class="['btn btn-dark btn-action',{'disabled': offOn}]"
              title="Generar reporte"
            >
              <i class="fas fa-file-alt"></i> Reporte
            </button>
          </div>
        </div>
        <!-- Lista de ventas con tabla profesional -->
        <div v-if="sells" ref="loaderSells" class="vld-parent p-0 m-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped table-sm align-middle text-nowrap">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width: 60px;">ID</th>
                  <th style="width: 120px;">📅 Fecha</th>
                  <th class="d-none d-md-table-cell">🆔 RUT</th>
                  <th>💳 Método</th>
                  <th class="d-none d-lg-table-cell">📄 Folio</th>
                  <th class="d-none d-lg-table-cell">👨‍💼 Usuario</th>
                  <th class="text-end">💰 Total</th>
                  <th class="text-center" style="width: 100px;">⚡ Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in jsonTable.items" :key="item.id" :class="{'table-warning': item.trash == 1}">
                  <td class="text-center fw-bold">
                    <span class="badge order-id-badge">#{{ item.id }}</span>
                  </td>
                  <td>
                    <small>{{ formatDate(item.created_at) }}</small>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <code class="text-muted">{{ item.client_rut || 'N/A' }}</code>
                  </td>
                  <td>
                    <span :class="getPaymentBadgeClass(item.type)">
                      {{ getPaymentTypeDisplay(item.type) }}
                    </span>
                  </td>
                  <td class="d-none d-lg-table-cell">
                    <span v-if="item.sell_folio" class="badge folio-badge">📄 {{ item.sell_folio }}</span>
                    <span v-else class="text-muted">-</span>
                  </td>
                  <td class="d-none d-lg-table-cell">
                    <small class="text-muted">{{ item.fullname || 'Sistema' }}</small>
                  </td>
                  <td class="text-end fw-bold">
                    <span class="text-success">${{ formatNumber(item.total) }}</span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <button 
                        @click="openDetails(item)" 
                        class="btn btn-outline-info btn-sm" 
                        :class="item.trash==0 ? 'btn-outline-info' : 'btn-outline-secondary'" 
                        title="Ver detalles"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      <button 
                        v-if="permissionRemoveSell && (item.trash==0)" 
                        class="btn btn-outline-danger btn-sm" 
                        @click="openVerify(item)"
                        title="Eliminar venta"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Paginación profesional -->
          <div v-if="sells && sells.pages > 1" class="d-flex justify-content-center mt-4">
            <nav aria-label="Navegación de páginas">
              <paginate
                v-model="sells"
                :offOn="offOn"
                @getPage="refreshData"
                class="pagination pagination-sm"
              />
            </nav>
          </div>
        </div>
        
        <!-- Estado vacío mejorado -->
        <div v-else ref="loaderSells" class="vld-parent text-center py-5">
          <div class="empty-state">
            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No existen ventas registradas</h4>
            <p class="text-muted">Intenta ajustar los filtros o crear una nueva venta</p>
          </div>
        </div>
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
    // Formatear fecha de manera legible
    formatDate(dateString) {
      return moment(dateString).format('DD/MM/YYYY HH:mm');
    },
    // Obtener clase de badge para método de pago
    getPaymentBadgeClass(type) {
      const classes = {
        'efectivo': 'badge text-white payment-efectivo',
        'debito': 'badge text-white payment-debito',
        'boleta': 'badge text-white payment-debito', // Mapear boleta como débito
        'credito': 'badge text-white payment-credito',
        'transferencia': 'badge text-white payment-transferencia',
        'uber': 'badge text-white payment-uber',
        'uber_eats': 'badge text-white payment-uber',
        'rappi': 'badge text-white payment-rappi',
        'pedidos_ya': 'badge text-dark payment-pedidos-ya',
        'cornershop': 'badge text-white payment-cornershop',
        'ifood': 'badge text-white payment-ifood',
        'otro': 'badge text-white payment-otro'
      };
      return classes[type && type.toLowerCase()] || 'badge text-white payment-otro';
    },
    // Obtener texto display para método de pago
    getPaymentTypeDisplay(type) {
      const displays = {
        'efectivo': '💵 Efectivo',
        'debito': '💳 Débito',
        'boleta': '🧾 Boleta', // Mostrar boleta correctamente
        'credito': '💳 Crédito',
        'transferencia': '🏦 Transferencia',
        'uber': '🚗 Uber Eats',
        'uber_eats': '🚗 Uber Eats',
        'rappi': '🛵 Rappi',
        'pedidos_ya': '🍕 PedidosYa',
        'cornershop': '🛒 Cornershop',
        'ifood': '🍔 iFood',
        'otro': '❓ Otro'
      };
      return displays[type && type.toLowerCase()] || type || 'N/A';
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
  
  /* Botones modernos */
  .btn-action {
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid;
  }
  
  .btn-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  /* Gap para flexbox */
  .gap-1 {
    gap: 0.25rem;
  }
  
  .gap-2 {
    gap: 0.5rem;
  }
  
  /* Card mejorado */
  .card {
    border-radius: 12px;
  }
  
  .title-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
  }
  
  /* Tabla profesional */
  .table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.1) !important;
    transform: scale(1.01);
    transition: all 0.2s ease;
  }
  
  .table th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #dee2e6;
  }
  
  .table td {
    vertical-align: middle;
    font-size: 0.9rem;
  }
  
  /* Estado vacío */
  .empty-state {
    padding: 3rem;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background: #f8f9fa;
  }
  
  /* Badges personalizados */
  .badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
    border-radius: 0.5rem;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  
  /* 🆔 Badge para ID de orden */
  .order-id-badge {
    background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
    color: white;
    border: 1px solid #6f42c1;
    font-weight: 700;
    letter-spacing: 0.5px;
  }
  
  /* 📄 Badge para folio */
  .folio-badge {
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    color: white;
    border: 1px solid #17a2b8;
  }
  
  /* Colores corporativos reales de cada método de pago */
  .payment-efectivo {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    border: 1px solid #1e7e34 !important;
  }
  
  .payment-debito {
    background: linear-gradient(135deg, #0066ff 0%, #3399ff 100%) !important;
    border: 1px solid #0052cc !important;
    box-shadow: 0 3px 6px rgba(0, 102, 255, 0.3) !important;
  }
  
  .payment-credito {
    background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%) !important;
    border: 1px solid #e55a4e !important;
  }
  
  .payment-transferencia {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%) !important;
    border: 1px solid #117a8b !important;
  }
  
  /* 🚗 Uber Eats - Verde corporativo real */
  .payment-uber {
    background: linear-gradient(135deg, #06c167 0%, #00d4aa 100%) !important;
    border: 1px solid #04a151 !important;
    animation: uber-pulse 2s infinite;
  }
  
  @keyframes uber-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
  }
  
  /* 🛵 Rappi - Naranja vibrante corporativo */
  .payment-rappi {
    background: linear-gradient(135deg, #ff441f 0%, #ff6b35 100%) !important;
    border: 1px solid #e63312 !important;
    position: relative;
    overflow: hidden;
  }
  
  .payment-rappi::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: rappi-shine 3s infinite;
  }
  
  @keyframes rappi-shine {
    0% { transform: translateX(-100%) translateY(-100%); }
    50% { transform: translateX(100%) translateY(100%); }
    100% { transform: translateX(-100%) translateY(-100%); }
  }
  
  /* 🍕 PedidosYa - Amarillo corporativo */
  .payment-pedidos-ya {
    background: linear-gradient(135deg, #ffcc02 0%, #ffb300 100%) !important;
    border: 1px solid #e6ac00 !important;
    color: #1a1a1a !important;
    font-weight: 700;
  }
  
  /* 🛒 Cornershop - Verde azulado */
  .payment-cornershop {
    background: linear-gradient(135deg, #00d4aa 0%, #00bfa5 100%) !important;
    border: 1px solid #00a693 !important;
  }
  
  /* 🍔 iFood - Rojo corporativo */
  .payment-ifood {
    background: linear-gradient(135deg, #ea1d2c 0%, #ff4757 100%) !important;
    border: 1px solid #d31b2a !important;
  }
  
  /* ❓ Otros métodos */
  .payment-otro {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
    border: 1px solid #495057 !important;
  }
  
  /* Hover effects para todos los badges */
  .badge:hover {
    transform: translateY(-1px) scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
  }
  
  /* Responsive mejorado */
  @media (max-width: 885px) {
    .d-none-01{
      display: none;
    }
  }
  @media (max-width: 768px) {
    .d-none-0{
      display: none;
    }
    .btn-action {
      padding: 0.4rem 0.8rem;
      font-size: 0.85rem;
    }
  }
  @media (max-width: 660px) {
    .d-none-1{
      display: none;
    }
  }
  @media (max-width: 575px) {
    .btn-action {
      display: block;
      width: 100%;
      margin-bottom: 0.25rem;
    }
    .gap-1, .gap-2 {
      gap: 0.25rem;
    }
  }
  @media (max-width: 520px) {
    .d-none-2{
      display: none !important;
    }
  }
</style>
