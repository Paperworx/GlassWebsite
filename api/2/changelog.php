<?php
require_once dirname(__DIR__) . "/../private/class/AddonManager.php";
header('Content-Type: text/tml');

$addon = AddonManager::getFromId($_REQUEST['id']);
if($addon === false) {
  die("Error: add-on doesn't exist");
}

$updates = AddonManager::getUpdates($addon);
foreach($updates as $up) {
  echo "<version:" . $up->getVersion() . ">\n";
  echo $up->getChangeLog();
  echo "\n</version>\n";
}
?>
