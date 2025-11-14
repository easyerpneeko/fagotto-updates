<template>
  <div v-if="feeds" class="container-fluid">
    <HeaderAdmin :title="'Noticias'" />

    <!-- <div class="pb-3">
      <label for="rutcliente">Buscar</label>
      <input id="rutcliente" type="text" placeholder="id" class="form-control" />
    </div> -->

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Noticias</h3>
        <div class="card-tools d-flex flex-wrap">
          <div class="mr-1">
            <a @click="getFeeds" class="btn btn-block bg-two text-white btn-font-r">
              <i class="fas fa-sync-alt"></i>
              <span class="mobile-modules">Refrescar</span>
            </a>
          </div>
          <div class="ml-1">
            <a @click="openFeed(false)" class="btn btn-block bg-one text-white btn-font-r">
              <i class="fas fa-plus"></i>
              <span class="mobile-modules">Crear noticia</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <table class="table table-striped projects">
          <thead>
            <tr>
              <th class="display-none">Titulo</th>
              <th class="text-center">Descripción</th>
              <th class="display-none text-center">Fecha</th>
              <th class="actionsFeeds"></th>
            </tr>
          </thead>
          <tbody v-for="(feed, index) in feeds.items"  :key="index">
            <tr>
              <td class="display-none">
                {{ (feed.title) ? feed.title : 'Sin titulo' }}
              </td>
              <td class="descFeed">
                {{feed.desc}}
              </td>
              <td class="display-none text-center">
                {{feed.created_at}}
              </td>
              <td class="text-right">
                <a @click="openFeed(feed)" class="btn bg-two btn-sm" href="#">
                  <i class="fas fa-edit"></i>
                </a>
                <a @click="removeFeed(feed)" class="btn bg-one btn-sm" href="#">
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
          v-if="feeds.items.length > 1"
          :back="feeds.paginate.back"
          :next="feeds.paginate.next"
          :activePage="feeds.page"
          :totalPages="feeds.pages"
          @refreshData="getFeeds"
        />
      </nav>
    </div>

    <create-to-edit v-model="modal" @refresh="getFeeds" :dataEdit="dataEdit"/>
  </div>
</template>

<script>
import HeaderAdmin from '../Components/header.vue';
import paginate from  '../Components/paginate.vue';
import createToEdit from '../Components/modals/feeds/createToEdit.vue';

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
    if(!this.feeds) this.getFeeds();
  },
  methods: {
    async getFeeds(val = false){
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

        await this.$store.dispatch('feeds/index', params);
        await this.$store.dispatch('aplication/getAllAplications');
      loader.hide();
    },
    async removeFeed(feed){
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
        let request = await this.$store.dispatch('feeds/remove', feed.id);
      loader.hide();

      if(request.success){
        this.$toastr.success(request.data, 'Exitoso');
        this.getFeeds();
      }else{
        this.$toastr.error(request.data, 'Error');
      }
    },
    openFeed(feed){
      this.dataEdit = feed;
      this.modal = true;
    },
  },
  computed:{
    feeds:{ get() { return this.$store.getters['feeds/getFeeds'] } }
  },
}
</script>

<style media="screen">
  .actionsFeeds{
    width: 105px;
  }
</style>
