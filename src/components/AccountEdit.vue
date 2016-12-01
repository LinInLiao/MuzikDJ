<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    section.mdl-cell.mdl-cell--3-col.mdl-cell--4-col-tablet.mdl-cell--12-col-phone.m-box--align-center
      form.m-account__form
        h2.e-slogan.color--light-blue.m-font__lato--thin Edit Account
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#name.mdl-textfield__input.m-font__lato--thin(type="text", v-model="user.name")
          label.mdl-textfield__label.m-font__lato--thin(for="name") Name
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#password.mdl-textfield__input.m-font__lato--thin(type="password", v-model="password")
          label.mdl-textfield__label.m-font__lato--thin(for="password") New Password
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent.m-font__lato--thin(type="button", @click="save()") Save
</template>

<script>
export default {
  name: 'AccountEdit',
  methods: {
    save () {

    }
  },
  data () {
    return {
      user: {
        name: ''
      },
      password: ''
    }
  }
}
</script>
