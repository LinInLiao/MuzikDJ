const request = require('superagent')
const API_END_POINT = process.env.API_END_POINT
const ROOM_CHECK = API_END_POINT + '/room/check'
const JOIN_PRIVATE_ROOM = API_END_POINT + '/room/private'
const CREATE_SONG_FOR_ROOM = API_END_POINT + '/song/create'
const ACCOUNT_ROOMS = API_END_POINT + '/account/rooms'
const SEARCH_ROOMS = API_END_POINT + '/search'
const CREATE_ROOM = API_END_POINT + '/room/create'
const LISTEN_ROOMS = API_END_POINT + '/listen/rooms'
const REMOVE_SONG = API_END_POINT + '/song/remove'
let SINGLE_ROOM_SONGS = API_END_POINT + '/room'

import * as types from '../mutation-types'

export const createRoom = ({ commit, rootState }, payload) => {
  return new Promise((resolve, reject) => {
    request
      .post(CREATE_ROOM)
      .set('Authorization', 'Bearer ' + rootState.users.token)
      .send({
        roomType: payload.roomType,
        roomId: payload.roomId,
        roomPassword: payload.roomPassword
      })
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: 'Need login.'
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
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
            message: 'Need login.'
          })
        }
      })
  })
}

export const listenRooms = ({ commit }) => {
  return new Promise((resolve, reject) => {
    request
      .post(LISTEN_ROOMS)
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: 'Need login.'
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.ROOMS, response.result)
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
            message: 'Need login.'
          })
        }
      })
  })
}

export const searchRoom = ({ commit }, keyword) => {
  return new Promise((resolve, reject) => {
    request
      .post(SEARCH_ROOMS)
      .send({
        keyword: keyword
      })
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: 'Need login.'
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.ROOMS, response.result)
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
            message: 'Need login.'
          })
        }
      })
  })
}

export const fetchAccountRooms = ({ commit, rootState }) => {
  return new Promise((resolve, reject) => {
    request
      .post(ACCOUNT_ROOMS)
      .set('Authorization', 'Bearer ' + rootState.users.token)
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: 'Need login.'
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
            message: 'Need login.'
          })
        }
      })
  })
}

export const removeSongForRoom = ({ commit, rootState }, payload) => {
  return new Promise((resolve, reject) => {
    request
      .post(REMOVE_SONG)
      .set('Authorization', 'Bearer ' + rootState.users.token)
      .send({
        id: payload.songId,
        room: payload.roomId
      })
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: 'Need login.'
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.REMOVE_SINGLE_SONG, payload.songId)
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
            message: 'Need login.'
          })
        }
      })
  })
}

export const createSongForRoom = ({ commit, state }, creds) => {
  return new Promise((resolve, reject) => {
    request
      .post(CREATE_SONG_FOR_ROOM)
      .send(creds)
      .end((err, res) => {
        if (err) {
          return reject({
            status: 'err',
            message: 'Need login.'
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.APPEND_ROOM_SONG, response.result)
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
            message: 'Need login.'
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
            message: 'Need login.'
          })
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.ROOM_TOKEN, response.token)
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
            message: 'Need login.'
          })
        }
      })
  })
}

export const fetchSingleRoomSongs = ({ commit, rootState }, alias) => {
  return new Promise((resolve, reject) => {
    let req = request.get(SINGLE_ROOM_SONGS + '/' + alias)
    if (rootState.users.token !== '' && typeof rootState.users.token !== 'undefined') {
      req.set('Authorization', 'Bearer ' + rootState.users.token)
    }
    req.query({
      token: rootState.rooms.roomToken
    })
    .end((err, res) => {
      if (err) {
        return reject({
          status: 'err',
          message: 'Need login.'
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
          message: 'Need login.'
        })
      }
    })
  })
}

export const setRoomToken = ({ commit }, token) => {
  commit(types.ROOM_TOKEN, token)
}

export const checkRoom = ({ commit, rootState }, alias) => {
  return new Promise((resolve, reject) => {
    let req = request.post(ROOM_CHECK)
    if (rootState.users.token !== '' && typeof rootState.users.token !== 'undefined') {
      req.set('Authorization', 'Bearer ' + rootState.users.token)
    }
    req.send({
      alias: alias,
      token: rootState.rooms.roomToken
    })
    .end((err, res) => {
      if (err) {
        return reject({
          status: 'err',
          message: 'Need login.'
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
          message: 'Need login.'
        })
      }
    })
  })
}
