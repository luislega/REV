<?php

require_once('../classes/DB.class.php');

$db = new DB;
var_dump ($db->gimme());
//echo $db->insert('tbl_contact', array(comment=>"hola", email=>"luislega@gmail.com"));

?>