module.exports = {

  home: (req, res) => {
    res.render('page/home', {
      title: 'Accueil'
    })
  }

}
