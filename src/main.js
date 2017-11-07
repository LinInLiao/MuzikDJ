import Vue from 'vue'
import App from './App'
import router from './router'
import store from './vuex'
import VideoBackground from './plugins/youtube-player'

Vue.config.productionTip = false
Vue.use(VideoBackground)

/* eslint-disable no-new */
new Vue({
  el: '#app',
  store,
  router,
  render: h => h(App)
})
