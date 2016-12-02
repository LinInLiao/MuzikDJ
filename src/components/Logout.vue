<template>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'logout',
  beforeRouteEnter (to, from, next) {
    next(vm => {
      vm.logout().then(() => {
        vm.$router.push({ name: 'homepage' })
      })
    })
  },
  methods: mapActions(['logout'])
}
</script>

