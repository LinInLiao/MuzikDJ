import Vue from 'vue'
import Vuex from 'vuex'
import createLogger from 'vuex/dist/logger'
import sessionStorage from './plugins/sessionStorage'
import users from './users/store'
import rooms from './rooms/store'

Vue.use(Vuex)

const debug = process.env.NODE_ENV === 'development'
const plugins = debug ? [createLogger(), sessionStorage()] : [sessionStorage()]

const store = new Vuex.Store({
  modules: {
    users,
    rooms
  },
  strict: debug,
  plugins: plugins
})

if (module.hot) {
  module.hot.accept([
    './users/store',
    './rooms/store'
  ], () => {
    store.hotUpdate({
      modules: {
        users: require('./users/store').default,
        rooms: require('./rooms/store').default
      }
    })
  })
}

export default store
