<?php

require_once("../classes/DB.class.php");
require_once("../classes/GenTools.class.php");

$db = new DB;
$gt = new GenTools;

$row = "";
if ($gt->setAndNotEmpty($_POST['ID'])) {
	$row = $_POST['ID'];

	$result = $db->update("tbl_reservations", array(active=>0), array(ID=>$row));
	
	if ($result) {
		echo json_encode(array(result=>"success", type=>"deactivated", description=>"Row #$row deactivated"));
	}else {
		echo json_encode(array(result=>"error", type=>"badRow", description=>"Row #$row does not exist"));
	}
}else {
	echo json_encode(array(result=>"error", type=>"noRow", description=>"No row posted"));
}

?>