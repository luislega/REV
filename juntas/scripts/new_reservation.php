<?php require_once('../../Connections/conn_juntas.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

mysql_select_db($database_conn_juntas, $conn_juntas);

$t0 = '';
if (isset($_POST['t0'])&&$_POST['t0']!=""){
	$t0 = $_POST['t0'];
}else {
	echo json_encode (array("result"=>"error","type"=>"badInfo","info"=>"badStartTime"));
	break;
}
$tf = '';
if (isset($_POST['tf'])&&$_POST['tf']!=""){
	$tf = $_POST['tf'];
}else {
	echo json_encode (array("result"=>"error","type"=>"badInfo","info"=>"badEndTime"));
	break;
}
if (strtotime($tf)-strtotime($t0) > 7200) {
	echo json_encode(array("result"=>"error","type"=>"badDuration","info"=>"reservationTooLong"));
	break;
}
if (strtotime($tf)<=strtotime($t0)) {
	echo json_encode(array("result"=>"error","type"=>"badDuration","info"=>"wrongStartEnd"));
	break;
}
$sql = 'SELECT tbl_users.name, tbl_users.lastname, tbl_users.email, tbl_users.utype, tbl_reservations.ID, tbl_reservations.reserved_by, tbl_reservations.description, tbl_reservations.startTime, tbl_reservations.endTime
		FROM tbl_reservations, tbl_users
		WHERE
			(startTime = "'.$t0.'"
			OR
				endTime = "'.$tf.'"
			OR
				(startTime < "'.$t0.'" AND "'.$t0.'" < endTime)
			OR
				(startTime < "'.$tf.'" AND "'.$tf.'" < endTime)
			OR
				("'.$t0.'" < startTime AND startTime < "'.$tf.'")
			OR
				("'.$t0.'" < endTime AND endTime < "'.$tf.'"))
			AND tbl_reservations.active = 1
			AND tbl_users.ID = tbl_reservations.reserved_by';

$rs_collision = mysql_query($sql, $conn_juntas) or die(mysql_error());
$row_rs_collision = mysql_fetch_assoc($rs_collision);
$totalRows_rs_collision = mysql_num_rows($rs_collision);
//}

if ($totalRows_rs_collision) {
	$collisions = array($row_rs_collision);
	while ($row_rs_collision = mysql_fetch_assoc($rs_collision)){
		array_push ($collisions, array_map("utf8_encode", $row_rs_collision));
	}
	echo json_encode (array("result"=>"error","type"=>"collision", "info"=>$collisions));
}else {
	// DO INSERT
	$u_id = "";
	if (isset($_POST['u_id'])){
		$u_id = $_POST['u_id'];
	}else {
		echo json_encode (array("result"=>"error","type"=>"badInfo","info"=>"badUser"));
		break;
	}
	$description = "";
	if (isset($_POST['description'])){
		$description = $_POST['description'];
	}else {
		echo json_encode (array("result"=>"error","type"=>"badInfo","info"=>"badDescription"));
		break;
	}
	$insertSQL = "INSERT INTO tbl_reservations (startTime, endTime, description, reserved_by) VALUES ('$t0', '$tf', '$description', '$u_id')";
	if ($result = mysql_query($insertSQL, $conn_juntas) or die(mysql_error())) {
		$insertID = mysql_insert_id();
		if ($_POST['invitees']&&$_POST['invitees']!=''){
			$inviteesArr = split(",",$_POST['invitees']);
			$inviteeSQLValues = "(NULL,'$insertID','".implode("'),(NULL,'$insertID','", $inviteesArr)."')";
			$insertInviteesSQL = "INSERT INTO tbl_invitees VALUES ".$inviteeSQLValues;
			$resultInvitees = mysql_query ($insertInviteesSQL, $conn_juntas) or die(mysql_error());
		}
		echo json_encode (array("result"=>"success", "info"=>array_map("utf8_encode", array("startTime"=>$t0,"endTime"=>$t1,"description"=>$description,"withInvitees"=>$_POST['invitees']))));
	}
}

mysql_free_result($rs_collision);
?>
