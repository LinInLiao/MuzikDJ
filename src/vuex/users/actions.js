import axios from 'axios'
import * as types from '../mutation-types'
import cookies from '@/plugins/cookies'
import { MyError, generateResponse, generateErrorResponse } from '../plugins/common'

const Promise = window.Promise || require('promise-polyfill')

const API_END_POINT = process.env.API_END_POINT
const LOGIN = API_END_POINT + '/login'
const SIGNUP = API_END_POINT + '/signup'
const CHECK_TOKEN = API_END_POINT + '/auth/check'

export const checkToken = async ({ commit }, token) => {
  try {
    if (typeof token === 'undefined') {
      return Promise.reject(new MyError({
        status: 'err',
        code: 405,
        messages: 'User token is not defined.'
      }))
    }

    let res = await axios({
      url: CHECK_TOKEN,
      method: 'post',
      headers: {
        Authorization: 'Bearer ' + token
      }
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.USER_DATA, res.user)
      commit(types.USER_TOKEN, res.token)
      cookies.set('muzikDJToken', res.token, { expires: 86400 })
      return res
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
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

export const userSignup = async ({ commit }, payload) => {
  try {
    let res = await axios({
      url: SIGNUP,
      method: 'post',
      data: {
        username: payload.username,
        email: payload.email,
        password: payload.password
      }
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.USER_DATA, res.user)
      commit(types.USER_TOKEN, res.token)
      cookies.set('muzikDJToken', res.token, { expires: 86400 })
      return res
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}

export const login = async ({ commit }, loginData) => {
  try {
    let res = await axios({
      url: LOGIN,
      method: 'post',
      data: loginData
    }).then(generateResponse, generateErrorResponse)

    if (res) {
      commit(types.USER_DATA, res.user)
      commit(types.USER_TOKEN, res.token)
      cookies.set('muzikDJToken', res.token, { expires: 86400 })
      return res
    }
  } catch (e) {
    return Promise.reject(new MyError(e))
  }
}
