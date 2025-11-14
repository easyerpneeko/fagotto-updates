<template>
  <StackModal
  :show="showSubmodules"
  @close="$emit('closeModal', false)"
  :modal-class="{ [modalClass]: true }"
  v-if="dataSubmodule"
  >
      <div slot="modal-header">
        <div class="modal-header bg-one">
          <h5 class="modal-title">{{dataSubmodule.data.name}} {{dataSubmodule.data.version}}</h5>
          <a class="close" aria-label="Close" @click="$emit('closeModal', false)">
            <span aria-hidden="true">&times;</span>
          </a>
        </div>
      </div>

      <div class="col-12 p-0">
        <div class="card-widget widget-user-2 p-0 m-0">
          <div v-if="dataSubmodule.data.settings != ''" class="card-body p-0">
            <div v-for="(settings, index) in dataSubmodule.data.settings" :key="index" class="col-12 card-modules border-bottom">
              <div class="collapsed-card bg-grey card-modules">
                <div class="card-header p-2 pl-3">
                  <h6 class="card-title card-title-padding text-font-modules">{{settings.name}}</h6>
                  <div class="card-tools">
                    <a @click="active_desactive((!settings.active) ? 1 : 0, settings.relid)" class="btn btn-sm mx-2" :class="(!settings.active) ? 'bg-two' : 'bg-dark' ">
                      <i class="fas" :class="(!settings.active) ? 'fa-check' : 'fa-trash-alt' "></i>
                      <span class="mobile-modules">{{(!settings.active) ? 'Activar' : 'Desactivar' }}</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-if="dataSubmodule.data.settings == ''" class="card-body">
            <div class="text-center">
              <a class="btn-add-module p-5" href="#">
                <h4 class="text-add-module">No existen ajustes para este submodulo</h4>
              </a>
            </div>
          </div>
        </div>
      </div>

      <div slot="modal-footer">
        <div class="modal-footer">
          <a @click="actualization(dataSubmodule.data.relid)" class="btn bg-one text-white">Actualizar submodulo</a>
        </div>
      </div>
    </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
export default {
  name: 'detailsModules',
  data(){
    return{
      modalClass: '',
      sub: null,
    }
  },
  mounted(){
  },
  components:{
    StackModal
  },
  props:[
    'showSubmodules',
    'dataSubmodule'
  ],
  methods:{
    // Actualizar submodulos
    async actualization(id){
      let loader = this.$loading.show({
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        var request = await this.$store.dispatch('submodules/actualizationsubmodule', id);
        if(!request.success){
          this.$toastr.error(request.data, 'Error');
        }else{
          this.$emit('closeModal',false, this.dataSubmodule.index);
          this.$toastr.success(request.data, 'Exitoso');
        }
      loader.hide();
    },
    // activar y desactivar ajuste de submodulos
    async active_desactive(value, id){
      let loader = this.$loading.show({
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        var thing = new FormData();
        thing.append('value', value);

        var request = await this.$store.dispatch('submodules/activeDesactiveSetting', {data: thing, id});
        if(!request.success){
          this.$toastr.error(request.data, 'Error');
        }else{
          this.$emit('closeModal',false, this.dataSubmodule.index);
          this.$toastr.success(request.data, 'Exitoso');
        }
      loader.hide();
    },
  },
}
</script>
