<?php
	header('Content-Type: text/javascript; charset=utf8');
    header('Access-Control-Allow-Origin: https://clima-molina.000webhostapp.com/');
    header('Access-Control-Max-Age: 3628800');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

    $idOperacion = $_GET["idOperacion"];
	$idOrig = $_GET["idOrig"];
	$anio = $_GET["anio"];
	$mes = $_GET["mes"];

	$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
	$rs=NULL;
	$res=NULL;
	$row=NULL;

	if($idOperacion=='1'){
		$sqlTraerTodoClimaDia = 
		"SELECT FECHA as fecha, ID_ORIG as idOrig, TEMP_PROM as tempProm, TEMP_MAX as tempMax, TEMP_MIN tempMin, HUMEDAD as humedad, LLUVIA as lluvia, PRESION as presion,
		 IFNULL(VELOC_VIENTO, '999') as velocViento, DIREC_VIENTO as direcViento 
		FROM CLIMA_DIA
		WHERE ID_ORIG='$idOrig'
		AND EXTRACT(YEAR_MONTH FROM FECHA)='$anio$mes'
		UNION 
		SELECT 'Promedio' as fecha, ID_ORIG as idOrig, TRUNCATE(AVG(TEMP_PROM),2) as tempProm, TRUNCATE(AVG(TEMP_MAX),2) as tempMax, TRUNCATE(AVG(TEMP_MIN),2) as tempMin,
		TRUNCATE(AVG(HUMEDAD),2) as humedad, '999' as lluvia, '999' as presion, '999' as velocViento, '999' as direcViento 
		FROM CLIMA_DIA
		WHERE ID_ORIG='$idOrig'
		AND EXTRACT(YEAR_MONTH FROM FECHA)='$anio$mes'
		UNION 
		SELECT 'Maximos' as fecha, ID_ORIG as idOrig, MAX(TEMP_PROM) as tempProm, MAX(TEMP_MAX) as tempMax, MAX(TEMP_MIN) as tempMin,
		MAX(HUMEDAD) as humedad, '999' as lluvia, '999' as presion, '999' as velocViento, '999' as direcViento 
		FROM CLIMA_DIA
		WHERE ID_ORIG='$idOrig'
		AND EXTRACT(YEAR_MONTH FROM FECHA)='$anio$mes'
		UNION 
		SELECT 'Minimos' as fecha, ID_ORIG as idOrig, MIN(TEMP_PROM) as tempProm, MIN(TEMP_MAX) as tempMax, MIN(TEMP_MIN) as tempMin,
		MIN(HUMEDAD) as humedad, '999' as lluvia, '999' as presion, '999' as velocViento, '999' as direcViento 
		FROM CLIMA_DIA
		WHERE ID_ORIG='$idOrig'
		AND EXTRACT(YEAR_MONTH FROM FECHA)='$anio$mes'";        

	    $rs = mysqli_query($cn, $sqlTraerTodoClimaDia);
		while ($row = mysqli_fetch_assoc($rs)){
			$res[] = array_map('utf8_encode', $row);			
		}
		echo json_encode($res, JSON_PRETTY_PRINT);	
	}

	if($idOperacion=='2'){
		$sqlTraerMesAño = 
		"SELECT CONCAT(SUBSTR(X.MES_AÑO,6,7), '-', SUBSTR(X.MES_AÑO, 1,4)) as mesAnio
		 FROM 
		 (SELECT DISTINCT(SUBSTR(FECHA,1,7)) AS MES_AÑO FROM CLIMA_DIA ORDER BY EXTRACT(YEAR_MONTH FROM FECHA) DESC) X";

		$rs = mysqli_query($cn, $sqlTraerMesAño);
		while ($row = mysqli_fetch_assoc($rs)) {
			$res[] = array_map('utf8_encode', $row);
		}
		echo json_encode($res, JSON_PRETTY_PRINT);	
	}
?>