import * as types from '../mutation-types'
import * as actions from './actions'
import * as getters from './getters'

const state = {
  rooms: {},
  singleRoom: {},
  singleRoomSongs: [],
  roomToken: ''
}

const mutations = {
  [types.ROOMS] (state, data) {
    state.rooms = data
  },
  [types.SINGLE_ROOM] (state, data) {
    state.singleRoom = data
  },
  [types.SINGLE_ROOM_SONGS] (state, data) {
    state.singleRoomSongs = data
  },
  [types.APPEND_ROOM_SONG] (state, data) {
    state.singleRoomSongs.unshift(data)
  },
  [types.REMOVE_SINGLE_SONG] (state, data) {
    const index = state.singleRoomSongs.findIndex((song) => {
      return song.id === data
    })
    if (index > -1) {
      state.singleRoomSongs.splice(index, 1)
    }
  },
  [types.ACCOUNT_ROOMS] (state, data) {
    state.accountRooms = data
  },
  [types.ROOM_TOKEN] (state, data) {
    state.roomToken = data
  }
}

export default {
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}
