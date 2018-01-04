<?php
	require_once "../_appServices/utils.php";

	$loginUrl = 'https://www.fido.ca/web/Fido.portal?_nfpb=true&_windowLabel=FidoSignIn_1_1&FidoSignIn_1_1_actionOverride=%2Fcom%2Ffido%2Fportlets%2Fecare%2Faccount%2Fsignin%2FsignIn';
	$remotePageUrlStep1 = 'https://www.fido.ca/web/Fido.portal?_nfpb=true&_pageLabel=Ecare_MSSPostPaid'; //url of the page you want to save 
	$remotePageUrlStep2 = 'https://www.fido.ca/web/Fido.portal?_nfpb=true&_windowLabel=mobileSelfServe_1_2&mobileSelfServe_1_2_actionOverride=%2Fcom%2Ffido%2Fportlets%2Fecare%2FmobileSelfServeUsage%2FmanagePostPaidUsage';
	
	$loginFields ="FidoSignIn_1_1{actionForm.fidonumber}=jeffwmair81&FidoSignIn_1_1{actionForm.password}={PASSWORD GOES HERE}&FidoSignIn_1_1{actionForm.loginAsGAM}=true";
	$login = getUrl($loginUrl, 'post', $loginFields); //login to the site
	$dataUsagePageStep1 = getUrl($remotePageUrlStep1, 'post', ''); 
	$dataUsagePageStep2 = getUrl($remotePageUrlStep2, 'post', 'selectedUsageType=Data');
	
	// $dataUsagePageStep2 = getUrl('http://localhost/WebApps/IpadInternetUsageTestData.html', 'post', '');
	
	preg_match_all('/todayDate\">.*\<\/div\>/', $dataUsagePageStep2, $matchToday);
	$todayDate = str_ireplace('</div>','',$matchToday[0][0]);
	$todayDate = str_ireplace('todayDate">','',$todayDate);
	$todayDate = str_ireplace('Today,','',$todayDate);
	$todayDate = trim($todayDate);
	$todayDate = str_ireplace('&nbsp;','',$todayDate);
	
	preg_match_all('/Days into Cycle:.*\n.*day(s)?/', $dataUsagePageStep2, $daysIntoCycleMatch);
	$daysIntoCycle = $daysIntoCycleMatch[0][0];
	$daysIntoCycle = str_ireplace('</span>', '', $daysIntoCycle);
	$daysIntoCycle = str_ireplace('<span class="paddingLeft3px">', '', $daysIntoCycle);
	$daysIntoCycle = str_ireplace('Days into Cycle:', '', $daysIntoCycle);
	$daysIntoCycle = str_ireplace('days', '', $daysIntoCycle);
	$daysIntoCycle = str_ireplace('day', '', $daysIntoCycle);
	$daysIntoCycle = trim($daysIntoCycle);
	$daysIntoCycle = str_ireplace('&nbsp;', '', $daysIntoCycle);	
		
	preg_match_all('/Days remaining:.*\n.*day(s)?/', $dataUsagePageStep2, $daysRemainingMatch);
	$daysRemainingCycle = $daysRemainingMatch[0][0];
	$daysRemainingCycle = str_ireplace('</span>', '', $daysRemainingCycle);
	$daysRemainingCycle = str_ireplace('<span class="paddingLeft3px">', '', $daysRemainingCycle);
	$daysRemainingCycle = str_ireplace('Days remaining:', '', $daysRemainingCycle);
	$daysRemainingCycle = str_ireplace('days', '', $daysRemainingCycle);
	$daysRemainingCycle = str_ireplace('day', '', $daysRemainingCycle);	
	$daysRemainingCycle = trim($daysRemainingCycle);
	$daysRemainingCycle = str_ireplace('&nbsp;', '', $daysRemainingCycle);	
	
	preg_match_all('/Used:.*\n.*\n.*\n.*\n.*/', $dataUsagePageStep2, $usedMatch);
	$used = $usedMatch[0][0];
	$used = str_ireplace('</div>', '', $used);
	$used = str_ireplace('<div class="floatLeft paddingLeft3px">', '', $used);
	$used = str_ireplace('<label>', '', $used);
	$used = str_ireplace('</label>', '', $used);
	$used = str_ireplace('<span>', '', $used);
	$used = str_ireplace('<span >', '', $used);
	$used = str_ireplace('</span>', '', $used);
	$used = str_ireplace('<span></span>', '', $used);
	$used = str_ireplace('Used:', '', $used);
	$used = str_ireplace('MB', '', $used);
	$used = str_ireplace('&nbsp;', '', $used);
	$used = trim($used);

	// echo $todayDate . '<br />';
	// echo $daysIntoCycle . '<br />';
	// echo $daysRemainingCycle . '<br />';
	// echo $used;
	
	$sql = "insert into ipad_data (insert_time, today, days_passed, days_left, data_used) 
	SELECT now(), '{$todayDate}', {$daysIntoCycle}, {$daysRemainingCycle}, {$used} 
	FROM dual
	WHERE NOT EXISTS (SELECT * FROM ipad_data WHERE today = '$todayDate' and DATEDIFF(CURDATE(), insert_time) < 30);";
	// echo $sql;
	
	getSqlResult($sql);
				
	unlink('cookies/ipadDataUsageCookie.txt');
	
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
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies/ipadDataUsageCookie.txt');
	    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies/ipadDataUsageCookie.txt');
	    $buffer = curl_exec($ch);
	    curl_close($ch);
	    return $buffer;
	}
	
?>