<template>
  <div class="">
    <StackModal :show="showEnvs" @close="$emit('closeModals', false)" :modal-class="{ [modalClass]: true }">
      <div slot="modal-header">
        <div class="modal-header bg-one">
          <h5 class="modal-title">Variables de Entorno</h5>
          <a class="close" aria-label="Close" @click="$emit('closeModals', false)">
            <span aria-hidden="true">&times;</span>
          </a>
        </div>
      </div>
      <div class="modal-body" style="overflow-y: auto; max-height: 65vh;" v-if="env_vars">
        <div v-if="(env_vars && env_vars.length > 0)" class="row px-2 m-0">
          <div  v-if="!entorno.hidden"
                v-for="(entorno, index) in env_vars" 
                :key="index" 
                :class="['form-group col-12', (entorno.type != 'check') ? 'col-md-6' : '' ]"
          >
            <div v-if="entorno.type == 'image'" class="m-0 p-0">
              <label :for="'idInput'+index" class="text-capitalize">{{entorno.label}}</label>
              <div>
                <img 
                  v-if="entorno.value" 
                  :src="bUrl.getUrl('images/'+entorno.value)" 
                />
                <input 
                  type="file" 
                  accept="image/*"
                  @change="($event) => uploadImage($event, index)" 
                />
              </div>
            </div>
            <div v-else-if="entorno.type == 'check'" class="custom-control custom-checkbox">
              <input v-model="entorno.value" type="checkbox" class="custom-control-input" :id="'idInput'+index">
              <label class="custom-control-label" :for="'idInput'+index">{{entorno.label}}</label>
            </div>
            <div v-else class="m-0 p-0">
              <!-- type=='string' || type=='number' -->
              <label :for="'idInput'+index" class="text-capitalize">{{entorno.label}}</label>
              <input :id="'idInput'+index" type="text" class="form-control" v-model="entorno.value" :maxlength="entorno.max">
            </div>
          </div>
        </div>
        <div v-else class="w-100 text-center px-2">
          <h5>No existen variables de entorno</h5>
        </div>
      </div>
      <div slot="modal-footer">
        <div class="modal-footer">
          <a @click="$emit('closeModals', false)" class="btn bg-two text-white">Cerrar</a>
          <a @click="saveChanges" class="btn bg-one text-white">Guardar</a>
        </div>
      </div>
    </StackModal>
  </div>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import RegexSii from '../../../classes/RegexSii.js';
import BaseUrl from '../../../../assets/helpers/BaseUrl';

export default {
  name: 'Envs',
  data(){
    return{
      modalClass: 'modal-lg',
      env_vars: [],
    }
  },
  components:{
    StackModal,
  },
  props:['showEnvs'],
  methods:{
    async saveChanges(){
      console.log(this.env_vars);
      var varReturn = false;
      this.env_vars.map((key)=>{
        if(key.value != "" && key.value != null){
          //
          // if(key.keyname == "sii_rut" || key.keyname == "sii_emisor_rut"){
          //   if(!RegexSii.testRut(key.value)) {
          //     this.$toastr.error("El rut "+ key.label +" esta mal construido", 'Error');
          //     varReturn = true;
          //   }
          // }
          if(key.keyname == "sii_arteco" || key.keyname == "sii_iva_amount"){
            if(!RegexSii.testNumeric(key.value)) {
              this.$toastr.error("El campo "+ key.label +" debe ser numerico", 'Error');
              varReturn = true;
            }
          }

        }
      });
      if(varReturn) return;
      let loader = this.$loading.show({
        // Optional parameters
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 99999,
      });

      var data = new FormData();
      for (let entorno in this.env_vars) {
        const currentVar = { ...this.env_vars[entorno] };
        if (currentVar.type === 'image') {
          /*currentVar.value = currentVar.reallyValue;
          delete currentVar.reallyValue;
          console.log('current Var', currentVar);
          console.log('really entorno', this.env_vars[entorno]);*/
          console.log('Image', currentVar.reallyValue);
          if (currentVar.reallyValue) {
            data.append('image_'+currentVar.keyname, currentVar.reallyValue);
            delete currentVar.reallyValue;
          }
        }
        data.append(currentVar.keyname, JSON.stringify(currentVar));
      }
      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/modifyEnvs', {id: this.app.Id, data});
      if (request.success) {
        this.$toastr.success('Variable creada exitosamente', 'Exito!');
        this.$emit('closeModals', true);
      }else this.$toastr.error(request.data, 'Error');

      loader.hide();
    },
    uploadImage(event, index) {
      console.log('Upload image in ', index);
      console.log('vars', this.env_vars);
      const image = event.target.files[0];
      this.env_vars[index].reallyValue = image;
    }
  },
  computed:{
    app:{
      get(){ return this.$store.getters['aplication/getterApp']; },
    },
    bUrl: {
      get() { return BaseUrl; }
    }
  },
  watch:{
    showEnvs(value){
      if(value){
        this.env_vars = [];
        var filds = ['label','value','type','max'];
        for (var key in this.app.Entorno) {
          if(key != 'enverioments_version'){
            var object = {};
            filds.map((fild)=>{
              object[fild] = this.app.Entorno[key][fild];
            });
            object['keyname'] = key;
            this.env_vars.push(object);
          }
        }
      };
    }
  }
}
</script>
