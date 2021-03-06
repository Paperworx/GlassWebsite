<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class ServerTracker {
  public static function updateRecord($ip, $port, $host, $clients) {
    $db = new DatabaseManager();

    ServerTracker::verifyTable($db);

    $res = $db->query($sq = "SELECT COUNT(*) FROM `server_tracking` WHERE `ip`='" . $db->sanitize($ip) . "' AND `port`='" . $db->sanitize($port) . "'");
    $ret = $res->fetch_row();
    if(!isset($ret[0]) || $ret[0] == 0) {
      $res = $db->query($sq = "INSERT INTO `server_tracking` (`ip`, `port`, `host`, `clients`) VALUES (
      '" . $db->sanitize($ip) . "',
      '" . $db->sanitize($port) . "',
      '" . $db->sanitize($host) . "',
      '" . $db->sanitize(json_encode($clients)) . "')");
    } else {
      $db->update("server_tracking", ["ip"=>$ip, "port"=>$port], ["host"=>$host, "clients"=>json_encode($clients)], "lastUpdate");
    }
  }

  public static function getActiveServers() {
    $db = new DatabaseManager();
    $res = $db->query("SELECT * FROM `server_tracking` WHERE `lastUpdate` > now() - INTERVAL 10 MINUTE");
    $ret = array();
    while($obj = $res->fetch_object()) {
      $ret[] = $obj;
    }
    return $ret;
  }

  public static function verifyTable($database) {
		if($database->debug()) {
			if(!$database->query("CREATE TABLE IF NOT EXISTS `server_tracking` (
      `ip` text NOT NULL,
      `port` int(6) NOT NULL,
      `host` text NOT NULL,
      `clients` text NOT NULL,
      `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)")) {
				throw new Exception("Failed to create table server_tracking: " . $database->error());
			}
    }
  }
}

?>
