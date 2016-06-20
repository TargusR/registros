<?php 

/*-----------------------
	.blu framework
	Nombre componente: State Toggler
	Version: dev
-----------------------*/

include('conex.php');

if(isset($_GET['tabla'])) $tabla = $_GET['tabla'];
else $tabla = false;

if(isset($_GET['propiedad'])) $propiedad = $_GET['propiedad'];
else $propiedad = false;

if(isset($_GET['key'])) $key = $_GET['key'];
else $key = false;

if(isset($_GET['id'])) $id = $_GET['id'];
else $id = false;

?>

<!doctype html>
<HTML>
<head>
	<title>State Toggler - Blufr</title>
	<meta charset="utf-8" />
	<style>
		body{
			margin:0; padding:0;
			font-family:Verdana, Geneva, sans-serif;
			color:#121212;
			font-size:.8em;
			}
		a, a:visited {
			border:0;
			outline:0;
			text-decoration:none;
			transition:color .6s;
			} a:hover { }
			
		a.toggler {
			width:80px;
			height:40px;
			display:block;
			overflow:hidden;
			background:url('../sources/toggler_bg.jpg') no-repeat -11px center;
			transition:background-position .2s;
		}
	</style>
	<script>
	function change(value,toggler)
	{
		if(value)
			toggler.style.backgroundPosition="-11px center";
		else
			toggler.style.backgroundPosition="-69px center";
		
		cadena = ("?tabla=<?php echo $tabla;?>&propiedad=<?php echo $propiedad;?>&key=<?php echo $key;?>&id=<?php echo $id;?>&value=" + value);
		cadena = "window.location='"+ cadena +"'";
		
		setTimeout(cadena, 210);
	}
	</script>
</head>
<body>

<?php
//Si existe un valor a cambiar en la cadena GET
if(isset($_GET['value'])){
	$value = $_GET['value'];
	$sql[] = "UPDATE ". $tabla ." SET ". $tabla .".". $propiedad ." = '". $value ."' WHERE ". $tabla .".". $key ." = '". $id ."';";
	
	//ejecuta la cola de operaciónes sql, si existe
	if(isset($sql)){
		
		while($sql) {
			mysql_query($sql[0], $conex) or die(mysql_error());
			array_shift($sql); //quita el valor más reciente de la cola
		}
		
	} unset($sql);
}


if($tabla && $propiedad && $key && $id) {
	
	$sql="SELECT ". $propiedad ." FROM ". $tabla ." WHERE ". $tabla .".". $key ." = '". $id ."';";
	$consult = mysql_query($sql, $conex) or die(mysql_error());
	
	if(mysql_num_rows($consult)!=0)
		while($row = mysql_fetch_assoc($consult)){
			
			if($row[$propiedad])
				//echo "<a href='?tabla=". $tabla ."&propiedad=". $propiedad ."&key=". $key ."&id=". $id ."&value=0' class='toggler' style='background-position:-11px center'><img src='../sources/toggler_mask.png'></a>";
				echo "<a href='#' onClick='change(0,this); return false;' class='toggler' style='background-position:-11px center'><img src='../sources/toggler_mask.png'></a>";
			else
				//echo "<a href='?tabla=". $tabla ."&propiedad=". $propiedad ."&key=". $key ."&id=". $id ."&value=1' class='toggler' style='background-position:-69px center'><img src='../sources/toggler_mask.png'></a>";
				echo "<a href='#' onClick='change(1,this); return false;' class='toggler' style='background-position:-69px center'><img src='../sources/toggler_mask.png'></a>";
		}
	else echo "<p>!!</p>";
	
} else echo "<p>!!</p>";
	

?>

</body>