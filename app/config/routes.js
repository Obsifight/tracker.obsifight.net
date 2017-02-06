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
  },
  'get /user/:username/money': {
    action: 'UserController.getMoney',
    auth: true
  },
  'get /user/:username/accounts': {
    action: 'UserController.getDoubleAccounts',
    auth: true
  },
  'get /user/find/ip/:ip': {
    action: 'UserController.findByIP',
    auth: true
  },
  'get /user/find/mac/:mac': {
    action: 'UserController.findByMAC',
    auth: true
  }

}
