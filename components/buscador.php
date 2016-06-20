<?php 

/*-----------------------
	.blu framework
	Nombre componente: Buscador
	Version: dev
-----------------------*/
	
	session_start();
	ini_set('date.timezone','America/Mexico_City');
	set_time_limit (60);
	require_once('../blufr/conex.php');
	require_once('../blufr/blufr.php');
	
	
	// Examinar si existe un acceso concedido
	if(isset($_SESSION['blu_usuario'])) $usuario = $_SESSION['blu_usuario'];
	else $usuario = false;
	
	if(isset($_SESSION['blu_acceso'])) $acceso = $_SESSION['blu_acceso'];
	else $acceso = false;
	
	// Obtener parametros de la URL enviada desde el/los formularios
	
	// Exclusivos
	$pedido = catchValue('pedido','GET');
	
	// Comprobar pedido
	if( !is_numeric($pedido)) {
		$pedido = false;
		if(isset($_GET['pedido']))
			$msg = "<p>Error: Proporcione un número de pedido válido</p>";
	}

	$correo = catchValue('correo','GET');
	
	// Obtener identificador de operación via post o get
	if(isset($_POST['ident'])) $ident = $_POST['ident'];
	else if(isset($_GET['ident'])) $ident = $_GET['ident'];
	else $ident = -1;
	
	
	// Automata de operaciones
	Switch($ident){
		
		case 0: //operación de inserción
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
			table, form {
				margin:10px 0 0 10px;
				}
			.section-label {
				color:#727272;
				text-indent:.5em;
				letter-spacing:.1em;
				}
				
			#progress-bar {
				display:block;
				width:700px;
				margin:0;
				padding:0;
				background-color:#d1d1d1;
				}
				#progress-bar .current {
					display:block;
					line-height:50px;
					background-color:#68f982;
					}
				#progress-bar .value {
					display:block;
					width:100px;
					margin:0;
					padding:0;
					text-align:center;
					font-weight:bold;
					font-size:x-large;
					}
				
			
		</style>
		<script type='text/javascript' src='../blufr/common.js'></script>
		
	</head>
	<body onload='setTimeout(limpiar(document.getElementById("buscador")),10);'>
	
		<?php if(isset($msg)) echo "<div class='alert'>".$msg."</div>"; //mostrar salida
		
		//Formulario de Acceso
		if(!$pedido){
			
			?>
				<div id='sub-selector'>
					<span>Buscar pedido</span>
				</div>
				<form method=GET id="buscador">
					<span>No. Pedido: </span><input type=TEXT name='pedido'>
					<?php if (!$acceso){ ?>
						<span>Correo del Cliente: </span><input type=TEXT name='correo'>
					<?php } ?>
					<input type=SUBMIT  value='Buscar' class='cmd'>
				</form>
			<?php
			
		} else {
			
			// Menú para resetar la busqueda
			echo "<div id='sub-selector'>";
			echo "<span>Información Pedido</span>";
			echo "<a href='?'>Buscar otro pedido</a>";
			echo "<a href='#' class='reload' onClick='location.reload(); return false;'>Recargar <img src='../sources/reload_small_white.png'></a>";
			echo "</div>";
			
			$sql="SELECT correo FROM paquetes WHERE paquetes.pedido = '". $pedido ."';";
			$consult = mysql_query($sql, $conex) or die(mysql_error());
			
			//Impresión de tabla
			if(mysql_num_rows($consult) != 0){
				
				$consult = mysql_fetch_assoc($consult);
				
				//corroborar que el dato ingresado en correo coincida, o que sea tenga una sesión abierta
				if($acceso || $consult['correo'] == $correo) {
					
					// registrar pedido en cadena de consulta
					$extensions[] = $pedido;
					
					// obtener extensiones
					$sql = "select pedido as extension from paquetes as a where pedido LIKE '". $pedido ."\_%' || pedido LIKE '". $pedido ."_';";
					$ext = mysql_query($sql, $conex) or die(mysql_error());
					if(mysql_num_rows($ext) != 0)
						while($row = mysql_fetch_assoc($ext))
							$extensions[] = $row['extension'];
					
					for($i=0; $i < count($extensions); $i++) {
						
						// Marcador para guias adicionales
						if($i == 1)
							echo "<h3>Guías adicionales:</h3>"; //"<h2>Extension ". ($i+1) ."° guía</h2>";
						
						// Obtención de fila
						$sql="SELECT * FROM paquetes WHERE paquetes.pedido = '". $extensions[$i] ."';";
						$consult = mysql_query($sql, $conex) or die(mysql_error());
						$consult = mysql_fetch_assoc($consult);
						
						// obtener ponderación de estado
						if( $value = analize_state($consult['estado'], false) )
							$value = floor(100 * ( explode('/',$value)[0] / $value = explode('/',$value)[1] ) );
						
						// Calculos exclusivos para vista ejecutiva
						if($acceso) {
							
							// Calcular tiempo Transcurrido
							if($value < 100)
								$transcurrido = get_time_interval(strtotime($consult['finicio']), time());
							else
								$transcurrido = get_time_interval(strtotime($consult['finicio']), strtotime($consult['fultima']));
						}
						
						echo "<table id='resumen'><tr><th>Pedido</th><td>".$consult['pedido']."</td><th>Estado</th><td>".$consult['estado']."</td></tr>";
						echo "<tr><th>Usuario</th><td>".$consult['correo']."</td><th>Ultima Actualización</th><td>".$consult['fultima']."</td></tr>";	
						if($acceso)
							echo "<tr><th>Guia</th><td>".$consult['guia']."</td><th>Tiempo transcurrido</th><td>". $transcurrido ."</td></tr>";
						else
							echo "<tr><th colspan=2>Guia</th><td colspan=2>".$consult['guia']."</td></tr>";
						echo "</table>";
						
						// Imprimir barra de progreso
						echo "<table><tr><th width='100'>Progreso del paquete</th>";
						echo "<td><div id='progress-bar'><span class='current' style='width:". $value ."%;'><span class='value'>". $value ."%</span></span></div></td></tr></table>";
						
						
						// Contar tiempo mostrar progreso
						$tablas = array(
							"cotejados",
							"impresos",
							"separados",
							"revision",
							"cerrado",
							"recoleccion"
							);
						while($tabla = array_shift($tablas)) {
							$sql="SELECT fecha FROM ". $tabla ." WHERE ". $tabla .".pedido = '". $pedido ."' ORDER BY ". $tabla .".fecha DESC LIMIT 1;";
							$consult = mysql_query($sql, $conex) or die(mysql_error());
							
							if(mysql_num_rows($consult) != 0){
								$row = mysql_fetch_assoc($consult);
								if(!isset($primera)) $primera = $row['fecha'];
								$ultima = $row['fecha'];
							} else break;
						}
						//echo "Las fechas son: ". $primera ." - ". $ultima;
					
					}
						
						
					// Encabezado tabla
					echo "<table id='historial'><tr>";
					if($acceso) echo "<th>Fecha</th>";
					echo "<th>Pedido</th><th>Asunto</th><th>Comentario</th><th>Estado</th>";
					if($acceso) echo "<th>Encargado</th>";
					echo "</tr>";
					
					
					//Extraer entradas de la bitacora Cotejados
					$sql="SELECT * FROM cotejados WHERE pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%' ORDER BY fecha;";
					if(!$acceso) $sql="SELECT * FROM cotejados WHERE estado = 1 && (pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%') ORDER BY fecha;";
					$consult = mysql_query($sql, $conex) or die(mysql_error());
					
					if(mysql_num_rows($consult)!=0){
						
						// linea de test echo "<table id='Tabla-Resultado'><tr><th>Fecha</th><th>Pedido</th><th>Asunto</th><th>Comentario</th><th>Estado</th><th>ID</th><th>Padre</th>";
						//echo "<table id='Tabla-Resultado'><tr><th>Fecha</th><th>Pedido</th><th>Asunto</th><th>Comentario</th><th>Estado</th>";
						
						//campos especiales para administración
						//if($acceso) echo "<th>Encargado</th>";
						
						//echo "</tr>";
						if($acceso) echo "<tr class='section-label'><td colspan='6'><span>Cotejados</span></td></tr>";
						
						while($row = mysql_fetch_assoc($consult)){
							echo "<tr>";
							if($acceso)
								echo "<td>".$row['fecha']."</td>";
							echo "<td>".$row['pedido']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
							
							//Indicador de estado del paquete
							if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
							else echo "<td><img src='../sources/icon_cruz.png'></td>";
							
							//campos temporales de control
							/*
							echo "<td>".$row['id']."</td>";
							echo "<td>".$row['padre']."</td>";
							*/
							
							//campos especiales para administración
							if($acceso) echo "<td>".$row['encargado']."</td>";
							
							echo "</tr>";
						} /*echo '</table>';*/
					
					} //else echo "<tr><td colspan='6'><div class='alert'><p>No hay entradas en Cotejados</p></div></td></tr>";
					
					
					/*
					//Extraer entradas de la bitacora Cotejados
					$sql="SELECT * FROM cotejados WHERE cotejados.pedido = '$pedido' && cotejados.padre = '0' ORDER BY cotejados.fecha;";
					$consult = mysql_query($sql, $conex) or die(mysql_error());
					
					if(mysql_num_rows($consult)!=0){
						
						echo "<section id='Cotejados' class='bitacora'>";
						
						while($row = mysql_fetch_assoc($consult)){
							
							echo "<article class='";
							if($row['estado']) echo "validado";
							echo "'>";
								echo "<header>";
									echo "<h1>". $row['asunto'] ."</h1>";
									echo "<span> · ". $row['fecha'] ."</span>";
								echo "</header>";
								echo "<p>". $row['comentario'] ."</p>";
							
								echo "<footer>";
								if($acceso) echo "Por <span>" .$row['encargado']. "</span>";
								echo "</footer>";
								
							echo "</article>";
							
							
							//consulta de entradas hijas
							$sql="SELECT * FROM cotejados WHERE cotejados.pedido = '$pedido' && cotejados.padre = '". $row['id'] ."' ORDER BY cotejados.fecha;";
							$subconsult = mysql_query($sql, $conex) or die(mysql_error());
							
							if(mysql_num_rows($subconsult)!=0){
								while($row = mysql_fetch_assoc($subconsult)){
						
									echo "<article class='child , ";
									if($row['estado']) echo "validado , ";
									if($row['encargado'] == 'cliente') echo "client , ";
									echo "'>";
										echo "<header>";
											echo "<h1>". $row['asunto'] ."</h1>";
											echo "<span> · ". $row['fecha'] ."</span>";
										echo "</header>";
										echo "<p>". $row['comentario'] ."</p>";
										
										echo "<footer>";
										if($acceso) echo "Por <span>" .$row['encargado']. "</span>";
										echo "</footer>";
									
									echo "</article>";
									
								}
							}
						}
						
						echo "</section>";
					
					} else echo "<div class='alert'><p>No hay entradas en Cotejados</p></div>";
					*/
					
					
					
					//Extraer entradas de la bitacora Impresos
					$sql="SELECT * FROM impresos WHERE pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%' ORDER BY fecha;";
					if(!$acceso) $sql="SELECT * FROM impresos WHERE estado = 1 && (pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%') ORDER BY fecha;";
					$consult = mysql_query($sql, $conex) or die(mysql_error());
					
					if(mysql_num_rows($consult)!=0){
						
						if($acceso) echo "<tr class='section-label'><td colspan='6'><span>Impresos</span></td></tr>";
						
						while($row = mysql_fetch_assoc($consult)){
							echo "<tr>";
							if($acceso)
								echo "<td>".$row['fecha']."</td>";
							echo "<td>".$row['pedido']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
							
							//Indicador de estado del paquete
							if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
							else echo "<td><img src='../sources/icon_cruz.png'></td>";
							
							//campos especiales para administración
							if($acceso) echo "<td>".$row['encargado']."</td>";
							
							echo "</tr>";
						}
					
					}
					
					
					
					//Extraer entradas de la bitacora Separados
					$sql="SELECT * FROM separados WHERE pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%' ORDER BY fecha;";
					if(!$acceso) $sql="SELECT * FROM separados WHERE estado = 1 && (pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%') ORDER BY fecha;";
					$consult = mysql_query($sql, $conex) or die(mysql_error());
					
					if(mysql_num_rows($consult)!=0){
						
						if($acceso) echo "<tr class='section-label'><td colspan='6'><span>Separados</span></td></tr>";
						
						while($row = mysql_fetch_assoc($consult)){
							echo "<tr>";
							if($acceso)
								echo "<td>".$row['fecha']."</td>";
							echo "<td>".$row['pedido']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
							
							//Indicador de estado del paquete
							if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
							else echo "<td><img src='../sources/icon_cruz.png'></td>";
							
							//campos especiales para administración
							if($acceso) echo "<td>".$row['encargado']."</td>";
							
							echo "</tr>";
						}
					
					}
					
					
					
					//Extraer entradas de la bitacora Revisión
					$sql="SELECT * FROM revision WHERE pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%' ORDER BY fecha;";
					if(!$acceso) $sql="SELECT * FROM revision WHERE estado = 1 && (pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%') ORDER BY fecha;";
					$consult = mysql_query($sql, $conex) or die(mysql_error());
					
					if(mysql_num_rows($consult)!=0){
						
						if($acceso) echo "<tr class='section-label'><td colspan='6'><span>Revisión</span></td></tr>";
							
						while($row = mysql_fetch_assoc($consult)){
							echo "<tr>";
							if($acceso)
								echo "<td>".$row['fecha']."</td>";
							echo "<td>".$row['pedido']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
							
							//Indicador de estado del paquete
							if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
							else echo "<td><img src='../sources/icon_cruz.png'></td>";
							
							//campos especiales para administración
							if($acceso) echo "<td>".$row['encargado']."</td>";
							
							echo "</tr>";
						}
					
					}
					
					
					
					//Extraer entradas de la bitacora Cerrado
					$sql="SELECT * FROM cerrado WHERE pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%' ORDER BY fecha;";
					if(!$acceso) $sql="SELECT * FROM cerrado WHERE estado = 1 && (pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%') ORDER BY fecha;";
					$consult = mysql_query($sql, $conex) or die(mysql_error());
					
					if(mysql_num_rows($consult)!=0){
						
						if($acceso) echo "<tr class='section-label'><td colspan='6'><span>Cerrado</span></td></tr>";
						
						while($row = mysql_fetch_assoc($consult)){
							echo "<tr>";
							if($acceso)
								echo "<td>".$row['fecha']."</td>";
							echo "<td>".$row['pedido']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
							
							//Indicador de estado del paquete
							if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
							else echo "<td><img src='../sources/icon_cruz.png'></td>";
							
							//campos especiales para administración
							if($acceso) echo "<td>".$row['encargado']."</td>";
							
							echo "</tr>";
						}
					
					}
					
					
					
					//Extraer entradas de la bitacora Recolección
					$sql="SELECT * FROM recoleccion WHERE pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%' ORDER BY fecha;";
					if(!$acceso) $sql="SELECT * FROM recoleccion WHERE estado = 1 && (pedido = '". $pedido ."' || pedido LIKE '". $pedido ."_%') ORDER BY fecha;";
					$consult = mysql_query($sql, $conex) or die(mysql_error());
					
					if(mysql_num_rows($consult)!=0){
						
						if($acceso) echo "<tr class='section-label'><td colspan='6'><span>Recolección</span></td></tr>";
						
						while($row = mysql_fetch_assoc($consult)){
							echo "<tr>";
							if($acceso)
								echo "<td>".$row['fecha']."</td>";
							echo "<td>".$row['pedido']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
							
							//Indicador de estado del paquete
							if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
							else echo "<td><img src='../sources/icon_cruz.png'></td>";
							
							//campos especiales para administración
							if($acceso) echo "<td>".$row['encargado']."</td>";
							
							echo "</tr>";
						}
					
					}
				
					// Cerrar tabla
					echo '</table>';
				
				} else echo "<div class='alert'><p>La información que nos proporciona no coincide con nuestros registros, revise su correo y número de pedido <a href='?'>Aceptar</a></p></div>";
				
			} else echo "<div class='alert'><p>El número de pedido que nos proporcionó no se encuentra en nuestro sistema <a href='?'>Aceptar</a></p></div>";
			
		}	
		?>
	</body>
</html>