<template>
  <div class="modal fade modalForce" id="detailSell" tabindex="-1" role="dialog" aria-labelledby="detailSell"
    aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primario">
          <h5 class="modal-title">Detalle de venta</h5>
          <button type="button" class="text-white close" @click="$emit('closeModal')" data-dismiss="modal"
            aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div v-if="dataDetail" class="modal-body">
          <div class="row">
            <div v-if="(dataDetail.client && clientInstalled)" class="col-md-4 col-12">
              <!-- Data client -->
              <div class="text-center">
                <h5 class="mt-2 text-capitalize">Cliente</h5>
                <h5 class="mt-2 text-capitalize">{{ dataDetail.client.name }} {{ dataDetail.client.lastname }}</h5>
                <div class="p-3 circleStyle">
                  <img src="../../assets/person.jpg" class="user-img img-fluid img-circle">
                </div>
                <h5 class="mt-2 text-capitalize">Rut: {{ dataDetail.client.rut }}</h5>
                <h5 class="mt-2 text-capitalize" v-if="dataDetail && siiInstalled && dataDetail.glosa_sii">
                  SII: {{ dataDetail.glosa_sii }}
                </h5>
              </div>
              <hr class="w-95 m-3" style="border: 0.5px solid rgba(0,0,0,0.2);">
              <ul class="list-group client_list">
                <li v-if="(dataDetail.client.phone && this.clientsPhone)" class="list-group-item-border">
                  <i class="fas fa-mobile mr-2"></i> {{ dataDetail.client.phone }}
                </li>
                <li v-if="(dataDetail.client.city && dataDetail.client.comuna)" class="list-group-item-border">
                  <i class="fas fa-route mr-2"></i> {{ dataDetail.client.city }} - {{ dataDetail.client.comuna }}
                </li>
                <li v-if="dataDetail.client.razon_social" class="list-group-item-border">
                  <i class="fas fa-edit mr-2"></i> {{ dataDetail.client.razon_social }}
                </li>
              </ul>
            </div>
            <!-- Data user -->
            <div v-else class="col-md-4 col-12">

              <!-- Data user -->
              <div class="text-center" v-if="dataDetail && dataDetail.trash">
                <h6 class="mt-2 text-capitalize text-red">Motivo de cancelacion: {{ dataDetail.trash_comment }} </h6>
                <h6 class="mt-2 text-capitalize text-red">Venta eliminada por: </h6>
                <h6 class="mt-2 text-capitalize">{{ dataDetail.user_trash }}</h6>
                <div class="p-3 circleStyle">
                  <img :src="getImage(dataDetail.avatar)" class="user-img img-fluid img-circle">
                </div>
              </div>

              <div v-else class="text-center">
                <h5 class="mt-2 text-capitalize">Usuario</h5>
                <h5 class="mt-2 text-capitalize">{{ dataDetail.fullname }}</h5>
                <div class="p-3 circleStyle">
                  <img :src="getImage(dataDetail.avatar)" class="user-img img-fluid img-circle">
                </div>
                <h5 class="mt-2 text-capitalize" v-if="dataDetail && siiInstalled && dataDetail.glosa_sii">
                  SII: {{ dataDetail.glosa_sii }}
                </h5>
              </div>

              <!-- <hr class="w-95 m-3" style="border: 0.5px solid rgba(0,0,0,0.2);"> -->
            </div>

            <!-- data products and sell -->
            <div class="col-md-8 col-12">
              <div class="p-3">
                <h5>Productos comprados - {{ dataDetail.type }}</h5>
              </div>
              <div class="list-group">
                <custom-table v-model="jsonTable" v-slot="props">
                  <a v-if="devoluciones" class="py-1 px-2 text-center btn bg-warning" href="#" @click="openVerifyDelete(props.item)">
                    <i class="fas fa-undo"></i>
                  </a>
                </custom-table>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer" v-if="dataDetail && !dataDetail.trash">
          <button type="button" class="btn bg-secundario text-white"
            v-if="(dataDetail && !dataDetail.type && !type && dataDetail.type == 'factura')"
            @click="editarCliente(null)">
            Editar Cliente
          </button>
          <button type="button" class="btn bg-dark text-white text-capitalize"
            v-if="notaInstalled && dataDetail && dataDetail.type == 'factura'" @click="cancelSell">
            Cancelar factura
          </button>
          <button type="button" class="btn bg-primario text-white text-capitalize"
            v-if="dataDetail && siiInstalled && settingFactura && dataDetail.type == 'factura'" @click="consultarVenta">
            Consultar Factura
          </button>

          <button type="button" class="btn bg-primario text-white text-capitalize"
            v-if="dataDetail && siiInstalled && settingBoleta && dataDetail.type == 'boleta'" @click="consultarVenta">
            Consultar Boleta
          </button>


          <button type="button" class="btn bg-dark text-white"
            v-if="dataDetail && siiInstalled && settingBoleta && ((!dataDetail.type && !type) || dataDetail.type == 'Boleta Local')"
            @click="procesarVenta('boleta', true, 'print')">
            Procesar Boleta
          </button>
          <button type="button" class="btn bg-primario text-white"
            v-if="dataDetail && siiInstalled && settingFactura && !dataDetail.type && !type"
            @click="procesarVenta('factura', true, 'print')">
            Procesar Factura
          </button>

          <button type="button" class="btn bg-dark text-white text-capitalize"
            v-if="dataDetail && siiInstalled && settingFactura && dataDetail.type == 'factura'"
            @click="procesarVenta((dataDetail.type) ? dataDetail.type : type, false, 'print')">
            Imprimir Factura
          </button>

          <button type="button" class="btn bg-dark text-white text-capitalize"
            v-if="dataDetail && siiInstalled && settingBoleta && dataDetail.type == 'boleta'"
            @click="procesarVenta((dataDetail.type) ? dataDetail.type : type, false, 'print')">
            Imprimir Boleta
          </button>

          <button type="button" class="btn bg-dark text-white text-capitalize" @click="printPDFGuia('print')"
            v-if="dataDetail && siiInstalled && settingFactura && dataDetail.type == 'guia_despacho'">
            Imprimir PDF Guia despacho
          </button>
          <button type="button" class="btn bg-dark text-white text-capitalize" @click="printPDFGuia('pdf')"
            v-if="dataDetail && siiInstalled && settingFactura && dataDetail.type == 'guia_despacho'">
            PDF Guia despacho
          </button>

          <button type="button" class="btn bg-dark text-white text-capitalize"
            v-if="dataDetail && siiInstalled && settingFactura && dataDetail.type == 'factura'"
            @click="processGuiaDespacho((dataDetail.type) ? dataDetail.type : type, false, 'pdf')">
            PDF Factura
          </button>

          <button type="button" class="btn bg-dark text-white text-capitalize"
            v-if="dataDetail && siiInstalled && settingBoleta && dataDetail.type == 'boleta'"
            @click="processGuiaDespacho((dataDetail.type) ? dataDetail.type : type, false, 'pdf')">
            PDF Boleta
          </button>

          <button type="button" class="btn bg-secundario text-white" @click="printPDF(false, 'print')"
            v-if="dataDetail && ventaImprimir && !boletaLocal && !(dataDetail.type || type)">
            Imprimir Venta
          </button>
          <button type="button" class="btn bg-secundario text-white" @click="printPDF(true, 'print')"
            v-if="boletaLocal && dataDetail && dataDetail.type && dataDetail.type == 'Boleta Local'">
            Imprimir Boleta Local
          </button>
          <button type="button" class="btn bg-secundario text-white" @click="printPDF(false, 'pdf')"
            v-if="dataDetail && ventaImprimir && !boletaLocal && !(dataDetail.type || type)">
            PDF Venta
          </button>
          <button type="button" class="btn bg-secundario text-white" @click="printPDF(true, 'pdf')"
            v-if="boletaLocal && dataDetail && dataDetail.type && dataDetail.type == 'Boleta Local'">
            PDF Boleta Local
          </button>

          <!--<button type="button" class="btn bg-primario text-white" data-dismiss="modal">Cerrar</button>-->
        </div>
      </div>
    </div>
      <!-- modales -->
    <verifyDevolution :propVerify="propVerify"  @refreshData="refreshData"/>
  </div>
