
console.log('APP INITIALIZED IN MAIN.JS');/**/

import Vue from 'vue'
import axios from 'axios'
import VueAWN from "vue-awesome-notifications";
let options = {};
import 'vue-awesome-notifications/dist/styles/style.css';
Vue.use(VueAWN, options);

import Multiselect from 'vue-multiselect';
Vue.component('multiselect', Multiselect);

import App from './App';
import Layout from './Layout/layout.vue';
import router from './router';
import store from './store';
import JQuery from 'jquery';
import DatePicker from 'vue2-datepicker';
import Autocomplete from '@trevoreyre/autocomplete-vue';
import vSelect from 'vue-select';

import '@trevoreyre/autocomplete-vue/dist/style.css'
Vue.use(Autocomplete)
Vue.use(DatePicker);

window.$ = JQuery;
import mask from 'inputmask';
import '@fortawesome/fontawesome-free/css/all.css';
import '@fortawesome/fontawesome-free/js/all.js';
import "bootstrap/dist/css/bootstrap.min.css";
import "mdbootstrap/css/bootstrap.min.css";

import "mdbootstrap/css/mdb.min.css";
import "mdbootstrap/css/style.css";
import "mdbootstrap/js/bootstrap.min.js";
import "./css/adminlte.css";
import "vue-multiselect/dist/vue-multiselect.min.css";
import 'vue2-datepicker/index.css';
import 'vue-select/dist/vue-select.css';

import EchoHelper, { $pusher, $echo } from './helpers/EchoHelper';
import VersionTracker from './helpers/VersionTracker';
import Chartkick from 'vue-chartkick'
import Chart from 'chart.js'

Vue.use(Chartkick.use(Chart))
Vue.component('v-select', vSelect)
Vue.use(require('bootstrap'))

// Vue.use(require('mdbootstrap'));
import "./css"

import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
Vue.use(Loading);

if (!process.env.IS_WEB) Vue.use(require('vue-electron'))
Vue.http = Vue.prototype.$http = axios
Vue.config.productionTip = false

Vue.Pusher = Vue.prototype.$pusher = $pusher;
Vue.Echo = Vue.prototype.$echo = $echo;
Vue.EchoHelper = Vue.prototype.$echoHelper = EchoHelper;

/* eslint-disable no-new */
new Vue({
  components: { App, Layout },
  router,
  store,
  template: '<App/>',
}).$mount('#app');

// Iniciar tracking de versión automático
// Se enviará la versión cada 5 minutos al servidor
setTimeout(() => {
  VersionTracker.startTracking();
  console.log('📊 Version tracking activado - monitoreando versión cada 5 minutos');
}, 10000); // Esperar 10 segundos después de que cargue la app

