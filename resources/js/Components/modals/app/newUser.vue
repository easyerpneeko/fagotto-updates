<template>
  <StackModal
    :show="showModal"
    @close="$emit('closeModal', false)"
    :modal-class="{ [modalClass]: true }"
  >
    <div slot="modal-header">
      <div class="modal-header bg-one">
        <h3 class="modal-title">{{(this.dataEdit == null) ? 'Nuevo usuario' : 'Editar usuario'}}</h3>
        <button type="button" class="close close-center" data-dismiss="modal" aria-label="Close" @click="$emit('closeModal', false)">
          <span class="close-color" aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
    <div class="modal-body modal-body-p-0">
      <form role="form" class="px-2 py-1">
        <div class="row">
          <div class="form-group col-md-6 col-12">
            <label for="fullname">Nombre completo</label>
            <input v-model="fullname" type="text" class="form-control" id="fullname" >
          </div>
          <div class="form-group col-md-6 col-12">
            <label for="username">Nombre de usuario</label>
            <input v-model="username" type="text" class="form-control" id="username" >
          </div>
          <div class="form-group col-md-6 col-12">
            <label for="email">Correo</label>
            <input v-model="email" type="text" class="form-control" id="email">
          </div>
          <div class="form-group col-md-6 col-12">
            <label>Rol</label>
            <div class="select">
              <select class="select_class" v-model="role" name="slct" id="slct">
                <option disabled selected>Selecciona una opción</option>
                <option v-if="roleOptions" v-for="(option,index) in roleOptions" :value="option.value">
                  {{option.label}}
                </option>
              </select>
            </div>
            <!-- <v-select v-if="roleOptions"  :options="roleOptions" v-model="role" style="width: 100%;"></v-select> -->
          </div>
          <div class="form-group col-md-6 col-12">
            <label for="password">Contraseña</label>
            <input v-model="password" type="password" class="form-control" id="password">
          </div>
          <div class="form-group col-md-6 col-12">
            <label for="confirmPass">Confirmar contraseña</label>
            <input v-model="confirmPass" type="password" class="form-control" id="confirmPass">
          </div>
        </div>
      </form>
    </div>
    <div slot="modal-footer">
      <div class="modal-footer">
        <a class="btn bg-one text-white" style="float:right;" @click="sendInfo"> {{(this.dataEdit == null) ? 'Crear' : 'Editar'}}</a>
      </div>
    </div>
  </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import moment from 'moment';

export default {
  name: 'createApp',
  data(){
    return{
      modalClass: '',
      fullname: null,
      username: null,
      email: null,
      password: null,
      role: null,
      confirmPass: null,
    }
  },
  components:{
    StackModal,
  },
  props:[
    'showModal',
    'dataEdit'
  ],
  watch: {
   dataEdit: {
      handler(data) {
       if (data != null) {
         console.log("no es null");
         this.fullname = data.fullname;
         this.username = data.username;
         this.email = data.email;
         this.role = data.role;
       }else{
         console.log("es null");
         this.fullname = null;
         this.username = null;
         this.email = null;
         this.role = null;
       }
      }
    },
  },
  methods:{
    // Agregar nuevos Usuarios
    async sendInfo(){
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

        /* Solo debera validar que sea obligatoria la contraseña si se esta
            creando un nuevo usuario, si no, debe ser opcional
          */
        if(this.dataEdit == null) {
          if(this.password == '' || this.password == null){
            loader.hide();
            return this.$toastr.error('Porfavor inserte una contraseña', 'Error');
          }
        }
        /* Solo debe validar la confirmacion de contraseña si se esta creando
           un nuevo usuario o... se agrego una contraseña
           */
        if(this.dataEdit == null || (this.password != '' && this.password != null)) {
          if(this.confirmPass == '' || this.confirmPass == null){
            loader.hide();
            return this.$toastr.error('Porfavor inserte la confirmacion de contraseña', 'Error');
          }
        }

        //Esto si esta bien se valide en ambas
        if(this.role == null || this.role == ''){
          loader.hide();
          return this.$toastr.error('Porfavor indique un rol', 'Error');
        }

        const data = {
          fullname: this.fullname,
          username: this.username,
          email: this.email,
          password: this.password,
          confirmPass: this.confirmPass,
          role: this.role,
        };
        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);


        if(this.dataEdit == null)
          var request = await this.$store.dispatch('aplication/newUserApp', {data: thing, id: this.app.Id});
        else
          var request = await this.$store.dispatch('aplication/editUserApp', {data: thing, idUser: this.dataEdit.userID, id: this.app.Id});

        if(!request.success){
          var allErrors = request.data;
          if (typeof(allErrors) == 'object') {
            for (var errorkey in allErrors) {
              if (allErrors[errorkey]){
                for (var error of allErrors[errorkey]) {
                  this.$toastr.error(error, 'Error');
                }
              }
            }
          }else{
            //Si no es objeto entonces de una
            this.$toastr.error(request.data, 'Error');
          }
        }else{
          this.fullname = null;
          this.username = null;
          this.email = null;
          this.password = null;
          this.confirmPass = null;
          this.role = null;
          this.$emit('closeModal', true)
          this.$toastr.success(request.data, 'Exitoso');
        }
       loader.hide();
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
    roleOptions:{ get(){ return this.$store.getters['aplication/getterRolesOptions']; } }
  },
}
</script>

<style scoped>

  .close-color{
    color: #ffffff;
  }

  .modal-body{
    padding: 0 !important;
  }
</style>
