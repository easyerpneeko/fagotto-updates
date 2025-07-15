<template>
  <StackModal
    :show="showModal"
    @close="showModal = false"
    :modal-class="{ [modalClass]: true }"
  >
    <div slot="modal-header">
      <div class="modal-header bg-one">
          <h3 class="modal-title">Noticia</h3>
          <button type="button" class="close close-center" data-dismiss="modal" aria-label="Close" @click="showModal = false">
            <span class="close-color" aria-hidden="true">&times;</span>
          </button>
      </div>
    </div>

      <div class="d-flex flex-wrap pt-2">
        <div class="form-group col-12">
          <label for="title">Titulo (opcional)</label>
          <input v-model="title" type="text" class="form-control" id="title" >
        </div>
        <div class="form-group col-12">
          <label for="desc">Descripción</label>
          <textarea rows="4" cols="50" v-model="desc" class="form-control" id="desc">
          </textarea>
        </div>
        <div class="form-group col-12">
          <div class="custom-control custom-checkbox custom-control-inline">
            <input v-model="isApp" type="checkbox" class="custom-control-input" id="defaultInline2">
            <label class="custom-control-label" for="defaultInline2">Enviar a una aplicación especifica</label>
          </div>
        </div>
        <div v-if="isApp && optionsApps && optionsApps.length > 0" class="form-group col-12">
          <label>Aplicación</label>
          <v-select :options="optionsApps" v-model="app_id" style="width: 100%;"></v-select>
        </div>
      </div>
    <div slot="modal-footer">
      <div class="modal-footer">
        <a class="btn bg-two text-white" style="float:right;" @click="showModal = false">Cerrar</a>
        <a class="btn bg-one text-white" style="float:right;" @click="sendInfo">Guardar</a>
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
  name: 'createToEdit',
  data(){
    return{
      modalClass: 'modal-md',
      title: null,
      desc: null,
      app_id: null,
      isApp: false
    }
  },
  components:{StackModal},
  props:['value', 'dataEdit'],
  computed:{
    showModal:{
      get(){ return this.value },
      set(value){ this.$emit('input', value) }
    },
    optionsApps:{
      get(){ return this.$store.getters['aplication/getSelectAplications']; }
    }
  },
  watch:{
    dataEdit(value){
      if(value){
        this.title =  value.title;
        this.desc =  value.desc;
        this.app_id =  value.app_id;
      }else{
        this.title =  null;
        this.desc =  null;
        this.app_id =  null;
      }
    }
  },
  methods:{
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
        let data = {
          title: this.title,
          desc: this.desc
        };
        if(this.isApp && this.app_id) data.app_id = this.app_id.value;

        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);

        if(this.dataEdit) var request = await this.$store.dispatch('feeds/update', {data: thing, id:this.dataEdit.id});
        else var request = await this.$store.dispatch('feeds/store', thing);
      loader.hide();

      if(!request.success){
        AllErrors.getError(request.data);
      }else{
        this.title = null,
        this.desc = null,
        this.app_id = null,
        this.isApp = false;
        this.showModal = false;
        this.$emit('refresh', true);
        this.$toastr.success(request.data, 'Exitoso');
      }
    },
  },
}
</script>

<style scoped>
  .close-color{
    color: #ffffff;
  }
</style>
