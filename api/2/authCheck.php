<?php
require_once dirname(__FILE__) . "/private/ClientConnection.php";

header('Content-Type: text/json');
if(isset($_REQUEST['ident']) && $_REQUEST['ident'] != "") {
  // glass checks in every 5 (?) minutes
  // on the old site, this was used to keep the "currently active" list
  // this will get added back later

	$con = ClientConnection::loadFromIdentifier($_REQUEST['ident']);
  $ret = new stdClass();
  if(!is_object($con)) {
    $ret->status = "fail";
  } else {
    $ret->ident = $con->getIdentifier();
    $ret->blid = $con->getBLID();
    $ret->status = "success";
  }

  echo json_encode($ret, JSON_PRETTY_PRINT);
}
?>