<?php
	require_once "./simple_html_dom.php";
	require_once "../_appServices/utils.php";
	
	$url = 'http://www.gfo.ca/Marketing/DailyCommodityReport/DailyCommodityReportStaticReport.aspx';
	// Create DOM from URL or file
	$html = file_get_html($url);
	// $contentForDate = $html->find('div.content > div > div > div');
	$contentForDate = $html->find('div.content > div > div');
	$date = $contentForDate[0]->find('b');
	$date = $date[0]->plaintext;
	$date = str_ireplace('FARM MARKET NEWS - ONTARIO COMMODITY REPORT FOR', '', $date);
	$date = trim($date);

	// $content = $html->find('div.content > div > div > div > b');
	$content = $html->find('div.content > div > div > b');

	$arrLen = count($content);
	$foundSoybeans = false;
	$finished = false;
	$locationType;
	for ($i = 0; $i < $arrLen; $i++) {
		if (!$finished)
		{
			$item = $content[$i];
			if (!$foundSoybeans)
			{
				if (strpos($item, 'soybeans') !== false) {
					$foundSoybeans = true;
				}
			}
			else
			{
				if (strpos($item, 'ELEVATORS') !== false) {
					$locationType = 'Elevator';
				}
				else if (strpos($item, 'PROCESSORS') !== false) {
					$locationType = 'Processor';
				}
				else if (strpos($item, 'TRANSFER') !== false) {
					$locationType = 'Transfer';
				} 
				else if (strpos($item, 'wheat') !== false) {
					$finished = true;
				}
				else
				{
					if ($locationType != '')
					{
						// preg_match_all('/^[a-zA-Z]*[0-9]{1}\.[0-9]{1}/', $item, $matches);
						preg_match_all('/\<b\>[A-Z][a-zA-Z]*[\s]*[a-zA-Z]*/', $item, $locMatches);
						if (count($locMatches) == 1)
						{
							$location = trim($locMatches[0][0]);
							$location = str_ireplace('<b>', '', $location);
							// var_dump($location);
							$dataRow = str_ireplace($location, '', $item);
							$dataRow = str_ireplace('</b>', '', $dataRow);
							$dataRow = str_ireplace('<b>', '', $dataRow);
							$dataRow = str_ireplace('&#160;', ' ', $dataRow);
							$dataRow = trim($dataRow);
							$dataElements = explode(' ', $dataRow);

							$dataElementCount = count($dataElements);
							
							// echo $item;
							// echo $dataElementCount;
							
							/* okay, if there are 29 elements, then I am
							* assuming that we have the data we want */
							if ($dataElementCount == 29)
							{
								$oldCropPricePerBu = $dataElements[21];
								$newCropPricePerBu = $dataElements[27];
								// echo "Old: $oldCropPricePerBu New: $newCropPricePerBu <br/>";
								
								$sql = "insert into crop_prices (insert_time, update_date, location_type, location, old_crop, new_crop) 
								SELECT current_date(), '$date', '$locationType', '$location', $oldCropPricePerBu, $newCropPricePerBu
								FROM dual
								WHERE NOT EXISTS (SELECT * FROM crop_prices WHERE update_date = '$date' and location_type = '$locationType' and location='$location' and DATEDIFF(CURDATE(), insert_time) < 300);";
								// echo $sql . '<br/>';
								
								getSqlResult($sql);

							}
						}
					}
				}
			}
		}
	}

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
		// curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies/beanPricesCookies.txt');
	    // curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies/beanPricesCookies.txt');;
	    $buffer = curl_exec($ch);

	    curl_close($ch);
	    return $buffer;
	}

?>