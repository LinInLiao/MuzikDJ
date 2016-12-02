<template>
  <div id="app" class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <mz-header></mz-header>
    <router-view></router-view>
    <mz-footer></mz-footer>
    <div id="gaConf" :data-conf="gaOptions"></div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'
import cookies from './plugins/cookies'
import Header from './components/Header.vue'
import Footer from './components/Footer.vue'

export default {
  name: 'app',
  components: {
    'mz-header': Header,
    'mz-footer': Footer
  },
  methods: mapActions(['checkToken', 'setUserToken']),
  created () {
    const token = cookies.get('muzikDJToken')
    if (typeof token !== 'undefined') {
      this.setUserToken(token)
      this.checkToken(token)
    }
  },
  data () {
    return {
      gaOptions: {
      }
    }
  },
  mounted () {
    console.log(this.$el.children[0].parentElement)
  }
}
</script>

<style lang="scss">
@import './styles/style.scss';
</style>

