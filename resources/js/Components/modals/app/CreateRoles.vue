<template>
  <StackModal
    :show="showModal"
    @close="$emit('closeModal', false)"
    :modal-class="{ [modalClass]: true }"
  >
    <div slot="modal-header">
      <div class="modal-header modal-title-blue">
        <h3 class="modal-title">{{(dataEdit) ? 'Editar rol' : 'Crear rol'}}</h3>
        <button type="button" class="close close-center" data-dismiss="modal" aria-label="Close" @click="$emit('closeModal', false)">
          <span class="close-color" aria-hidden="true">&times;</span>
        </button>
      </div>
    </div>
    <div class="modal-body modal-body-p-0">
      <form role="form" class="p-4">
        <div class="row">
          <div class="form-group col-12">
            <label for="name">Nombre del rol</label>
            <input v-model="name" type="text" class="form-control" id="name" >
          </div>
          <div class="form-group col-12">
            <label for="keyname">Nombre clave</label>
            <input v-model="keyname" type="text" class="form-control" id="keyname" >
          </div>
          <div class="form-group col-12">
            <div class="custom-control custom-checkbox custom-control-inline">
              <input v-model="permisoAdmin" :disabled="(permission.length != 0) ? true : false" type="checkbox" class="custom-control-input" id="defaultInline2">
              <label class="custom-control-label" for="defaultInline2">Todos los permisos</label>
            </div>
          </div>

          <div v-if="!permisoAdmin" class="form-group col-12">
            <label class="typo__label">Permisos</label>
            <multiselect v-model="permission" tag-placeholder="Añadir" placeholder="Añade un permiso" label="name" track-by="code" :options="permisosApp" :multiple="true" :taggable="true" @tag="addTag"></multiselect>
          </div>
        </div>
      </form>
    </div>
    <div slot="modal-footer">
      <div class="modal-footer">
        <a class="btn btn-primary text-white" style="float:right;" @click="sendInfo">{{(dataEdit) ? 'Editar' : 'Crear'}}</a>
      </div>
    </div>
  </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import Multiselect from 'vue-multiselect';
import AllErrors from '../../../Helpers/AllErrors';
import moment from 'moment';

export default {
  name: 'createRoles',
  data(){
    return{
      modalClass: '',
      name: null,
      keyname: null,
      permission: [],
      optionP: [],
      permissionData: [],
      permisoAdmin: false,
      arrayBackend: [],
    }
  },
  components:{
    StackModal,
    Multiselect
  },
  props:[
    'showModal',
    'idApp',
    'dataEdit'
  ],
  watch: {
   dataEdit: {
      handler(val) {
        this.refreshData(val);
      }
    },
  },
  methods:{
    // datos a editar
    refreshData(data){
      if(data){
        this.name = data.name;
        this.keyname = data.keyname;
        if(data.permission == null){
          this.permisoAdmin = true;
          this.permission = [];
        }else{
          this.permisoAdmin = false;
          this.permission = [];

          var arrayData = JSON.parse(data.permission);

          for (var i = 0; i < arrayData.length; i++) {
            this.addTag({
              name: this.arrayBackend[ arrayData[i] ],
              code: arrayData[i]
            });
          }
        }
      }else{
        this.name = null;
        this.keyname = null;
        this.permisoAdmin = false;
        this.permission = [];
      }
    },
    // Funcion de estilos que trae la libreria
    addTag (newTag) {
      const tag = newTag;
      this.permisosApp.push(tag)
      this.permission.push(tag)
    },
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
        if(this.name == '' || this.name == null){
          loader.hide();
          return this.$toastr.error('Por favor inserte un nombre de rol', 'Error');
        }
        if(this.keyname == '' || this.keyname == null){
          loader.hide();
          return this.$toastr.error('Por favor inserte un nombre clave de rol', 'Error');
        }
        if(this.permission){
          this.permissionData = [];
          for (var i = 0; i < this.permission.length; i++) {
            this.permissionData.push(this.permission[i].code);
          }
        }
        if(this.permisoAdmin){
          const data = {
            name: this.name,
            keyname: this.keyname,
            permission: 'null',
          };
          var thing = new FormData();
          for (let key in data) if (data[key]) thing.append(key, data[key]);
        }else{
          const data = {
            name: this.name,
            keyname: this.keyname,
            permission: JSON.stringify(this.permissionData),
          };
          var thing = new FormData();
          for (let key in data) if (data[key]) thing.append(key, data[key]);
        }

        if(this.dataEdit){
          var request = await this.$store.dispatch('aplication/editTypeUser', {data: thing, idApp: this.idApp, id: this.dataEdit.id});
        }else{
          var request = await this.$store.dispatch('aplication/newTypeUser', {data: thing, id: this.idApp});
        }

        if(!request.success){
          console.log(request);
          //this.$toastr.error(request.data, 'Error');
          AllErrors.getError(request.data);
        }else{
          this.name = null;
          this.keyname = null;
          this.permission = [];
          this.$emit('closeModal', true);
          this.$toastr.success(request.data, 'Exitoso');
        }
       loader.hide();
    },
  },
  computed:{
    permisosApp:{
      get() {
        var request = this.$store.getters['aplication/getterPermisos'];
        this.arrayBackend = request;
        this.optionP = [];
        for (var key in request) {
          if (request.hasOwnProperty(key)) {
            this.optionP.push({
              name: request[key],
              code: key
            });
          }
        }
        return this.optionP;
      }
    }
  },
}
</script>

<style scoped>
  .modal-title-blue{
    background-color: #007bff;
    color: #ffffff;
  }

  .close-color{
    color: #ffffff;
  }

  .modal-body{
    padding: 0 !important;
  }
</style>
