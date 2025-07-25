<template>
  <div class="product-bg-modern p-3">
    <!-- Tarjetas de Gestión -->
    <div class="d-flex w-100 h-100 flex-column justify-content-start">
      <!-- Acciones para los productos -->
      <div class="pl-2 d-flex row w-100 align-items-start">
        <div class="col-md-4 col-sm-4 col-12 d-flex flex-column">
          <div class="card modern-info-card modern-info-card-static">
            <div class="card-header modern-card-header">
              <i class="fas fa-boxes-stacked me-2"></i>
              <div>
                <h5 class="font-weight-bold m-0">Gestión de Productos</h5>
                <span class="card-subtitle">Edita, agrega o elimina productos</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-4 col-12 d-flex flex-column">
          <div class="card modern-info-card modern-info-card-clickable" @click="openGlobalHistoryProduct">
            <div class="card-header modern-card-header">
              <i class="fas fa-history me-2"></i>
              <div>
                <h5 class="font-weight-bold m-0">Historial de productos</h5>
                <span class="card-subtitle">Transacciones globales (registrado, editado)</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-sm-4 col-12 d-flex flex-column">
          <div class="top-right-buttons">
            <button type="button" class="modern-action-btn btn-primary-modern" data-toggle="modal"
              data-target="#newProductModal">
              <i class="fas fa-plus me-2"></i>
              Añadir Producto
            </button>
            <label for="importProducts" class="modern-action-btn btn-secondary-modern">
              <i class="fas fa-upload me-2"></i>
              Importar Productos
              <input type="file" id="importProducts" style="display: none;"
                accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                @change="uploadExcel">
            </label>
            <button type="button" class="modern-action-btn btn-accent-modern" data-toggle="modal"
              data-target="#categoriesModal" v-if="categoriesInstalled">
              <i class="fas fa-tags me-2"></i>
              Categorías
            </button>
            <button v-if="downloadExcelInstaller" @click="downloadExcel"
              :class="['modern-action-btn', 'btn-inventory-modern', { 'disabled': offOn }]">
              <i class="fas fa-download me-2"></i>
              Inventario
            </button>
          </div>
        </div>
      </div>

      <!-- Filtros integrados con Bootstrap Grid -->
      <div class="row px-3 mt-3 mb-2" v-if="productsGet">
        <div class="col-md-9">
          <input 
            type="text" 
            v-model="ProductName" 
            :disabled="this.disableCategory"
            @keypress.enter="getProducts"
            class="form-control modern-input-clean" 
            placeholder="🔍 Buscar producto..."
          />
        </div>
        <div class="col-md-3" v-if="categoriesInstalled">
          <select 
            v-model="category" 
            @change="getProducts"
            :disabled="disableCategory"
            class="form-control modern-select-clean"
          >
            <option :value="null">Todas las categorías</option>
            <option v-for="cat in categories" v-if="cat.status === 0" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Listado de productos-->
    <div ref="loaderProduct" v-if="(products && products.items.length > 0)" class="vld-parent px-2 mt-2">
      <!-- tabla -->
      <div class="modern-table-container" v-if="productTable">
        <customTable v-model="jsonTable" @orderBy="orderBy" v-slot="props">
          <div class="action-buttons-group">
            <button @click="selectProduct(props.item)" class="btn-icon btn-edit" 
              data-toggle="modal" data-target="#newProductModal" title="Editar">
              <i class="fas fa-edit"></i>
            </button>
            <button @click="openVerify(props.item)" class="btn-icon btn-delete" title="Eliminar">
              <i class="fas fa-trash-alt"></i>
            </button>
            <button @click="openHistoryProduct(props.item)" class="btn-icon btn-view" title="Ver historial">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </customTable>
      </div>
      <!-- lista -->
      <div v-if="!productTable" class="modern-product-grid row">
        <div v-for="(product, index) in products.items" :key="'product-' + index" class="products__col">
          <div class="modern-product-card">
            <card-product v-if="product.category_status === 0" :product="product" @edit="selectProduct"
              @remove="openVerify"></card-product>
          </div>
        </div>
      </div>

      <!-- Paginacion -->
      <div class="modern-pagination-container">
        <paginate v-if="(products && products.pages > 1)" v-model="products" :offOn="offOn" @getPage="getProducts" />
      </div>
    </div>
    <div ref="loaderProduct" v-else class="vld-parent px-2 mt-2">
      <div class="modern-empty-state">
        <i class="fas fa-box-open empty-icon"></i>
        <h3 class="empty-title">No existen productos actualmente</h3>
        <p class="empty-subtitle">Agrega tu primer producto para comenzar</p>
        <button type="button" class="modern-action-btn btn-primary-modern" data-toggle="modal"
          data-target="#newProductModal">
          <i class="fas fa-plus me-2"></i>
          Crear Primer Producto
        </button>
      </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="historyModal" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" v-if="propProduct"><b>Historial de producto {{ propProduct.name }}</b></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="d-flex flex-column px-2" ref="loaderHistoryProduct"
              v-if="(productsHistories && productsHistories.items.length > 0)">
              <div class="noti-title bg-primario">Historial de transacciones</div>

              <!-- <div class="noti-list bg-light" v-for="val in productsHistories.items">
                 <span style="font-weight:bold">{{ val.type }}:</span> <span> {{val.transaction}} </span>
              </div> -->
              <customTable v-model="jsonTableHistoryTransation" @orderBy="orderBy" v-slot="props">

              </customTable>

              <paginate v-if="(productsHistories && productsHistories.pages > 1)" v-model="productsHistories"
                :offOn="offOn" @getPage="getHistoryProducts" />
              <div class="md-12">
                <button type="button" data-dismiss="modal"
                  class="btn bg-secundario btn-sm text-white float-right">Cerrar</button>
              </div>
            </div>
            <div class="d-flex flex-column px-2" ref="loaderHistoryProduct" v-else>
              <h5>No hay historial de transacciones actualmente</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="historyGlobalModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><b>Historial de productos</b></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="mediaWidth">
              <div class="d-flex flex-column m-1">
                <label>Rango de fechas</label>
                <date-picker class="widthInput" format="YYYY-MM-DD" type="date" v-model="rangeDate"
                  @change="getHistoryGlobalsProducts(oldPage)" range placeholder="Fechas" confirm></date-picker>
              </div>
            </div>
            <div class="d-flex flex-column px-2" ref="loaderHistoryProduct"
              v-if="(productsGlobalsHistories && productsGlobalsHistories.items.length > 0)">
              <customTable v-model="jsonTableHistory" @orderBy="orderBy" v-slot="props">
              </customTable>
              <paginate v-if="(productsGlobalsHistories && productsGlobalsHistories.pages > 1)"
                v-model="productsGlobalsHistories" :offOn="offOn" @getPage="getHistoryGlobalsProducts" />
              <div class="md-12">
                <button type="button" data-dismiss="modal"
                  class="btn bg-secundario btn-sm text-white float-right">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="stockInstalled && productsSelect" class="modal fade " id="modalProductStock" tabindex="-1" role="dialog"
      aria-labelledby="modalProductStock" aria-hidden="true" data-backdrop="false">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primario">
            <h5 class="modal-title">Producto Stock</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" ref="loaderStockProduct">
            <div class="row">
              <div class="col-12 text-center">
                <span><strong>Escriba el nombre del producto</strong></span>
                <!-- <Select2 v-model="this.itemSelect" :options="this.productsSelect" :settings="{ dropdownParent: '#modalProductStock', width: '100%' }" @change="thisProduct($event)" @select="thisProductEvent($event)"/> -->
                <v-select v-model="itemSelect" :options="this.productsSelect" @input="thisProductEvent" label="text" />
                <br>
                <div v-if="stock_first">
                  <span><strong>{{ stock_first }}</strong></span>
                  <input class="Jinput-border-none btn" type="number" v-model="product_stock"
                    v-on:keyup.enter="productStockAdd()" name="product_stock" ref="product_Stock_counter"
                    v-on:keydown="handleKeyDown2" id="product_stock" min="1" autofocus />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <categories v-if="categoriesInstalled" />
    <verify-modal :propVerify="propVerify" @refreshData="refreshData" />
    <new-product @refresh="refreshData" ref="newProduct" @closeEdit="selectProduct" />
  </div>
