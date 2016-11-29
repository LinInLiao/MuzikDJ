import Vue from 'vue'
import VueRouter from 'vue-router'
import swal from 'sweetalert2'
import App from './App.vue'
import Homepage from './components/Homepage.vue'

Vue.use(VueRouter)
Vue.prototype.$swal = swal

const router = new VueRouter({
  mode: 'history',
  linkActiveClass: 'is-active',
  routes: [
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
