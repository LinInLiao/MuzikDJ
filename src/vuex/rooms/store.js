import {
  ROOMS,
  SINGLE_ROOM,
  SINGLE_ROOM_SONGS
} from '../mutation-types'

import * as actions from './actions'
import * as getters from './getters'

const state = {
  rooms: {},
  singleRoom: {},
  singleRoomSongs: []
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
  }
}

export default {
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}
