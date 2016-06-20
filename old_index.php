<?php 

/* Datos de la Aplicación:
	ID: Escritorio Apps.
	Version: 2.0
	Codificado y diseñado por Estudio Veintitres 
*/
	session_start();
	include('acces_module.php');
	
	//Control acceso por solicitud
	if(isset($_POST['solicitud_acceso']))
		if(!$errorlog = create_session($_POST['usuario'], $_POST['contrasenia'], isset($_POST['guardar'])))
			unset($errorlog);
	
	//Examinar si existe un acceso vigente o una credencial valida
	if(isset($_SESSION['blu_acceso']))
		$acceso = $_SESSION['blu_acceso'];
	
	//Revisar credencial, si existe
	else if(isset($_COOKIE['blu_session']))
		$errorlog = validate_session($_COOKIE['blu_session'])
		if(!isset($_SESSION['blu_acceso'])) $acceso = false; //garantiza poner acceso en falso, si se ha destruido

	else $acceso = false;
	
	
		
	/* Añadido identificadores de Datos de Usuario*/
	
	if(isset($_SESSION['Apps_usuario'])) $usuario = $_SESSION['Apps_usuario'];
	else $usuario = false;
	
	if(isset($_SESSION['Apps_nombre'])) $nombre = utf8_encode($_SESSION['Apps_nombre']);
	else $nombre = false;
	
	//Automata de comandos en la url
	if(isset($_GET['cmd'])) {
		switch ($_GET['cmd']) {
			case "logmeout":
				destroy_session();
				break;
		} header("Location: ?");
	}
	
?>
<!doctype html>
<HTML>
<head>
	<title>Bienvenido <?php echo $nombre ?></title>
	<meta charset="utf-8" />
	<style>
body{
	margin:0; padding:0;
	font-family:Verdana, Geneva, sans-serif;
	<?php if($acceso) echo "background:#EAEAEA url('sources/fondo_gris.jpg') center center;"; else echo "background:#EAEAEA url('sources/fondo_blanco.jpg') center center;"; ?>
}
a, a:visited {
	border:0;
	text-decoration:none;
	color:#e04243;
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
	#smartMenu span{
		display:inline-block;
		line-height:50px;
		list-style-type:none;
		padding:0 20px;
		margin:0;
		text-indent:0;
		}
	#appID {
		color:#f8cbcc;
		font-size:3.6em;
		letter-spacing:.02em;
		overflow:hidden;
		}
	#fullname {
		position:absolute;
		right:30px;
		}
		#fullname span { padding:0; color:#91D6F7; }
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
		
/* Panel Apps */	
#mainBody {
	margin:0 12px;
	overflow:hidden;
	position:relative;
	background:#b3b3b3;
	background:rgba(179,179,179,.6);
	}
    .app:first-child { margin:28px 30px 2px 30px; }
    .app { margin:2px 30px 2px 30px; }
	.app h2, .app a {
	    display:inline-block;
	    font-size:2em;
	    margin:2px 0;
	    padding:10px 18px;
	    background:#b3b3b3;
	    color:#FFF;
	    }
	    .app h2 {
	       font-weight:normal;
	       text-transform:uppercase;
	       font-style:italic;
	       }
	    .app a{
	       background:#e04243;
	       transition:background 1s;
	       } .app a:hover { background:#e76969; }
		
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
				
	/* Notificaciones */
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

/* Formulario de Acceso  */
#sessionForm {
	margin:0px auto;
	margin-top:20%;
	padding:0;
	text-align:center;
	background:#b3b3b3;
	background:rgba(179,179,179,.6);
	}
	#logo {
		display:inline-block;
		text-align:right;
		margin-right:10px;
		color:#FFF;
		}
		#logo h1 { font-size:3em; line-height:.1em; }
		#logo h1 span { color:#91D6F7; }
		#logo h2 { font-size:1.2em; color:#b3b3b3; }
	#main {
		display:inline-block;
		text-align:left;
		border-left:1px dotted #FFF;
		}
		
	</style>
	<script type='text/javascript'>
		var unidadAncho = window.innerWidth - 24;
		var unidadAlto = window.innerHeight - 88;
		function workarea(){
			cuadros = document.getElementById("mainBody");
			if(cuadros) cuadros.style.height = unidadAlto + "px";
			cuadros = document.getElementById("sessionForm");
			if(cuadros) cuadros.style.marginTop = ((window.innerHeight - cuadros.offsetHeight)/2) + "px";
		}
	</script>
</head>
<body onLoad='workarea();' onResize='workarea();'>
	
	<?php if($acceso){ ?>
	<nav id='smartMenu'>
		<span id='appID'>Apps Ventronic</span>
		<span id='fullname'>Bienvenido <span><?php echo utf8_encode($_SESSION['Apps_nombre']);?></span></span>
		<div id='config'><a href='?cmd=logmeout'><img src='sources/config.png'></a></div>
	</nav>
	
	<section id='mainBody'>
			<article class='app' id='app_catalogo'>
				<h2>Catálogo</h2>
				<?php if($acceso == 'admin' || $acceso == 'sucursal' || $acceso == 'almacen'){ ?> <a href='catalogo'>Registrar Productos</a> <?php } ?>
				<a href='catalogo'>Buscar Productos</a>
				<a href='catalogo'>Consultar Inventario</a>
			</article>
			<?php if($acceso == 'admin'){ ?>
				<article class='app' id='app_Bodega'>
					<h2>Bodega</h2>
					<a href='bodega'>Inventario</a>
				</article>
			<?php } ?>
			<article class='app' id='app_garantias'>
				<h2>Garantias</h2>
				<a href='garantias'>Administrar</a>
			</article>
			<article class='app' id='app_guias'>
				<h2>Guias</h2>
				<a href='guias'>Guias</a>
			</article>
			<article class='app' id='app_cotizador'>
				<h2>Cotizador</h2>
				<a href='cotizador'>Cotizador</a>
			</article>
			<article class='app' id='app_etiquetas'>
				<h2>Etiquetas</h2>
				<a href='etiquetas'>Etiquetas</a>
			</article>
	</section>
	
	<?php } else { ?>
	<section id='sessionForm'>
		<div id='logo'>
			<h1>App<span>s</span></h1>
			<h2>[para Ventronic]</h2>
		</div>
		<div id='main'>
			<!-- Formulario de Acceso -->
			<form method=POST>
				<span>Usuario: </span><input type=TEXT name='usuario'><br>
				<span>Contraseña: </span><input type=PASSWORD name='contrasenia'><br>
				<span>Recordar Sesión</span><input type=CHECKBOX name='guardar'>
				<input type=SUBMIT  name='solicitud_acceso' value='Ingresar' class='cmd'>
			</form>
			<?php if(isset($errorlog)) echo "<div class='alert'><p>".$errorlog."</p></div>"; ?>
		</div>
	</section>
	<?php } ?>
	
</body>
</HTML>

