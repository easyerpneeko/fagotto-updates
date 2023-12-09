<template>
  <div style="z-index: 99999999999999999999999999" class="modal fade modalForce" id="initMoney" tabindex="-1" role="dialog" aria-labelledby="initMoney" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Iniciar Turno</h5>
        </div>
        <div class="modal-body">
          <div class="form-group w-100">
            <label for="rut">Monto Inicial del Turno</label>
            <input id="money" type="number" class="form-control" v-model="init_money" @keyup.enter="startShift" min="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn bg-secundario text-white" @click="logout">Cerrar</button>
          <button type="button" class="btn bg-primario text-white" @click="startShift">Iniciar Turno</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import $ from 'jquery';
import ConfigHelper from '@/helpers/ConfigHelper.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
const fs = require('fs');

export default {
  data(){
    return{
      init_money: 0,
    }
  },
  methods:{
    async logout(){
      await fs.unlink('authorization.json',(error)=>{
        if (error) {
          // console.log(error);
        }
        this.$router.push('/login');
      });
    },
    async sendInfo(){
      if(this.init_money == null && this.init_money == '') return this.$awn.alert('Valor de monto inicial incorrecto');

      let data = new FormData();
      data.append('init_money', this.init_money);

      Loader.fullPage();
      var request = await this.$store.dispatch('main/setInitMoney', data);
      console.log(request.data);
      Loader.hide();

      if (request.success){
        this.$awn.success('Monto inicial establecido exitosamente',{labels:{success:'CORRECTO'}});
      }else{
        let allErrors = request.data;
        if (typeof(allErrors) == 'object') {
          for (var errorkey in allErrors) {
            if (allErrors[errorkey]){
              for (var error of allErrors[errorkey]) {
                return this.$awn.alert(error);
              }
            }
          }
        }else{
          return this.$awn.alert(allErrors);
        }
      }

      $('#initMoney').modal('hide');
    },
    startShift(){
      console.log("startShift");
      if(this.init_money == null && this.init_money == '') return this.$awn.alert('Valor de monto inicial incorrecto');
      Loader.fullPage();
      //guardar la hora y fecha de inicio de turno en un local storage
      let start_workshift = localStorage.getItem('start_workshift');
      
      if(start_workshift === null){
        const init_money = localStorage.setItem('init_money', this.init_money);
        const start_workshift = localStorage.setItem('start_workshift', moment().format('YYYY-MM-DD HH:mm:ss'));
      }else{
        this.$awn.info('Ya existe un turno iniciado, debes finalizar el turno antes de iniciar otro');
      }
     

      Loader.hide();
      $('#initMoney').modal('hide');
    },
  },
  computed:{
  }
}
</script>

<style scoped>
  .modal-md{
    width: 50vw;
  }
  .text-uppercase{
    text-transform: uppercase;
  }
  @media (max-width: 1000px){
    .modal-md{
      width: 80vw;
    }
  }
  @media (max-width: 650px){
    .modal-md{
      width: 100vw;
      margin: 0px !important;
    }
  }
</style>
