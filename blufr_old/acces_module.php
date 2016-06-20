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

function create_session($usuario,$pass,$recordar){
	require_once('conex.php');
	
	//comprobar datos
	if(!$usuario && !$pass) return "Ingrese datos de Acceso";
	else if(!$pass) return "Ingrese una contraseña";
	else if(!$usuario) return "Especifíque un usuario";

	//validar datos
	$sql="SELECT * FROM usuarios WHERE usuarios.usuario LIKE '$usuario'";
	$consult = mysql_query($sql, $conex) or die(mysql_error());
	if(mysql_num_rows($consult) != 0){
		$row = mysql_fetch_assoc($consult);
		if($row['pass'] === $pass)
		{
			$_SESSION['blu_usuario']= $row['usuario'];
			$_SESSION['blu_acceso'] = $row['departamento'];
			
			
			//hasta aqui
			if($recordar){
				setcookie('blu_session[sc]', encrypt($row['pass']), time()+60*60*24*30);
				setcookie('blu_session[un]', $row['usuario'], time()+60*60*24*30);
				setcookie('blu_session[ac]', $row['departamento'], time()+60*60*24*30);
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
	$pass = decrypt($session['sc']);
	
	$sql="SELECT * FROM usuarios WHERE usuarios.usuario LIKE '$usuario'";
	$consult = mysql_query($sql, $conex) or die(mysql_error());
	if(mysql_num_rows($consult) != 0)
	{
		$row = mysql_fetch_assoc($consult);
		if($row['pass'] === $pass)
		{
			$_SESSION['blu_usuario']= $row['usuario'];
			$_SESSION['blu_acceso'] = $row['departamento'];
			
			return true;
			
		}
		else
			destroy_session($session);		
	}
	
	return "Error al validar acceso, inicia sessión de nuevo";
}

function destroy_session($session){
	//Destruir todas las variables vinculadas a la credencial y al acceso temporal, devolver false para usar valor
	
	$codigo = $session['sc'];
	$usuario = $session['un'];
	$departamento = $session['ac'];

	setcookie('blu_session[sc]', $codigo, time()-60*60*24*30);
	setcookie('blu_session[un]', $usuario, time()-60*60*24*30);
	setcookie('blu_session[ac]', $departamento, time()-60*60*24*30);
	unset($_SESSION['blu_acceso']);
	
	return false;
}

?>