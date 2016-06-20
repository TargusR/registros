<?php

/* Datos de la App:
	Nombre: Catalogo, base.
	Version: 1.2
	Codificado por Estudio Veintitres.  
*/

	session_start();
	//buscar comandos en la url
	if(isset($_GET['cmd'])){
		$cmd = $_GET['cmd'];
		if($cmd == "logmeout") unset($_SESSION['Apps_acceso']);
	} else $cmd = false;
	
	//examinar si existe un acceso concedido
	if(isset($_SESSION['Apps_acceso'])) $acceso = $_SESSION['Apps_acceso'];
	else $acceso = false;
	
	/* Añadido identificadores de Datos de Usuario*/
	
	if(isset($_SESSION['Apps_usuario'])) $usuario = $_SESSION['Apps_usuario'];
	else $usuario = false;
	
	if(isset($_SESSION['Apps_nombre'])) $nombre = utf8_encode($_SESSION['Apps_nombre']);
	else $nombre = false;
	
	if($acceso){ //checar acceso y publicar
?>

<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8" />
	<title>Listado de Productos</title>
	<style>
body{
	margin:0; padding:0;
	font-family:Verdana, Geneva, sans-serif;
    background:#EAEAEA url('../sources/fondo_gris.jpg') center center;
	}
a, a:visited {
	border:0;
	text-decoration:none; color:#e04243;
	transition:color .6s;
	} a:hover { color:#e76969; }
		
/* Menu de Aplicación */
#smartMenu {
    border-bottom:6px solid #b3b3b3;
    margin-bottom:20px;
    background:#FFF;
    color:#B3B3B3;
    font-size:1.4em;
	position:relative;
	text-align:left;
    }
    #smartMenu span, #smartMenu ul{
        display:inline-block;
        line-height:50px;
        list-style-type:none;
        padding:0 20px;
        margin:0;
		text-indent:0;
        }
    #smartMenu ul li{
        display:inline-block;
        padding:0 25px;
        border-right:1px dotted #B3B3B3;
		transition:background .7s;
		margin-left:-8px;
        }
        #smartMenu ul li:first-child{ border-left:1px dotted #B3B3B3; margin-left:0;}
		#smartMenu ul li:hover{ background:#EAEAEA; }
	#appID {
		margin-left:60px!important;
		}
	#fullname{
		position:absolute;
		right:30px;
		color:#91D6F7;
		}
	#smartMenu div {
		position:absolute;
		width:40px;
		height:50px;
		margin:0; padding:0;
		overflow:hidden;
		}
		#backLink {
			top:0; left:0;
			position:relative;
			}
			#backLink a img {
				position:absolute;
				top:0; left:0;
				transition:left .3s;
				} #backLink a img:hover {
					left:-40px;
					}
		#config { top:0; right:0; }
		
/* Slider de Trabajo */
#mainBody {
	margin:0 12px;
	overflow:hidden;
	position:relative;
	}
	#mainBody-grid{
		margin:0; padding:0;
		background:#b3b3b3;
		background:rgba(179,179,179,.6);
		left:0px;
		transition:left .35s ease-in-out;
		}
    #mainBody article {
		margin:0;padding:0;
        display:inline-block;
		overflow:hidden;
        }
		#mainBody article form span {
			font-style:italic;
			}
		
/* Estilo de Formulario */
form {
	padding:14px 20px;
	line-height:2em;
	color:#121212;
	}
	form input, form select, form a.cmd {
		border:1px solid #fff;
		padding:3px 5px;
		color:#6f6f6f;
		transition:border .2s ease-out, background .4s ease-in;
		}
		form input.noborder{ border:0; }
		form input.cmd {
			font-size:1em;
			margin:8px 2px;
			}
		form a.cmd{
			padding:5px 7px;
			}
		form .cmd-cancel{
			background-color:#e04243;
			color:#FFF!important;
			border-color:#e76969!important;
			}
		form .cmd-cancel:hover{
			background:#e76969;
			}
			form input:focus, form select:focus{
				border-color:#FCD125;
				}
			form input:hover, form select:hover, form a.cmd:hover{
				border-color:#EAEAEA;
				}

		
