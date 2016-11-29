<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    .mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      .mdl-grid
        rooms(:rooms="rooms")
</template>

<script>
import Rooms from './Rooms.vue'

export default {
  name: 'listen',
  components: {
    rooms: Rooms
  },
  data () {
    return {
      rooms: []
    }
  }
}
</script>
