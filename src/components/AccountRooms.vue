<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    .mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      .mdl-grid
        rooms(:rooms="rooms")
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import Rooms from './Rooms.vue'

export default {
  name: 'AccountRooms',
  components: {
    rooms: Rooms
  },
  methods: mapActions(['fetchAccountRooms']),
  computed: mapGetters(['getAccountRooms']),
  beforeRouteEnter (to, from, next) {
    next(vm => {
      vm.fetchAccountRooms().then(() => {
        vm.rooms = vm.getAccountRooms
      })
    })
  },
  data () {
    return {
      rooms: []
    }
  }
}
</script>
