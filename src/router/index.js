import Vue from 'vue'
import Router from 'vue-router'
import Homepage from '@/components/Homepage.vue'
import ErrorPage from '@/components/ErrorPage.vue'
import CreateRoom from '@/components/CreateRoom.vue'
import Login from '@/components/Login.vue'
import Logout from '@/components/Logout.vue'
import Signup from '@/components/Signup.vue'
import Listen from '@/components/Listen.vue'
import Playlist from '@/components/Playlist.vue'
import Search from '@/components/Search.vue'
import Room from '@/components/Room.vue'
import SingleRoom from '@/components/SingleRoom.vue'
import Account from '@/components/Account.vue'
import AccountEdit from '@/components/AccountEdit.vue'
import AccountRooms from '@/components/AccountRooms.vue'
import AccountFavorite from '@/components/AccountFavorite.vue'

Vue.use(Router)

export default new Router({
  mode: 'history',
  linkActiveClass: 'is-active',
  routes: [
    {
      name: 'login',
      path: '/login',
      component: Login
    },
    {
      name: 'logout',
      path: '/logout',
      component: Logout
    },
    {
      name: 'error',
      path: '/error',
      component: ErrorPage
    },
    {
      name: 'signup',
      path: '/signup',
      component: Signup
    },
    {
      name: 'listen',
      path: '/listen',
      component: Listen
    },
    {
      name: 'playlist',
      path: '/playlist',
      component: Playlist
    },
    {
      name: 'search',
      path: '/search',
      component: Search
    },
    {
      name: 'createRoom',
      path: '/create/room',
      component: CreateRoom
    },
    {
      path: '/account',
      component: Account,
      children: [
        {
          name: 'accountEdit',
          path: 'edit',
          component: AccountEdit
        },
        {
          name: 'accountFavorite',
          path: 'favorite',
          component: AccountFavorite
        },
        {
          name: 'accountRooms',
          path: 'rooms',
          component: AccountRooms
        }
      ]
    },
    {
      path: '/room',
      component: Room,
      children: [
        {
          name: 'singleRoom',
          path: ':alias',
          component: SingleRoom
        }
      ]
    },
    {
      name: 'homepage',
      path: '/',
      component: Homepage
    },
    {
      path: '*',
      component: Homepage
    }
  ]
})
