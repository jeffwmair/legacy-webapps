<?php
	$jsonTorWeather = getUrl('http://www.theweathernetwork.com/dataaccess/citypage/json/caon0696');
	$jsonTorWeather_a = json_decode($jsonTorWeather, TRUE);
	$tempCToronto = $jsonTorWeather_a['PACKAGE']['Observation']['temperature_c'];
	
	require_once "../_appServices/utils.php";
	$temperatureJeff = $_GET['t'];
	$sql = "INSERT INTO temp_reading (insert_time, temp_c_jeff, temp_c_toronto) values (now(), $temperatureJeff, $tempCToronto);";
	getSqlResult($sql);
	
	function getUrl($url, $method='', $vars='') {
	    $ch = curl_init();
	    if ($method == 'post') {
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	    }
		curl_setopt($ch, CURLOPT_URL, $url);	
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);						
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies/cookies.txt');
	    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies/cookies.txt');;
	    $buffer = curl_exec($ch);

	    curl_close($ch);
	    return $buffer;
	}

?>