<?php

/*-----------------------
	Datos de la Aplicación:
	ID: Gestor de paquetes.
	Version: dev
-----------------------*/

	session_start();
	ini_set('date.timezone','America/Mexico_City'); 
	include('blufr/acces_module.php');
	
	// Control acceso por solicitud
	if(isset($_POST['solicitud_acceso']))
		if(!$errorlog = create_session($_POST['usuario'], $_POST['pass'], isset($_POST['guardar'])))
			unset($errorlog);
	
	// Examinar si existe un acceso vigente o una credencial valida
	if(isset($_SESSION['blu_acceso']))
		$acceso = $_SESSION['blu_acceso'];
	
	// Revisar credencial, si existe
	else if(isset($_COOKIE['blu_session']))
	{
		$errorlog = validate_session($_COOKIE['blu_session']);
		if(!isset($_SESSION['blu_acceso'])) $acceso = false; //garantiza poner acceso en falso, si se ha destruido
	}
	else $acceso = false;
	
	// Registrar tablas autorizadas
	if(!isset($_SESSION['blu_allowed_views']) && $acceso)
		$_SESSION['blu_allowed_views'] = get_allowed_views($acceso);
	
	// Obtener tablas autorizadas
	if(isset($_SESSION['blu_allowed_views']))
		$bitacoras = $_SESSION['blu_allowed_views'];
	
	//Automata de comandos en la url
	if(isset($_GET['cmd'])) {
		switch ($_GET['cmd']) {
			case "logmeout":
				destroy_session();
				break;
			case "forcereset":
				session_unset();
				break;
		} header("Location: ?");
	}
	
?>
<!doctype html>
<html>
<head>
	<title>Control Paquetes</title>
	<meta charset="utf-8"/>
	<link rel="stylesheet" type="text/css" href="blufr/bluUI.css">
	<link rel="stylesheet" type="text/css" href="blufr/estructure.css">
	<style>
		.centrar {
			position: absolute;
			top: 50%; 
			left: 50%;
			transform: translate(-50%, -50%);
		}

		.cuadro {
			width:400px;
			height:200px;
			display:block;
			background-color:#47e1f3;
		}
		
		
	.slide-controls-wrap {
		width:100%;
		min-height:36px;
		background:#47e1f3;
		position:relative;
		}
	
	.slide-controls-wrap form {
		height:0px;
		overflow:hidden;
		opacity:0.0;
		transition:height .3s ease-in-out, opacity .2s;
		}
	
	.slide-controls-wrap .filtro {
		height:auto;
		opacity:1.0;
		}

	.slide-controls-wrap .show-toggle {
		margin:4px; padding:0;
		height:28px;
		width:28px;
		position:absolute;
		bottom:0; right:0;
		}

	.slide-controls-wrap .show-toggle a {
		background:url('sources/mas_menos_slide_small.png') no-repeat center top;
		margin:0; padding:0;
		display:block;
		transition:background-position .4s ease-in;
		overflow:hidden;
		} 
		.slide-controls-wrap .show-toggle a label{
			display:block;
			width:190px;
			position:absolute;
			top:3px; left:0px;
			color:transparent;
			overflow:hidden;
			transition:color .2s, left .3s;
			}
			.slide-controls-wrap .show-toggle a:hover label {
				color:#FFF;
				left:-190px;
			}

			
	#sessionForm {
		width:375px;
		margin:20% auto 0 auto;
		background:#ff4070;
		color:#ffb3c6;
		}
		#sessionForm h1 {
			color:#91D6F7;
			background:#323334;
			text-align:center;
			line-height:50px;
		}
		#sessionForm form {
			padding-bottom:10px;
		}
		#sessionForm th {
			color:#fff;
		}
		#sessionForm .alert{
			margin:0;
		}

	#blufrLbl {
		color:#727272;
		font-size:x-small;
		position:absolute;
		bottom:8px;
		right:6px;
		}		
	
	</style>
	<script type='text/javascript' src='blufr/estructure.js'></script>
	<script>
		function showForm(button) {
			
			forms = button.parentNode.parentNode.getElementsByTagName('form');
			
			for(i = 0; i < forms.length; i++){
		
				if(forms[i].offsetHeight == 0) {
					
					//calcular alto del formulario
					var alto_form = 0;
					tables = forms[i].getElementsByTagName('table');
					for(j = 0; j < tables.length; j++)
						alto_form += tables[j].offsetHeight;
					
					forms[i].style.height = (alto_form + 'px');
					forms[i].style.opacity = '1.0';
					
					if(i == 0) {
						button.style.backgroundPosition = "center -28px";
						setTimeout( function(){
							etiqueta = button.getElementsByTagName('label')[0];
							etiqueta.innerHTML = "Ocultar Formulario"
							//etiqueta.style.left = ('-' + etiqueta.offsetWidth + 'px');
							}, 300 );
						
					}
					
				} else {
					forms[i].style.height = '0px';
					forms[i].style.opacity = '0.0';
					
					if(i == 0) {
						button.style.backgroundPosition = "center top";
						setTimeout( function(){
							etiqueta = button.getElementsByTagName('label')[0];
							etiqueta.innerHTML = "Añadir Nuevo Registro"
							//etiqueta.style.left = ('-' + etiqueta.offsetWidth + 'px');
							}, 300 );
					}
					
				}
				
				/*
				
					Problemas conocidos
					-la etiqueta de información no se ajusta
				
				*/
				
			}
		}
		
		function add_markup(item) {
			list = item.parentNode.getElementsByTagName("li");
			for(var i=0; i < list.length; i++)
				list[i].className = '';
			item.className = 'markup';
		}			
	</script>
