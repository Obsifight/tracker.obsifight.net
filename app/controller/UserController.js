const api = new (require('../../vendors/eywek/obsifight/api'))(config.api.username, config.api.password)

module.exports = {

  loginPage: (req, res) => {
    res.render('user/login', {
      title: 'Se connecter'
    })
  },

  login: (req, res) => {
    // Check request
    if (!req.body || !req.body.username || !req.body.password)
      return res.status(400).json({status: false, error: 'Malformed request.'})
    if (req.body.username.length === 0)
      return res.status(400).json({status: false, error: 'Missing username.'})
    if (req.body.password.length === 0)
      return res.status(400).json({status: false, error: 'Missing password.'})

    // send request to api
    api.request({
      route: '/user/authenticate',
      method: 'post',
      body: {
        username: req.body.username,
        password: req.body.password
      }
    }, (err, result) => {
      if (err) {
        console.error(err)
        log.error('Error when login with API')
        return res.status(500).json({status: false, error: 'Internal error when login with API.'})
      }
      if (!result.status) // error
        return res.status(result.code).json({status: false, error: result.error})

      // success, check if authorized
      if (config.authorizedUsers.indexOf(req.body.username) === 1)
        return res.status(403).json({status: false, error: 'Not authorized, need rank up.'})

      // connect user
      req.session.auth = {
        user: {
          id: result.body.user.id
        }
      }
      req.session.save()

      // tell to js to redirect him
      return res.status(200).json({status: true, success: 'Logged! Redirect...'})
    })
  },

  get: (req, res) => {
    // Check request
    if (!req.params.username || req.params.username.length === 0)
      return res.badRequest()

    // Find user
    api.request({
      route: '/user/' + req.params.username,
      method: 'get'
    }, (err, result) => {
      if (err) {
        log.error('Error when login with API')
        return res.internalError(err)
      }
      if (!result.status) // error
        return res.error(result.code, result.error)

      // set vars
      return res.render('user/get_user', {
        user: result.body,
        title: result.body.usernames.current
      })
    })
  },

  getSanctions: (req, res) => {
    // Check request
    if (!req.params.username || req.params.username.length === 0)
      return res.status(400).json({status: false, error: 'Malformed request.'})

    // Find user
    api.request({
      route: '/user/' + req.params.username + '/sanctions',
      method: 'get'
    }, (err, result) => {
      if (err) {
        log.error('Error when login with API')
        return res.status(500).json({status: false, error: 'Error when login with API.'})
      }
      if (!result.status) // error
        return res.status(result.code).json({status: false, error: result.error})

      return res.json({status: true, data: result.body})
    })
  },

  getMoney: (req, res) => {
    // Check request
    if (!req.params.username || req.params.username.length === 0)
      return res.status(400).json({status: false, error: 'Malformed request.'})

    // Find user
    api.request({
      route: '/user/' + req.params.username + '/money/timeline',
      method: 'get'
    }, (err, result) => {
      if (err) {
        log.error('Error when login with API')
        return res.status(500).json({status: false, error: 'Error when login with API.'})
      }
      if (!result.status) // error
        return res.status(result.code).json({status: false, error: result.error})

      return res.json({status: true, data: result.body})
    })
  }

}
