module.exports = {

  '/login': 'UserController.login',
  '/': {
    action: 'PageController.home',
    auth: true
  }

}
