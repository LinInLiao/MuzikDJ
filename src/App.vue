<template>
  <div id="app" class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <mz-header></mz-header>
    <router-view></router-view>
    <mz-footer></mz-footer>
  </div>
</template>

<script>
import Header from './components/Header.vue'
import Footer from './components/Footer.vue'

export default {
  name: 'app',
  components: {
    'mz-header': Header,
    'mz-footer': Footer
  }
}
</script>

<style lang="scss">
@import './styles/style.scss';
</style>
