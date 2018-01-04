<?php
		/* just a test service */
		$startNum = $_GET['startnum'];
		$chance = rand(1,10);
		if ($chance == 1) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "This is an error message from the Service";
			return;
		}
		$data = Array();
		for($i = $startNum; $i < ($startNum + 10000); $i++) {
			array_push($data, "Data $i");
		}
		$json = json_encode($data);
		header('Content-Type: application/json');
		echo $json;
?>