module.exports = {

  'get /login': 'UserController.loginPage',
  'post /login': 'UserController.login',
  'get /': {
    action: 'PageController.home',
    auth: true
  }

}
