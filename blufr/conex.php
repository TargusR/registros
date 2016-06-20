<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"

//Datos Locales
$hostname = "localhost";
$database = "envios";
$username = "test";
$password = "000000";


/*Datos Remotos
$hostname = "localhost";
$database = "ventron1_bd_apps";
$username = "ventron1_appu";
$password = "vSObL?WcwSRb";
*/


$conex = mysql_pconnect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($database, $conex);
?>
