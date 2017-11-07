const Promise = window.Promise || require('promise-polyfill')

class MyError extends Error {
  constructor (data = {}, ...params) {
    super(...params)
    Error.captureStackTrace(this, MyError)
    this.data = data
    this.date = new Date()
  }
}

const generateResponse = (res) => {
  let message = ''
  if (typeof res.data !== 'undefined') {
    message = res.data.message || ''
    if (res.status >= 200 && res.status < 300) {
      if (typeof res.data.status !== 'undefined') {
        if (res.data.status === 'ok') {
          return Promise.resolve(res.data)
        }
      }
    }
  }

  return Promise.reject(new MyError({
    status: 'err',
    message: message,
    state: res.status
  }))
}
const generateErrorResponse = (err) => {
  if (typeof err.response.data !== 'undefined') {
    let message = err.response.data.message || ''

    if (err.response.status === 404) {
      return Promise.reject(new MyError({
        status: 'err',
        code: err.response.status,
        message: message,
        results: []
      }))
    } else {
      return Promise.reject(new MyError({
        status: 'err',
        code: err.response.status,
        message: message
      }))
    }
  } else {
    return Promise.reject(new MyError({
      status: 'err',
      code: err.response.status,
      message: ''
    }))
  }
}

export { MyError, generateResponse, generateErrorResponse }
