<template>
  <div class="product-bg d-flex flex-column align-items-end">
    <div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center pt-3 px-3">
      <div class="pl-2 d-flex row w-100">
        <div class="card title-card col-md-5 col-sm-7 col-12">
          <div class="card-body">
            <h5 class="font-weight-bold m-0">Usuarios</h5>
            <span>Edita, agrega o elimina usuarios</span>
          </div>
        </div>
        <div class="offset-md-4 offset-sm-1 col-md-3 col-sm-4 col-12 d-flex flex-column">
          <button @click="resetValue(null)" type="button" class="m-1 btn-width btn bg-primario text-white text-capitalize" data-toggle="modal" data-target="#newUserModal">Añadir Usuario</button>
          <button data-toggle="modal" data-target="#typeUserModal" type="button" class="m-1 btn-width btn bg-secundario text-white text-capitalize" v-if="userTypeGestion">Tipos de usuarios</button>
        </div>
      </div>
      <div class="d-flex row w-100 pt-4" v-if="usersGet">
        <div class="col-md-4 col-sm-6 col-12">
          <label for="rutcliente1">Buscar</label>
          <input :disabled="offOn" id="rutcliente1" v-model="rutUser" type="text" class="form-control" @keypress.enter="getUsers" />
        </div>
        <div class="col-md-8 col-sm-6 col-12 d-flex align-items-end">
          <button :disabled="offOn" @click="refreshData" class="btn mx-1 mt-1 mb-0 bg-primario btnPersonalice">
            <i class="fas fa-sync-alt"></i>
            <span class="d-none-0">Refrescar</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Lista de usuarios -->
    <div class="d-flex w-100 h-100 flex-column justify-content-start align-items-start pt-2 px-3">
      <div ref="loaderUsers" v-if="(users && users.items.length > 0)" :class="['vld-parent d-flex w-100 flex-wrap align-items-center',(users.items.length <= 5) ? '' : 'justify-content-around']">
        <div class="m-2" v-for="(user, index) in users.items" :key="index">
          <div class="card">
            <div class="card-body p-1 px-2 pb-2 text-center">
              <div class="p-3">
                <img :src="getImage(user.avatar)" class="user-img img-fluid img-circle">
              </div>
              <h5 class="mt-2 text-capitalize">{{user.fullname}}</h5>
              <h6 class="color-money text-capitalize">{{user.RoleName}}</h6>
              <hr class="w-95 m-3" style="border: 0.5px solid rgba(0,0,0,0.2);">

              <div class="row justify-content-end align-items-end w-100">
                <div class="">
                  <button type="button" class="btn bg-secundario btn-rounded" data-toggle="modal" data-target="#newUserModal" @click="resetValue(user)">
                    <i class="fas fa-pencil-alt"></i>
                  </button>
                  <button v-if="me.id != user.id" type="button" class="btn bg-danger btn-rounded" @click="openVerify(user)">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginacion -->
        <paginate
          v-if="(users && users.pages > 1)"
          v-model="users"
          :offOn="offOn"
          @getPage="getUsers"
        />
      </div>
      <div ref="loaderUsers" v-else class="vld-parent box-false d-flex flex-center text-center p-2 w-100">
        <h2>No existen usuarios actualmente</h2>
      </div>
    </div>

    <!-- Modals -->
    <type-user />
    <verify-modal :propVerify="propVerify" @refreshData="refreshData" />
    <crud-user v-model="userEdit" @refresh="refreshData"/>
  </div>
</template>

<script>
// components
import paginate from  '@/components/MPage.vue';
import crudUser from '@/components/modals/newUser.vue';
import typeUser from '@/components/modals/typeUser.vue';
import verifyModal from '@/components/modals/verifyDelete.vue';

// Helpers
import BaseUrl from '@/helpers/baseUrl.js';
import ConfigHelper from '@/helpers/ConfigHelper.js';
import Loader from '@/helpers/Loader';

export default {
  data(){
    return{
      users: null,
      userEdit: null,
      oldPage: 1,
      rutUser: null,
      propVerify: null,
    }
  },
  mounted(){
    console.log('=============USUARIOS================')
    //HavePermission
    this.refreshData(true);
  },
  components: {
    crudUser,
    typeUser,
    verifyModal,
    paginate
  },
  props:{
    value: {
      type: Boolean,
      default: false
    }
  },
  methods:{
    // Refrescando usuarios
    async refreshData(loading = false){
      // Iniciando refrescamiento (carga y botones disabled)
      this.offOn = true;
      if(loading === true) Loader.containe(this.$refs.loaderUsers);
      else Loader.dinamic();

      if (this.usersGet){
        this.rutUser = null;
        this.getUsers(this.oldPage, true);

        // Refrescando roles
        if(this.userTypeGestion) await this.$store.dispatch("roles/getRoles");
      }else{
        this.$router.push('/inicio');
      }

      // Culminando la funcion
      Loader.hide();
      this.offOn = false;
    },

    // Traer usuarios y parametros de usuarios
    async getUsers(page = false, isRefresh = false){
      if(!isRefresh){
        this.offOn = true;
        Loader.containe(this.$refs.loaderUsers);
      }

        // Parametros en la ruta
        var params = '?params=true';
        if(page && this.oldPage != page) this.oldPage = page;
        params += '&page=' + this.oldPage;
        if(this.rutUser != null && this.rutUser != '') params += '&rutOfUser=' + this.rutUser;

        // Iniciando peticion
        let request = await this.$store.dispatch("main/getUsers", params);
        // Verificando la respuesta
        if (!request.success) this.$awn.alert(request.data);
        else{
          this.users = request.data;
        }

      if(!isRefresh){
        Loader.hide();
        this.offOn = false;
      }
    },

    // Modal Verify
    openVerify(user){
      this.propVerify = {
        params: user.id,
        title: 'Eliminar usuario',
        text: '¿Usted esta seguro de querer eliminar a '+ user.fullname+ '?',
        store: 'main/deleteUser',
        success: 'Usuario eliminado exitosamente'
      };
      $('#verifyDelete').modal('show');
    },

    // Url img
    getImage(image){
      return BaseUrl.getUrl('images/'+image);
    },

    // Reseteando el usuario del modal para editar o crear
    resetValue(user){
      if(this.userEdit != user) this.userEdit = user;
    }
  },
  computed:{
    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input',offOn) }
    },
    // my user
    me:{ get(){ return this.$store.getters['main/user']; } },
    // Permisos de usuario
    usersGet:{ get(){ return ConfigHelper.HavePermission('usuarios_obtener'); } },
    userTypeGestion:{ get(){ return ConfigHelper.HavePermission('tipos_usuarios_gestion'); } }
  },
}
</script>
