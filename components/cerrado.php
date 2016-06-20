<?php 

/*-----------------------
	.blu framework
	Nombre componente: Cerrado
	Version: 1.01
-----------------------*/
	
	session_start();
	ini_set('date.timezone','America/Mexico_City');
	set_time_limit (120);
	require_once('../blufr/conex.php');
	require_once('../blufr/blufr.php');
	
	// Parametros de la tabla
	$consultas = 16; //numero de registros recientes a mostrar por página
	$table_name = 'cerrado';
	$parent_table_name = 'revision';
	$valid_states = array(
		"Paquete Cerrado"
		); // Estados definidos como aprovados para las publicaciones
	$estado_label_next = "Esperando Recolección";
	$estado_label_act = "Mercancía Revisada";
	
	// Examinar si existe un acceso concedido
	if(isset($_SESSION['blu_usuario'])) $usuario = $_SESSION['blu_usuario'];
	else $usuario = false;
	
	if(isset($_SESSION['blu_acceso'])) $acceso = $_SESSION['blu_acceso'];
	else $acceso = false;
	
	// Obtener tablas autorizadas
	if(isset($_SESSION['blu_allowed_views'])) {
		$bitacoras = $_SESSION['blu_allowed_views'];
	
		// Denegar acceso por falta de permisos
		if( !in_array($table_name, $bitacoras) ) $acceso = false;
	} else $acceso = false;
	
	// Parametros de la URL enviada desde el/los formularios
	
	// Exclusivos
	$pedido = catchValue('pedido','POST');
	
	// Generales
	$asunto = catchValue('asunto','POST');
	$comentario = catchValue('comentario','POST');
	$padre = catchValue('padre','POST',0);
	
	// Definir Estado de la publicación
	if(in_array($asunto, $valid_states)) $estado = 1;
	else $estado = 0;
	
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
		$_SESSION[$table_name]['tabla'] = $tabla;	
	else {
		if( isset($_SESSION[$table_name]['tabla']) )
			$tabla = $_SESSION[$table_name]['tabla'];
		else
			$tabla = 'RECIENTES';
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
			if($pedido < 1 || !is_numeric(explode('_', $pedido)[0]) || !is_numeric(explode('_', $pedido)[0]) ) {
				$msg = "<p>Error: Proporcione un número de pedido válido</p>";
				$alert = true;
				break;
			}
				
			// Definir Etiquetas de Tiempo y estado
			$timestamp = date("Y-m-d H:i:s");
			if($estado)
				$estado_label = $estado_label_next;
			else
				$estado_label = $estado_label_act;
			
			
			// revisar si ya existe en paquetes
			$paquete = "SELECT estado from paquetes WHERE paquetes.pedido = '$pedido';";
			$paquete = mysql_query($paquete, $conex) or die(mysql_error());
			if(mysql_num_rows($paquete)==0) {
				// Alertar que el pedido no existe, impedir registros fantasma
				$msg = "<p>Error: el número de pedido <b>". $pedido ."</b> no se encuentra registrado en el sistema</p>";
				$alert = true;
				break;
			} else {
				// sacar estado actual
				$paquete = mysql_fetch_assoc($paquete);
				
				// corroborar actualización
				if( analize_state($paquete["estado"], $estado_label) )
					$sql[] = "UPDATE paquetes SET fultima = '$timestamp', estado = '$estado_label' WHERE pedido = '$pedido';";
			}
				
			$sql[] = "INSERT INTO ". $table_name ." (fecha, encargado, pedido, asunto, comentario, padre, estado) VALUES('$timestamp', '$usuario', '$pedido', '$asunto', '$comentario', '$padre', '$estado');";
			$msg = "<p>Se añadió correctamente el registro <b>". $asunto ."</b> para el pedido <b>". $pedido ."</b></p>";
		
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
		<script type='text/javascript'>
			
			function addForm_registrar(fila,num_pedido){
				
				anterior = document.getElementById('inlineForm');
				if(anterior != null) fila.parentNode.removeChild(anterior);
				
				nuevoFormulario = document.createElement('form');
				nuevoFormulario.method = 'POST';
				/*nuevoFormulario.action = '?';  Ayudaría a regresar al inicio en el indice */
				
				tablaForm = document.createElement('table');
				filaForm = document.createElement('tr');
				
				//Campos Ocultos
				pedido = document.createElement('input');
				pedido.type = 'HIDDEN';
				pedido.name = 'pedido';
				pedido.value = num_pedido;
				nuevoFormulario.appendChild(pedido);
				
				ident = document.createElement('input');
				ident.type = 'HIDDEN';
				ident.name = 'ident';
				ident.value = 'REGISTER';
				nuevoFormulario.appendChild(ident);
				
				
				//Campo Select
				celdaForm = document.createElement('th');
				
				label = document.createElement('span');
				label.appendChild(document.createTextNode('Asunto'));
				celdaForm.appendChild(label);
				filaForm.appendChild(celdaForm);
				
				celdaForm = document.createElement('td');
				
				asunto = document.createElement('select');
				asunto.name = 'asunto';
					option = document.createElement('option');
					option.value = 'Paquete Cerrado';
					option.appendChild(document.createTextNode('Paquete Cerrado'));
					asunto.appendChild(option);
					
					/*
					option = document.createElement('option');
					option.value = 'Respuesta';
					option.appendChild(document.createTextNode('Respuesta'));
					asunto.appendChild(option);
					*/
					
				celdaForm.appendChild(asunto);
				filaForm.appendChild(celdaForm);
				
				//Campo Nota
				celdaForm = document.createElement('th');
				
				label = document.createElement('span');
				label.appendChild(document.createTextNode('Comentario'));
				celdaForm.appendChild(label);
				filaForm.appendChild(celdaForm);
				
				celdaForm = document.createElement('td');
				
				comentario = document.createElement('textarea');
				comentario.name = 'comentario';
				celdaForm.appendChild(comentario);
				filaForm.appendChild(celdaForm);
				
				/*Campo Check
				celdaForm = document.createElement('td');
				
				label = document.createElement('span');
				label.appendChild(document.createTextNode('Validado'));
				celdaForm.appendChild(label);
				
				estado = document.createElement('input');
				estado.type = 'CHECKBOX';
				estado.name = 'estado';
				celdaForm.appendChild(estado);
				filaForm.appendChild(celdaForm);*/
				
				//Botón envío
				celdaForm = document.createElement('td');
				
				agregar = document.createElement('input');
				agregar.type = 'SUBMIT';
				agregar.value = 'Agregar';
				agregar.className = 'cmd';
				celdaForm.appendChild(agregar);
				filaForm.appendChild(celdaForm);
				
				// Botón Cancelar
				celdaForm = document.createElement('td');
				
				cancelar = document.createElement('a');
				cancelar.href = '#';
				// Eliminación con evento click
				cancelar.addEventListener("click", function(){
					anterior = document.getElementById('inlineForm');
					if(anterior != null) fila.parentNode.removeChild(anterior);
				});
				icon = document.createElement('img')
				icon.src = '../sources/close_crox_white_small.png';
				cancelar.appendChild(icon);
				celdaForm.appendChild(cancelar);
				filaForm.appendChild(celdaForm);
				
				//Unir
				tablaForm.appendChild(filaForm);
				nuevoFormulario.appendChild(tablaForm);
				
				//Construir Celda contenedora
				nuevaFila = document.createElement('tr');
				nuevaFila.id = 'inlineForm';
				nuevaColumna = document.createElement('td');
				nuevaColumna.colSpan = '8';
				nuevaColumna.appendChild(nuevoFormulario);
				nuevaFila.appendChild(nuevaColumna);
				fila.parentNode.insertBefore(nuevaFila,fila.nextSibling);
			}
			
			function addForm_responder(fila,num_pedido,id_padre){
				
				anterior = document.getElementById('inlineForm');
				if(anterior != null) fila.parentNode.removeChild(anterior);
				
				nuevoFormulario = document.createElement('form');
				nuevoFormulario.method = 'POST';
				/*nuevoFormulario.action = '?';  Ayudaría a regresar al inicio en el indice */
				
				tablaForm = document.createElement('table');
				filaForm = document.createElement('tr');
				
				//Campos Ocultos
				pedido = document.createElement('input');
				pedido.type = 'HIDDEN';
				pedido.name = 'pedido';
				pedido.value = num_pedido;
				nuevoFormulario.appendChild(pedido);
				
				ident = document.createElement('input');
				ident.type = 'HIDDEN';
				ident.name = 'ident';
				ident.value = 'REGISTER';
				nuevoFormulario.appendChild(ident);
				
				padre = document.createElement('input');
				padre.type = 'HIDDEN';
				padre.name = 'padre';
				padre.value = id_padre;
				nuevoFormulario.appendChild(padre);
				
				asunto = document.createElement('input');
				asunto.type = 'HIDDEN';
				asunto.name = 'asunto';
				asunto.value = 'respuesta';
				nuevoFormulario.appendChild(asunto);
				
				//Campo Nota
				celdaForm = document.createElement('th');
				
				label = document.createElement('span');
				label.appendChild(document.createTextNode('Responder'));
				celdaForm.appendChild(label);
				filaForm.appendChild(celdaForm);
				
				celdaForm = document.createElement('td');
				
				comentario = document.createElement('textarea');
				comentario.name = 'comentario';
				celdaForm.appendChild(comentario);
				filaForm.appendChild(celdaForm);
				
				//Botón envío
				celdaForm = document.createElement('td');
				
				agregar = document.createElement('input');
				agregar.type = 'SUBMIT';
				agregar.value = 'Agregar';
				agregar.className = 'cmd';
				celdaForm.appendChild(agregar);
				filaForm.appendChild(celdaForm);
				
				// Botón Cancelar
				celdaForm = document.createElement('td');
				
				cancelar = document.createElement('a');
				cancelar.href = '#';
				// Eliminación con evento click
				cancelar.addEventListener("click", function(){
					anterior = document.getElementById('inlineForm');
					if(anterior != null) fila.parentNode.removeChild(anterior);
				});
				icon = document.createElement('img')
				icon.src = '../sources/close_crox_white_small.png';
				cancelar.appendChild(icon);
				celdaForm.appendChild(cancelar);
				filaForm.appendChild(celdaForm);
				
				//Unir
				tablaForm.appendChild(filaForm);
				nuevoFormulario.appendChild(tablaForm);
				
				//Construir Celda contenedora
				nuevaFila = document.createElement('tr');
				nuevaFila.id = 'inlineForm';
				nuevaColumna = document.createElement('td');
				nuevaColumna.colSpan = '8';
				nuevaColumna.appendChild(nuevoFormulario);
				nuevaFila.appendChild(nuevaColumna);
				fila.parentNode.insertBefore(nuevaFila,fila.nextSibling);
			}
			
		</script>
	</head>
	<body class='dobleCetrado' 
		<?php
		if(!$alert)
			echo "onLoad='setTimeout(limpiar(parent.document.getElementById(&quot;cerrado-form&quot;)),5);'";
		?>
	>
	
	<?php
		
		 // Mostrar salida de alerta
		if( isset($msg) )
			echo "<div class='alert'>".$msg."<a href='#' class='close' onclick='remove_element(this.parentNode); return false;'><img src='../sources/close_crox_black_small.png'></a></div>";
		
	
		// Selector de tablas
		echo "<div id='sub-selector'>";
		if($tabla == 'HEREDADOS') echo "<span>Nuevos Revisados</span>";
		else echo "<a href='?tabla=HEREDADOS'>Nuevos Revisados</a>";
		if($tabla == 'RECIENTES') echo "<span>Bitacora Cerrado</span>";
		else echo "<a href='?tabla=RECIENTES'>Bitacora Cerrado</a>";
		
		if($filtro) echo "<a href='?filtro=unset' class='filtro'>Borrar Filtro<img src='../sources/borrar_filtro_small.png'></a>";
		echo "<a href='?' class='reload''>Recargar<img src='../sources/reload_small_white.png'></a>";
		echo "</div>";
	
		
		switch($tabla) {
			
			case 'HEREDADOS': // Tabla Recientes
				
				// Ensamblar Filtro
				if($filtro)
					$filtro = " && ". $parent_table_name .".pedido like '%". $filtro ."%' || ". $parent_table_name .".encargado like '%". $filtro ."%' || ". $parent_table_name .".comentario like '%". $filtro ."%' || paquetes.correo like '%". $filtro ."%'";
				
				//obtener el total de registros
				$sql="SELECT count(*) as total FROM ". $parent_table_name ." LEFT JOIN (SELECT correo, pedido as pedido_p FROM paquetes) AS paquetes ON ". $parent_table_name .".pedido = paquetes.pedido_p WHERE ". $parent_table_name .".pedido not in (SELECT ". $table_name .".pedido from ". $table_name.") && ". $parent_table_name .".estado = 1". $filtro .";";
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
				$sql="SELECT * FROM ". $parent_table_name ." LEFT JOIN (SELECT correo, pedido as pedido_p FROM paquetes) AS paquetes ON ". $parent_table_name .".pedido = paquetes.pedido_p WHERE ". $parent_table_name .".pedido not in (SELECT ". $table_name .".pedido from ". $table_name.") && ". $parent_table_name .".estado = 1". $filtro ." ORDER BY ". $orderBy ." ". $orderBy_order ." LIMIT ". $consultas * $indice .", $consultas;";
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
						
						if ($orderBy == "correo") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=correo'>Cliente</a></th>";
						
						if ($orderBy == "encargado") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=encargado'>Encargado</a></th>";
						
						if ($orderBy == "asunto") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=asunto'>Asunto</a></th>";
						
						if ($orderBy == "comentario") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=comentario'>Comentario</a></th>";
						
						if ($orderBy == "estado") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=estado'>Estado</a></th>";
						
						echo "<th>Añadir</th>";
						
					echo "</tr>";
					
					while($row = mysql_fetch_assoc($consult)){
						echo "<tr><td>".$row['fecha']."</td><td>".$row['pedido']."</td><td> ".$row['correo']." </td><td>".$row['encargado']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
						
						//Indicador de estado del paquete
						if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
						else echo "<td><img src='../sources/icon_cruz.png'></td>";
						
						//Boton para Anidación
						echo "<td><a href='#' onClick='addForm_registrar(this.parentNode.parentNode,\"". $row['pedido'] ."\"); return false;'>Cerrar</a></td>";
						
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
			
			
			case 'RECIENTES': // Tabla Recientes
			
				// Ensamblar Filtro
				if($filtro)
					$filtro = " WHERE ". $table_name .".pedido like '%". $filtro ."%' || ". $table_name .".encargado like '%". $filtro ."%' || ". $table_name .".comentario like '%". $filtro ."%' || paquetes.correo like '%". $filtro ."%'";
				
				//obtener el total de registros
				$sql="SELECT count(*) as total FROM ". $table_name ." LEFT JOIN (SELECT correo, pedido as pedido_p FROM paquetes) AS paquetes ON ". $table_name .".pedido = paquetes.pedido_p". $filtro .";";
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
				$sql="SELECT * FROM ". $table_name ." LEFT JOIN (SELECT correo, pedido as pedido_p FROM paquetes) AS paquetes ON ". $table_name .".pedido = paquetes.pedido_p". $filtro ." ORDER BY ". $orderBy ." ". $orderBy_order ." LIMIT ". $consultas * $indice .", $consultas;";
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
						
						if ($orderBy == "correo") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=correo'>Cliente</a></th>";
						
						if ($orderBy == "encargado") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=encargado'>Encargado</a></th>";
						
						if ($orderBy == "asunto") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=asunto'>Asunto</a></th>";
						
						if ($orderBy == "comentario") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=comentario'>Comentario</a></th>";
						
						if ($orderBy == "estado") echo "<th class='ordered , ". $orderBy_order ."'>";
						else echo "<th>";
						echo "<a href='?indice=". $indice ."&orderBy=estado'>Estado</a></th>";
						
						echo "<th>Añadir</th>";
						
					echo "</tr>";
					
					while($row = mysql_fetch_assoc($consult)){
						echo "<tr><td>".$row['fecha']."</td><td>".$row['pedido']."</td><td> ".$row['correo']." </td><td>".$row['encargado']."</td><td>".$row['asunto']."</td><td>".$row['comentario']."</td>";
						
						//Indicador de estado del paquete
						if($row['estado']) echo "<td><img src='../sources/icon_paloma.png'></td>";
						else echo "<td><img src='../sources/icon_cruz.png'></td>";
						
						//Boton para Anidación
						if($row['padre'] > 0) echo "<td><a href='#' onClick='addForm_responder(this.parentNode.parentNode,\"". $row['pedido'] ."\",". $row['padre'] ."); return false;'>Responder</a></td>";
						else echo "<td><a href='#' onClick='addForm_registrar(this.parentNode.parentNode,\"". $row['pedido'] ."\"); return false;'>+</a></td>";
						
						echo "</tr>";
						
					} echo '</table>';
				
				} else {
					if($filtro)
						echo "<div class='alert'><p>No hay información para mostrar con ese Filtro :/ <a href='?filtro=unset'>Borrar Filtro</a></p></div>";
					else
						echo "<div class='alert'><p>Parece que la tabla está vacía :0 <a href='?'>Aceptar</a></p></div>";
				}
				
				indice($indice, $total_paginas);
				
				break;
		}
		
		?>
	</body>
</html>
<?php } else header("Location: ../"); //redireccionar a esc ?>