<template>
  <div v-if="aplications" class="container-fluid">
    <HeaderAdmin :title="'Aplicacion'" />

    <div class="pb-3">
      <label for="rutcliente">Buscar por rut del cliente</label>
      <input id="rutcliente" v-model="rutClient" type="text" placeholder="Rut" class="form-control" @keyup="getAplicationRut" />
    </div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Aplicaciones</h3>
        <div class="card-tools">
          <a @click="modalOpen()" class="btn btn-block bg-one text-white btn-font-r">
            <i class="fas fa-plus"></i>
            <span class="mobile-modules">Crear aplicación</span>
          </a>
        </div>
      </div>
      <div class="card-body p-0">
        <table class="table table-striped projects">
          <thead>
            <tr>
              <th class="width-name">
                Aplicacion
              </th>
              <th class="display-none width-username">
                Cliente
              </th>
              <th class="display-none text-center width-app">
                Estado
              </th>
              <th class="display-none text-center width-app">
                Expiracion
              </th>
              <th class="width-view-more text-right">

              </th>
            </tr>
          </thead>
          <tbody v-for="(aplication, index) in aplications.items"  :key="index">
            <tr>
              <td class="width-name">
                <a>
                  {{ aplication.name }}
                </a>
              </td>
              <td class="display-none">
                <ul class="list-inline">
                  <li class="list-inline-item ">
                    {{ aplication.username }}
                    <!-- Adriana Vargas -->
                  </li>
                </ul>
              </td>
              <td class="project-state display-none">
                <span v-if="aplication.active" class="badge badge-success bg-one">Activa</span>
                <span v-if="!aplication.active" class="badge badge-danger bg-dark">Cortada</span>
              </td>
              <td class="project-state display-none">
                <span class="badge" :class="(new Date(aplication.expiration) < new Date || aplication.expiration == null) ? 'badge-danger bg-dark' : 'badge-success bg-one'">
                  {{ (new Date(aplication.expiration) < new Date || aplication.expiration == null) ? 'Vencida' : 'Activada' }}
                </span>
              </td>
              <td class="project-actions text-right width-view-more">
                <a @click="openModalDetails(aplication.id)" class="btn bg-two btn-sm" href="#">
                  <i class="fas fa-eye"></i>
                  <span class="mobile-modules">Ver mas</span>
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
          v-if="aplications.pages > 1"
          :back="aplications.paginate.back"
          :next="aplications.paginate.next"
          :activePage="aplications.page"
          :totalPages="aplications.pages"
          @refreshData="getAplications"
        />
      </nav>
    </div>

    <modalDetailsApp :showDetails="modalDetails" :dataEdit="dataEdit" @changeState="changeStateApps"/>
    <modalCreateApp :showModal="modal" @changeState="changeState"/>
  </div>
</template>

<script>
import paginate from  '../Components/paginate.vue';
import HeaderAdmin from '../Components/header.vue';
import modalCreateApp from '../Components/modals/app/createApp.vue';
import modalDetailsApp from '../Components/modals/app/detailsApp.vue';

export default {
  data(){
    return{
      modal: false,
      modalDetails: false,
      dataEdit: '',
      rutClient: null,
      pageActual: 1
    }
  },
  components:{
    HeaderAdmin,
    modalCreateApp,
    modalDetailsApp,
    paginate
  },

  mounted(){
    this.getAplications();
  },
  methods: {
    changeState(val){
      this.modal = false;
      if(val) this.getAplications(false);
    },
    changeStateApps(val){
      this.modalDetails = false;
      this.getAplications(false);
    },
    modalOpen(){
      this.modal = true;
    },
    async getAplicationRut(){
      await this.$store.dispatch('aplication/getAplicationRut', this.rutClient);
    },
    async getAplications(val = false){
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
        if(val){
          this.pageActual = val;
        }
        var filterdata = '?page=' + this.pageActual;
        await this.$store.dispatch('aplication/getAplications', filterdata);
      loader.hide();
    },
    openModalDetails(id){
      this.dataEdit = id;
      this.modalDetails = true;
    },
  },
  computed:{
    aplications:{
      get() {
        return this.$store.getters['aplication/getterAplications'];
      }
    }
  },
}
</script>
