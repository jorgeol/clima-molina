<?php
	header('Content-Type: text/javascript; charset=utf8');
    header('Access-Control-Allow-Origin: https://clima-molina.000webhostapp.com/');
    header('Access-Control-Max-Age: 3628800');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

    $idOperacion = $_GET["idOperacion"];
	$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
	$rs=NULL;
	$res=NULL;
	$row=NULL;

    if($idOperacion==1){
    	$sqlObtenerTodasEstaciones = 
    	"SELECT ID_ORIG as idOrig, NOMBRE as nombre, DPTO as departamento, PROV as provincia,
    	 DIST as distrito, LATITUD as latitud, LONGITUD as longitud, ALTITUD as altitud    	
    	FROM ESTACION";

    	$rs = mysqli_query($cn, $sqlObtenerTodasEstaciones);
		while ($row = mysqli_fetch_assoc($rs)){
			$res[] = array_map('utf8_encode', $row);			
		}
		echo json_encode($res, JSON_PRETTY_PRINT);
    }
?>