</template>

<script>
// Components
import cardProduct from '@/components/cards/card_product.vue';
import paginate from '@/components/MPage.vue';
import newProduct from '@/components/modals/newProduct.vue';
import categories from '@/components/modals/categories.vue';
import verifyModal from '@/components/modals/verifyDelete.vue';
import customTable from '@/components/tables/table.vue';
import moment from 'moment';

// Helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';
import Print from '@/helpers/Print.js';

export default {
  data() {
    return {
      rangeDate: [new Date(), new Date()],
      startTime: '00:00:00',
      endTime: '23:59:59',
      oldPage: 1,
      products: null,
      category: null,
      propVerify: null,
      propProduct: null,
      productsHistories: null,
      productsGlobalsHistories: null,
      ProductName: null,
      disableCategory: false,
      jsonTable: {
        btn: true,
        items: null,
        rows: [
          { key: 'name', class: '', permission: 'default' },
          { key: 'price', class: '', permission: 'default', subKey: 'cecina', subPermission: 'cecinaInstalled', },
          { key: 'category_name', class: '', permission: 'categoriesInstalled' },
          { key: 'stock', class: '', permission: 'stockInstalled' },
        ],
        titles: [
          { label: 'Nombre', class: 'th-sm', permission: 'default', type: 'orderBy', orderBy: false },
          { label: 'Precio', class: '', permission: 'default', type: false },
          { label: 'Categoria', class: 'th-sm', permission: 'categoriesInstalled', type: false },
          { label: 'Stock', class: 'th-sm', permission: 'stockInstalled', type: false },
          { label: 'Acciones', class: 'th-sm text-center', permission: 'default', type: false },
        ]
      },
      jsonTableHistory: {
        items: null,
        rows: [
          { key: 'name', class: '', permission: 'default' },
          { key: 'transaction', class: '', permission: 'default' },
          { key: 'created_at', class: '', permission: 'default' },
        ],
        titles: [
          { label: 'Producto', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Transaccion', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Fecha', class: '', permission: 'default', type: 'false', orderBy: false },
        ]
      },

      jsonTableHistoryTransation: {
        items: [],
        rows: [

          { key: 'created_at', class: '', permission: 'default' },
          { key: 'name', class: '', permission: 'default' },
          { key: 'namecategory', class: '', permission: 'default' },
          { key: 'type', class: '', permission: 'default' },
          // { key: 'field_afected', class: '', permission: 'default' },
          { key: 'old_value', class: '', permission: 'default' },
          { key: 'new_value', class: '', permission: 'default' },
          { key: 'fullname', class: '', permission: 'default' },
          // {key:'name_old', class:'', permission:'default'},
          // {key:'name_new', class:'', permission:'default'},
          // {key:'stock_old', class:'', permission:'default'},
          // {key:'stock_new', class:'', permission:'default'},
        ],

        titles: [
          { label: 'Fecha y hora', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Producto', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Categoria', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Tipo', class: '', permission: 'default', type: 'false', orderBy: false },
          // { label: 'Campo', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Anterior', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Actual', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Usuario', class: '', permission: 'default', type: 'false', orderBy: false },
        ]
      },

      productsSelect: [],
      itemsSelect: null,
      itemSelect: '',
      stock_first: '',
      product_stock: '',
    }
  },
  async mounted() {
    //HavePermission
    this.refreshData(true);

    document.addEventListener("keydown", (e) => {
      if (e.keyCode == 115) {
        this.modalProductStock();
      }
    });
  },
  components: {
    newProduct,
    categories,
    verifyModal,
    paginate,
    cardProduct,
    customTable
  },
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    // Refrescando productos
    async refreshData(loading = false) {
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      this.disableCategory = true;
      if (loading) Loader.containe(this.$refs.loaderProduct);
      else Loader.dinamic();

      if (this.productsGet) {
        await this.getProducts(this.oldPage, true);

        if (this.categoriesInstalled) await this.$store.dispatch("products/getCategories");
      } else {
        this.$router.push('/inicio');
      }

      // Culminando la funcion
      Loader.hide();
      this.disableCategory = false;
      this.offOn = false;
    },
    // Ordenamiento de asc/desc
    orderBy() {
      this.jsonTable.titles[0].orderBy = !this.jsonTable.titles[0].orderBy;
      this.refreshData();
    },
    // Traer productos y parametros de productos
    async getProducts(page = false, isRefresh = false) {
      if (!isRefresh) {
        this.disableCategory = true;
        Loader.containe(this.$refs.loaderProduct);
      }

      // Parametros para la ruta
      var params = '?params=true';
      if (page && this.oldPage != page) this.oldPage = page;
      params += '&page=' + this.oldPage;

      if (this.category != null && this.category != '') params += '&categoryOfProduct=' + this.category;

      if (this.ProductName != null && this.ProductName != '') params += '&nameOfProduct=' + this.ProductName;

      // Ordenamiento
      var orderBy = (this.jsonTable.titles[0].orderBy) ? 'asc' : 'desc';
      params += '&orderBy_name=' + orderBy;
      // Iniciando peticion
      var request = await this.$store.dispatch("products/getProducts", params);
      // Verificando respuesta
      if (!request.success) this.$awn.alert('Error al obtener los productos');
      else {
        this.products = (request.data.items.length == 0) ? false : request.data;
        this.jsonTable.items = this.products.items;
      }

      if (!isRefresh) {
        Loader.hide();
        this.disableCategory = false;
      }
    },
    // Modal de verificacion
    openVerify(product) {
      this.propVerify = {
        params: product.id,
        title: 'Eliminar producto',
        text: '¿Usted esta seguro de querer eliminar el producto ' + product.name + '?',
        store: 'products/deleteProduct',
        success: 'Producto eliminado exitosamente'
      };
      $('#verifyDelete').modal('show');
    },
    openHistoryProduct(product) {
      this.propProduct = {
        'id': product.id,
        'name': product.name
      };
      this.getHistoryProducts(this.oldPage);
    },
    async getHistoryProducts(page = false) {
      this.offOn = true;
      Loader.containe(this.$refs.loaderHistoryProduct);
      var params = '?params=true';
      params += '&product_id=' + this.propProduct.id;

      if (page && this.oldPage != page) {
        this.oldPage = page;
        params += '&page=' + this.oldPage;
      }
      var request = await this.$store.dispatch("products/getHistoryProducts", params);

      if (!request.success) {
        this.$awn.alert('Error al obtener el historial del producto');
      } else {
        this.productsHistories = (request.data.items.length == 0) ? false : request.data;
        if (this.productsHistories && this.productsHistories.items.length > 0) {
          //agrego la info al objecto de table.
          this.jsonTableHistoryTransation.items = this.productsHistories.items;
          console.log("HISTORIAL DE PRODUCTOOOOS!!", this.productsHistories);
          console.log("MOSTRAR OBJECTO DE LA TABLA", this.jsonTableHistoryTransation);
          $('#historyModal').modal('show');
        } else {

          this.$awn.info('No hay historial de transacciones actualmente');
        }
      }
      Loader.hide();
      this.offOn = false;
    },
    openGlobalHistoryProduct() {
      $('#historyGlobalModal').modal('show');
      this.getHistoryGlobalsProducts(this.oldPage);
    },
    async getHistoryGlobalsProducts(page = false) {
      this.offOn = true;
      Loader.containe(this.$refs.loaderHistoryProduct);
      var params = '?params=true';
      if (page && this.oldPage != page) {
        this.oldPage = page;
        params += '&page=' + this.oldPage;
      }
      var startTime = (this.startTime == null) ? '00:00:00' : this.startTime;
      var endTime = (this.endTime == null) ? '23:59:59' : this.endTime;

      var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + startTime;
      var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + endTime;
      const data = {
        startDate: startDate,
        endDate: endDate,
      };
      console.log("original", data)
      params += '&startDate=' + startDate;
      params += '&endDate=' + endDate;
      if (this.rangeDate.length == 0) return this.$awn.alert('Porfavor inserte un rango de fechas');

      // Parametros para los contadores
      var request = await this.$store.dispatch("products/getHistoryProducts", params);

      if (!request.success) {
        this.$awn.alert('Error al obtener el historial global de productos');
      } else {
        this.productsGlobalsHistories = (request.data.items.length == 0) ? false : request.data;
        if (this.productsGlobalsHistories && this.productsGlobalsHistories.items.length > 0) {
          this.jsonTableHistory.items = this.productsGlobalsHistories.items;
        } else {
          this.$awn.info('No hay historial de transacciones en el rango de fechas seleccionadas');
        }
      }
      Loader.hide();
      this.offOn = false;
    },

    // Seleccionar producto a editar
    selectProduct(product) {
      if (!product) {
        this.$refs.newProduct.refreshData();
      } else {
        this.$refs.newProduct.refreshData(product);
      }
    },

    async downloadExcel() {
      Loader.fullPage();
      let request = await this.$store.dispatch("products/exportProducts");
      Loader.hide();

      // Verficando respuesta
      if (!request.success) return this.$awn.alert('Desacargar fallida', { labels: { success: 'Error' } });
      console.log(request.data);
      // Guardando documento
      await Print.downloadExcel(request.data);
      this.$awn.success('Descarga exitosa', { labels: { success: 'CORRECTO' } });
    },

    async uploadExcel(event) {
      Loader.fullPage();
      var thing = new FormData();
      thing.append('products', event.target.files[0]);
      var request = await this.$store.dispatch("sells/importProducts", thing);
      if (request.success) {
        Loader.hide();
        this.refreshData();
        this.$awn.success("Productos importados con exito", { labels: { success: 'CORRECTO' } });
      } else {
        this.$awn.alert(request.data, { labels: { success: 'Error' } });
      }
      Loader.hide();

      document.getElementById('importProducts').value = null;
    },
    async modalProductStock() {
      if (this.productsGet) {
        // Iniciando peticion
        var request = await this.$store.dispatch("products/getProductsOfSell");
        // Verificando respuesta
        if (request.success) {
          let productsSelect = [];
          var productos = (request.data.length == 0) ? false : request.data;
          console.log(productos);
          if (this.barcodeInstalled) {
            $.each(productos, function (key, val) {
              productsSelect.push({ "id": val.id, "text": val.barcode + ' ' + val.name + ' - stock ' + val.stock });
            });
          }
          if (!this.barcodeInstalled) {
            $.each(productos, function (key, val) {
              productsSelect.push({ "id": val.id, "text": val.name + ' - stock ' + val.stock });
            });
          }
          this.productsSelect = productsSelect;
          $('#modalProductStock').modal('show');
        } else {
          this.$awn.alert('Error al obtener los productos');
        }
      }
    },

    thisProductEvent({ id, text }) {
      text = text.replace('-', '\n\n');
      this.stock_first = text;
      this.product_id = id;
      console.log("focus 2 borrar-focus");
      setTimeout(() => { this.$refs.product_Stock_counter.focus(); }, 500);
    },
    async productStockAdd() {
      if (this.product_id > 0) {
        Loader.fullPage();
        let data = new FormData();
        data.append('id', this.product_id);
        data.append('stock', this.product_stock);
        var request = await this.$store.dispatch("products/editStock", data);
        if (request.success) {
          this.$awn.success(request.data, { labels: { success: 'CORRECTO' } });
          $('#modalProductStock').modal('hide');
        } else {
          this.$awn.alert('Error al actualizar el stock');
        }
      }
      Loader.hide();
    },
    getSearchValue(result) {
      return result.name + '';
    },
    handleKeyDown2(event) {
      if (/^\d$/.test(event.key)) {

      } else if (event.key === '+' || event.key === '-' || event.key === 'ArrowUp' || event.key === 'ArrowDown') {
        // Ejecutar las funciones mas() o menos() aquí
        if (event.key === '+' || event.key === 'ArrowUp') {
          this.increaseProduct_stock();
        } else if (event.key === '-' || event.key === 'ArrowDown') {
          this.decreaseProduct_stock();
        }
        event.preventDefault();
      }
    },
    increaseProduct_stock() {
      this.product_stock++
    },
    decreaseProduct_stock() {
      if (this.product_stock > 1) {
        this.product_stock--
      }
    },
  },
  computed: {
    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input', offOn) }
    },
    // Categorias existentes
    categories: { get() { return this.$store.getters['products/categories']; } },
    // Permisos para los productos y sus modulos
    productsGet: { get() { return ConfigHelper.HavePermission('productos_obtener'); } },
    productTable: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.productos_tabla'); } },
    categoriesInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.submodulos.categorias'); } },
    downloadExcelInstaller: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.donwload_inventory'); } },
    stockInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_stock'); } },
    barcodeInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_barcode'); } },

    promoInstalled:{
      get(){
        return ConfigHelper.ConfStr('modulos.productos.submodulos.precio_promo');
      }
    },
    productsGet: {
      get() {

        if (ConfigHelper.HavePermission('productos_obtener')) {
          return ConfigHelper.HavePermission('productos_obtener');
        } else {
          return false;
        }

      }
    },
  }
}
</script>

