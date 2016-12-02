<template>
<div class="video-background-player">
  <div></div>
  <div class="video-player--masker"></div>
</div>
</template>

<script>
export default {
  name: 'videoBackgroundPlayer',
  props: {
    videoId: {
      type: String,
      require: true
    },
    playlist: {
      type: Array,
      default () {
        return []
      }
    },
    ratio: {
      type: Number,
      default () {
        return 16 / 9
      }
    },
    loop: {
      type: Boolean,
      default: false
    },
    mute: {
      type: Boolean,
      default: false
    },
    start: {
      type: Number,
      default: 0
    },
    end: {
      type: Number
    },
    contentZIndex: {
      type: Number,
      default: 999
    },
    allowClickEvents: {
      type: Boolean,
      default: false
    },
    mobileImage: {
      type: String,
      default: ''
    },
    playerCallback: {
      type: Function
    },
    stateCallback: {
      type: Function
    }
  },
  methods: {
    getPropertyAllSides (property, func) {
      const sides = ['top', 'right', 'bottom', 'left']
      let getProperty = (obj, side) => {
        obj[side] = parseInt(func.call(this.computedStyles, property + '-' + side), 10)
        return obj
      }
      return sides.reduce(getProperty, {})
    },
    calculateParentDimensions (dimensions, spacers) {
      function calculateSpacerValues () {
        let args = Array.prototype.slice.call(arguments)
        let spacer
        let sum = 0
        let sumValues = (_sum, arg) => {
          return spacer[arg] ? _sum + spacer[arg] : _sum
        }
        for (let key in spacers) {
          if (spacers.hasOwnProperty(key)) {
            spacer = spacers[key]
            sum += args.reduce(sumValues, 0)
          }
        }
        return sum
      }
      return {
        width: dimensions.width + calculateSpacerValues('left', 'right'),
        height: (this.detectIE() && this.detectIE() < 12) ? dimensions.height : dimensions.height + calculateSpacerValues('top', 'bottom')
      }
    },
    detectIE () {
      const ua = window.navigator.userAgent
      const msie = ua.indexOf('MSIE ')
      const trident = ua.indexOf('Trident/')
      const edge = ua.indexOf('Edge/')

      if (msie > 0) {
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10)
      }
      if (trident > 0) {
        let rv = ua.indexOf('rv:')
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10)
      }
      if (edge > 0) {
        return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10)
      }
      return false
    },
    getPlayerDimensions () {
      const aspectHeight = parseInt(this.parentDimensions.width / this.ratio, 10)
      const aspectWidth = parseInt(this.parentDimensions.height * this.ratio, 10)
      const useAspectHeight = this.parentDimensions.height < aspectHeight
      return {
        width: useAspectHeight ? this.parentDimensions.width : aspectWidth,
        height: useAspectHeight ? aspectHeight : this.parentDimensions.height
      }
    },
    getParentDimensions () {
      this.computedStyles = window.getComputedStyle(this.$el.parentElement)
      let dimensionProperties = ['width', 'height']
      let spacerProperties = ['border', 'margin']
      if (this.detectIE() && this.detectIE() < 12) {
        spacerProperties.push('padding')
      }
      dimensionProperties = dimensionProperties.reduce((obj, property) => {
        obj[property] = parseInt(this.computedStyles.getPropertyValue(property), 10)
        return obj
      }, {})
      spacerProperties = spacerProperties.reduce((obj, property) => {
        obj[property] = this.getPropertyAllSides(property, this.computedStyles.getPropertyValue)
        return obj
      }, {})
      return this.calculateParentDimensions(dimensionProperties, spacerProperties)
    },
    styleContentElements () {
      let content = this.$el.children[1]
      let hasContent = !!content.children.length

      content.parentElement.style.position = 'relative'
      if (!hasContent) {
        this.$el.style.position = 'absolute'
        this.$el.style.left = '0px'
        this.$el.style.top = '0px'
        this.$el.style.right = '0px'
        this.$el.style.bottom = '0px'
      }
      content.style.zIndex = this.contentZIndex || 99
    },
    updateDimensions () {
      this.styleContentElements()
      this.parentDimensions = this.getParentDimensions()
      this.playerDimensions = this.getPlayerDimensions()
    },
    playlistVideoChange () {
      let videoObj = this.playlist[this.player.getPlaylistIndex()]
      this.loopVideo(videoObj)
    },
    loopVideo (video) {
      let duration, msDuration
      video = video || this
      if (video.end) {
        duration = video.end - (video.start || 0)
      } else if (this.start) {
        duration = this.player.getDuration() - video.start
      } else {
        duration = this.player.getDuration()
      }
      msDuration = duration * 1000
      this.videoTimeout = setTimeout(() => {
        if (this.playlist.length > 0) {
          this.player.nextVideo()
        } else {
          this.seekToStart(video)
        }
      }, msDuration)
    },
    resizeAndPositionPlayer () {
      let options = {
        zIndex: 1,
        position: 'absolute',
        width: this.playerDimensions.width + 'px',
        height: this.playerDimensions.height + 'px',
        left: parseInt((this.parentDimensions.width - this.playerDimensions.width) / 2, 10) + 'px',
        top: parseInt((this.parentDimensions.height - this.playerDimensions.height) / 2, 10) + 'px'
      }
      if (!this.allowClickEvents) {
        options.pointerEvents = 'none'
      }
      for (let key in options) {
        this.mPlayer.style[key] = options[key]
      }
    },
    windowResized () {
      setTimeout(() => {
        this.$nextTick(() => {
          this.updateDimensions()
          this.resizeAndPositionPlayer()
        })
      }, 100)
    },
    seekToStart (video) {
      video = video || this
      this.player.seekTo(video.start || 0)
    },
    setBackgroundImage (img) {
      this.$el.parentElement.style.backgroundImage = 'url(' + img + ')'
      this.$el.parentElement.style.backgroundSize = 'cover'
      this.$el.parentElement.style.backgroundPosition = 'center center'
    },
    playerReady () {
      this.mPlayer = this.$el.children[0]

      if (typeof this.playerCallback === 'function') {
        setTimeout(() => {
          this.playerCallback(this.player)
        }, 10)
      }
      if (this.playlist.length > 0) {
        this.player.loadPlaylist(this.videoArr)
        if (this.loop) {
          this.player.setLoop(true)
        }
      }
      if (this.mute && !this.player.isMuted()) {
        this.player.mute()
      } else if (this.player.isMuted()) {
        this.player.unMute()
      }
      this.seekToStart()
    },
    playerStateChange (evt) {
      if (typeof this.stateCallback === 'function') {
        this.stateCallback(evt.data)
      }
      if (evt.data === window.YT.PlayerState.PLAYING) {
        this.$nextTick(() => {
          this.mPlayer.style.display = 'block'
        })

        if (!this.playlist.length > 0 && this.loop) {
          this.loopVideo()
        }
        if (this.playlist.length > 0 && this.loop) {
          this.playlistVideoChange()
        }
      }

      if (evt.data === window.YT.PlayerState.UNSTARTED && this.playlist.length > 0) {
        let videoObj = this.playlist[this.player.getPlaylistIndex()]
        let videoMute
        if (typeof videoObj !== 'undefined') {
          videoMute = typeof videoObj.mute === 'undefined' ? this.mute : videoObj.mute
          this.backgroundImage = videoObj.mobileImage || this.mobileImage || 'https://img.youtube.com/vi/' + videoObj.videoId + '/maxresdefault.jpg'
          this.setBackgroundImage(this.backgroundImage)
          this.seekToStart(videoObj)
        }

        if (videoMute || (videoMute && this.mute)) {
          if (!this.player.isMuted()) {
            this.player.mute()
          }
        } else if (!videoMute || !this.mute) {
          if (this.player.isMuted()) {
            this.player.unMute()
          }
        }
        this.$nextTick(() => {
          this.mPlayer.style.display = 'block'
        })
      }
      this.windowResized()
    }
  },
  created () {
    if (this.playlist.length > 0) {
      this.videoArr = this.playlist.map((videoObj) => {
        return videoObj.videoId
      })
    }

    window.onYouTubeIframeAPIReady = () => {
      this.updateDimensions()
      let playerOptions = {
        autoplay: 0,
        controls: 0,
        iv_load_policy: 3,
        cc_load_policy: 0,
        modestbranding: 1,
        playsinline: 1,
        rel: 0,
        showinfo: 0
      }

      this.player = new window.YT.Player(this.playerId, {
        width: this.playerDimensions.width,
        height: this.playerDimensions.height,
        playerVars: playerOptions,
        events: {
          onReady: this.playerReady,
          onStateChange: this.playerStateChange
        }
      })
      this.resizeAndPositionPlayer()
    }
  },
  mounted () {
    this.mPlayer = this.$el.children[0]
    this.playerId = 'player' + Array.prototype.slice.call(document.querySelectorAll('div[video-id]')).indexOf(this.$el)
    this.mPlayer.setAttribute('id', this.playerId)
    this.mPlayer.style.display = 'none'

    window.addEventListener('resize', this.windowResized)

    const ytScript = document.querySelector('script[src="https://www.youtube.com/iframe_api"]')
    if (!ytScript) {
      let tag = document.createElement('script')
      tag.src = 'https://www.youtube.com/iframe_api'
      let firstScriptTag = document.getElementsByTagName('script')[0]
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag)
    }
  },
  watch: {
    videoId (current, old) {
      if (current && old && current !== old) {
        clearTimeout(this.videoTimeout)
        this.backgroundImage = this.mobileImage || 'https://img.youtube.com/vi/' + current + '/maxresdefault.jpg'
        this.setBackgroundImage(this.backgroundImage)
        this.mPlayer.style.display = 'none'
        this.player.loadVideoById(current)
      }
    },
    playlist (current, old) {
      if (current && old && current !== old) {
        clearTimeout(this.videoTimeout)
        this.videoArr = current.map((videoObj) => {
          return videoObj.videoId
        })
        this.player.loadPlaylist(this.videoArr)
        if (this.loop) {
          this.player.setLoop(true)
        }
      }
    }
  },
  beforeDestroy () {
    if (this.videoTimeout) {
      clearTimeout(this.videoTimeout)
    }
    window.removeEventListener('resize', this.windowResized)
    if (this.player !== null && typeof this.player !== 'undefined') {
      this.player.destroy()
    }
  },
  data () {
    return {
      player: null,
      mPlayer: null,
      playerId: null,
      videoTimeout: null,
      computedStyles: null,
      videoArr: [],
      playerDimensions: {
        width: 0,
        height: 0
      }
    }
  }
}
</script>

<style>
.video-background-player > iframe {
  height: 100%;
}
.video-player--masker::after {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url(../assets/dot_masker.png) left bottom repeat rgba(0, 0, 0, 0.36);
    z-index: 99;
    filter: blur(5px) sepia(100%);
}
</style>