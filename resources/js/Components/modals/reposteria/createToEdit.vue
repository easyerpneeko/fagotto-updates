<template>
    <StackModal
      :show="showModal"
      @close="showModal = false"
      :modal-class="{ [this.modalClass]: true }"
    >
      <div slot="modal-header">
        <div class="modal-header bg-one">
            <h3 class="modal-title">Producto</h3>
            <button type="button" class="close close-center" data-dismiss="modal" aria-label="Close" @click="showModal = false">
              <span class="close-color" aria-hidden="true">&times;</span>
            </button>
        </div>
      </div>
  
        <div class="d-flex flex-wrap pt-2">
          <div class="form-group col-12">
            <label for="name">Nombre</label>
            <input v-model="name" type="text" class="form-control" id="name" >
          </div>
          <div class="form-group col-12">
            <label for="price">Precio</label>
            <input v-model="price" type="text" class="form-control" id="price" >
          </div>
          <div class="form-group col-12">
            <label for="min_quantity">Cantidad Minina</label>
            <input v-model="min_quantity" type="text" class="form-control" id="min_quantity" >
          </div>
          <!-- <div class="form-group col-12">
            <label for="min_quantity">Imagen</label>
            <input v-model="image" type="text" class="form-control" id="min_quantity" >
          </div> -->
          <!-- <div v-if="isApp && optionsApps && optionsApps.length > 0" class="form-group col-12">
            <label>Aplicación</label>
            <v-select :options="optionsApps" v-model="app_id" style="width: 100%;"></v-select>
          </div> -->
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
  // import Datepicker from 'vuejs-datepicker';
  // import moment from 'moment';
  
  export default {
    name: 'createToEdit',
    data(){
      return{
        modalClass: 'modal-md',
        name:  null,
        price: null,
        min_quantity:null,
        // image:null,
        app_id:null,
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
    //   optionsApps:{
    //     get(){ return this.$store.getters['aplication/getSelectAplications']; }
    //   }
    },
    watch:{
      dataEdit(value){
        if(value){
          this.name =  value.name;
          this.price =  value.price;
          this.min_quantity =  value.min_quantity;
          // this.image = value.image;
          this.app_id =  value.app_id;
        }else{
          this.name =  null;
          this.price =  null;
          this.min_quantity =  null;
          // this.image = null;
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
            name: this.name,
            price: this.price,
            min_quantity: this.min_quantity,
            // image: this.image
          };
          if(this.isApp && this.app_id) data.app_id = this.app_id.value;
  
          var thing = new FormData();
          for (let key in data) if (data[key]) thing.append(key, data[key]);
  
          if(this.dataEdit) var request = await this.$store.dispatch('reposteria/update', {data: thing, id:this.dataEdit.id});
          else var request = await this.$store.dispatch('reposteria/store', thing);
        loader.hide();
  
        if(!request.success){
          AllErrors.getError(request.data);
        }else{
          this.name = null,
          this.price = null,
          this.min_quantity = null,
          // this.image = null,
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
  