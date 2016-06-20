<?php
	session_start();
	
	function puntos_cm ($medida, $resolucion=72){
	   //// 2.54 cm / pulgada
	   return ($medida/(2.54))*$resolucion;
	} //conversor de cm
	
	//declaración de librería e inicialización de documento
	include ('class.ezpdf.php');
	$pdf =& new Cezpdf('LETTER');
	$pdf->selectFont('./fonts/Helvetica.afm');
	
	//firma del documento
	$firma = array (
		'Title'=>'Cotización',
		'Author'=>'Cotizador 1.1.4',
		'Subject'=>'Cotización para Usuario',
		'Creator'=>'ventronic@hotmail.com',
		);
	$pdf->addInfo($firma);
	
	//encabezado
	function ventronic_logo(&$pdf){
		$pdf->saveState();
		$height = 160;
		$x = 0;
		$y = puntos_cm(27.94)-$height;
		
		$pdf->setColor(0.08,0.66,0.93);
		$pdf->filledRectangle($x,$y,puntos_cm(21.59),$height);
		$pdf->addJpegFromFile('logoventronic.jpg',$x+puntos_cm(21.59)-320,$y+30,300);
		
		$pdf->setColor(1,1,1);
		$pdf->addText($x+puntos_cm(21.59)-300,$y+15,14,'"Caminando de la mano con la tecnología"');
		
		$pdf->ezSetY($y+$height-30);
		$pdf->ezText('Uriel Velázquez Rivera',14);
		$pdf->ezText('Calle Colón 209 interior 207, Colonia Centro',10);
		$pdf->ezText('Guadalajara Jalisco, CP 44100',10);
		$pdf->ezText('.::Telefonos::.',10);
		$pdf->ezText('(0133) 36-17-46-62',10);
		$pdf->ezText('(0133) 36-17-54-92',10);
		$pdf->ezText('ventronic@hotmail.com',12);
		$pdf->ezText('RFC: VERU871230VE8',12);
		
		$pdf->restoreState();
		return $y;
	}
	$pdf->ezSetY(ventronic_logo($pdf)-20);
	
	$pdf->addText(puntos_cm(21.59)-190,puntos_cm(27.94)-190,14,"Gdl, Jalisco, ".date("d-m-Y"));
		
	$pdf->saveState();
	$pdf->setColor(0.99,0.31,0.29);
	$pdf->ezText('Cotización:',30);
	$pdf->restoreState();

	
	//Datos de cliente
	/*if($_POST['usuario']){$pdf->ezText('A nombre de: '.$_POST['usuario'],16);}
	if($_POST['compañia']){$pdf->ezText('Para: '.$_POST['compañia'],16);}*/
		
	//asignación de datos de cotización
	$sql = $_SESSION['busqueda'];
	$consult = mysql_query($sql, $conex) or die(mysql_error());
	
	$datos = $_SESSION['ventcot'];
	
	//resetear contadores
	reset($datos); 
	$total=0;
	$i=1;
	do{
		$actual = current($datos);
		$final[$i]['No']=$i;
		$final[$i]['Cantidad']=$actual['cantidad'];
		$final[$i]['Descripción']=$actual['nombre'];
		if(isset($_POST['unitariot'])){
			$final[$i]['Precio Unit.']='$'.(round(($actual['costo']/1.16)*100)/100);
		}if(isset($_POST['importet'])){
			$final[$i]['Importe']='$'.$actual['cantidad']*(round(($actual['costo']/1.16)*100)/100);
		}
		$final[$i]['Costo Real']='$'.$actual['costo'].' c/u';
		$final[$i]['Importe Total']='$'.$actual['costo']*$actual['cantidad'];
		$total=$total+($actual['cantidad']*$actual['costo']);
		$i++;
	}while(next($datos));
	$iva=$total-(round(($total/1.16)*100)/100);
	$subtotal=(round(($total/1.16)*100)/100);
	$pdf->ezText(' ',20);
	$pdf->ezTable($final);
	$pdf->ezText(' ',20);

	$pdf->saveState();
	$pdf->setColor(0.07,0.66,0.93);
	if(isset($_POST['subtotal'])){
		$pdf->ezText('Subtotal: $'.$subtotal,12);
	}if(isset($_POST['iva'])){
		$pdf->ezText('IVA: $'.$iva,12);
	}
	$pdf->ezText('Total: $'.$total,30);
	$pdf->restoreState();
	
	$pdf->ezText('',16);
	$pdf->saveState();
	$pdf->setColor(0.5,0.5,0.5);
	$pdf->ezText('Estamos a sus Ordenes para cualquier aclaracion',14);
	$pdf->restoreState();
	$pdf->ezSetDy(-10);
	$pdf->ezText('Este documento no tiene validez como comprobante fiscal.',8);
		
	$pdf->ezStream();
?>