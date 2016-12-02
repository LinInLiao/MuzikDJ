var merge = require('webpack-merge')
var prodEnv = require('./prod.env')

module.exports = merge(prodEnv, {
  NODE_ENV: '"development"',
  GA: '',
  API_END_POINT: '"http://muzikdj.dev/api"'
})
