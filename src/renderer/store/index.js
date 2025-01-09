import Vue from 'vue'
import Vuex from 'vuex'

import { createPersistedState, createSharedMutations } from 'vuex-electron'

// import modules from './modules'
import main from './main';
import products from './products';
import reposteria from './reposteria';
import roles from './roles';
import sells from './sells';
import reports from './reports';
import expenses from './expenses';
//Pedidos
import requests from './requests';
//Operaciones
import operations from './operations';
//Devoluciones
import devolutions from './devolutions';

// Modulo de cafeteria
import cafeteria from './cafeteria/cafeteria';
import waiters from './cafeteria/waiters';
import boards from './cafeteria/boards';
import co_mobile_devices from './client_orders/mobile_devices';
import cafeteria_kitchen from './cafeteria/kitchen';
import clients from './clients';
import guia from './guia';

Vue.use(Vuex)
export default new Vuex.Store({
  modules: {
    main,
    products,
    reposteria,
    roles,
    sells,
    cafeteria,
    waiters,
    boards,
    reports,
    expenses,
    requests,
    operations,
    devolutions,
    'cafeteria/kitchen': cafeteria_kitchen,
    'client_orders/mobile_devices': co_mobile_devices,
    clients,
    guia
  },
  plugins: [
    createPersistedState(),
    // createSharedMutations()
  ],
  strict: false/*process.env.NODE_ENV !== 'production'*/
})
