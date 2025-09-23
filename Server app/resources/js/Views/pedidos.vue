<template>
    <div v-if="products" class="container-fluid">
      <HeaderAdmin :title="'Productos'" />
  
      <!-- <div class="pb-3">
        <label for="rutcliente">Buscar</label>
        <input id="rutcliente" type="text" placeholder="id" class="form-control" />
      </div> -->
  
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Productos</h3>
          <div class="card-tools d-flex flex-wrap">
            <div class="mr-1">
              <a @click="getProducts" class="btn btn-block bg-two text-white btn-font-r">
                <i class="fas fa-sync-alt"></i>
                <span class="mobile-modules">Refrescar</span>
              </a>
            </div>
            <div class="ml-1">
              <a @click="openProduct(false)" class="btn btn-block bg-one text-white btn-font-r">
                <i class="fas fa-plus"></i>
                <span class="mobile-modules">Crear producto</span>
              </a>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped projects">
            <thead>
              <tr>
                <th class="">Nombredasdasdsad</th>
                <th class="">Unidad(Precio)</th>
                <th class="">Precio Individual</th>
                <th class="">Cantidad Minina</th>
                <th class="">Stock</th>
                <th class="">Estado</th>
                <th class="actions">Acciones</th>
              </tr>
            </thead>
            <tbody v-for="(product, index) in products.items"  :key="index">
              <tr>
                <td class="display-none">
                  {{ product.name}}
                </td>
                <td class="descFeed">
                  {{product.price}}
                </td>
                <td class="descFeed">
                  {{product.compra}}
                </td>
                <td class="descFeed">
                  {{product.min_quantity}}
                </td>
                <td class="display-none">
                  {{ (product.stock != null) ? product.stock : '0' }}
                </td>
                <td class="text-center">
                  <span v-if="product.active == 1" class="badge badge-success">Activo</span>
                  <span v-else class="badge badge-danger">Inactivo</span>
                </td>
                <!-- <td class="display-none text-center">
                  {{product.created_at}}
                </td> -->
                <td class="text-right">
                  <a @click="openProduct(product)" class="btn bg-two btn-sm mr-1" href="#" title="Editar">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a @click="toggleProductStatus(product)" 
                     :class="['btn', 'btn-sm', 'mr-1', product.active == 1 ? 'btn-warning' : 'btn-success']" 
                     href="#" 
                     :title="product.active == 1 ? 'Desactivar' : 'Activar'">
                    <i :class="['fas', product.active == 1 ? 'fa-eye-slash' : 'fa-eye']"></i>
                  </a>
                  <a @click="removeProduct(product)" class="btn bg-one btn-sm" href="#" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- /.card-body -->
      </div>
      <div class="col-12">
        <nav aria-label="Contacts Page Navigation">
          <!-- Paginacion -->
          <paginate
            v-if="products.items.length > 1"
            :back="products.paginate.back"
            :next="products.paginate.next"
            :activePage="products.page"
            :totalPages="products.pages"
            @refreshData="getProducts"
          />
        </nav>
      </div>
  
      <create-to-edit v-model="modal" @refresh="getProducts" :dataEdit="dataEdit"/>
    </div>
  </template>
  
  <script>
  import HeaderAdmin from '../Components/header.vue';
  import paginate from  '../Components/paginate.vue';
  import createToEdit from '../Components/modals/products/createToEdit.vue';
  
  export default {
    data(){
      return{
        pageActual: 1,
        modal: false,
        dataEdit: null
      }
    },
    components:{
      HeaderAdmin,
      paginate,
      createToEdit,
    },
  
    mounted(){
      if(!this.products) this.getProducts();
    },
    methods: {
      async getProducts(val = false){
        let loader = this.$loading.show({
          // Optional parameters
          container: this.$refs.formContainer,
          color: '#007bff',
          width: 80,
          height: 80,
          backgroundColor: '#000000',
          opacity: 0.8,
          zIndex: 999,
        });
          if(val) this.pageActual = val;
          let params = '?page=' + this.pageActual;
  
          await this.$store.dispatch('products/index', params);
          await this.$store.dispatch('aplication/getAllAplications');
        loader.hide();
      },
      async removeProduct(product){
        let loader = this.$loading.show({
          // Optional parameters
          container: this.$refs.formContainer,
          color: '#007bff',
          width: 80,
          height: 80,
          backgroundColor: '#000000',
          opacity: 0.8,
          zIndex: 999,
        });

        let request = await this.$store.dispatch('products/remove', product.id);
        loader.hide();
  
        if(request.success){
          this.$toastr.success(request.data, 'Exitoso');
          this.getProducts();
        }else{
          this.$toastr.error(request.data, 'Error');
        }
      },
      async toggleProductStatus(product){
        let loader = this.$loading.show({
          container: this.$refs.formContainer,
          color: '#007bff',
          width: 80,
          height: 80,
          backgroundColor: '#000000',
          opacity: 0.8,
          zIndex: 999,
        });

        const newStatus = product.active == 1 ? 0 : 1;
        const actionText = newStatus == 1 ? 'activado' : 'desactivado';
        
        let request = await this.$store.dispatch('products/toggleStatus', {
          id: product.id,
          active: newStatus
        });
        
        loader.hide();

        if(request.success){
          this.$toastr.success(`Producto ${actionText} exitosamente`, 'Exitoso');
          this.getProducts();
        }else{
          this.$toastr.error(request.data, 'Error');
        }
      },
      openProduct(product){
        this.dataEdit = product;
        this.modal = true;
      },
    },
    computed:{
      products:{ get() { return this.$store.getters['products/getProducts'] } }
    },
  }
  </script>
  
  <style media="screen">
    .actions{
      width: 105px;
    }
  </style>
  