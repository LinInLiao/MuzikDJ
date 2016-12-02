<template lang="jade">
header.mdl-layout__header.mdl-layout__header--transparent
  .mdl-layout__header-row
    router-link(:to="{ name: 'homepage' }")
      img.mdl-layout-title(src="../assets/logo.svg")
    .mdl-layout-spacer
    nav.mdl-navigation
      router-link.mdl-navigation__link.m-font__lato--thin(:to="{ name: 'login' }", v-if="!isLogin")
        span.color--light-blue Login
      router-link.mdl-navigation__link.m-font__lato--thin(:to="{ name: 'signup' }", v-if="!isLogin")
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
