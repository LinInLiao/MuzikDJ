import axios from 'axios'
import * as types from '../mutation-types'
import { MyError, generateResponse, generateErrorResponse } from '../plugins/common'

const API_END_POINT = process.env.API_END_POINT
const ROOM_CHECK = API_END_POINT + '/room/check'
const JOIN_PRIVATE_ROOM = API_END_POINT + '/room/private'
const CREATE_SONG_FOR_ROOM = API_END_POINT + '/song/create'
const ACCOUNT_ROOMS = API_END_POINT + '/account/rooms'
const SEARCH_ROOMS = API_END_POINT + '/search'
const CREATE_ROOM = API_END_POINT + '/room/create'
const LISTEN_ROOMS = API_END_POINT + '/listen/rooms'
const REMOVE_SONG = API_END_POINT + '/song/remove'
const SINGLE_ROOM_SONGS = API_END_POINT + '/room'

const Promise = window.Promise || require('promise-polyfill')

export const createRoom = async ({ commit, rootState }, payload) => {
  try {
    if (typeof rootState.users.token === 'undefined' ||
      rootState.users.token === ''
    ) {
      return Promise.reject(new MyError({
        status: 'err',
        code: 405,
        messages: 'User token is not defined.'
      }))
    }

    let res = await axios({
      url: CREATE_ROOM,
      method: 'post',
      headers: {
        Authorization: 'Bearer ' + rootState.users.token
      }
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const listenRooms = async ({ commit }) => {
  try {
    let res = await axios({
      url: LISTEN_ROOMS,
      method: 'post'
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const searchRoom = async ({ commit }, keyword) => {
  try {
    let res = await axios({
      url: SEARCH_ROOMS,
      method: 'post',
      data: {
        keyword: keyword
      }
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.ROOMS, res.result)
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const fetchAccountRooms = async ({ commit, rootState }) => {
  try {
    if (typeof rootState.users.token === 'undefined' ||
      rootState.users.token === ''
    ) {
      return Promise.reject(new MyError({
        status: 'err',
        code: 405,
        messages: 'User token is not defined.'
      }))
    }

    let res = await axios({
      url: ACCOUNT_ROOMS,
      method: 'post',
      headers: {
        Authorization: 'Bearer ' + rootState.users.token
      }
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.ACCOUNT_ROOMS, res.result)
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const removeSongForRoom = async ({ commit, rootState }, payload) => {
  try {
    if (typeof rootState.users.token === 'undefined' ||
      rootState.users.token === ''
    ) {
      return Promise.reject(new MyError({
        status: 'err',
        code: 405,
        messages: 'User token is not defined.'
      }))
    }

    let res = await axios({
      url: REMOVE_SONG,
      method: 'post',
      data: {
        id: payload.songId,
        room: payload.roomId
      },
      headers: {
        Authorization: 'Bearer ' + rootState.users.token
      }
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.REMOVE_SINGLE_SONG, payload.songId)
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const createSongForRoom = async ({ commit, state }, creds) => {
  try {
    let res = await axios({
      url: CREATE_SONG_FOR_ROOM,
      method: 'post',
      data: creds
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.APPEND_ROOM_SONG, res.result)
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const joinPrivateRoom = async ({ commit }, creds) => {
  try {
    let res = await axios({
      url: JOIN_PRIVATE_ROOM,
      method: 'post',
      data: creds
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.ROOM_TOKEN, res.token)
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const fetchSingleRoomSongs = async ({ commit, rootState }, alias) => {
  try {
    let options = {
      url: SINGLE_ROOM_SONGS + '/' + alias,
      method: 'post'
    }

    if (typeof rootState.rooms.roomToken !== 'undefined' &&
      rootState.rooms.roomToken !== ''
    ) {
      options.data = {
        token: rootState.rooms.roomToken
      }
    }
    if (typeof rootState.users.token !== 'undefined' &&
      rootState.users.token !== ''
    ) {
      options.headers = {
        Authorization: 'Bearer ' + rootState.users.token
      }
    }
    let res = await axios(options)
      .then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.SINGLE_ROOM_SONGS, res.result)
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const setRoomToken = ({ commit }, token) => {
  commit(types.ROOM_TOKEN, token)
}

export const checkRoom = async ({ commit, rootState }, alias) => {
  try {
    let options = {
      url: ROOM_CHECK,
      method: 'post',
      data: {
        alias: alias
      }
    }
    if (typeof rootState.rooms.roomToken !== 'undefined' &&
      rootState.rooms.roomToken !== ''
    ) {
      options.data.token = rootState.rooms.roomToken
    }
    if (typeof rootState.users.token !== 'undefined' &&
      rootState.users.token !== ''
    ) {
      options.headers = {
        Authorization: 'Bearer ' + rootState.users.token
      }
    }
    let res = await axios(options)
      .then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.SINGLE_ROOM, res.result)
      return res.result
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}
