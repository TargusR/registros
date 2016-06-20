<?php
function inventario($precio5,$inv_CM,$inv_PT05,$inv_PT177,$inv_MT,$inv_Alm){
				$inv_Tot = $inv_CM + $inv_PT05 + $inv_PT177 + $inv_MT + $inv_Alm;
				return $inv_Tot;
			}
			
function inversion($precio5,$inv_CM,$inv_PT05,$inv_PT177,$inv_MT,$inv_Alm,$sucursal){
				$inv_Tot = $inv_CM + $inv_PT05 + $inv_PT177 + $inv_MT + $inv_Alm;
				$tot_CM = $inv_CM * $precio5;
				$tot_PT05 = $inv_PT05*$precio5;
				$tot_PT177 = $inv_PT177*$precio5;
				$tot_MT = $inv_MT*$precio5;
				$tot_Alm = $inv_Alm*$precio5;
				$Tot_Precio = $precio5*$inv_Tot;
				if($sucursal)
					{
						if($inv_CM !=NULL) return (float)$tot_CM;
						if($inv_PT05 !=NULL) return $tot_PT05;
						if($inv_PT177 !=NULL) return $tot_PT177;
						if($inv_MT !=NULL) return $tot_MT;
						if($inv_Alm !=NULL) return $tot_Alm;
					}
					
				else{
					return $Tot_Precio;
					}
			}


function precios($cadena){
	//$cadena = str_replace('p', 'P', $cadena);
	$mod=substr($cadena,-1);
	$salida = "Precio ".$mod;
	return  $salida;
}


?>