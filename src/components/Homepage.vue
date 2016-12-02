<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid.m-section__header
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__header
      h1.e-slogan.m-inline--align-center.m-font__lato--thin.color--light-blue Share Music, Kinda Glee.
      form.m-join-room__form
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#room.mdl-textfield__input(type="text", v-model="roomName")
          label.mdl-textfield__label(for="room") Room ID
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent(type="button", @click="join()") Join
  .mdl-grid.m-section__navigation
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__navigation
      h3.e-slogan.m-font__lato--thin.color--light-blue.m-- MuzikDJ
      .mdl-grid.pt-
        .mdl-cell.mdl-cell--3-col
          a.m-link__card(href="/create/room")
            .mdl-card.mdl-shadow--2dp.muzikdj-card
            span.e-description.m-font__lato--thin Create Room
        .mdl-cell.mdl-cell--3-col
          a.m-link__card(href="/search")
            .mdl-card.mdl-shadow--2dp.muzikdj-card
            span.e-description.m-font__lato--thin Search Room
        .mdl-cell.mdl-cell--3-col
          a.m-link__card(href="/playlist")
            .mdl-card.mdl-shadow--2dp.muzikdj-card
            span.e-description.m-font__lato--thin Add To Playlist
        .mdl-cell.mdl-cell--3-col
          a.m-link__card(href="/listen")
            .mdl-card.mdl-shadow--2dp.muzikdj-card
            span.e-description.m-font__lato--thin Listen Now
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'homepage',
  data () {
    return {
      roomName: ''
    }
  },
  methods: Object.assign(
    mapActions([
      'checkRoom'
    ]),
    {
      join () {
        this.checkRoom(this.roomName).then((res) => {
          this.$router.push({ name: 'singleRoom', params: { alias: res.alias } })
        }, (err) => {
          this.$swal({
            title: 'Oops!',
            type: 'error',
            text: err.message
          })
        })
      }
    }
  )
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
