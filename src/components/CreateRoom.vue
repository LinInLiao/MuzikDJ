<template lang="pug">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    section.mdl-cell.mdl-cell--3-col.mdl-cell--4-col-tablet.mdl-cell--12-col-phone.m-box--align-center
      form.m-create-room__form
        h2.e-slogan.color--light-blue.m-font__lato--thin.m-inline--align-center Create Room
        span.e-label Room Type
        label.mdl-radio.mdl-js-radio.mdl-js-ripple-effect(for="public")
          input#public.mdl-radio__button(type="radio", name="room_type", v-model="roomType", value="public")
          span.mdl-radio__label.m-font__lato--thin Public
        label.mdl-radio.mdl-js-radio.mdl-js-ripple-effect(for="private")
          input#private.mdl-radio__button(type="radio", name="room_type", v-model="roomType", value="private")
          span.mdl-radio__label.m-font__lato--thin Private
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#room_id.mdl-textfield__input.m-font__lato--thin(type="text", v-model="roomId")
          label.mdl-textfield__label.m-font__lato--thin(for="room_id") Room ID
        span.s-error-message.m-font__lato--thin(v-if="error === 'room_id'") Room ID is required.
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label(v-show="passwordField")
          input#password.mdl-textfield__input.m-font__lato--thin(type="password", v-model="roomPassword")
          label.mdl-textfield__label.m-font__lato--thin(for="password") Password
        span.s-error-message.m-font__lato--thin(v-if="error === 'password'") Password is required for private room.
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent.m-font__lato--thin(type="button", @click="create()") Create
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'create-room',
  computed: {
    passwordField () {
      return this.roomType === 'private'
    }
  },
  methods: {
    create () {
      this.createRoom({
        roomType: this.roomType,
        roomId: this.roomId,
        roomPassword: this.roomPassword
      }).then((res) => {
        this.$router.push({
          name: 'singleRoom',
          params: {
            alias: res
          }
        })
      }, (err) => {
        this.$swal({
          title: 'Oops!',
          type: 'error',
          text: err.message
        })
      })
    },
    ...mapActions({
      createRoom: 'rooms/createRoom'
    })
  },
  data () {
    return {
      roomType: 'private',
      roomId: '',
      roomPassword: '',
      error: ''
    }
  }
}
</script>
