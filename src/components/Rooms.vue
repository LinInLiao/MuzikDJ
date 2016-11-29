<template lang="jade">
section
  .mdl-cell.mdl-cell--4-col.mdl-cell--12-col-phone(v-for="room in rooms")
    .mdl-card.mdl-shadow--2dp.listen-rooms-card(v-bind:style="roomCover(room)")
      .mdl-card__title.mdl-card--expand
        h4.e-slogan.m-font__lato--thin {{ room.name }}
        h6.s-private__room.m-font__lato--thin(v-if="room.status === 'private'") private
      .mdl-card__actions.mdl-card--border
        a.mdl-button.mdl-button--colored.mdl-js-button.mdl-js-ripple-effect.m-font__lato--thin(v-bind:href="'/room/' + room.alias") Enter
        a.mdl-button.mdl-button--colored.mdl-js-button.mdl-js-ripple-effect.m-font__lato--thin(@click="removeRoom(room.id)", v-if="room.owner === user.id") Delete
</template>

<script>
export default {
  props: {
    rooms: {
      type: Array,
      default () {
        return []
      }
    }
  },
  methods: {
    roomCover (room) {
      return {}
    },
    removeRoom () {

    }
  }
}
</script>
