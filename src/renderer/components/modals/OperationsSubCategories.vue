<template>
  <div class="modal fade" id="subCategoriesModal" tabindex="-1" role="dialog" aria-labelledby="subCategoriesModal"
    aria-hidden="true">
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
          <div class="col-md-4 col-sm-6 col-md-12 p-2">
            <label for="Category">Categorias</label>
            <select class="form-control" v-model="category" @change="getSubcategoriesByCategory(category.id)"
              :disabled="disableCategory">
              <!-- <option :value="null" class="text-capitalize">Todas</option> -->
              <option :value="category" v-for="category in categories" :key="category.id" class="text-capitalize">
                {{ category.name }}</option>
            </select>
          </div>
          <div class="d-flex flex-column px-2">
            <h6 class="font-weight-bold">Lista de Subcategorias</h6>
            <ul class="list-group mt-2 categories-list" v-if="subcategoriesByCategory.length > 0">
              <li class="list-group-item d-flex justify-content-between" v-for="subcategory in subcategoriesByCategory">
                <span class="pt-2 text-capitalize">{{ subcategory.name }}</span>
                <div class="btn-group">
                  <button type="button" class="btn bg-info text-white px-1 py-1 text-sm q-btn-sm" data-toggle="modal"
                    data-target="#subcategoriesCrudModal" :disabled="waitResponse"
                    @click="editSubcategory(subcategory)">
                    <i class="fa fa-pencil-alt"></i>
                  </button>
                  <button type="button" class="btn bg-danger text-white px-1 py-1 text-sm q-btn-sm"
                    :disabled="waitResponse" @click="deleteSubcategory(subcategory.id)">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </li>
            </ul>
            <div ref="loaderOperation" v-else class="vld-parent px-2 mt-2">
              <div class="box-false d-flex flex-center text-center p-2 w-100">
                <h2>Selecciona una categoria</h2>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer" v-if="category != null">
          <button type="button" class="btn bg-secundario text-white" data-dismiss="modal"
            :disabled="waitResponse">Cerrar</button>
          <button type="button" class="btn bg-primario text-white" :disabled="waitResponse" data-toggle="modal"
            data-target="#subcategoriesCrudModal" @click="selectSubcategory">Crear Subcategoria</button>
        </div>
      </div>
    </div>
    <div class="modal fade" id="subcategoriesCrudModal" tabindex="-1" role="dialog"
      aria-labelledby="subcategoriesCrudModal" aria-hidden="true" data-backdrop="false">
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
              <h6 class="font-weight-bold">{{ (!this.isEdit) ? 'Añadir Subcategoria' : 'Editar Subcategoria' }}</h6>
              <div class="form-group my-1">
                <input type="text" class="form-control" placeholder="Nombre" :disabled="waitResponse" v-model="name"
                  @keyup.enter="newSubcategory">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn bg-secundario text-white" @click="closeModal"
              :disabled="waitResponse">Cerrar</button>
            <template>
              <button v-if="this.isEdit" type="button" class="btn bg-primario px-2 align-self-end"
                :disabled="waitResponse" @click="newSubcategory()">Editar</button>
              <button v-else type="button" class="btn bg-primario px-2 align-self-end" :disabled="waitResponse"
                @click="newSubcategory()">Crear</button>
            </template>
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
  components: {
    // categoriesCrud
  },
  data() {
    return {
      waitResponse: false,
      name: '',
      category_id: null,
      category: null,
      disableCategory: false,
      subcategoriesByCategory: [],
      id: 0,
      isEdit: false
    }
  },
  mounted() {
    // this.refreshData();
  },
  methods: {
    closeModal() {
      this.isEdit = false;
      $('#subcategoriesCrudModal').modal('hide');
    },
    async refreshData() {
      this.waitResponse = true;
      this.getSubcategoriesByCategory(this.category.id);
      this.waitResponse = false;
    },
    async deleteSubcategory(id) {
      this.waitResponse = true;
      let request = await this.$store.dispatch("operations/removeSubcategory", id);
      console.log(request);
      if (request.success) {
        this.$awn.success('Subcategoria Removida Exitosamente', { labels: { success: 'CORRECTO' } });
        this.refreshData();
      } else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
    },
    async newSubcategory() {
      let fd = new FormData();

      fd.append('name',this.name);
      fd.append('operations_categories_id',this.category.id);

      this.waitResponse = true;
      Loader.fullPage();
      if (this.isEdit) {
        var request = await this.$store.dispatch("operations/editSubcategory", { id: this.subcategory.id, data: fd });
      } else {
        var request = await this.$store.dispatch("operations/newSubcategory", fd);
      }
      if (request.success) {
        if (this.isEdit) {
          this.$awn.success('Subcategoria Modificada Exitosamente', { labels: { success: 'CORRECTO' } });
        } else {
          this.$awn.success('Subcategoria Creada Exitosamente', { labels: { success: 'CORRECTO' } });
        }
        this.closeModal();
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
    },
    async editSubcategory(subcategory) {
      if (subcategory) {
        this.isEdit = true;
      }
      this.name = subcategory.name;
      this.id = subcategory.id;
      this.subcategory = subcategory;
      $('#categoriesCrudModal').modal('show');
    },
    selectSubcategory() {
      this.isEdit = false;
      this.name = '';
      this.id = 0;
      // this.$emit('closeEdit');
    },
    async getSubcategoriesByCategory(id) {
      this.waitResponse = true;
      let request = await this.$store.dispatch("operations/getSubcategoriesByCategory", id);
      console.log(request);
      if (request.success) {
        // this.$awn.success('Subcategorias', { labels: { success: 'CORRECTO' } });
        // this.refreshData();
        this.subcategoriesByCategory = request.data;
      } else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
    }
  },
  computed: {
    // subcategories: {
    //   get() {
    //     return this.$store.getters['operations/subcategories'];
    //   }
    // },
    categories: {
      get() {
        return this.$store.getters['operations/categories'];
      }
    },
  },
}
</script>