</template>

<script>
import customTable from '../tables/table.vue';
import verifyDelete from '@/components/modals/verifyDelete.vue';
import verifyDevolution from '@/components/modals/verifyDevolution.vue';

import ConfigHelper from '@/helpers/ConfigHelper.js';
import Connection from '../../helpers/Connection.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import AllErrors from '@/helpers/AllErrors.js';
import BaseUrl from '@/helpers/baseUrl.js';
import Print from '@/helpers/Print.js';
import Loader from '@/helpers/Loader';
import { refreshData } from '../../store/main/actions';
const fs = require('fs');

export default {
  name: 'detailSell',
  data() {
    return {
      type: null,
      jsonTable: {
        btn: true,
        items: null,
        height: '350px',
        footer: {
          label: 'Total de venta',
          value: false
        },
        rows: [
          { key: 'id', class: 'text-capitalize', permission: 'devolutionsInstalled' },
          { key: 'name', class: 'text-capitalize', permission: 'default' },
          { key: 'quantity', class: '', permission: 'default' },
          { key: 'unitary_price', class: '', permission: 'default' },
          { key: 'totalPrice', class: '', permission: 'siiInstaller' },
        ],
        titles: [
          { label: 'ID', class: '', permission: 'devolutionsInstalled', type: false },
          { label: 'Nombre', class: '', permission: 'default', type: false },
          { label: 'Cantidad', class: '', permission: 'default', type: false },
          { label: 'Precio c/u', class: '', permission: 'default', type: false },
          { label: 'Total', class: '', permission: 'default', type: false },
          { label: 'Devolver', class: '', permission: 'devolutionsInstalled', type: false },
        ]
      },
      propVerify: null,
    }
  },
  props: [
    'dataDetail',
    'clientFromModal'
  ],
  components: {
    customTable,
    verifyDevolution
  },
  methods: {
    // Cancelar la venta
    async cancelSell() {
      Loader.fullPage();
      var request = await this.$store.dispatch("sells/cancelFactura", this.dataDetail.id);
      console.log(request);
      if (request.success) {
        var printPDF = await Print.printBase64(request.data);

        this.$awn.success('Venta cancelada exitosamente', { labels: { success: 'CORRECTO' } });
        this.$emit('refreshData', false, null, false, false, this.dataDetail.id);
      } else {
        this.$awn.alert(request.data);
      }
      Loader.hide();
    },
    async refreshData(){
      this.$emit('refreshData');
    },
    // Impresion
    async printPDF(boleta_local = false, action = 'print') {
      /*
        El cliente llama a la api
        /api/sell/<id>/pdf
        que genera el PDF en el Controllers_local/SellController.php->printPDF
        los archivos TEMPLATES son:
        boleta.blade.php
        addition_boleta.blade.php
      */
      Loader.fullPage();
      var boleta = (boleta_local == true) ? true : false;
      var request = await this.$store.dispatch("sells/printPDF", { id: this.dataDetail.id, boleta: boleta });
      console.log(request);
      Loader.hide();

      if (request.success) {

        // Si hay que imprimir
        if (action === 'print') {
          var printPDF = await Print.printBase64(request.data);
          if (printPDF) this.$awn.success('Impresion realizada exitosamente', { labels: { success: 'CORRECTO' } });
        } else if (action === 'pdf') {
          var printPDF = await Print.viewBase64(request.data);
        }

      } else {
        AllErrors.getError(request.data);
      }

    },

    // Impresion
    async printPDFGuia(action = 'print') {
      Loader.fullPage();
      var request = await this.$store.dispatch("sells/printPDFGuia", { id: this.dataDetail.id });
      console.log(request);
      Loader.hide();

      if (request.success) {

        // Si hay que imprimir
        if (action === 'print') {
          var printPDF = await Print.printBase64(request.data);
          if (printPDF) this.$awn.success('Impresion realizada exitosamente', { labels: { success: 'CORRECTO' } });
        } else if (action === 'pdf') {
          var printPDF = await Print.viewBase64(request.data);
        }

      } else {
        AllErrors.getError(request.data);
      }

    },

    // Procesar venta
    async procesarVenta(type = null, isProcess = false, action = 'print') {

      var typeOfSell = null;
      if (!type) {
        typeOfSell = this.type;
      } else {
        typeOfSell = type;
      }

      Loader.fullPage();
      var thing = new FormData();
      thing.append('modal', 1);
      var request = await this.$store.dispatch("sells/processSell", { id: this.dataDetail.id, type: typeOfSell, data: thing });
      Loader.hide();

      console.log('>>>>>>>>IS PETICION BOLETA FACTURA', type);
      console.log('PETICION DE FACTURA/BOLETA PDF: ', request);

      if (request.success) {

        if (action === 'print') {
          var printPDF = await Print.printBase64(request.data);
          if (printPDF) this.$awn.success('Impresion realizada exitosamente', { labels: { success: 'CORRECTO' } });
        } else if (action === 'pdf') {
          var printPDF = await Print.viewBase64(request.data);
        }

        this.type = typeOfSell;
        if (isProcess) {
          this.$awn.success('Venta procesada exitosamente', { labels: { success: 'CORRECTO' } });
        }


        this.$emit('refreshData', false, null, false, false, this.dataDetail.id);

      } else {
        this.type = null;
        this.$awn.alert(request.data);
      }

    },

    async processGuiaDespacho(type = null, isProcess = false, action = 'print') {

      var typeOfSell = null;
      if (!type) {
        typeOfSell = this.type;
      } else {
        typeOfSell = type;
      }

      Loader.fullPage();
      var thing = new FormData();
      thing.append('modal', 1);
      var request = await this.$store.dispatch("sells/processSell", { id: this.dataDetail.id, type: typeOfSell, data: thing });
      Loader.hide();

      console.log('>>>>>>>>IS PETICION BOLETA FACTURA', type);
      console.log('PETICION DE FACTURA/BOLETA PDF: ', request);

      if (request.success) {

        if (action === 'print') {
          var printPDF = await Print.printBase64(request.data);
          if (printPDF) this.$awn.success('Impresion realizada exitosamente', { labels: { success: 'CORRECTO' } });
        } else if (action === 'pdf') {
          var printPDF = await Print.viewBase64(request.data);
        }

        this.type = typeOfSell;
        if (isProcess) {
          this.$awn.success('Venta procesada exitosamente', { labels: { success: 'CORRECTO' } });
        }


        this.$emit('refreshData', false, null, false, false, this.dataDetail.id);

      } else {
        this.type = null;
        this.$awn.alert(request.data);
      }

    },

    // Consultar venta
    async consultarVenta() {
      Loader.fullPage();
      var request = await this.$store.dispatch("sells/consultarDTE", this.dataDetail.id);
      console.log(request);
      Loader.hide();
      if (request.success) {
        this.$awn.success(request.data, { labels: { success: 'CORRECTO' } });
        this.$emit('refreshData', false, null, false, false, this.dataDetail.id);
      } else {
        this.$awn.alert(request.data);
      }
    },

    // Editar cliente
    async editarCliente(client) {
      if (!client) {
        $('#clientCreate').modal('show');
        return;
      }
      $('#clientCreate').modal('hide');
      Loader.fullPage();

      var thing = new FormData();
      for (let key in client) {
        if (client[key]) {
          thing.append(key, client[key]);
        }
      }
      var request = await this.$store.dispatch("sells/editClient", { data: thing, id: this.dataDetail.id });
      Loader.hide();
      if (request.success) {
        this.$awn.success('Cliente editado exitosamente', { labels: { success: 'CORRECTO' } });
        this.$emit('refreshData', false, null, false, false, this.dataDetail.id);
      } else {
        this.$awn.alert(request.data);
      }
    },


    // Otras funciones
    formatNumber(number) {
      return FormatNumber.format(number);
    },
    deFormatNumber(number, backend = true) {
      if (backend) {
        return FormatNumber.deFormatBackend(number);
      } else {
        return FormatNumber.deFormat(number);
      }
    },
    getImage(image) {
      return BaseUrl.getUrl('images/' + image);
    },
    openVerifyDelete(product_sell) {
      this.propVerify = {
        params: {'product_sell_id' : product_sell.id, 'quantity': product_sell.quantity},
        title: 'Devolucion de producto',
        text: `¿Usted esta seguro de querer eliminar el producto ${product_sell.name} de la venta?`,
        store: 'devolutions/devolutionProductSell',
        success: 'Producto eliminado exitosamente'
      };
      $('#verifyDelete').modal('show');
    },
    
  },
  computed: {
    clientsPhone: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes.ajustes.cliente_telefono'); } },
    clientInstalled: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes'); } },
    siiInstalled: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii'); } },
    ventaImprimir: { get() { return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_imprimir_venta'); } },
    notaInstalled: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.nota_de_credito');
      }
    },
    boletaLocal: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local');
      }
    },
    settingBoleta: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta');
      }
    },
    settingFactura: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.factura');
      }
    },
    devoluciones:{ get(){return ConfigHelper.ConfStr('modulos.ventas.submodulos.devolutions'); } }
  },
  watch: {
    clientFromModal: {
      handler(val) {
        console.log(val);
        if (val) {
          this.editarCliente(val);
        }
      }
    },
    dataDetail(value) {
      this.jsonTable.items = value.products;
      if (value.products) {
        for (var i = 0; i < this.jsonTable.items.length; i++) {
          var subtotal = this.jsonTable.items[i].unitary_price * this.jsonTable.items[i].quantity;
          this.jsonTable.items[i].totalPrice = subtotal.toString();
        }
      }
      this.jsonTable.footer.value = '$' + this.formatNumber(this.deFormatNumber(value.total));
    }
  },
}
</script>

<style scoped>
.client_list {
  height: 100% !important;
  max-height: 140px !important;
}

.list-group {
  overflow-y: scroll;
  overflow: auto;
}

::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-track {
  background: #c0c0c0;
  border-radius: 50px;
}

::-webkit-scrollbar-thumb {
  background: #000000;
  border-radius: 50px;
}

.list-group-item-action {
  border: none !important;
}

.list-group-item-border {
  border-bottom: 1px solid #e2e2e2 !important;
  padding: 12px 15px;
}

/* .list-group-item-action:hover{
    z-index: 2 !important;
    color: #ffffff !important;
    background-color: #007bff !important;
    border-color: #007bff !important;
  } */
@media (max-width: 768px) {
  .list-group {
    height: 250px !important;
  }
}

@media (min-width: 576px) {
  .modal-dialog {
    max-width: 800px !important;
  }
}
</style>
