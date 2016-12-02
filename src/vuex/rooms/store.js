import {
  ROOMS,
  SINGLE_ROOM,
  SINGLE_ROOM_SONGS,
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
