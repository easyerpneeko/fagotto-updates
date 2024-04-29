<template>
  <div class="modal fade" id="categoriesModal" tabindex="-1" role="dialog" aria-labelledby="categoriesModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Categorias de Operaciones</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <!-- <span class="ml-4">Ahora las categorias se pueden ocultar</span> -->
      <div class="modal-body">
        <div class="d-flex flex-column px-2">
          <h6 class="font-weight-bold">Lista de Categorias</h6>
          <ul class="list-group mt-2 categories-list">
            <li class="list-group-item d-flex justify-content-between" v-for="category in categories">
              <span class="pt-2 text-capitalize">{{category.name}}</span>
              <div class="btn-group">
                <!-- <button type="button" class="btn bg-secundario text-white px-1 py-1 text-sm" :disabled="waitResponse">
                  <i class="far fa-eye"></i>
                </button> -->
                <button type="button" class="btn bg-danger text-white px-1 py-1 text-sm q-btn-sm" :disabled="waitResponse" @click="deleteCategory(category.id)">
                  <i class="fa fa-trash"></i>
                </button>
                <button v-if="category.status == 1" type="button" class="btn bg-light text-black px-1 py-1 text-sm q-btn-sm" :disabled="waitResponse" @click="showCategory(category.id)">
                  <i class="far fa-square"></i>
                </button>
                <button v-else type="button" class="btn bg-light px-1 py-1 text-sm q-btn-sm" :disabled="waitResponse" @click="hideCategory(category.id)">
                  <i class="far fa-check-square" style="color: #6ccff6;"></i>
                </button>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn bg-secundario text-white" data-dismiss="modal" :disabled="waitResponse">Cerrar</button>
        <button type="button" class="btn bg-primario text-white" :disabled="waitResponse" data-toggle="modal" data-target="#categoriesCrudModal">Crear</button>
      </div>
    </div>
  </div>
  <categoriesCrud @refresh="refreshData"/>
</div>
</template>

<script>
import $ from 'jquery';
import categoriesCrud from '@/components/modals/crudCategories.vue';
import Loader from '@/helpers/Loader';
export default {
  components:{
    categoriesCrud
  },
  data(){
    return{
      waitResponse:false,
    }
  },
  mounted(){
    this.refreshData();
  },
  methods:{
    async refreshData(){
      this.waitResponse = true;
      await this.$store.dispatch("products/getCategories");
      this.waitResponse = false;
    },
    async deleteCategory(id){
      this.waitResponse = true;
      let request = await this.$store.dispatch("products/deleteCategory",id);
      console.log(request);
      if (request.success) {
        this.$awn.success('Categoria Removida Exitosamente',{labels:{success:'CORRECTO'}});
        this.refreshData();
      }else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
    },
    async hideCategory(id){
      this.waitResponse = true;
      Loader.fullPage();
      let request = await this.$store.dispatch("products/hideCategory",id);
      console.log(request);
      if (request.success) {
        this.$awn.success('Categoria Ocultada Exitosamente',{labels:{success:'CORRECTO'}});
        this.refreshData();
        $('#categoriesModal').modal('hide');
        this.$emit('refresh');
      }else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
      Loader.hide();
    },
    async showCategory(id){
      this.waitResponse = true;
      Loader.fullPage();
      let request = await this.$store.dispatch("products/showCategory",id);
      console.log(request);
      if (request.success) {
        this.$awn.success('Categoria activada Exitosamente',{labels:{success:'CORRECTO'}});
        this.refreshData();
        $('#categoriesModal').modal('hide');
        this.$emit('refresh');
      }else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
      Loader.hide();
    },
    // async newCategory(){
    //   let fields = ['name'];
    //   let fd = new FormData();
    //   for (var field of fields) {
    //     if (!this[field] || this[field] == ' '){
    //       this.$awn.alert('Debes llenar todos los campos');
    //       return;
    //     }
    //     fd.append(field, this[field]);
    //   }
    //   this.waitResponse = true;
    //   var request = await this.$store.dispatch("products/newCategory",fd);
    //   console.log(request);
    //   if (request.success) {
    //     this.$awn.success('Categoria Creada Exitosamente',{labels:{success:'CORRECTO'}});
    //     for (var field of fields){
    //       this[field] = '';
    //     }
    //     this.refreshData();
    //   }else {
    //     console.log(request.data);
    //     let allErrors = request.data;
    //     if (typeof(allErrors) == 'object') {
    //       for (var errorkey in allErrors) {
    //         if (allErrors[errorkey]){
    //           for (var error of allErrors[errorkey]) {
    //             this.$awn.alert(error);
    //           }
    //         }
    //       }
    //     }else{
    //       this.$awn.alert(allErrors);
    //     }
    //   }
    //   this.waitResponse = false;
    // }
  },
  computed:{
    categories:{
      get() {
        return this.$store.getters['products/categories'];
      }
    },
  },
}
</script>
