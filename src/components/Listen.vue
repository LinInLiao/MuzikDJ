<template lang="pug">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    .mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      rooms(:rooms="rooms")
</template>

<script>
import { mapActions } from 'vuex'
import Rooms from '@/components/Rooms.vue'

export default {
  name: 'listen',
  components: {
    rooms: Rooms
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      vm.listenRooms().then((res) => {
        vm.rooms = res
      })
    })
  },
  methods: mapActions({
    listenRooms: 'rooms/listenRooms'
  }),
  data () {
    return {
      rooms: []
    }
  }
}
</script>
