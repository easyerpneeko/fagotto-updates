<template>
  <div class="bg-home ">
    <div class="pt-5 pb-3 px-3 px-sm-5">
      <div id="ofBar">
        <div id="ofBar-content">
          <b>Hey {{ this.me.fullname }} !</b> No olvides cerrar tu turno antes de cuadrar la caja. ¡Gracias!
        </div>
      </div>
    </div>
    <div :class="['bg-parallax-home', (feeds != 'Failed to fetch' && feeds && feeds.length > 0) ? '' : 'bg-full-height']">
      <div class="rgbaBlack d-flex flex-column justify-content-center align-items-center py-5">
        <img class="home_logo" src="../assets/logo.png" alt="easy-erp">
        <h2 class="text-bienvenida">
          Bienvenido a tu mejor aplicación de gestión en negocios
        </h2>
      </div>
    </div>    

    <div v-if="feeds != 'Failed to fetch' && feeds && feeds.length > 0" class="pt-5 pb-3 px-3 px-sm-5">
      <h4 class="mb-4">Ultimas notícias</h4>

      <div class="mx-sm-2 m-0 p-0" v-for="(feed, index) in feeds" :key="index">
        <feed-card :feed="feed" />
      </div>
    </div>

    <div v-if="feeds != 'Failed to fetch' && feeds && feeds.length > 0" class="pt-5 pb-3 px-3 px-sm-5">
      <h4 class="mb-4">Folios Disponibles</h4>

      <div class="container">
        <div class="row">
            <div class="col-md-4 col-xl-3">
                <div class="card bg-c-blue order-card">
                    <div class="card-block">
                        <h6 class="m-b-20">Facturas</h6>
                        <h2 class="text-right"><i class="fa fa-file-alt f-left"></i><span>{{ this.foliosFactura }}</span></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-xl-3">
                <div class="card bg-c-green order-card">
                    <div class="card-block">
                        <h6 class="m-b-20">Boletas</h6>
                        <h2 class="text-right"><i class="fa fa-file-contract f-left"></i><span>{{this.foliosBoleta}}</span></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-xl-3">
                <div class="card bg-c-yellow order-card">
                    <div class="card-block">
                        <h6 class="m-b-20">Nota de credito</h6>
                        <h2 class="text-right"><i class="fa fa-file-invoice f-left"></i><span>{{this.foliosNotaCredito}}</span></h2>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 col-xl-3">
                <div class="card bg-c-pink order-card">
                    <div class="card-block">
                        <h6 class="m-b-20">Guia de Despacho</h6>
                        <h2 class="text-right"><i class="fa fa-file-import f-left"></i><span>{{this.foliosGuiaDespacho}}</span></h2>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>

    <div class="pt-5 pb-3 px-3 px-sm-5">  
      <div  class="row m-0 mb-2" v-if="this.siiInstalled">
        
        <div class="col-md-12 col-12 order-md-2">
          <div class="card card-widget widget-user-2 m-0">
            <div class="card-header bg-one">
              <h3 class="card-title card-title-padding">Cargar folio</h3>
            </div>
            <div class="card-body">
              <div class="form-group w-100 my-2">
                <label for="xml">Folios en XML</label>
                <!-- v-model="xml_string" -->
                <textarea  class="form-control rounded-0" id="xml" rows="5"></textarea>
                <!-- @change="XMLToString" -->
                <input type="file" id="filexml" ref="xmlFile" accept="text/xml"  @change="XMLToString" style="display: none">
              </div>
              <!-- @click="sendFolios(false)" -->
              <a  class="btn bg-one text-white mt-3 mx-1 text-bold" @click="sendFolios(false)" style="float:right;">Cargar folios</a>
              <label for="filexml" class="btn bg-one text-white mt-3 mx-1 text-bold" style="float:right;">Subir folios</label>
            </div>
          </div>
        </div>
      </div>
      <!-- v-else -->
      <div class="m-0 my-2 text-center w-100" v-else>
        <h5>El modulo de SII no se encuentra activado</h5>
      </div>
    </div>

  </div>
</template>

<script>
import feedCard from '@/components/cards/feedCard.vue';
import Loader from '@/helpers/Loader';
import ConfigHelper from '@/helpers/ConfigHelper.js';

