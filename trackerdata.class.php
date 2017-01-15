<?php

class ObsiDataTracker {

  private $data = array(
    'website_id' => 0,
    'logblock_id' => 0,
    'auth_id' => 0,
    'username' => array(
      'current' => '',
      'first' => '',
      'updates' => array()
    ),
    'UUID' => '',
    'UUID_formatted' => '',
    'signup' => array(
      'date' => '1970-01-01 00:00:00',
      'ip' => '127.0.0.1'
    ),
    'points_transfer' => array(),
    'emails' => array(
      'current' => '',
      'first' => '',
      'updates' => array()
    ),
    'ingame' => array(
      'last_connection' => '1970-01-01 00:00:00',
      'online_time' => 0,
      'kills' => 0,
      'deaths' => 0,
      'ratio' => 0,
      'logblock' => array(
        'blocks_destroyed' => array(
          'count' => 0,
          'list' => array()
        ),
        'blocks_placed' => array(
          'count' => 0,
          'list' => array()
        )
      ),
      /*'tchat' => array(
        'messages' => array(),
        'commands' => array()
      )*/
      'tchat' => array()
    ),
    'adress' => array(
      'ip' => array(
        'last' => array(
          'website' => '127.0.0.1',
          'launcher' => '127.0.0.1',
          'ingame' => '127.0.0.1'
        ),
        'used' => array(
          'website' => array(),
          'launcher' => array(),
          'ingame' => array()
        )
      ),
      'mac' => ''
    ),
    'crediting' => array(
      'v5' => array(
        'starpass' => array(),
        'dedipass' => array(),
        'paysafecard' => array()
      )
    ),
    'purchases' => array(
      'v5' => array(),
      'v4' => array(),
      'v3' => array(),
      'v2' => array()
    ),
    'obsiguard' => array(
      'used' => false,
      'dynamic_ip' => false,
      'whitelist' => array(),
      'actions' => array()
    ),
    'sanctions' => array(
      'banned' => false,
      'muted' => false,
      'list' => array(
        'his' => array(),
        'gived' => array()
      )
    )
  );

  public function __construct($username) {
    /*require 'trackerdata.class.php';
    $this->tracker = new ObsiTracker($username);*/

    $this->username = $username;
  }

  public function getWebProfile() {
    $user = Database::get('web_v5')->fetch('SELECT * FROM users WHERE pseudo=?', array($this->username));
    if(!empty($user)) {

      $this->data['website_id'] = $user['id'];

      $this->data['username']['current'] = $user['pseudo'];
      $this->data['username']['first'] = $user['pseudo'];

      $this->data['signup']['ip'] = $user['ip'];
      $this->data['signup']['date'] = $user['created'];

      $this->data['emails']['current'] = $user['email'];
      $this->data['emails']['first'] = $user['email'];

      return $this;
    }
    return false;
  }

  public function getPointsTransfer() {
    $find = Database::get('web_v5')->fetchAll(
      "SELECT * FROM `histories` WHERE  `user_id` = ? AND action = 'SEND_MONEY'",
      array($this->data['website_id'])
    );

    foreach ($find as $transfer) {
      $this->data['points_transfer'][] = array(
        'to' => explode('|', $transfer['other'])[0],
        'amount' => explode('|', $transfer['other'])[1],
        'date' => $transfer['created']
      );
    }

    return $this;

  }

  public function getUsernameUpdates() {
    $updates = Database::get('web_v5')->fetchAll('SELECT * FROM obsi__pseudo_update_histories WHERE user_id=?', array($this->data['website_id']));

    $i = 0;
    foreach ($updates as $update) {
      $i++;

      if($i == 1) {
        $this->data['username']['first'] = $update['old_pseudo'];
      }

      $this->data['username']['updates'][$update['new_pseudo']] = $update['created'];

    }

    return $this;

  }

  public function getEmailUpdates() {
    $updates = Database::get('web_v5')->fetchAll('SELECT * FROM obsi__email_update_histories WHERE user_id=?', array($this->data['website_id']));

    $i = 0;
    foreach ($updates as $update) {
      $i++;

      if($i == 1) {
        $this->data['emails']['first'] = $update['old_email'];
      }

      $this->data['emails']['updates'][$update['new_email']] = $update['created'];

    }

    return $this;
  }

