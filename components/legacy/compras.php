<?php 

/*-----------------------
	.blu framework
	Nombre componente: Compras
	Version: 1.0x
-----------------------*/
	
	session_start();
	ini_set('date.timezone','America/Mexico_City'); 
	require_once('../blufr/conex.php');
	require_once('../blufr/blufr.php');
	
	// Parametros de la tabla
	$consultas = 30; //numero de registros recientes a mostrar por página
	$table_name = 'separados';
	
	
	// Examinar si existe un acceso concedido
	if(isset($_SESSION['blu_usuario'])) $usuario = $_SESSION['blu_usuario'];
	else $usuario = false;
	
	if(isset($_SESSION['blu_acceso'])) $acceso = $_SESSION['blu_acceso'];
	else $acceso = false;
	
	// Obtener tablas autorizadas
	if(isset($_SESSION['blu_allowed_views']))
		$bitacoras = $_SESSION['blu_allowed_views'];
	
	// Denegar acceso por falta de permisos
	if( !in_array("compras", $bitacoras) ) $acceso = false;
	
	
	// Parametros de la URL enviada desde el/los formularios
	
	$indice = catchValue('indice','GET',0);
	if($indice < 0) $indice = 0; //pone el indice a 0 en caso de que sea forzado
	
	$alert = false; // Alerta: indica que ha ocurrido un error, y se detuvieron las operaciones
	// Obtener identificador de operación
	$ident = catchValue('ident','ANY', -1);
	
	// Guardar Filtro
	$filtro = catchValue('filtro','GET');
	if($filtro) {
		if( $filtro != 'unset' )
			$_SESSION[$table_name]['filtro'] = $filtro;
		else {
			if( isset($_SESSION[$table_name]['filtro']) )
				unset($_SESSION[$table_name]['filtro']);
			$filtro = false;
		}
	} else if( isset($_SESSION[$table_name]['filtro']) ){
		$filtro = $_SESSION[$table_name]['filtro'];
	}
	
	// Guardar Tabla
	$tabla = catchValue('tabla','GET');
	if($tabla)
		$_SESSION['compras']['tabla'] = $tabla;	
	else {
		if( isset($_SESSION['compras']['tabla']) )
			$tabla = $_SESSION['compras']['tabla'];
		else
			$tabla = 'PENDIENTES';
	}
	
	
	if(isset($_GET['orderBy'])) {
		//si existe ya una variable en SESSION, compara para cambiar el orden
		if(isset($_SESSION[$table_name]['orderBy']))
			if($_SESSION[$table_name]['orderBy'] == $_GET['orderBy'])
				if( isset($_SESSION[$table_name]['orderBy_order']) )
					$_SESSION[$table_name]['orderBy_order'] = toggle_order($_SESSION[$table_name]['orderBy_order']);
				else 
					$_SESSION[$table_name]['orderBy_order'] = "ASC"; // Guardar valor inverso al por defecto
		$_SESSION[$table_name]['orderBy'] = $_GET['orderBy'];
	}
	
	
	//automata de operaciones
	Switch($ident){
		
		case 'REGISTER': //operación de inserción
		
		break;
		
		case -1: //operación nula
		break;
		
		default: //Adición o actualización
		break;
		
	} 
	
	// Cola de operaciones SQL
	if(isset($sql)) {
		execute_sql($sql, $conex);
		unset($sql);
	}
	
	if($acceso){
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Si lees esto, algo estás haciendo mal...</title>
		<link rel="stylesheet" type="text/css" href="../blufr/bluUI.css">
		<style>
			body {
				margin:0; padding:0;
				background:#FFF;
				font-size:85%;
				}			
			
			table .extra{
				display:none;
				}
			
			.imageContainer {
				width:80px;
				height:80px;
				overflow:hidden;
				background:url('../sources/noimage.jpg') no-repeat center center;
				}
				.imageContainer img {
					max-width:80px;
					max-height:80px;
					position:relative;
					}
					
			.alert:first-child {
				width:80%;
				position:absolute;
				left:7%;
				border:3px solid #323334;
				top:10%;
				z-index:3;
			}
			
		</style>
		<script type='text/javascript' src='../blufr/common.js'></script>
	</head>
	<body class='dobleCetrado' 
		<?php
		if(!$alert)
			echo "onLoad='setTimeout(limpiar(parent.document.getElementById(&quot;separados-form&quot;)),5);'";
		?>
	>
	
	<?php
		
		 // Mostrar salida de alerta
		if( isset($msg) )
			echo "<div class='alert'>".$msg."<a href='#' class='close' onclick='remove_element(this.parentNode); return false;'><img src='../sources/close_crox_black_small.png'></a></div>";
		
	
		// Selector de tablas
		echo "<div id='sub-selector'>";
		if($tabla == 'PENDIENTES') echo "<span>Compras Pendientes</span>";
		else echo "<a href='?tabla=PENDIENTES'>Compras Pendientes</a>";
		if($tabla == 'PERSONALES') echo "<span>Clientes Personales</span>";
		else echo "<a href='?tabla=PERSONALES'>Clientes Personales</a>";
		
		if($filtro) echo "<a href='?filtro=unset' class='filtro'>Borrar Filtro<img src='../sources/borrar_filtro_small.png'></a>";
		echo "<a href='?' class='reload''>Recargar<img src='../sources/reload_small_white.png'></a>";
		echo "</div>";
	
		
		switch($tabla) {
			
			case 'PENDIENTES': // Tabla Recientes
				
				// Ensamblar Filtro
				if($filtro)
					$filtro = " && ". $table_name .".pedido like '%". $filtro ."%' || ". $table_name .".encargado like '%". $filtro ."%' || ". $table_name .".comentario like '%". $filtro ."%'";
				
				//obtener el total de registros
				$sql="SELECT count(*) as total FROM ". $table_name ." WHERE ". $table_name .".estado = 0 && ". $table_name .".pedido not in (SELECT ". $table_name .".pedido from ". $table_name." WHERE ". $table_name .".estado = 1) && ". $table_name .".fecha in (SELECT MAX(". $table_name .".fecha) FROM ". $table_name ." GROUP BY ". $table_name .".pedido)". $filtro .";";
				$consult = mysql_query($sql, $conex) or die(mysql_error());
				$consult = mysql_fetch_assoc($consult);
				$total = $consult['total'];
				unset($consult);
				
				//obtener cantidad de paginas a mostrar
				$total_paginas = ceil($total / $consultas);
				
				//Obtener variables de ordenación
				if(isset($_SESSION[$table_name]['orderBy'])) $orderBy = $_SESSION[$table_name]['orderBy'];
				else $orderBy = "fecha";
				
				if(isset($_SESSION[$table_name]['orderBy_order'])) $orderBy_order = $_SESSION[$table_name]['orderBy_order'];
				else $orderBy_order = "DESC";
				
				//obtener datos entre los limites solicitados
				$sql="SELECT * FROM ". $table_name ." WHERE ". $table_name .".estado = 0 && ". $table_name .".pedido not in (SELECT ". $table_name .".pedido from ". $table_name." WHERE ". $table_name .".estado = 1) && ". $table_name .".fecha in (SELECT MAX(". $table_name .".fecha) FROM ". $table_name ." GROUP BY ". $table_name .".pedido)". $filtro ." ORDER BY ". $table_name .".". $orderBy ." ". $orderBy_order ." LIMIT ". $consultas * $indice .", $consultas;";
				$consult = mysql_query($sql, $conex) or die(mysql_error());
				
				indice($indice, $total_paginas);
				
				//Impresión de ultimos registros actualizados
				if(mysql_num_rows($consult)!=0){
					
					//headers de tabla
					echo "<table id='Tabla-Resultado'><tr>";
						if ($orderBy == "fecha") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=fecha'>Fecha</a></th>";
						
						if ($orderBy == "pedido") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=pedido'>Pedido</a></th>";
						
						echo "<th>Cliente</th>";
						
						if ($orderBy == "encargado") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=encargado'>Encargado</a></th>";
						
						if ($orderBy == "asunto") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=asunto'>Asunto</a></th>";
						
						echo "<th>Comentario</th>";
						
						echo "<th>Vendedor</th>";
						
					echo "</tr>";
					
					while($row = mysql_fetch_assoc($consult)){
						echo "<tr><td>".$row['fecha']."</td><td>".$row['pedido']."</td>";
						
						//aqui preguntamos por el correo
						$sql = "SELECT correo FROM paquetes WHERE paquetes.pedido = '".$row['pedido']."';";
						$paquete = mysql_query($sql, $conex) or die(mysql_error());
						$paquete = mysql_fetch_assoc($paquete);
						echo "<td> ".$paquete['correo']." </td>";

						
						echo "<td>".$row['encargado']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
						
						//aqui preguntamos por el correo
						$sql = "SELECT encargado FROM cotejados WHERE cotejados.pedido = '".$row['pedido']."';";
						$cotejado = mysql_query($sql, $conex) or die(mysql_error());
						$cotejado = mysql_fetch_assoc($cotejado);
						echo "<td> ".$cotejado['encargado']." </td>";
						
						echo "</tr>";
						
					} echo '</table>';
				
				} else {
					if($filtro)
						echo "<div class='alert'><p>No hay información para mostrar con ese Filtro :/ <a href='?filtro=unset'>Borrar Filtro</a></p></div>";
					else
						echo "<div class='alert'><p>No hay Registros por revisar <a href='?'>Recargar</a></p></div>";
				}
				
				indice($indice, $total_paginas);
				
				break;
				
			case 'PERSONALES': // Tabla Recientes
				
				// Ensamblar Filtro
				if($filtro)
					$filtro = " && ". $table_name .".pedido like '%". $filtro ."%' || ". $table_name .".encargado like '%". $filtro ."%' || ". $table_name .".comentario like '%". $filtro ."%'";
				
				//obtener el total de registros
				$sql="SELECT count(*) as total FROM ". $table_name ." WHERE ". $table_name .".estado = 0 && ". $table_name .".pedido not in (SELECT ". $table_name .".pedido from ". $table_name." WHERE ". $table_name .".estado = 1) && ". $table_name .".pedido in (SELECT cotejados.pedido from cotejados WHERE cotejados.encargado = '". $usuario ."') && ". $table_name .".fecha in (SELECT MAX(". $table_name .".fecha) FROM ". $table_name ." GROUP BY ". $table_name .".pedido)". $filtro .";";
				$consult = mysql_query($sql, $conex) or die(mysql_error());
				$consult = mysql_fetch_assoc($consult);
				$total = $consult['total'];
				unset($consult);
				
				//obtener cantidad de paginas a mostrar
				$total_paginas = ceil($total / $consultas);
				
				//Obtener variables de ordenación
				if(isset($_SESSION[$table_name]['orderBy'])) $orderBy = $_SESSION[$table_name]['orderBy'];
				else $orderBy = "fecha";
				
				if(isset($_SESSION[$table_name]['orderBy_order'])) $orderBy_order = $_SESSION[$table_name]['orderBy_order'];
				else $orderBy_order = "DESC";
				
				//obtener datos entre los limites solicitados
				$sql="SELECT * FROM ". $table_name ." WHERE ". $table_name .".estado = 0 && ". $table_name .".pedido not in (SELECT ". $table_name .".pedido from ". $table_name." WHERE ". $table_name .".estado = 1) && ". $table_name .".pedido in (SELECT cotejados.pedido from cotejados WHERE cotejados.encargado = '". $usuario ."') && ". $table_name .".fecha in (SELECT MAX(". $table_name .".fecha) FROM ". $table_name ." GROUP BY ". $table_name .".pedido)". $filtro ." ORDER BY ". $table_name .".". $orderBy ." ". $orderBy_order ." LIMIT ". $consultas * $indice .", $consultas;";
				$consult = mysql_query($sql, $conex) or die(mysql_error());
				
				indice($indice, $total_paginas);
				
				//Impresión de ultimos registros actualizados
				if(mysql_num_rows($consult)!=0){
					
					//headers de tabla
					echo "<table id='Tabla-Resultado'><tr>";
						if ($orderBy == "fecha") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=fecha'>Fecha</a></th>";
						
						if ($orderBy == "pedido") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=pedido'>Pedido</a></th>";
						
						echo "<th>Cliente</th>";
						
						if ($orderBy == "encargado") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=encargado'>Encargado</a></th>";
						
						if ($orderBy == "asunto") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=asunto'>Asunto</a></th>";
						
						echo "<th>Comentario</th>";
						
						echo "<th>Vendedor</th>";
						
					echo "</tr>";
					
					while($row = mysql_fetch_assoc($consult)){
						echo "<tr><td>".$row['fecha']."</td><td>".$row['pedido']."</td>";
						
						//aqui preguntamos por el correo
						$sql = "SELECT correo FROM paquetes WHERE paquetes.pedido = '".$row['pedido']."';";
						$paquete = mysql_query($sql, $conex) or die(mysql_error());
						$paquete = mysql_fetch_assoc($paquete);
						echo "<td> ".$paquete['correo']." </td>";

						
						echo "<td>".$row['encargado']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
						
						//aqui preguntamos por el correo
						$sql = "SELECT encargado FROM cotejados WHERE cotejados.pedido = '".$row['pedido']."';";
						$cotejado = mysql_query($sql, $conex) or die(mysql_error());
						$cotejado = mysql_fetch_assoc($cotejado);
						echo "<td> ".$cotejado['encargado']." </td>";
						
						echo "</tr>";
						
					} echo '</table>';
				
				} else {
					if($filtro)
						echo "<div class='alert'><p>No hay información para mostrar con ese Filtro :/ <a href='?filtro=unset'>Borrar Filtro</a></p></div>";
					else
						echo "<div class='alert'><p>No hay Registros por revisar <a href='?'>Recargar</a></p></div>";
				}
				
				indice($indice, $total_paginas);
				
				break;
				
		}
		
		?>
	</body>
</html>
<?php } else header("Location: ../"); //redireccionar a esc ?>