<?php
	header('Content-Type: text/javascript; charset=utf8');
    header('Access-Control-Allow-Origin: https://clima-molina.000webhostapp.com/');
    header('Access-Control-Max-Age: 3628800');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    
	$idOperacion = $_GET["idOperacion"];
	$idOrig = $_GET["idOrig"];
	$fecha = $_GET["fecha"];

	$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
	$rs=NULL;
	$res=NULL;
	$row=NULL;

	if ($idOperacion=='1'){
		$sqlClimaDiaXFecha = 
		"SELECT *
		FROM  CLIMA_DIA 
		WHERE  
		ID_ORIG = '$idOrig' AND 
		FECHA = '$fecha'";

		$rs = mysqli_query($cn, $sqlClimaDiaXFecha);
		$row = mysqli_fetch_assoc($rs);
		$array_final = 
		[	
			"fecha" => $row['FECHA'],
			"idOrig" => $row['ID_ORIG'],
			"tempProm" => $row['TEMP_PROM'],
			"tempMax" => $row['TEMP_MAX'],
			"tempMin" => $row['TEMP_MIN'],
			"humedad" => $row['HUMEDAD'],
			"lluvia" => $row['LLUVIA'],
			"presion" => $row['PRESION'],
			"velocViento" => $row['VELOC_VIENTO'],
			"direcViento" => $row['DIREC_VIENTO'],			
		];
		echo json_encode($array_final, JSON_PRETTY_PRINT);
	}
	
?>