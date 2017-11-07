<template lang="pug">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid.m-section__header
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__header
      h1.e-slogan.m-inline--align-center.m-font__lato--thin.color--light-blue Search Room
      .m-join-room__form
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#search.mdl-textfield__input(type="text", v-model="keyword", @keydown.enter="search()")
          label.mdl-textfield__label(for="search") Room's name
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent(type="button", @click="search()") Search
        br
        span.s-error-message.m-font__lato--thin(v-if="error === 'keyword'") Room name is required.
  rooms(:rooms="rooms")
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import Rooms from '@/components/Rooms.vue'

export default {
  name: 'search',
  components: {
    rooms: Rooms
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      if (typeof vm.$route.query !== 'undefined') {
        if (typeof vm.$route.query.q !== 'undefined') {
          if (vm.$route.query.q !== '') {
            vm.searchRoom(vm.$route.query.q).then(() => {
              vm.rooms = vm.getRooms
            })
          }
        }
      }
    })
  },
  computed: mapGetters(['getRooms']),
  methods: {
    search () {
      this.searchRoom(this.keyword).then(() => {
        this.$router.replace({
          query: {
            q: this.keyword
          }
        })
        this.rooms = this.getRooms
      }, (err) => {
        this.$swal({
          title: 'Oops!',
          type: 'error',
          text: err.message
        })
      })
    },
    ...mapActions({
      searchRoom: 'rooms/searchRoom'
    })
  },
  data () {
    return {
      rooms: [],
      error: '',
      keyword: ''
    }
  }
}
</script>
