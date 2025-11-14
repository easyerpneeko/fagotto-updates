<template>
  <StackModal
    :show="showModal"
    @close="$emit('changeState', false)"
    :modal-class="{ [modalClass]: true }"
  >
    <div slot="modal-header">
      <div class="modal-header bg-one">
            <h3 class="modal-title">Nueva Aplicacion</h3>
          <button type="button" class="close close-center" data-dismiss="modal" aria-label="Close" @click="$emit('changeState', false)">
            <span class="close-color" aria-hidden="true">&times;</span>
          </button>
      </div>
    </div>
    <div class="modal-body modal-body-p-0" >
      <div class="">
        <form role="form" class="p-4">
          <div class="row">
            <div class="form-group col-md-6 col-12">
              <label for="rut" >Rut</label>
              <input :disabled="keyDisabled" v-model="rut" type="text" class="form-control" id="rut" @change="searchUser" >
            </div>
            <div class="form-group col-md-6 col-12">
              <label for="client">Nombre del cliente</label>
              <input :disabled="keyDisabled" v-model="username" type="text" class="form-control" id="client" >
            </div>
            <div class="form-group col-md-6 col-12">
              <label for="direction">Dirección</label>
              <input :disabled="keyDisabled" v-model="direction" type="text" class="form-control" id="direction">
            </div>
            <div class="form-group col-md-6 col-12">
              <label>Genero</label>
              <v-select :options="optionsSexo" v-model="sexo" style="width: 100%;"></v-select>
            </div>
            <div class="form-group col-md-6 col-12">
              <label for="email">Correo del cliente</label>
              <input :disabled="keyDisabled" v-model="email" type="text" class="form-control" id="email">
            </div>
            <div class="form-group col-md-6 col-12">
              <label for="description">Sobre el cliente</label>
              <textarea rows="4" cols="50" :disabled="keyDisabled" v-model="description" class="form-control" id="description">
              </textarea>
            </div>
            <div class="form-group col-md-6 col-12">
              <label for="name">Nombre de la aplicación</label>
              <input v-model="name" type="email" class="form-control" id="name" @keypress="validaInput($event)">
            </div>
            <div class="form-group col-md-6 col-12">
              <label for="name_public">Nombre publico de la aplicación</label>
              <input v-model="name_public" type="email" class="form-control" id="name_public">
            </div>
            <div class="form-group col-12">
              <label>Fecha de expiracion:</label>
              <div class="input-group">
                <input :format="customFormatter" :min="tomorrowDate" :max="nextYearDate" class="form-control" type="date" v-model="expiration" @change="validateExpiration"/>
              </div>
            </div>
          </div>
        </form>
      </div>

    </div>
    <div slot="modal-footer">
      <div class="modal-footer">
        <a class="btn bg-one text-white" style="float:right;" @click="sendInfo">Crear</a>
      </div>
    </div>
  </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import AllErrors from '../../../Helpers/AllErrors';
import Datepicker from 'vuejs-datepicker';
import moment from 'moment';

export default {
  name: 'createApp',
  data(){
    return{
      modalClass: 'modal-xl',
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
      description: null,
      direction: null,
      email: null,
      name_public: null,
      tomorrowDate: null,
      nextYearDate: null,
    }
  },
  components:{
    StackModal,
    Datepicker
  },
  props:[
    'showModal'
  ],
  mounted(){
    var today = new Date();
    var tomorrowDate = new Date(today.setDate(today.getDate() + 1)).toISOString().split("T")[0];
    this.tomorrowDate = tomorrowDate;
    var nextYearDate = new Date(today.setDate(today.getDate() + 364)).toISOString().split("T")[0];
    this.nextYearDate = nextYearDate;
  },
  methods:{
    validaInput($event){
      var ch = String.fromCharCode($event.which);

      if(!(/[a-zA-Z0-9\040]+/.test(ch))){
        $event.preventDefault();
      }
    },
    //Obtener fecha
    customFormatter(expiration) {
      return this.expiration = moment(expiration).format('YYYY-MM-DD h:mm:ss');
    },
    validateExpiration(){
      var expiration = this.expiration;
      if (expiration < this.tomorrowDate || expiration > this.nextYearDate) {
        //this.expiration = moment(expiration).format('YYYY-MM-DD h:mm:ss');
      }
    },
    // Buscar usuario
    async searchUser(){
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
        this.keyDisabled = true;
        var request = await this.$store.dispatch('aplication/getRut', this.rut);
        if(!request.success){
          this.keyDisabled = false;
          this.$toastr.info(request.data, 'Información');
        }else{
          this.keyDisabled = false;
          this.username = request.data.username;
          this.direction = request.data.direction;
          this.description = request.data.description;
          this.email = request.data.email;
          this.sexo = (request.data.sexo == 'male') ? this.optionsSexo[1] : this.optionsSexo[0];
          this.$toastr.success('Cliente encontrado', 'Exitoso');
        }
      loader.hide();
    },
    // Agregar una aplicacion
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
        if(this.name == null || this.name == ''){
          loader.hide();
          return this.$toastr.error('Porfavor introduzca un nombre aplicación', 'Error');
        }
        if(this.username == null || this.username == '' || this.rut == null || this.rut == '' || this.sexo == null || this.sexo == '' || this.email == null || this.email == ''){
          loader.hide();
          return this.$toastr.error('Porfavor llene los campos del cliente correctamente', 'Error');
        }
        if(this.expiration == null || this.expiration == ''){
          loader.hide();
          return this.$toastr.error('Porfavor introduzca una fecha de expiración', 'Error');
        }
        if(this.name_public == null || this.name_public == ''){
          loader.hide();
          return this.$toastr.error('Porfavor introduzca un nombre publico de aplicación', 'Error');
        }
        const data = {
          name: this.name,
          rut: this.rut,
          username: this.username,
          direction: this.direction,
          sexo: this.sexo.value,
          description: this.description,
          expiration: this.expiration,
          email: this.email,
          name_public: this.name_public,
        };
        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);

        var request = await this.$store.dispatch('aplication/newApp', thing);
        if(!request.success){
          //this.$toastr.error(request.data, 'Error');
          AllErrors.getError(request.data);
        }else{
          await this.$store.dispatch('aplication/getAplications');
          this.name = null,
          this.username = null,
          this.rut = null,
          this.sexo = "",
          this.disable = false,
          this.keyDisabled = false,
          this.expiration = null,
          this.email = null,
          this.description = null,
          this.direction = null,
          this.name_public = null

          this.$emit('changeState', true);
          this.$toastr.success(request.data, 'Exitoso');
        }
       loader.hide();
    },
  },
}
</script>

<style>

  .close-color{
    color: #ffffff;
  }

  .modal-body{
    padding: 0 !important;
  }
</style>
