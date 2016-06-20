
function limpiar(formulario){
	
	if(formulario != null) {
		controles = formulario.getElementsByTagName("input");
		
		for(i = 0; i < controles.length; i++){
			
			if(controles[i].type == "text")
				controles[i].value = "";
			
		}
	}
}