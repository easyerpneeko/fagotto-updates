<template>
  <div v-if="clients" class="container-fluid">

    <HeaderAdmin :title="'Clientes'" />
    <div class="pb-3">
      <label for="rutcliente1">Buscar por rut del cliente</label>
      <input id="rutcliente1" v-model="rutClient" type="text" placeholder="Rut" class="form-control" @keyup="getclientRut" />
    </div>
    <div class="row">
      <div v-for="(client, index) in clients.items"  :key="index" class="col-xl-3 col-md-4 col-sm-6 col-12">
        <div class="card card-widget widget-user">

          <div :class="['widget-user-header',(client.sexo == 'male') ? 'bg-one' : 'bg-two']">
            <h3 class="widget-user-username">{{ client.username }}</h3>
            <h5 class="widget-user-desc">{{ client.rut }}</h5>
          </div>
          <div class="widget-user-image">
            <img class="img-circle elevation-2" :src="getImage(client.sexo)" alt="User Avatar">
          </div>

          <div class="card-footer">
            <div class="row">
              <div class="col-sm-12">
                <div class="description-block">
                  <h5 class="description-header">
                    {{client.numAplications}}
                  </h5>
                  <span class="description-text">Aplicaciones actuales</span>
                </div>
              </div>
            </div>
            <a @click="modalOpen(client)" :class="['btn btn-block text-white',(client.sexo == 'male') ? 'bg-one' : 'bg-two']">
              Ver mas
            </a>
          </div>

        </div>
      </div>
      <div class=" col-12 ">
        <nav aria-label="Contacts Page Navigation">
          <!-- Paginacion -->
          <paginate
            v-if="clients.pages > 1"
            :back="clients.paginate.back"
            :next="clients.paginate.next"
            :activePage="clients.page"
            :totalPages="clients.pages"
            @refreshData="getclients"
          />
        </nav>
      </div>
    </div>
    <modalDetails :dataDetails="dataDetails" :showModal="modal" @changeState="changeState"/>
  </div>
</template>

<script>
import paginate from  '../Components/paginate.vue';
import HeaderAdmin from '../Components/header.vue';
import modalDetails from '../Components/modals/clients/detailsClient.vue';
import BaseUrl from "../../assets/helpers/BaseUrl";

export default {
  data(){
    return{
      dataDetails: '',
      modal: false,
      rutClient: null,
    }
  },
  components:{
    HeaderAdmin,
    modalDetails,
    paginate
  },
  mounted(){
    this.getclients();
  },
  methods:{
    async getclients(page){
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
      if(page) var filterdata = '?page='+page;
      await this.$store.dispatch('clients/getclients', filterdata);
      loader.hide();
    },
    changeState(val){
      this.modal = val;
    },
    async getclientRut(){
      await this.$store.dispatch('clients/getclientRut', this.rutClient);
    },
    getImage(val){
      if(val == "male")
        return BaseUrl.getUrl('images/male.jpg');
      else
        return BaseUrl.getUrl('images/female.jpg');
    },
    async modalOpen(data){
      let loader = this.$loading.show({
        // Optional parameters
        container: this.fullPage ? null : this.$refs.formContainer,
        canCancel: true,
        onCancel: this.onCancel,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 999,
      });
        var request = await this.$store.dispatch('clients/getClientAplicacions', data.id);
        this.dataDetails = {
          aplication: request.data,
          client: data,
        }
        this.modal = true;
      loader.hide();
    },
    openModal(val){
        this.modal = val;
    }
  },
  computed:{
    clients:{
      get() {
        return this.$store.getters['clients/getterClients'];
      }
    }
  },
}
</script>
