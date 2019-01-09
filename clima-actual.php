<?php
	header('Content-Type: text/javascript; charset=utf8');
    header('Access-Control-Allow-Origin: https://clima-molina.000webhostapp.com/');
    header('Access-Control-Max-Age: 3628800');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

	$idOperacion = $_GET["idOperacion"];
	$idOrig = $_GET["idOrig"]; //cuando se tiene que obtener la estacion por ubicacion se manda "X"
	$ubicLatidud = $_GET["ubicLatidud"];
	$ubicLongitud = $_GET["ubicLongitud"];
	//$idOrig = '472AC278';
	//$idOperacion = '2';

	$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
	$rs=NULL;
	$res=NULL;
	$row=NULL;

	if($idOperacion=='1'){
		if ($idOrig=='X'){			
			$sqlBuscarEstacionMasCercana = 
			"SELECT ID_ORIG FROM ESTACION
			ORDER BY (($ubicLatidud - LATITUD)*($ubicLatidud - LATITUD)) 
			+ (($ubicLongitud - LONGITUD)*($ubicLongitud - LONGITUD)) ASC LIMIT 1";

			$rowIdOrig = mysqli_fetch_assoc(mysqli_query($cn, $sqlBuscarEstacionMasCercana));
			$idOrig = $rowIdOrig['ID_ORIG'];
		}

		$sqlClimaHoraActual = 
		"SELECT A.*, B.*		
        FROM CLIMA_HORA A 
		INNER JOIN ESTACION B
		ON A.ID_ORIG=B.ID_ORIG
		WHERE A.ID_ORIG='$idOrig'
  		ORDER BY 
    	EXTRACT(YEAR_MONTH FROM FECHA) DESC,
    	EXTRACT(DAY FROM FECHA) DESC,
    	EXTRACT(HOUR FROM HORA) DESC
    	LIMIT 1";

    	$sqlTempMaxMin = 
		"SELECT MAX(TEMP) as TEMP_MAX, MIN(TEMP) AS TEMP_MIN
		FROM  CLIMA_HORA 
		WHERE FECHA = (SELECT MAX(FECHA) FROM CLIMA_HORA WHERE ID_ORIG='$idOrig') AND ID_ORIG='$idOrig'";

		/*$rs = mysqli_query($cn, $sqlClimaHoraActual);
		while ($row = mysqli_fetch_assoc($rs)){
			$res[] = array_map('utf8_encode', $row);
			$fechaActual = $row['FECHA'];
			echo json_encode($res);	*/
		$rs = mysqli_query($cn, $sqlClimaHoraActual);
		$row = mysqli_fetch_assoc($rs);

		$rs2 = mysqli_query($cn, $sqlTempMaxMin);
		$row2 = mysqli_fetch_assoc($rs2);

		$array_final = 
		[	
			"hora" => $row['HORA'],
			"fecha" => $row['FECHA'],
			"idOrig" => $row['ID_ORIG'],
			"temp" => $row['TEMP'],
			"tempRocio" => $row['TEMP_ROCIO'],
			"humedad" => $row['HUMEDAD'],
			"lluvia" => $row['LLUVIA'],
			"presion" => $row['PRESION'],
			"radiacion" => $row['RADIACION'],
			"viento" => $row['VIENTO'],
			"fechaHoraRegistro" => $row['FH_HORA_REGISTRO'],
			"idOrig" => $row['ID_ORIG'],
			"nombre" => $row['NOMBRE'],
			"departamento" => $row['DPTO'],
			"provincia" => $row['PROV'],					
			"distrito" => $row['DIST'],
			"latitud" => $row['LATITUD'],
			"longitud" => $row['LONGITUD'],
			"altitud" => $row['ALTITUD'],
			"tempMax" => $row2['TEMP_MAX'],
			"tempMin" => $row2['TEMP_MIN'],						
		];
		echo json_encode($array_final, JSON_PRETTY_PRINT);
	}

	else if ($idOperacion=='2'){
		$sqlTempMaxMin = 
		"SELECT MAX(TEMP) as TEMP_MAX, MIN(TEMP) AS TEMP_MIN
		FROM  CLIMA_HORA 
		WHERE FECHA = (SELECT MAX(FECHA) FROM CLIMA_HORA WHERE ID_ORIG='$idOrig')";
		$rs = mysqli_query($cn, $sqlTempMaxMin);
		while ($row = mysqli_fetch_assoc($rs)){
			$res[] = array_map('utf8_encode', $row);
			echo json_encode($res);
		}	
	}
	mysqli_close($cn);
?>