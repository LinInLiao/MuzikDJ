const request = require('superagent')
const API_END_POINT = process.env.API_END_POINT
const LOGIN = API_END_POINT + '/login'
const SIGNUP = API_END_POINT + '/signup'
const CHECK_TOKEN = API_END_POINT + '/auth/check'

import * as types from '../mutation-types'
import cookies from '../../plugins/cookies'

export const checkToken = ({ commit }, token) => {
  return new Promise((resolve, reject) => {
    request
      .post(CHECK_TOKEN)
      .send({
        token: token
      })
      .end((err, res) => {
        if (err) {
          return reject(JSON.parse(err.text))
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.USER_DATA, response.user)
            commit(types.USER_TOKEN, response.token)
            cookies.set('muzikDJToken', response.token, { expires: 86400 })
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

export const setUserToken = ({ commit }, token) => {
  commit(types.USER_TOKEN, token)
}

export const logout = ({ commit }) => {
  commit(types.USER_DATA, {})
  commit(types.USER_TOKEN, '')
  cookies.remove('muzikDJToken')
  return Promise.resolve()
}

export const userSignup = ({ commit }, payload) => {
  return new Promise((resolve, reject) => {
    request
      .post(SIGNUP)
      .send({
        username: payload.username,
        email: payload.email,
        password: payload.password
      })
      .end((res, err) => {
        if (err) {
          return reject(JSON.parse(err.text))
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.USER_DATA, response.user)
            commit(types.USER_TOKEN, response.token)
            cookies.set('muzikDJToken', response.token, { expires: 86400 })
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
            message: 'Signup failed.'
          })
        }
      })
  })
}

export const login = ({ commit }, loginData) => {
  return new Promise((resolve, reject) => {
    request
      .post(LOGIN)
      .send(loginData)
      .end((err, res) => {
        if (err) {
          return reject(JSON.parse(err.text))
        }
        const response = JSON.parse(res.text)
        if (typeof response.status !== 'undefined') {
          if (response.status === 'ok') {
            commit(types.USER_DATA, response.user)
            commit(types.USER_TOKEN, response.token)
            cookies.set('muzikDJToken', response.token, { expires: 86400 })
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
