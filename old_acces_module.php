<?php

function noi_code($string){
	$noi = "";
	$charmap = array(0=>"i",1=>"o",2=>"n",3=>".",4=>" ",5=>"c");
	for($i=0; $i<strlen($string); $i++) {
		$num = ord(substr($string, $i, 1));
		$x=0; $cero=true;
		while($num){
			$res = $num%3;
			while($res){
				$noi.= $charmap[$x];
				$res--;
				$cero=false;
			}
			if($cero && $x==2) $noi.= $charmap[5];
			$x++;
			if($x>2){
				$noi.= $charmap[$x];
				$x=0;
			}
			$num = floor($num/3);
		} if($i+1 <strlen($string)) $noi.= $charmap[4];
	} return strrev($noi);
}

function noi_decode($noi){
	$string = ""; $int = 0;
	$charmap = array("i"=>1,"o"=>3,"n"=>9,"c"=>0);
	for($i=0; $i<strlen($noi); $i++) {
		$char = substr($noi, $i, 1);
		switch($char){
			case " ":
				$string.=chr($int);
				$int=0;
				break;
			case ".":
				$int=$int*27;
				break;
			case "i":
			case "o":
			case "n":
				$int+=$charmap[$char];
				break;
			default:
				return "Not Coded string given";
		}
	} $string.=chr($int);
	return strrev($string);
}

function encrypt($string) {
   $result = '';
   $key = $_SERVER['REMOTE_ADDR'];
   for($i=0; $i<strlen($string); $i++) {
      $char = substr($string, $i, 1);
      $keychar = substr($key, ($i % strlen($key))-1, 1);
	  $char = chr(ord($char)+ord($keychar));
      $result.=$char;
   }
   //return base64_encode($result);
   //return $result;
   return noi_code($result);
}

function decrypt($string) {
   $result = '';
   $key = $_SERVER['REMOTE_ADDR'];
   //$string = base64_decode($string);
   $string = noi_decode($string);
   for($i=0; $i<strlen($string); $i++) {
      $char = substr($string, $i, 1);
      $keychar = substr($key, ($i % strlen($key))-1, 1);
      $char = chr(ord($char)-ord($keychar));
      $result.=$char;
   }
   return $result;
}

function create_session($usuario,$contrasenia,$recordar){
	require_once('conex.php');
	
	//comprobar datos
	if(!$usuario && !$contrasenia) return "Ingrese datos de Acceso";
	else if(!$contrasenia) return "Ingrese una contraseña";
	else if(!$usuario) return "Especifíque un usuario";

	//validar datos
	$sql="SELECT * FROM usuarios WHERE usuarios.usuario LIKE '$usuario'";
	$consult = mysql_query($sql, $conex) or die(mysql_error());
	if(mysql_num_rows($consult)!=0){
		$row = mysql_fetch_assoc($consult);
		if($row['contrasenia'] === $contrasenia) {
			$_SESSION['Apps_acceso'] = $row['tipo'];
			//Le movi 
			$_SESSION['Apps_usuario']= $row['usuario'];
			$_SESSION['Apps_nombre']= $row['nombre'];
			//hasta aqui
			if($recordar){
				setcookie('Apps_session[sc]', encrypt($row['contrasenia']), time()+60*60*24*30);
				setcookie('Apps_session[un]', $row['usuario'], time()+60*60*24*30);
				setcookie('Apps_session[fn]', $row['nombre'], time()+60*60*24*30);
			}
		}
		else return "Contraseña Incorrecta";
	} else return "Nombre de Usuario Incorrecto";
	
	//salida para errorlog
	return false;
}

function validate_session($session){
	require_once('conex.php');
	
	//obtener datos
	$usuario = $session['un'];
	$contrasenia = decrypt($session['sc']);
	
	$sql="SELECT * FROM usuarios WHERE usuarios.usuario LIKE '$usuario'";
	$consult = mysql_query($sql, $conex) or die(mysql_error());
	if(mysql_num_rows($consult)!=0){
		$row = mysql_fetch_assoc($consult);
		if($row['contrasenia'] === $contrasenia){
			$_SESSION['Apps_acceso'] = $row['tipo'];
			return true;
		}
	} return false;
}

function destroy_session($session){
	//Destruir todas las variables vinculadas a la credencial y al acceso temporal, devolver false para usar valor
	
	$codigo = $session['sc'];
	$usuario = $session['un'];
	$nombre = $session['fn'];

	setcookie('Apps_session[sc]', $codigo, time()-60*60*24*30);
	setcookie('Apps_session[un]', $usuario, time()-60*60*24*30);
	setcookie('Apps_session[fn]', $nombre, time()-60*60*24*30);
	unset($_SESSION['Apps_acceso']);
	return false;
}

?>