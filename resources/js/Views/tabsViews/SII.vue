<template>
  <div class="p-0 m-0">
    <div v-if="siiInstaller" class="row m-0 mb-2">
      <div class="col-md-5 col-12 order-md-2">
        <div class="card card-widget widget-user-2 m-0">
          <div class="card-header bg-one">
            <h3 class="card-title card-title-padding">Cargar folio</h3>

            <div class="card-tools">
              <button type="button" class="btn btn-tool text-white" data-card-widget="collapse"><i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="form-group w-100 my-2">
              <label for="xml">Folios en XML</label>
              <textarea v-model="xml_string" class="form-control rounded-0" id="xml" rows="5"></textarea>
              <input type="file" id="filexml" ref="xmlFile" accept="text/xml" @change="XMLToString" style="display: none">
            </div>
            <a @click="sendFolios(false)" class="btn bg-one text-white mt-3 mx-1 text-bold" style="float:right;">Cargar folios</a>
            <label for="filexml" class="btn bg-one text-white mt-3 mx-1 text-bold" style="float:right;">Subir folios</label>
          </div>
        </div>
      </div>
      <div class="col-md-7 col-12">
        <folios-table v-model="folios" />
      </div>
    </div>
    <div class="m-0 my-2 text-center w-100" v-else>
      <h5>El modulo de SII no se encuentra activado</h5>
    </div>
  </div>
</template>

<script>
import foliosTable from '../../Components/table/foliosTable.vue';
export default {
  data(){
    return{
      xml_string: null,
      folios: [
        {label:'Facturas', value: 0},
        {label:'Boletas', value: 0},
        {label:'Notas de credito', value: 0},
        {label:'Guia de Despacho', value:0}
      ]
    }
  },
  components:{
    foliosTable
  },
  props:['siiInstaller'],
  destroyed(){
    this.folios = [
      {label:'Facturas', value: 0},
      {label:'Boletas', value: 0},
      {label:'Notas de credito', value: 0},
      {label:'Guia de Despacho', value:0}
    ]
  },
  methods:{
    // Cargar folios
    async sendFolios(xml_string = false){
      if(xml_string !== false) this.xml_string = xml_string;

      // Verificando campo
      if(this.xml_string == '' || this.xml_string == null) return this.$toastr.error('Por favor inserte un xml', 'Error');

      let loader = this.$loading.show({
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
      var data = new FormData();
      data.append('xml_string', this.xml_string);
      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/sendFolios', {id:this.app.Id, data});
      // Verificando datos
      if(!request.success) this.$toastr.error(request.data, 'Error');
      else{
        this.xml_string = null;
        this.$refs.xmlFile.files = null;
        await this.getFolios();
        this.$toastr.success(request.data, 'Exitoso');
      }
      loader.hide();
    },
    // Traer los folios
    async getFolios(){
      this.folios.map((key)=>{
        key.value = 0;
      });
      // Iniciando peticion
      var request = await this.$store.dispatch('aplication/getFolios', this.app.Id);
      // Verificando datos
      if(!request.success) this.$toastr.error(request.data, 'Error');
      if(request.data.length > 0){
        request.data.map((item)=>{
          if(item.type == 'factura') this.folios[0].value = this.folios[0].value + 1
          if(item.type == 'boleta') this.folios[1].value = this.folios[1].value + 1
          if(item.type == 'nota_de_credito') this.folios[2].value = this.folios[2].value + 1
        });
      }
    },

    async XMLToString(){
      var fileInInput = this.$refs.xmlFile.files[0];
      var reader = new FileReader();
      var _this = this;

      var file = reader.onload = ((theFile) => {
        return async function(e) {
          await _this.sendFolios(e.target.result);
        }
      })(fileInInput);

      reader.readAsText(fileInInput);

    }
  },
  watch:{
    siiInstaller(value){
      if(value || value == true){
        this.getFolios();
      }
    }
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>
