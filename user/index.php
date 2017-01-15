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

if(isset($_GET['user']) && !empty($_GET['user'])) {

  $username = $_GET['user'];

  require '../trackerdata.class.php';
  $tracker = new ObsiDataTracker($username);

  $user = $tracker->getWebProfile();

  if($user == false) {
    header("HTTP/1.0 404 Not Found");
    exit(json_encode(array(
      'error' => '404',
      'errorMessage' => 'User Not Found.'
    )));
  }

  $user
    ->getUsernameUpdates()
    ->getPointsTransfer()
    ->getEmailUpdates()
    ->getWebsiteIP()
    ->getLauncherIP()
    ->getIngameIP()
    ->getWebsiteCrediting()
    ->getWebsitePurchases()
    ->getObsiguardInfos()
      /*->getMessages()
      ->getCommands()*/
      ->getTchat()
    //->getSanctions()
    ->getIngameInfos()
      //->getDestroyedBlocks()
      //->getPlacedBlocks()
    ->getKillsDeathsRatio()
  ;

  $data = $tracker->getData();
}

//echo json_encode($tracker->getData(), JSON_PRETTY_PRINT);
?>
<?php require '../include/header.php' ?>
    <!--header-->
    <div class="header">
      <div class="container">
        <div class="col-md-8">
          <h3>Tracker</h3>
          <h4>Espionnez n'importe quel joueur !</h4>
        </div>
      </div>
    </div>
  </div>

  <?php if(!isset($username)) { ?>
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
                  <input type="text" class="form-control" name="user">
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
              <h3 class="panel-title">Informations globales sur le joueur</h3>
            </div>
            <div class="panel-body">
              <p>
                <b>Utilisateur tracké :</b> <?= $username ?> <small class="text-muted">(Premier pseudo: <?= $data['username']['first'] ?>)</small>
              </p>
              <p>
                <b>Email courant :</b> <?= $data['emails']['current'] ?> <small class="text-muted">(Premier email: <?= $data['emails']['first'] ?>)</small>
              </p>
              <p>
                <b>ID site :</b> <?= $data['website_id'] ?>
              </p>
              <p>
                <b>ID authentification :</b> <?= $data['auth_id'] ?>
              </p>
              <p>
                <b>ID logblock :</b> <?= $data['logblock_id'] ?>
              </p>
              <p>
                <b>Inscription :</b> Le <?= $data['signup']['date'] ?> avec l'IP <?= $data['signup']['ip'] ?>
              </p>
            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Informations InGame sur le joueur</h3>
            </div>
            <div class="panel-body">
              <p>
                <b>Dernière connexion :</b> <?= $data['ingame']['last_connection'] ?>
              </p>
              <p>
                <b>Temps de connexion :</b> <?= $data['ingame']['online_time'] ?> secondes
              </p>
              <p>
                <b>Tués :</b> <?= $data['ingame']['kills'] ?>
              </p>
              <p>
                <b>Morts :</b> <?= $data['ingame']['deaths'] ?>
              </p>
              <p>
                <b>Ratio :</b> <?= $data['ingame']['ratio'] ?>
              </p>
              <p>
                <b>UUID :</b> <?= $data['UUID'] ?>
              </p>
              <p>
                <b>UUID formatté :</b> <?= $data['UUID_formatted'] ?>
              </p>
              <p>
                <b>Adresse MAC :</b> <?= $data['adress']['mac'] ?>
              </p>
            </div>
          </div>

          <div class="panel panel-primary">
            <div class="panel-heading">
              <h3 class="panel-title">Informations sur ObsiGuard</h3>
            </div>
            <div class="panel-body">
              <p>
                <b>Actif :</b> <?= ($data['obsiguard']['used']) ? '<span class="label label-success">Oui</span>' : '<span class="label label-danger">Non</span>' ?>
              </p>
              <p>
                <b>Système d'IP dynamique :</b> <?= ($data['obsiguard']['dynamic_ip']) ? '<span class="label label-success">Oui</span>' : '<span class="label label-danger">Non</span>' ?>
              </p>

              <hr>

              <h5>Whitelist d'IP</h5>

              <table class="table">
                <thead>
                  <tr>
                    <th>IP</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if(is_array($data['obsiguard']['whitelist'])) {
                    foreach ($data['obsiguard']['whitelist'] as $ip) {
                      echo '<tr>';
                        echo '<td>'.$ip.'</td>';
                      echo '</tr>';
                    }
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Actions</h5>

              <label>
                <input type="checkbox" onchange="toggle('#obsiguard')">
                Voir les actions
              </label>

              <table class="table" style="display:none;" id="obsiguard">
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
                  foreach ($data['obsiguard']['actions'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['ip'].'</td>';
                      echo '<td>'.$action['type'].'</td>';
                      echo '<td>';
                        if(isset($action['ip_added'])) {
                          echo $action['ip_added'];
                        }
                        if(isset($action['ip_removed'])) {
                          echo $action['ip_removed'];
                        }
                      echo '</td>';
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
              <h3 class="panel-title">Actions sur le site</h3>
            </div>
            <div class="panel-body">
              <hr>

              <h5>Changements de pseudo</h5>

              <table class="table">
                <thead>
                  <tr>
                    <th>Nouveau pseudo</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['username']['updates'] as $pseudo => $date) {
                    echo '<tr>';
                      echo '<td>'.$pseudo.'</td>';
                      echo '<td>'.$date.'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Changements d'email</h5>

              <table class="table">
                <thead>
                  <tr>
                    <th>Nouvel email</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['emails']['updates'] as $email => $date) {
                    echo '<tr>';
                      echo '<td>'.$email.'</td>';
                      echo '<td>'.$date.'</td>';
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
                    <th>Nombre de connexion</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['adress']['ip']['used']['launcher'] as $ip => $infos) {
                    echo '<tr>';
                      echo '<td>'.$ip.'</td>';
                      echo '<td>'.$infos['count'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Connexion au site</h5>

              <table class="table">
                <thead>
                  <tr>
                    <th>IP</th>
                    <th>Nombre de connexion</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['adress']['ip']['used']['website'] as $ip => $infos) {
                    echo '<tr>';
                      echo '<td>'.$ip.'</td>';
                      echo '<td>'.$infos['count'].'</td>';
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
                    <th>Nombre de connexion</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['adress']['ip']['used']['ingame'] as $ip => $infos) {
                    echo '<tr>';
                      echo '<td>'.$ip.'</td>';
                      echo '<td>'.$infos['count'].'</td>';
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
              <?php /*
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
                  foreach ($data['ingame']['tchat']['messages'] as $action) {
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
                  foreach ($data['ingame']['tchat']['commands'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['command'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
*/ ?>
              <label>
                <input type="checkbox" onchange="toggle('#commands_messages')">
                Voir le tchat du joueur
              </label>
              <table class="table" style="display:none;" id="commands_messages">
                <thead>
                  <tr>
                    <th>Commande/Message</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame']['tchat'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['message_cmd'].'</td>';
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

              <h5>Blocs placés <small class="text-muted">(<?= $data['ingame']['logblock']['blocks_placed']['count'] ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#placed_blocks')">
                Voir les blocs placés
              </label>
              <table class="table" style="display:none;" id="placed_blocks">
                <thead>
                  <tr>
                    <th>ID du bloc</th>
                    <th>Data du bloc</th>
                    <th>Coordonnées</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame']['logblock']['blocks_placed']['list'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['bloc_id'].'</td>';
                      echo '<td>'.$action['bloc_data'].'</td>';
                      echo '<td>X: '.$action['coordinates']['x'].', Y: '.$action['coordinates']['y'].', Z: '.$action['coordinates']['z'].'</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Blocs détruits <small class="text-muted">(<?= $data['ingame']['logblock']['blocks_destroyed']['count'] ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#destroyed_blocks')">
                Voir les blocs détruits
              </label>
              <table class="table" style="display:none;" id="destroyed_blocks">
                <thead>
                  <tr>
                    <th>ID du bloc</th>
                    <th>Data du bloc</th>
                    <th>Coordonnées</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['ingame']['logblock']['blocks_destroyed']['list'] as $action) {
                    echo '<tr>';
                      echo '<td>'.$action['bloc_id'].'</td>';
                      echo '<td>'.$action['bloc_data'].'</td>';
                      echo '<td>X: '.$action['coordinates']['x'].', Y: '.$action['coordinates']['y'].', Z: '.$action['coordinates']['z'].'</td>';
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
              <h3 class="panel-title">Achats au cours des versions</h3>
            </div>
            <div class="panel-body">

              <h5>Achats V5 <small class="text-muted">(<?= count($data['purchases']['v5']) ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#purchases_v5')">
                Voir les achats
              </label>
              <table class="table" style="display:none;" id="purchases_v5">
                <thead>
                  <tr>
                    <th>ID de l'article <small class="text-muted">(Optionnel)</small></th>
                    <th>Nom de l'article</th>
                    <th>IP <small class="text-muted">(Optionnel)</small></th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['purchases']['v5'] as $purchase) {
                    echo '<tr>';
                      echo '<td>';
                        if(isset($purchase['item_id'])) {
                          echo $purchase['item_id'];
                        }
                      echo '</td>';
                      echo '<td>'.$purchase['item_name'].'</td>';
                      echo '<td>';
                        if(isset($purchase['ip'])) {
                          echo $purchase['ip'];
                        }
                      echo '</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Achats V4 <small class="text-muted">(<?= count($data['purchases']['v4']) ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#purchases_v4')">
                Voir les achats
              </label>
              <table class="table" style="display:none;" id="purchases_v4">
                <thead>
                  <tr>
                    <th>ID de l'article <small class="text-muted">(Optionnel)</small></th>
                    <th>Nom de l'article</th>
                    <th>IP <small class="text-muted">(Optionnel)</small></th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['purchases']['v4'] as $purchase) {
                    echo '<tr>';
                      echo '<td>';
                        if(isset($purchase['item_id'])) {
                          echo $purchase['item_id'];
                        }
                      echo '</td>';
                      echo '<td>'.$purchase['item_name'].'</td>';
                      echo '<td>';
                        if(isset($purchase['ip'])) {
                          echo $purchase['ip'];
                        }
                      echo '</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Achats V3 <small class="text-muted">(<?= count($data['purchases']['v3']) ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#purchases_v3')">
                Voir les achats
              </label>
              <table class="table" style="display:none;" id="purchases_v3">
                <thead>
                  <tr>
                    <th>ID de l'article <small class="text-muted">(Optionnel)</small></th>
                    <th>Nom de l'article</th>
                    <th>IP <small class="text-muted">(Optionnel)</small></th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['purchases']['v3'] as $purchase) {
                    echo '<tr>';
                      echo '<td>';
                        if(isset($purchase['item_id'])) {
                          echo $purchase['item_id'];
                        }
                      echo '</td>';
                      echo '<td>'.$purchase['item_name'].'</td>';
                      echo '<td>';
                        if(isset($purchase['ip'])) {
                          echo $purchase['ip'];
                        }
                      echo '</td>';
                      echo '<td>'.$action['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Achats V2 <small class="text-muted">(<?= count($data['purchases']['v2']) ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#purchases_v2')">
                Voir les achats
              </label>
              <table class="table" style="display:none;" id="purchases_v2">
                <thead>
                  <tr>
                    <th>ID de l'article <small class="text-muted">(Optionnel)</small></th>
                    <th>Nom de l'article</th>
                    <th>IP <small class="text-muted">(Optionnel)</small></th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['purchases']['v2'] as $purchase) {
                    echo '<tr>';
                      echo '<td>';
                        if(isset($purchase['item_id'])) {
                          echo $purchase['item_id'];
                        }
                      echo '</td>';
                      echo '<td>'.$purchase['item_name'].'</td>';
                      echo '<td>';
                        if(isset($purchase['ip'])) {
                          echo $purchase['ip'];
                        }
                      echo '</td>';
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
              <h3 class="panel-title">Ajout de crédits V5</h3>
            </div>
            <div class="panel-body">

              <h5>StarPass <small class="text-muted">(<?= count($data['crediting']['v5']['starpass']) ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#starpass')">
                Voir les achats
              </label>
              <table class="table" style="display:none;" id="starpass">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Nom de l'offre</th>
                    <th>PB ajoutés</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['crediting']['v5']['starpass'] as $purchase) {
                    echo '<tr>';
                      echo '<td>'.$purchase['code'].'</td>';
                      echo '<td>'.$purchase['offer_name'].'</td>';
                      echo '<td>'.$purchase['credits_gived'].'</td>';
                      echo '<td>'.$purchase['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>Dédipass <small class="text-muted">(<?= count($data['crediting']['v5']['dedipass']) ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#dedipass')">
                Voir les achats
              </label>
              <table class="table" style="display:none;" id="dedipass">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Offre/Palier</th>
                    <th>PB ajoutés</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['crediting']['v5']['dedipass'] as $purchase) {
                    echo '<tr>';
                      echo '<td>'.$purchase['code'].'</td>';
                      echo '<td>'.$purchase['rate'].'</td>';
                      echo '<td>'.$purchase['credits_gived'].'</td>';
                      echo '<td>'.$purchase['date'].'</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>

              <hr>

              <h5>PaySafeCard <small class="text-muted">(<?= count($data['crediting']['v5']['paysafecard']) ?>)</small></h5>

              <label>
                <input type="checkbox" onchange="toggle('#psc')">
                Voir les achats
              </label>
              <table class="table" style="display:none;" id="psc">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Montant</th>
                    <th>PB ajoutés</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($data['crediting']['v5']['paysafecard'] as $purchase) {
                    echo '<tr>';
                      echo '<td>'.$purchase['code'].'</td>';
                      echo '<td>'.$purchase['amount'].'</td>';
                      echo '<td>'.$purchase['credits_gived'].'</td>';
                      echo '<td>'.$purchase['date'].'</td>';
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