  public function getWebsiteIP() {
    $connections = Database::get('web_v5')->fetchAll("SELECT `ip`,COUNT(*) AS count, GROUP_CONCAT(`created`) AS dates FROM  `obsi__connection_logs` WHERE  `user_id` = ? GROUP BY  `ip`", array($this->data['website_id']));

    foreach ($connections as $connection) {

      $this->data['adress']['ip']['last']['website'] = $connection['ip'];
      $this->data['adress']['ip']['used']['website'][$connection['ip']] = array(
        'count' => $connection['count'],
        'dates' => explode(',', $connection['dates'])
      );

    }

    return $this;
  }

  public function getLauncherIP() {
    $connections = Database::get('launcherlogs')->fetchAll("SELECT `ip`,COUNT(*) AS count, GROUP_CONCAT(`date`) AS dates FROM `loginlogs` WHERE `username` = ? GROUP BY `ip`", array($this->username));

    foreach ($connections as $connection) {

      $this->data['adress']['ip']['last']['launcher'] = $connection['ip'];
      $this->data['adress']['ip']['used']['launcher'][$connection['ip']] = array(
        'count' => $connection['count'],
        'dates' => explode(',', $connection['dates'])
      );

    }

    return $this;
  }

  public function getIngameIP() {
    $connections = Database::get('playerlogger')->fetchAll("SELECT `data` AS ip,COUNT(*) AS count, GROUP_CONCAT(`time`) AS dates FROM `playerlogger` WHERE `playername` = ? AND type = 'join' GROUP BY `ip`", array($this->username));

    foreach ($connections as $connection) {

      $this->data['adress']['ip']['last']['ingame'] = $connection['ip'];

      $times = explode(',', $connection['dates']);
      $dates = array();
      foreach ($times as $time) {
        $dates[] = date('Y-m-d H:i:s', $time);
      }

      $this->data['adress']['ip']['used']['ingame'][$connection['ip']] = array(
        'count' => $connection['count'],
        'dates' => $dates
      );

    }
    return $this;
  }

  public function getWebsiteCrediting() {
    $starpasses = Database::get('web_v5')->fetchAll("SELECT * FROM `shop__starpass_histories` WHERE  `user_id` = ?", array($this->data['website_id']));
    $search_starpasses_offer = Database::get('web_v5')->fetchAll("SELECT * FROM `shop__starpasses`", array());
    $starpasses_offer = array();

    foreach ($search_starpasses_offer as $offer) {
      $starpasses_offer[$offer['id']] = $offer['name'];
    }

    foreach ($starpasses as $starpass) {

      $this->data['crediting']['v5']['starpass'][] = array(
        'code' => $starpass['code'],
        'offer_name' => @$starpasses_offer[$starpass['offer_id']],
        'credits_gived' => $starpass['credits_gived'],
        'date' => $starpass['created']
      );

    }

    unset($starpasses);
    unset($search_starpasses_offer);
    unset($starpasses_offer);


    $dedipasses = Database::get('web_v5')->fetchAll("SELECT * FROM `shop__dedipass_histories` WHERE  `user_id` = ?", array($this->data['website_id']));

    foreach ($dedipasses as $dedipass) {

      $this->data['crediting']['v5']['dedipass'][] = array(
        'code' => $dedipass['code'],
        'rate' => $dedipass['rate'],
        'credits_gived' => $dedipass['credits_gived'],
        'date' => $dedipass['created']
      );

    }
    unset($dedipasses);


    $paysafecards = Database::get('web_v5')->fetchAll("SELECT * FROM `shop__paysafecard_histories` WHERE  `user_id` = ?", array($this->data['website_id']));

    foreach ($paysafecards as $paysafecard) {

      $this->data['crediting']['v5']['paysafecard'][] = array(
        'code' => $paysafecard['code'],
        'amount' => $paysafecard['amount'],
        'credits_gived' => $paysafecard['credits_gived'],
        'date' => $paysafecard['created']
      );

    }
    unset($paysafecards);

    return $this;
  }

