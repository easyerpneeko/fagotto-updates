<template>
  <div>
    <StackModal :show="showDetails" @close="$emit('changeState', false)" :modal-class="{ [modalClass]: true }" v-if="app">
      <div slot="modal-header">
        <div class="modal-header bg-one">
          <h3 class="modal-title">{{app.Name}}</h3>
          <button type="button" class="close p-3" data-dismiss="modal" aria-label="Close" @click="$emit('changeState', false)">
            <span class="close-color" aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>

      <div class="modal-body">
        <div class="row scrollApp">
          <div v-if="app.Active" class="col-md-6 col-12 mb-2">
            <expired />
          </div>

          <div v-if="app.Active" class="col-md-6 col-12 mb-2">
            <edit-expired />
          </div>

          <div v-if="!app.Active" class="col-12 mb-2">
            <div class="text-center">
              <a class="btn-add-module p-5" href="#">
                <h4 class="text-add-module p-4">El servicio se encuentra cortado</h4>
              </a>
            </div>
          </div>

          <div class="col-12">
            <card-module />
          </div>

          <div class="col-md-5 col-12 pt-2">
            <add-module @refreshApp="refreshData" />
          </div>
        </div>
      </div>

      <div slot="modal-footer">
        <div class="modal-footer">
          <!-- Boton de paquetes iniciales -->
          <a @click="installDefaultModules" class="btn bg-dark text-white">Instalar paquetes iniciales</a>

          <!-- Controlar corte y activacion de servicio -->
          <a v-if="app.Active" class="btn bg-one text-white" style="float:right;" @click="checkService">
            Cortar servicio
          </a>
          <a v-else class="btn bg-one text-white" style="float:right;" @click="checkService">
            Activar servicio
          </a>

          <!-- Ver todos los detalles -->
          <a class="btn bg-two text-white" style="float:right;" @click="$router.push('/admin/aplicaciones/detalle?id='+dataEdit)">
            Ver más
          </a>
        </div>
      </div>
    </StackModal>

    <modalCheck :showCheck="modal" @closeModals="closeModals"/>
  </div>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import modalCheck from './modalCheck.vue';

import addModule from '../../cards/addModule.vue';
import cardModule from '../../cards/modules.vue';
import expired from '../../cards/expired.vue';
import editExpired from '../../cards/editExpired.vue';

export default {
  name: 'DetailsApp',
  data(){
    return{
      modalClass: 'modal-xl',
      modal: false
    }
  },
  components:{
    StackModal,
    modalCheck,
    addModule,
    cardModule,
    expired,
    editExpired,
  },
  async mounted(){
    await this.$store.dispatch('modules/getModulesOptions');
  },
  props:[
    'showDetails',
    'dataEdit'
  ],
  watch: {
   dataEdit: {
      handler(val) {
       if (val != null || val != false) {
         this.refreshData();
       }
      }
    },
  },
  methods:{
    // refrescamiento de datos
    async refreshData(type = false){
      let loader = this.$loading.show({
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        // if(this.dataEdit.pageNow) var filterdata = '?page=' + this.dataEdit.pageNow;
        // await this.$store.dispatch('aplication/getAplications', filterdata);

        var request = await this.$store.dispatch('aplication/getAplication', this.dataEdit);
        if(!request.success) {
          this.$toastr.error(request.data, 'Error');
          loader.hide();
          return false;
        }
        if(!type){
          await this.$store.dispatch('aplication/getUserApp', this.dataEdit);
          await this.$store.dispatch('aplication/getTypeUser', this.dataEdit);
        }

      loader.hide();
    },

    // modals de verificacion de datos
    async checkService(){
      if(!this.app.Active){
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
        var request = await this.$store.dispatch('aplication/activeService', this.app.Id);
        if(!request.success) this.$toastr.success(request.data, 'Error');
        else{
          this.$toastr.success(request.data, 'Exitoso');
          this.refreshData();
        }
        loader.hide();
      }else{
        this.modal = true;
      }
    },

    // Paquetes iniciales
    async installDefaultModules(){
      if(this.app.Modules.length > 0) return this.$toastr.info('La aplicacion ya contiene modulos instalados', 'Info');;

      let loader = this.$loading.show({
        // Optional parameters
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });

      //Instalamos el modulo de productos y venta
      for (var m = 1; m <= 2; m++) {
        var thing = new FormData();
        thing.append('module', m);

        var request = await this.$store.dispatch('modules/addModule', {data: thing, id:this.app.Id});
        if(!request.success){
          loader.hide();
          return this.$toastr.error(request.data, 'Error');
        }
      }

      // Actualizamos la aplicacion
      await this.refreshData();

      // Activamos las settings exceptuando la cantidad minima
      for (var i = 0; i < this.app.Modules.length; i++) {
        for (var setting of this.app.Modules[i].settings) {
          if (setting) {
            if (setting.key != 'permitir_cantidad_minima' || setting.key != 'productos_tabla') {
              var fd = new FormData();
              fd.append('value', 1);
              var request = await this.$store.dispatch('modules/activeDesactiveSetting', {data: fd, id:setting.relid});
              if(!request.success){
                loader.hide();
                this.$toastr.error(request.data, 'Error');
                return;
              }
            }
          }
        }
      }

      //Añadimos el submodulo de clientes en ventas
      var thing = new FormData();
      thing.append('submodule', 2);
      var request = await this.$store.dispatch('submodules/addSubmodule', {data: thing, id:this.app.Id});
      if(!request.success){
        loader.hide();
        return this.$toastr.error(request.data, 'Error');
      }
      loader.hide();
      this.$toastr.success('Paquete inicial instalado correctamente', 'Exito!');
    },

    // manejo de modals (close)
    closeModals(val){
      this.modal = false;
      if(val) this.refreshData();
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>

<style scoped>
  .close-color{
    color: #ffffff;
  }
  .modal-body{
    padding: 10px !important;
  }
  @media (max-width: 575px) {
    .modal-body {
      padding: 0px !important;
    }
  }
  @media (max-width: 400px) {
    .modal-title {
      font-size: 18px !important;
    }
  }
</style>
