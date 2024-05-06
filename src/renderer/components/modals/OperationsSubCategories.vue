<template>
  <div class="modal fade" id="subCategoriesModal" tabindex="-1" role="dialog" aria-labelledby="subCategoriesModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Subcategorias de Operaciones</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <!-- <span class="ml-4">Ahora las categorias se pueden ocultar</span> -->
      <div class="modal-body">
        <div class="d-flex flex-column px-2">
          <h6 class="font-weight-bold">Lista de Subcategorias</h6>
          <ul class="list-group mt-2 categories-list">
            <li class="list-group-item d-flex justify-content-between" v-for="subcategory in subcategories">
              <span class="pt-2 text-capitalize">{{subcategory.name}}</span>
              <div class="btn-group">
                <button type="button" class="btn bg-danger text-white px-1 py-1 text-sm q-btn-sm" :disabled="waitResponse" @click="deleteSubcategory(subcategory.id)">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn bg-secundario text-white" data-dismiss="modal" :disabled="waitResponse">Cerrar</button>
        <button type="button" class="btn bg-primario text-white" :disabled="waitResponse" data-toggle="modal" data-target="#subcategoriesCrudModal">Crear</button>
      </div>
    </div>
  </div>
  <div class="modal fade" id="subcategoriesCrudModal" tabindex="-1" role="dialog" aria-labelledby="subcategoriesCrudModal"
      aria-hidden="true" data-backdrop="false">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Subcategorias</h5>
            <button @click="closeModal" :disabled="waitResponse" type="button" class="close" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="d-flex flex-column px-2 mb-2">
              <h6 class="font-weight-bold">Añadir Subcategoria</h6>
              <div class="form-group my-1">
                <input type="text" class="form-control" placeholder="Nombre" :disabled="waitResponse" v-model="name"
                  @keyup.enter="newSubcategory">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn bg-secundario text-white" @click="closeModal"
              :disabled="waitResponse">Cerrar</button>
            <button type="button" class="btn bg-primario px-2 align-self-end" :disabled="waitResponse"
              @click="newSubcategory">Añadir</button>
          </div>
        </div>
      </div>
    </div>
  <!-- <categoriesCrud @refresh="refreshData"/> -->
</div>
</template>

<script>
import $ from 'jquery';
// import categoriesCrud from '@/components/modals/crudCategories.vue';
import Loader from '@/helpers/Loader';
export default {
  components:{
    // categoriesCrud
  },
  data(){
    return{
      waitResponse:false,
      name:''
    }
  },
  mounted(){
    // this.refreshData();
  },
  methods:{
    closeModal() {
      $('#subcategoriesCrudModal').modal('hide');
    },
    async refreshData(){
      this.waitResponse = true;
      await this.$store.dispatch("operations/getSubcategories");
      this.waitResponse = false;
    },
    async deleteSubcategory(id){
      this.waitResponse = true;
      let request = await this.$store.dispatch("operations/removeSubcategory",id);
      console.log(request);
      if (request.success) {
        this.$awn.success('Subcategoria Removida Exitosamente',{labels:{success:'CORRECTO'}});
        this.refreshData();
      }else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
    },
    async newSubcategory(){
      let fields = ['name'];
      let fd = new FormData();
      for (var field of fields) {
        if (!this[field] || this[field] == ' ') {
          this.$awn.alert('Debes llenar todos los campos');
          return;
        }
        fd.append(field, this[field]);
      }
      this.waitResponse = true;
      Loader.fullPage();
      var request = await this.$store.dispatch("operations/newSubcategory", fd);
      console.log(request);
      if (request.success) {
        this.$awn.success('Subcategoria Creada Exitosamente', { labels: { success: 'CORRECTO' } });
        for (var field of fields) {
          this[field] = '';
        }
        $('#subcategoriesCrudModal').modal('hide');
        this.refreshData();
      } else {
        console.log(request.data);
        let allErrors = request.data;
        if (typeof (allErrors) == 'object') {
          for (var errorkey in allErrors) {
            if (allErrors[errorkey]) {
              for (var error of allErrors[errorkey]) {
                this.$awn.alert(error);
              }
            }
          }
        } else {
          this.$awn.alert(allErrors);
        }
      }
      this.waitResponse = false;
      Loader.hide();
    }
  },
  computed:{
    subcategories:{
      get() {
        return this.$store.getters['operations/subcategories'];
      }
    },
  },
}
</script>
