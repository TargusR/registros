/*-----------------------
	.blu framework
	Nombre componente: Common Javascript
	Version: dev 2
-----------------------*/


function limpiar(formulario){
	
	if(formulario != null) {
		
		//limpiar INPUTS de diferentes tipos
		controles = formulario.getElementsByTagName("input");
		for(i = 0; i < controles.length; i++){
			
			if(controles[i].type == "text")
				controles[i].value = "";
			
			if(controles[i].type == "checkbox")
				controles[i].checked = false;
			
			if(controles[i].type == "file")
				controles[i].value = "";
			
		}
		
		//limpiar TEXTAREAS
		controles = formulario.getElementsByTagName("textarea");
		for(i = 0; i < controles.length; i++) {
			controles[i].value = "";
		}
	}
}

function remove_element(rem) {
	rem.parentNode.removeChild(rem);
}