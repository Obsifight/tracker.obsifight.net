<?php

class ObsiTracker {

  public function __construct($username, $ip = NULL, $range = NULL, $date = NULL) {
    $this->username = $username;
    $this->ip = $ip;
    $this->date = $date;
    $this->range = $range;
  }

  private function __makeConditions($username = true, $ipField = 'ip', $dateField = 'created', $timestamp = false) {
    if($username) {
      $sql = "username='$this->username'";
    } else {
      $sql = "";
    }

    $range = $this->range;
    if($timestamp) {
      $range[0] = strtotime($range[0]);
      $range[1] = strtotime($range[1]);
    }

    if(!empty($this->ip) && !empty($this->range) && $ipField !== false) {
      $sql .= " AND $ipField='$this->ip' AND $dateField >= '{$range[0]}' AND $dateField <= '{$range[1]}'";
    } elseif(!empty($this->range)) {
      $sql .= " AND $dateField >= '{$range[0]}' AND $dateField <= '{$range[1]}'";
    } elseif(!empty($this->date)) {
      $sql .= " AND $dateField='$this->date'";
    }

    return $sql;
  }

  private function __getWebsiteID() {
    if(!isset($this->websiteID)) {
      $user = Database::get('web_v5')->fetch('SELECT * FROM users WHERE pseudo=?', array($this->username));
      if(empty($user)) {
        return false;
      }
      $this->websiteID = $user['id'];
    }
    return $this->websiteID;
  }

  public function loginOnWebsite() {
    $connections = Database::get('web_v5')->fetchAll(
      "SELECT * FROM `obsi__connection_logs` WHERE  `user_id` = ? {$this->__makeConditions(false)}",
      array($this->__getWebsiteID())
    );

    return !empty($connections);
  }

  public function getObsiguardActions() {
    $data = array();
    $obsiguard_histories = Database::get('web_v5')->fetchAll("SELECT * FROM `obsi__obsiguard_histories` WHERE  `user_id` = ? {$this->__makeConditions(false)}", array($this->__getWebsiteID()));

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
        'date' => $action['created'],
        'obsiguard_ip' => $action['obsiguard_ip']
      );

      $data[] = $add;

    }
    return $data;

  }

  public function getPointsTransfer() {
    $data = array();
    $find = Database::get('web_v5')->fetchAll(
      "SELECT * FROM `histories` WHERE  `user_id` = ? AND action = 'SEND_MONEY' {$this->__makeConditions(false, false)}",
      array($this->__getWebsiteID())
    );

    foreach ($find as $transfer) {
      $data[] = array(
        'to' => explode('|', $transfer['other'])[0],
        'amount' => explode('|', $transfer['other'])[1],
        'date' => $transfer['created']
      );
    }

    return $data;

  }

  /*public function getPasswordUpdates() {}*/

  /*public function getEmailUpdates() {}*/

  public function getLauncherLogs() {
    $data = array();
    $connections = Database::get('launcherlogs')->fetchAll(
      "SELECT * FROM `loginlogs` WHERE {$this->__makeConditions(true, 'ip', 'date')}",
      array()
    );

    foreach ($connections as $key => $value) {
      $data[] = array(
        'ip' => $value['ip'],
        'date' => $value['date']
      );
    }

    return $data;
  }

  public function getIngameLogin() {
    $data = array();
    $connections = Database::get('playerlogger')->fetchAll(
      "SELECT `data` AS `ip`,FROM_UNIXTIME(`time`) AS `date` FROM `playerlogger` WHERE `playername` = ? AND type = 'join' {$this->__makeConditions(false, 'data', 'time', true)}",
      array($this->username)
    );

    foreach ($connections as $key => $value) {
      $data[] = array(
        'ip' => $value['ip'],
        'date' => $value['date']
      );
    }

    return $data;
  }

  public function getIngameMessages() {
    $data = array();
    $messages = Database::get('playerlogger')->fetchAll("SELECT `data` AS message,`time` FROM `playerlogger` WHERE `playername` = ? AND type = 'chat' {$this->__makeConditions(false, false, 'time', true)}", array($this->username));

    foreach ($messages as $message) {

      $data[] = array(
        'message' => $message['message'],
        'date' => date('Y-m-d H:i:s', $message['time'])
      );

    }

    return $data;
  }

  public function getIngameCommands() {
    $data = array();
    $commands = Database::get('playerlogger')->fetchAll("SELECT `data` AS command,`time` FROM `playerlogger` WHERE `playername` = ? AND type = 'command' {$this->__makeConditions(false, false, 'time', true)}", array($this->username));

    foreach ($commands as $message) {

      $data[] = array(
        'command' => $message['command'],
        'date' => date('Y-m-d H:i:s', $message['time'])
      );

    }

    return $data;
  }

  public function getIngameBlocksPlaced() {
    $data = array('count' => array(), 'list' => array());
    $lb_player = Database::get('logblock')->fetch('SELECT * FROM `lb-players` WHERE playername=?', array($this->username));
    $getDestroyedBlocks = Database::get('logblock')->fetchAll("SELECT x,y,z,type,`date` FROM `lb-FACTION` WHERE playerid=? AND replaced=0 {$this->__makeConditions(false, false, 'date')}", array($lb_player['playerid']));
    $data['count'] = count($getDestroyedBlocks);

    foreach ($getDestroyedBlocks as $block) {
      $data['list'][] = array(
        'type' => $block['type'],
        'date' => $block['date'],
        'coordinates' => array(
          'x' => $block['x'],
          'y' => $block['y'],
          'z' => $block['z']
        )
      );
    }

    return $data;
  }

  public function getIngameBlocksDestroyed() {
    $data = array('count' => array(), 'list' => array());
    $lb_player = Database::get('logblock')->fetch('SELECT * FROM `lb-players` WHERE playername=?', array($this->username));
    $getDestroyedBlocks = Database::get('logblock')->fetchAll("SELECT x,y,z,replaced,`date` FROM `lb-FACTION` WHERE playerid=? AND type=0 {$this->__makeConditions(false, false, 'date')}", array($lb_player['playerid']));
    $data['count'] = count($getDestroyedBlocks);

    foreach ($getDestroyedBlocks as $block) {
      $data['list'][] = array(
        'type' => $block['replaced'],
        'date' => $block['date'],
        'coordinates' => array(
          'x' => $block['x'],
          'y' => $block['y'],
          'z' => $block['z']
        )
      );
    }

    return $data;
  }

}