  public function getWebsitePurchases() {
    // V5
      $purchases = Database::get('web_v5')->fetchAll("SELECT * FROM `shop__items_buy_histories` WHERE  `user_id` = ?", array($this->data['website_id']));
      $search_items = Database::get('web_v5')->fetchAll("SELECT * FROM `shop__items`", array());
      $items = array();

      foreach ($search_items as $item) {
        $items[$item['id']] = $item['name'];
      }

      foreach ($purchases as $purchase) {

        $this->data['purchases']['v5'][] = array(
          'item_id' => $purchase['item_id'],
          'item_name' => (isset($items[$purchase['item_id']])) ? $items[$purchase['item_id']] : 'N/A',
          'date' => $purchase['created']
        );

      }

      unset($items);
      unset($purchases);
      unset($search_items);

    // V4
      $purchases = Database::get('web_v4')->fetchAll("SELECT * FROM `histories` WHERE action = 'BUY_ITEM' AND `author` = ?", array($this->username));

      foreach ($purchases as $purchase) {

        $this->data['purchases']['v4'][] = array(
          'item_name' => $purchase['other'],
          'date' => $purchase['created']
        );

      }

      unset($purchases);

    // V3
      $purchases = Database::get('web_v3')->fetchAll("SELECT * FROM `historique` WHERE  `joueur` = ?", array($this->username));
      $search_items = Database::get('web_v3')->fetchAll("SELECT * FROM `boutique`", array());
      $items = array();

      foreach ($search_items as $item) {
        $items[$item['id']] = $item['nom'];
      }

      foreach ($purchases as $purchase) {

        $this->data['purchases']['v3'][] = array(
          'item_id' => $purchase['nom_offre'],
          'item_name' => (isset($items[$purchase['nom_offre']])) ? $items[$purchase['nom_offre']] : 'N/A',
          'ip' => $purchase['adresse_ip'],
          'date' => date('Y-m-d H:i:s', $purchase['date_achat'])
        );

      }

      unset($items);
      unset($purchases);
      unset($search_items);

    // V2
      $purchases = Database::get('web_v2')->fetchAll("SELECT * FROM `historique` WHERE  `joueur` = ?", array($this->username));
      $search_items = Database::get('web_v2')->fetchAll("SELECT * FROM `boutique`", array());
      $items = array();

      foreach ($search_items as $item) {
        $items[$item['id']] = $item['nom'];
      }

      foreach ($purchases as $purchase) {

        $this->data['purchases']['v2'][] = array(
          'item_id' => $purchase['nom_offre'],
          'item_name' => (isset($items[$purchase['nom_offre']])) ? $items[$purchase['nom_offre']] : 'N/A',
          'ip' => $purchase['adresse_ip'],
          'date' => date('Y-m-d H:i:s', $purchase['date_achat'])
        );

      }

      unset($items);
      unset($purchases);
      unset($search_items);

    return $this;
  }

  public function getObsiguardInfos() {
    $obsiguard_histories = Database::get('web_v5')->fetchAll("SELECT * FROM `obsi__obsiguard_histories` WHERE  `user_id` = ?", array($this->data['website_id']));
    $obsiguard = Database::get('auth')->fetch("SELECT * FROM `joueurs` WHERE  `user_pseudo` = ?", array($this->username));

    $this->data['adress']['mac'] = $obsiguard['mac_adress'];
    $this->data['auth_id'] = $obsiguard['user_id'];

    $this->data['obsiguard']['used'] = ($obsiguard['authorised_ip'] != null);
    $this->data['obsiguard']['dynamic_ip'] = ($obsiguard['authorised_ip'] == '1') ? true : false;
    $this->data['obsiguard']['whitelist'] = unserialize($obsiguard['authorised_ip']);

    foreach ($obsiguard_histories as $action) {

      switch ($action['type']) {
        case 1:
          $type = 'enable';
          break;
        case 2:
          $type = 'disable';
          break;
        case 3:
          $type = 'addIP';
          break;
        case 4:
          $type = 'removeIP';
          break;
        case 5:
          $type = 'enableDynamicIP';
          break;
        case 6:
          $type = 'disableDynamicIP';
          break;
        case 7:
          $type = 'generateConfirmCode';
          break;
        default:
          $type = $action['type'];
          break;
      }

      $add = array(
        'ip' => $action['ip'],
        'type' => $type,
        'date' => $action['created']
      );

      if($type == 'addIP') {
        $add['ip_added'] = $action['obsiguard_ip'];
      }
      if($type == 'removeIP') {
        $add['ip_removed'] = $action['obsiguard_ip'];
      }

      $this->data['obsiguard']['actions'][] = $add;

    }

    return $this;
  }

  public function getMessages() {
    $messages = Database::get('playerlogger')->fetchAll("SELECT `data` AS message,`time` FROM `playerlogger` WHERE `playername` = ? AND type = 'chat'", array($this->username));

    foreach ($messages as $message) {

      $this->data['ingame']['tchat']['messages'][] = array(
        'message' => $message['message'],
        'date' => date('Y-m-d H:i:s', $message['time'])
      );

    }

    return $this;
  }

