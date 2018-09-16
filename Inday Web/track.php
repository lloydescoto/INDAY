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
	
	function getAddress($latitude,$longitude){
    if(!empty($latitude) && !empty($longitude)){
        //Send request and receive json data by address
        $geocodeFromLatLong = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false'); 
        $output = json_decode($geocodeFromLatLong);
        $status = $output->status;
        //Get address from json data
        $address = ($status=="OK")?$output->results[0]->formatted_address:'';
        //Return address of the given latitude and longitude
        if(!empty($address)){
            return $address;
        }else{
            return false;
        }
    }else{
        return false;   
    }
    }
    $latitude = $_GET["latitude"];
    $longitude = $_GET["longitude"];
	$user = 3;
    $address = getAddress($latitude,$longitude);
    $address = $address?$address:'Not found';
	date_default_timezone_set('Asia/Hong_Kong');
	$date = date('Y-m-d H:i:s');
	$day = date('l');
	$stmt = $connect->prepare("INSERT INTO tracklogger(Latitude,Longitude,Place,Date,Day,UserID) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("sssssi",$latitude,$longitude,$address,$date,$day,$user);
    $stmt->execute();
    $stmt->close();
	$stmt2 = $connect->prepare("UPDATE Locate SET Status = 'OFF'");
	$stmt2->execute();
	$stmt2->close();
?>


