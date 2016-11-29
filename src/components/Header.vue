<template lang="jade">
header.mdl-layout__header.mdl-layout__header--transparent
  .mdl-layout__header-row
    a(href="/")
      img.mdl-layout-title(src="../assets/logo.svg")
    .mdl-layout-spacer
    nav.mdl-navigation
      a.mdl-navigation__link.m-font__lato--thin(v-if="!user.login", href="/login")
        span.color--light-blue Login
      a.mdl-navigation__link.m-font__lato--thin(v-if="!user.login", href="/signup")
        span.color--light-blue Sign up
      .mdl-navigation__link.m-font__lato--thin.user__name(v-if="user.login")
        span.m-font__lato--thin.color--light-blue(v-bind="user.name")
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
export default {
  data () {
    return {
      user: {
        login: false,
        name: ''
      }
    }
  }
}
</script>
