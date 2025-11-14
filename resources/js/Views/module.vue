<template>
  <div v-if="Modules" class="container-fluid">
    <HeaderAdmin :title="'Modulos'" />

    <div class="d-flex">
      <div class="row col-12 m-0 p-0">
        <div class="col-lg-4 col-sm-6 col-12" v-for="(Module, index) in Modules.items"  :key="index">
        <!-- small card -->
        <div class="small-box bg-one">
          <div class="inner pb-4 pt-1">
            <h3>{{ Module.name }}</h3>
          </div>
          <div class="icon">
            <i class="fas" :class="Module.icon"></i>
          </div>
          <a @click="modalOpen(Module)" href="#" class="small-box-footer">
            Ver mas <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      </div>
    </div>

    <!-- Paginacion -->
    <paginate
      v-if="Modules.pages > 1"
      :back="Modules.paginate.back"
      :next="Modules.paginate.next"
      :activePage="Modules.page"
      :totalPages="Modules.pages"
      @refreshData="getModules"
    />
    <modalDetailsModule :showModal="modal" @changeState="changeState" :dataShow="dataShow"/>
  </div>
</template>

<script>
import paginate from  '../Components/paginate.vue';
import HeaderAdmin from '../Components/header.vue';
import modalDetailsModule from '../Components/modals/modules/detailsModule';

export default {
  data(){
    return{
      modal: false,
      dataShow: null,
    }
  },
  components:{
    HeaderAdmin,
    modalDetailsModule,
    paginate
  },
  mounted(){
    this.getModules();
  },
  methods: {
    changeState(val){
      this.modal = val;
      // this.modalDetails = val;
    },
    async modalOpen(val){
      let loader = this.$loading.show({
        // Optional parameters
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 99999,
      });
        var request = await this.$store.dispatch('submodules/getSubmodules', val.id);
        if(!request.success){
          console.log(request);
        }else{
          this.dataShow = {
            module: val,
            submodules: request.data,
          }
          this.modal = true;
        }
      loader.hide();
    },
    async getModules(val){
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
      if(val) var filterdata = '?page='+val;
      await this.$store.dispatch('modules/getModules', filterdata);
      loader.hide();
    },
  },
  computed:{
    Modules:{
      get() {
        return this.$store.getters['modules/getterModules'];
      }
    }
  },
}
</script>
