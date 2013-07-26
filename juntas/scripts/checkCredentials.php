<?php

require_once ("../classes/UserTools.class.php");

$ut = new UserTools;

$result = $ut->checkCredentials();
$return = $ut->getResult();
$user = $return['user'];
$return['postAsAlias'] = $user->uname=="cocano"||$user->uname=="msanchez"||$user->uname=="llegarreta"?true:false;

echo json_encode($return);

?>