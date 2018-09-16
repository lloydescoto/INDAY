<?php
	$server = "localhost";
	$dbUsername = "root";
	$dbPassword = "";
	$database = "indaydb";
	$connect = new mysqli($server, $dbUsername, $dbPassword, $database);

	if ($connect->connect_error)
	{
		die("Database Connection Failed:" . $connect->connect_error);
	}
	
	$distance = $_GET['distance'];
	$user = 3;
	$date = date('Y-m-d');
	$day = date('l');
	$stmt = $connect->prepare("INSERT INTO objectlogger(Distance,Date,UserID) VALUES (?,?,?)");
    $stmt->bind_param("dsi",$distance,$date,$user);
    $stmt->execute();
?>


