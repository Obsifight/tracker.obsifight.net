<?php
session_start();
if(!isset($_SESSION['tracker']['logged']) || !$_SESSION['tracker']['logged']) {
  header('Location: /');
  exit;
}


ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set("memory_limit", "-1");
date_default_timezone_set('Europe/Paris');

//header('Content-Type: application/json');

require '../functions.php';
require '../db.class.php';
require '../config.php';

require '../tracker.class.php';
$data = array();

/*$tracker = new ObsiTracker(
  'theboss2645',
  '90.1.206.205',
  array(
    '2016-06-21 03:15:00',
    date('Y-m-d H:i:s', strtotime('+2 hour', strtotime('2016-06-21 03:15:00')))
  )
);*/
/*$tracker = new ObsiTracker(
  'Eywek',
  '176.150.149.78',
  array(
    '2016-06-21 03:15:00',
    date('Y-m-d H:i:s')
  )
);*/

$username = (isset($_GET['username'])) ? $_GET['username'] : null;
if(!empty($username)) {

  $ip = (isset($_GET['ip'])) ? $_GET['ip'] : null;
  $date = (isset($_GET['date'])) ? $_GET['date'] : null;
  $range = (isset($_GET['range'])) ? $_GET['range'] : '2 hours';

  $tracker = new ObsiTracker(
    $username,
    $ip,
    array(
      $date,
      date('Y-m-d H:i:s', strtotime('+'.$range, strtotime($date)))
    )
  );

  if($tracker->loginOnWebsite()) {

    $data['loginOnWebsite'] = true;
    $data['obsiguard_actions'] = $tracker->getObsiguardActions();
    $data['points_transfer'] = $tracker->getPointsTransfer();
    //$data['password_updates'] = $tracker->getPasswordUpdates();
    //$data['email_update'] = $tracker->getEmailUpdates();

  } else {
    $data['loginOnWebsite'] = false;
  }

  $data['launcher_logs'] = $tracker->getLauncherLogs();
  $data['ingame_login'] = $tracker->getIngameLogin();
  $data['ingame_messages'] = $tracker->getIngameMessages();
  $data['ingame_commands'] = $tracker->getIngameCommands();
  $data['ingame_blocks_placed'] = $tracker->getIngameBlocksPlaced();
  $data['ingame_blocks_destroyed'] = $tracker->getIngameBlocksDestroyed();

}
?>
<?php require '../include/header.php' ?>
    <!--header-->
    <div class="header">
      <div class="container">
        <div class="col-md-8">
          <h3>Tracker</h3>
          <h4>Apprenez rapidement ce qu'un hacker a fait !</h4>
        </div>
      </div>
    </div>
  </div>

  <?php if(empty($username)) { ?>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Choississez le joueur</h3>
            </div>
            <div class="panel-body">
              <form action="" method="get">
                <div class="form-group">
                  <label>Pseudo tracké</label>
                  <input type="text" class="form-control" name="username">
                </div>
                <div class="form-group">
                  <label>IP trackée</label>
                  <input type="text" class="form-control" name="ip">
                </div>
                <div class="form-group">
                  <label>Date du hack</label>
                  <input type="text" class="form-control" name="date" placeholder="Format: YYYY-MM-DD HH:MM:SS">
                </div>
                <div class="form-group">
                  <label>Durée du hack</label>
                  <input type="text" class="form-control" name="range" value="2 hours">
                </div>
                <button type="submit" class="btn btn-success">Récupérer les informations</button>
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
              <h3 class="panel-title">Informations sur les données</h3>
            </div>
            <div class="panel-body">
              <p>
                <b>Utilisateur tracké :</b> <?= $username ?>
              </p>
              <p>
                <b>IP trackée :</b> <?= $ip ?>
              </p>
              <p>
                <b>Date du hack :</b> <?= $date ?>
              </p>
              <p>
                <b>Durée du hack :</b> <?= $range ?>
              </p>
            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Actions sur le site</h3>
            </div>
            <div class="panel-body">
              <p>
                <b>Connexion au site :</b> <?= ($data['loginOnWebsite']) ? '<span class="label label-success">Oui</span>' : '<span class="label label-danger">Non</span>' ?>
              </p>
              <?php if($data['loginOnWebsite']) { ?>
                <hr>

                <h5>Actions avec ObsiGuard</h5>

                <table class="table">
                  <thead>
                    <tr>
                      <th>IP auteur</th>
                      <th>Action</th>
                      <th>IP cible <small class="text-muted">(Optionnel)</small></th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($data['obsiguard_actions'] as $action) {
                      echo '<tr>';
                        echo '<td>'.$action['ip'].'</td>';
                        echo '<td>'.$action['type'].'</td>';
                        echo '<td>'.$action['obsiguard_ip'].'</td>';
                        echo '<td>'.$action['date'].'</td>';
                      echo '</tr>';
                    }
                    ?>
                  </tbody>
                </table>

                <hr>

                <h5>Transfert de points</h5>

                <table class="table">
                  <thead>
                    <tr>
                      <th>À qui ?</th>
                      <th>Montant</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($data['points_transfer'] as $action) {
                      echo '<tr>';
                        echo '<td>'.$action['to'].'</td>';
                        echo '<td>'.$action['amount'].'</td>';
                        echo '<td>'.$action['date'].'</td>';
                      echo '</tr>';
                    }
                    ?>
                  </tbody>
                </table>
              <?php } ?>
            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Connexions</h3>
            </div>
            <div class="panel-body">
              <hr>

              <h5>Connexion au launcher</h5>

              <table class="table">
                <thead>
                  <tr>
                    <th>IP</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['launcher_logs'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['ip'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Connexion au jeu</h5>

              <table class="table">
                <thead>
                  <tr>
                    <th>IP</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame_login'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['ip'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Actions dans le tchat</h3>
            </div>
            <div class="panel-body">
              <hr>

              <h5>Messages envoyés</h5>

              <label>
                <input type="checkbox" onchange="toggle('#messages')">
                Voir les messages
              </label>
              <table class="table" style="display:none;" id="messages">
                <thead>
                  <tr>
                    <th>Message</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame_messages'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['message'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Commandes exécutées</h5>

              <label>
                <input type="checkbox" onchange="toggle('#commands')">
                Voir les commandes
              </label>
              <table class="table" style="display:none;" id="commands">
                <thead>
                  <tr>
                    <th>Command</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame_commands'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['command'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Actions sur les blocs</h3>
            </div>
            <div class="panel-body">
              <hr>

              <h5>Blocs placés <small class="text-muted">(<?= $data['ingame_blocks_placed']['count'] ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#placed_blocks')">
                Voir les blocs placés
              </label>
              <table class="table" style="display:none;" id="placed_blocks">
                <thead>
                  <tr>
                    <th>ID du bloc</th>
                    <th>Coordonnées</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame_blocks_placed']['list'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['type'].'</td>';
                      echo '<td>X: '.$action['coordinates']['x'].', Y: '.$action['coordinates']['y'].', Z: '.$action['coordinates']['z'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Blocs détruits <small class="text-muted">(<?= $data['ingame_blocks_destroyed']['count'] ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#destroyed_blocks')">
                Voir les blocs détruits
              </label>
              <table class="table" style="display:none;" id="destroyed_blocks">
                <thead>
                  <tr>
                    <th>ID du bloc</th>
                    <th>Coordonnées</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame_blocks_destroyed']['list'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['type'].'</td>';
                      echo '<td>X: '.$action['coordinates']['x'].', Y: '.$action['coordinates']['y'].', Z: '.$action['coordinates']['z'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

            </div>
          </div>


        </div>
      </div>
    </div>

  <?php } ?>

<?php require '../include/footer.php' ?>
