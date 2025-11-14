<template>
  <div class="tab_container">
    <!-- Tabs btn -->
    <template v-for="(tab, index) in tabs">
      <input :disabled="tab.disabled" class="input_tab" :id="tab.id" type="radio" name="tabs" :checked="(tab.id == 'tab1') ? true : false">
      <label :class="['label_tab',{'disabled': tab.disabled}]" :for="tab.id">
        <i :class="['fa', tab.icon]"></i>
        <div>{{tab.label}}</div>
      </label>
    </template>

    <!-- tabs sections -->
    <section id="content1" class="section_tab tab-content">
      <h3 class="title">{{app.Name}}</h3>
      <home-section />
    </section>

    <section id="content2" class="section_tab tab-content">
      <h3 class="title">Módulos actuales</h3>
      <modules-section @refreshApp="refreshApp" />
    </section>

    <section id="content3" class="section_tab tab-content">
      <h3 class="title">Usuarios</h3>
      <users-section />
    </section>

    <section id="content4" class="section_tab tab-content">
      <h3 class="title">Modulo SII</h3>
      <sii-section :appId="this.appId" :siiInstaller="siiInstaller" />
    </section>
  </div>
</template>

<script>
import homeSection from './tabsViews/home.vue';
import modulesSection from './tabsViews/modules.vue';
import usersSection from './tabsViews/users.vue';
import siiSection from './tabsViews/SII.vue';

export default {
  data(){
    return{
      siiInstaller: false,
      tabs:[
        {id:'tab1', icon:'fa-home', label:'Inicio'},
        {id:'tab2', icon:'fa-box', label:'Modulos'},
        {id:'tab3', icon:'fa-users', label:'Usuarios'},
        {id:'tab4', icon:'fa-box', label:'SII'},
      ]
    }
  },
  mounted(){
    if(this.appId == null) {
      this.$router.push('/admin/inicio');
    }else{
      this.getApp();
    }
  },
  components:{
    homeSection,
    modulesSection,
    usersSection,
    siiSection
  },
  props:{
    appId:{
      type: [Number, String],
      default: null
    }
  },
  methods: {
    async refreshApp(key = true){
      if(key || key == true){
        var request = await this.$store.dispatch('aplication/getAplication', this.appId);
      }

      var sii = false;
      this.app.Modules.map((key)=>{
        if(key.name == 'Ventas'){
          key.sub.map((sub)=>{
            if(sub.name == 'SII') sii = true;
          });
        }
      });

      if(sii) this.siiInstaller = true;
      else this.siiInstaller = false;
    },
    // Obteniendo app
    async getApp(){
      let loader = this.$loading.show({
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
      // Iniciando peticion (app y usuarios)
      var request = await this.$store.dispatch('aplication/getAplication', this.appId);
      console.log("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!",request);
      if(request) {
        await this.$store.dispatch('aplication/getUserApp', this.appId);
        await this.$store.dispatch('aplication/getTypeUser', this.appId);

        var sii = false;
        this.app.Modules.map((key)=>{
          if(key.name == 'Ventas'){
            key.sub.map((sub)=>{
              if(sub.name == 'SII') sii = true;
            });
          }
        });

        if(sii) this.siiInstaller = true;
        else this.siiInstaller = false;
        // Verificando datos
        if(!request.success){
          this.$router.push('/admin/inicio');
          this.$toastr.error(request.data, 'Error');
        }
      }else{
        this.$router.push('/admin/inicio');
      }
      loader.hide();
    },
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>

<style lang="scss">
// Styles home
.info-box{
  margin-bottom: 0px !important;
}
.info-box-icon{
  font-size: 50px !important;
}


/*Fun begins*/
.tab_container {
	width: 100%;
	margin: 0 auto;
  min-height: 100%;
}

.input_tab, .section_tab {
  clear: both;
  padding-top: 10px;
  display: none;
}

.label_tab {
  font-weight: 700;
  font-size: 17px;
  display: block;
  float: left;
  width: 25%;
  padding: .7em;
  color: #757575;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  background: #f0f0f0;
  margin: 0px;
}

#tab1:checked ~ #content1,
#tab2:checked ~ #content2,
#tab3:checked ~ #content3,
#tab4:checked ~ #content4,{
  display: block;
  padding: 10px;
  background: #f4f6f9;
  min-height: 100%;
}

.tab_container .tab-content .title, .tab_container .tab-content div {
  -webkit-animation: fadeInScale 0.3s ease-in-out;
  -moz-animation: fadeInScale 0.3s ease-in-out;
  animation: fadeInScale 0.3s ease-in-out;
}
.tab_container .tab-content .title  {
  text-transform: capitalize;
  text-align: center;
  margin-bottom: 15px;
  color: #3e3e3e;
}

.tab_container [id^="tab"]:checked + label {
  background: #f4f6f9;
  box-shadow: inset 0 3px #0B4F6C;
}

.tab_container [id^="tab"]:checked + label .fa {
  color: #0B4F6C;
}

.tab_container [id^="tab"] + label {
  transition: .4s all ease;
}
.tab_container [id^="tab"] + label .fa {
  transition: .4s all ease;
}

.tab_container [id^="tab"]:hover + label {
  background: #f4f6f9;
  box-shadow: inset 0 3px #0B4F6C;
}

.tab_container [id^="tab"]:hover + label .fa {
  color: #0B4F6C;
}

.label_tab .fa {
  font-size: 1.2em;
  margin: 0;
}

/*Media query*/
@media (max-width: 1024px) {
  .label_tab {
    font-size: 16px;
  }
}

@media (max-width: 720px) {
  .label_tab div {
    display: none;
  }
}

/*Content Animation*/
@keyframes fadeInScale {
  0% {
  	transform: scale(0.6);
  	opacity: 0;
  }
  25% {
  	transform: scale(0.7);
  	opacity: 0.25;
  }
  50% {
  	transform: scale(0.8);
  	opacity: 0.5;
  }
  75% {
  	transform: scale(0.9);
  	opacity: 0.75;
  }
  100% {
  	transform: scale(1);
  	opacity: 1;
  }
}

</style>
