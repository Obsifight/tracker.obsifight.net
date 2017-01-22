module.exports = {

  'get /login': 'UserController.loginPage',
  'post /login': 'UserController.login',
  'get /': {
    action: 'PageController.home',
    auth: true
  },

  'get /user/:username': {
    action: 'UserController.get',
    auth: true
  },
  'get /user/:username/sanctions': {
    action: 'UserController.getSanctions',
    auth: true
  }

}
