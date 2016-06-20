<!doctype html>
<HTML>
<head>
	<title>convierte - traduce</title>
	<meta charset="utf-8" />
</head>
<body>
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

//Automata de comandos en la url
	if(isset($_GET['convierte'])) {
		echo "<p>".noi_code($_GET['convierte'])."</p>";		
	} 
	
	else if(isset($_GET['traduce'])) {
		echo "<p>".noi_decode($_GET['traduce'])."</p>";		
	}
	
	else {
		echo "<p>Dime que hacer...</p>";
	}
	
?>
</body>
</html>