/*
	Versión dev
	
	Problemas conocidos
	-Margenes horizontales en mainBody no generan problemas, mientras que los margenes Verticales sí
	-redimensionar la ventana cuando no se está en el elemento uno hace que el mainGrid se desplaze
		(arreglo temporal, resetea desplazamiento)
		
*/

function measureUnits(){
	
	cuadros = document.getElementById("mainBody");
	//alert(window.innerWidth - document.body.offsetWidth); //es 76 cuando debería ser 88
	//alert(window.innerHeight + ", " + document.body.offsetHeight + ", " + cuadros.offsetHeight);
	
	unidad = {
		w:cuadros.offsetWidth + (window.innerWidth - document.body.offsetWidth),
		h:window.innerHeight - (document.body.offsetHeight - cuadros.offsetHeight)
	};
	return unidad;
}

function workarea() {
	
	unidad = measureUnits();
	
	cuadros = document.getElementById("mainBody");
	cuadros.style.height = unidad.h + "px";
	cuadros = cuadros.getElementsByTagName("article");
	i = 0;
	while(cuadros[i]) {
		cuadros[i].style.width = unidad.w + "px";
		cuadros[i].style.height = unidad.h + "px";
			
			// Obtener Forms
			pieza = cuadros[i].getElementsByTagName("form");
			
			hform = 0;
			// Calcular hform sumando todas las alturas de form y restarlo a 'unidad' para obtener hframe
			if(pieza[0]) {
				j = 0;
				while(pieza[j]) { 
					hform += pieza[j].offsetHeight;
					j++;
				} 
				hframe = unidad.h - hform;
			} else hframe = unidad.h; //arreglo peligroso
			
			// Obtener y procesar frames
			pieza = cuadros[i].getElementsByTagName("iframe");
			
			if(pieza[0]) {
				//hframe = Math.round(hframe / pieza.length);
				j = 0;
				while(pieza[j]) {
					
					// detectar si el frame es de ordenación horizontal
					if( pieza[j].className == "horizontal" ) {
						pieza[j].style.width = Math.floor(unidad.w / pieza.length) + "px"; // A veces sobra un pixel, buscar alternativa
						pieza[j].style.height = hframe + "px";
					} else {
						pieza[j].style.height = Math.round(hframe / pieza.length) + "px";
					}
					j++
				}
			}
						
		i++;
	}
	cuadros = document.getElementById("mainBody-grid");
	cuadros.style.width = (i*unidad.w + (i-1)*6) + "px"; //el 6 es por calibración de espacios, puede requerir ajustes -->tu muy bien josue!
	cuadros.style.left = 0;
	//cuadros.style.position = "absolute";
}

function change(niveles){
	unidad = measureUnits();
	document.getElementById("mainBody-grid").style.left = "-" + ((unidad.w + 6) * niveles) + "px"; //mismo caso, 6 de calibración de espacio
}