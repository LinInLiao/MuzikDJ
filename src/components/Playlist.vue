<template lang="jade">
main.mdl-layout__content.m-content--bgc-lighter.view-change-animate
  .mdl-grid.m-section__header
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-section__header
      h1.e-slogan.m-inline--align-center.color--light-blue Anonymous
  .mdl-grid
    section.mdl-cell.mdl-cell--10-col.mdl-cell--4-col-phone.m-box--align-center
      form.m-add-url__form(action="#")
        .mdl-textfield.mdl-js-textfield.mdl-textfield--floating-label
          input#url.mdl-textfield__input.m-font__lato--thin(type="url", pattern="^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?", v-model="url")
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
          tr.song(v-bind:class="{ playing: playingStatus.id === song.id }", @click="play($event, $index)", v-for="song in songs")
            td.mdl-data-table__cell--non-numeric.song-cover(v-bind:style="{ backgroundImage: 'url(' + song.cover + ')' }")
              i.mdi.icon--floating(v-bind:class="playingStatus.id === song.id ? ['mdi-play-circle','color--light-blue'] : ['mdi-pause-circle']")
            td.mdl-data-table__cell--non-numeric.m-font__lato--thin.song-title {{ song.name }}
            td.m-font__lato--thin.song-dj {{ song.dj }}
    youtube-video(video-url="youtubeUrl", player-vars="youtubePlayerVars", player="youtubePlayer", style="display:none;width:0px;height:0px;")
</template>

<script>
export default {
  name: 'playlist',
  methods: {
    play () {

    },
    add () {

    }
  },
  data () {
    return {
      url: '',
      error: '',
      songs: [],
      playingStatus: {
        id: ''
      }
    }
  }
}
</script>
