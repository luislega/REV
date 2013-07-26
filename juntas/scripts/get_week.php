<?php require_once('conn_juntas.php'); ?>
<?php
//header('Content-Type: text/html; charset=utf-8');
//mb_internal_encoding('utf-8');

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

$now = date('j\,n\,N\,t\,Y\,G\,i');

$namesArray = array ("dia",
					 "mes",
					 "diaDeSemana",
					 "mesN",
					 "anio",
					 "hora",
					 "minuto");

$nowArray = explode(',', $now);

$timeInfoObject = array();

$i = 0;

foreach ($namesArray as $value) {
	$timeInfoObject[$value] = intval ($nowArray[$i]);
	$i++;
}

$from = date ("Y-m-d 00:00:00", strtotime("last Sunday"));
$to = date("Y-m-d 00:00:00", strtotime("last Sunday +1 week"));
//echo $from . " " . $to . "<br>";
if (isset($_POST['from'])) {
	$from = date ("Y-m-d 00:00:00", strtotime(urldecode($_POST['from'])));
}
if (isset($_POST['to'])) {
	$to = date ("Y-m-d 00:00:00", strtotime(urldecode($_POST['to'])));
}

$colname_rs_week = "-1";
if (isset($from)) {
  $colname_rs_week = $from;
}

mysql_select_db($database_conn_juntas, $conn_juntas);
$query_rs_week = sprintf("SELECT tbl_reservations.ID, startTime, endTime, tbl_reservations.description, reserved_by, name, lastname, email, utype, tbl_companies.company, color FROM tbl_reservations, tbl_users, tbl_companies WHERE tbl_users.ID=reserved_by AND tbl_companies.ID=tbl_users.company AND tbl_reservations.startTime >= %s AND tbl_reservations.startTime <= %s AND tbl_reservations.active=1 ORDER BY startTime ASC", GetSQLValueString($colname_rs_week, "date"), GetSQLValueString($to, "date"));
//echo $query_rs_week . "<br>";
$rs_week = mysql_query($query_rs_week, $conn_juntas) or die(mysql_error());
//$row_rs_week = mysql_fetch_assoc($rs_week);
$totalRows_rs_week = mysql_num_rows($rs_week);

$timeInfoObject ["fechaSQL_desde"] =  $from;
$timeInfoObject ["fechaSQL_hasta"] =  $to;

$reservations = array();

while ($row_rs_week = mysql_fetch_assoc($rs_week)){
	array_push ($reservations, array_map("utf8_encode", $row_rs_week));
}

$return = array("timeInfo"=>$timeInfoObject, "reservations"=>$reservations);

echo json_encode ($return);

?>
























