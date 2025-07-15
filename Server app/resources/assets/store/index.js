import Vue from 'vue';
import Vuex from 'vuex';
import general from './general';
import users from './users';
import clients from './clients';
import aplication from './aplication';
import modules from './Modules';
import submodules from './submodules';
import feeds from './feeds';
import products from './products';
import reposteria from './reposteria';
import ingredients from './ingredients';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
  modules: {
    general,
    users,
    clients,
    aplication,
    modules,
    submodules,
    feeds,
    products,
    reposteria,
    ingredients
  },
  strict: debug
});
