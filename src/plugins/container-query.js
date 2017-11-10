'use strict'

const isEqual = require('lodash/isEqual')
const findKey = require('lodash/findKey')
const ResizeObserverLite = require('resize-observer-lite').default
const matchQueries = require('container-query-toolkit/lib/matchQueries').default

class ContainerQueryCore {
  constructor (query = {}, callback = (params) => void 0) {
    this.rol = new ResizeObserverLite(size => {
      const result = matchQueries(query)(size)
      if (!isEqual(this.result, result)) {
        callback(result)
        this.result = result
      }
    })
  }
  observe (element = void 0) {
    this.rol.observe(element)
  }

  disconnect () {
    this.rol.disconnect()
  }
}

const ContainerQuery = {
  name: 'container-query',
  props: {
    query: {
      type: Object,
      default () {
        return {}
      },
      children: {
        type: Function,
        default () {
          return null
        }
      }
    }
  },
  data () {
    return {
      cqCore: void 0,
      params: {}
    }
  },
  beforeMount () {
    this.cqCore = new ContainerQueryCore(this.query, params => {
      this.params = params
    })

    this.cqCore.observe(this.$el)
  },
  updated () {
    if (typeof this.cqCore !== 'undefined') {
      this.cqCore.observe(this.$el)
    }
  },
  mounted () {
    if (typeof this.children === 'function') {
      this.children(this.params)
    }
  },
  beforeDestroy () {
    if (typeof this.cqCore !== 'undefined') {
      this.cqCore.disconnect()
    }
    this.cqCore = void 0
  },
  render (h) {
    let key = findKey(this.params, param => !!param)
    if (typeof key !== 'undefined') {
      return h(
        'div',
        {
          class: ['container-query'],
          style: {
            opacity: 1
          },
          props: {
            query: this.query
          }
        }, [
          h(
            'div',
            {
              class: this.params,
              key: 'containerQueryChildren',
              ref: 'containerQueryChildren'
            },
            this.$slots.default
          )
        ]
      )
    }
    return h('div')
  }
}

export function install (Vue) {
  Vue.component('container-query', ContainerQuery)
}

export default {
  ContainerQuery,
  install
}
