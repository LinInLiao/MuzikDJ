import * as types from '../mutation-types'
import * as actions from './actions'
import * as getters from './getters'

const state = {
  data: {},
  token: ''
}

const mutations = {
  [types.USER_DATA] (state, data) {
    state.data = data
  },
  [types.USER_TOKEN] (state, data) {
    state.token = data
  }
}

export default {
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}
