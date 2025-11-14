<template>
  <div class="card card-widget widget-user-2 m-0">
    <div class="card-header bg-dark">
      <h3 class="card-title card-title-padding">Módulos actuales</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div v-if="app.Modules && app.Modules.length > 0" class="card-body p-0">
      <div v-for="(moduleApp, index) in app.Modules" :key="index" class="col-12 card-modules">
        <div class="card bg-grey card-modules">
          <div class="card-header p-2 pl-3">
            <h6 class="card-title card-title-padding text-font-modules">{{moduleApp.name}} {{moduleApp.version}}</h6>
            <div class="card-tools">
              <a v-if="moduleApp.sub != ''" class="btn btn-sm mx-2 bg-gris" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </a>
            </div>
            <div class="card-tools">
              <a @click="openDetail(index)" class="btn btn-sm mx-2 bg-two"><i class="fas fa-cog"></i>
              </a>
            </div>
            <div class="card-tools">
              <a @click="migrate(moduleApp.relid, index)" class="btn btn-sm mx-2 bg-one">
                <i class="fas fa-share-square"></i>
                <span class="mobile-modules">Migrar</span>
              </a>
            </div>
            <div class="card-tools">
              <a @click="unistallModule(moduleApp.relid)" class="btn btn-sm mx-2 bg-dark">
                <i class="fas fa-trash-alt"></i>
                <span class="mobile-modules">Desintalar</span>
              </a>
            </div>
          </div>
          <div v-if="moduleApp.sub" v-for="(subModules, index) in moduleApp.sub" :key="index" class="card-body p-2">
            <h6 class="m-0 text-font-submodules">{{subModules.name}}</h6>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="card-body">
      <div class="text-center">
        <a class="btn-add-module p-5" href="#">
          <h3 class="text-add-module">No existen módulos instalados</h3>
        </a>
      </div>
    </div>

    <verify-migrate :showCheck="modalVerify" @closeModal="closeModal" :dataId="dataId" />
    <details-module-app :showDetails="detailsModule" @closeModals="closeModal" @refreshData="refreshApp" v-if="dataModule" :dataModule="dataModule" />
  </div>
</template>

<script>
import verifyMigrate from '../modals/migrations/verifyMigrate.vue';
import detailsModuleApp from '../modals/modules/detailsModuleApp.vue';

export default {
  data(){
    return{
      detailsModule: false,
      modalVerify: false,
      dataId: null,
      dataModule: null
    }
  },
  components:{
    verifyMigrate,
    detailsModuleApp
  },
  methods: {
    // Migrar modulo
    async migrate(id, index){
        this.dataId = {
          name: 'modules',
          id,
          index
        }
        this.modalVerify = true;
    },

    // Desintalar modulo
    async unistallModule(id){
      let loader = this.$loading.show({
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        var request = await this.$store.dispatch('modules/deleteModule', id);

        if(!request.success) this.$toastr.error(request.data, 'Error');
        else this.$toastr.success(request.data, 'Exitoso');

        this.refreshApp();
      loader.hide();
    },

    // Refrescar app
    async refreshApp(index = null){
      this.$emit('refreshApp', false);
      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/getAplication', this.app.Id);

      // Verificando datos
      if(!request.success){
        this.$router.push('/admin/inicio');
        this.$toastr.error(request.data, 'Error');
      }

      if (index !== null){
        this.dataModule = {
          module: this.app.Modules[index],
          idApp: this.app.Id,
          index
        };
      }
    },

    // Abriendo modal
    async openDetail(index){
      await this.refreshApp(index);
      this.detailsModule = true;
    },

    // Cerrar modal
    closeModal(val, index = false){
      this.modalVerify = val;
      this.detailsModule = val;
      if(index !== false) this.refreshApp(index);
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  }
}
</script>

<style lang="scss" scoped>
// Styles addmodule
.card-title{
  font-size: 1.5rem;
}
.info-box-text{
  text-overflow: none;
  white-space: normal;
}
.progress-description{
  text-overflow: none;
  white-space: normal;
}
</style>
