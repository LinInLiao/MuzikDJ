<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    section.mdl-cell.mdl-cell--3-col.mdl-cell--4-col-tablet.mdl-cell--12-col-phone.m-box--align-center
      form.m-signup__form
        h2.e-slogan.color--light-blue.m-font__lato--thin Login
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#email.mdl-textfield__input.m-font__lato--thin(type="text", pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}", v-model="email")
          label.mdl-textfield__label.m-font__lato--thin(for="email") Email
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#password.mdl-textfield__input.m-font__lato--thin(type="password", v-model="password")
          label.mdl-textfield__label.m-font__lato--thin(for="password") Password
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent.m-font__lato--thin(type="button", @click="userLogin()") Login
        p.e-description.m-font__lato--thin
          router-link(to="signup") Does not have account?
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'login',
  methods: Object.assign(
    mapActions([
      'login'
    ]),
    {
      userLogin () {
        this.login({
          email: this.email,
          password: this.password
        }).then(() => {
          this.$router.push({ name: 'homepage' })
        }, (error) => {
          this.$swal({
            title: 'Oops!',
            type: 'error',
            text: error.message
          })
        })
      }
    }
  ),
  data () {
    return {
      email: '',
      password: '',
      error: ''
    }
  }
}
</script>
