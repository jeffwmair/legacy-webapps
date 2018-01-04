<?php
	function connect() {
		$username = "jefftron_netdata";
		$password = "";
		$host = "localhost";	
		$DB = "jefftron_InternetUsage";

		if (!($conn = @ mysql_connect($host, $username, $password)))
			die("Unable to connect to MySQL at $host with the username $username.  Check your password and that the username has access to MySql at this host.");
		if (!(mysql_select_db($DB, $conn)))
			die("couldn't select $DB");

		return $conn;
	}
?>
