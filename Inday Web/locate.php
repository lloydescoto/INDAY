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
	
	$stmt = $connect->prepare("SELECT * FROM locate");
    $stmt->execute();
	$result = $stmt->get_result();
	while($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		echo "[" . $row['Status'] . "]";
	}
?>