<?php
	require_once "../_appServices/utils.php";
	require_once "./simple_html_dom.php";

	set_time_limit(180);

	
	$urlJson = "http://www.myhood.ca/scripts/reviews/map_functions.php";
	

	
	
	$p2 = "action=get_points&ne=43.619538,-79.465548&sw=43.602871,-79.497425&page=review&zoom=15&blank=1&recommend=75&rating=4&num_reviews=0&price_min%5B%5D=0&bedrooms=0&bathrooms=0";
	
	$latInc = 0.16662 / 2.0;
	$lonInc = 0.031877 / 2.0; 
	
	// echo $latInc;
	// echo "<br>";
	// echo $lonInc;
	// return;
	
	$origLat = 43.602871;
	$origLon = -79.497425;

	
	$curLatA = $origLat;
	$curLatB = $curLatA + $latInc;
	
	$curLonA = $origLon;
	$curLonB = $curLonA + $lonInc;
	
	$allResults = array();
	
	$i = 0;
	while($curLatA < 43.95) {
		
		// if ($i > 2) break;
		// $i++;
		// inner loop
		while ($curLonA < -79.05 ) {
			
			$ne = 'ne=' . $curLatB . ',' . $curLonB;
			$sw = 'sw=' . $curLatA . ',' . $curLonA;
			echo $ne . ', ' . $sw . '<br>';

			
			// $params2 = json_encode($params);
			// echo $params2;
			$params = "action=get_points&".$ne."&".$sw."&page=review&zoom=1&blank=1&recommend=75&rating=4&num_reviews=0&price_min%5B%5D=0&bedrooms=0&bathrooms=0";
			$json = httpRequest($urlJson, 'post', $params);
			array_push($allResults, $json);

			$curLonA += $lonInc;
			$curLonB = $curLonA + $lonInc;
		}
		
		$curLonA = $origLon;
		$curLonB = $curLonA + $lonInc;
		
		$curLatA += $latInc;
		$curLatB = $curLatA + $latInc;
	}

	$sz = count($allResults);
	for($i = 0; $i < $sz; $i++) {
		$js = $allResults[$i];
		echo "<br><br>";
		var_dump($js);
	}

// var_dump($allResults);
return;	
	// echo $json;
	
	

	function httpRequest($url, $method='', $vars='') {
	    $ch = curl_init();
	    if ($method == 'post') {
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	    }
		curl_setopt($ch, CURLOPT_URL, $url);	
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);	
		curl_setopt($ch, CURLOPT_AUTOREFERER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // prevent the data from showing on the page
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies/cookies.txt');
	    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies/cookies.txt');;
		// curl_setopt($ch, CURLOPT_HTTPHEADER, 
		// 	array('Accept: application/json', 
		// 	'Content-Type: application/json; charset=UTF-8',
		// 	'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36'
		// 	)
		// );
	    $buffer = curl_exec($ch);

	    curl_close($ch);
	    return $buffer;
	}
	


?>