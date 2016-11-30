import Vue from 'vue'
import Vuex from 'vuex'
import createLogger from 'vuex/dist/logger'
import users from './users/store'
import rooms from './rooms/store'

Vue.use(Vuex)

const debug = true

export default new Vuex.Store({
  modules: {
    users,
    rooms
  },
  strict: debug,
  plugins: debug ? [createLogger()] : []
})
