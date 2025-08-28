import state from './state'
import * as mutations from './mutations'
import * as actions from './actions'
import getters from './getters'

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}