<style lang="scss">
/* ===== MODERN PRODUCTS INTERFACE STYLES ===== */
.product-bg-modern {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  min-height: 100vh;
  position: relative;
  font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Top Right Buttons Container */
.top-right-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: stretch;
  height: fit-content;
  justify-content: flex-start;
  padding: 0;
  margin: 0;
}

/* Modern Info Cards */
.modern-info-card {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  height: fit-content;
  margin-bottom: 0.5rem;
  background: white;
}

/* Static card - no interaction */
.modern-info-card-static {
  cursor: default;
}

.modern-info-card-static:hover {
  transform: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

/* Clickable card - precise hitbox */
.modern-info-card-clickable {
  cursor: pointer;
}

.modern-info-card-clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Ensure the clickable area is only the card content */
.modern-info-card-clickable .modern-card-header {
  cursor: pointer;
  user-select: none;
}

.modern-card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1rem 1.25rem;
  color: white;
  border: none;
  display: flex;
  align-items: center;
  gap: 10px;
  position: relative;
  overflow: hidden;
  min-height: auto;
  border-radius: 12px;
}

.modern-card-header::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
  animation: card-shine 3s infinite;
  pointer-events: none;
}

@keyframes card-shine {
  0% { transform: translateX(-100%) translateY(-100%); }
  50% { transform: translateX(100%) translateY(100%); }
  100% { transform: translateX(-100%) translateY(-100%); }
}

