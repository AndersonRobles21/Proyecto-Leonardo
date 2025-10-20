<html>
	<html>
	<head>
		<title>Ejercicio 70</title>
		<meta charset="UTF-8" />
		<style>
			th{
				border : 5px solid gray;
				background : blue;
                text-shadow: black;  
                width: 10%;
				 }

	    </style>
	</head>
	<body>
	<?php
		include 'conexion2.php'; 
		$consulta = $conexion2->query("SELECT nombre, Edad, Fecha, VIP, Direccion, Telefono, Provincia FROM compania");

if (!$consulta) {
    die("Error en la consulta SQL: " . $conexion2->error);
}

			while ( $registro = $consulta -> fetch_assoc() ) {
				echo '<table>'.
				"<th>nombre</th>
				 <th>Edad</th>
				 <th>Fecha</th>
				 <th>VIP</th>
				 <th>Direccion</th>
				 <th>Telefono</th>
				 </th>Provincia</th>
				 ".
				 

				"<tr>".
				"<td>".$registro['nombre']."</td>".
				"<td>".$registro['Edad']."</td>".
				"<td>".$registro['Fecha']."</td>".
				"<td>".$registro['VIP']."</td>".
				"<td>".$registro['Direccion']."</td>".
				"<td>".$registro['Telefono']."</td>".
				"<td>".$registro['Provincia']."</td>".
			    "</tr>".
			    "</table>";
               }
			    $conexion2=null;
	?>

	<br>
	<button><a href="eliminar2.php">Eliminar</a></button>
	<button><a href="form.html">Inicio</a></button>
	<button><a href="actualizar4.php">Modificar</a></button>
    	
	</body>
	</html>