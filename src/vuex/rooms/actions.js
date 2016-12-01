const request = require('superagent')
const API_END_POINT = 'http://muzikdj.dev/api'
const ROOM_CHECK = API_END_POINT + '/room/check'
const JOIN_PRIVATE_ROOM = API_END_POINT + '/room/private'
const CREATE_SONG_FOR_ROOM = API_END_POINT + '/song/create'
const ACCOUNT_ROOMS = API_END_POINT + '/account/rooms'
let SINGLE_ROOM_SONGS = API_END_POINT + '/room'
import * as types from '../mutation-types'

export const fetchAccountRooms = ({ commit, rootState }) => {
  return new Promise((resolve, reject) => {
    request
      .post(ACCOUNT_ROOMS)
      .set('Authorization', 'Bearer ' + rootState.users.token)
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: err
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.ACCOUNT_ROOMS, response.result)
            return resolve(response.result)
          } else {
            return reject({
              status: 'err',
              message: response.message
            })
          }
        } else {
          return reject({
            status: 'err',
            message: 'Login failed.'
          })
        }
      })
  })
}

export const createSongForRoom = ({ commit }, creds) => {
  return new Promise((resolve, reject) => {
    request
      .post(CREATE_SONG_FOR_ROOM)
      .send(creds)
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: err
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            return resolve()
          } else {
            return reject({
              status: 'err',
              message: response.message
            })
          }
        } else {
          return reject({
            status: 'err',
            message: 'Login failed.'
          })
        }
      })
  })
}

export const joinPrivateRoom = ({ commit }, creds) => {
  return new Promise((resolve, reject) => {
    request
      .post(JOIN_PRIVATE_ROOM)
      .send(creds)
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: err
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            return resolve()
          } else {
            return reject({
              status: 'err',
              message: response.message
            })
          }
        } else {
          return reject({
            status: 'err',
            message: 'Login failed.'
          })
        }
      })
  })
}

export const fetchSingleRoomSongs = ({ commit }, alias) => {
  return new Promise((resolve, reject) => {
    request
      .get(SINGLE_ROOM_SONGS + '/' + alias)
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: err
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.SINGLE_ROOM_SONGS, response.result)
            return resolve(response.result)
          } else {
            return reject({
              status: 'err',
              message: response.message
            })
          }
        } else {
          return reject({
            status: 'err',
            message: 'Login failed.'
          })
        }
      })
  })
}

export const checkRoom = ({ commit }, alias) => {
  return new Promise((resolve, reject) => {
    request
      .post(ROOM_CHECK)
      .send({
        alias: alias
      })
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: err
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.SINGLE_ROOM, response.result)
            return resolve(response.result)
          } else {
            return reject({
              status: 'err',
              message: response.message
            })
          }
        } else {
          return reject({
            status: 'err',
            message: 'Login failed.'
          })
        }
      })
  })
}
