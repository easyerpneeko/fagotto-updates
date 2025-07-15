<template>
  <div class="card card-widget widget-user-2 m-0">
    <div class="card-header bg-one">
      <h3 class="card-title card-title-padding">Agregar módulos</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse"><i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="select">
        <select class="select_class" v-model="moduleAdd" name="slct" id="slct">
          <option disabled selected>Selecciona una opción</option>
          <option v-for="(option,index) in optionsModules" :value="option.value">
            {{option.label}}
          </option>
        </select>
      </div>
      <!-- <v-select :options="optionsModules" v-model="moduleAdd" style="width: 100%;"></v-select> -->
      <a @click="addModule" class="btn bg-one text-white mt-3" style="float:right;">Agregar</a>
    </div>
  </div>
</template>

<script>
export default {
  data(){
    return{
      moduleAdd: null,
    }
  },
  mounted(){
    this.$store.dispatch('modules/getModulesOptions');
  },
  methods: {
    async addModule(){
      let loader = this.$loading.show({
        // Optional parameters
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        if(this.moduleAdd == '' && this.moduleAdd == null) return this.$toastr.error('Porfavor seleccione un modulo', 'Error');

        var thing = new FormData();
        thing.append('module', this.moduleAdd);

        var request = await this.$store.dispatch('modules/addModule', {data: thing, id:this.app.Id});
        if(!request.success){
          this.$toastr.error(request.data, 'Error');
        }else{
          this.moduleAdd = null,
          this.$emit('refreshApp', true);
          this.$toastr.success('Modulo agregado exitosamente', 'Exitoso');
        }
      loader.hide();
    }
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
    optionsModules:{
      get() {
        var data = [];
        var request = this.$store.getters['modules/getterModulesOptions'];
        if(request){
          request.map((item) => {
            data.push({ label: item.name, value: item.id });
          })
        }
        return data;
      }
    }
  },
}
</script>

<style lang="scss" scoped>
// Styles addmodule
.card-title{
  font-size: 1.5rem;
}
.info-box-text{
  text-overflow: none;
  white-space: normal;
}
.progress-description{
  text-overflow: none;
  white-space: normal;
}
</style>
