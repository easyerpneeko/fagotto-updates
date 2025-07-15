<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title text-bold">Folios</h2>
      <div class="card-tools">
        <a @click="getFolios" class="btn btn-block bg-one text-white btn-font-r">
          <i class="fas fa-sync"></i>
          <span class="mobile-modules">Refrescar folios</span>
        </a>
      </div>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped projects">
        <thead>
          <tr>
            <th class="width-name">
              DTC
            </th>
            <th class="width-username">
              Folios disponibles
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in data" :key="index">
            <td class="width-name">
              {{item.label}}
            </td>
            <td class="width-name">
              {{item.value}}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
</template>

<script>
// Helpers
export default {
  name: 'foliosTable',
  methods:{
    // Traer los folios
    async getFolios(){
      let loader = this.$loading.show({
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
      this.data.map((key)=>{
        key.value = 0;
      });

      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/getFolios', this.app.Id);
      // Verificando datos
      if(!request.success) this.$toastr.error(request.data, 'Error');

      if(request.data.length > 0){
        request.data.map((item)=>{
          if(item.type == 'factura') this.data[0].value = this.data[0].value + 1;
          if(item.type == 'boleta') this.data[1].value = this.data[1].value + 1;
          if(item.type == 'nota_de_credito') this.data[2].value = this.data[2].value + 1;
          if(item.type == 'guia_de_despacho') this.data[3].value = this.data[3].value + 1;
        });
      }
      loader.hide();
    }
  },
  props:{
    value:{
      type: Array,
      default: []
    }
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
    data:{
      get(){ return this.value; },
      set(value){ this.$emit('input', value) }
    }
  },
}
</script>

<style scoped>
</style>
