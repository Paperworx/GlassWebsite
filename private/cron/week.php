<?php
header('Content-Type: text/json');
require_once dirname(__DIR__) . '/class/CronStatManager.php';
require_once dirname(__DIR__) . '/class/StatManager.php';

StatManager::endIteration();

?>
