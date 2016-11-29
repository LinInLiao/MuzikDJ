<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid
    section.mdl-cell.mdl-cell--3-col.mdl-cell--4-col-tablet.mdl-cell--12-col-phone.m-box--align-center
      form.m-signup__form
        h2.e-slogan.color--light-blue.m-font__lato--thin Login
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#email.mdl-textfield__input.m-font__lato--thin(type="text", pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}", ng-model="email")
          label.mdl-textfield__label.m-font__lato--thin(for="email") Email
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#password.mdl-textfield__input.m-font__lato--thin(type="password", ng-model="password")
          label.mdl-textfield__label.m-font__lato--thin(for="password") Password
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent.m-font__lato--thin(type="button", ng-click="login()") Login
        p.e-description.m-font__lato--thin
          a(href="/signup") Does not have account?
</template>
