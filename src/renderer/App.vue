<template>
  <div id="app">
    <router-view :version="version"></router-view>
  </div>
</template>

<script>
import State from "./store/main/state";
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';
import ConfigHelper from '@/helpers/ConfigHelper.js';
const fs = require('fs');
export default {
  name: 'fagotto-cafe-aplication',
  data() {
    return{
      version: State.versionNumber
    }
  },
  created(){
    this.success = false;
    console.log('APP INITIALIZED IN APP.VUE')
    ConfigHelper.readAppFile((err, data) => {
      if(err) {
        fs.unlink('authorization.json',(err) => {
            if (err) return;
            console.log('Authorization.json eliminado correctamente');
        });
        if (this.$route.path != '/') this.$router.push('/');
        return;
      };
      //Si el archivo se leyo correctamente, saltar al login
      console.log('EJECUCION DE ConfigHandler DE SERIAL LOCAL');
      ConfigHelper.ConfigHandler(false, JSON.parse(data), false);
      ConfigHelper.readAppFile(async (err, data) => {
        if(err){
          console.log('Error al leer el archivo de autorizacion');
          if (this.$route.path != '/login'){
            this.$router.push('/login');
          }
          return;
        }

        console.log('Se Leyo el archivo de authorization.json')
        //Si el archivo se leyo correctamente, llena los headers

        Connection.fillHeaders(JSON.parse(data));
        this.$echoHelper.fillHeaders(JSON.parse(data));
        /*
          Se hace un request para solicitar el usuario y para comprobar
          que enrealidad funcione el authorization key
        */

        let getMe = await Connection.request('get',BaseUrl.getUrl('api/local/me'));
        console.log('Respuesta inmediata de pedir usiuario')
        console.log(Object.assign(getMe));
        if (!getMe.success){
          console.log('Error al leer el usuario ');
          Connection.desfillHeaders();
          this.$echoHelper.desfillHeaders();
          return;
        }

        this.$store.commit('main/setProperty',{ key:'user' , data: getMe.data });

        console.log('usuario leido con exito');
/**/
        console.log('Si hay usuario');
        if (this.$route.path != '/inicio'){
          this.$router.push('/inicio');
        }
      },'authorization.json');
    });
  }
}
</script>
