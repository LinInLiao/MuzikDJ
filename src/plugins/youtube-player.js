'use strict'

const youtubeRegexp = /https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:['"][^<>]*>|<\/a>))[?=&+%\w.-]*/ig
const timeRegexp = /t=(\d+)[ms]?(\d+)?s?/

export function getIdFromURL (url) {
  let id = url.replace(youtubeRegexp, '$1')

  if (id.indexOf(';') !== -1) {
    const pieces = id.split(';')

    if (pieces[1].indexOf('%') !== -1) {
      const uriComponent = decodeURIComponent(pieces[1])
      id = `http://youtube.com${uriComponent}`.replace(youtubeRegexp, '$1')
    } else {
      id = pieces[0]
    }
  } else if (id.indexOf('#') !== -1) {
    id = id.split('#')[0]
  }

  return id
}

/**
 * get time from url
 * @param  {string} url url
 * @return {number}     time
 */
export function getTimeFromURL (url = '') {
  const times = url.match(timeRegexp)

  if (!times) {
    return 0
  }

  let [full, minutes, seconds] = times

  if (typeof seconds !== 'undefined') {
    seconds = parseInt(seconds, 10)
    minutes = parseInt(minutes, 10)
  } else if (full.indexOf('m') !== -1) {
    minutes = parseInt(minutes, 10)
    seconds = 0
  } else {
    seconds = parseInt(minutes, 10)
    minutes = 0
  }

  return seconds + (minutes * 60)
}

export function getPropertyAllSides (property, func) {
  const sides = ['top', 'right', 'bottom', 'left']
  let getProperty = (obj, side) => {
    obj[side] = parseInt(func.call(computedStyles, property + '-' + side), 10)
    return obj
  }
  return sides.reduce(getProperty, {})
}

export function detectIE () {
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
}

export function calculateParentDimensions (dimensions, spacers) {
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
    height: (detectIE() && detectIE() < 12) ? dimensions.height : dimensions.height + calculateSpacerValues('top', 'bottom')
  }
}

export function getParentDimensions (el) {
  computedStyles = window.getComputedStyle(el)
  let dimensionProperties = ['width', 'height']
  let spacerProperties = ['border', 'margin']
  if (detectIE() && detectIE() < 12) {
    spacerProperties.push('padding')
  }
  dimensionProperties = dimensionProperties.reduce((obj, property) => {
    obj[property] = parseInt(computedStyles.getPropertyValue(property), 10)
    return obj
  }, {})
  spacerProperties = spacerProperties.reduce((obj, property) => {
    obj[property] = getPropertyAllSides(property, computedStyles.getPropertyValue)
    return obj
  }, {})
  return calculateParentDimensions(dimensionProperties, spacerProperties)
}

export function getPlayerDimensions (el, ratio = 16 / 9) {
  const parentDimensions = getParentDimensions(el)
  const aspectHeight = parseInt(parentDimensions.width / ratio, 10)
  const aspectWidth = parseInt(parentDimensions.height * ratio, 10)
  const useAspectHeight = parentDimensions.height < aspectHeight
  return {
    width: useAspectHeight ? parentDimensions.width : aspectWidth,
    height: useAspectHeight ? aspectHeight : parentDimensions.height
  }
}

export function styleContentElements (el) {
  let content = el.children[1]
  let hasContent = !!content.children.length

  el.style.position = 'relative'
  if (!hasContent) {
    el.style.position = 'absolute'
    el.style.left = '0px'
    el.style.top = '0px'
    el.style.right = '0px'
    el.style.bottom = '0px'
  }
}

export function playlistVideoChange (self) {
  let videoObj = self.playlist[self.player.getPlaylistIndex()]
  loopVideo(videoObj, self)
}

export function loopVideo (video, self) {
  let duration, msDuration
  video = video || self
  if (video.end) {
    duration = video.end - (video.start || 0)
  } else if (self.start) {
    duration = self.player.getDuration() - video.start
  } else {
    duration = self.player.getDuration()
  }
  msDuration = duration * 1000
  self.videoTimeout = setTimeout(() => {
    if (self.playlist.length > 0) {
      self.player.nextVideo()
    } else {
      seekToStart(self, video)
    }
  }, msDuration)
}

export function resizeAndPositionPlayer (self) {
  const playerDimensions = getPlayerDimensions(self.$el, self.ratio)
  const parentDimensions = getParentDimensions(self.$el)
  let options = {
    zIndex: 1,
    position: 'absolute',
    width: playerDimensions.width + 'px',
    height: playerDimensions.height + 'px',
    left: parseInt((parentDimensions.width - playerDimensions.width) / 2, 10) + 'px',
    top: parseInt((parentDimensions.height - playerDimensions.height) / 2, 10) + 'px'
  }
  if (!self.allowClickEvents) {
    options.pointerEvents = 'none'
  }
  for (let key in options) {
    self.mPlayer.style[key] = options[key]
  }
}

export function windowResized (evt, self) {
  setTimeout(() => {
    self.$nextTick(() => {
      resizeAndPositionPlayer(self)
    })
  }, 100)
}

export function seekToStart (self, video) {
  video = video || self
  self.player.seekTo(video.start || 0)
}

export function setBackgroundImage (img, el) {
  el.style.backgroundImage = 'url(' + img + ')'
  el.style.backgroundSize = 'cover'
  el.style.backgroundPosition = 'center center'
}

