const request = require('request')

module.exports = class {

  constructor(username, password, endpoint = 'http://api.obsifight.net') {
    this.username = username
    this.password = password
    this.endpoint = endpoint
  }

  getToken(next) {
    request.post({
      url: this.endpoint + '/authenticate',
      body: JSON.stringify({username: this.username, password: this.password}),
      headers: {
        'Content-Type': 'application/json'
      }
    }, (err, httpResponse, body) => {
      // check errors
      if (err) return next(err)
      try {
        body = JSON.parse(body)
      } catch (e) {
        console.log('Body: ', body)
        return next(e)
      }
      if (httpResponse.statusCode !== 200 || !body || !body.status) return next(body)
      // return token
      return next(undefined, body.data.token)
    })
  }

  request(config, callback) {
    // config
    const route = config.route
    const method = config.method || 'GET'
    const body = config.body || {}
    const self = this
    // check token
    if (!this.token)
      this.getToken(next)
    else
      next(undefined, this.token)

    // request
    function next(err, token) {
      if (err) return callback(err)
      request[method]({
        url: self.endpoint + route,
        body: JSON.stringify(body),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': token
        }
      }, (err, httpResponse, body) => {
        if (err) return callback(err)
        // result
        try {
          body = JSON.parse(body)
        } catch (e) {
          console.log('Body: ', body)
          return callback(e)
        }
        return callback(undefined, {
          status: httpResponse.statusCode === 200,
          code: httpResponse.statusCode,
          success: body.status,
          error: body.error || undefined,
          body: body.data || undefined
        })
      })
    }
  }

}
