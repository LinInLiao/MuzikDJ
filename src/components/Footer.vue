<template lang="pug">
footer.mdl-mega-footer.m-footer--bgc
  .mdl-mega-footer--top-section
    .mdl-mega-footer--left-section
      span.e-description.m-font__lato--thin {{ year }} © MuzikDJ. All Rights Reserved.
    .mdl-mega-footer--right-section
      button.mdl-button.mdl-button--icon
        i.material-icons share
</template>

<script>
export default {
  name: 'footer',
  computed: {
    year () {
      return (new Date()).getFullYear()
    }
  }
}
</script>
