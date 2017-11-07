<template lang="pug">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    section.mdl-cell.mdl-cell--3-col.mdl-cell--4-col-tablet.mdl-cell--12-col-phone.m-box--align-center
      form.m-signup__form
        h2.e-slogan.color--light-blue.m-font__lato--thin Sign Up
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#name.mdl-textfield__input.m-font__lato--thin(type="text", pattern="[a-z,A-Z,0-9 ]*", v-model="username")
          label.mdl-textfield__label.m-font__lato--thin(for="name") Name
        span.s-error-message.m-font__lato--thin(v-if="error === 'username'") Name is required.
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#email.mdl-textfield__input.m-font__lato--thin(type="email", v-model="email")
          label.mdl-textfield__label.m-font__lato--thin(for="email") Email
        span.s-error-message.m-font__lato--thin(v-if="error === 'email'") E-mail is required.
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#password.mdl-textfield__input.m-font__lato--thin(type="password", v-model="password")
          label.mdl-textfield__label.m-font__lato--thin(for="password") Password
        span.s-error-message.m-font__lato--thin(v-if="error === 'password'") Password is required.
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent.m-font__lato--thin(type="button", @click="signup()") Signup
        p.e-description.m-font__lato--thin
          a(href="/login") Already have an account?

</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'signup',
  methods: {
    signup () {
      this.userSignup({
        username: this.username,
        email: this.email,
        password: this.password
      }).then(() => {
        this.$router.push({ name: 'accountEdit' })
      }, (err) => {
        this.$swal({
          title: 'Oops!',
          type: 'error',
          text: err.message
        })
      })
    },
    ...mapActions({
      userSignup: 'users/userSignup'
    })
  },
  data () {
    return {
      username: '',
      email: '',
      password: '',
      error: ''
    }
  }
}
</script>