.modern-card-header i {
  font-size: 1.2rem;
  opacity: 0.9;
  min-width: 20px;
}

.modern-card-header h5 {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0;
  line-height: 1.2;
}

.card-subtitle {
  font-size: 0.75rem;
  opacity: 0.85;
  font-weight: 400;
  margin-top: 2px;
  line-height: 1.3;
}

/* Modern Action Buttons */
.modern-action-btn {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  color: white;
  font-weight: 600;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  margin: 4px 0;
  transition: all 0.3s ease;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 0.85rem;
  box-shadow: 0 1px 6px rgba(30, 58, 138, 0.25);
  min-height: 36px;
}

.btn-primary-modern {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 1px 6px rgba(102, 126, 234, 0.25);
}

.btn-secondary-modern {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 1px 6px rgba(16, 185, 129, 0.25);
}

.btn-accent-modern {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 1px 6px rgba(245, 158, 11, 0.25);
}

.btn-inventory-modern {
  background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
  box-shadow: 0 1px 6px rgba(139, 92, 246, 0.25);
}

.modern-action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
  color: white;
  text-decoration: none;
}

.modern-action-btn:disabled,
.modern-action-btn.disabled {
  opacity: 0.6;
  transform: none;
  cursor: not-allowed;
}

.modern-action-btn i {
  font-size: 0.8rem;
}