export default {
  name:'home',
  props:['value','feedsWatch'],
  components:{ feedCard },
  data(){return {
    foliosFactura:      0,
    foliosBoleta:       0,
    foliosNotaCredito:  0,
    foliosGuiaDespacho: 0,
    xml_string:         null,
    app_id:             0
  }},
  async mounted(){

    if(this.feedsWatch){
      this.offOn = true;
      Loader.dinamic();
        await this.$store.dispatch('main/getFeeds');
        await this.getFolios()        
      Loader.hide();
      this.offOn = false;
    }else{
      this.feeds = null;
    }
    console.log('USUARIO LOGUEADO', this.me);
    
  },
  computed:{
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input',offOn) }
    },
    feeds:{
      get(){ return this.$store.getters['main/getFeeds'] },
      set(val){ return this.$store.commit('main/setProperty', {key:'feeds', data: val}) }
    },
    me:{ get(){ return this.$store.getters['main/user']; } },

    siiInstalled:{ async get(){  return await ConfigHelper.ConfStr('modulos.ventas.submodulos.sii'); } },

  },
  watch:{
    async feedsWatch(val){
      if(val) {
        this.offOn = true;
          await this.$store.dispatch('main/getFeeds');
          await this.getFolios()
          console.log('SII:',this.siiInstalled);
        this.offOn = false;
      }
    }
  },
  methods:{
    countFolios(request){
      if(request.data.length > 0){
          request.data.map((item)=>{
            if(item.type == 'factura') this.foliosFactura++;
            if(item.type == 'boleta') this.foliosBoleta++;
            if(item.type == 'nota_de_credito') this.foliosNotaCredito++;
            if(item.type == 'guia_de_despacho') this.foliosGuiaDespacho++;
          });
        }
    },
    // Cargar folios
    async sendFolios(xml_string = false){
      if(xml_string !== false) this.xml_string = xml_string;

      // Verificando campo
      // if(this.xml_string == '' || this.xml_string == null) return this.$toastr.error('Por favor inserte un xml', 'Error');
      if(this.xml_string == '' || this.xml_string == null) console.log('El campo esta vacio');

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
      // id:this.app.Id
      var request = await this.$store.dispatch('main/sendFolios', {id:this.app_id, data});
      // Verificando datos
      // if(!request.success) this.$toastr.error(request.data, 'Error');
      if(!request.success) console.log('Error: ',request.data);
      else{
        this.xml_string = null;
        this.$refs.xmlFile.files = null;
        await this.getFolios()
        // this.$toastr.success(request.data, 'Exitoso');
        console.log('Exitoso');
      }
      loader.hide();
    },
    // Traer los folios
    async getFolios(){
      // this.folios.map((key)=>{
      //   key.value = 0;
      // });
      // Iniciando peticion
      let app = await this.getApp();
      console.log('app id:', this.app_id);
      var request = await this.$store.dispatch('main/getFolios', this.app_id);
      // Verificando datos
      this.countFolios(request);
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

    },

    async getApp(){
      var request = await this.$store.dispatch('main/refreshData', '?slim');
      this.app_id = request.data.Id;
      return request;
    }
  }
}
</script>
<style scoped>
#ofBar {
  background-color: #192b5f ;
  color: #fff;
  padding: 10px;
  text-align: center;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  display: flex; /* Cambio: Usar display flex para alinear elementos internos */
  justify-content: space-between; /* Cambio: Espaciado uniforme entre elementos internos */
  align-items: center; /* Cambio: Alinear elementos verticalmente al centro */
}

#ofBar-logo img {
  max-width: 100px;
}

#ofBar-content {
  font-size: 18px;
  flex: 1; /* Cambio: Permitir que el contenido ocupe el espacio restante */
}

#ofBar-right {
  display: flex;
  align-items: center;
}

#btn-bar {
  background-color: #27ae60;
  color: #fff;
  padding: 8px 15px;
  text-decoration: none;
  margin-left: 10px; /* Cambio: Ajustar margen izquierdo para separar el botón del texto */
  border-radius: 5px;
}

#btn-bar:hover {
  background-color: #2ecc71;
}

#close-bar {
  cursor: pointer;
  font-size: 20px;
}
.order-card {
    color: #fff;
}

.bg-c-blue {
  background: linear-gradient(45deg,#06192f,#4da0ff);
}

.bg-c-green {
  background: linear-gradient(45deg,#006d1d,#4fc38e);
}

.bg-c-yellow {
  background: linear-gradient(45deg,#a26000,#ffb54b);
}

.bg-c-pink {
  background: linear-gradient(45deg,#731a22,#ec0000);
}


.card {
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
    box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
    border: none;
    margin-bottom: 30px;
    -webkit-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
}

.card .card-block {
    padding: 25px;
}
.card-title {
    float: left;
    font-size: 1.1rem;
    font-weight: 400;
    margin: 0;
}

.order-card i {
    font-size: 26px;
}

.f-left {
    float: left;
}

.f-right {
    float: right;
}
.bg-one {
    background-color: var(--primary);
    color: #fff!important;
}
</style>