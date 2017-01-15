<?php
session_start();
$logged = false;
if(isset($_SESSION['tracker']['logged']) && $_SESSION['tracker']['logged']) {
  $logged = true;
}
if($_POST) {
  if(!empty($_POST['password'])) {
    if(sha1($_POST['password']) == '6859b1de400153cd99edfb44d9ff317dd0080afb') {
      $_SESSION['tracker']['logged'] = true;
      $logged=true;
    }
  }
}
?>
<?php require 'include/header.php' ?>

    <!--header-->
    <div class="header">
      <div class="container">
        <div class="col-md-8">
          <h3>Tracker</h3>
          <h4>Outils d'administration</h4>
        </div>
      </div>
    </div>
  </div>

  <?php if(!$logged) { ?>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Connectez-vous</h3>
            </div>
            <div class="panel-body">
              <form action="" method="post">
                <input type="text" class="form-control" name="user" value="admin" style="display:none;">
                <div class="form-group">
                  <label>Mot de passe</label>
                  <input type="password" class="form-control" name="password">
                </div>
                <button type="submit" class="btn btn-success">Connexion</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php } else { ?>

    <div class="container">
      <div class="row">
        <div class="col-md-12">

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Différents panels</h3>
            </div>
            <div class="panel-body">
              <a href="http://tracker.obsifight.net/hack" class="btn btn-success btn-lg btn-block">Voir les dêgats d'un hack</a>
              <a href="http://tracker.obsifight.net/user" class="btn btn-success btn-lg btn-block">Tracker/Espionner un joueur</a>
              <a href="http://sanctions.obsifight.net/" class="btn btn-success btn-lg btn-block">Voir les sanctions</a>
              <a href="http://tracker.obsifight.net/ip" class="btn btn-success btn-lg btn-block">Rechercher un pseudo par IP</a>
              <a href="http://tracker.obsifight.net/pseudo" class="btn btn-success btn-lg btn-block">Rechercher une IP par pseudo</a>
              <a href="http://tracker.obsifight.net/pseudo" class="btn btn-success btn-lg btn-block">Rechercher une adresse MAC par pseudo</a>
              <a href="http://tracker.obsifight.net/mac" class="btn btn-success btn-lg btn-block">Rechercher un pseudo par adresse MAC</a>
            </div>
          </div>

        </div>
      </div>
    </div>

  <?php } ?>

<?php require 'include/footer.php' ?>
