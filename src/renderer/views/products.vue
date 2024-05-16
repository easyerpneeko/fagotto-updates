<template>
  <div class="product-bg p-3">
    <!-- Filtros y acciones para los productos -->
    <div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center">
      <!-- Acciones para los productos -->
      <div class="pl-2 d-flex row w-100">
        <div class="col-md-4 col-sm-4 col-12 d-flex flex-column">
          <div class="card title-card">
            <div class="card-body">
              <h5 class="font-weight-bold m-0">Gestion de Productos</h5>
              <span>Edita, agrega o elimina productos</span>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-4 col-12 d-flex flex-column" style="cursor: pointer;"
          @click="openGlobalHistoryProduct">
          <div class="card title-card">
            <div class="card-body">
              <h5 class="font-weight-bold m-0">Historial de productos</h5>
              <span>Transacciones globales (registrado, editado)</span>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-sm-4 col-12 d-flex flex-column">
          <button type="button" class="m-1 btn bg-primario text-white text-capitalize" data-toggle="modal"
            data-target="#newProductModal">
            Añadir Producto
          </button>
          <label for="importProducts" class="m-1 btn bg-primario text-white text-capitalize">
            Importar Productos
            <input type="file" id="importProducts" style="display: none;"
              accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
              @change="uploadExcel">
          </label>
          <button type="button" class="m-1 btn bg-secundario text-white text-capitalize" data-toggle="modal"
            data-target="#categoriesModal" v-if="categoriesInstalled">
            Categorias
          </button>
        </div>
      </div>

      <!-- Filtros de productos -->
      <div class="d-flex row w-100 pt-4" v-if="productsGet">
        <div class="col-md-4 col-sm-6 col-12">
          <label for="ProductName">Buscar</label>
          <input :disabled="this.disableCategory" id="ProductName" v-model="ProductName" type="text"
            class="form-control" @keypress.enter="getProducts" />
        </div>

        <div class="col-md-4 col-sm-6 col-12" v-if="categoriesInstalled && productsGet">
          <label for="Category">Categoria</label>
          <select class="form-control" v-model="category" @change="getProducts" :disabled="disableCategory">
            <option :value="null" class="text-capitalize">Todas</option>
            <option :value="category.id" v-for="category in categories" v-if="category.status === 0" :key="category.id"
              class="text-capitalize">{{ category.name }}</option>
          </select>
        </div>
        <div v-if="downloadExcelInstaller"
          class="col-lg-2 col-md-4 col-sm-6 col-12 d-flex justify-content-md-end align-items-end">
          <a @click="downloadExcel"
            :class="['mt-2 mb-0 mx-0 w-100 btn bg-primario text-white text-capitalize', { 'disabled': offOn }]">
            Inventario
          </a>
        </div>
      </div>
    </div>

    <!-- Listado de productos-->
    <div ref="loaderProduct" v-if="(products && products.items.length > 0)" class="vld-parent px-2 mt-2">
      <!-- tabla -->
      <customTable v-if="productTable" v-model="jsonTable" @orderBy="orderBy" v-slot="props">
        <a @click="selectProduct(props.item)" class="py-1 px-2 text-center btn bg-secundario" href="#"
          data-toggle="modal" data-target="#newProductModal">
          <i class="fas fa-edit"></i>
        </a>
        <a @click="openVerify(props.item)" class="py-1 px-2 text-center btn bg-primario" href="#">
          <i class="fas fa-trash-alt"></i>
        </a>
        <a @click="openHistoryProduct(props.item)" class="py-1 px-2 text-center btn bg-dark" href="#">
          <i class="fas fa-solid fa-eye"></i>
        </a>
      </customTable>
      <!-- lista -->
      <div v-if="!productTable" class="row">
        <div v-for="(product, index) in products.items" :key="'product-' + index" class="products__col">
          <card-product v-if="product.category_status === 0" :product="product" @edit="selectProduct"
            @remove="openVerify"></card-product>
        </div>
      </div>

      <!-- Paginacion -->
      <paginate v-if="(products && products.pages > 1)" v-model="products" :offOn="offOn" @getPage="getProducts" />
    </div>
    <div ref="loaderProduct" v-else class="vld-parent px-2 mt-2">
      <div class="box-false d-flex flex-center text-center p-2 w-100">
        <h2>No existen productos actualmente</h2>
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
                    id="product_stock" min="1" autofocus />
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
          { key: 'field_afected', class: '', permission: 'default' },
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
          { label: 'Campo', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Valor Anterior', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Valor Nuevo', class: '', permission: 'default', type: 'false', orderBy: false },
          { label: 'Usuario', class: '', permission: 'default', type: 'false', orderBy: false },
        ]
      },

      productsSelect: [],
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
              // productsSelect.push({"id":val.id, "text":val.barcode+ ' '+val.name+ ' - stock '+val.stock });
              productsSelect.push({ "id": val.id, "text": val.name + ' - stock ' + val.stock });
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
          setTimeout(() => {
            this.productSearch = '';
          }, 500);
        } else {
          this.$awn.alert('Error al actualizar el stock');
        }
      }
      Loader.hide();
    },
    getSearchValue(result) {
      return result.name + '';
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
