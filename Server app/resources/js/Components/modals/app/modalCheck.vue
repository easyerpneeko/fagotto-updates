<template>
<StackModal
    :show="showCheck"
    @close="$emit('closeModals', false)"
    :modal-class="{ [modalClass]: true }"
  >
    <div slot="modal-header">
      <div class="modal-header bg-one">
            <h3 class="modal-title">Corte de servicio</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="$emit('closeModals', false)">
            <span class="close-color" aria-hidden="true">&times;</span>
          </button>
      </div>
    </div>
    <div class="modal-body">
      <h5 class="text-mobile">
        <i class="fas fa-exclamation-triangle bg-primario"></i>
        Estas seguro de cortar el servicio de la aplicacion <span class="text-name">"{{app.Name}}"</span>
      </h5>
    </div>
    <div slot="modal-footer">
      <div class="modal-footer">
        <a @click="$emit('closeModals', false)" class="btn bg-dark text-white">Cancelar</a>
        <a @click="cutApp" class="btn bg-one text-white">Cortar servicio</a>
      </div>
    </div>
  </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';

export default {
  name: 'DetailsApp',
  data(){
    return{
      modalClass: 'modal-lg',
    }
  },
  components:{
    StackModal,
  },
  props:['showCheck',],
  methods:{
    async cutApp(){
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

        var request = await this.$store.dispatch('aplication/cutService', this.app.Id);
        if(request.success){
          this.$emit('closeModals', true);
          this.$toastr.success('Servicio cortado exitosamente', 'Exitoso');
        }else this.$toastr.error(request.data, 'Error');

      loader.hide();
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>

<style>
  .modal-body{
    padding: 10px !important;
  }
  .fa-exclamation-triangle{
    color: red;
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