  public function getCommands() {
    $commands = Database::get('playerlogger')->fetchAll("SELECT `data` AS command,`time` FROM `playerlogger` WHERE `playername` = ? AND type = 'command'", array($this->username));

    foreach ($commands as $command) {

      $this->data['ingame']['tchat']['commands'][] = array(
        'command' => $command['command'],
        'date' => date('Y-m-d H:i:s', $command['time'])
      );

    }

    return $this;
  }

  public function getTchat() {
    $messages = Database::get('playerlogger')->fetchAll("SELECT `data` AS message_cmd,`time` FROM `playerlogger` WHERE `playername` = ? AND type = 'command' OR type= 'chat'", array($this->username));

    foreach ($messages as $message) {

      $this->data['ingame']['tchat'][] = array(
        'message_cmd' => $message['message_cmd'],
        'date' => date('Y-m-d H:i:s', $message['time'])
      );

    }

    return $this;
  }

  public function getSanctions() {
    $getUUID = Database::get('sanctions')->fetch('SELECT UUID FROM BAT_players WHERE BAT_player=?', array($this->username));

    if(!empty($getUUID)) {

      $this->data['UUID'] = $getUUID['UUID'];

      $isMuted = Database::get('sanctions')->fetch('SELECT mute_id FROM BAT_mute WHERE UUID=? AND mute_state=1', array($this->data['UUID']));
      $this->data['sanctions']['muted'] = (!empty($isMuted));
      unset($isMuted);

      $isBanned = Database::get('sanctions')->fetch('SELECT ban_id FROM BAT_ban WHERE UUID=? AND ban_state=1', array($this->data['UUID']));
      $this->data['sanctions']['banned'] = (!empty($isBanned));
      unset($isBanned);

      /*
        On récupère les sanctions qu'il a subit
      */

        $hisBans = Database::get('sanctions')->fetchAll('SELECT * FROM BAT_ban WHERE UUID=?', array($this->data['UUID']));
        foreach ($hisBans as $ban) {

          $this->data['sanctions']['list']['his'][] = array(
            'type' => 'ban',
            'id' => $ban['ban_id'],
            'from' => $ban['ban_staff'],
            'start' => $ban['ban_begin'],
            'end' => $ban['ban_end'],
            'reason' => $ban['ban_reason'],
            'status' => $ban['ban_state'],
            'removed_date' => $ban['ban_unbandate'],
            'removed_by' => $ban['ban_unbanstaff'],
            'removed_reason' => $ban['ban_unbanreason']
          );

        }
        unset($hisBans);

        $hisMutes = Database::get('sanctions')->fetchAll('SELECT * FROM BAT_mute WHERE UUID=?', array($this->data['UUID']));
        foreach ($hisMutes as $mute) {

          $this->data['sanctions']['list']['his'][] = array(
            'type' => 'mute',
            'id' => $mute['mute_id'],
            'from' => $mute['mute_staff'],
            'start' => $mute['mute_begin'],
            'end' => $mute['mute_end'],
            'reason' => $mute['mute_reason'],
            'status' => $mute['mute_state'],
            'removed_date' => $mute['mute_unmutedate'],
            'removed_by' => $mute['mute_unmutestaff'],
            'removed_reason' => $mute['mute_unmutereason']
          );

        }
        unset($hisMutes);

        $hisKicks = Database::get('sanctions')->fetchAll('SELECT * FROM BAT_kick WHERE UUID=?', array($this->data['UUID']));
        foreach ($hisKicks as $kick) {

          $this->data['sanctions']['list']['his'][] = array(
            'type' => 'kick',
            'id' => $kick['kick_id'],
            'from' => $kick['kick_staff'],
            'reason' => $kick['kick_reason']
          );

        }
        unset($hisKicks);

      /*
        On récupère les sanctions qu'il a envoyé
      */

        $hisBans = Database::get('sanctions')->fetchAll('SELECT * FROM BAT_ban WHERE ban_staff=?', array($this->username));
        foreach ($hisBans as $ban) {

          if(!filter_var($ban['UUID'], FILTER_VALIDATE_IP)) {
            $ban['UUID'] = Database::get('sanctions')->fetch('SELECT BAT_player FROM BAT_players WHERE UUID=?', array($ban['UUID']))['BAT_player'];
          }

          $this->data['sanctions']['list']['gived'][] = array(
            'type' => 'ban',
            'id' => $ban['ban_id'],
            'to' => $ban['UUID'],
            'start' => $ban['ban_begin'],
            'end' => $ban['ban_end'],
            'reason' => $ban['ban_reason'],
            'status' => $ban['ban_state'],
            'removed_date' => $ban['ban_unbandate'],
            'removed_by' => $ban['ban_unbanstaff'],
            'removed_reason' => $ban['ban_unbanreason']
          );

        }
        unset($hisBans);

        $hisMutes = Database::get('sanctions')->fetchAll('SELECT * FROM BAT_mute WHERE mute_staff=?', array($this->username));
        foreach ($hisMutes as $mute) {

          if(!filter_var($mute['UUID'], FILTER_VALIDATE_IP)) {
            $mute['UUID'] = Database::get('sanctions')->fetch('SELECT BAT_player FROM BAT_players WHERE UUID=?', array($mute['UUID']))['BAT_player'];
          }

          $this->data['sanctions']['list']['gived'][] = array(
            'type' => 'mute',
            'id' => $mute['mute_id'],
            'to' => $mute['UUID'],
            'start' => $mute['mute_begin'],
            'end' => $mute['mute_end'],
            'reason' => $mute['mute_reason'],
            'status' => $mute['mute_state'],
            'removed_date' => $mute['mute_unmutedate'],
            'removed_by' => $mute['mute_unmutestaff'],
            'removed_reason' => $mute['mute_unmutereason']
          );

        }
        unset($hisMutes);

        $hisKicks = Database::get('sanctions')->fetchAll('SELECT * FROM BAT_kick WHERE kick_staff=?', array($this->username));
        foreach ($hisKicks as $kick) {

          if(!filter_var($kick['UUID'], FILTER_VALIDATE_IP)) {
            $kick['UUID'] = Database::get('sanctions')->fetch('SELECT BAT_player FROM BAT_players WHERE UUID=?', array($kick['UUID']))['BAT_player'];
          }

          $this->data['sanctions']['list']['gived'][] = array(
            'type' => 'kick',
            'id' => $kick['kick_id'],
            'to' => $kick['UUID'],
            'reason' => $kick['kick_reason']
          );

        }
        unset($hisKicks);

    }

    unset($getUUID);

    return $this;
  }

