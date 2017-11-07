<template lang="pug">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid.m-section__header(v-if="roomCheck !== false")
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__header.s-background--transparent
      h1.e-slogan.m-inline--align-center.m-font__lato--thin.color--light-blue {{ getSingleRoom.name }}
      form.m-join-room__form(v-if="roomCheck === 'login'")
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#password.mdl-textfield__input.m-font__lato--thin(type="password", v-model="roomPassword")
          label.mdl-textfield__label.m-font__lato--thin(for="password") Password
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent.m-font__lato--thin(type="button", @click="join()") Join
        br
        span.s-error-message.m-font__lato--thin(v-if="error === 'password'") Password is invalid.
    video-background(:playlist="playlist", :content-z-index="999", :loop="false", :mute="false", @ready="readyCallback", @ended="endedCallback")
  .mdl-grid(v-if="roomCheck === true")
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      form.m-add-url__form(action="#")
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#url.mdl-textfield__input.m-font__lato--thin(type="url", v-model="songUrl")
          label.mdl-textfield__label.m-font__lato--thin(for="url") Youtube URL
        span.s-error-message.m-font__lato--thin(v-if="error === 'url'") URL is invalid.
        button.mdl-button.mdl-js-button.mdl-button--raised.mdl-js-ripple-effect.m-button--full-transparent.m-font__lato--thin(type="button", @click="add()") Add To Playlist
    section.mdl-cell.mdl-cell--8-col.mdl-cell--12-col-phone.m-box--align-center
      table.mdl-data-table.mdl-js-data-table.room-playlist__table
        thead
          tr
            th.mdl-data-table__cell--non-numeric.color--light-blue.m-font__lato--thin.song-cover Cover
            th.mdl-data-table__cell--non-numeric.color--light-blue.m-font__lato--thin.song-title Title
            th.color--light-blue.m-font__lato--thin.song-dj DJ
            th.color--light-blue.m-font__lato--thin.song-remove
        tbody
          tr.song(v-bind:class="{ playing: playingStatus.id === song.id }", v-for="(song, key) in getSingleRoomSongs")
            td.mdl-data-table__cell--non-numeric.song-cover(:style="{ backgroundImage: 'url(' + song.cover + ')' }", @click="play(key)")
              i.material-icons.icon--floating(v-bind:class="{ 'color--light-blue': playingStatus.id === song.id }") play_circle_filled
            td.mdl-data-table__cell--non-numeric.m-font__lato--thin.song-title {{ song.name }}
            td.m-font__lato--thin.song-dj {{ song.dj }}
            td.m-font__lato--thin.song-remove
              button.mdl-button.mdl-js-button.mdl-button--icon(@click="remove(key)")
                i.material-icons cancel
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
  name: 'single-room',
  beforeRouteEnter (to, from, next) {
    next(vm => {
      if (typeof to.params.alias === 'undefined') {
        vm.$router.push({ name: 'error' })
      }
      if (typeof to.query.token !== 'undefined') {
        if (to.query.token !== '') {
          vm.setRoomToken(to.query.token)
        }
      }

      checkRoom()

      async function checkRoom () {
        if (typeof vm.getSingleRoom.status === 'undefined') {
          let room = await vm.checkRoom(to.params.alias)
          if (typeof room === 'undefined') {
            vm.$router.push({ name: 'error' })
          }
          if (room.status === 'err') {
            if (room.code === 403) {
              vm.roomPrivateCheck = false
            } else {
              vm.$router.push({ name: 'error' })
            }
          } else if (room.status === 'public') {
            vm.fetchSingleRoomSongs(to.params.alias).then(res => {
              vm.getPlaylist()
            }, err => {
              console.dir(err.data)
            })
          }
        } else {
          if (vm.getSingleRoom.status === 'public') {
            vm.fetchSingleRoomSongs(to.params.alias).then(() => {
              vm.getPlaylist()
            })
          }
        }
      }
    })
  },
  computed: {
    roomCheck () {
      return typeof this.getSingleRoom.status !== 'undefined'
        ? (this.getSingleRoom.status === 'private'
          ? (this.roomPrivateCheck ? true : 'login') : true) : (this.roomPrivateCheck ? true : 'login')
    },
    ...mapGetters({
      getRoomToken: 'rooms/getRoomToken',
      getSingleRoom: 'rooms/getSingleRoom',
      getSingleRoomSongs: 'rooms/getSingleRoomSongs'
    })
  },
  methods: {
    readyCallback (player) {
      this.player = player
      this.play(this.playingStatus.index === -1 ? 0 : this.playingStatus.index)
    },
    endedCallback () {
      this.play(this.playingStatus.index + 1)
    },
    remove (index) {
      this.removeSongForRoom({
        songId: this.getSingleRoomSongs[index].id,
        roomId: this.getSingleRoom.id
      }).then(() => {
        // DO NOTHING.
      }, (err) => {
        this.$swal({
          title: 'Oops!',
          type: 'error',
          text: err.message
        })
      })
    },
    play (index) {
      if (typeof this.player !== 'undefined') {
        if (this.getSingleRoomSongs.length > 0 && this.playingStatus.index !== index) {
          let song = this.getSingleRoomSongs[index]
          if (song === undefined) {
            song = this.getSingleRoomSongs[0]
            index = 0
          }
          this.playingStatus.id = song.id
          this.playingStatus.index = index
          let videoId = this.$videoBackground.getIdFromURL(song.url)
          this.player.loadVideoById(videoId)
        }
      } else {
        this.playingStatus.index = index
        window.onYouTubeIframeAPIReady()
      }
    },
    getPlaylist () {
      if (this.getSingleRoomSongs.length > 0) {
        this.playlist = this.getSingleRoomSongs.reduce((videos, video) => {
          videos.push({
            videoId: this.$videoBackground.getIdFromURL(video.url)
          })
          return videos
        }, [])
      }
    },
    add () {
      this.createSongForRoom({
        room: this.getSingleRoom.alias,
        url: this.songUrl
      }).then(() => {
        this.$nextTick(() => {
          this.songUrl = ''
        })
      }, (err) => {
        this.$swal({
          title: 'Oops!',
          type: 'error',
          text: err.message
        })
      })
    },
    join () {
      this.joinPrivateRoom({
        alias: this.getSingleRoom.alias,
        password: this.roomPassword
      }).then((res) => {
        this.roomPrivateCheck = true
        this.setRoomToken(res.token)
        this.$router.replace({
          query: {
            token: res.token
          }
        })
        this.fetchSingleRoomSongs(this.getSingleRoom.alias)
      }, (err) => {
        this.$swal({
          title: 'Oops!',
          type: 'error',
          text: err.message
        })
      })
    },
    ...mapActions({
      setRoomToken: 'rooms/setRoomToken',
      checkRoom: 'rooms/checkRoom',
      fetchSingleRoomSongs: 'rooms/fetchSingleRoomSongs',
      joinPrivateRoom: 'rooms/joinPrivateRoom',
      createSongForRoom: 'rooms/createSongForRoom',
      removeSongForRoom: 'rooms/removeSongForRoom'
    })
  },
  data () {
    return {
      error: '',
      songUrl: '',
      roomPassword: '',
      roomPrivateCheck: false,
      playingStatus: {
        playing: false,
        index: -1,
        id: ''
      },
      playlist: [],
      player: undefined
    }
  }
}
</script>
