<template lang="pug">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    .mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
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
  beforeRouteEnter (to, from, next) {
    next(vm => {
      vm.fetchAccountRooms().then(() => {
        vm.rooms = vm.getAccountRooms
      })
    })
  },
  methods: mapActions({
    fetchAccountRooms: 'rooms/fetchAccountRooms'
  }),
  computed: mapGetters({
    getAccountRooms: 'rooms/getAccountRooms'
  }),
  data () {
    return {
      rooms: []
    }
  }
}
</script>
