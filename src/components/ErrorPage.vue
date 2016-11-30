<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid.m-section__header
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__header
      h1.e-slogan.m-inline--align-center.m-font__lato--thin.color--light-blue Oops! There are something wrong.
      form.m-join-room__form
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#room.mdl-textfield__input(type="text", v-model='roomName')
          label.mdl-textfield__label(for="room") Room ID
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent(type="button", @click="join()") Join
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'error',
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
        }, (error) => {
          this.$swal({
            title: 'Oops!',
            type: 'error',
            text: error.message
          })
        })
      }
    }
  )
}
</script>