<template>
    <div class="container-fluid">
      <HeaderAdmin :title="'Gramaje'" />

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Gramaje de Ingredientes</h3>
          <div class="card-tools d-flex flex-wrap">
            <div class="mr-1">
              <a @click="getIngredients" class="btn btn-block bg-two text-white btn-font-r">
                <i class="fas fa-sync-alt"></i>
                <span class="mobile-modules">Refrescar</span>
              </a>
            </div>
            <!-- <div class="ml-1">
              <a @click="openIngredient(false)" class="btn btn-block bg-one text-white btn-font-r">
                <i class="fas fa-plus"></i>
                <span class="mobile-modules">Crear Ingrediente</span>
              </a>
            </div> -->
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped projects">
            <thead>
              <tr>
                <th class="">Nombre</th>
                <th class="">Gramaje</th>
                <th class="actions">Acciones</th>
              </tr>
            </thead>
            <tbody v-for="(ingredient, index) in ingredients"  :key="index">
              <tr>
                <td class="display-none">
                  {{ ingredient.name}}
                </td>
                <td class="descFeed">
                  {{ingredient.quantity_grams}}
                </td>
                <td class="text-right">
                  <a @click="openProduct(ingredient)" class="btn bg-two btn-sm" href="#">
                    <i class="fas fa-edit"></i>
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
          <!-- <paginate
            v-if="ingredients.items.length > 1"
            :back="ingredients.paginate.back"
            :next="ingredients.paginate.next"
            :activePage="ingredients.page"
            :totalPages="ingredients.pages"
            @refreshData="getIngredients"
          /> -->
        </nav>
      </div>
  
      <create-to-edit v-model="modal" @refresh="getIngredients" :dataEdit="dataEdit"/>
    </div>
  </template>
  
  <script>
  import HeaderAdmin from '../Components/header.vue';
  import paginate from  '../Components/paginate.vue';
  import createToEdit from '../Components/modals/ingredients/createToEdit.vue';
  
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
      if(!this.ingredients) this.getIngredients();
    },
    methods: {
      async getIngredients(val = false){
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
  
          await this.$store.dispatch('ingredients/index', params);
          await this.$store.dispatch('aplication/getAllAplications');
        loader.hide();
      },
      // async removeProduct(ingredient){
      //   let loader = this.$loading.show({
      //     // Optional parameters
      //     container: this.$refs.formContainer,
      //     color: '#007bff',
      //     width: 80,
      //     height: 80,
      //     backgroundColor: '#000000',
      //     opacity: 0.8,
      //     zIndex: 999,
      //   });

      //   let request = await this.$store.dispatch('ingredients/remove', ingredient.id);
      //   loader.hide();
  
      //   if(request.success){
      //     this.$toastr.success(request.data, 'Exitoso');
      //     this.getIngredients();
      //   }else{
      //     this.$toastr.error(request.data, 'Error');
      //   }
      // },
      openProduct(ingredient){
        this.dataEdit = ingredient;
        this.modal = true;
      },
    },
    computed:{
      ingredients:{ get() { return this.$store.getters['ingredients/getIngredients'] } }
    },
  }
  </script>
  
  <style media="screen">
    .actions{
      width: 105px;
    }
  </style>
  