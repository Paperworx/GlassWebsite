<?php
header('Content-Type: text/json');
require_once dirname(__DIR__) . "/../../../private/class/AddonManager.php";
require_once dirname(__DIR__) . "/../../../private/class/RTBAddonManager.php";

$recs = RTBAddonManager::getReclaims();
$arr = [];
foreach($recs as $rec) {
  $addon = AddonManager::getFromId($rec->glass_id);
  $obj = new stdClass();
  $obj->id = $rec->id;
  $obj->glass_id = $addon->getId();
  $obj->glass_name = $addon->getName();
  $arr[] = $obj;
}

$ret = new stdClass();
$ret->addons = $arr;
$ret->status = "success";

echo json_encode($ret, JSON_PRETTY_PRINT);
?>