  public function getIngameInfos() {
    $lb_player = Database::get('logblock')->fetch('SELECT * FROM `lb-players` WHERE playername=?', array($this->username));
    $this->data['UUID_formatted'] = $lb_player['UUID'];
    $this->data['ingame']['last_connection'] = $lb_player['lastlogin'];
    $this->data['ingame']['online_time'] = $lb_player['onlinetime'];
    $this->data['logblock_id'] = $lb_player['playerid'];

    return $this;
  }

  public function getDestroyedBlocks() {
    $getDestroyedBlocks = Database::get('logblock')->fetchAll('SELECT replaced,data,x,y,z,`date` FROM `lb-FACTION` WHERE playerid=? AND type=0', array($this->data['logblock_id']));
    $this->data['ingame']['logblock']['blocks_destroyed']['count'] = count($getDestroyedBlocks);

    foreach ($getDestroyedBlocks as $block) {
      $this->data['ingame']['logblock']['blocks_destroyed']['list'][] = array(
        'block_id' => $block['replaced'],
        'block_data' => $block['data'],
        'date' => $block['date'],
        'coordinates' => array(
          'x' => $block['x'],
          'y' => $block['y'],
          'z' => $block['z']
        )
      );
    }

    return $this;
  }

  public function getPlacedBlocks() {
    $getPlacedBlocks = Database::get('logblock')->fetchAll('SELECT type,data,x,y,z,`date` FROM `lb-FACTION` WHERE playerid=? AND replaced=0', array($this->data['logblock_id']));
    $this->data['ingame']['logblock']['blocks_placed']['count'] = count($getPlacedBlocks);

    foreach ($getPlacedBlocks as $block) {
      $this->data['ingame']['logblock']['blocks_placed']['list'][] = array(
        'block_id' => $block['type'],
        'block_data' => $block['data'],
        'date' => $block['date'],
        'coordinates' => array(
          'x' => $block['x'],
          'y' => $block['y'],
          'z' => $block['z']
        )
      );
    }

    return $this;
  }

  public function getKillsDeathsRatio() {
    $killstats = Database::get('killstats')->fetch('SELECT kills,deaths,ratio FROM killstats_data WHERE playerName=?', array($this->username));

    if(!empty($killstats)) {
      $this->data['ingame']['kills'] = $killstats['kills'];
      $this->data['ingame']['deaths'] = $killstats['deaths'];
      $this->data['ingame']['ratio'] = $killstats['ratio'];
    }

    return $this;
  }

  public function getData() {
    return $this->data;
  }

}
