<template>
  <div class="modal fade" id="typeUserModal" tabindex="-1" role="dialog" aria-labelledby="typeUserModal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tipos de Usuario</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="d-flex flex-column px-2">
            <h6 class="font-weight-bold">Lista de Roles</h6>
            <ul class="list-group mt-2 typeUser-list">
              <li class="list-group-item d-flex justify-content-between" v-for="rol in roles">
                <span class="pt-2 text-capitalize">{{rol.name}}</span>
                <div class="">
                  <button type="button" @click="openModal(rol)" class="btn bg-secundario text-white px-1 py-1 q-btn-sm text-sm m-0">
                    <i class="fas fa-pencil-alt"></i>
                  </button>
                  <button type="button" class="btn bg-danger text-white px-1 py-1 q-btn-sm text-sm m-0" :disabled="waitResponse" @click="deleteRole(rol.id)">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn bg-info text-white mr-2" @click="openAddPermissionModal" :disabled="waitResponse">
            <i class="fas fa-plus"></i> Añadir Privilegio
          </button>
          <button type="button" class="btn bg-secundario text-white" data-dismiss="modal" :disabled="waitResponse">Cerrar</button>
          <button type="button" class="btn text-white align-self-end btn-primary" @click="openModal(false)">Crear</button>
        </div>
      </div>
    </div>
    <crudRoles @refreshData="refreshData" @closeModal="closeModal" :dataEdit="dataEdit"/>
    <addPermission @permissionAdded="onPermissionAdded"/>
  </div>
</template>

<script>
import $ from 'jquery';
import crudRoles from './crudRoles.vue';
import addPermission from './addPermission.vue';
import ConfigHelper from '@/helpers/ConfigHelper';
export default{
  data(){
    return{
      waitResponse:false,
      dataEdit: false,
    }
  },
  mounted(){
    this.refreshData();
  },
  components:{
    crudRoles,
    addPermission
  },
  methods:{
    async refreshData(){
      this.waitResponse = true;
      await this.$store.dispatch("roles/getRoles");
      await this.$store.dispatch("main/refreshData");
      this.waitResponse = false;
    },
    async deleteRole(id){
      this.waitResponse = true;
      let request = await this.$store.dispatch("roles/deleteRole",id);
      console.log(request);
      if (request.success) {
        this.$awn.success('Rol Removido Exitosamente',{labels:{success:'CORRECTO'}});
        var refreshApp = await this.$store.dispatch('main/refreshData');
        ConfigHelper.writeAppFile(refreshApp.data);
        ConfigHelper.readAppFile((err, data) => {
          if(err) { return; };
          //Si el archivo se leyo correctamente, saltar al login
          console.log('EJECUSION DE ConfigHandler DE ROLES');
          ConfigHelper.ConfigHandler(false, JSON.parse(data), false);

        });
        this.refreshData();
      }else {
        this.$awn.alert('Error en el servidor');
      }
      this.waitResponse = false;
    },
    // Funcion para abrir modal
    openModal(data){
      if(data)
        this.dataEdit = data;
      else
        this.dataEdit = false;
      $('#crudRoles').modal('show');
    },
    closeModal(){
      $('#crudRoles').modal('hide');
    },
    // Funciones para el modal de añadir privilegios
    openAddPermissionModal() {
      $('#addPermissionModal').modal('show');
    },
    onPermissionAdded(newPermission) {
      this.$awn.success(`Privilegio "${newPermission.description}" añadido exitosamente`, {labels:{success:'PRIVILEGIO AÑADIDO'}});
      this.refreshData();
    },
  },
  computed:{
    roles:{
      get() {
        return this.$store.getters['roles/getterRoles'];
      }
    }
  },
}
</script>
