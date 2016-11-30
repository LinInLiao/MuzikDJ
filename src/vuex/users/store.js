import {
  USER_DATA,
  USER_TOKEN
} from '../mutation-types'

import * as actions from './actions'
import * as getters from './getters'

const state = {
  data: {},
  token: ''
}

const mutations = {
  [USER_DATA] (state, data) {
    state.data = data
  },
  [USER_TOKEN] (state, data) {
    state.token = data
  }
}

export default {
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}
