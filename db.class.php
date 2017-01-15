<?php
class Database {

  static $config = array();
  static $connection = array();
  static $queryInstances = array();

  static public function setConfig($key, $config) {
    self::$config[$key] = $config;
  }

  static public function getDatabase($key) {
    if(!isset(self::$connection[$key])) {

      try {
        self::$connection[$key] = new PDO('mysql:host='.self::$config[$key]['host'].';dbname='.self::$config[$key]['dbname'].';charset=utf8', self::$config[$key]['user'], self::$config[$key]['password']);
      } catch(Exception $e) {
        exit($key.' : '.$e->getMessage());
      }

    }

    return self::$connection[$key];
  }

  static public function get($key) {
    if(!isset(self::$queryInstances[$key])) {
      self::$queryInstances[$key] = new Query(self::getDatabase($key));
    }
    return self::$queryInstances[$key];
  }


}

class Query {

  private $queries = array();

  public function __construct(PDO $connection) {
    $this->db = $connection;
  }

  private function req($query, $args) {
    $query = $this->db->prepare($query);
    $query->execute($args);
    return $query;
  }

  public function fetch($query, $args) {
    $query_id = $query.'+'.serialize($args);
    if(!isset($this->queries[$query_id])) {
      $query = $this->req($query, $args);
      $this->queries[$query_id] = $query->fetch();
    }
    return $this->queries[$query_id];
  }

  public function fetchAll($query, $args) {
    $query = $this->req($query, $args);
    return $query->fetchAll();
  }

}
