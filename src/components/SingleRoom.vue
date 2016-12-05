<template lang="jade">
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
    video-background(:playlist="playlist", :content-z-index="999", :loop="false", :mute="false", :player-callback="videoCallback", :state-callback="stateCallback")
  .mdl-grid(v-if="roomCheck === true")
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      form.m-add-url__form(action="#")
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#url.mdl-textfield__input.m-font__lato--thin(type="url", pattern="^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?", v-model="songUrl")
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

import VideoBackground from './VideoBackground.vue'

export default {
  name: 'singleRoom',
  components: {
    'video-background': VideoBackground
  },
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
          let room = await vm.checkRoom(to.params.alias).catch(reason => vm.$router.push({ name: 'error' }))
          if (typeof room === 'undefined') {
            vm.$router.push({ name: 'error' })
          }
          if (room.status === 'public') {
            await vm.fetchSingleRoomSongs(to.params.alias).then(() => {
              vm.getPlaylist()
            }).catch(reason => {
              console.log(reason)
              vm.getPlaylist()
            })
          }
        } else {
          if (vm.getSingleRoom.status === 'public') {
            await vm.fetchSingleRoomSongs(to.params.alias).then(() => {
              vm.getPlaylist()
            }).catch(reason => {
              console.log(reason)
              vm.getPlaylist()
            })
          }
        }
      }
    })
  },
  computed: Object.assign(
    mapGetters([
      'getRoomToken',
      'getSingleRoom',
      'getSingleRoomSongs'
    ]),
    {
      roomCheck () {
        console.log(this.getSingleRoom.status)
        return typeof this.getSingleRoom.status !== 'undefined'
          ? (this.getSingleRoom.status === 'private'
            ? (this.roomPrivateCheck ? true : 'login') : true) : false
      }
    }
  ),
  methods: Object.assign(
    mapActions([
      'setRoomToken',
      'checkRoom',
      'fetchSingleRoomSongs',
      'joinPrivateRoom',
      'createSongForRoom',
      'removeSongForRoom'
    ]),
    {
      videoCallback (player) {
        this.player = player
        this.play(this.playingStatus.index === -1 ? 0 : this.playingStatus.index)
      },
      stateCallback (state) {
        if (state === window.YT.PlayerState.ENDED) {
          this.play(this.playingStatus.index + 1)
        }
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
            this.getYoutubeId(song.url)
            this.playingStatus.id = song.id
            this.playingStatus.index = index
            this.player.loadVideoById(this.youtubeId)
          }
        } else {
          this.playingStatus.index = index
          window.onYouTubeIframeAPIReady()
        }
      },
      getPlaylist () {
        if (this.getSingleRoomSongs.length > 0) {
          return this.getSingleRoomSongs.map((video) => {
            return {
              videoId: this.getYoutubeId(video.url)
            }
          })
        }
      },
      getYoutubeId (url) {
        let videoId = decodeURIComponent(url).split('v=')[1]
        let ampersandPosition = videoId.indexOf('&')
        if (ampersandPosition >= 0) {
          videoId = videoId.substring(0, ampersandPosition)
        }
        this.youtubeId = videoId
        return videoId
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
      }
    }
  ),
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
