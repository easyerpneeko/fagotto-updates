<template>
  <div class="">
    <StackModal
    :show="showRole"
    @close="$emit('closeModals', false)"
    :modal-class="{ [modalClass]: true }"
    >
      <div slot="modal-header">
        <div class="modal-header bg-one">
          <h5 class="modal-title">Roles de usuario</h5>
          <a class="close" aria-label="Close" @click="$emit('closeModals', false)">
            <span aria-hidden="true">&times;</span>
          </a>
        </div>
      </div>

      <div v-if="dataRoles" class="card mb-0 scroollH">
        <div class="card-body p-0">
          <table v-if="dataRoles" class="table table-striped">
            <thead>
              <tr>
                <th style="width: 10px">id</th>
                <th>Nombre</th>
                <th>Keyname</th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(role, index) in dataRoles" :key="index">
                <td>{{role.id}}</td>
                <td>{{role.name}}</td>
                <td>{{role.keyname}}</td>
                <td class="d-flex">
                  <button class="btn bg-dark btn-sm mx-1" name="button" @click="deleteRole(role.id)">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                  <button class="btn bg-two btn-sm mx-1" name="button" @click="openModal(role)">
                    <i class="fas fa-pencil-alt"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="!dataRoles">
        <h4 class="text-center">No existen roles creados</h4>
      </div>

      <div slot="modal-footer">
        <div class="modal-footer">
          <a @click="$emit('closeModals', false)" class="btn bg-dark">Cerrar</a>
          <a @click="openModal(false)" class="btn bg-one">Crear</a>
        </div>
      </div>
    </StackModal>
    <ModalCreate :showModal="modal" @closeModal="closeModal" :idApp="this.app.Id" :dataEdit="dataEdit"/>
  </div>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import ModalCreate from './CreateRoles.vue';
export default {
  name: 'Roles',
  data(){
    return{
      modalClass: 'pa-0',
      modal: false,
      dataEdit: false,
    }
  },
  components:{
    StackModal,
    ModalCreate
  },
  props:[ 'showRole' ],
  methods:{
    // Refrescar roles
    async refreshRoles(){
      let loader = this.$loading.show({
        // Optional parameters
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 999999,
      });

      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/getTypeUser', this.app.Id);
      if(!request.success) console.log(request.data);

      loader.hide();
    },

    // Modal para editar y crear roles
    openModal(val = false){
      this.dataEdit = val;
      this.modal = true;
    },

    // Eliminar rol
    async deleteRole(idRole) {
      let loader = this.$loading.show({
       // Optional parameters
       color: '#007bff',
       width: 80,
       height: 80,
       backgroundColor: '#000000',
       opacity: 0.8,
       zIndex: 999999,
     });
      var request = await this.$store.dispatch('aplication/trashTypeUser', { idRole, id: this.app.Id });

      if(!request.success) this.$toastr.error(request.data, 'Error');
      else{
        this.$toastr.success('Rol eliminado con exito', 'Exitoso');
        this.refreshRoles();
      }

      loader.hide();
    },

    // Close modal
    closeModal(val = false){
      this.modal = false;
      if(val) this.refreshRoles();
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
    dataRoles:{ get(){ return this.$store.getters['aplication/getterRoles']; } }
  },
}
</script>

<style lang="scss">
  .pa-0 .modal-content .modal-body{
    padding: 0px !important;
  }
  .scroollH{
    overflow-x: auto;
    width: 100%;
  }
  .scroollH::-webkit-scrollbar{
    width: 6px;
    height: 6px
  }
  .scroollH::-webkit-scrollbar-track{
    background:#c0c0c0;
    border-radius:50px;
  }
  .scroollH::-webkit-scrollbar-thumb{
    background:#000000;
    border-radius:50px;
  }
</style>
