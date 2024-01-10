<template>
  <div class="landing-bg">
    <div class="d-flex w-100 h-100 flex-column align-items-center py-2 justify-content-center">
      <div class="text-center mb-xl-4">
        <img src="@/assets/logo.png" alt="Logo" class="brand-image2">
        <h4 class="logo-text">Sincronizacion de aplicaciones</h4>
      </div>
      <div class="shadow-down card mt-4 mb-xl-4">
        <div class="card-body d-flex flex-column w-100 align-items-center">
          <h5>Serial de la aplicacion</h5>
          <div class="form-group w-100 px-3 mt-2">
            <input type="text" class="form-control text-center" v-model="serial"
            @keyup.enter="sendSerial" :disabled="waitResponse" id="serialInput">
          </div>
          <button type="button" class="mt-2 btn btn-serial" @click="sendSerial" :disabled="waitResponse">Establecer</button>
        </div>
      </div>
      <div class="shadow-down border-r mt-4 info-card p-3 d-flex align-items-center">
        <div class="col-7 text-center">
          <span>Introduzca el serial asignado para sincronizar su cliente local con su aplicacion en la nube</span>
        </div>
        <div class="col-5 text-center">
          <i class="fas fa-cloud cloud-icon"></i>
        </div>
      </div>
      <br />
      <div class="gray-text-version" v-if="true">
        App in {{ appInProduction }} || Versión {{ version }}
      </div>
      <p id="version"></p>
      <div id="notification" class="hidden">
        <p id="message"></p>
        <button id="close-button" @click="closeNotification()">
          Close
        </button>
        <button id="restart-button" @click="restartApp()" class="hidden">
          Restart
        </button>
      </div>
    </div>
  </div>
</template>

<script>

import ConfigHelper from '../helpers/ConfigHelper.js';
import Loader from '@/helpers/Loader';

const remote = require('electron').remote;
const Inputmask = require('inputmask');
const $ = require('jquery');
const { ipcRenderer } = require('electron');

export default {
  name: 'landing-page',
  components: { },
  data(){
    return{
      serial: '',
      w: remote.getCurrentWindow(),
      waitResponse: false,
      appInProduction: process.env.NODE_ENV
    }
  },
  props:['version'],
  created(){

    ConfigHelper.readAppFile((err, data) => {
      if(err) return;
      //Si el archivo se leyo correctamente, saltar al login
      ConfigHelper.ConfigHandler(false, JSON.parse(data), false);
    });
  },
  mounted(){
    // var serialInput = document.getElementById("serialInput");
    // var im = new Inputmask("****-****-****-****-****");
    // im.mask(serialInput);
    console.log('mounted');
    const version = document.getElementById('version');
    ipcRenderer.send('app_version');
    ipcRenderer.on('app_version', (event, arg) => {
      ipcRenderer.removeAllListeners('app_version');
      version.innerText = 'Version ' + arg.version;
    });

    const notification = document.getElementById('notification');
    const message = document.getElementById('message');
    const restartButton = document.getElementById('restart-button');
    ipcRenderer.on('update_available', () => {
      console.log('update available');
      ipcRenderer.removeAllListeners('update_available');
      message.innerText = 'A new update is available. Downloading now...';
      notification.classList.remove('hidden');
    });
    ipcRenderer.on('update_downloaded', () => {
      console.log('update downloaded');
      ipcRenderer.removeAllListeners('update_downloaded');
      message.innerText = 'Update Downloaded. It will be installed on restart. Restart now?';
      restartButton.classList.remove('hidden');
      notification.classList.remove('hidden');
    });

  },
  methods: {
    async sendSerial(){
      this.waitResponse = true;
      Loader.fullPage();
      let request = await this.$store.dispatch("main/sendSerial",this.serial);
      console.log(request);
      if (request.success) {
        ConfigHelper.writeFile(request.data, 'aplication.json', (err) => {
          if(err){
            this.$awn.alert(err);
            return;
          }
          // console.log(request.data);
          this.$awn.success('Archivo creado',{labels:{success:'CORRECTO'}});
          this.w.reload();
        });
      }else{
        Loader.hide();
        this.waitResponse = false;
        this.$awn.alert(request.data);
      }
    },
    closeNotification() {
      notification.classList.add('hidden');
    },
    restartApp() {
      ipcRenderer.send('restart_app');
    }
  }
}
</script>

<style media="screen">
  .gray-text-version {
    color: gray;
  }
  #notification {
    position: fixed;
    bottom: 20px;
    left: 20px;
    width: 200px;
    padding: 20px;
    border-radius: 5px;
    background-color: white;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
  }
  .hidden {
    display: none;
  }
</style>
