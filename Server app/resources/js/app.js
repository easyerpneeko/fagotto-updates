require('./bootstrap');

window.Vue = require('vue');
window.toastr = require('toastr');
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';
import 'vue-select/dist/vue-select.css';
import 'vue-multiselect/dist/vue-multiselect.min.css';

// import js
import Vue from 'vue';
import VueRouter from 'vue-router';
import Store from "../assets/store/store";
import Loading from 'vue-loading-overlay';
import Datepicker from 'vuejs-datepicker';
import vSelect from 'vue-select';
import FlashMessage from '@smartweb/vue-flash-message';
import VueToastr2 from 'vue-toastr-2';
import 'vue-toastr-2/dist/vue-toastr-2.min.css';
import moment from 'moment-timezone';

Vue.component('v-select', vSelect);

Vue.use(Store);
Vue.use(VueRouter);
Vue.use(Store);
Vue.use(Loading);
Vue.use(Datepicker);
Vue.use(FlashMessage);
Vue.use(VueToastr2);

/*const moment = require('moment')
*/


require('moment/locale/es');

moment.tz.setDefault('America/Santiago');


Vue.use(require('vue-moment'), {
  moment
})

import Dashboard from './Views/dashboard';
import Clients from './Views/clients';
import Aplications from './Views/aplications';
import Module from './Views/module';
import Debug from './Views/debug';
import DetailApp from './Views/detailApp';
import AdminFeeds from './Views/adminFeeds';
import Pedidos from './Views/pedidos';
import Reposteria from './Views/reposteria';
import Ingredients from './Views/ingredients';

// ---------------------------------------Rutas de la SPA--------------------------------------- //
const router = new VueRouter({
    mode: 'history',
    routes: [
      {
        path: '/admin/inicio',
        name: 'Dashboard',
        component: Dashboard,
      },
      {
        path: '/admin/dashboard',
        name: 'Anti dashboard',
        component: Debug,
      },
      {
        path: '/admin/clientes',
        name: 'Clients',
        component: Clients,
      },
      {
        path: '/admin/aplicaciones',
        name: 'Aplications',
        component: Aplications,
      },
      {
        path: '/admin/aplicaciones/detalle',
        name: 'detailApp',
        props: (route) => ({ appId: route.query.id }),
        component: DetailApp,
      },
      {
        path: '/admin/modulos',
        name: 'Module',
        component: Module,
      },
      {
        path: '/admin/noticias',
        name: 'AdminFeeds',
        component: AdminFeeds,
      },
      {
        path: '/admin/pedidos',
        name: 'Pedidos',
        component: Pedidos,
      },
      {
        path: '/admin/reposteria',
        name: 'Reposteria',
        component: Reposteria
      },
      {
        path: '/admin/ingredients',
        name: 'Ingredients',
        component: Ingredients
      }
    ],
});
// ---------------------------------------Rutas de la SPA--------------------------------------- //

import BaseUrl from "../assets/helpers/BaseUrl";
import Connection from "../assets/helpers/Connection";
import store from '../assets/store';

const app = new Vue({
  el: '#app',
  components: {
    Datepicker
  },
  router,
  linkActiveClass: 'active',
  methods: {
    getMeta(metaName) {
      const metas = document.getElementsByTagName('meta');

      for (let i = 0; i < metas.length; i++) {
        if (metas[i].getAttribute('name') === metaName) {
          return metas[i].getAttribute('content');
        }
      }

      return '';
    },
  },
  async created() {

    console.log('APP INICIALIZADA');

    this.$store.commit('users/setProperty',{ key:'apiUrl' , data: window.BaseUrl });

    const token = this.getMeta('auth-token');
    if (token == 'token-not-found') {
      window.location.replace(BaseUrl.getUrl("/login"));
      return false;
    }
    const user = JSON.parse(this.getMeta('user-data'));
    Connection.fillHeaders(token);

    this.$store.commit('users/setProperty',{ key:'apiUrl' , data: window.BaseUrl });
    this.$store.commit('users/setProperty',{ key:'token' , data: token });
    this.$store.commit('users/setProperty',{ key:'user' , data: user });

    await this.$store.dispatch('modules/getModules');
    await this.$store.dispatch('clients/getclients');
    await this.$store.dispatch('aplication/getAplications');
    this.$store.dispatch('users/setVerifyIfNotDefined');

  }
});
