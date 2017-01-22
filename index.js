// ===========
//    INIT
// ===========
const express = require('express')
const app = express()
const path = require('path')
const fs = require('fs')
const session = require('express-session')

// =====================
//    CUSTOM FUNCTIONS
// =====================
app.use((req, res, next) => {
  res.notFound = () => {
    res.status(404)
    log.warn('Get 404 on ' + req.originalUrl)
    res.render('errors/error', {title: 'Page introuvable', error: "La page demandée n'a pas été trouvée."})
  }
  res.forbidden = () => {
    res.status(403)
    log.warn('Get 403 on ' + req.originalUrl)
    res.render('errors/error', {title: 'Accès non autorisé', error: "Vous n'êtes pas autorisé à accéder à cette page."})
  }
  res.internalError = (err) => {
    res.status(500)
    log.warn('Get 500 on ' + req.originalUrl)
    console.error(err)
    res.render('errors/error', {title: 'Erreur interne', error: "Une erreur interne est survenue. Veuillez rééssayer."})
  }
  res.badRequest = (err) => {
    res.status(400)
    log.warn('Get 400 on ' + req.originalUrl)
    console.error(err)
    res.render('errors/error', {title: 'Requête invalide', error: "La requête envoyée au serveur est invalide."})
  }
  res.error = (code, message) => {
    res.status(code)
    log.warn('Get custom error (' + code + ': ' + message + ') on ' + req.originalUrl)
    res.render('errors/error', {
      code: code,
      error: message,
      title: 'Une erreur est survenue'
    })
  }
  next()
})

// ===========
//    BODY
// ===========
var bodyParser = require('body-parser')
app.use(bodyParser.urlencoded({extended: true}))
app.use(bodyParser.json())

// ===========
//   VIEWS
// ===========
app.use(express.static('public'))
app.set('view engine', 'jade')
app.set('views', path.join(__dirname, '/app/views'))

app.locals.version = fs.readFileSync('./VERSION')
const moment = require('moment')
moment.locale('fr')
app.locals.moment = moment

// =============
//   SESSIONS
// =============
app.set('trust proxy', 1) // trust first proxy
app.use(session({
  secret: '8Mta7Y4rX7KkBJLQ4cnc27Anp5L25b',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: false
  }
}))

// ===========
//   CONFIG
// ===========
const config = require('./app/config/global')
global.config = config

// ===========
//   ROUTES
// ===========
let routes = require('./app/config/routes')
for (var route in routes) {
  // Get action
  let action
  if (typeof routes[route] === 'object')
    action = routes[route].action
  else
    action = routes[route]
  let controller = action.split('.')[0]
  action = action.split('.')[1]
  // need auth
  let auth = (typeof routes[route] === 'object' && routes[route].auth)
  // get method
  let routeSplited = route.split(' ')
  route = (routeSplited.length === 1) ? routeSplited[0] : routeSplited[1]
  let method = (routeSplited.length === 1) ? 'get' : routeSplited[0]
  // push route
  if (auth)
    app[method](route, require('./app/middleware/auth'), require('./app/controller/' + controller)[action])
  else
    app[method](route, require('./app/controller/' + controller)[action])
}

// ===========
//   ERRORS
// ===========
app.use((req, res, next) => {
  res.status(404)
  log.warn('Get 404 on ' + req.originalUrl)
  res.render('errors/error', {title: 'Page introuvable', error: "La page demandée n'a pas été trouvée."})
})
app.use((req, res, next) => {
  res.status(403)
  log.warn('Get 403 on ' + req.originalUrl)
  res.render('errors/error', {title: 'Accès non autorisé', error: "Vous n'êtes pas autorisé à accéder à cette page."})
})
app.use((err, req, res, next) => {
  log.error('Get 500 on ' + req.originalUrl)
  console.error(err)
  res.status(500)
  res.render('errors/error', {title: 'Erreur interne', error: "Une erreur interne est survenue. Veuillez rééssayer."})
})

// ===========
//  LOGGING
// ===========
const clc = require('cli-color')
const consoleLogging = {
  info: clc.blue,
  warn: clc.yellow,
  error: clc.red
}
const log = {}
Object.keys(consoleLogging).forEach((method) => {
  log[method] = (message) => {
    console.log('[' + consoleLogging[method](method.toUpperCase()) + '] ' + message)
  }
})
global.log = log

// ===========
//   LISTEN
// ===========
app.listen(config.http.port, () => {
  log.info('Listen app on ' + config.http.port)
})
