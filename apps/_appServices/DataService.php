<?php
	// 
	// $CODE_BAD_SESSION = 510;
	// $CODE_BAD_SESSION_PASS = 511;
	// $CODE_INVALID_ARGS = 512;
	// 
	// http://localhost/WebApps/_appServices/DataService.php?method=GET&function=session&pass={PASSWORD_HERE}&application=test
	// http://jefftron.com/WebApps/_appServices/DataService.php?method=GET&function=session&pass={PASSWORD_HERE}&application=test
	// http://jefftron.com/WebApps/_appServices/DataService.php?method=GET&function=heartbeats&application=test&&sessionid=417790327
	
	define('ARG_SESSION', 'sessionid');
	define('ARG_APP', 'application');
	define('ARG_PASS', 'pass');
	define('ARG_TIMESTAMP', 'timestamp');
	define('ARG_HEARTBEAT_INTERVAL', 'heartbeatinterval');
	define('ARG_MAXROWS', 'maxrows');
	define('ARG_MSG_TYPE', 'messagetype');
	define('ARG_MSG', 'message');
	define('ARG_APT_JSON', 'apartmentjson');
	define('ARG_APP_STATUS', 'status');
	define('ARG_USE_HTML', 'usehtml');
	define('MAX_SESSION_HEARTBEAT_INFO', 3600);
	

	define('CODE_BAD_SESSION', 510);
	define('CODE_BAD_SESSION_PASS', 511);
	define('CODE_INVALID_ARGS', 512);
			
	require_once "utils.php";
	
	$method = getRequestArgument('method', true);
	$function = getRequestArgument('function', true);
	call_user_func(strtolower($method) . $function);
		
	/*
	*
	*
	*
	*
	*/
	
	function getTest() {
		// echo encrypt('original', 'key');
		phpinfo();
	}
	
	function getTestRestService() {
		/* just a test service */
		$startNum = $_GET['startnum'];
		$chance = rand(1,10);
		if ($chance == 1) {
			header("HTTP/1.1 500 Internal Server Error");
			return;
		}
		$data = Array();
		for($i = $startNum; $i < ($startNum + 10000); $i++) {
			array_push($data, "Data $i");
		}
		$json = json_encode($data);
		returnJson($json);
	}
	
	function getDomains() {
		
		validateSession();
		$sql = "select * from crawler_domains;";
		$rows = getSqlResult($sql);
		$domains = '';
		while ($row = @ mysql_fetch_array($rows))
		{
			$domains .= $row['domain'];	
		}		
		
		echo $domains;
	}
	
	function getLog() {
		validateSession();
		$maxrows = getRequestArgument(ARG_MAXROWS, true);
		$msgType = getRequestArgument(ARG_MSG_TYPE, false);
		$whereClause = "";
		if (isset($msgType) && $msgType != 'ALL') {
			$whereClause = " where message_type = '$msgType' ";
		}
		$sql = "select app_name, timestamp, message_type, message from log $whereClause order by timestamp desc limit $maxrows;";
		$rows = getSqlResult($sql);
		$json = '[';
		while ($row = @ mysql_fetch_array($rows))
		{
			if ($json != '[') {
				$json .= ',';
			}
			$ts = $row['timestamp'];
			$msgtype = $row['message_type'];
			
			$msg = cleanJson($row['message']);
			$msg = str_replace('"', '\"', $msg);
			
			$app = $row['app_name'];
			$json .= "{ \"appname\":\"$app\", \"timestamp\":\"$ts\", \"messagetype\":\"$msgtype\", \"message\":\"$msg\" }";
		}
		$json .= ']';
		returnJson($json);
	}
	function setLog() {
		$app = getRequestArgument(ARG_APP, true);
		$time = getRequestArgument(ARG_TIMESTAMP, true);
		$msgType = getRequestArgument(ARG_MSG_TYPE, true);
		$msg = getRequestArgument(ARG_MSG, true);
		$sql = "insert into log (app_name, timestamp, message_type, message) values ('$app', '$time', '$msgType', substring('$msg' from 1 for 2000));";
		getSqlResult($sql);
		returnJson(getSuccessResponse());		
	}
	
	function setKeyRequest() {
		$key = getRequestArgument('key', true);
		$action = getRequestArgument('action', true);
		
		$sql = "select key_id from keyfetcher_keylist where keyname = \"$key\";";
		$result = getSqlResult($sql);
		$keyRow = @ mysql_fetch_array($result);
		$keyid = $keyRow["key_id"];
		$sql = '';
		if ($action = 'clear') {
			$sql = "delete from keyfetcher_requests where key_id = $keyid;";
		}
		else {
			$sql = "insert into keyfetcher_requests (time_stamp, key_id, complete) values (now(), $keyid, 0);";
		}
		$res = getSqlResult($sql);
		returnJson(getSuccessResponse($res));
	}
	function setKey() {
		$keyname = getRequestArgument('key', true);
		$sql = "insert ignore into keyfetcher_keylist (keyname) values (\"$keyname\");";
		$res = getSqlResult($sql);
		if ($res == 1) {
			returnJson(getSuccessResponse($res));
		}
		else
		{
			returnJson(getFailureResponse($res));			
		}
	}
	function setApartmentComment() {
		//validateSession(); shoudl really validate setting..  could put the pw in php code in the calling webpage
		$ivit = getRequestArgument('ivit', true);
		$comment = getRequestArgument('comment', true);

		// $comment = str_replace('"', '&quot;', $comment);

		$sql = "update apt_list set Comments = \"$comment\" where iVit = $ivit;";
		echo getSqlResult($sql);
	}
		
	function setApartmentItemList() {
		//validateSession(); shoudl really validate setting..  could put the pw in php code in the calling webpage
		$ivit = getRequestArgument('ivit', true);
		$list = getRequestArgument('list', true);
		
		$sql = "update apt_list set MyList = $list where iVit = $ivit;";
		echo getSqlResult($sql);
	}
		
	function setApartmentSetActive() {
		$active = getRequestArgument('active', true);
		$ivit = getRequestArgument('ivit', true);
		$sql = "update apt_list set IsActive = $active, statuschangetime=now() where iVit = $ivit;";
		echo getSqlResult($sql);
	}
	function setApartmentListing() {

		validateSession();
		$ivit = getRequestArgument('ivit', true);
		$lat = getRequestArgument('latitude', true);
		$lon = getRequestArgument('longitude', true);
		$desc = getRequestArgument('description', true);
		$add = getRequestArgument('address', true);
		$price = getRequestArgument('price', true);
		$pic = getRequestArgument('extpicurl', true);
		$rooms = getRequestArgument('bedrooms', true);
		$status = getRequestArgument('listingstatus', true);
		$intersection = getRequestArgument('intersection', true);
		$vid = getRequestArgument('video', true);
		$util = getRequestArgument('utilities', true);
		$features = getRequestArgument('features', true);
		$active = getRequestArgument('active', true);
		
		$list = 0;

		$sql = "insert ignore into apt_list (iVit, timestamp, Latitude, Longitude, Description, Address, Price, ExtPicUrl, Bedrooms, ListingStatus, Intersection, Video, Utilities, Comments, Features, IsActive, MyList)
			values ($ivit, now(), $lat, $lon, \"$desc\", \"$add\", $price, \"$pic\", $rooms, $status, \"$intersection\", $vid, $util, \"\", \"$features\", $active, $list)";
		$res = getSqlResult($sql);
			
		returnJson(getSuccessResponse());
	}
	function getApartmentIntersections() {
		$sql = "select intersection, count(*) as count from apt_list"
			. " group by intersection"
			. " having count(*) > 5 "
			. " order by count(*) desc "
			. " limit 40;";
		
		$rows = getSqlResult($sql);
		returnJson(cleanJson(convertSqlRowsToJson($rows)));
	}
	function getApartmentBoundaries() {
		$sql = "select * from apt_bounds where name = 'Default';";
		$rows = getSqlResult($sql);
		returnJson(convertSqlRowsToJson($rows));
	}
	function getApartmentListing() {
		
		$minprice = getRequestArgument('minprice', true);
		$maxprice = getRequestArgument('maxprice', true);
		$bedrooms = getRequestArgument('bedrooms', true);
		$status = getRequestArgument('status', true);
		$listid = getRequestArgument('listid', true);
		$ord = getRequestArgument('ordering', false);
		$hasComments = getRequestArgument('hascomments', false);
		$lastdays = getRequestArgument('lastdays', false);
		$intersection = getRequestArgument('intersection', true);
		$sw_lat = getRequestArgument('sw_lat', true);
		$sw_lon = getRequestArgument('sw_lon', true);
		$ne_lat = getRequestArgument('ne_lat', true);
		$ne_lon = getRequestArgument('ne_lon', true);
		
		$sql = 'select * from apt_list '
			. ' where Price >= ' . $minprice
			. ' and (Price <= ' . $maxprice . ' or ' . $maxprice .' = -1)'
			. ' and (Bedrooms = ' . $bedrooms . ' or ' . $bedrooms .' = -1)'
			. ' and (MyList = ' . $listid . ' or ' . $listid . ' = -1)'
			. ' and (IsActive = ' . $status . ' or '. $status . ' = -1)'
			. ' and (Latitude >= ' . $sw_lat . ' and Latitude <= ' . $ne_lat . ' and Longitude >= ' . $sw_lon . ' and Longitude <= ' . $ne_lon . ' ) ';
		
		if ($intersection != 'ANY') {
			$sql .= " and (Intersection = \"$intersection\") ";			
		}
			
		if ($lastdays != -1) {
			$sql .= " and (timestamp >= DATE_ADD(current_date(), INTERVAL -$lastdays DAY)) ";
		}
		
		if ($hasComments == 1) {
			$sql .= ' and not comments = "" ';
		} elseif ($hasComments == 2) {
			$sql .= ' and comments = ""';
		}
		
		if ($ord == 1) {
			$sql .= ' order by Price asc;';
		} elseif ($ord == 2) {
			$sql .= ' order by Price desc;';			
		} elseif ($ord == 3) {
			$sql .= ' order by Timestamp desc;';			
		} elseif ($ord == 4) {
			$sql .= ' order by Address asc;';
		}

		$rows = getSqlResult($sql);	
		returnJson(cleanJson(convertSqlRowsToJson($rows)));
	}
	
	function getCurrentTemperature() {
		// $useHtml = getRequestArgument(ARG_USE_HTML, false);
		$sql = "SELECT insert_time, temp_c_jeff, temp_c_toronto
		FROM temp_reading
		ORDER BY insert_time desc limit 1;";
		$rows = getSqlResult($sql);

		returnJson(cleanJson(convertSqlRowsToJson($rows)));
	}
	function getTemperature() {
		validateSession();
		$interval = getRequestArgument('days', true);
		$sql = '';
		if ($interval <= 15)
		{
			$sql = "SELECT insert_time as ins_time, temp_c_jeff, temp_c_toronto
			FROM temp_reading
			WHERE insert_time > DATE_ADD(current_date(), INTERVAL -$interval DAY)
			ORDER BY insert_time;";
		}
		else if ($interval <= 30)
		{
			$sql = "SELECT concat(year(insert_time), '-',
							month(insert_time), '-', 
							case 
			            		when (dayofmonth(insert_time) < 10) then concat('0', dayofmonth(insert_time))
			            		else dayofmonth(insert_time)
			          		end,
							' ', 
							case 
			            		when (hour(insert_time) < 10) then concat('0', hour(insert_time))
			            		else hour(insert_time)
			          		end,
			          		':00:00') as ins_time, 
							round(avg(temp_c_jeff), 2) as temp_c_jeff, round(avg(temp_c_toronto), 2) as temp_c_toronto
					FROM temp_reading
					WHERE insert_time > DATE_ADD(current_date(), INTERVAL -$interval DAY)
					GROUP BY dayofyear(insert_time),hour(insert_time) 
					ORDER BY insert_time ;";
		}
		else
		{
			$sql = "SELECT concat(year(insert_time), '-',
						month(insert_time), '-', 
						case 
		            		when (dayofmonth(insert_time) < 10) then concat('0', dayofmonth(insert_time))
		            		else dayofmonth(insert_time)
		          		end) as ins_time, 
						round(avg(temp_c_jeff), 2) as temp_c_jeff, round(avg(temp_c_toronto), 2) as temp_c_toronto
				FROM temp_reading
				WHERE insert_time > DATE_ADD(current_date(), INTERVAL -$interval DAY)
				GROUP BY dayofyear(insert_time) 
				ORDER BY insert_time;";
		}
		$res = getSqlResult($sql);
		returnJson(cleanJson(convertSqlRowsToJson($res)));
	
	}
	function getHeartbeat() {
		validateSession();
		$sql = "select s.sessionid, s.application_name, (UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(update_date)) as seconds_since, timestamp, update_interval_seconds, s.info from application_session s left join application_heartbeats hb on s.sessionid = hb.sessionid order by application_name, timestamp;";
		$rows = getSqlResult($sql);
		$json = '[';
		while ($row = @ mysql_fetch_array($rows))
		{
			if ($json != '[') {
				$json .= ',';
			}
			$appname = $row['application_name'];
			$seconds_since = $row['seconds_since'];
			if ($seconds_since > MAX_SESSION_HEARTBEAT_INFO) {
				$seconds_since = 'DISCONNECTED';
			}
			$ts = $row['timestamp'];
			$updateinterval = $row['update_interval_seconds'];
			$info = $row['info']; 
			$s = $row['sessionid'];
			$json .= "{ \"sessionid\":\"$s\", \"applicationname\":\"$appname\", \"secondssinceheartbeat\":\"$seconds_since\", \"sessiontimestamp\":\"$ts\", \"updateinterval\":\"$updateinterval\", \"info\": \"$info\" }";
		}
		$json .= ']';
		returnJson($json);
	}
	function setHeartbeat() {
		validateSession();
		$sessionid = getRequestArgument(ARG_SESSION, true);
		// $timestamp = getRequestArgument(ARG_TIMESTAMP, true);
		$heartbeatintervalseconds = getRequestArgument(ARG_HEARTBEAT_INTERVAL, true);
		
		/* has it ever heartbeated? */
		$sql = "SELECT * FROM application_heartbeats WHERE sessionid = $sessionid;";
		$rows = getSqlResult($sql);
		$alreadyExists = mysql_fetch_array($rows);
		if ($alreadyExists)
		{
			$sql = "UPDATE application_heartbeats SET update_date = now(), update_interval_seconds = $heartbeatintervalseconds
			 	WHERE sessionid = $sessionid;";
		}
		else
		{
			$sql = "INSERT INTO application_heartbeats values ($sessionid, now(), $heartbeatintervalseconds);";
		}
		$res = getSqlResult($sql);
	
		returnJson(getSuccessResponse($res));
	}
	function getCropPriceTopLocal() {
		$sql = "SELECT insert_time, update_date, location, old_crop, new_crop
		FROM crop_prices where location in ('Palmerston', 'Hensall')
		ORDER BY new_crop desc limit 1";
		
		$res = getSqlResult($sql);
		echo '<h1>Top End-of-day New Crop Price for Palmerston or Hensall</h1>';
		while ($row = @ mysql_fetch_array($res))
		{
			echo "<strong>Location:</strong> " . $row['location'] . "<br />";
			echo "<strong>Date:</strong> " . $row['update_date'] . "<br />";
			echo "<strong>New Crop:</strong> $" . $row['new_crop'] . "<br />";
			echo "<strong>Old Crop:</strong> $" . $row['old_crop'] . "<br />";
		}
	}
	function getCropPrices() {
		$interval = getRequestArgument('days', true);
		$location = getRequestArgument('location', true);

		$sql = "SELECT insert_time, update_date, old_crop, new_crop
		FROM crop_prices
		WHERE insert_time > DATE_ADD(current_date(), INTERVAL -$interval DAY)
		and location = '$location'
		ORDER BY insert_time; ";
		// echo $sql;
		
		$res = getSqlResult($sql);
		returnJson(convertSqlRowsToJson($res));
	}
	function getCropPriceLocations() {
		$localOnly = getRequestArgument('localonly', true);
		$sql = "select update_date from crop_prices order by insert_time desc limit 1";
		$res = getSqlResult($sql);
		$records = mysql_fetch_array($res);
		$latest = $records["update_date"];

		/* get the items for the dropdown list */
		$sql = "SELECT insert_time, update_date, location, old_crop, new_crop
		FROM crop_prices
		WHERE update_date = '$latest'
		and ((location = 'Palmerston' or location = 'Hensall') or $localOnly = 0)
		ORDER BY new_crop desc";

		$res = getSqlResult($sql);
		$json = '[';
		while ($row = @ mysql_fetch_array($res))
		{
			$loc = $row['location'];
			$old = $row['old_crop'];
			$new = $row['new_crop'];
			$theDate = $row['update_date'];
			if ($json != '[')
			{
				$json .= ',';
			}
			$json .= "{\"location\":\"$loc\",\"oldPrice\":$old,\"newPrice\":$new,\"updateDate\":\"$theDate\"}";
		}
		$json .= ']';
		returnJson($json);
	}
	function getBlackboardUpdates() {
		
		$pw = getRequestArgument('password', true);
		if ($pw != 'ryecs') {
			header("HTTP/1.1 500 Internal Server Error");
			return;
		}
		$coursesOnly = getRequestArgument('coursesonly', true);// $_GET['RyersonAnnouncementsOnly'];
		$numDays = getRequestArgument('days', true);
		// $useJson = getRequestArgument('json', true);
		
		if (empty($coursesOnly)) $coursesOnly = 0;

		$sql = "SELECT announcement_id, announcement_date, course, subject, announcement_text  
				FROM rye_announcements
				WHERE $coursesOnly = 0 or course not like 'Ryerson%'";
				
		if ($numDays != -1) {
			$sql .= " and (insert_time >= DATE_ADD(current_date(), INTERVAL -$numDays DAY)) ";
		}
				
		$sql .=	"ORDER by insert_time desc;";
		$result = getSqlResult($sql);

		$useJson = 0;
		if ($useJson == 1) {
			returnJson(cleanJson(convertSqlRowsToJson($result)));
		}
		else {
			header('Content-Type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
			echo '<announcements>'."\n";
			while ($row = @ mysql_fetch_array($result))
			{
				$content = str_replace(chr(34), '\'', $row['announcement_text']);
				$subject = str_replace(chr(34), '\'', $row['subject']);
				$subject = str_replace('&', '&amp;', $subject);
				echo "<announcement id=\"{$row['announcement_id']}\" date=\"{$row['announcement_date']}\" course=\"{$row['course']}\" subject=\"{$subject}\" ><![CDATA[$content]]></announcement>";
			}
			echo "</announcements>";
		}
	}
	
	function getKeyRequests() {
		validateSession();
		$sql = "SELECT distinct keyfetcher_keylist.keyname FROM keyfetcher_keylist INNER JOIN keyfetcher_requests "
						. " ON keyfetcher_keylist.key_id = keyfetcher_requests.key_id " 
						. " WHERE keyfetcher_requests.complete = 0;";
						
		$rows = getSqlResult($sql);
		returnJson(cleanJson(convertSqlRowsToJson($rows)));
		
	}
	function getKeyList() {
		$sql = "select key_id, keyname from keyfetcher_keylist;";		
		$rows = getSqlResult($sql);
		returnJson(cleanJson(convertSqlRowsToJson($rows)));
	}

	function deleteLog() {
		validateSession();
		$sql = "delete from log;";
		getSqlResult($sql);
		returnJson(getSuccessResponse());
	}
	function deleteKey() {
		$key = getRequestArgument('key', true);
		
		$sql = "select key_id from keyfetcher_keylist where keyname = \"$key\";";
		$result = getSqlResult($sql);
		$keyRow = @ mysql_fetch_array($result);
		$keyid = $keyRow["key_id"];
		
		$sql = "delete from keyfetcher_keylist where key_id = $keyid;";
		getSqlResult($sql);
		returnJson(getSuccessResponse());
	}
	
	function setCacheItem() {
		validateSession();
		$key = getRequestArgument('key', true);
		$val = getRequestArgument('value', true);
		$seconds = getRequestArgument('seconds', true);
		$maxrequests = getRequestArgument('maxrequests', false);
		if (!isset($maxrequests)) {
			$maxrequests = 9999;
		}
		$result = setCacheItemInternal($key, $val, $maxrequests, $seconds);
		returnJson($result);
	}
	
	function getCacheItem() {
		validateSession();
		$key = getRequestArgument('key', true);
		$result = getCacheItemInternal($key);
		echo $result;
	}
	
	function setCacheItemInternal($key, $val, $maxrequests, $seconds) {
		$sql1 = "delete from jeffcache where key_name = \"$key\";";
		getSqlResult($sql1);
		$expiry = time() + $seconds;
		$sqlIns = "insert into jeffcache (key_name, data, expiryUnixTime, requestCount, maxRequests) values (\"$key\",\"$val\", $expiry, 0, $maxrequests);";
		getSqlResult($sqlIns);
		return getSuccessResponse();
	}
	function getCacheItemInternal($key) {
		$sql1 = "update jeffcache set requestCount = requestCount + 1 where key_name = \"$key\";";
		getSqlResult($sql1);
		
		/* clear any expired */
		$sqlPurge = "delete from jeffcache where expiryUnixTime < " . time() . " or requestCount > maxRequests;";
		getSqlResult($sqlPurge);
		
		/* get the item in question */		
		$sqlGet = "select data from jeffcache where key_name = '$key';";
		$rows = getSqlResult($sqlGet);
		$row = @ mysql_fetch_array($rows);
		return $row['data'];
	}
	
	function setGymActivityLog() {
		validateSession();
		$activity = getRequestArgument('activity', true);
		$sets = getRequestArgument('sets', true);
		$reps = getRequestArgument('reps', true);
		$weight = getRequestArgument('weight', true);
		$sql = "insert into gym_log values (now(), \"$activity\", $sets, $reps, $weight);";
		getSqlResult($sql);
		returnJson(getSuccessResponse());
	}
	
	function getGymActivityLog() {
		$sql = "select timestamp, activity, sets, reps, weight from gym_log order by timestamp desc;";
		$rows = getSqlResult($sql);
		returnJson(cleanJson(convertSqlRowsToJson($rows)));
	}
	
	function getGymActivityList() {
		$sql = "select distinct activity from gym_log order by activity;";
		$rows = getSqlResult($sql);
		$json = '[';
		while ($row = @ mysql_fetch_array($rows))
		{
			if ($json != '[') {
				$json .= ',';
			}
			$activity = $row['activity'];
			$json .= "{ \"value\":\"$activity\", \"tokens\":[\"$activity\", \"all\"] }";
		}
		$json .= ']';
		returnJson($json);
	}
	

?>