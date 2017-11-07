import cookies from '@/plugins/cookies'

export default function sessionStorage () {
  return store => {
    let token = window.sessionStorage.getItem('muzikdjToken')
    let userData = window.sessionStorage.getItem('muzikdjUserData')

    if (userData && token && userData !== null && token !== null && userData !== '' && token !== '') {
      cookies.set('muzikdjToken', token, { expires: 86400 })
      store.commit('users/USER_TOKEN', token)
      store.commit('users/USER_DATA', JSON.parse(userData))
    } else {
      let query
      try {
        query = window.location.search.substring(1)
        query = JSON.parse('{"' + decodeURIComponent(query).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}')
      } catch (e) {}
      if (typeof query !== 'undefined') {
        if (typeof query.token !== 'undefined') {
          token = query.token.match(/^[A-Za-z0-9-_=]+\.[A-Za-z0-9-_=]+\.?[A-Za-z0-9-_.+/=]*/gim)
        }
      }
      if (token !== null && Array.isArray(token)) {
        cookies.set('muzikdjToken', token[0], { expires: 86400 })
        store.commit('users/USER_TOKEN', token[0])
        window.sessionStorage.setItem('muzikdjToken', token[0])
      } else {
        token = cookies.get('muzikdjToken')
        if (typeof token !== 'undefined') {
          store.commit('users/USER_TOKEN', token)
          window.sessionStorage.setItem('muzikdjToken', token)
        }
      }
    }
  }
}
