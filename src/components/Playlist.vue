<template lang="pug">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid.m-section__header
    video-background(:playlist="playlist", :content-z-index="999", :loop="false", :mute="false", @ready="readyCallback", @ended="endedCallback")
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__header
      h1.e-slogan.m-inline--align-center.color--light-blue Anonymous
  .mdl-grid
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      .m-add-url__form
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#url.mdl-textfield__input.m-font__lato--thin(type="url", v-model="url")
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
        tbody
          tr.song(v-bind:class="{ playing: playingStatus.id === index }", @click.prevent="play(index)", v-for="(song, index) in songs")
            td.mdl-data-table__cell--non-numeric.song-cover(v-bind:style="{ backgroundImage: 'url(' + song.cover + ')' }")
              i.material-icons.icon--floating(v-bind:class="{ 'color--light-blue': playingStatus.id === index }") play_circle_filled
            td.mdl-data-table__cell--non-numeric.m-font__lato--thin.song-title {{ song.name }}
            td.m-font__lato--thin.song-dj {{ song.dj }}
</template>

<script>
import axios from 'axios'
const CREATE_SONG = process.env.API_END_POINT + '/song/create'

export default {
  name: 'playlist',
  methods: {
    readyCallback (player) {
      this.player = player
    },
    endedCallback () {
      this.play(this.playingStatus.index + 1)
    },
    play (index) {
      if (typeof this.player !== 'undefined') {
        if (this.songs.length > 0 && this.playingStatus.index !== index) {
          let song = this.songs[index]
          if (song === undefined) {
            song = this.songs[0]
            index = 0
          }
          this.playingStatus.id = song.id
          this.playingStatus.index = index
          this.player.loadVideoById(this.$videoBackground.getIdFromURL(song.url))
        }
      } else {
        this.playingStatus.index = index
        window.onYouTubeIframeAPIReady()
      }
    },
    add () {
      axios({
        url: CREATE_SONG,
        method: 'post',
        data: {
          url: this.url
        }
      }).then(res => {
        let message = ''
        let succeeded = false
        if (typeof res.data !== 'undefined') {
          message = res.data.message || ''
          if (res.status >= 200 && res.status < 300) {
            if (typeof res.data.status !== 'undefined') {
              if (res.data.status === 'ok') {
                this.songs.push({
                  name: res.data.result.title,
                  cover: res.data.result.cover,
                  url: this.url,
                  dj: 'MuizkDJ'
                })
                this.playlist.push({
                  videoId: this.$videoBackground.getIdFromURL(this.url)
                })
                this.url = ''
                succeeded = true
              }
            }
          }
        }
        if (!succeeded) {
          this.$swal({
            title: 'Oops!',
            type: 'error',
            text: message
          })
        }
      }, () => {
        this.url = ''
        this.$swal({
          title: 'Oops!',
          type: 'error',
          text: 'Yuotube URL invalid.'
        })
      })
    }
  },
  data () {
    return {
      url: '',
      error: '',
      songs: [],
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