</head>

<body onLoad='workarea();' onResize='workarea();'>

<?php if($acceso){ ?>

	<nav id='bluFrMenu'>
		<div id='firstLvl'>
			
			<span id='appID'>Control de Paquetes <i>[v1.0x]</i></span>
			
			<span id='fullname'>Bienvenido <?php echo utf8_encode($_SESSION['blu_usuario']); ?></span>
			<div id='config'><a href='?cmd=logmeout'><img src='sources/config.png'></a></div>
		
		</div>
		<div id='secondLvl'>
			<ul>
				<li class='markup'><a href='#' onClick='change(0); add_markup(this.parentNode); return false;'>Busqueda</a></li>    
				<?php 
					// Aguja para apuntar el slider
					$needle = 1;
					
					if( in_array("cotejados", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle ."); add_markup(this.parentNode); return false;'>Cotejados</a></li>";
						$needle++;
					}
					if( in_array("impresos", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle .");  add_markup(this.parentNode); return false;'>Impresos</a></li>";
						$needle++;
					}
					if( in_array("separados", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle ."); add_markup(this.parentNode); return false;'>Separados</a></li>";
						$needle++;
					}
					if( in_array("revision", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle ."); add_markup(this.parentNode); return false;'>Revisión</a></li>";
						$needle++;
					}
					if( in_array("cerrado", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle ."); add_markup(this.parentNode); return false;'>Cerrado</a></li>";
						$needle++;
					}
					if( in_array("recoleccion", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle ."); add_markup(this.parentNode); return false;'>Recolección</a></li>";
						$needle++;
					}
					if( in_array("compras", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle ."); add_markup(this.parentNode); return false;'>Compras</a></li>";
						$needle++;
					}
					if( in_array("cuentas", $bitacoras) ) {
						echo "<li><a href='#' onClick='change(". $needle ."); add_markup(this.parentNode); return false;'>Cuenta Paquetes</a></li>";
						$needle++;
					}
				?>
			</ul>
		</div>
	</nav>

	<section id="mainBody">
		<div id="mainBody-grid">
			
			<article id="buscar">
				<iframe name='buscador' src='components/buscador.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
			</article>
			
			<?php if( in_array("cotejados", $bitacoras) ) { ?>
			<article id="cotejados">
				
				<div class='slide-controls-wrap'>
					<form target='cotejados' method=POST action='components/cotejados.php' id='cotejados-form'>
						<table>
							<tr>
								<th>
									<span>No. Pedido: </span>
								</th>
								<td>
									<input type=TEXT name='pedido'>
								</td>
								<th>
									<span>Asunto: </span>
								</th>
								<td>
									<select name='asunto'>
										<option value='Pago Validado'>Pago Validado</option>
										<option value='Saldo Pendiente'>Saldo Pendiente</option>
										<option value='Saldo a Favor'>Saldo a Favor</option>
									</select>
								</td>
								<td></td>
							</tr><tr>
								<th>
									<span>Correo del Cliente: </span>
								</th>
								<td>
									<input type=TEXT name='correo'>
								</td>
								<th>
									<span>Comentario: </span>
								</th>
								<td>
									<textarea name='comentario'></textarea>
								</td>
								<th>
									<input type=SUBMIT  value='Agregar' class='cmd'>
								</th>
							</tr>
						</table>
						
						<input type=HIDDEN name='ident' value='REGISTER'>
						
					</form>
					
					<form target='cotejados' method=GET action='components/cotejados.php' class='filtro'>
						<table>
							<tr>
								<td><input type=TEXT name='filtro' size='30'></td>
								<td><input type=SUBMIT value='Filtrar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
					<div class='show-toggle'>
						<a href='#' onClick='showForm(this); return false;'>
							<label>Añadir Nuevo registro</label>
							<img src='sources/circle_button_small.png'>
						</a>
					</div>
				</div>
				
				
				<iframe name='cotejados' src='components/cotejados.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
				
			</article>
			<?php } ?>
			
			<?php if( in_array("impresos", $bitacoras) ) { ?>
			<article id="impresos">
			
				<div class='slide-controls-wrap'>
					<form target='impresos' method=POST action='components/impresos.php' id='impresos-form'>
						<table>
							<tr>
								<th>
									<span>No. Pedido: </span>
								</th>
								<td>
									<input type=TEXT name='pedido'>
								</td>
								<th>
									<span>Asunto: </span>
								</th>
								<td>
									<select name='asunto'>
										<option value='Guía Asignada (DHL)'>Guía Asignada (DHL)</option>
										<option value='Guía Asignada (ESTAFETA)'>Guía Asignada (ESTAFETA)</option>
										<option value='Guía Pendiente'>Guía Pendiente</option>
										<option value='Cambio de Guía (DHL)'>Cambio de Guía (DHL)</option>
										<option value='Cambio de Guía (ESTAFETA)'>Cambio de Guía (ESTAFETA)</option>
									</select>
								</td>
								<td></td>
							</tr><tr>
								<th>
									<span>Guia: </span>
								</th>
								<td>
									<input type=TEXT name='guia'>
								</td>
								<th>
									<span>Comentario: </span>
								</th>
								<td>
									<textarea name='comentario'></textarea>
								</td>
								<th>
									<input type=SUBMIT  value='Agregar' class='cmd'>
								</th>
							</tr>
						</table>
						
						<input type=HIDDEN name='ident' value='REGISTER'>
						
					</form>
					
					<form target='impresos' method=GET action='components/impresos.php' class='filtro'>
						<table>
							<tr>
								<td><input type=TEXT name='filtro' size='30'></td>
								<td><input type=SUBMIT value='Filtrar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
					<div class='show-toggle'>
						<a href='#' onClick='showForm(this); return false;'>
							<label>Añadir Nuevo registro</label>
							<img src='sources/circle_button_small.png'>
						</a>
					</div>
				</div>
				
				
				<iframe name='impresos' src='components/impresos.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
			</article>
			<?php } ?>
			
			<?php if( in_array("separados", $bitacoras) ) { ?>
			<article id="separados">
			
				<div class='slide-controls-wrap'>
					<form target='separados' method=POST action='components/separados.php' id='separados-form'>
						<table>
							<tr>
								<th>
									<span>No. Pedido: </span>
								</th>
								<td>
									<input type=TEXT name='pedido'>
								</td>
								<th>
									<span>Asunto: </span>
								</th>
								<td>
									<select name='asunto'>
										<option value='Artículos Separados'>Artículos Separados</option>
										<option value='Artículos Faltantes'>Artículos Faltantes</option>
									</select>
								</td>
								<td></td>
							</tr><tr>
								<th>
									<span>Comentario: </span>
								</th>
								<td colspan=3>
									<textarea name='comentario' cols='50'></textarea>
								</td>
								<th>
									<input type=SUBMIT  value='Agregar' class='cmd'>
								</th>
							</tr>
						</table>
						
						<input type=HIDDEN name='ident' value='REGISTER'>
						
					</form>
					
					<form target='separados' method=GET action='components/separados.php' class='filtro'>
						<table>
							<tr>
								<td><input type=TEXT name='filtro' size='30'></td>
								<td><input type=SUBMIT value='Filtrar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
					<div class='show-toggle'>
						<a href='#' onClick='showForm(this); return false;'>
							<label>Añadir Nuevo registro</label>
							<img src='sources/circle_button_small.png'>
						</a>
					</div>
				</div>
				
				
				<iframe name='separados' src='components/separados.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
			</article>
			<?php } ?>
			
			<?php if( in_array("revision", $bitacoras) ) { ?>
			<article id="revision">
			
				<div class='slide-controls-wrap'>
					<form target='revision' method=POST action='components/revision.php' id='revision-form'>
						<table>
							<tr>
								<th>
									<span>No. Pedido: </span>
								</th>
								<td>
									<input type=TEXT name='pedido'>
								</td>
								<th>
									<span>Asunto: </span>
								</th>
								<td>
									<select name='asunto'>
										<option value='Paquete Revisado'>Paquete Revisado</option>
										<option value='Paquete Incompleto'>Paquete Incompleto</option>
									</select>
								</td>
								<td></td>
							</tr><tr>
								<th>
									<span>Comentario: </span>
								</th>
								<td colspan=3>
									<textarea name='comentario' cols='50'></textarea>
								</td>
								<th>
									<input type=SUBMIT  value='Agregar' class='cmd'>
								</th>
							</tr>
						</table>
						
						<input type=HIDDEN name='ident' value='REGISTER'>
						
					</form>
					
					<form target='revision' method=GET action='components/revision.php' class='filtro'>
						<table>
							<tr>
								<td><input type=TEXT name='filtro' size='30'></td>
								<td><input type=SUBMIT value='Filtrar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
					<div class='show-toggle'>
						<a href='#' onClick='showForm(this); return false;'>
							<label>Añadir Nuevo registro</label>
							<img src='sources/circle_button_small.png'>
						</a>
					</div>
				</div>
				
				
				<iframe name='revision' src='components/revision.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
			</article>
			<?php } ?>
			
			<?php if( in_array("cerrado", $bitacoras) ) { ?>
			<article id="cerrado">
			
				<div class='slide-controls-wrap'>
					<form target='cerrado' method=POST action='components/cerrado.php' id='cerrado-form'>
						<table>
							<tr>
								<th>
									<span>No. Pedido: </span>
								</th>
								<td>
									<input type=TEXT name='pedido'>
								</td>
								<th>
									<span>Asunto: </span>
								</th>
								<td>
									<select name='asunto'>
										<option value='Paquete Cerrado'>Paquete Cerrado</option>
									</select>
								</td>
								<td></td>
							</tr><tr>
								<th>
									<span>Comentario: </span>
								</th>
								<td colspan=3>
									<textarea name='comentario' cols='50'></textarea>
								</td>
								<th>
									<input type=SUBMIT  value='Agregar' class='cmd'>
								</th>
							</tr>
						</table>
						
						<input type=HIDDEN name='ident' value='REGISTER'>
						
					</form>
					
					<form target='cerrado' method=GET action='components/cerrado.php' class='filtro'>
						<table>
							<tr>
								<td><input type=TEXT name='filtro' size='30'></td>
								<td><input type=SUBMIT value='Filtrar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
					<div class='show-toggle'>
						<a href='#' onClick='showForm(this); return false;'>
							<label>Añadir Nuevo registro</label>
							<img src='sources/circle_button_small.png'>
						</a>
					</div>
				</div>
				
				
				<iframe name='cerrado' src='components/cerrado.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
			</article>
			<?php } ?>
			
			<?php if( in_array("recoleccion", $bitacoras) ) { ?>
			<article id="recoleccion">
			
				<div class='slide-controls-wrap'>
					<form target='recoleccion' method=POST action='components/recoleccion.php' id='recoleccion-form'>
						<table>
							<tr>
								<th>
									<span>No. Pedido: </span>
								</th>
								<td>
									<input type=TEXT name='pedido'>
								</td>
								<th>
									<span>Asunto: </span>
								</th>
								<td>
									<select name='asunto'>
										<option value='Paquete Recogido'>Paquete Recogido</option>
										<option value='No coincide Guía'>No coincide Guía</option>
									</select>
								</td>
								<td></td>
							</tr><tr>
								<th>
									<span>Comentario: </span>
								</th>
								<td colspan=3>
									<textarea name='comentario' cols='50'></textarea>
								</td>
								<th>
									<input type=SUBMIT  value='Agregar' class='cmd'>
								</th>
							</tr>
						</table>
						
						<input type=HIDDEN name='ident' value='REGISTER'>
						
					</form>
					
					<form target='recoleccion' method=GET action='components/recoleccion.php' class='filtro'>
						<table>
							<tr>
								<td><input type=TEXT name='filtro' size='30'></td>
								<td><input type=SUBMIT value='Filtrar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
					<div class='show-toggle'>
						<a href='#' onClick='showForm(this); return false;'>
							<label>Añadir Nuevo registro</label>
							<img src='sources/circle_button_small.png'>
						</a>
					</div>
				</div>
				
				
				<iframe name='recoleccion' src='components/recoleccion.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
			</article>
			<?php } ?>
			
			<?php if( in_array("compras", $bitacoras) ) { ?>
			<article id="compras">
			
				<div class='slide-controls-wrap'>
					
					<form target='compras' method=GET action='components/compras.php' class='filtro'>
						<table>
							<tr>
								<td><input type=TEXT name='filtro' size='30'></td>
								<td><input type=SUBMIT value='Filtrar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
				</div>
				
				
				<iframe name='compras' src='components/compras.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
			</article>
			<?php } ?>
			
			<?php if( in_array("cuentas", $bitacoras) ) { ?>
			<article id="cuentas">
			
				<div class='slide-controls-wrap'>
					
					<form target='cuentas' method=GET action='components/cuentas_cerrado.php' autocomplete='off' class='filtro'>
						<table>
							<tr>
								<th><span>Entre: </span></th>
								<td><input type=TEXT list='old' name='fmin' size='20'></td>
								<datalist id='old'>
									<?php
										echo "<option value='". date("Y-m-d", time() - 691200) ."'>". date("Y-m-d", time() - 691200) ." (Hace 8 días)</option>";
									?>
								</datalist>
								<th><span>y: </span></th>
								<td><input type=TEXT list='now' name='fmax' size='20'></td>
								<datalist id='now'>
									<?php
										echo "<option value='". date("Y-m-d") ."'>". date("Y-m-d") ." (Hoy)</option>";
									?>
								</datalist>
								<th><span>Filtro: </span></th>
								<td><input type=TEXT name='filtro' size='20'></td>
								<td><input type=SUBMIT value='Buscar' class='cmd'></td>
							</tr>
						</table>
					</form>
					
				</div>
				
				
				<iframe name='cuentas' src='components/cuentas_cerrado.php' frameborder=0 width=100% height=400><p>iframes no soportados. :(</p></iframe>
				
			</article>
			<?php } ?>
			
		</div>
	</section>

<?php } else { ?>

	<section id='sessionForm'>
		<div id='logo'>
			<h1>Control Paquetes</h1>
		</div>
		<div id='main'>
			<!-- Formulario de Acceso -->
			<form method=POST>
				<table>
				<tr>
					<th><span>Usuario:</span></th>
					<td><input type=TEXT name='usuario'></td>
				</tr>
				<tr>
					<th><span>Contraseña:</span></th>
					<td><input type=PASSWORD name='pass'></td>
				</tr>
				<tr>
					<td><span>Recordar Sesión</span><input type=CHECKBOX name='guardar'></td>
					<td><input type=SUBMIT  name='solicitud_acceso' value='Ingresar' class='cmd'></td>
				</tr>
				</table>
			</form>
			<?php if(isset($errorlog)) echo "<div class='alert'><p>".$errorlog."</p></div>"; ?>
		</div>
	</section>
	
	<div id='blufrLbl'>
		<span>[v1.x]</span>
		<img src='sources/lg_blufr_tag.png'>
	</div>

<?php } ?>

</body>
</html>