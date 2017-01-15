module.exports = (req, res, next) => {
  if (!req.session.auth) // redirect
    return res.redirect('/login?b=' + req.originalUrl)
  else
    req.user = req.session.auth.user

  return next()
}
