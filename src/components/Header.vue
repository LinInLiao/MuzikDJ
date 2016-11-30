<template lang="jade">
header.mdl-layout__header.mdl-layout__header--transparent
  .mdl-layout__header-row
    a(href="/")
      img.mdl-layout-title(src="../assets/logo.svg")
    .mdl-layout-spacer
    nav.mdl-navigation
      a.mdl-navigation__link.m-font__lato--thin(v-if="!isLogin", href="/login")
        span.color--light-blue Login
      a.mdl-navigation__link.m-font__lato--thin(v-if="!isLogin", href="/signup")
        span.color--light-blue Sign up
      .mdl-navigation__link.m-font__lato--thin.user__name(v-if="isLogin")
        span.m-font__lato--thin.color--light-blue {{ getUserData.name }}
        .user__menu
          ul
            li
              a.m-font__lato--thin.color--light-blue(href="/account/edit") Edit Account
            li
              a.m-font__lato--thin.color--light-blue(href="/account/favorite") My Favorite
            li
              a.m-font__lato--thin.color--light-blue(href="/account/rooms") My Rooms
            li
              a.m-font__lato--thin.color--light-blue(href="/logout") Logout
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: Object.assign(
    {
      isLogin () {
        return typeof this.getUserData !== 'undefined' && typeof this.getUserData.id !== 'undefined'
      }
    },
    mapGetters([
      'getUserData',
      'getToken'
    ]),
  ),
  data () {
    return {
      isLogin: false
    }
  }
}
</script>
