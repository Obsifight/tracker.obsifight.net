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

if(isset($_GET['ip']) && !empty($_GET['ip'])) {

  $ip = $_GET['ip'];
  $data = array();

  $connections = Database::get('launcherlogs')->fetchAll("SELECT * FROM `loginlogs` WHERE `ip` = ? ORDER BY id DESC", array($ip));
  $data['connections'] = $connections;
  $data['count_connections'] = count($connections);

  $accounts = Database::get('launcherlogs')->fetchAll("SELECT username FROM `loginlogs` WHERE `ip` = ? GROUP BY username", array($ip));
  $data['count_accounts'] = count($accounts);

}

//echo json_encode($tracker->getData(), JSON_PRETTY_PRINT);
?>
<?php require '../include/header.php' ?>
    <!--header-->
    <div class="header">
      <div class="container">
        <div class="col-md-8">
          <h3>Tracker</h3>
          <h4>Retrouver les connexion des joueurs avec une IP</h4>
        </div>
      </div>
    </div>
  </div>

  <?php if(!isset($ip)) { ?>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Choississez l'IP</h3>
            </div>
            <div class="panel-body">
              <form action="" method="get">
                <div class="form-group">
                  <label>IP trackée</label>
                  <input type="text" class="form-control" name="ip">
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
              <h3 class="panel-title">Informations globales</h3>
            </div>
            <div class="panel-body">
              <p>
                <b>IP trackée :</b> <?= $ip ?>
              </p>
              <p>
                <b>Nombre d'utilisations :</b> <?= $data['count_connections'] ?>
              </p>
              <p>
                <b>Nombre de compte différents :</b> <?= $data['count_accounts'] ?>
              </p>
            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Connexions</h3>
            </div>
            <div class="panel-body">

              <table class="table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Pseudo</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['connections'] as $connection) {
                    echo '<tr>';
                      echo '<td>'.$connection['id'].'</td>';
                      echo '<td>'.$connection['username'].'</td>';
                      echo '<td>'.$connection['date'].'</td>';
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.9/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.9/js/dataTables.bootstrap.min.js"></script>
    <script>
    $(document).ready(function(){

        $('table').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": false,
          "ordering": false,
          "info": false,
          "autoWidth": false,
          'searching': true
        });

    });
    </script>

  <?php } ?>

  <div class="clearfix"></div>

  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-md-8">
          <p class="text-muted">© ObsiFight 2016</p>
        </div>
        <div class="col-md-4">
          <p class="text-muted">
            Développé et maintenu par <a href="http://eywek.fr">Eywek</a>.
          </p>
        </div>
      </div>
    </div>
  </footer>
  </body>
</html>
