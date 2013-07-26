<?php

require_once("../classes/UserTools.class.php");
require_once("../classes/GenTools.class.php");

$ut = new UserTools;
$gt = new GenTools;

$u = "";
if ($gt->setAndNotEmpty($_POST['uname'])) $u = $_POST['uname'];
$p = "";
if ($gt->setAndNotEmpty($_POST['pwd'])) $p = $_POST['pwd'];
$r = "";
if ($gt->setAndNotEmpty($_POST['remember'])) $r = $_POST['remember'];

$result = $ut->login($u, $p, $r);
$return = $ut->getResult();
$user = $return['user'];
$return['postAsAlias'] = $user->uname=="cocano"||$user->uname=="msanchez"||$user->uname=="llegarreta"?true:false;
echo json_encode($return);

?>