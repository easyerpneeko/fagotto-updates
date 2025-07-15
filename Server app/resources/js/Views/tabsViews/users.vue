<template>
  <div v-if="usersApp" class="container-fluid">
    <div class="row">
      <div class="col-12 pb-2">
        <a class="btn bg-one text-white mx-1" style="float:right;" @click="openModal()">
          Crear usuario
        </a>
        <a class="btn bg-dark text-white mx-1" style="float:right;" @click="modalRole = true">
          Gestion de roles
        </a>
      </div>
      <div v-for="(user, index) in usersApp.items" :key="index" class="col-md-4 col-sm-6 col-12">
        <div class="card card-widget widget-user">
          <div class="widget-user-header bg-one pb-3">
              <h3 class="widget-user-username">{{user.fullname}}</h3>
              <h5 class="widget-user-desc">{{user.RoleName}}</h5>
          </div>
          <div class="widget-user-image">
            <img class="img-circle elevation-2" src="../../../assets/images/person.jpg" alt="User Avatar">
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="col-12">
                <div class="description-block text-center">
                  <h5 class="description-header">Usuario</h5>
                  <span class="">{{user.username}}</span>
                </div>
              </div>
              <div class="col-12">
                <div class="description-block">
                  <h5 class="description-header">Correo</h5>
                  <span class="">{{user.email}}</span>
                </div>
              </div>
              <div class="col-12">
                <a class="btn btn-sm bg-one text-white mx-1" style="float:right;" @click="openModal(user)">
                  <i class="fas fa-pen"></i>
                </a>
                <a class="btn btn-sm bg-dark text-white mx-1" style="float:right;" @click="deleteUser(user)">
                  <i class="fas fa-trash-alt"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Paginacion -->
    <paginate
      v-if="usersApp.pages > 1"
      :back="usersApp.paginate.back"
      :next="usersApp.paginate.next"
      :activePage="usersApp.page"
      :totalPages="usersApp.pages"
      @refreshData="getUsers"
    />
    <new-user :showModal="modal" @closeModal="closeModal" :dataEdit="dataEdit" />
    <modal-roles :showRole="modalRole" @closeModals="closeModal" :dataId="this.app.Id"/>
  </div>
</template>

<script>
import paginate from  '../../Components/paginate.vue';
import newUser from '../../Components/modals/app/newUser.vue';
import modalRoles from '../../Components/modals/app/modalRoles.vue';

export default {
  data(){
    return{
      modal: false,
      modalRole: false,
      dataEdit: null,
    }
  },
  components:{
    newUser,
    modalRoles,
    paginate
  },
  methods:{
    async getUsers(page = false){

      // Parametros a enviar
      var params = this.app.Id + '?params=true'
      if(page != false) params += '&page='+page;

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
      var request = await this.$store.dispatch('aplication/getUserApp', params);
      if(!request.success) return this.$toastr.error(request.data, 'Error');

      loader.hide();
    },

    openModal(data = null){
      this.dataEdit = data;
      this.modal = true;
    },

    async deleteUser(data) {

      let loader = this.$loading.show({
       // Optional parameters
       container: this.$refs.formContainer,
       color: '#007bff',
       width: 80,
       height: 80,
       backgroundColor: '#000000',
       opacity: 0.8,
       zIndex: 999999,
     });
      var request = await this.$store.dispatch('aplication/trashUserApp', { idUser: data.userID, id: this.app.Id });
      loader.hide();
      if(!request.success){
        this.$toastr.error(request.data, 'Error');
      }else{
        this.$toastr.success('Usuario eliminado con exito', 'Exitoso');
      }
      this.getUsers();

    },

    closeModal(val){
      this.modal = false;
      this.modalRole = false;
      if(val) this.getUsers();
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
    usersApp:{ get(){ return this.$store.getters['aplication/getterUsersApp'] } }
  },
}
</script>

<style scoped>
/* Styles users */
  .img-circle{
    height: 105px !important;
    width: 100px !important;
  }
</style>
