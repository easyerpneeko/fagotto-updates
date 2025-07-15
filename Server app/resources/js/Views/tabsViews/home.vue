<template>
  <div class="">
    <!-- Servicio activo -->
    <div v-if="app" class="row m-0">
      <div class="col-md-4 col-sm-6 col-12 px-2 mb-2">
        <a @click="installDefaultModules" class="package-btn">
          <div><i class="fas fa-star package-btn-icon"></i></div>
          <div>Paquetes Iniciales</div>
        </a>
      </div>
      <div class="col-md-8 col-12 px-2 mb-2">
        <expired />
      </div>
      <div class="col-md-4 col-sm-6 col-12 px-2 mb-2 btn-users">
        <div class="w-100 mb-1">
          <a @click="modalEnvs = true" class="package-btn package-two">
            <div><i class="fas fa-code package-btn-icon"></i></div>
            <div>Variables de entorno</div>
          </a>
        </div>
        <div class="w-100 mt-1">
          <a @click="checkService()" class="package-btn package-dark">
            <div><i :class="['fas package-btn-icon',(app.Active) ? 'fa-cut' : 'fa-check']"></i></div>
            <div>{{ (app.Active) ? 'Cortar servicio' : 'Activar servicio' }}</div>
          </a>
        </div>
      </div>
      <div class="col-md-6 col-12 px-2 mb-2">
        <edit-expired />
      </div>
    </div>

    <modal-envs :showEnvs="modalEnvs" @closeModals="closeModals"/>
    <modal-check :showCheck="modal" @closeModals="closeModals"/>
  </div>
</template>

<script>
import expired from '../../Components/cards/expired.vue';
import editExpired from '../../Components/cards/editExpired.vue';
import modalEnvs from '../../Components/modals/app/modalEnvs.vue';
import modalCheck from '../../Components/modals/app/modalCheck.vue';

export default {
  data(){
    return{
      modalEnvs: false,
      modal: false
    }
  },
  components:{
    expired,
    editExpired,
    modalCheck,
    modalEnvs
  },
  methods:{
    // Refrescar app
    async refreshApp(){
      console.log("--- Refrescando la app ---");
      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/getAplication', this.app.Id);

      // Verificando datos
      if(!request.success){
        this.$router.push('/admin/inicio');
        this.$toastr.error(request.data, 'Error');
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
      await this.refreshApp();

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
            this.refreshApp();
          }
        loader.hide();
      }else{
        this.modal = true;
      }
    },

    // Control de modals al cerrar
    closeModals(val = false){
      this.modalEnvs = false;
      this.modal = false;
      if(val) this.refreshApp();
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>

<style lang="scss">
@media (max-width: 768px){
  .btn-users{
    order: -1;
  }
}
// Styles home
.package-btn{
  width: 100%;
  height: 100%;
  border-radius: 5px;
  border: 3px solid #0B4F6C;
  text-align: center;
  padding: 15px 10px;
  color: #0B4F6C !important;
  background-color: transparent;
  transition: .4s all ease;
  cursor: pointer;
  font-size: 25px;
  // flex
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  &-icon{
    font-size: 45px !important;
  }
}
.package-btn:hover{
  background-color: #0B4F6C;
  border: 3px solid #0B4F6C;
  color: #fff !important;
}
.package-two{
  background-color: #0B4F6C !important;
  border: 3px solid #0B4F6C !important;
  color: #fff !important;
}
.package-two:hover{
  background: transparent !important;
  border: 3px solid #0B4F6C !important;
  color: #0B4F6C !important;
}


.package-dark{
  background-color: #292F36 !important;
  border: 3px solid #292F36 !important;
  color: #fff !important;
}
.package-dark:hover{
  background: transparent !important;
  border: 3px solid #292F36 !important;
  color: #292F36 !important;
}
</style>
