<html>
	<head>
	</head>
	<body>
		<?php
			require_once "../_appServices/utils.php";
			$loginPage = getUrl('https://cas.ryerson.ca/login?service=https%3A%2F%2Fmy.ryerson.ca%2FLogin');
			$ticketVal = getRyeLoginFormTicketVal($loginPage);
			$execVal = getRyeLoginFormExecutionVal($loginPage);	
				
			
			checkStringVarForContent('TicketVal', $ticketVal);
			checkStringVarForContent('ExecVal', $execVal);
			
			$postFields = "service=https://my.ryerson.ca/";
			$postFields = $postFields . "&Login&username=jeffrey.mair";
			$postFields = $postFields . "&password=";
			$postFields = $postFields . "&submit=Login&lt=$ticketVal";
			$postFields = $postFields . "&execution=$execVal";
			$postFields = $postFields . "&_eventId=submit";

			$loginUrl = "https://cas.ryerson.ca/login";
			$homepage = getUrl($loginUrl, 'post', $postFields);			
			
			preg_match_all('/key:.*\n.*\n.*\n.*}/', $homepage, $keyVal);
			// look at the htmlspecialchars($homepage) for all the source
			// echo htmlspecialchars($homepage);
			//	
			$getCoursesRawString = $keyVal[0][0];
			$getAnnouncementsRawString = $keyVal[0][1];
			
						
			checkStringVarForContent('GetCoursesRawString', $getCoursesRawString);
			checkStringVarForContent('GetAnnouncementsRawString', $getAnnouncementsRawString);
			
			$username = "jeffrey.mair";
			$getCourses_key = substr($getCoursesRawString, 6, 64);
		 	$getCourses_namespace = substr($getCoursesRawString, 121, 22);
			$getAnnouncements_key = substr($getAnnouncementsRawString, 6, 64);
			$getAnnouncements_namespace = substr($getAnnouncementsRawString, 121, 24);

			// course list
			// $postFields = "key=$getCourses_key&username=$username&namespace=$getCourses_namespace";
			// $courseList = getUrl('https://my.ryerson.ca/BbPortlet/courses/ws/getCourses.action', 'post', $postFields);
			// echo "<label style='font-size:1.5em;display:block;margin-bottom:15px'>Course List:</label>";
			// echo "<div style='padding-left:20px'>";
			// echo $courseList;
			// echo "</div>";
			// announcements
			$postFields = "key=$getAnnouncements_key&username=$username&namespace=$getAnnouncements_namespace";
			$announcements = getUrl('https://my.ryerson.ca/BbPortlet/announcements/ws/getAnnouncements.action', 'post', $postFields);
				
		 	$announcementsDontHide = str_replace("display: none", "display: inline", $announcements); // strip out the value attribute end-quote
		
			$allAnnouncements = getAnnouncementsTable($announcementsDontHide);
 			
			$numAnnouncements = count($allAnnouncements);
			for($i = 0; $i < $numAnnouncements; $i++) {
				$announceCourse = $allAnnouncements[$i][0];
				$announceDate = $allAnnouncements[$i][1];
				$announceSubject = $allAnnouncements[$i][2];
				$announceContent = $allAnnouncements[$i][3];
				echo 'Course:' . $announceCourse . "<br />";
				echo 'Date:' . $announceDate . "<br />";
				echo 'Subject:' . $announceSubject . "<br />";
				echo 'Content:' . $announceContent . "<br />";
				// file_put_contents ('tempOutputFile.txt', $announceContent);
				insertToDBIfNotExists($announceCourse, $announceDate, $announceSubject, $announceContent, $conn);	
			}
			
			unlink('cookies/cookies.txt');
		
			function insertToDBIfNotExists($course, $date, $subj, $announcmentContent, $conn)
			{
				// echo $course;
				// 	echo $date;
				// 	echo $subj;
				// 	echo $announcmentContent;
				// 	echo $conn;
				
				/* first check to see if the announcement aleady exists
				*  Based on announcement date, course, subject
				*/
				$sqlExists = "select * from rye_announcements where announcement_date = '$date'
					and course = '$course' and subject = '$subj' and announcement_text = '$announcmentContent';";
				// mysql_query("SET time_zone = '-4:00';", $conn);
				$res = getSqlResult($sqlExists);
				$existingRecords = mysql_fetch_array($res);
				if (!$existingRecords)
				{
					echo 'starting to insert!<br />';
					/* start transaction */
					mysql_query("BEGIN");
					$sqlAnnouncementContent = "INSERT INTO rye_announcements (announcement_date, insert_time, course, subject, announcement_text) values ('$date', now(), '$course', '$subj', '$announcmentContent');";
					// $sqlAnnouncementContent = "INSERT INTO rye_announcement_text (announcement_text) values ('$announcmentContent');";
					$res1 = getSqlResult($sqlAnnouncementContent);
					if (!$res1)
					{
						header('HTTP/1.1 500 Internal Server Error');
						echo "Failed to insert announcement:  $sqlAnnouncementContent";
					}
					// else
					// {
					// 	$announcementTextId = mysql_insert_id();
					// 	$sqlAnnouncment = "INSERT INTO rye_announcement (announcement_date, insert_time, course, subject, announcement_text_id) select '$date', now(), '$course', '$subj', $announcementTextId;";
					// 	$res2 = mysql_query($sqlAnnouncment, $conn);
					// 	if (!$res2)
					// 	{
					// 		header('HTTP/1.1 500 Internal Server Error');
					// 		echo "Failed to execute $sqlAnnouncment <br/>";
					// 	}	
					// }
				
					/* commit or rollback the transaction */
					if ($res1 and $res2)
					{
						mysql_query("COMMIT");
						// echo 'Success';
					}
					else
					{
						mysql_query("ROLLBACK");
						// echo "Failure; Res1: $res1, Res2: $res2";
					}
				}
				else
				{
					echo 'already exists<br />';
				}
			}
			
			function checkStringVarForContent($varName, $var)
			{
				if (strlen($var) == 0)
				{
					echo "$varName value not found! <br />";
				}
			}
			
			function getAnnouncementsTable($markup)
			{
				preg_match_all('/bbAnnouncementsTable".*\<\/table\>/s', $markup, $matches);	
				$rows = explode('/tr', $matches[0][0]);
				$course = '';
				$date = '';
				$subject = '';
				$content = '';
				$allMessages = array();
				for($i = 0; $i < count($rows); $i++) {
					preg_match_all('/\<td.*\<\/td\>/', $rows[$i], $cell);
					for ($j = 0; $j < count($cell[0]); $j++) {
						$cellContent = $cell[0][$j];
						if (strpos($cellContent,'bbacourse') !== false) {
							preg_match_all('/bbacourse.*\<\//', $cellContent, $match);
						    $course = $match[0][0];
							$course = str_replace("bbacourse\" colspan=\"2\">", "", $course);
							$course = str_replace("</", "", $course);
						}
						else if (strpos($cellContent,'bbadate') !== false) {
							preg_match_all('/bbadate.*\<\//', $cellContent, $match);
						    $date = $match[0][0];
							$date = str_replace("bbadate\">", "", $date);
							$date = str_replace("</", "", $date);
						}
						else if (strpos($cellContent, 'bbasubject') !== false) {
							preg_match_all('/href.*\<\/a/', $cellContent, $match);
						    $subject = $match[0][0];
							$subject = str_replace("href=\"#\">", "", $subject);
							$subject = str_replace("</a", "", $subject);
							$subject = str_replace("'", "\'", $subject);
						}
						else if (strpos($cellContent, 'bbacontent') !== false) {
							
							/* feb 8, looks like its easier just to leave the markup
								// in-place for the content area */
							// preg_match_all('/inline;\" >.*\<\/s/', $cellContent, $match);
						    // $content = $match[0][0];
						    // $content = str_replace("inline;\" >", "", $content);
						    // $content = str_replace("</s", "", $content);
							$content = $cellContent;
							$content = str_replace("'", '&#39;', $content);
							/* if we have the cell content, then we are making
							the assumption here that we have gathered the rest
							of the needed information */
							$msg = array();
							$msg[0] = $course;
							$msg[1] = $date;
							$msg[2] = $subject;
							$msg[3] = $content;
							array_push($allMessages, $msg);
						}
					}
				}
				return $allMessages;
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
				curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies/cookies.txt');
			    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies/cookies.txt');;
			    $buffer = curl_exec($ch);

			    curl_close($ch);
			    return $buffer;
			}

			function getRyeLoginFormTicketVal($page)
			{
				preg_match_all('/name="lt"\svalue=".+"/', $page, $valueLine);
//				preg_match_all('/[A-Z][A-Z]-\d{6}-.{30}/', $valueLine[0][0], $val);
				preg_match_all('/[A-Z][A-Z]-.+/', $valueLine[0][0], $val);	// gets all the way to the end of the value quote
				return str_replace("\"", "", $val[0][0]); // strip out the value attribute end-quote
			}

			function getRyeLoginFormExecutionVal($page)
			{
				preg_match_all('/name="execution"\svalue=".+"/', $page, $valueLine);
				preg_match_all('/e\d*s\d*/', $valueLine[0][0], $val);
				return $val[0][0];	
			}
			
		?>		
	</body>
</html>
