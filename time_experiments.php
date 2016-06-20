<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Si lees esto, algo estás haciendo mal...</title>
	</head>
<body>

<?php
ini_set('date.timezone','America/Mexico_City'); 
echo time()."<br>";
echo date("g:i A")."<br>";
echo date("Y-m-d H:i:s");

echo "<br>".$_SERVER['PHP_SELF']."<br>";

require_once('blufr/blufr.php');

echo "<br><br><br><br>// Probando función de comparación de estados<br><br>";


$actual = 'Envío en Curso';
$nuevo = 'Mercancía Apartada';

if( analize_state($actual, $nuevo) )
	echo "El estado ".$actual." es menor o igual a ".$nuevo;
else
	echo "El estado ".$actual." NO es menor o igual a ".$nuevo;

if($value = analize_state($actual, false))
	echo "<br>".$actual." ".analize_state($actual, false);
else echo "<br>no existe"

echo "<br>";

?>

</body>
</html>