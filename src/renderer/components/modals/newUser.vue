<template>
  <div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="newUserModal" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{(!this.edit) ? 'Nuevo usuario' : 'Editar usuario'}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-column align-items-center py-4">
          <label for="avatar">
            <img :src='(preview)?preview:getImage("default")' class="img-lg-custom">
            <div class="img-user-edit img-lg-custom"></div>
            <input type="file" id="avatar" ref="fileAvatar" accept="image/x-png,image/gif,image/jpeg" style="display:none" @change="uploadFile">
          </label>
        </div>
        <div class="d-flex row flex-wrap">
          <div class="form-group col-md-6 col-12">
            <label for="fullname">Nombre completo</label>
            <input id="fullname" type="text" class="form-control" placeholder="Nombre Completo" :disabled="waitResponse"
            v-model="fullname" @keyup.enter="newUser">
          </div>
          <div class="form-group col-md-6 col-12">
            <label for="username">Usuario</label>
            <input id="username" type="text" class="form-control" placeholder="Nombre de Usuario" :disabled="waitResponse"
            v-model="username" @keyup.enter="newUser">
          </div>
        </div>
        <div class="d-flex row flex-wrap">
          <div class="form-group col-md-6 col-12">
            <label for="password">Contraseña</label>
            <input id="password" type="password" class="form-control" placeholder="Contraseña" :disabled="waitResponse"
            v-model="password" @keyup.enter="newUser">
          </div>
          <div class="form-group col-md-6 col-12">
            <label for="confirmPass">Confirmar contraseña</label>
            <input id="confirmPass" type="password" class="form-control" placeholder="Confirmacion de Contraseña" :disabled="waitResponse"
            v-model="confirmPass" @keyup.enter="newUser">
          </div>
        </div>
        <div class="d-flex row flex-wrap">
          <div class="form-group col-md-12 col-12">
            <label for="role">Rol de empresa</label>
            <select id="role" class="browser-default custom-select" v-model="role">
              <option selected disabled value="">Rol</option>
              <option :value="rol.id" v-for="rol in roles" class="text-capitalize">{{rol.name}}</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn bg-secundario text-white" data-dismiss="modal">
          Cerrar
        </button>
        <button type="button" class="btn bg-primario text-white" @click="newUser" :disabled="waitResponse">
          {{(!this.edit) ? 'Crear' : 'Editar'}}
        </button>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import $ from 'jquery';
import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';
export default {
  data(){
    return{
      username:'',
      password:'',
      confirmPass: '',
      fullname:'',
      role: '',
      avatar:'',
      preview: '',
      waitResponse: false,
      edit: false,
      id: null
    }
  },
  props:{
    value:{
      type: [Object, Array],
      default: null
    }
  },
  methods:{
    // Guardar o editar usuario
    async newUser(){
      let fields = ['username','password','confirmPass','fullname','role','avatar'];
      let fd = new FormData();
      for (var field of fields) {
        if (!this.edit){
          if ((!this[field] || this[field] == ' ') && field != 'avatar'){
            this.$awn.alert('Debes llenar todos los campos');
            return;
          }
        }
        fd.append(field, this[field]);
      }

      // Iniciando carga
      this.waitResponse = true;
      Loader.fullPage();
      // Iniciando tipo de peticion
      if (this.edit) var request = await this.$store.dispatch("main/editUser",{id:this.id,data:fd});
      else var request = await this.$store.dispatch("main/newUser",fd);

      Loader.hide();
      if (request.success){
        $('#newUserModal').modal('hide');
        if (this.edit) this.$awn.success('Usuario Editado Exitosamente',{labels:{success:'CORRECTO'}});
        else this.$awn.success('Usuario Creado Exitosamente',{labels:{success:'CORRECTO'}});
        await this.$store.dispatch("main/checkAuth");
        this.$emit('refresh');
      }else{
        console.log(request.data);
        let allErrors = request.data;
        if (typeof(allErrors) == 'object') {
          for (var errorkey in allErrors) {
            if (allErrors[errorkey]){
              for (var error of allErrors[errorkey]) {
                this.$awn.alert(error);
              }
            }
          }
        }else{
          this.$awn.alert(allErrors);
        }
      }
      this.refreshData();
      this.waitResponse = false;
    },

    // Actualizar modal
    refreshData(){
      let fields = ['username','fullname','id','avatar','role'];
      for (var field of fields){
        if (this.userEdit != null){
          this[field] = this.userEdit[field];
          this.edit = true;
          if (field == 'avatar' && this.userEdit[field]){
            this.preview = this.getImage(this.userEdit[field]);
          }
        }else{
          this[field] = '';
          this.preview = '';
          this.confirmPass = null;
          this.password = null;
          this.edit = false;
        }
      }
    },

    // Funciones para mostrar y actualizar las imagenes
    getImage(image){
      return BaseUrl.getUrl('images/'+image);
    },
    uploadFile(){
      console.log("[File] Change")
      let uploadFile=this.$refs.fileAvatar.files[0]
      if(!uploadFile){
        console.log("[File] None")
        return;
      }
      this.avatar = uploadFile;
      this.preview = URL.createObjectURL(uploadFile);
    }
  },
  computed:{
    // v-model
    userEdit: {
      get() { return this.value },
      set(userEdit) { this.$emit('input',userEdit) }
    },
    // Roles
    roles:{ get() { return this.$store.getters['roles/getterRoles']; } }
  },
  watch: {
    value: {
      handler(val) {
        this.refreshData();
      }
    },
  }
}
</script>
<style scoped>
.img-lg-custom {
  width: 140px;
  height: 140px;
}
.img-user-edit{
  position: absolute;
  top: 40px;
  width: 140px;
  height: 140px;
  cursor: pointer;
  background: none;
}
.img-user-edit:hover{
  background-image: url('../../assets/iconeditwhite.png');
  image-resolution: snap;
  background-color: rgba(0,0,0,.3);
  background-position: center;
  background-repeat: no-repeat;
  background-size: 60px;
  transition: all .3s ease-out;
}
</style>