/* Modern Filters */
.modern-filters-container {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
  margin-top: 0.75rem;
}

/* Clean Filters - No Background */
.modern-filters-clean {
  background: transparent;
  border-radius: 0;
  padding: 0;
  box-shadow: none;
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Compact Filters Wrapper */
.modern-filters-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 1.5rem;
  margin-bottom: 1rem;
}

.modern-filters-compact {
  background: white;
  border-radius: 16px;
  padding: 1rem 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 1.5rem;
  max-width: fit-content;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.filter-item {
  display: flex;
  align-items: center;
  min-width: 200px;
}

/* Clean Input/Select Styles - More Subtle */
.modern-input-clean,
.modern-select-clean {
  border-radius: 12px;
  border: 1px solid #ddd;
  box-shadow: none;
  font-size: 14px;
  padding: 10px 15px;
  transition: all 0.3s ease;
  background: white;
  height: 42px;
}

.modern-input-clean:focus,
.modern-select-clean:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  outline: none;
}

.modern-input-clean::placeholder {
  color: #6b7280;
  font-weight: 400;
}

.modern-export-btn {
  background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  cursor: pointer;
  box-shadow: 0 1px 6px rgba(139, 92, 246, 0.25);
  font-size: 0.85rem;
  height: 36px;
}

.modern-export-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 12px rgba(139, 92, 246, 0.3);
}

