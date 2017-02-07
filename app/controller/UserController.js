const api = new (require('../../vendors/eywek/obsifight/api'))(config.api.username, config.api.password)
const async = require('async')
const _ = require('underscore')

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

  getDoubleAccounts: (req, res) => {
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

      // find potential double account with IP + MAC
      let user = result.body
      user.accounts = []
      async.parallel([
        // find with ip
        (cb) => {
          async.each(user.adresses.ip, (ip, callback) => {
            api.request({
              route: '/user/find',
              method: 'post',
              body: {
                ip: ip
              }
            }, (err, result) => {
              if (err) {
                log.error('Error when login with API')
                return
              }
              if (!result.status) // error
                return
              for (var i = 0; i < result.body.length; i++) {
                user.accounts.push({
                  username: result.body[i].username,
                  lastConnection: result.body[i].last_connection,
                  ip: ip,
                  mac: undefined
                })
              }
              callback()
            })
          }, () => {
            cb()
          })
        },
        // find with mac
        (cb) => {
          async.each(user.adresses.mac, (mac, callback) => {
            api.request({
              route: '/user/find',
              method: 'post',
              body: {
                mac: mac
              }
            }, (err, result) => {
              if (err) {
                log.error('Error when login with API')
                return
              }
              if (!result.status) // error
                return
              for (var i = 0; i < result.body.length; i++) {
                user.accounts.push({
                  username: result.body[i].username,
                  lastConnection: result.body[i].last_connection,
                  ip: undefined,
                  mac: mac
                })
              }
              callback()
            })
          }, () => {
            cb()
          })
        }
      ], (err, results) => {
        // formatting accounts
        let accounts = {}
        for (var i = 0; i < user.accounts.length; i++) {
          if (user.accounts[i].username === user.usernames.current || _.find(user.usernames.histories, function(obj) { return obj.username == user.accounts[i].username })) // current user
            continue
          if (accounts[user.accounts[i].username] === undefined) { // add to accounts
            accounts[user.accounts[i].username] = {
              lastConnection: user.accounts[i].lastConnection,
              ip: (user.accounts[i].ip) ? [user.accounts[i].ip] : [],
              mac: (user.accounts[i].mac) ? [user.accounts[i].mac] : []
            }
          } else { // add IP/Mac, edit lastConnection
            accounts[user.accounts[i].username].lastConnection = user.accounts[i].lastConnection
            if (user.accounts[i].ip)
              accounts[user.accounts[i].username].ip.push(user.accounts[i].ip)
            if (user.accounts[i].mac)
              accounts[user.accounts[i].username].mac.push(user.accounts[i].mac)
          }
        }
        user.accounts = accounts
        // set vars
        return res.json({
          status: true,
          data: {
            user: user
          }
        })
      })
    })
  },

  getStats: (req, res) => {
    // Check request
    if (!req.params.username || req.params.username.length === 0)
      return res.badRequest()
    // Find user
    api.request({
      route: '/user/' + req.params.username + '/stats',
      method: 'get'
    }, (err, result) => {
      if (err) {
        log.error('Error when login with API')
        return res.internalError(err)
      }
      if (!result.status) // error
        return res.error(result.code, result.error)
      var data = result.body
      // handle data
      var usersKills = {}
      var usersDeaths = {}
      async.parallel([
        // handle kills
        (callback) => {
          async.each(data.history.kills, (killed, next) => {
            if (usersKills[killed] !== undefined)
              next()
            // compare users
            api.request({
              route: '/user/compare/' + req.params.username + '/' + killed,
              method: 'get'
            }, (err, result) => {
              if (err) return log.error('Error when login with API')
              usersKills[killed] = {ip: result.body.commonIPPercentage, mac: result.body.commonMACPercentage}
              next()
            })
          }, callback)
        },
        // handle deaths
        (callback) => {
          async.each(data.history.deaths, (killer, next) => {
            if (usersKills[killer] !== undefined)
              next()
            // compare users
            api.request({
              route: '/user/compare/' + req.params.username + '/' + killer,
              method: 'get'
            }, (err, result) => {
              if (err) return log.error('Error when login with API')
              usersDeaths[killer] = {ip: result.body.commonIPPercentage, mac: result.body.commonMACPercentage}
              next()
            })
          }, callback)
        }
      ], (err, results) => {
        return res.json({
          status: true,
          data: {
            kills: {
              count: data.ks.kills,
              history: usersKills
            },
            deaths: {
              count: data.ks.deaths,
              history: usersDeaths
            },
            ratio: data.ks.ratio
          }
        })
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
  },

  findByIP: (req, res) => {
    // Check request
    if (!req.params.ip || req.params.ip.length === 0)
      return res.status(400).json({status: false, error: 'Malformed request.'})

    // Find user
    api.request({
      route: '/user/find',
      method: 'post',
      body: {
        ip: req.params.ip
      }
    }, (err, result) => {
      if (err) {
        log.error('Error when login with API')
        return res.internalError(err)
      }
      if (!result.status) // error
        return res.error(result.code, result.error)

      // set vars
      result.body.reverse()
      return res.render('user/find_ip', {
        connections: result.body,
        title: 'Recherche par IP',
        ip: req.params.ip
      })
    })
  },

  findByMAC: (req, res) => {
    // Check request
    if (!req.params.mac || req.params.mac.length === 0)
      return res.status(400).json({status: false, error: 'Malformed request.'})

    // Find user
    api.request({
      route: '/user/find',
      method: 'post',
      body: {
        mac: req.params.mac
      }
    }, (err, result) => {
      if (err) {
        log.error('Error when login with API')
        return res.internalError(err)
      }
      if (!result.status) // error
        return res.error(result.code, result.error)

      // set vars
      result.body.reverse()
      return res.render('user/find_mac', {
        connections: result.body,
        title: 'Recherche par adresse MAC',
        mac: req.params.mac
      })
    })
  }

}
