<template>
  <StackModal
  :show="showCheck"
  @close="$emit('closeModal', false)"
  :modal-class="{ [modalClass]: true }"
  >
    <div slot="modal-header">
      <div class="modal-header bg-one">
        <h3 class="modal-title">Ejecutar migración</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="$emit('closeModal', false)">
          <span class="close-color" aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
    <div class="modal-body">
      <h5 class="text-mobile">
        <i class="fas fa-exclamation-triangle"></i>
        Estas seguro que deseas realizar esta migracion
      </h5>
    </div>
    <div slot="modal-footer">
      <div class="modal-footer">
        <a @click="$emit('closeModal', false)" class="btn bg-dark text-white">Cancelar</a>
        <a @click="migrate" class="btn bg-one text-white">Migrar Submodulo</a>
      </div>
    </div>
  </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import Datepicker from 'vuejs-datepicker';
import moment from 'moment';

export default {
  name: 'createApp',
  data(){
    return{
      modalClass: '',
      name: null,
      username: null,
      rut: null,
      sexo: "",
      optionsSexo: [
        {label: 'Mujer', value: 'female'},
        {label: 'Hombre', value: 'male'},
      ],
      disable: false,
      keyDisabled: false,
      expiration: null,
    }
  },
  components:{
    StackModal,
    Datepicker
  },
  props:[
    'showCheck',
    'dataId'
  ],
  methods:{
    // Ejecutar migracion
    async migrate(){
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
        if(this.dataId.name == 'modules')
          var request = await this.$store.dispatch('aplication/executeMigrate', this.dataId.id);
        else
          var request = await this.$store.dispatch('aplication/executeMigrateSubmodule', this.dataId.id);

        if(!request.success){
          this.$toastr.error(request.data, 'Error');
        }else{
          this.$emit('closeModal', false, this.dataId.index);
          this.$toastr.success(request.data, 'Exitoso');
        }
      loader.hide();
    },
  },
}
</script>

<style>
  .modal-body{
    padding: 10px !important;
  }
  .fa-exclamation-triangle{
    color: #0B4F6C !important;
  }
  .text-name{
    text-transform: capitalize;
    font-weight: bold;
  }
  @media (max-width: 500px) {
    .text-mobile {
      font-size: 17px !important;
    }
  }
  @media (max-width: 400px) {
    .text-mobile {
      font-size: 15.5px !important;
    }
  }
</style>
