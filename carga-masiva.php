<?php
	include 'simple_html_dom.php';

	$idEstacion= $_GET["idEstacion"];
	$añoInicio = 2018;//2010
	$mesInicio = 9; //10
	$añoFin = date("Y");
	$mesFin = date("m");

	//472AC278
	$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
	$porReemplazar = array ('<td ><div align=center>', '</div></td>');

	while ($añoInicio*100+$mesInicio<=$añoFin*100+$mesFin){

		if($mesInicio<10){
			$urlPorDia = 'https://www.senamhi.gob.pe/mapas/mapa-estaciones/_dat_esta_tipo02.php?estaciones='.$idEstacion.'&tipo=SUT&CBOFiltro='.$añoInicio.'0'.$mesInicio.'&t_e=M2';			
		}
		else{
			$urlPorDia = 'https://www.senamhi.gob.pe/mapas/mapa-estaciones/_dat_esta_tipo02.php?estaciones='.$idEstacion.'&tipo=SUT&CBOFiltro='.$añoInicio.$mesInicio.'&t_e=M2';				
		}

		$htmlPorDia = file_get_html($urlPorDia);
		$i=1;	
			while($i<=31){
				$elements = $htmlPorDia->find('tr', -$i);
				$i++;
				$porReemplazar = array ('<tr aling=center >', '<br>', '<td ><div align=center>');		
				$elements = str_replace($porReemplazar, "", $elements);

				$element = explode('</div></td>', $elements);
				$fecha = formatearFecha(trim($element[0]));
				$fecha = date('Y-m-d', strtotime($fecha));
				$tempProm = trim($element[1]);
				$tempMax = trim($element[2]);
				$tempMin = trim($element[3]);
				$humedad = trim($element[4]);
				$lluvia = trim($element[5]);
				$presion = trim($element[6]);
				$velocViento = trim($element[7]);
				$direcViento = trim($element[8]);

				//revisar si los datos estan vacios -->si esta vacios se asigna null
				$tempProm = is_numeric($tempProm) ? "'$tempProm'" : "NULL";
				$tempMax = is_numeric($tempMax) ? "'$tempMax'" : "NULL";
				$tempMin = is_numeric($tempMin) ? "'$tempMin'" : "NULL";
				$humedad = is_numeric($humedad) ? "'$humedad'" : "NULL";
				$lluvia = is_numeric($lluvia) ? "'$lluvia'" : "NULL";
				$presion = is_numeric($presion) ? "'$presion'" : "NULL";
				$velocViento = is_numeric($velocViento) ? "'$velocViento'" : "NULL";
				$direcViento = is_numeric($direcViento) ? "'$direcViento'" : "NULL";


				//if ($fecha=="" || $tempProm>=50 || $tempProm<=-50 || $fecha=='1970-01-01'){
				if ($fecha=="" || $tempProm=="" || $fecha=='1970-01-01'){
					echo "<br> NO INSERT <br>";			
				}		
				else{
					$cn = mysqli_connect("localhost", "id5342713_jortiz", "duis123", "id5342713_dbclimamolina");
					$sqlInsertClimaDia = "INSERT INTO CLIMA_DIA (FECHA, ID_ORIG, TEMP_PROM, TEMP_MAX, TEMP_MIN, HUMEDAD, LLUVIA, PRESION, VELOC_VIENTO, DIREC_VIENTO)
				 	 VALUES ('$fecha', '$idEstacion', $tempProm, $tempMax, $tempMin, $humedad, $lluvia, $presion, $velocViento, $direcViento)";
				 	if(mysqli_query($cn, $sqlInsertClimaDia)){
						echo "<br> INSERT OK - FECHA = $fecha <br> $sqlInsertClimaDia";

					}
					else{ 
						echo mysqli_error($cn);
					}
				}

			}
		if($mesInicio<12){
			$mesInicio++;
		}
		else{
			$añoInicio++;
			$mesInicio=1;
		}

	}	

/*	while ($añoInicio*10+$mesInicio<=$añoFin*10+$mesFin){
		$urlPorDia = 'http://www.senamhi.gob.pe/include_mapas/_dat_esta_tipo02.php?estaciones='.$idEstacion.'&tipo=SUT&CBOFiltro='.$añoInicio.$mesInicio.'&t_e=M2';
		$htmlPorDia = file_get_html($urlPorDia);
		$fecha = str_replace($porReemplazar, "", $htmlPorDia->find('td', -9));
		$tempProm = str_replace($porReemplazar, "", $htmlPorDia->find('td', -8));
		$tempMax = str_replace($porReemplazar, "", $htmlPorDia->find('td', -7));
		$tempMin = str_replace($porReemplazar, "", $htmlPorDia->find('td', -6));
		$humedad = str_replace($porReemplazar, "", $htmlPorDia->find('td', -5));
		$lluvia = str_replace($porReemplazar, "", $htmlPorDia->find('td', -4));
		$presion = str_replace($porReemplazar, "", $htmlPorDia->find('td', -3));
		$velocViento = str_replace($porReemplazar, "", $htmlPorDia->find('td', -2));
		$direcViento= str_replace($porReemplazar, "", $htmlPorDia->find('td', -1));	

	}

	
	$fecha = str_replace($porReemplazar, "", $htmlPorDia->find('td', -9));
	$tempProm = str_replace($porReemplazar, "", $htmlPorDia->find('td', -8));
	$tempMax = str_replace($porReemplazar, "", $htmlPorDia->find('td', -7));
	$tempMin = str_replace($porReemplazar, "", $htmlPorDia->find('td', -6));
	$humedad = str_replace($porReemplazar, "", $htmlPorDia->find('td', -5));
	$lluvia = str_replace($porReemplazar, "", $htmlPorDia->find('td', -4));
	$presion = str_replace($porReemplazar, "", $htmlPorDia->find('td', -3));
	$velocViento = str_replace($porReemplazar, "", $htmlPorDia->find('td', -2));
	$direcViento= str_replace($porReemplazar, "", $htmlPorDia->find('td', -1));*/

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

?>