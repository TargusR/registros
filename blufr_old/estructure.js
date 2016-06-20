/*
	Versión dev
	
	Problemas conocidos
	-Dimensiones muy pequeñas tienen comportamiento inestable
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

function workarea(){
	
	unidad = measureUnits();
	
	cuadros = document.getElementById("mainBody");
	cuadros.style.height = unidad.h + "px";
	cuadros = cuadros.getElementsByTagName("article");
	i = 0;
	while(cuadros[i]){
		cuadros[i].style.width = unidad.w + "px";
		cuadros[i].style.height = unidad.h + "px";
			
			// Especial para parejas de forms y frames, no se use si las pestañas contienen más de uno
			pieza = cuadros[i].getElementsByTagName("form");
			if(pieza[0]) hform = unidad.h - pieza[0].offsetHeight;
			else hform = unidad.h; //arreglo peligroso
			pieza = cuadros[i].getElementsByTagName("iframe");
			if(pieza[0]) pieza[0].style.height = hform + "px";
			
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