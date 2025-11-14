<template>
  <div class="card">
    <div class="card-header bg-dark">
      <h3 class="card-title card-title-padding">Editar vencimiento</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label>Tiempo</label>
        <div class="select">
          <select class="select_class" v-model="time" name="slct" id="slct">
            <option disabled selected>Selecciona una opción</option>
            <option v-for="(option,index) in optionsDays" :value="option.value">
              {{option.label}}
            </option>
          </select>
        </div>
        <!-- <v-select :options="optionsDays" v-model="time" style="width: 100%;"></v-select> -->

        <a class="btn bg-one text-white mt-3" style="float:right;" @click="sendInfo(1)">Agregar</a>
        <a class="btn bg-dark text-white mt-3 mr-2" style="float:right;" @click="sendInfo(2)">Restar</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data(){
    return{
      time: null,
      optionsDays: [
        {label: '1 mes', value: '1 month'},
        {label: '15 días', value: '15 days'},
        {label: '6 días', value: '6 days'},
        {label: '5 días', value: '5 days'},
        {label: '3 días', value: '3 days'},
        {label: '1 día', value: '1 days'},
        {label: '3 horas', value: '3 hour'},
        {label: '1 hora', value: '1 hour'},
        {label: '30 minutos', value: '30 minutes'},
        {label: '10 minutos', value: '10 minutes'},
      ],
    }
  },
  methods: {
    // Refrescar app
    async refreshApp(){
      console.log("--- Refrescando la app ---");
      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/getAplication', this.app.Id);

      // Verificando datos
      if(!request.success){
        this.$router.push('/admin/inicio');
        this.$toastr.error(request.data, 'Error');
      }
    },
    async sendInfo(type){
        if(this.time == null || this.time == '') return this.$toastr.error('Porfavor inserte una fecha', 'Error');

        let loader = this.$loading.show({
          // Optional parameters
          color: '#007bff',
          width: 80,
          height: 80,
          backgroundColor: '#000000',
          opacity: 0.8,
          zIndex: 9999,
        });

        const data = {
          expiration: this.time,
          type: type,
        };

        var thing = new FormData();
        for (let key in data) if (data[key]) thing.append(key, data[key]);
        var request = await this.$store.dispatch('aplication/addTime',{data: thing, id: this.app.Id});
        loader.hide();

        if(!request.success) this.$toastr.error(request.data, 'Error');
        else{
          this.time = null;
          this.$toastr.success(request.data, 'Exitoso');
          this.refreshApp();
        }
    }
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>

<style lang="scss" scoped>
// Styles editExpired
.card{
  margin: 0 !important;
}
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
