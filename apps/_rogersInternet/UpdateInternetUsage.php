<?php
	// require_once "./utils/conn.php";
	// $conn = connect();
	require_once "../_appServices/utils.php";
			
	$loginUrl = 'https://www.rogers.com/siteminderagent/forms/login.fcc'; //action from the login form
	$remotePageUrl = 'https://www.rogers.com/web/myrogers/internetUsageBeta'; //url of the page you want to save  
					  
	$loginFields ="USER=jeffwmair&signinPassword1Hp=Enter+Password&password={PASSWORD GOES}&TARGET=https%3A%2F%2Fwww.rogers.com%2Fweb%2FloginSuccess.jsp&SMAUTHREASON=0";
	$login = getUrl($loginUrl, 'post', $loginFields); //login to the site
	$remotePageFields = "actionTab=DayToDay";
	$internetUsagePage = getUrl($remotePageUrl, 'post', $remotePageFields); 

	preg_match_all('/bgcolor.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*/', $internetUsagePage, $dataRows);
	$arrLen = count($dataRows[0]);
	//echo "Matched rows: {$arrLen}" . "<br /><br />";
	for ($i = 0; $i < $arrLen; $i++) {
		$rowOfLabels = "{$dataRows[0][$i]}";	// this is a row of labels like <label>date</label>\n<label>####</label></label>####</label></label>####</label>
		preg_match_all('/Jan.*\d{4}|Feb.*\d{4}|March.*\d{4}|Apr.*\d{4}|May.*\d{4}|Jun.*\d{4}|Jul.*\d{4}|Aug.*\d{4}|Sep.*\d{4}|Oct.*\d{4}|Nov.*\d{4}|Dec.*\d{4}/', $rowOfLabels, $rowDate);
		preg_match_all('/\d.*\d/', $rowOfLabels, $rowUsages);
	
		//echo "{$rowOfLabels}";
		//echo "<br />";
		
		$usageDate = $rowDate[0][0];
		$dataDl = str_replace(',', '', $rowUsages[0][2]);
		$dataUl = str_replace(',', '', $rowUsages[0][3]);
		$dataTot = $rowUsages[0][4];
		
		// echo $dataDl . "<br/>";
		// echo $dataUl . "<br/>";
		
		date_default_timezone_set('UTC');
		$thisDate = date_parse("{$usageDate}");
		$startBillingPeriod = '';
		$endBillingPeriod  ='';
		if ($thisDate['day'] >= 21)
		{
			// goes into the next month's billing period
			// current month's billing period
			$endMonth = 0;
			$endYr = 0;
			$startMonth = 0;
			$startYr = 0;
			if ($thisDate['month'] == 1)
			{
				$startMonth = 1;
				$startYr = $thisDate['year'];
				$endYr = $thisDate['year'];
				$endMonth = 2;
			}
			else if ($thisDate['month'] == 12)
			{
				$startMonth = $thisDate['month'];
				$startYr = $thisDate['year'];
				$endYr = $thisDate['year']+1;	
				$endMonth = 1;					
			}
			else
			{
				$startMonth = $thisDate['month'];
				$startYr = $thisDate['year'];
				$endMonth = $thisDate['month'] + 1;
				$endYr = $startYr;
			}
		}
		else
		{
			// current month's billing period
			$endMonth = 0;
			$endYr = 0;
			$startMonth = 0;
			$startYr = 0;
			if ($thisDate['month'] == 1)
			{
				$startMonth = 12;
				$startYr = $thisDate['year'] - 1;
				$endYr = $thisDate['year'];
				$endMonth = 1;
			}
			else
			{
				$startMonth = $thisDate['month'] - 1;
				$startYr = $thisDate['year'];
				$endMonth = $thisDate['month'];
				$endYr = $startYr;
			}
		}
		$startBillingPeriod = "$startYr/$startMonth/21";
		$endBillingPeriod = "$endYr/$endMonth/20";
	
		$sql = "insert into daily_usage (day, insert_time, billing_start, billing_end, uploaded, downloaded, package_max_gb, package_charge_per_extra_gb) 
		SELECT STR_TO_DATE('{$usageDate}', '%M %d, %Y'), now(), '$startBillingPeriod', '$endBillingPeriod', $dataUl, $dataDl, 150, 2.0
		FROM dual
		WHERE NOT EXISTS (SELECT * FROM daily_usage WHERE day = STR_TO_DATE('{$usageDate}', '%M %d, %Y') );";
		
		$res1 = getSqlResult($sql);

	}
	
	unlink('cookies/cookies.txt');
	
	$userRefresh = $_GET['UserRefresh'];
	if (isset($userRefresh))
	{
		header( 'Location: http://www.jefftron.com/internet/' ) ;
	}
	
	function getUrl($url, $method='', $vars='', $cookieValue='') {
	    $ch = curl_init();
	    if ($method == 'post') {
	        curl_setopt($ch, CURLOPT_POST, 1);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
	    }
		curl_setopt($ch, CURLOPT_URL, $url);	
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_COOKIE, $cookieValue);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies/cookies.txt');
	    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies/cookies.txt');
	    $buffer = curl_exec($ch);
	    curl_close($ch);
	    return $buffer;
	}
?>