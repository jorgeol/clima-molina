<?php
	include 'simple_html_dom.php';

	//$idEstacion= '472AC278';
	date_default_timezone_set('America/Lima');
	$anio = date("Y");
	$mes = date("m");
	$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
	$sqlObtenerEstaciones="SELECT ID_ORIG FROM ESTACION";
	$resulEstaciones = mysqli_query($cn, $sqlObtenerEstaciones) or die(mysqli_error($cn));
	while ($rowIdEstaciones = mysqli_fetch_assoc($resulEstaciones)){
		$resIdEstaciones[] = array_map('utf8_encode', $rowIdEstaciones);
	}
	$urlPorHora = 'http://www.senamhi.gob.pe/site/lvera/unalm.php';//solo para unalm
	//$urlPorDia = "http://www.senamhi.gob.pe/include_mapas/_dat_esta_tipo02.php?estaciones=472AC278&tipo=SUT&CBOFiltro=$anio$mes&t_e=M2";	
	//$htmlPorDia = file_get_html($urlPorDia);
	$htmlPorHora = file_get_html($urlPorHora);


	//1.Registrar clima por dia
	for($i=0;$i<count($resIdEstaciones);$i++){
		$idEstacion= $resIdEstaciones[$i]["ID_ORIG"];
		//$urlPorDia = "http://www.senamhi.gob.pe/include_mapas/_dat_esta_tipo02.php?estaciones=$idEstacion&tipo=SUT&CBOFiltro=$anio$mes&t_e=M2";
		$urlPorDia = "https://www.senamhi.gob.pe/mapas/mapa-estaciones/_dat_esta_tipo02.php?estaciones=$idEstacion&tipo=SUT&CBOFiltro=$anio$mes&t_e=M2";
		$htmlPorDia = file_get_html($urlPorDia);
		$porReemplazar = array ('<td ><div align=center>', '</div></td>');
		$fecha = formatearFecha(trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -9))));
		$fecha = date ('Y-m-d', strtotime($fecha));
		$tempProm = trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -8)));
		$tempMax = trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -7)));
		$tempMin = trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -6)));
		$humedad = trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -5)));
		$lluvia = trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -4)));
		$presion = trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -3)));
		$velocViento = trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -2)));
		$direcViento= trim(str_replace($porReemplazar, "", $htmlPorDia->find('td', -1)));

		//revisar si los datos estan vacios -->si esta vacios se asigna null
		$tempProm = is_numeric($tempProm) ? "'$tempProm'" : "NULL";
		$tempMax = is_numeric($tempMax) ? "'$tempMax'" : "NULL";
		$tempMin = is_numeric($tempMin) ? "'$tempMin'" : "NULL";
		$humedad = is_numeric($humedad) ? "'$humedad'" : "NULL";
		$lluvia = is_numeric($lluvia) ? "'$lluvia'" : "NULL";
		$presion = is_numeric($presion) ? "'$presion'" : "NULL";
		$velocViento = is_numeric($velocViento) ? "'$velocViento'" : "NULL";
		$direcViento = is_numeric($direcViento) ? "'$direcViento'" : "NULL";


		//ver si el registro ya existe
		$sqlCountClimaDia="SELECT *  FROM CLIMA_DIA WHERE FECHA='$fecha' AND ID_ORIG='$idEstacion'";
		$resultsql1 = mysqli_query($cn, $sqlCountClimaDia) or die(mysqli_error($cn));
		$countClimaDia = mysqli_num_rows($resultsql1);

		//si no existe, insertamos
		if ($countClimaDia==0) {

			if($tempMax == "NULL"){
				$sqlInsertClimaDia = "INSERT INTO CLIMA_DIA (FECHA, ID_ORIG, TEMP_PROM, TEMP_MAX, TEMP_MIN, HUMEDAD, LLUVIA, PRESION, VELOC_VIENTO, DIREC_VIENTO)
				VALUES ('$fecha', '$idEstacion', $tempProm, (SELECT COALESCE(MAX(TEMP),-999) FROM CLIMA_HORA WHERE FECHA='$fecha' AND ID_ORIG='$idEstacion'), $tempMin, $humedad, $lluvia, $presion, $velocViento, $direcViento)";
			}else{
				$sqlInsertClimaDia = "INSERT INTO CLIMA_DIA (FECHA, ID_ORIG, TEMP_PROM, TEMP_MAX, TEMP_MIN, HUMEDAD, LLUVIA, PRESION, VELOC_VIENTO, DIREC_VIENTO)
			 	VALUES ('$fecha', '$idEstacion', $tempProm, $tempMax, $tempMin, $humedad, $lluvia, $presion, $velocViento, $direcViento)";
			}

			if(mysqli_query($cn, $sqlInsertClimaDia)){
				echo "<br> INSERT OK - DÍA - $idEstacion";
			}
			else{ 
				echo mysqli_error($cn);
			}
		}
		else {
			if($tempMax == "NULL"){
				$sqlInsertClimaDia = "INSERT INTO CLIMA_DIA (FECHA, ID_ORIG, TEMP_PROM, TEMP_MAX, TEMP_MIN, HUMEDAD, LLUVIA, PRESION, VELOC_VIENTO, DIREC_VIENTO)
				VALUES ('$fecha', '$idEstacion', $tempProm, (SELECT COALESCE(MAX(TEMP),-999) FROM CLIMA_HORA WHERE FECHA='$fecha' AND ID_ORIG='$idEstacion'), $tempMin, $humedad, $lluvia, $presion, $velocViento, $direcViento)";
			}else{
				$sqlInsertClimaDia = "INSERT INTO CLIMA_DIA (FECHA, ID_ORIG, TEMP_PROM, TEMP_MAX, TEMP_MIN, HUMEDAD, LLUVIA, PRESION, VELOC_VIENTO, DIREC_VIENTO)
			 	VALUES ('$fecha', '$idEstacion', $tempProm, $tempMax, $tempMin, $humedad, $lluvia, $presion, $velocViento, $direcViento)";
			}
			$sqlDeleteClimaDia= "DELETE FROM CLIMA_DIA WHERE FECHA='$fecha' AND ID_ORIG='$idEstacion'";
			
			if (mysqli_query($cn, $sqlDeleteClimaDia)){
				if (mysqli_query($cn, $sqlInsertClimaDia)){
					echo ("<br> DELETE LUEGO INSERT OK - DÍA - $idEstacion");
				}
			}
			else{
				echo mysqli_error($cn);
			}
		}
	}

	//2.Registrar clima por hora para unalm
	$porReemplazar = array('<p3>', '</p3>');
	$fecha = date ('y-m-d', strtotime(trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -13)))));
	$hora = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -12)));
	$temp = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -11)));
	$tempRocio = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -10)));
	$humedad = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -7)));
	$lluvia = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -4)));
	$presion = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -3)));
	$radiacion = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -2)));
	$viento = trim(str_replace($porReemplazar, "", $htmlPorHora->find('p3', -1)));

	//revisar si los datos estan vacios -->si esta vacios se asigna null
	$temp = is_numeric($temp) ? "'$temp'" : "NULL";
	$tempRocio = is_numeric($tempRocio) ? "'$tempRocio'" : "NULL";
	$humedad = is_numeric($humedad) ? "'$humedad'" : "NULL";
	$lluvia = is_numeric($lluvia) ? "'$lluvia'" : "NULL";
	$presion = is_numeric($presion) ? "'$presion'" : "NULL";
	$radiacion = is_numeric($radiacion) ? "'$radiacion'" : "NULL";
	$viento = !empty($viento) ? "'$viento'": "NULL";
	
	//ver si el registro ya existe
	$sqlCountClimaHora="SELECT *  FROM CLIMA_HORA WHERE FECHA='$fecha' AND HORA='$hora' AND ID_ORIG='$idEstacion'";
	$resultsql2 = mysqli_query($cn, $sqlCountClimaHora) or die(mysqli_error($cn));
	$countClimaHora = mysqli_num_rows($resultsql2);

	//si no existe, insertamos
	if ($countClimaHora==0) {
		$sqlInsertClimaHora = "INSERT INTO CLIMA_HORA (HORA, FECHA, ID_ORIG, TEMP, TEMP_ROCIO, HUMEDAD, LLUVIA, PRESION, RADIACION, VIENTO, FH_HORA_REGISTRO)
		 VALUES ( '$hora', '$fecha', '$idEstacion', $temp, $tempRocio, $humedad, $lluvia, $presion, $radiacion, $viento, CONVERT_TZ(NOW(),'+05:00','+00:00'))";
		if(mysqli_query($cn, $sqlInsertClimaHora)){
			echo "<br> INSERT OK - HORA - $idEstacion";
		}
		else{ 
			echo mysqli_error($cn);
		}
	}
	else{
		echo ("<br> NO INSERT - HORA - $idEstacion");
	}

	//3.Registrar clima por hora para las otras estaciones
	
	date_default_timezone_set('America/Lima');
	$horaServidor = date('H');
	$minutoServidor = date('i');
	$fechaServidor = date('Y-m-d');
	//echo "<br>$horaServidor";
	//echo "<br>$fechaServidor";
	//echo "<br>$minutoServidor";
	$sqlObtenerEstaciones =null;
	$resulEstaciones = null;
	$rowIdEstaciones = null;
	$resIdEstaciones[] = null;
	$sqlObtenerEstaciones="SELECT ID_ORIG, MINUTO_ACTUALIZA FROM ESTACION WHERE ID_ORIG <>'472AC278'";
	$resulEstaciones = mysqli_query($cn, $sqlObtenerEstaciones) or die(mysqli_error($cn));
	while ($rowIdEstaciones = mysqli_fetch_assoc($resulEstaciones)){
		$sqlInsert=null;
		$sqlDelte=null; 
		$idOrig = $rowIdEstaciones["ID_ORIG"];
		$minutoActualiza = $rowIdEstaciones["MINUTO_ACTUALIZA"];
		//obtener numero de registros insertados en el dia
		$sql = "SELECT * FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'";
		$resultsql = mysqli_query($cn, $sql) or die(mysqli_error($cn));		
		$countClimaHoraXDia = mysqli_num_rows($resultsql);	

		//si estamos en el minuto de actualización +5 o +6 y el #registros = 0 registramos como primer registro del dia 00:00 horas
		if(($minutoServidor>=$minutoActualiza+1  && $minutoServidor<=$minutoActualiza+4) && $countClimaHoraXDia==0){
			//verificar que no existe un registro en CLIMA_HORA para la fecha especificada. Si no existe, insertar como el primer registro del dia (00:00 horas)
			$sqlInsert = "INSERT INTO CLIMA_HORA(ID_ORIG, FECHA, HORA, TEMP, TEMP_ROCIO, HUMEDAD, LLUVIA, PRESION, RADIACION, VIENTO, FH_HORA_REGISTRO) 
			SELECT ID_ORIG, FECHA, '$horaServidor:00:00', TRUNCATE(TEMP_PROM,1), -999, HUMEDAD, -999,-999,-999, VELOC_VIENTO, CONVERT_TZ(NOW(),'+05:00','+00:00') 
			FROM CLIMA_DIA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'";
			//echo "<br>$sqlInsert";
			if(mysqli_query($cn, $sqlInsert)){
				echo "<br> INSERT OK - HORA - $idOrig";	
			}
			else{ 
				echo mysqli_error($cn);
			}
		//si ya existe un registro significa que es el registro de las 00:00 horas. Hay que eliminarlo e insertar de nuevo.
		}else if (($minutoServidor>=$minutoActualiza+1 && $minutoServidor<=$minutoActualiza+4) && $countClimaHoraXDia==1 && $horaServidor=='00'){
			echo"<br>$horaServidor";
			echo"<br>$countClimaHoraXDia";
			$sqlDelte = "DELETE FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'";
			echo"<br>$sqlDelte";
			$resultSqlDelete = mysqli_query($cn, $sqlDelte);
			$sqlInsert = "INSERT INTO CLIMA_HORA(ID_ORIG, FECHA, HORA, TEMP, TEMP_ROCIO, HUMEDAD, LLUVIA, PRESION, RADIACION, VIENTO, FH_HORA_REGISTRO) 
			SELECT ID_ORIG, FECHA, '$horaServidor:00:00', TRUNCATE(TEMP_PROM,1), -999, HUMEDAD, -999,-999,-999, VELOC_VIENTO, CONVERT_TZ(NOW(),'+05:00','+00:00') 
			FROM CLIMA_DIA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'";
			if(mysqli_query($cn, $sqlInsert)){
				echo "<br> DELETE OK - INSERT OK - HORA - $idOrig";
			}else{
				echo mysqli_error($cn);
			}
		//insertar registro para las demás horas.
		}else if (($minutoServidor>=$minutoActualiza+1 && $minutoServidor<=$minutoActualiza+4) && $countClimaHoraXDia>=1){
			$sqlDelte = "DELETE FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor' AND HORA='$horaServidor:00:00'";
			$resultSqlDelete = mysqli_query($cn, $sqlDelte);
			$sqlInsert="INSERT INTO CLIMA_HORA (ID_ORIG, FECHA, HORA, TEMP, TEMP_ROCIO, HUMEDAD, LLUVIA, PRESION, RADIACION, VIENTO, FH_HORA_REGISTRO)
				SELECT
				'$idOrig',
				'$fechaServidor',
				'$horaServidor:00:00',
				TRUNCATE((SELECT COUNT(*) + 1 FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor') * TEMP_PROM -(SELECT SUM(TEMP) FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'), 1),
				-999,
				TRUNCATE((SELECT COUNT(*) + 1 FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor') * HUMEDAD -(SELECT SUM(HUMEDAD) FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'), 2),
				TRUNCATE((SELECT COUNT(*) + 1 FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor') * LLUVIA -(SELECT SUM(LLUVIA) FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'), 2),
				(SELECT COUNT(*) + 1 FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor') * PRESION -(SELECT SUM(PRESION) FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'),
				-999,
				TRUNCATE((SELECT COUNT(*) + 1 FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor') * VELOC_VIENTO -(SELECT SUM(VIENTO) FROM CLIMA_HORA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'), 2),
				CONVERT_TZ(NOW(),'+05:00','+00:00') 
				FROM CLIMA_DIA WHERE ID_ORIG='$idOrig' AND FECHA='$fechaServidor'";
			//echo "<br>$sqlDelte";		
			//echo "<br>$sqlInsert";		
			if(mysqli_query($cn, $sqlInsert)){
				echo "<br> INSERT OK - HORA (OTRO) - $idOrig";
			}
			else{ 
				echo mysqli_error($cn);
			}
		}
	}

	mysqli_close($cn);	

	function formatearFecha($fecha){
		$mes=substr($fecha, 3, -5);
		if($mes=='Ene'){
			$fecha = str_replace('Ene', 'JAN', $fecha);			
		}
		elseif ($mes=='Feb') {
			$fecha = str_replace('Feb', 'FEB', $fecha);
		}
		elseif ($mes=='Mar') {
			$fecha = str_replace('Mar', 'MAR', $fecha);
		}
		elseif ($mes=='Abr') {
			$fecha = str_replace('Abr', 'APR', $fecha);
		}
		elseif ($mes=='May') {
			$fecha = str_replace('May', 'MAY', $fecha);
		}
		elseif ($mes=='Jun') {
			$fecha = str_replace('Jun', 'JUN', $fecha);
		}
		elseif ($mes=='Jul') {
			$fecha = str_replace('Jul', 'JUL', $fecha);
		}
		elseif ($mes=='Ago') {
			$fecha = str_replace('Ago', 'AUG', $fecha);
		}
		elseif ($mes=='Sep') {
			$fecha = str_replace('Sep', 'SEP', $fecha);
		}
		elseif ($mes=='Oct') {
			$fecha = str_replace('Oct', 'OCT', $fecha);
		}
		elseif ($mes=='Nov') {
			$fecha = str_replace('Nov', 'NOV', $fecha);
		}
		elseif ($mes=='Dic') {
			$fecha = str_replace('Dic', 'DEC', $fecha);
		}
		return $fecha;
	}

	/* Para mostrar las variables
	echo "fecha $fecha <br>";
	echo "hora $hora <br>";
	echo "temperatura $temp <br>";
	echo "temperatura de rocio $tempRocio <br>";
	echo "humedad $humedad <br>";
	echo "lluvia $lluvia <br>";
	echo "presion $presion <br>";
	echo "radiacion $radiacion <br>";
	echo "viento $viento <br>";	*/
?>