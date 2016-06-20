<?php

/*-----------------------
	.blu framework
	Nombre componente: Main Library
	Version: dev
-----------------------*/

function catchValue( $name, $method = "ANY", $default = false ) {
	/*
		Obtiene un valor por diferentes metodos o le asigna un valor por defecto
	*/
	
	// Atrapar en Cualquiera
	if( $method == "ANY" ) {
		if( isset($_POST[$name]) ) return $_POST[$name];
		if( isset($_GET[$name]) ) return $_GET[$name];
	}
	
	// Atrapar en POST
	if( $method == "POST" && isset($_POST[$name]) )
		return $_POST[$name];
	
	// Atrapar en GET
	if( $method == "GET" && isset($_GET[$name]) )
		return $_GET[$name];
	
	return $default;
}

function toggle_order($order) {
	
	/*
		Recibe un valor de ordenación, devuelve el valor contrario
	*/
	if($order == "ASC") return "DESC";
	return "ASC";
	
	/*
	if(isset($_SESSION['orderBy_order'])) {
		if($_SESSION['orderBy_order'] == "DESC") $_SESSION['orderBy_order'] = "ASC";
		else $_SESSION['orderBy_order'] = "DESC";
	}
	else $_SESSION['orderBy_order'] = "DESC";
	*/
}

function analize_state($actual, $nuevo, $def_file = 'default_states.ini') {
	/* 
		Devolver true si el estado nuevo es igual o de menor rango que el actual
		$def_file especifica una ruta relativa al directorio blufr o completa desde cualquier ubicación
	*/
	
	// Buscar y abrir archivo dado o por defecto
	if( !$file = fopen($def_file, "r",1) ) {
		error_log("Component Missing: can't find states definition file.",0);
		return false;
	}
	
	// Crear array de estados
	while( !feof($file) ) {
		$linea = fgets($file);
		
		// limpiar linea
		if( mb_detect_encoding($linea) != 'UTF-8')
			$linea = utf8_encode($linea);
		$linea = trim($linea);
		$linea = preg_replace('/[^\P{C}\n]+/u', '', $linea); //limpieza caracteres invisibles
		$linea = addslashes($linea);
		
		if ( substr($linea,0,2) != "//" && substr($linea,0,1) != " " && $linea != "")
			$estados[] = $linea;
	}
	
	// obtener ambos indicies
	$actual_i = array_search($actual, $estados);
	$nuevo_i = array_search($nuevo, $estados);
	
	// si se dio $nuevo como false y $actual si se encuentra
	if($nuevo === false && $actual_i !== false)
		return $actual_i."/".(count($estados)-1);
	
	// si ambos no existen
	if($actual_i === false && $nuevo_i === false)
		return false;
	
	// Sentenciar
	if($actual_i <= $nuevo_i)
		return true;
	
	return false;
}


function get_time_interval($before, $after, $array = false) {
					
	/*
		Devuelve una cadena con el tiempo transcurrido (en español)
		El tiempo $after, que debe ser más reciente que $before
		Si $array es true, devolverá un array con todos los valores de tiempo
		Debe recibir fechas en UTC
	*/
	
	// Obtener diferencia
	$interval = $after - $before;
	$tiempo['sec'] = $interval%60; // Se recortan sobrantes
	// Pasar a minutos
	$interval = floor($interval/60);
	$tiempo['min'] = $interval%60;
	// Pasar a horas
	$interval = floor($interval/60);
	$tiempo['hou'] = $interval%24;
	// Pasar a días
	$interval = floor($interval/24);
	$tiempo['day'] = $interval%7;
	// Pasar a semanas
	$interval = floor($interval/7);
	$tiempo['wee'] = $interval%4;
	// Pasar a meses
	$interval = floor($interval/4);
	$tiempo['mon'] = $interval%12;
	// Pasar a años
	$tiempo['yea'] = floor($interval/12);
	
	// Devolver array si se solicitó
	if($array)
		return $tiempo;
	
	// ensamblar frase de tiempo	
	if($tiempo['yea'] > 1)
		$frase[] = $tiempo['yea']." años"; // plural
	if($tiempo['yea'] == 1)
		$frase[] = $tiempo['yea']." año"; // singular
	
	if($tiempo['mon'] > 1)
		$frase[] = $tiempo['mon']." meses";
	if($tiempo['mon'] == 1)
		$frase[] = $tiempo['mon']." mes";
	
	if($tiempo['wee'] > 1)
		$frase[] = $tiempo['wee']." semanas";
	if($tiempo['wee'] == 1)
		$frase[] = $tiempo['wee']." semana";
	
	if($tiempo['day'] > 1)
		$frase[] = $tiempo['day']." días";
	if($tiempo['day'] == 1)
		$frase[] = $tiempo['day']." día";
	
	if($tiempo['hou'] > 1)
		$frase[] = $tiempo['hou']." horas";
	if($tiempo['hou'] == 1)
		$frase[] = $tiempo['hou']." hora";
	
	if($tiempo['min'] > 1)
		$frase[] = $tiempo['min']." minutos";
	if($tiempo['min'] == 1)
		$frase[] = $tiempo['min']." minuto";
	
	if($tiempo['sec'] > 1)
		$frase[] = $tiempo['sec']." segundos";
	if($tiempo['sec'] == 1)
		$frase[] = $tiempo['sec']." segundo";
	
	$tiempo = implode(", ",$frase);
	
	return $tiempo;
}


function execute_sql($sql, $conex) {
	/*
		Ejecuta una serie de Operaciones Mysql encadenadas
	*/
	
	while($sql)
		mysql_query(array_shift($sql), $conex) or die(mysql_error());
}

/* --- Controles --- */

function indice($indice, $total_paginas) {
	//Imprimir el Indice
	echo "<div class='index'>";
	if($indice > 0) echo "<a href='?indice=". ($indice - 1) ."'>Anterior</a>";
	else echo "<span class='disable'>Anterior</span>";
	
	if( ($indice - 3) >= 0) echo " <a href='?indice=0'>1</a>...";
	if( ($indice - 2) >= 0) echo " <a href='?indice=". ($indice - 2) ."'>". ($indice - 1) ."</a>";
	if( ($indice - 1) >= 0) echo " <a href='?indice=". ($indice - 1) ."'>". $indice ."</a>";
	echo " <span class='markup'>". ($indice + 1) ."</span>";
	if( ($indice + 1) < $total_paginas) echo " <a href='?indice=". ($indice + 1) ."'>". ($indice + 2) ."</a>";
	if( ($indice + 2) < $total_paginas) echo " <a href='?indice=". ($indice + 2) ."'>". ($indice + 3) ."</a>";
	if( ($indice + 3) < $total_paginas) echo " ...<a href='?indice=". ($total_paginas - 1) ."'>". $total_paginas ."</a>";
	
	if( ($indice + 1) < $total_paginas ) echo " <a href='?indice=". ($indice + 1) ."'>Siguiente</a>";
	else echo " <span class='disable'>Siguiente</span>";
	echo "</div>";
}

?>