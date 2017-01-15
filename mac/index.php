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

if(isset($_GET['mac_adress']) && !empty($_GET['mac_adress'])) {

  $mac_adress = $_GET['mac_adress'];
  $data = array();

  $accounts = Database::get('auth')->fetchAll("SELECT user_pseudo FROM `joueurs` WHERE `mac_adress` = ?", array($mac_adress));
  $data['accounts'] = $accounts;
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
          <h4>Retrouver les connexion des joueurs avec une adresse MAC</h4>
        </div>
      </div>
    </div>
  </div>

  <?php if(!isset($mac_adress)) { ?>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Choississez l'adresse MAC</h3>
            </div>
            <div class="panel-body">
              <form action="" method="get">
                <div class="form-group">
                  <label>Adresse MAC trackée</label>
                  <input type="text" class="form-control" name="mac_adress">
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
              <h3 class="panel-title">Informations</h3>
            </div>
            <div class="panel-body">
              <p>
                <b>Adresse MAC trackée :</b> <?= $mac_adress ?>
              </p>
              <p>
                <b>Nombre de compte différents :</b> <?= $data['count_accounts'] ?>
              </p>
            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Liste des comptes</h3>
            </div>
            <div class="panel-body">

              <table class="table">
                <thead>
                  <tr>
                    <th>Pseudo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['accounts'] as $connection) {
                    echo '<tr>';
                      echo '<td>'.$connection['user_pseudo'].'</td>';
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
