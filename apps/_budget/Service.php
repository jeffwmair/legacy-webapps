<?php

	require_once "./utils/conn.php";
	$conn = connect();
	
	$method = $_POST['Method'];
	$year = $_POST['Year'];
	$month = $_POST['Month'];
	$category = $_POST['Category'];
	$amount = $_POST['Amount'];
	$desc = $_POST['Description'];
	$request_body = file_get_contents('php://input');
	
	var_dump($request_body);
	
	
	echo "$method";
	var_dump($_POST);
	
?>