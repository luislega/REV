<?php require_once('conn_juntas.php'); ?>
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
$query_rs_users = "SELECT ID, name, lastname FROM tbl_users WHERE active = 1 ORDER BY name ASC";
$rs_users = mysql_query($query_rs_users, $conn_juntas) or die(mysql_error());
$row_rs_users = mysql_fetch_assoc($rs_users);
$totalRows_rs_users = mysql_num_rows($rs_users);

$users = array();

do {
	$new = array();
	foreach ($row_rs_users as $n=>$v) {
		$new[$n] = utf8_encode($v);
	}
	array_push ($users, $new);
}while ($row_rs_users = mysql_fetch_assoc($rs_users));

echo json_encode($users);

mysql_free_result($rs_users);
?>
