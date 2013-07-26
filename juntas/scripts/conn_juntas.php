<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_conn_juntas = "mysql.revdevelopers.com";
$database_conn_juntas = "my_animaes_juntas";
$username_conn_juntas = "animaes_juntas";
$password_conn_juntas = "MdTaTxjhTzp5BMs8";
$conn_juntas = mysql_pconnect($hostname_conn_juntas, $username_conn_juntas, $password_conn_juntas) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_set_charset('utf8',$conn_juntas);
?>