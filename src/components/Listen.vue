<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    .mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      .mdl-grid
        section.mdl-cell.mdl-cell--4-col.mdl-cell--12-col-phone
          mz-rooms(v-for="room in rooms", :room="room")
</template>

<script>
import Rooms from './Rooms.vue'

export default {
  name: 'listen',
  components: {
    'mz-rooms': Rooms
  },
  data () {
    return {
      rooms: []
    }
  }
}
</script>