</style>
	<script type='text/javascript'>
		function limpiar(){
			Editor = document.getElementById("Editor");
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
			if(Editor.proveedores) Editor.proveedores.value = "";
			if(Editor.categoria) Editor.categoria.value = "";
			if(Editor.subcategoria) Editor.subcategoria.value = "";
			if(Editor.imagen) Editor.imagen.value = "";
			if(Editor.enviar) Editor.enviar.value = "Enviar";
			Editor.ident.value = 0;
		}
		
		var unidadAncho = window.innerWidth - 24;
		var unidadAlto = window.innerHeight - 88;
		function workarea(){
			cuadros = document.getElementById("mainBody");
			cuadros.style.height = unidadAlto + "px";
			cuadros = cuadros.getElementsByTagName("article");
			i = 0;
			while(cuadros[i]){
				cuadros[i].style.width = unidadAncho + "px";
				cuadros[i].style.height = unidadAlto + "px";
					//especial para parejas de forms y frames, no se use si las pestañas contienen más de uno
					pieza = cuadros[i].getElementsByTagName("form");
					if(pieza[0]) altoform = unidadAlto - pieza[0].offsetHeight;
					pieza = cuadros[i].getElementsByTagName("iframe");
					if(pieza[0]) pieza[0].style.height = altoform + "px";
				i++;
			}
			cuadros = document.getElementById("mainBody-grid");
			cuadros.style.width = (i*unidadAncho + (i-1)*6) + "px"; //el 6 es por calibración de espacios, puede requerir ajustes -->tu muy bien josue!
			cuadros.style.position = "absolute";
		}
		
		function cambiar(niveles){
			document.getElementById("mainBody-grid").style.left = "-" + ((unidadAncho + 6) * niveles) + "px"; //mismo caso, 6 de calibración de espacio
		}
		
		
		
	</script>
</head>
	
