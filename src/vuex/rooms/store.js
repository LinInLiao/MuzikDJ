import {
  ROOMS,
  SINGLE_ROOM,
  SINGLE_ROOM_SONGS,
  APPEND_ROOM_SONG,
  REMOVE_SINGLE_SONG,
  ACCOUNT_ROOMS,
  ROOM_TOKEN
} from '../mutation-types'

import * as actions from './actions'
import * as getters from './getters'

const state = {
  rooms: {},
  singleRoom: {},
  singleRoomSongs: [],
  roomToken: ''
}

const mutations = {
  [ROOMS] (state, data) {
    state.rooms = data
  },
  [SINGLE_ROOM] (state, data) {
    state.singleRoom = data
  },
  [SINGLE_ROOM_SONGS] (state, data) {
    state.singleRoomSongs = data
  },
  [APPEND_ROOM_SONG] (state, data) {
    state.singleRoomSongs.unshift(data)
  },
  [REMOVE_SINGLE_SONG] (state, data) {
    const index = state.singleRoomSongs.findIndex((song) => {
      return song.id === data
    })
    if (index > -1) {
      state.singleRoomSongs.splice(index, 1)
    }
  },
  [ACCOUNT_ROOMS] (state, data) {
    state.accountRooms = data
  },
  [ROOM_TOKEN] (state, data) {
    state.roomToken = data
  }
}

export default {
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}
