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
  </div>
</template>

<script>
import feedCard from '@/components/cards/feedCard.vue';
import Loader from '@/helpers/Loader';

export default {
  name:'home',
  props:['value','feedsWatch'],
  components:{ feedCard },
  async mounted(){
    if(this.feedsWatch){
      this.offOn = true;
      Loader.dinamic();
        await this.$store.dispatch('main/getFeeds');
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
  },
  watch:{
    async feedsWatch(val){
      if(val) {
        this.offOn = true;
          await this.$store.dispatch('main/getFeeds');
        this.offOn = false;
      }
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
</style>