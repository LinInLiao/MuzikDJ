<template>
  <div class="muzikdj-player">
    <video-background
      :playlist="playlist"
      :content-z-index="999"
      :loop="false"
      :mute="false"
      @ready="readyCallback"
      @ended="endedCallback"></video-background>
  </div>
</template>

<script>
export default {
  name: 'player',
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
    }
  },
  data () {
    return {
      songs: [],
      playingStatus: {
        playing: false,
        index: -1,
        id: ''
      },
      playlist: [],
      player: undefined
    }
  },
  mounted () {
    this.$el.style.top = this.$el.parentElement.children[0].offsetHeight + 'px'
  }
}
</script>

<style scoped>
.muzikdj-player {
  position: absolute;
  width: 100%;
  height: 4px;
  overflow: hidden;
  z-index: 999;
  background-color: #39b6eb;
}
</style>
