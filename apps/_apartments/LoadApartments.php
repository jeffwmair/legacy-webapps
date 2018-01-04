<?php
	require_once "../_appServices/utils.php";
	require_once "./simple_html_dom.php";

	set_time_limit(180);
	
	$targetHost = $_GET['targethost'];
	
	$baseUrl = "http://$targetHost/WebApps/_appServices/DataService.php?";
	
	/* get a session with my service */
	$sessionPass = getSessionPw();
	$getSessionUrl = $baseUrl . 'method=GET&function=session&application=LoadApartments&pass=' . $sessionPass;
	$session = httpRequest($getSessionUrl, 'GET');
	$session = json_decode($session, true);
	$session = $session[0];
	$sessionid = $session["sessionid"];
	
	logMsg('INFO', 'Got a session', 'LoadApartments (' . $targetHost . ')');
	
	/* get all the entries from the database first */
	logMsg('INFO', 'Getting entries from the database...', 'LoadApartments (' . $targetHost . ')');
	$urlGetExisting = "http://$targetHost/WebApps/_appServices/DataService.php?method=GET&function=apartmentlisting&minprice=0&maxprice=-1&bedrooms=-1&status=-1&listid=-1&intersection=-1";
	$existingApartmentsRecords = json_decode(httpRequest($urlGetExisting, 'GET'), TRUE);	
	$existingCount = count($existingApartmentsRecords);	
	logMsg('INFO', "Loaded $existingCount entries", 'LoadApartments (' . $targetHost . ')');
	
	$curDbIvitsActive = Array();
	$curDbIvitsInActive = Array();
	for($i = 0; $i < $existingCount; $i++) {
		if ($existingApartmentsRecords[$i]["IsActive"] == 1) {
			array_push($curDbIvitsActive, $existingApartmentsRecords[$i]["iVit"]);	
		}
		else {
			array_push($curDbIvitsInActive, $existingApartmentsRecords[$i]["iVit"]);			
		}
	}
	
	$urlJson = "http://www.viewit.ca/vwMapSearch.aspx/GetAllListingForBound";
	
	$urlGetBounds = $baseUrl . "method=GET&function=apartmentboundaries";
	$resultBounds = httpRequest($urlGetBounds, 'GET');
	$bounds = json_decode($resultBounds, true);

	$BoundaryLatNe = ($bounds[0]['lat_ne']);
	$BoundaryLongNe = ($bounds[0]['lon_ne']);
	$BoundaryLatSw = ($bounds[0]['lat_sw']);
	$BoundaryLongSw = ($bounds[0]['lon_sw']);

	// close to Yonge/Eg (For a smallet set of data to load)
	// $BoundaryLongNe = "-79.3937450";
	// $BoundaryLatNe = "43.7123410";
	// $BoundaryLongSw = "-79.4053479";
	// $BoundaryLatSw = "43.7051630";
	
	$mapIncrement = 0.0150;
	
	$curLonSw = $BoundaryLongSw;
	$curLatSw = $BoundaryLatSw;
	$curLonNe = $curLonSw + ($mapIncrement * 1.5);
	$curLatNe = $curLatSw + ($mapIncrement * 1.5);
	
	$jsonSets = Array();
	$loopCount = 0;
	$innerLoopCounter = 0;
	
	logMsg('INFO', "Starting iteration through map sections...", 'LoadApartments (' . $targetHost . ')');
	while($curLonSw < $BoundaryLongNe) {
							
		while($curLatSw < $BoundaryLatNe) {
			
			$params = array(
				"NELat" => $curLatNe,
				"NELong" => $curLonNe,
				"SWLat" => $curLatSw,
				"SWLong" => $curLonSw,
				"MapCenterLat" => 0,
				"MapCenterLng" => 0,
				"MapZoom" => 2,
				"price1" => true, "price2" => true, "price3" => true, "price4" => true, "price5" => true, "price6" => true,
				"room" => true,
				"bachelor" => true,
				"room1" => true, "room2" => true, "room3" => true, 
				"furnished" => true, "unfurnished" => true,
				"loft" => false, "basement" => true, "retirement" => false,
				"AC" => false, "FridgeStove" => false,  "Parking" => false, "Balcony" => false, "Pets" => false, "Cats" => false, "Fireplace" => false,
				"Laundary" => false, "Yard" => false, "Dishwasher" => false, "Exerciseroom" => false, "Pool" => false,	"Deck" => false,
				"sFilterText" => "",
				"iCityID" => 0
			);	
			
			$postParams = json_encode($params);
			$json = httpRequest($urlJson, 'post', $postParams);
			array_push($jsonSets, json_decode($json, TRUE));
			var_dump($json);
			logMsg('INFO', "Loaded a map section [" . $innerLoopCounter . "]", 'LoadApartments (' . $targetHost . ')');

			$curLatSw = $curLatSw + $mapIncrement;
			$curLatNe = $curLatNe + ($mapIncrement * 1.5); // increase the far edge more
			
			$innerLoopCounter = $innerLoopCounter + 1;
		}
		
		logMsg('INFO', "Latitude section completed [" . $loopCount . "]", 'LoadApartments (' . $targetHost . ')');
					
		$curLatSw = $BoundaryLatSw;
		$curLatNe = $curLatSw + ($mapIncrement * 1.5);
				
		/* increment our position on the map */
		$curLonSw = $curLonSw + $mapIncrement;
		$curLonNe = $curLonSw + $mapIncrement * 1.5;
		
		// $notFinishedTraversingMap = $curLatSw < $BoundaryLatNe || $curLonSw < $BoundaryLongNe;

		// $notFinishedTraversingLon = $curLonSw < $BoundaryLongNe;

		$loopCount = $loopCount + 1;	
		// if ($loopCount > 4) break;
	}
	logMsg('INFO', "Finished iteration through map sections", 'LoadApartments (' . $targetHost . ')');
		
	$jsonSetCount = count($jsonSets);
	$latestIvits = Array();
	
	/* loop through all the json, sending each item to the web service */
	for($i = 0; $i < $jsonSetCount; $i++) {
		
		$jsonSetSingle = $jsonSets[$i]["d"];	
		$jsonDataItemCount = count($jsonSetSingle);
			
		for($j = 0; $j < $jsonDataItemCount; $j++) {
		
			$item = $jsonSetSingle[$j];		
			$ivit = $item["iVit"];
			$lon = $item['Longitude'];
			$lat = $item['Latitude'];
			
			$ivit = $item["iVit"];
			$price = $item["Price"];
			if ($price >= 950 && $price <= 1450) {
							
				/* make a list of all the current ivits for later finding inactive ones in the db */
				if (!in_array($ivit, $latestIvits)) {
					array_push($latestIvits, $ivit);	
				}
		
				/* send to the web service only if it doesn't already exist in the DB! */
				if (isWithinBoundaries($lon, $lat, $BoundaryLongNe, $BoundaryLatNe, $BoundaryLongSw, $BoundaryLatSw)) {
				
					/* is it not in the active, nor inactive */
					if (!in_array($ivit, $curDbIvitsActive) && !in_array($ivit, $curDbIvitsInActive)) {			

						$urlDetails = "http://www.viewit.ca/vwExpandView.aspx?ViT=" . $ivit;
						$detailsPageHtml = httpRequest($urlDetails);
						$detailsPageDom = str_get_html($detailsPageHtml);

						/* got the description here */
						$largeDescription = $detailsPageDom->find('span.bodyText');
						$largeDescription = $largeDescription[0]->plaintext;
					
						/* get utils included or not */
						// $utilsInc = $detailsPageDom->find('#ctl00_ContentPlaceHolder1_lblUtilities');
						// $utilsInc = $utilsInc[0]->plaintext;

						// cleanup
						// $largeDescription

						$featuresString = ' ';
						$featuresHtmlArr = $detailsPageDom->find('#AmenitiesTable td');
						$countFeatures = count($featuresHtmlArr);
						for($i = 0; $i < $countFeatures; $i++) {
							$excluded = stristr($featuresHtmlArr[$i], 'color:LightGrey');
							if ($excluded == '') {
								/* append to the features list here */
								$featuresString .= trim($featuresHtmlArr[$i]->plaintext) . ', ';
							}
						}

						$reqURl = $baseUrl 
							. "method=SET&function=apartmentlisting&app_name=LoadApartmentListing&sessionid=$sessionid"
							. '&ivit=' . urlencode($ivit)
							. '&intersection=' . urlencode($item['Intersection'])
							. '&latitude=' . urlencode($lat)
							. '&longitude=' . urlencode($lon)		
							. '&description=' . urlencode($largeDescription)
							. '&address=' . urlencode($item['Address'])	
							. '&price=' . urlencode($item['Price'])
							. '&extpicurl=' . urlencode($item['ExtPictURL'])					
							. '&bedrooms=' . urlencode($item['Bedroom'])
							. '&listingstatus=' . urlencode($item['ListingStatus'])
							. '&video=' . urlencode($item['Video'])
							. '&utilities=' . urlencode($item['Utilities'])
							. '&utilsincl=' .urlencode($utilsInc)
							. '&features=' . urlencode($featuresString)
							. '&active=1';
						
						$result = httpRequest($reqURl, 'GET');
						logMsg('INFO', "Apartment listing sent to DataService for iVit#" . $item["iVit"],  'LoadApartments (' . $targetHost . ')');
						array_push($curDbIvitsActive, $item["iVit"]);
						// logMsg('INFO', "Apartment listing sent to DataService for iVit#" . $item["iVit"], 'LoadApartments (' . $targetHost . ')');
					
					}
					else if (in_array($ivit, $curDbIvitsInActive)) {
											/* time to reactive it! */
											$url = $baseUrl 
												. "method=SET&function=apartmentsetactive&app_name=LoadApartmentListing&sessionid=$sessionid&active=1&ivit=$ivit";
											$res = httpRequest($url, 'GET');	
											logMsg('INFO', "Apartment listing Re-Actived for iVit#" . $item["iVit"], 'LoadApartments (' . $targetHost . ')');
					}
				}
			}
		}
	}
	
	logMsg('INFO', "Beginning to check for removed items", 'LoadApartments (' . $targetHost . ')');	
	$dbItemCount = count($curDbIvitsActive);
	for($i = 0; $i < $dbItemCount; $i++) {
		$ivit = $curDbIvitsActive[$i];
		if (!in_array($ivit, $latestIvits)) {
			/* mark as inactive in the DB */
			$urlMarkInactive = $baseUrl
				. "method=SET&function=apartmentsetactive&app_name=LoadApartmentListing&sessionid=$sessionid&active=0&ivit=$ivit";
			httpRequest($urlMarkInactive, 'GET');
			logMsg('INFO', "Marked ivit#" . $ivit . " as inactive", 'LoadApartments (' . $targetHost . ')');	
		}
	}
	logMsg('INFO', "Finished checking for removed items", 'LoadApartments (' . $targetHost . ')');	
		
	logMsg('INFO', "LoadApartments Finished", 'LoadApartments (' . $targetHost . ')');	
	
	function isWithinBoundaries($lon, $lat, $boundNeLon, $boundNeLat, $boundSwLon, $boundSwLat) {
		
		$withinLat = $lat >= $boundSwLat && $lat <= $boundNeLat;
		$withinLon = $lon >= $boundSwLon && $lon <= $boundNeLon;
		
		return $withinLon && $withinLat;
	}

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
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			array('Accept: application/json', 
			'Content-Type: application/json; charset=UTF-8',
			'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36'
			)
		);
	    $buffer = curl_exec($ch);

	    curl_close($ch);
	    return $buffer;
	}


?>