<template>
  <div class="modal fade" id="categoriesModal" tabindex="-1" role="dialog" aria-labelledby="categoriesModal"
    aria-hidden="true">
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
                <span class="pt-2 text-capitalize">{{ category.name }}</span>
                <div class="btn-group">
                  <button type="button" class="btn bg-info text-white px-1 py-1 text-sm q-btn-sm" data-toggle="modal"
                    data-target="#categoriesCrudModal" :disabled="waitResponse" @click="editCategory(category)">
                    <i class="fa fa-pencil-alt"></i>
                  </button>
                  <button type="button" class="btn bg-danger text-white px-1 py-1 text-sm q-btn-sm"
                    :disabled="waitResponse" @click="deleteCategory(category.id)">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn bg-secundario text-white" data-dismiss="modal"
            :disabled="waitResponse">Cerrar</button>
          <button type="button" class="btn bg-primario text-white" :disabled="waitResponse" data-toggle="modal"
            data-target="#categoriesCrudModal" @click="selectCategory">Crear</button>
        </div>
      </div>
    </div>

    <div class="modal fade" id="categoriesCrudModal" tabindex="-1" role="dialog" aria-labelledby="categoriesCrudModal"
      aria-hidden="true" data-backdrop="false">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Categorias</h5>
            <button @click="closeModal" :disabled="waitResponse" type="button" class="close" aria-label="Close">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="d-flex flex-column px-2 mb-2">
              <h5 class="modal-title">{{ (!this.isEdit) ? 'Nueva Categoria' : 'Editar Categoria' }}</h5>
              <div class="form-group my-1">
                <input type="text" class="form-control" placeholder="Nombre" :disabled="waitResponse" v-model="name"
                  @keyup.enter="newCategory">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn bg-secundario text-white" @click="closeModal"
              :disabled="waitResponse">Cerrar</button>
            <template>
              <button v-if="this.isEdit" type="button" class="btn bg-primario px-2 align-self-end"
                :disabled="waitResponse" @click="newCategory()">Editar</button>

              <button v-else type="button" class="btn bg-primario px-2 align-self-end" :disabled="waitResponse"
                @click="newCategory()">Crear</button>
            </template>

          </div>
        </div>
      </div>
    </div>
    <!-- <categoriesCrud @refresh="refreshData" /> -->
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
      $('#categoriesCrudModal').modal('hide');
    },
    async refreshData() {
      this.waitResponse = true;
      await this.$store.dispatch("operations/getCategories");
      this.waitResponse = false;
    },
    async deleteCategory(id) {
      this.waitResponse = true;
      let request = await this.$store.dispatch("operations/removeCategory", id);
      console.log(request);
      if (request.success) {
        this.$awn.success('Categoria Removida Exitosamente', { labels: { success: 'CORRECTO' } });
        this.refreshData();
      } else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
    },
    async newCategory() {
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
      if (this.isEdit) {
        console.log('Ediat', this.category);
        request = await this.$store.dispatch('operations/editCategory', { id: this.category.id, data: fd });
      } else {
        var request = await this.$store.dispatch("operations/newCategory", fd);
      }
      if (request.success) {
        if (this.isEdit) {
          this.$awn.success('Categoria Creada Exitosamente', { labels: { success: 'CORRECTO' } });
        } else {
          this.$awn.success('Categoria Modificada Exitosamente', { labels: { success: 'CORRECTO' } });
        }
        for (var field of fields) {
          this[field] = '';
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
    async editCategory(category) {
      if (category) {
        this.isEdit = true;
      }
      this.name = category.name;
      this.id = category.id;
      this.category = category;
      $('#categoriesCrudModal').modal('show');
    },
    selectCategory() {
      this.isEdit = false;
      this.name = '';
      this.id = 0;
      // this.$emit('closeEdit');
    },
  },
  computed: {
    categories: {
      get() {
        return this.$store.getters['operations/categories'];
      }
    },
  },
}
</script>
