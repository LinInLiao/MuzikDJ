<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid.m-section__header
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__header
      h1.e-slogan.m-inline--align-center.m-font__lato--thin.color--light-blue Search Room
      form.m-join-room__form
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#search.mdl-textfield__input(type="text", ng-model="keyword")
          label.mdl-textfield__label(for="search") Room"s name
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent(type="button", ng-click="search()") Search
        br
        span.s-error-message.m-font__lato--thin(ng-if="error === 'keyword'") Room name is required.
  .mdl-grid
    section.mdl-cell.mdl-cell--4-col.mdl-cell--12-col-phone(v-key="room in rooms")
      rooms(:room="room")
</template>

<script>
import Rooms from './Rooms.vue'

export default {
  name: 'search',
  components: {
    rooms: Rooms
  }
}
</script>
