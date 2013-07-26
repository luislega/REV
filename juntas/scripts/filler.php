<?php
require_once ("../classes/DB.class.php");
require_once ("../classes/User.class.php");
require_once ("../classes/UserTools.class.php");
require_once ("../classes/Encrypt.class.php");

$hash = new Encrypt;
$db1 = new DB("intranet", "intranet_admin", "AauaFq8F9AXGpw8R", "localhost");

//[ID] => 1 [nombre] => Carlos [apellido] => Abugaber [foto] => cabugaber [uname] => cabugaber [pwd] => Mold8292 [utype] => 2 [active] => 1 [day] => 22 [month] => 2

$users = $db1->select("tbl_people");

$db2 = new DB;

foreach($users as $n=>$v) {
	$data = array(name=>$v['nombre'],
				  lastname=>$v['apellido'],
				  uname=>$v['uname'],
				  email=>$v['uname']."@animaestudios.com",
				  active=>$v['active'],
				  company=>1,
				  utype=>$v['utype']=='guest'?2:1);
				  
	$u = new User($data);
	//var_dump ($data);
	var_dump ($u);
	echo "<br><br>";
	$u->save(hash('sha256', $v['pwd']));
}

?>