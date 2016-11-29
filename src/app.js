import Vue from 'vue'
import VueRouter from 'vue-router'
import swal from 'sweetalert2'
import App from './App.vue'
import Homepage from './components/Homepage.vue'
import Login from './components/Login.vue'
import Signup from './components/Signup.vue'
import Listen from './components/Listen.vue'
import Playlist from './components/Playlist.vue'
import Search from './components/Search.vue'

Vue.use(VueRouter)
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

/* eslint-disable no-new */
new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
