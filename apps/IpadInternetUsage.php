<style type='text/css'>
	* { font-family:helvetica}
	div.jeff { padding-left:40px; border:2px solid #1B84E0; padding:20px; margin:15px}
	ul.jeff { list-style-type: square; margin-left:10px;padding-left:10px }
</style>
<?php
	
	echo '<h2>iPad Data Usage</h2>';
	echo '<h3>Current Month Usage</h3></div>';
	$loginUrl = 'https://www.fido.ca/web/Fido.portal?_nfpb=true&_windowLabel=FidoSignIn_1_1&FidoSignIn_1_1_actionOverride=%2Fcom%2Ffido%2Fportlets%2Fecare%2Faccount%2Fsignin%2FsignIn';
	$remotePageUrlStep1 = 'https://www.fido.ca/web/Fido.portal?_nfpb=true&_pageLabel=Ecare_MSSPostPaid'; //url of the page you want to save 
	$remotePageUrlStep2 = 'https://www.fido.ca/web/Fido.portal?_nfpb=true&_windowLabel=mobileSelfServe_1_2&mobileSelfServe_1_2_actionOverride=%2Fcom%2Ffido%2Fportlets%2Fecare%2FmobileSelfServeUsage%2FmanagePostPaidUsage';
	
	$loginFields ="FidoSignIn_1_1{actionForm.fidonumber}=jeffwmair81&FidoSignIn_1_1{actionForm.password}={PASSWORD HERE}&FidoSignIn_1_1{actionForm.loginAsGAM}=true";
	$login = getUrl($loginUrl, 'post', $loginFields); //login to the site
	$dataUsagePageStep1 = getUrl($remotePageUrlStep1, 'post', ''); 
	$dataUsagePageStep2 = getUrl($remotePageUrlStep2, 'post', 'selectedUsageType=Data');
	
	// $dataUsagePageStep2 = getUrl('http://localhost/WebApps/IpadInternetUsageTestData.html', 'post', '');
	
	preg_match_all('/todayDate\">.*\<\/div\>/', $dataUsagePageStep2, $matchToday);
	$todayDate = str_replace('</div>','',$matchToday[0][0]);
	$todayDate = str_replace('todayDate">','',$todayDate);
	
	preg_match_all('/Days into Cycle:.*\n.*day(s)?/', $dataUsagePageStep2, $daysIntoCycleMatch);
	$daysIntoCycle = $daysIntoCycleMatch[0][0];
	$daysIntoCycle = str_replace('</span>', '', $daysIntoCycle);
	$daysIntoCycle = str_replace('<span class="paddingLeft3px">', '', $daysIntoCycle);
	$daysIntoCycle = str_replace('Days into Cycle', 'Days into the billing cycle', $daysIntoCycle);
		
	preg_match_all('/Days remaining:.*\n.*day(s)?/', $dataUsagePageStep2, $daysRemainingMatch);
	$daysRemainingCycle = $daysRemainingMatch[0][0];
	$daysRemainingCycle = str_replace('</span>', '', $daysRemainingCycle);
	$daysRemainingCycle = str_replace('<span class="paddingLeft3px">', '', $daysRemainingCycle);
	$daysRemainingCycle = str_replace('Days remaining', 'Days remaining in the billing cycle', $daysRemainingCycle);
	
	preg_match_all('/Used:.*\n.*\n.*\n.*\n.*/', $dataUsagePageStep2, $usedMatch);
	$used = $usedMatch[0][0];
	$used = str_replace('</div>', '', $used);
	$used = str_replace('<div class="floatLeft paddingLeft3px">', '', $used);
	$used = str_replace('<label>', '<label style="font-weight:bold">', $used);
	// $used = str_replace('</label>', '', $used);
	$used = str_replace('<span>', '', $used);
	$used = str_replace('Used:', 'Data Used this billing cycle:', $used);	

	echo '<div class="jeff">';
	echo $todayDate . '<br />';
	echo $daysIntoCycle . '<br />';
	echo $daysRemainingCycle . '<br />';
	echo $used;
	echo '</div>';
		
	// echo $dataUsagePageStep2;
	echo '<h3>The Costs</h3>';
	echo '<div class="jeff">';
	echo '<div class="blueBorder">';
	echo '<ul class="jeff">';
	echo '<li>$10: between 0 and 150 MB</li>';
	echo '<li>$25: between 150 MB and 1,000 MB (1GB)</li>';
	echo '<li>$35: between 1,000 MB (1GB) and 5,000 MB (5GB)</li>';
	echo '<li>and for anything over 5,000 MB, each additional 1,000 MB costs another $10</li>';
	echo '</ul>';
	echo '<a href="http://www.fido.ca/web/content/monthly/ipad_plans">Here</a> are the plan details if you like</p>';
	echo '</div>';	
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