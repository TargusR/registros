<?php 

/*-----------------------
	.blu framework
	Nombre componente: Thumbnlr
	Version: dev
-----------------------*/


//obtener parametros
if(isset($_GET['src']))
	if(file_exists($_GET['src']))
		$src = $_GET['src'];
	else $src = "../sources/noimage.jpg";
else $src = "../sources/noimage.jpg";

if(isset($_GET['w'])) $w = $_GET['w'];
else $w = "auto";

if(isset($_GET['h'])) $h = $_GET['h'];
else $h = "auto";

if(isset($_GET['s'])) $s = $_GET['s'];
else $s = false;

//extraer extensión de src
$type = explode("/", $src);
$type = $type[count($type) - 1];
$type = explode(".", $type);
$type = $type[count($type) - 1];
$type = strtolower($type);

//determinar ancho o alto maximo si existe s
if($s) {
	
	list($ancho, $alto) = getimagesize($src);
	
	if($ancho > $alto) {
		//la imagen es mas ancha que alta
		if($s < $ancho) {
			$w = $s;
			$h = $alto * ($s / $ancho); //formula para redefinir tamaño a escala
		} else {
			$w = $ancho;
			$h = $alto;
		}
	
	} else {
		//la imagen es mas alta que ancha
		if($s < $alto) {
			$h = $s;
			$w = $ancho * ($s / $alto);
		} else {
			$w = $ancho;
			$h = $alto;
		}
	}

}


//función que recostruye la imagen al tamaño dado
function rebuild_picture($original, $ancho, $alto) {
	
	//redefinir tamaños automaticos a escala
	if($ancho == "auto") {
		if($alto == "auto"){
			//ambas propiedades estan en auto
			$ancho = imagesx($original);
			$alto = imagesy($original);
		} else {
			//solo el ancho está en auto
			$ancho = imagesx($original) * ($alto / imagesy($original));
		}
	}
	else if($alto == "auto") {
		//solo el alto está en auto
		$alto = imagesy($original) * ($ancho / imagesx($original));
	}
	
	//definir el lienzo nuevo
	$imagen = Imagecreatetruecolor($ancho, $alto);
	
	//reconstruir nueva imagen
	imagecopyresized($imagen, $original, 0, 0, 0, 0,$ancho, $alto, imagesx($original), imagesy($original));
	
	return $imagen;
}

//Lanzar Header e imprimir imagen segun su tipo
switch($type){
	
	case "jpg":
		Header("Content-type: image/jpg");
		imagejpeg(rebuild_picture(imagecreatefromjpeg($src), $w, $h));
		break;
	
	case "jpeg":
		Header("Content-type: image/jpeg");
		imagejpeg(rebuild_picture(imagecreatefromjpeg($src), $w, $h));
		break;
	
	case "gif":
		Header("Content-type: image/gif");
		imagegif(rebuild_picture(imagecreatefromgif($src), $w, $h));
		break;
	
	case "png":
		Header("Content-type: image/png");
		imagegif(rebuild_picture(imagecreatefrompng($src), $w, $h));
		break;
	
	default:
		//crear imagen por defecto
		Header("Content-type: image/jpg");
		imagejpeg(imagecreatefromjpeg("../sources/noimage.jpg"));
		break;
}

?>