export function playerReady (evt, YouTube, self) {
  if (self.playlist.length > 0) {
    self.player.loadPlaylist(self.videoArr)
    if (self.loop) {
      self.player.setLoop(true)
    }
  }
  if (self.mute && !self.player.isMuted()) {
    self.player.mute()
  } else if (self.player.isMuted()) {
    self.player.unMute()
  }
  seekToStart(self)
}

export function playerStateChange (evt, YouTube, self) {
  if (evt.data === YouTube.PlayerState.PLAYING) {
    self.$nextTick(() => {
      self.mPlayer.style.display = 'block'
    })

    if (!self.playlist.length > 0 && self.loop) {
      loopVideo()
    }
    if (self.playlist.length > 0 && self.loop) {
      playlistVideoChange(self)
    }
  }

  if (evt.data === YouTube.PlayerState.UNSTARTED && self.playlist.length > 0) {
    let videoObj = self.playlist[self.player.getPlaylistIndex()]
    let videoMute
    if (typeof videoObj !== 'undefined') {
      videoMute = typeof videoObj.mute === 'undefined' ? self.mute : videoObj.mute
      const backgroundImage = videoObj.mobileImage || self.mobileImage || 'https://img.youtube.com/vi/' + videoObj.videoId + '/maxresdefault.jpg'
      setBackgroundImage(backgroundImage, self.$el)
      seekToStart(self, videoObj)
    }

    if (videoMute || (videoMute && self.mute)) {
      if (!self.player.isMuted()) {
        self.player.mute()
      }
    } else if (!videoMute || !self.mute) {
      if (self.player.isMuted()) {
        self.player.unMute()
      }
    }
    self.$nextTick(() => {
      self.mPlayer.style.display = 'block'
    })
  }
  windowResized(evt, self)
}

const events = {
  0: 'ended',
  1: 'playing',
  2: 'paused',
  3: 'buffering',
  5: 'queued'
}
let pid = 0
let computedStyles = {}

export const VideoBackgroundPlayer = {
  props: {
    videoId: {
      type: String,
      default: ''
    },
    playlist: {
      type: Array,
      default () { return [] }
    },
    ratio: {
      type: Number,
      default () { return 16 / 9 }
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
      type: Number,
      default: 0
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
    }
  },
  render: function (h) {
    return h(
      'div',
      {
        class: {
          'video-background-player': true
        }
      },
      [
        h(
          'div',
          {
            attrs: {
              id: this.elementId
            }
          }
        ),
        h(
          'div',
          {
            class: {
              'video-player--masker': true
            }
          }
        )
      ]
    )
  },
  data () {
    pid += 1
    return {
      elementId: `video-background-player-${pid}`,
      player: undefined,
      mPlayer: undefined,
      videoArr: []
    }
  },
  created () {
    if (this.playlist.length > 0) {
      this.videoArr = this.playlist.map((videoObj) => {
        return videoObj.videoId
      })
    }
  },
  mounted () {
    styleContentElements(this.$el)
    this.$el.style.zIndex = this.contentZIndex || 99
    this.$el.children[0].style.display = 'none'

    container.register((YouTube) => {
      const {
        playerVars = {
          autoplay: 0,
          controls: 0,
          iv_load_policy: 3,
          cc_load_policy: 0,
          modestbranding: 1,
          playsinline: 1,
          rel: 0,
          showinfo: 0
        }
      } = this

      const playerDimensions = getPlayerDimensions(this.$el, this.ratio)
      this.player = new YouTube.Player(this.elementId, {
        width: playerDimensions.width,
        height: playerDimensions.height,
        playerVars,
        events: {
          onReady: (event) => {
            this.$emit('ready', event.target)
            this.mPlayer = this.$el.children[0]
            playerReady(event, YouTube, this)
          },
          onStateChange: (event) => {
            if (event.data !== -1) {
              this.$emit(events[event.data], event.target)
              playerStateChange(event, YouTube, this)
            }
          },
          onError: (event) => {
            this.$emit('error', event.target)
          }
        }
      })
    })
  },
  watch: {
    videoId (current, old) {
      if (current && old && current !== old) {
        clearTimeout(this.videoTimeout)
        const backgroundImage = this.mobileImage || 'https://img.youtube.com/vi/' + current + '/maxresdefault.jpg'
        setBackgroundImage(backgroundImage)
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
    if (this.player !== null) {
      this.player.destroy()
    }
    delete this.player
  }
}

export const container = {
  scripts: [],

  run () {
    this.scripts.forEach((callback) => {
      callback(this.YT)
    })
    this.scripts = []
  },

  register (callback) {
    if (this.YT) {
      this.Vue.nextTick(() => {
        callback(this.YT)
      })
    } else {
      this.scripts.push(callback)
    }
  }
}

export function install (Vue) {
  container.Vue = Vue
  Vue.component('video-background', VideoBackgroundPlayer)
  Vue.prototype.$videoBackground = {getIdFromURL, getTimeFromURL}

  const tag = document.createElement('script')
  tag.src = 'https://www.youtube.com/player_api'
  const firstScriptTag = document.getElementsByTagName('script')[0]
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag)

  window.onYouTubeIframeAPIReady = () => {
    container.YT = window.YT
    Vue.nextTick(() => {
      container.run()
    })
  }
}

export default {
  getIdFromURL,
  getTimeFromURL,
  VideoBackgroundPlayer,
  install
}