<body onLoad='workarea();' onResize='workarea();'>
	
	<nav id='smartMenu'>
		<div id='backLink'><a href='../'><img src='../sources/backarrow.png'></a></div>
		<span id='appID'>Catálogo</span>
		<ul>
			<!-- inicio cambio chuy !-->
			<?php if($acceso == 'admin' || $acceso == 'sucursal' || $acceso == 'almacen'){ ?>
				<li><a href='#' onClick='cambiar(0);return false;'>Registrar</a></li>    
				<li><a href='#' onClick='cambiar(1);return false;'>Buscar</a></li>
				<li><a href='#' onClick='cambiar(2);return false;'>Inventario</a></li>
			<?php } else {  ?>
				<li><a href='#' onClick='cambiar(0);return false;'>Buscar</a></li>
				<li><a href='#' onClick='cambiar(1);return false;'>Inventario</a></li>
			<?php }  ?>
			<!-- fin cambio chuy !-->
		</ul>
		<span id='fullname'><?php echo $nombre;?></span>
		<div id='config'><a href='?cmd=logmeout'><img src='../sources/config.png'></a></div>
	</nav>
	
	<section id="mainBody">
		<div id="mainBody-grid">
			
			<?php if($acceso == 'admin' || $acceso == 'sucursal' || $acceso == 'almacen'){ ?> <!-- Pestaña Exclusiva para cuentas con acceso a edición -->
				<article id="registrar">
					<form target='sender' method=POST action='enviar.php' id='Editor' enctype='multipart/form-data'>
						<?php if($acceso == 'admin') { ?>
							<span>Nombre: </span><input type=TEXT name='nombre' size='100'>
							<br>
							<span>Categoría: </span><select name='categoria'> 
								<option value='Accesorios de Computo' selected=''>Accesorios de Computo</option>
								<option value='Almacenamiento'>Almacenamiento</option>
								<option value='Audio y Electrónica'>Audio y Electrónica</option>
								<option value='Cables y Adaptadores'>Cables y Adaptadores</option>
								<option value='Ensamble PC'>Ensamble PC</option>
								<option value='Redes'>Redes</option>
							</select> 
							<span>Subcategoria: </span><input type=TEXT name='subcategoria'> 
							<br>
							<span>Precio 1: </span><input type=TEXT name='precio1' size='8'>
							<span>Precio 2: </span><input type=TEXT name='precio2' size='8'>
							<span>Precio 3: </span><input type=TEXT name='precio3' size='8'>
							<span>Precio 4: </span><input type=TEXT name='precio4' size='8'>
							<span>Precio 5: </span><input type=TEXT name='precio5' size='8'>
						<?php } ?>
						<table>
							<tr>
								<th></th>
								<?php if($acceso == 'admin' || $usuario == 'suc_CM' || $usuario == 'almacen'){ ?><th>Centro Magno</th><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'suc_PT05' || $usuario == 'almacen'){ ?><th>Plaza T. 05</th><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'suc_PT177' || $usuario == 'almacen'){ ?><th>Plaza T. 177</th><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'suc_MT' || $usuario == 'almacen'){ ?><th>Macro Tienda</th><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'almacen'){ ?><th>Almacen</th><?php } ?>
							</tr><tr>
								<td><span>Inventario</span></td>
								<?php if($acceso == 'admin' || $usuario == 'suc_CM' || $usuario == 'almacen'){ ?><td><input type=TEXT name='inv_CM'></td><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'suc_PT05' || $usuario == 'almacen'){ ?><td><input type=TEXT name='inv_PT05'></td><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'suc_PT177' || $usuario == 'almacen'){ ?><td><input type=TEXT name='inv_PT177'></td><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'suc_MT' || $usuario == 'almacen'){ ?><td><input type=TEXT name='inv_MT'></td><?php } ?>
								<?php if($acceso == 'admin' || $usuario == 'almacen'){ ?><td><input type=TEXT name='inv_Alm'></td><?php } ?>
							</tr>
							<?php if($acceso == 'admin' || $acceso == 'sucursal'){ ?>
								<tr>
									<td><span>Pedido</span></td>
									<?php if($acceso == 'admin' || $usuario == 'suc_CM'){ ?><td><input type=TEXT name='ped_CM'></td><?php } ?>
									<?php if($acceso == 'admin' || $usuario == 'suc_PT05'){ ?><td><input type=TEXT name='ped_PT05'></td><?php } ?>
									<?php if($acceso == 'admin' || $usuario == 'suc_PT177'){ ?><td><input type=TEXT name='ped_PT177'></td><?php } ?>
									<?php if($acceso == 'admin' || $usuario == 'suc_MT'){ ?><td><input type=TEXT name='ped_MT'></td><?php } ?>
								</tr>
							<?php } ?>
						</table>
						<?php if($acceso == 'admin'){ ?>
							<span>Proveedores: </span><input type=TEXT name='proveedores' size='100'> 
							<br>
							<span>Imagen: </span><input type=FILE name='imagen' class='noborder'>
							<br>
						<?php } ?>
						<input type=HIDDEN name='ident' value='0' default>
						<input type=SUBMIT  value='Enviar' name='enviar' class='cmd'>
						<a href='#' onClick='limpiar();return false;' class='cmd , cmd-cancel'>Cancelar</a>
					</form>
					<iframe name='sender' id='Sender' src='enviar.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				</article>
			<?php } ?>
			
			<article id="buscar">
				<form target='searcher' method=GET action='buscar.php' id='Buscador'>
					<span>Buscar: </span><input type=TEXT name='busqueda'>
					<span>En: </span><select name='columna'>
						<option value='nombre'>Nombre</option>
						<option value='precio1'>Precio 1</option>
						<option value='precio2'>Precio 2</option>
						<option value='precio3'>Precio 3</option>
						<option value='precio4'>Precio 4</option>
						<option value='precio5'>Precio 5</option>
						<option value='proveedores'>Proveedores</option>
						<option value='categoria'>Categoria</option>
						<option value='subcategoria'>Subcategoria</option>
						<option value='id'>#ID</option>
					</select>
					<input type=SUBMIT  value='Buscar' class='cmd'>
				</form>
				<iframe name='searcher' src='buscar.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
			</article>
			
			<article id="inventario">
				<form target='invent' method=GET action='inventario.php' id='Inventariador'>
					<span>Filtrar por: </span><input type=TEXT name='busqueda'>
					<span>En: </span><select name='columna'>
						<option value='nombre'>Nombre</option>
						<option value='precio1'>Precio ML</option>
						<option value='categoria'>Categoria</option>
						<option value='subcategoria'>Subcategoria</option>
						<option value='id'>#ID</option>
					</select>
					<span>Sucursal: </span><select name='sucursal'>
						<option value='_CM'>Centro Magno</option>
						<option value='_PT05'>Plaza Tec. 05</option>
						<option value='_PT177'>Plaza Tec. 177</option>
						<option value='_MT'>Macro Tienda</option>
						<option value='_Alm'>Almacen</option>
						<option value='todas'>Todas</option>
					</select>
					<span>Calcular por: </span><select name='columnaP'>
						<option value='precio1'>Precio 1</option>
						<option value='precio2'>Precio 2</option>
						<option value='precio3'>Precio 3</option>
						<option value='precio4'>Precio 4</option>
						<option value='precio5'>Precio 5</option>
					</select>
					<input type=SUBMIT  value='Filtrar' class='cmd'>
				</form>
				<iframe name='invent' src='inventario.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
			</article>
			
		</div>
	</section>

</body>
</html>
<?php }else header("Location: ../"); //redireccionar a esc ?>