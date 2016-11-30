import Vue from 'vue'
import VueRouter from 'vue-router'
import swal from 'sweetalert2'
import { sync } from 'vuex-router-sync'
import VueYouTubeEmbed from './plugins/youtube-embed'
import store from './vuex/store'
import App from './App.vue'
import ErrorPage from './components/ErrorPage.vue'
import Homepage from './components/Homepage.vue'
import CreateRoom from './components/CreateRoom.vue'
import Login from './components/Login.vue'
import Signup from './components/Signup.vue'
import Listen from './components/Listen.vue'
import Playlist from './components/Playlist.vue'
import Search from './components/Search.vue'
import Room from './components/Room.vue'
import SingleRoom from './components/SingleRoom.vue'

Vue.use(VueRouter)
Vue.use(VueYouTubeEmbed)
Vue.prototype.$swal = swal

const router = new VueRouter({
  mode: 'history',
  linkActiveClass: 'is-active',
  routes: [
    {
      name: 'login',
      path: '/login',
      component: Login
    },
    {
      name: 'error',
      path: '/error',
      component: ErrorPage
    },
    {
      name: 'signup',
      path: '/signup',
      component: Signup
    },
    {
      name: 'listen',
      path: '/listen',
      component: Listen
    },
    {
      name: 'playlist',
      path: '/playlist',
      component: Playlist
    },
    {
      name: 'search',
      path: '/search',
      component: Search
    },
    {
      name: 'createRoom',
      path: '/create/room',
      component: CreateRoom
    },
    {
      name: 'room',
      path: '/room',
      component: Room,
      children: [
        {
          path: ':alias',
          name: 'singleRoom',
          component: SingleRoom
        }
      ]
    },
    {
      name: 'homepage',
      path: '/',
      component: Homepage
    },
    {
      path: '*',
      component: Homepage
    }
  ]
})

sync(store, router)

/* eslint-disable no-new */
new Vue({
  store: store,
  router: router,
  render: h => h(App)
}).$mount('#app')