/* Subtle Export Button - Less Prominent */
.modern-export-btn-subtle {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  cursor: pointer;
  box-shadow: 0 1px 6px rgba(102, 126, 234, 0.2);
  font-size: 0.85rem;
  height: 36px;
  opacity: 0.9;
}

.modern-export-btn-subtle:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 12px rgba(102, 126, 234, 0.25);
  opacity: 1;
}

.modern-export-btn i {
  font-size: 0.8rem;
}

.modern-export-btn-subtle i {
  font-size: 0.8rem;
}

/* Modern Table Container */
.modern-table-container {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-top: 1rem;
}

/* Action Buttons in Table */
.action-buttons-group {
  display: flex;
  gap: 6px;
  justify-content: center;
}

.btn-icon {
  border: none;
  padding: 8px 10px;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: all 0.15s ease;
  font-size: 0.875rem;
  position: relative;
  overflow: hidden;
}

.btn-edit {
  background: linear-gradient(135deg, #1e88e5 0%, #1976d2 100%);
  box-shadow: 0 2px 8px rgba(30, 136, 229, 0.2);
}

.btn-delete {
  background: linear-gradient(135deg, #e53935 0%, #d32f2f 100%);
  box-shadow: 0 2px 8px rgba(229, 57, 53, 0.2);
}

.btn-view {
  background: linear-gradient(135deg, #43a047 0%, #388e3c 100%);
  box-shadow: 0 2px 8px rgba(67, 160, 71, 0.2);
}

.btn-icon:hover {
  transform: translateY(-2px) scale(1.08);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
}

.btn-icon::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
}

.btn-icon:hover::before {
  left: 100%;
}

/* Modern Product Grid */
.modern-product-grid {
  margin-top: 1.5rem;
}

.modern-product-card {
  transition: all 0.3s ease;
}

.modern-product-card:hover {
  transform: translateY(-4px);
}

/* Modern Pagination */
.modern-pagination-container {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
  padding: 1rem;
}

/* Modern Pagination Styles */
::v-deep .pagination {
  display: flex;
  gap: 6px;
  align-items: center;
}

::v-deep .pagination .page-item .page-link {
  background: #e0e7ff;
  color: #1e3a8a;
  border: none;
  border-radius: 8px;
  padding: 8px 14px;
  margin: 0 2px;
  font-weight: 600;
  transition: all 0.2s ease;
  text-decoration: none;
  font-size: 0.875rem;
  box-shadow: 0 1px 3px rgba(30, 58, 138, 0.1);
}

::v-deep .pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(30, 58, 138, 0.3);
  transform: translateY(-1px);
}

::v-deep .pagination .page-item:not(.disabled) .page-link:hover {
  background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 3px 12px rgba(59, 130, 246, 0.3);
}

::v-deep .pagination .page-item.disabled .page-link {
  background: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
}

/* Table Animations */
@keyframes fadeInUp {
  from { 
    opacity: 0; 
    transform: translateY(15px); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}

::v-deep .table tbody tr {
  animation: fadeInUp 0.4s ease-in-out;
  animation-fill-mode: both;
}

::v-deep .table tbody tr:nth-child(1) { animation-delay: 0.05s; }
::v-deep .table tbody tr:nth-child(2) { animation-delay: 0.1s; }
::v-deep .table tbody tr:nth-child(3) { animation-delay: 0.15s; }
::v-deep .table tbody tr:nth-child(4) { animation-delay: 0.2s; }
::v-deep .table tbody tr:nth-child(5) { animation-delay: 0.25s; }

/* Stock Styling */
::v-deep .table tbody td {
  position: relative;
  transition: all 0.2s ease;
}

::v-deep .table tbody tr:hover {
  background-color: #f8fafc !important;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Stock negative styling */
::v-deep .stock-negative {
  color: #e53935 !important;
  background: #ffe5e5 !important;
  border-radius: 6px !important;
  padding: 4px 8px !important;
  font-weight: 600 !important;
  display: inline-block !important;
  font-size: 0.85rem !important;
}

/* Stock positive styling */
::v-deep .stock-positive {
  color: #43a047 !important;
  background: #e8f5e8 !important;
  border-radius: 6px !important;
  padding: 4px 8px !important;
  font-weight: 600 !important;
  display: inline-block !important;
  font-size: 0.85rem !important;
}

/* Modern Empty State */
.modern-empty-state {
  background: white;
  border-radius: 20px;
  padding: 3rem 2rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-top: 2rem;
}

.empty-icon {
  font-size: 4rem;
  color: #9ca3af;
  margin-bottom: 1rem;
}

.empty-title {
  color: #374151;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.empty-subtitle {
  color: #6b7280;
  margin-bottom: 2rem;
  font-size: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .modern-card-header {
    flex-direction: column;
    text-align: center;
    padding: 0.75rem;
    gap: 6px;
  }
  
  .modern-card-header i {
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
  }
  
  .modern-card-header h5 {
    font-size: 0.9rem;
  }
  
  .card-subtitle {
    font-size: 0.7rem;
  }
  
  .modern-action-btn {
    padding: 7px 12px;
    font-size: 0.8rem;
    margin: 3px 0;
  }
  
  .top-right-buttons {
    gap: 8px;
  }

  /* Clean filters responsive */
  .modern-input-clean,
  .modern-select-clean {
    height: 38px;
    font-size: 13px;
    margin-bottom: 10px;
  }
  
  .modern-input,
  .modern-select,
  .modern-input-clean,
  .modern-select-clean,
  .modern-export-btn,
  .modern-export-btn-subtle {
    height: 32px;
    font-size: 0.8rem;
  }
  
  .modern-input-clean,
  .modern-select-clean {
    padding: 8px 8px 8px 30px;
  }
  
  .input-icon {
    font-size: 0.75rem;
  }
  
  .action-buttons-group {
    flex-direction: column;
    gap: 4px;
  }
  
  .btn-icon {
    padding: 6px 8px;
    font-size: 0.8rem;
  }
  
  /* Hide some table columns on mobile */
  ::v-deep .table th:nth-child(3),
  ::v-deep .table td:nth-child(3) {
    display: none;
  }
  
  ::v-deep .pagination .page-item .page-link {
    padding: 6px 10px;
    font-size: 0.8rem;
  }
}

@media (max-width: 576px) {
  .modern-empty-state {
    padding: 2rem 1rem;
  }
  
  .empty-icon {
    font-size: 3rem;
  }
  
  .modern-action-btn {
    padding: 8px 12px;
    font-size: 0.8rem;
  }
  
  .top-right-buttons {
    gap: 6px;
  }

  /* Mobile clean filters styling */
  .modern-input-clean,
  .modern-select-clean {
    height: 36px;
    font-size: 12px;
    margin-bottom: 8px;
  }
  
  /* Stack action buttons horizontally on very small screens */
  .action-buttons-group {
    flex-direction: row;
    justify-content: space-around;
    gap: 2px;
  }
  
  .btn-icon {
    padding: 5px 6px;
    font-size: 0.75rem;
  }
  
  /* Hide price column on very small screens */
  ::v-deep .table th:nth-child(2),
  ::v-deep .table td:nth-child(2) {
    display: none;
  }
}

/* Large screens optimizations */
@media (min-width: 1200px) {
  .top-right-buttons {
    gap: 14px;
  }
  
  .modern-card-header {
    padding: 1.25rem 1.5rem;
  }
  
  .modern-filters-wrapper {
    padding-top: 1.25rem !important;
  }
  
  .modern-filters-container-aligned {
    padding: 1.25rem 2rem;
  }
  
  .filters-content {
    gap: 1.5rem;
  }
  
  .filter-group {
    min-width: 240px;
  }
}

/* Legacy products responsive styles */
.products {
  &__col {
    flex: 0 0 20%;
    width: 20%;
  }

  @media (max-width: 1150px) {
    &__col {
      flex: 0 0 25%;
      width: 25%;
    }
  }

  @media (max-width: 900px) {
    &__col {
      flex: 0 0 33.3333%;
      width: 33.3333%;
    }
  }

  @media (max-width: 767px) {
    &__col {
      flex: 0 0 50%;
      width: 50%;
    }
  }

  @media (max-width: 500px) {
    &__col {
      flex: 0 0 100%;
      width: 100%;
    }
  }
}
</style>
