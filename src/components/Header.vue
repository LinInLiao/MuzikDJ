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
              router-link.m-font__lato--thin.color--light-blue(:to="{ name: 'accountEdit' }") Edit Account
            li
              router-link.m-font__lato--thin.color--light-blue(:to="{ name: 'accountFavorite' }") My Favorite
            li
              router-link.m-font__lato--thin.color--light-blue(:to="{ name: 'accountRooms'} ") My Rooms
            li
              router-link.m-font__lato--thin.color--light-blue(:to="{ name: 'logout' }") Logout
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: Object.assign(
    mapGetters([
      'getUserData',
      'getToken'
    ]),
    {
      isLogin () {
        return typeof this.getUserData !== 'undefined' && typeof this.getUserData.id !== 'undefined'
      }
    },
  ),
  data () {
    return {
      isLogin: false
    }
  }
}
</script>
