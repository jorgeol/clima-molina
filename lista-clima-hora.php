<?php
	header('Content-Type: text/javascript; charset=utf8');
    header('Access-Control-Allow-Origin: https://clima-molina.000webhostapp.com/');
    header('Access-Control-Max-Age: 3628800');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

    $idOperacion = $_GET["idOperacion"];
	$idOrig = $_GET["idOrig"];
	$hora = $_GET["hora"];
	$fecha = $_GET["fecha"];

	$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
	$rs=NULL;
	$res=NULL;
	$row=NULL;

	if($idOperacion=='1'){
		$sqlTraerDiesUltimosClimaXHora = 
		"SELECT HORA as hora, FECHA as fecha, ID_ORIG as idOrig, TEMP as temp, TEMP_ROCIO as tempRocio, HUMEDAD as humedad, LLUVIA as lluvia, PRESION as presion,
		 RADIACION as radiacion, VIENTO as viento, FH_HORA_REGISTRO as fechaHoraRegistro
		FROM CLIMA_HORA
		WHERE ID_ORIG='$idOrig'
		AND HORA='$hora'
  		ORDER BY 
    	EXTRACT(YEAR_MONTH FROM FECHA) DESC,
    	EXTRACT(DAY FROM FECHA) DESC,
    	EXTRACT(HOUR FROM HORA) DESC
    	LIMIT 15";

    $rs = mysqli_query($cn, $sqlTraerDiesUltimosClimaXHora);
		while ($row = mysqli_fetch_assoc($rs)){
			$res[] = array_map('utf8_encode', $row);			
		}
		echo json_encode($res, JSON_PRETTY_PRINT);	
	}
?>