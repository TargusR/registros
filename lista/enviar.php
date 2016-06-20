<?php 

/* Datos de la App:
	Nombre: Catalogo, componente registro.
	Version: 1.2
	Codificado por Estudio Veintitres.  
*/
	
	session_start();
	require_once('../blufr/conex.php');
	
	
	//examinar si existe un acceso concedido
	/*
	if(isset($_SESSION['Apps_acceso'])) $acceso = $_SESSION['Apps_acceso'];
	else $acceso = false;
	
	if(isset($_SESSION['Apps_usuario'])) $usuario = $_SESSION['Apps_usuario'];
	else $usuario = false;
	*/
	$acceso = true;
	
	//Obtener parametros de la URL enviada desde el/los formularios
	
	//exclusivos
	if(isset($_POST['pedido'])) $pedido = $_POST['pedido'];
	else $pedido = false;

	if(isset($_POST['correo'])) $correo = $_POST['correo'];
	else $correo = false;
	
	//generales
	if(isset($_POST['asunto'])) $asunto = $_POST['asunto'];
	else $asunto = false;
	
	if(isset($_POST['comentario'])) $comentario = $_POST['comentario'];
	else $comentario = false;
	
	if(isset($_POST['estado'])) $estado = $_POST['estado'];
	else $estado = false;
	
	$sql = "INSERT IGNORE INTO paquetes (pedido,correo) VALUES('$pedido', '$correo');";
	$msg = "<p>Se Añadió correctamente el Registro</p>";
	
	
	
	/*
	
	
	if(isset($_POST['ident'])) $ident = $_POST['ident'];
	else if(isset($_GET['ident'])) $ident = $_GET['ident'];
	else $ident = -1;
	
	if(isset($_GET['del'])) $del = $_GET['del'];
	else $del = 0;
	
	if(isset($_GET['delconfirm'])) $delconfirm = $_GET['delconfirm'];
	else $delconfirm = false;
	
	//Identificar Operaciones
	Switch($ident){
		case 0:
			if($del==0){
				if($nombre && $categoria){
					$sql = "INSERT INTO articulos (nombre,categoria,subcategoria,precio1,precio2,precio3,precio4,precio5,inv_CM,ped_CM,inv_PT05,ped_PT05,inv_PT177,ped_PT177,inv_MT,ped_MT,inv_Alm,proveedores,imagen) VALUES('$nombre', '$categoria', '$subcategoria', '$precio1', '$precio2', '$precio3', '$precio4', '$precio5', '$inv_CM', '$ped_CM', '$inv_PT05', '$ped_PT05', '$inv_PT177', '$ped_PT177', '$inv_MT', '$ped_MT', '$inv_Alm', '$proveedores', '$imagen');";
					$msg = "<p>Se Añadió correctamente el Registro</p>";
				} else {
					$msg = "<p>No se han proporcionado datos suficientes para añadir</p>";
					$sql = "";
				}
			} else {
				if($delconfirm){
					$imagen_old = mysql_fetch_assoc(mysql_query("SELECT imagen FROM articulos WHERE articulos.id = $del"))['imagen'];
					if($imagen_old != NULL) unlink($imagen_old);
					$sql = "DELETE FROM articulos WHERE articulos.id = $del;";
					$msg = "<p>Se ha eliminado el campo con el <b>#ID ".$del."</b> exitosamente</p>";
				} else {
					$msg = "<p>Estás a punto de eliminar el campo con el <b>#ID ".$del.":</b></p>";
					$sql = "";
				}
			}
			break;
		
		case -1:
			$msg = FALSE;
			$sql = "";
			break;
		
		default:
		//cambio chuy
		if($acceso == 'admin'){
			if($imagen ==  Null) $sql = "UPDATE articulos SET nombre = '$nombre', categoria = '$categoria', subcategoria = '$subcategoria', precio1 = '$precio1', precio2 = '$precio2', precio3 = '$precio3', precio4 = '$precio4', precio5 = '$precio5', inv_CM = '$inv_CM', ped_CM = '$ped_CM', inv_PT05 = '$inv_PT05', ped_PT05 = '$ped_PT05', inv_PT177 = '$inv_PT177', ped_PT177 = '$ped_PT177', inv_MT = '$inv_MT', ped_MT = '$ped_MT', inv_Alm = '$inv_Alm', proveedores = '$proveedores', actualizado = CURRENT_TIMESTAMP WHERE articulos.id = $ident;";
			else{
				$imagen_old = mysql_fetch_assoc(mysql_query("SELECT imagen FROM articulos WHERE articulos.id = $ident"))['imagen'];
				if($imagen_old != NULL) unlink($imagen_old);
				$sql = "UPDATE articulos SET nombre = '$nombre', categoria = '$categoria', subcategoria = '$subcategoria', precio1 = '$precio1', precio2 = '$precio2', precio3 = '$precio3', precio4 = '$precio4', precio5 = '$precio5', inv_CM = '$inv_CM', ped_CM = '$ped_CM', inv_PT05 = '$inv_PT05', ped_PT05 = '$ped_PT05', inv_PT177 = '$inv_PT177', ped_PT177 = '$ped_PT177', inv_MT = '$inv_MT', ped_MT = '$ped_MT', inv_Alm = '$inv_Alm', proveedores = '$proveedores', imagen = '$imagen', actualizado = CURRENT_TIMESTAMP WHERE articulos.id = $ident;";
			}
		}
		else {
				$sql = "UPDATE articulos SET ";
				if($nombre) $sql .= "nombre = '$nombre', ";
				if($categoria) $sql .= "categoria = '$categoria', ";
				if($subcategoria) $sql .= "subcategoria = '$subcategoria', ";
				if($inv_CM || $inv_CM === '0') $sql .= "inv_CM = '$inv_CM', ";
				if($ped_CM || $ped_CM === '0') $sql .= "ped_CM = '$ped_CM', ";
				if($inv_PT05 || $inv_PT05 === '0') $sql .= "inv_PT05 = '$inv_PT05', ";
				if($ped_PT05 || $ped_PT05 === '0') $sql .= "ped_PT05 = '$ped_PT05', ";
				if($inv_PT177 || $inv_PT177 === '0') $sql .= "inv_PT177 = '$inv_PT177', ";
				if($ped_PT177 || $ped_PT177 === '0') $sql .= "ped_PT177 = '$ped_PT177', ";
				if($inv_MT || $inv_MT === '0') $sql .= "inv_MT = '$inv_MT', ";
				if($ped_MT || $ped_MT === '0') $sql .= "ped_MT = '$ped_MT', ";
				if($inv_Alm || $inv_Alm === '0') $sql .= "inv_Alm = '$inv_Alm', ";
				if($proveedores) $sql .= "proveedores = '$proveedores', ";
				$sql .= "actualizado = CURRENT_TIMESTAMP WHERE articulos.id = $ident;";          //fin cambio chuy;
		}
			$msg = "<p>Se actualizó correctamente el Registro con el <b>#ID ".$ident."</b></p>";
	} if($sql != "") mysql_query($sql, $conex) or die(mysql_error());
	
	*/
	
	if($sql != "") mysql_query($sql, $conex) or die(mysql_error());
	
	if($acceso){
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Si lees esto, algo estás haciendo mal...</title>
		<style>
			body {
				margin:0; padding:14px;
				font-family:Verdana, Geneva, sans-serif;
				background:#FFF;
				font-size:85%;
				color:#121212;
				}			
			
			.alert{
                background:#FCD125;
    			color:#121212;
    			margin:10px;
    			padding-left:65px;
    			height:50px;
    			position:relative;
				} .alert:before {
					content:'i';
					text-align:center;
					font-size:3em;
					font-family:times,serif;
					font-weight:bold;
					font-style:italic;
					color:#FCD125;
					display:block;
					width:50px;
					line-height:50px;
					position:absolute;
					left:0;
					background:#121212;			
					}
				.alert p { line-height:50px; }
			
			table th {
				background:#121212;
				color:#FCD125;
				text-align:center;
				padding:6px 12px;
				}
			table td{
				border-bottom:1px dotted #b3b3b3;
				padding:2px 12px;
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
			
		</style>
		<script type='text/javascript'>
			function editar(id,nombre,precio1,precio2,precio3,precio4,precio5,inv_CM,ped_CM,inv_PT05,ped_PT05,inv_PT177,ped_PT177,inv_MT,ped_MT,inv_Alm,proveedores,categoria,subcategoria,imagen){
				Editor = parent.document.getElementById("Editor");
				if(Editor.nombre) Editor.nombre.value = nombre;
				if(Editor.precio1) Editor.precio1.value = precio1;
				if(Editor.precio2) Editor.precio2.value = precio2;
				if(Editor.precio3) Editor.precio3.value = precio3;
				if(Editor.precio4) Editor.precio4.value = precio4;
				if(Editor.precio5) Editor.precio5.value.value = precio5;
				if(Editor.inv_CM) Editor.inv_CM.value = inv_CM;
				if(Editor.ped_CM) Editor.ped_CM.value = ped_CM;
				if(Editor.inv_PT05) Editor.inv_PT05.value = inv_PT05;
				if(Editor.ped_PT05) Editor.ped_PT05.value = ped_PT05;
				if(Editor.inv_PT177) Editor.inv_PT177.value = inv_PT177;
				if(Editor.ped_PT177) Editor.ped_PT177.value = ped_PT177;
				if(Editor.inv_MT) Editor.inv_MT.value = inv_MT;
				if(Editor.ped_MT) Editor.ped_MT.value = ped_MT;
				if(Editor.inv_Alm) Editor.inv_Alm.value = inv_Alm;
				if(Editor.proveedores) Editor.proveedores.value = proveedores;
				if(Editor.categoria) Editor.categoria.value = categoria;
				if(Editor.subcategoria) Editor.subcategoria.value = subcategoria;
				Editor.enviar.value = "Guardar";
				//Editor.imagen.value = imagen;
				Editor.ident.value = id;
			}
	
			function limpiar(){
				Editor = parent.document.getElementById("Editor");
				if(Editor.nombre) Editor.nombre.value = "";
				if(Editor.precio1) Editor.precio1.value = "";
				if(Editor.precio2) Editor.precio2.value = "";
				if(Editor.precio3) Editor.precio3.value = "";
				if(Editor.precio4) Editor.precio4.value = "";
				if(Editor.precio5) Editor.precio5.value = "";
				if(Editor.inv_CM) Editor.inv_CM.value = "";
				if(Editor.ped_CM) Editor.ped_CM.value = "";
				if(Editor.inv_PT05) Editor.inv_PT05.value = "";
				if(Editor.ped_PT05) Editor.ped_PT05.value = "";
				if(Editor.inv_PT177) Editor.inv_PT177.value = "";
				if(Editor.ped_PT177) Editor.ped_PT177.value = "";
				if(Editor.inv_MT) Editor.inv_MT.value = "";
				if(Editor.ped_MT) Editor.ped_MT.value = "";
				if(Editor.inv_Alm) Editor.inv_Alm.value = "";
				if(Editor.proveedores) Editor.proveedores.value = "";
				if(Editor.categoria) Editor.categoria.value = "";
				if(Editor.subcategoria) Editor.subcategoria.value = "";
				if(Editor.imagen) Editor.imagen.value = "";
				/*if(Editor.enviar)*/ Editor.enviar.value = "Enviar";
				Editor.ident.value = 0;
				}
			
			function resize(imagen){
				imagen = document.getElementById(imagen);
				ancho = imagen.width;
				alto = imagen.height
				factor = imagen.width / imagen.height;
				if(factor >= 1){
					//más ancha que alta
					imagen.style.height = '80px';
					ancho = Math.round(factor*80);
					imagen.style.maxWidth = ancho + "px";
					imagen.style.left = "-" + ((ancho - 80) / 2) + "px";
				} else { 
					//más alta que ancha
					imagen.style.width = '80px';
					alto = Math.round(80/factor);
					imagen.style.maxHeight = alto + "px";
					imagen.style.bottom = ((alto - 80) / 2) + "px";
				}
			}
			
			var unidadAncho = window.innerWidth - 24;
			function cambiar(niveles){ //para mover el grid
				parent.document.getElementById("mainBody-grid").style.left = "-" + ((unidadAncho + 6) * niveles) + "px"; //calibración de espacio con 6
			}
			
			function expanderTabla(){
				ocultos = document.getElementById("Tabla-Resultado").getElementsByTagName("th");
				i=0;
				while(ocultos[i]){
					if(ocultos[i].className == "extra"){
						if(ocultos[i].style.display != "table-cell") ocultos[i].style.display = "table-cell";
						else ocultos[i].style.display = "none";	
					}					
					i++;
				}
				
				ocultos = document.getElementById("Tabla-Resultado").getElementsByTagName("td");
				i=0;
				while(ocultos[i]){
					if(ocultos[i].className == "extra"){
						if(ocultos[i].style.display != "table-cell") ocultos[i].style.display = "table-cell";
						else ocultos[i].style.display = "none";	
					}					
					i++;
				}
				
			}
		</script>
	</head>
	<body onload='setTimeout(limpiar,10);'>
		<?php if($msg) echo "<div class='alert'>".$msg."</div>"; //mostrar salida

		$consultas = 6; //numero de registros recientes a mostras
		/*
		if($del && !$delconfirm) $sql="SELECT * FROM articulos WHERE articulos.id = $del";
		else $sql="SELECT * FROM articulos ORDER BY articulos.actualizado DESC LIMIT 0,$consultas;";
		*/
		$sql="SELECT * FROM paquetes ORDER BY paquetes.pedido DESC LIMIT 0,$consultas;";
		
		$consult = mysql_query($sql, $conex) or die(mysql_error());
				
		//Impresión de ultimos registros actualizados
		if(mysql_num_rows($consult)!=0){
			
			
			echo "<a href='' onClick='expanderTabla();return false;'>Expander</a>";
			if($acceso =='admin'|| $acceso =='ventas' || $acceso == 'agendados' || $acceso == 'guias') echo "<table id='Tabla-Resultado'><tr><th>#ID</th><th>Imagen</th><th>Nombre</th><th>Precio 1</th><th class='extra'>Precio 2</th><th class='extra'>Precio 3</th><th class='extra'>Precio 4</th><th class='extra'>Precio 5</th><th class='extra'>C. Magno Inv.</th><th class='extra'>C. Magno Ped.</th><th class='extra'>Plaza T. 05 Inv</th><th class='extra'>Plaza T. 05 Ped.</th><th class='extra'>Plaza T. 177 Inv.</th><th class='extra'>Plaza T. 177 Ped.</th><th class='extra'>Macro Tienda Inv.</th><th class='extra'>Macro Tienda Ped.</th><th class='extra'>Almacen</th><th class='extra'>Proveedores</th><th>Categoria</th><th>Subcategoria</th><th>Actualizado</th><th></th><th></th></tr>";
			if($acceso =='sucursal') echo "<table id='Tabla-Resultado'><tr><th>#ID</th><th>Imagen</th><th>Nombre</th><th>Precio 1</th><th class='extra'>" .$sucursal. " Inventario</th><th class='extra'>".$sucursal. " Pedidos</th><th>Categoria</th><th>Subcategoria</th><th>Actualizado</th><th></th></tr>";
			if($acceso == 'almacen') echo "<table id='Tabla-Resultado'><tr><th>#ID</th><th>Imagen</th><th>Nombre</th><th>Precio 1</th><th class='extra'>Precio 2</th><th class='extra'>Precio 3</th><th class='extra'>Precio 4</th><th class='extra'>Precio 5</th><th class='extra'>C. Magno Inv.</th><th class='extra'>C. Magno Ped.</th><th class='extra'>Plaza T. 05 Inv</th><th class='extra'>Plaza T. 05 Ped.</th><th class='extra'>Plaza T. 177 Inv.</th><th class='extra'>Plaza T. 177 Ped.</th><th class='extra'>Macro Tienda Inv.</th><th class='extra'>Macro Tienda Ped.</th><th class='extra'>Almacen</th><th class='extra'>Proveedores</th><th>Categoria</th><th>Subcategoria</th><th>Actualizado</th><th></th></tr>";
			while($row = mysql_fetch_assoc($consult)){
				if ($acceso =='admin') {
					echo "<tr><td>".$row['id']."</td><td><div class='imageContainer'><img id='img".$row['id']."' src='".$row['imagen']."' onload='resize(&#34;img".$row['id']."&#34;);'></div></td><td>".$row['nombre']."</td><td>$".$row['precio1']."</td><td class='extra'>$".$row['precio2']."</td><td class='extra'>$".$row['precio3']."</td><td class='extra'>$".$row['precio4']."</td><td class='extra'>$".$row['precio5']."</td><td class='extra'>".$row['inv_CM']."</td><td class='extra'>".$row['ped_CM']."</td><td class='extra'>".$row['inv_PT05']."</td><td class='extra'>".$row['ped_PT05']."</td><td class='extra'>".$row['inv_PT177']."</td><td class='extra'>".$row['ped_PT177']."</td><td class='extra'>".$row['inv_MT']."</td><td class='extra'>".$row['ped_MT']."</td><td class='extra'>".$row['inv_Alm']."</td><td class='extra'>".$row['proveedores']."</td><td>".$row['categoria']."</td><td>".$row['subcategoria']."</td><td>".$row['actualizado']."</td>";	
					echo "<td><a href='#' onClick='editar(&#34;".$row['id']."&#34;,&#34;".$row['nombre']."&#34;,&#34;".$row['precio1']."&#34;,&#34;".$row['precio2']."&#34;,&#34;".$row['precio3']."&#34;,&#34;".$row['precio4']."&#34;,&#34;".$row['precio5']."&#34;,&#34;".$row['inv_CM']."&#34;,&#34;".$row['ped_CM']."&#34;,&#34;".$row['inv_PT05']."&#34;,&#34;".$row['ped_PT05']."&#34;,&#34;".$row['inv_PT177']."&#34;,&#34;".$row['ped_PT177']."&#34;,&#34;".$row['inv_MT']."&#34;,&#34;".$row['ped_MT']."&#34;,&#34;".$row['inv_Alm']."&#34;,&#34;".$row['proveedores']."&#34;,&#34;".$row['categoria']."&#34;,&#34;".$row['subcategoria']."&#34;,&#34;".$row['imagen']."&#34;);cambiar(0);return false;' title='Editar este campo'><img src='sources/editar.png'></a></td>";
					echo "<td><a href='?del=".$row['id']."&ident=0' title='Eliminar este Campo'><img src='sources/eliminar.png'></a></td></tr>";
				} else if ($acceso =='sucursal') { 
					echo "<tr><td>".$row['id']."</td><td><div class='imageContainer'><img id='img".$row['id']."' src='".$row['imagen']."' onload='resize(&#34;img".$row['id']."&#34;);'></div></td><td>".$row['nombre']."</td><td>$".$row['precio1']."</td><td class='extra'>".$row[$inv]."</td><td class='extra'>".$row[$ped]."</td><td>".$row['categoria']."</td><td>".$row['subcategoria']."</td><td>".$row['actualizado']."</td>";	
					echo "<td><a href='#' onClick='editar(&#34;".$row['id']."&#34;,&#34;".$row['nombre']."&#34;,&#34;".$row['precio1']."&#34;,&#34;".$row['precio2']."&#34;,&#34;".$row['precio3']."&#34;,&#34;".$row['precio4']."&#34;,&#34;".$row['precio5']."&#34;,&#34;".$row['inv_CM']."&#34;,&#34;".$row['ped_CM']."&#34;,&#34;".$row['inv_PT05']."&#34;,&#34;".$row['ped_PT05']."&#34;,&#34;".$row['inv_PT177']."&#34;,&#34;".$row['ped_PT177']."&#34;,&#34;".$row['inv_MT']."&#34;,&#34;".$row['ped_MT']."&#34;,&#34;".$row['proveedores']."&#34;,&#34;".$row['categoria']."&#34;,&#34;".$row['subcategoria']."&#34;,&#34;".$row['imagen']."&#34;);cambiar(0);return false;' title='Editar este campo'><img src='sources/editar.png'></a></td>";
				} else if($acceso =='ventas' || $acceso == 'agendados' || $acceso == 'guias' ){
					echo "<tr><td>".$row['id']."</td><td><div class='imageContainer'><img id='img".$row['id']."' src='".$row['imagen']."' onload='resize(&#34;img".$row['id']."&#34;);'></div></td><td>".$row['nombre']."</td><td>$".$row['precio1']."</td><td class='extra'>$".$row['precio2']."</td><td class='extra'>$".$row['precio3']."</td><td class='extra'>$".$row['precio4']."</td><td class='extra'>$".$row['precio5']."</td><td class='extra'>".$row['inv_CM']."</td><td class='extra'>".$row['ped_CM']."</td><td class='extra'>".$row['inv_PT05']."</td><td class='extra'>".$row['ped_PT05']."</td><td class='extra'>".$row['inv_PT177']."</td><td class='extra'>".$row['ped_PT177']."</td><td class='extra'>".$row['inv_MT']."</td><td class='extra'>".$row['ped_MT']."</td><td class='extra'>".$row['proveedores']."</td><td>".$row['categoria']."</td><td>".$row['subcategoria']."</td><td>".$row['actualizado']."</td>";	
				} else if($acceso == 'almacen') {
					echo "<tr><td>".$row['id']."</td><td><div class='imageContainer'><img id='img".$row['id']."' src='".$row['imagen']."' onload='resize(&#34;img".$row['id']."&#34;);'></div></td><td>".$row['nombre']."</td><td>$".$row['precio1']."</td><td class='extra'>$".$row['precio2']."</td><td class='extra'>$".$row['precio3']."</td><td class='extra'>$".$row['precio4']."</td><td class='extra'>$".$row['precio5']."</td><td class='extra'>".$row['inv_CM']."</td><td class='extra'>".$row['ped_CM']."</td><td class='extra'>".$row['inv_PT05']."</td><td class='extra'>".$row['ped_PT05']."</td><td class='extra'>".$row['inv_PT177']."</td><td class='extra'>".$row['ped_PT177']."</td><td class='extra'>".$row['inv_MT']."</td><td class='extra'>".$row['ped_MT']."</td><td class='extra'>".$row['inv_Alm']."</td><td class='extra'>".$row['proveedores']."</td><td>".$row['categoria']."</td><td>".$row['subcategoria']."</td><td>".$row['actualizado']."</td>";	
					echo "<td><a href='#' onClick='editar(&#34;".$row['id']."&#34;,&#34;".$row['nombre']."&#34;,&#34;".$row['precio1']."&#34;,&#34;".$row['precio2']."&#34;,&#34;".$row['precio3']."&#34;,&#34;".$row['precio4']."&#34;,&#34;".$row['precio5']."&#34;,&#34;".$row['inv_CM']."&#34;,&#34;".$row['ped_CM']."&#34;,&#34;".$row['inv_PT05']."&#34;,&#34;".$row['ped_PT05']."&#34;,&#34;".$row['inv_PT177']."&#34;,&#34;".$row['ped_PT177']."&#34;,&#34;".$row['inv_MT']."&#34;,&#34;".$row['ped_MT']."&#34;,&#34;".$row['inv_Alm']."&#34;,&#34;".$row['proveedores']."&#34;,&#34;".$row['categoria']."&#34;,&#34;".$row['subcategoria']."&#34;,&#34;".$row['imagen']."&#34;);cambiar(0);return false;' title='Editar este campo'><img src='sources/editar.png'></a></td>";
				}
			} echo '</table>';
			if($del && !$delconfirm) echo "<p>¿Estas Seguro? <a href='?del=".$del."&delconfirm=true&ident=0'>Si</a> | <a href='?'>Olvidalo...</a></p>";
		
			echo "<table id='Tabla-Resultado'><tr><th>Pedido</th><th>Usuario</th></tr>";
			while($row = mysql_fetch_assoc($consult)){
				echo "<tr><td>".$row['pedido']."</td><td>".$row['correo']."</td>";	
				//echo "<td><a href='#' onClick='editar(&#34;".$row['id']."&#34;,&#34;".$row['nombre']."&#34;,&#34;".$row['precio1']."&#34;,&#34;".$row['precio2']."&#34;,&#34;".$row['precio3']."&#34;,&#34;".$row['precio4']."&#34;,&#34;".$row['precio5']."&#34;,&#34;".$row['inv_CM']."&#34;,&#34;".$row['ped_CM']."&#34;,&#34;".$row['inv_PT05']."&#34;,&#34;".$row['ped_PT05']."&#34;,&#34;".$row['inv_PT177']."&#34;,&#34;".$row['ped_PT177']."&#34;,&#34;".$row['inv_MT']."&#34;,&#34;".$row['ped_MT']."&#34;,&#34;".$row['inv_Alm']."&#34;,&#34;".$row['proveedores']."&#34;,&#34;".$row['categoria']."&#34;,&#34;".$row['subcategoria']."&#34;,&#34;".$row['imagen']."&#34;);cambiar(0);return false;' title='Editar este campo'><img src='sources/editar.png'></a></td>";
			} echo '</table>';
		
		} else echo "<div class='alert'><p>Parece que la tabla está vacía :0 <a href='?'>Aceptar</a></p></div>";
		
		?>
	</body>
</html>
<?php } else header("Location: ../"); //redireccionar a esc ?>