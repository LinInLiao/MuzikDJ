const request = require('superagent')
const API_END_POINT = 'http://muzikdj.dev/api'
const LOGIN = API_END_POINT + '/login'
import * as types from '../mutation-types'

export const login = ({ commit }, loginData) => {
  return new Promise((resolve, reject) => {
    request
      .post(LOGIN)
      .send(loginData)
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
            commit(types.USER_DATA, response.user)
            commit(types.USER_TOKEN, response.token)
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
