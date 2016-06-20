<?php 

/* Datos de la App:
	Nombre: Catalogo, componente inventario.
	Version: 1.2
	Codificado por Estudio Veintitres.  
*/
	
	session_start();
	require_once('conex.php');
	include('inventario_module.php');
	//examinar si existe un acceso concedido
	if(isset($_SESSION['Apps_acceso'])) $acceso = $_SESSION['Apps_acceso'];
	else $acceso = false;
	
	//Obtener parametros de la URL enviada desde el/los formularios
	if(isset($_GET['busqueda'])){
		$busqueda = $_GET['busqueda'];
		$busqueda = str_replace(" ","%",$busqueda);
	}else $busqueda = "";
	
	if(isset($_GET['columna'])) $columna = $_GET['columna'];
	else $columna = false;
	
	if(isset($_GET['columnaP'])) $columnaP = $_GET['columnaP'];
	else $columnaP = false;
	//quitar espacios en blanco;
	$pr_Number=$columnaP;
	$col=precios($columnaP);
	
	if(isset($_GET['sucursal'])) $sucursal = $_GET['sucursal'];
	else $sucursal = false;
	
	if($acceso){
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Si lees esto, algo estás haciendo mal...</title>
		<style>
			body {
				margin:0; padding:14px;
				font-family:Verdana, Geneva, sans-serif;
				background:#FFF;
				font-size:85%;
				color:#121212;
				}			
			
			.alert{
                background:#FCD125;
    			color:#121212;
    			margin:10px;
    			padding-left:65px;
    			height:50px;
    			position:relative;
				} .alert:before {
					content:'i';
					text-align:center;
					font-size:3em;
					font-family:times,serif;
					font-weight:bold;
					font-style:italic;
					color:#FCD125;
					display:block;
					width:50px;
					line-height:50px;
					position:absolute;
					left:0;
					background:#121212;			
					}
				.alert p { line-height:50px; }
			
			table th {
				background:#121212;
				color:#FCD125;
				text-align:center;
				padding:6px 12px;
				}
			table td{
				border-bottom:1px dotted #b3b3b3;
				padding:2px 12px;
				}
			table .extra{
				display:none;
				}
			
			.imageContainer {
				width:80px;
				height:80px;
				overflow:hidden;
				background:url('sources/noimage.jpg') no-repeat center center;
				}
				.imageContainer img {
					max-width:80px;
					max-height:80px;
					position:relative;
					}
		</style>
		<script type='text/javascript'>
			function editar(id,nombre,precio1,precio2,precio3,precio4,precio5,inv_CM,ped_CM,inv_PT05,ped_PT05,inv_PT177,ped_PT177,inv_MT,ped_MT,inv_Alm,proveedores,categoria,subcategoria,imagen){
				Editor = parent.document.getElementById("Editor");
				if(Editor.nombre) Editor.nombre.value = nombre;
				if(Editor.precio1) Editor.precio1.value = precio1;
				if(Editor.precio2) Editor.precio2.value = precio2;
				if(Editor.precio3) Editor.precio3.value = precio3;
				if(Editor.precio4) Editor.precio4.value = precio4;
				if(Editor.precio5) Editor.precio5.value.value = precio5;
				if(Editor.inv_CM) Editor.inv_CM.value = inv_CM;
				if(Editor.ped_CM) Editor.ped_CM.value = ped_CM;
				if(Editor.inv_PT05) Editor.inv_PT05.value = inv_PT05;
				if(Editor.ped_PT05) Editor.ped_PT05.value = ped_PT05;
				if(Editor.inv_PT177) Editor.inv_PT177.value = inv_PT177;
				if(Editor.ped_PT177) Editor.ped_PT177.value = ped_PT177;
				if(Editor.inv_MT) Editor.inv_MT.value = inv_MT;
				if(Editor.ped_MT) Editor.ped_MT.value = ped_MT;
				if(Editor.inv_Alm) Editor.inv_Alm.value = inv_Alm;
				if(Editor.proveedores) Editor.proveedores.value = proveedores;
				if(Editor.categoria) Editor.categoria.value = categoria;
				if(Editor.subcategoria) Editor.subcategoria.value = subcategoria;
				Editor.enviar.value = "Guardar";
				//Editor.imagen.value = imagen;
				Editor.ident.value = id;
			}
	
			function resize(imagen){
				imagen = document.getElementById(imagen);
				ancho = imagen.width;
				alto = imagen.height
				factor = imagen.width / imagen.height;
				if(factor >= 1){
					//más ancha que alta
					imagen.style.height = '80px';
					ancho = Math.round(factor*80);
					imagen.style.maxWidth = ancho + "px";
					imagen.style.left = "-" + ((ancho - 80) / 2) + "px";
				} else { 
					//más alta que ancha
					imagen.style.width = '80px';
					alto = Math.round(80/factor);
					imagen.style.maxHeight = alto + "px";
					imagen.style.bottom = ((alto - 80) / 2) + "px";
				}
			}
			
			var unidadAncho = window.innerWidth - 24;
			function cambiar(niveles){ //para mover el grid
				parent.document.getElementById("mainBody-grid").style.left = "-" + ((unidadAncho + 6) * niveles) + "px"; //calibración de espacio con 6
			}
			
			function expanderTabla(){
				ocultos = document.getElementById("Tabla-Resultado").getElementsByTagName("th");
				i=0;
				while(ocultos[i]){
					if(ocultos[i].className == "extra"){
						if(ocultos[i].style.display != "table-cell") ocultos[i].style.display = "table-cell";
						else ocultos[i].style.display = "none";	
					}					
					i++;
				}
				
				ocultos = document.getElementById("Tabla-Resultado").getElementsByTagName("td");
				i=0;
				while(ocultos[i]){
					if(ocultos[i].className == "extra"){
						if(ocultos[i].style.display != "table-cell") ocultos[i].style.display = "table-cell";
						else ocultos[i].style.display = "none";	
					}					
					i++;
				}
				
			}
		</script>
	</head>
	<body>
		<?php
		
		if($sucursal) {
			if($busqueda == "") $sql="SELECT * FROM articulos ORDER BY articulos.ID";
			else $sql="SELECT * FROM articulos WHERE articulos.$columna LIKE '%$busqueda%' ORDER BY articulos.ID";
			
			$consult = mysql_query($sql, $conex) or die(mysql_error());
			if(mysql_num_rows($consult)!=0){
				echo "<a href='' onClick='expanderTabla();return false;'>Expander</a>";
				echo "<table id='Tabla-Resultado'><tr><th>#ID</th><th>Imagen</th><th>Nombre</th><th>$col</th>";
				if($sucursal == "_CM") echo "<th>C. Magno Inv.</th><th>C. Magno Total</th>";
				else echo "<th class='extra'>C. Magno Inv.</th><th class = 'extra'>C. Magno Total</th>";
				if($sucursal == "_PT05") echo "<th>Plaza T. 05 Inv</th><th>Plaza T. 05 Total</th>";
				else echo "<th class='extra'>Plaza T. 05 Inv</th><th class='extra'>Plaza T. 05 Total</th>";
				if($sucursal == "_PT177") echo "<th>Plaza T. 177 Inv.</th><th>Plaza T. 177 Total</th>";
				else echo "<th class='extra'>Plaza T. 177 Inv.</th><th class='extra'>Plaza T. 177 Total</th>";
				if($sucursal == "_MT") echo "<th>Macro Tienda Inv.</th><th>Macro Tienda Total</th>";
				else echo "<th class='extra'>Macro Tienda Inv.</th><th class='extra'>Macro Tienda Total</th>";
				if($sucursal == "_Alm") echo "<th>Almacen Inv.</th><th>Almacen Total</th>";
				else echo "<th class='extra'>Almacen Inv.</th><th class='extra'>Almacen Total</th>";
				echo "<th>Categoria</th><th>Subcategoria</th><th>Inv. Total</th><th>Costo Total</th><th></th></tr>";
				while($row = mysql_fetch_assoc($consult)){
					echo "<tr><td>".$row['id']."</td><td><div class='imageContainer'><img id='img".$row['id']."' src='".$row['imagen']."' onload='resize(&#34;img".$row['id']."&#34;);'></div></td><td>".$row['nombre']."</td><td>$".number_format($row[$pr_Number],2)."</td>";
					//$tot_Suc=
					if($sucursal == "_CM") echo "<td>".$row['inv_CM']." pzs.</td><td>$".number_format(inversion($row[$columnaP],$row['inv_CM'],NULL,NULL,NULL,NULL,$sucursal),2)."</td>";					
					else echo "<td class='extra'>".$row['inv_CM']." pzs.</td><td class='extra'>$".number_format(inversion($row[$columnaP],$row['inv_CM'],NULL,NULL,NULL,NULL,$sucursal),2)."</td>";
					if($sucursal == "_PT05") echo "<td>".$row['inv_PT05']." pzs.</td><td>$".number_format(inversion($row[$columnaP],NULL,$row['inv_PT05'],NULL,NULL,NULL,$sucursal),2)."</td>";
					else echo "<td class='extra'>".$row['inv_PT05']." pzs.</td><td class='extra'>$".number_format(inversion($row[$columnaP],NULL,$row['inv_PT05'],NULL,NULL,NULL,$sucursal),2)."</td>";
					if($sucursal == "_PT177") echo "<td>".$row['inv_PT177']." pzs.</td><td>$".number_format(inversion($row[$columnaP],NULL,NULL,$row['inv_PT177'],NULL,NULL,$sucursal),2)."</td>";
					else echo "<td class='extra'>".$row['inv_PT177']." pzs.</td><td class='extra'>$".number_format(inversion($row[$columnaP],NULL,NULL,$row['inv_PT177'],NULL,NULL,$sucursal),2)."</td>";
					if($sucursal == "_MT") echo "<td>".$row['inv_MT']." pzs.</td><td>$".number_format(inversion($row[$columnaP],NULL,NULL,NULL,$row['inv_MT'],NULL,$sucursal),2)."</td>";
					else echo "<td class='extra'>".$row['inv_MT']." pzs.</td><td class='extra'>$".number_format(inversion($row[$columnaP],NULL,NULL,NULL,$row['inv_MT'],NULL,$sucursal),2)."</td>";
					if($sucursal == "_Alm") echo "<td>".$row['inv_Alm']." pzs.</td><td>$".number_format(inversion($row[$columnaP],NULL,NULL,NULL,NULL,$row['inv_Alm'],$sucursal),2)."</td>";
					else echo "<td class='extra'>".$row['inv_Alm']." pzs.</td><td class='extra'>$".number_format(inversion($row[$columnaP],NULL,NULL,NULL,NULL,$row['inv_Alm'],$sucursal),2)."</td>";
					echo "<td>".$row['categoria']."</td><td>".$row['subcategoria']."</td>";
					$inv = inventario($row[$columnaP],$row['inv_CM'],$row['inv_PT05'],$row['inv_PT177'],$row['inv_MT'],$row['inv_Alm']);
					$tot = inversion($row[$columnaP],$row['inv_CM'],$row['inv_PT05'],$row['inv_PT177'],$row['inv_MT'],$row['inv_Alm'],NULL);
					setlocale(LC_MONETARY, 'es_MX');
					
					echo "<td>".$inv."</td>";	
					echo "<td> $".number_format($tot,2)."</td>";	
					echo "<td><a href='#' onClick='editar(&#34;".$row['id']."&#34;,&#34;".$row['nombre']."&#34;,&#34;".$row['precio1']."&#34;,&#34;".$row['precio2']."&#34;,&#34;".$row['precio3']."&#34;,&#34;".$row['precio4']."&#34;,&#34;".$row['precio5']."&#34;,&#34;".$row['inv_CM']."&#34;,&#34;".$row['ped_CM']."&#34;,&#34;".$row['inv_PT05']."&#34;,&#34;".$row['ped_PT05']."&#34;,&#34;".$row['inv_PT177']."&#34;,&#34;".$row['ped_PT177']."&#34;,&#34;".$row['inv_MT']."&#34;,&#34;".$row['ped_MT']."&#34;,&#34;".$row['proveedores']."&#34;,&#34;".$row['categoria']."&#34;,&#34;".$row['subcategoria']."&#34;,&#34;".$row['imagen']."&#34;);cambiar(0);return false;' title='Editar este campo'><img src='sources/editar.png'></a></td></tr>";
				} echo '</table>'; ?>
				
			<?php
			} else echo "<div class='alert'><p>La búsqueda no ha generado resultados :(</p></div>";
		} else echo "<div class='alert'><p>Seleccione la sucursal e Ingrese palabras palabas para Filtar los resultados</p></div>";
		
		?>
	</body>
</html>
<?php } else header("Location: ../"); //redireccionar a esc ?>