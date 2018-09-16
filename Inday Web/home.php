<?php
	$url=$_SERVER['REQUEST_URI'];
	header("Refresh: 60; URL=$url");
    $server = "localhost";
	$dbUsername = "root";
	$dbPassword = "";
	$database = "indaydb";
	
	$conn = new mysqli($server, $dbUsername, $dbPassword, $database);
	$longitudeArray = array();
	$latitudeArray = array();
	$gpsDataPlace = array();
	$gpsUniquePlace = array();
	$gpsDatePlace = array();
	if ($conn->connect_error) {
		die("Connect Failed:" . $conn->connect_error);
	}
    
    if(isset($_SESSION['username']))
    {
        $stmt = $conn->prepare("SELECT * FROM user WHERE AccountID = ?");
        $stmt->bind_param("i",$_SESSION['id']);
        if($stmt->execute())
        {
            $result = $stmt->get_result();
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $stmt->close();
            if($_SESSION['type'] == "User")
            {
                $stmt1 = $conn->prepare("SELECT * FROM gpslogger WHERE UserID = ?");
                $stmt1->bind_param("i",$row['UserID']);
                if($stmt1->execute())
                {
                    $gpsResult = $stmt1->get_result();
                }
                $stmt2 = $conn->prepare("SELECT * FROM gpslogger WHERE UserID = ?");
                $stmt2->bind_param("i",$row['UserID']);
                if($stmt2->execute())
                {
                    $gpsArrayResult = $stmt2->get_result();
                    while($arrayRowGPS = $gpsArrayResult->fetch_array(MYSQLI_ASSOC))
                    {
                        array_push($longitudeArray,$arrayRowGPS['Longitude']);
                        array_push($latitudeArray,$arrayRowGPS['Latitude']);
                    }
                }
                $stmt3 = $conn->prepare("SELECT * FROM gpslogger WHERE UserID = ?");
                $stmt3->bind_param("i",$row['UserID']);
                if($stmt3->execute())
                {
                    $gpsDataResult = $stmt3->get_result();
                    while($gpsDataResultRow = $gpsDataResult->fetch_array(MYSQLI_ASSOC))
                    {
                         array_push($gpsDataPlace,$gpsDataResultRow['Place']);
                         array_push($gpsDatePlace,$gpsDataResultRow['Day']);
                    }
                }
                $stmt4 = $conn->prepare("SELECT DISTINCT Place FROM gpslogger WHERE UserID = ?");
                $stmt4->bind_param("i",$row['UserID']);
                if($stmt4->execute())
                {
                    $gpsUniqueResult = $stmt4->get_result();
                    while($gpsUniqueRow = $gpsUniqueResult->fetch_array(MYSQLI_ASSOC))
                    {
                        array_push($gpsUniquePlace,$gpsUniqueRow['Place']);
                    }
                }
            }
            if($_SESSION['type'] == "Admin")
            {
                $stmt1 = $conn->prepare("SELECT * FROM account");
                if($stmt1->execute())
                {
                    $adminAccountResult = $stmt1->get_result();
                }
                $stmt1->close();
                $stmt2 = $conn->prepare("SELECT * FROM user");
                if($stmt2->execute())
                {
                    $adminUserResult = $stmt2->get_result();
                }
                $stmt2->close();
                $stmt3 = $conn->prepare("SELECT * FROM gpslogger");
                if($stmt3->execute())
                {
                    $adminGPSResult = $stmt3->get_result();
                }
                $stmt3->close();
                $stmt4 = $conn->prepare("SELECT * FROM objectlogger");
                if($stmt4->execute())
                {
                    $adminObjectResult = $stmt4->get_result();
                }
                $stmt4->close();
            }
        }
        else
        {
            header("Location: index.php");
        }
    } 
    else
    {
        header("Location: index.php");
    }
	
    if(isset($_POST['addAccountButton']))
	{
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$username = strtolower($firstname . $lastname);
		$password = $username;
		$contact = $_POST['contact'];
		$address = $_POST['address'];
		$type = $_POST['accountType'];
		$stmt = $conn->prepare("INSERT INTO account(Username,Password,Type) VALUES (?,?,?)");
		$stmt->bind_param("sss",$username,$password,$type);
		if($stmt->execute())
		{
			$stmt->close();
			$stmt2 = $conn->prepare("SELECT * FROM Account WHERE Username = ?");
			$stmt2->bind_param("s",$username);
			if($stmt2->execute())
			{
				$result2 = $stmt2->get_result();
				$row2 = $result2->fetch_array(MYSQLI_ASSOC);
				$stmt2->close();
				$stmt3 = $conn->prepare("INSERT INTO User(FirstName,LastName,Address,Contact,AccountID) VALUES (?,?,?,?,?)");
				$stmt3->bind_param("ssssi",$firstname,$lastname,$address,$contact,$row2['AccountID']);
				if($stmt3->execute())
				{
					$stmt3->close();
					$checkMessage = "Successfully Added";
				}
				else
				{
					$errorMessage = "Failed to Add";
				}
			}
			else
			{
				$errorMessage = "Failed to Add";
			}
		}
		else
		{
			$errorMessage = "Failed to Add";
		}
	}
	
	if(isset($_POST['locateButton']))
	{
		$stmt = $conn->prepare("UPDATE Locate SET Status = 'ON'");
		$stmt->execute();
		$stmt->close();
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>INDAY</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta content="" name="keywords">
  <meta content="" name="description">

  <!-- Favicons -->
  <link href="img/favicon.png" rel="icon">
  <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,700,700i|Montserrat:300,400,500,700" rel="stylesheet">

  <!-- Bootstrap CSS File -->
  <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Libraries CSS Files -->
  <link href="lib/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <link href="lib/animate/animate.min.css" rel="stylesheet">
  <link href="lib/ionicons/css/ionicons.min.css" rel="stylesheet">
  <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
  <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
  <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="lib/Chart.js/Chart.bundle.js"></script>
  <script src="lib/Chart.js/samples/utils.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Main Stylesheet File -->
  <link href="css/style.css" rel="stylesheet">
  <style>
	canvas {
		-moz-user-select: none;
		-webkit-user-select: none;
		-ms-user-select: none;
	}
	
	canvas1 {
		-moz-user-select: none;
		-webkit-user-select: none;
		-ms-user-select: none;
	}
	canvas2{
		-moz-user-select: none;
		-webkit-user-select: none;
		-ms-user-select: none;
	}
	</style>
</head>

<body>
  <!--==========================
    Header
  ============================-->
  <header id="header">
    <div class="container-fluid">
      <div id="logo" class="pull-left">
        <h1><a href="#intro" class="scrollto">INDAY</a></h1>
        <!-- Uncomment below if you prefer to use an image logo -->
        <!-- <a href="#intro"><img src="img/logo.png" alt="" title="" /></a>-->
      </div>
      <nav id="nav-menu-container">
        <ul class="nav-menu" >
		  <li >
		  <div class="dropdown">
			<button  style=" background-color: Transparent;  border: none; overflow: hidden; outline:none; " class="btn btn-primary " type="button" data-toggle="dropdown"><h4 id="UserNameDisplay"> <?php echo $row['FirstName'] . " " . $row['LastName']; ?> </h4></button>
			<ul class="dropdown-menu">
			<li onclick="hideMenu4()"><a href="#">Profile</a></li>
			<li><a href="logout.php">Logout</a></li>
			</ul>
		</div>
		  </li>
        </ul>
      </nav><!-- #nav-menu-container -->
    </div>
  </header><!-- #header -->
 <script>
		var color = Chart.helpers.color;
		var placeData = <?php echo json_encode($gpsDataPlace); ?>;
		var uniquePlaceData = <?php echo json_encode($gpsUniquePlace); ?>;
		var datePlaceData = <?php echo json_encode($gpsDatePlace); ?>;
		var placeCount = [];
		var mondayCountData = [];
		var tuesdayCountData = [];
		var wednesdayCountData = [];
		var thursdayCountData = [];
		var fridayCountData = [];
		var saturdayCountData = [];
		var sundayCountData = [];
		var count = 0;
		var mondayCount = 0;
		var tuesdayCount = 0;
		var wednesdayCount = 0;
		var thursdayCount = 0;
		var fridayCount = 0;
		var saturdayCount = 0;
		var sundayCount = 0;
		var config = {
			type: 'line',
			data: {
				labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
				datasets: []
			},
			options: {
				responsive: true,
				title: {
					display: true,
					text: 'Inday Day Chart'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Days'
						}
					}],
					yAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Value'
						}
					}]
				}
			}
		};
		var colorNames = Object.keys(window.chartColors);
		for(var i = 0;i < uniquePlaceData.length;i++)
		{
		    var colorName = colorNames[config.data.datasets.length % colorNames.length];
			var newColor = window.chartColors[colorName];
			var newDataset = {
				label: uniquePlaceData[i],
				backgroundColor: newColor,
				borderColor: newColor,
				data: [],
				fill: false
			};
		    count = 0
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x])
		        {
		            count++;
		        }
		    }
		    placeCount.push(count);
		    mondayCount = 0;
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x] && datePlaceData[x] == "Monday")
		        {
		            mondayCount++;
		        }
		    }
		    mondayCountData.push(mondayCount);
		    tuesdayCount = 0;
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x] && datePlaceData[x] == "Tuesday")
		        {
		            tuesdayCount++;
		        }
		    }
		    tuesdayCountData.push(tuesdayCount);
		    wednesdayCount = 0;
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x] && datePlaceData[x] == "Wednesday")
		        {
		            wednesdayCount++;
		        }
		    }
		    wednesdayCountData.push(wednesdayCount);
		    thursdayCount = 0;
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x] && datePlaceData[x] == "Thursday")
		        {
		            thursdayCount++;
		        }
		    }
		    thursdayCountData.push(thursdayCount);
		    fridayCount = 0;
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x] && datePlaceData[x] == "Friday")
		        {
		            fridayCount++;
		        }
		    }
		    fridayCountData.push(fridayCount);
		    saturdayCount = 0;
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x] && datePlaceData[x] == "Saturday")
		        {
		            saturdayCount++;
		        }
		    }
		    saturdayCountData.push(saturdayCount);
		    sundayCount = 0;
		    for(var x = 0;x < placeData.length;x++)
		    {
		        if(uniquePlaceData[i] == placeData[x] && datePlaceData[x] == "Sunday")
		        {
		            sundayCount++;
		        }
		    }
		    sundayCountData.push(sundayCount);
		    for (var index = 0; index < config.data.labels.length; index++) {
		        if(index == 0)
		        {
				    newDataset.data.push(sundayCount);
		        }
		        if(index == 1)
		        {
		            newDataset.data.push(mondayCount);
		        }
		        if(index == 2)
		        {
		            newDataset.data.push(tuesdayCount);
		        }
		        if(index == 3)
		        {
		            newDataset.data.push(wednesdayCount);
		        }
		        if(index == 4)
		        {
		            newDataset.data.push(thursdayCount);
		        }
		        if(index == 5)
		        {
		            newDataset.data.push(fridayCount);
		        }
		        if(index == 6)
		        {
		            newDataset.data.push(saturdayCount);
		        }
			}
			config.data.datasets.push(newDataset);
		}
	    var dayChartData = {
			labels: uniquePlaceData,
			datasets: [{
				label: 'Sunday',
				backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
				borderColor: window.chartColors.red,
				borderWidth: 1,
				data: sundayCountData
			},{
				label: 'Monday',
				backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
				borderColor: window.chartColors.blue,
				borderWidth: 1,
				data: mondayCountData
			},{
				label: 'Tuesday',
				backgroundColor: color(window.chartColors.orange).alpha(0.5).rgbString(),
				borderColor: window.chartColors.orange,
				borderWidth: 1,
				data: tuesdayCountData
			},{
				label: 'Wednesday',
				backgroundColor: color(window.chartColors.violet).alpha(0.5).rgbString(),
				borderColor: window.chartColors.violet,
				borderWidth: 1,
				data: wednesdayCountData
			},{
				label: 'Thursday',
				backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
				borderColor: window.chartColors.blue,
				borderWidth: 1,
				data: thursdayCount
			},{
				label: 'Friday',
				backgroundColor: color(window.chartColors.purple).alpha(0.5).rgbString(),
				borderColor: window.chartColors.purple,
				borderWidth: 1,
				data: fridayCountData
			},{
				label: 'Saturday',
				backgroundColor: color(window.chartColors.yellow).alpha(0.5).rgbString(),
				borderColor: window.chartColors.yellow,
				borderWidth: 1,
				data: saturdayCountData
			}]
		};
		
		var barChartData = {
			labels: uniquePlaceData,
			datasets: [{
				label: 'Track',
				backgroundColor: color(window.chartColors.red).alpha(0.5).rgbString(),
				borderColor: window.chartColors.red,
				borderWidth: 1,
				data: placeCount
			}]
		};

		window.onload = function() {
			var ctx = document.getElementById('canvas').getContext('2d');
			var cty = document.getElementById('canvas1').getContext('2d');
			window.myBar = new Chart(ctx, {
				type: 'bar',
				data: barChartData,
				options: {
					responsive: true,
					legend: {
						position: 'top',
					},
					title: {
						display: true,
						text: 'Inday Track Chart'
					}
				}
			});
			window.myBar1 = new Chart(cty, {
				type: 'bar',
				data: dayChartData,
				options: {
					responsive: true,
					legend: {
						position: 'top',
					},
					title: {
						display: true,
						text: 'Inday Day Chart'
					}
				}
			});
			var ctw = document.getElementById('canvas2').getContext('2d');
			window.myLine = new Chart(ctw, config);

		};
	</script>
  <main id="main">
<div id="menu">
    <section id="about" style="top:40px">
      <div class="container">
        <header class="section-header">
        </header>
        <div class="row about-cols">
         <?php 
         if($_SESSION['type'] == "User")
         { ?>
          <div style="cursor: pointer;" class="col-md-4 wow fadeInUp" onclick="hideMenu()">
            <div class="about-col">
              <div class="img">
                <img src="img/3.jpg" alt="" class="img-fluid">
                <div class="icon"><i class="ion-android-map"></i></div>
              </div>
              <h2 class="title"><a href="#"> MAP </a></h2>
              <p>
				Track your love love ones using google map API. This offers locating the device's user location that can help on tracking them if they are lost.
              </p>
            </div>
          </div>

          <div style="cursor: pointer;" class="col-md-4 wow fadeInUp" data-wow-delay="0.1s" onclick="hideMenu1()">
            <div class="about-col">
              <div class="img">
                <img src="img/4.png" alt="" class="img-fluid">
                <div class="icon"><i class="ion-wand"></i></div>
              </div>
              <h2 class="title"><a href="#"> Location History </a></h2>
              <p>
			  Know the user's location and their records. Here you can identify the user most visited location on specific day of the week including their time when they record their location
              </p>
            </div>
          </div>

          <div style="cursor: pointer;" class="col-md-4 wow fadeInUp" data-wow-delay="0.2s" onclick="hideContact()">
            <div class="about-col">
              <div class="img">
                <img src="img/2.jpg" alt="" class="img-fluid">
                <div class="icon"><i class="ion-android-contacts"></i></div>
              </div>
              <h2 class="title"><a href="#"> Contact Us </a></h2>
              <p>
			  Contact the developers. You can leave suggestion, recommendation and feedback for the device. You can also report any technical issues about your device here.
              </p>
            </div>
          </div>
        <?php } ?>
        <?php 
        if($_SESSION['type'] == "Admin")
        {
        ?>
        <div style="cursor: pointer;" class="col-md-4 wow fadeInUp" onclick="hideMenuAdmin()">
            <div class="about-col">
              <div class="img">
                <img src="img/3.jpg" alt="" class="img-fluid">
                <div class="icon"><i class="ion-android-map"></i></div>
              </div>
              <h2 class="title"><a href="#"> Manage Accounts </a></h2>
              <p>
				Manage the database through adding accounts and users and able to lessen problems to the system
              </p>
            </div>
          </div>
		  
		  <div style="cursor: pointer;" class="col-md-4 wow fadeInUp" data-wow-delay="0.1s" onclick="hideMenu1Admin()">
            <div class="about-col">
              <div class="img">
                <img src="img/4.png" alt="" class="img-fluid">
                <div class="icon"><i class="ion-wand"></i></div>
              </div>
              <h2 class="title"><a href="#"> Database Records </a></h2>
              <p>
			  View database records listed to the system like accounts, users, gps logger, and object logger
              </p>
            </div>
          </div>
        <?php } ?>
        </div>

      </div>
    </section><!-- #about -->
</div>
<!--==========================
    MAP
  ============================-->
<?php
if($_SESSION['type'] == "User")
{
?>
<div id = "trackingMap" style="display:none; background: url('../img/1.jpg') center top no-repeat fixed;">
<style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #floating-panel {
        position: absolute;
        top: 10px;
        left: 25%;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }
      #floating-panel {
        position: absolute;
        top: 5px;
        left: 50%;
        margin-left: -180px;
        width: 350px;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
      }
      #latlng {
        width: 225px;
      }
    </style>
	
	<div style="width:100%;height:400px;">  
	</br></br></br></br>
    <div id="map"></div>
    <script>
      function initMap() {
        var longitude = <?php echo json_encode($longitudeArray); ?>;
		var latitude = <?php echo json_encode($latitudeArray); ?>;
	    var myLatLng = {lat: 15.169299, lng: 120.585937};
		
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 16,
          center: myLatLng
	
        });
		  
		  
        var geocoder = new google.maps.Geocoder;
        var infowindow = new google.maps.InfoWindow;
        var longtiLength = longitude.length;
		var latiLength = latitude.length;
		geocodeLatLng(geocoder, map, infowindow, latitude[latiLength-1], longitude[longtiLength-1]);

    }
      function geocodeLatLng(geocoder, map, infowindow, lat, lng) {
		var input_lat = lat.toString();
		var input_lng = lng.toString();
		var myLatLng = {lat: 15.226131, lng: 120.577690};
		 
        var input = input_lat+","+input_lng;
        var latlngStr = input.split(',', 2);
        var latlng = {lat: parseFloat(latlngStr[0]), lng: parseFloat(latlngStr[1])};
        geocoder.geocode({'location': latlng}, function(results, status) {
          if (status === 'OK') {
            if (results[0]) {
			   
               var i;
			   var text = ""
			   for (i = 0; i < 7; i++) {
					text += results[i].formatted_address + "<br>";
					}	
			  var place_address = text
              map.setZoom(16);

			  
              var marker = new google.maps.Marker({
                position: latlng,
                map: map

				
              });
            } else {
              window.alert('No results found');
            }
          } else {
            window.alert('Geocoder failed due to: ' + status);
          }
        });
      }
	  
	  
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDTamUVOavc4IztoXRQldwmmc6fMa-sYM4&callback=initMap">
    </script>
	<center>

<!--------- Test the multiple plotting of coordinates  ---------


	<table>
	<tr>
	<th><input id="lat" type="text" value="15.169877"> </th>
	<th><input id="lng" type="text" value="120.584452"></th>
	<th> <input id="submit" type="button" value="Plot"> </th>
	</tr>
	</table>
--------------- LLOYD ALISIN MO NALANG To HAHAHA  ------------>	
		
		
	</br></br>
	</center>
	<form style="padding-left:10px" method="POST">
	<button name="locateButton" style="width: 200px;">Locate</button>
	</form>
	<div style="padding-right: 40px; padding-top: 10px" class="float"> <input onclick="hideMap()" style="float: right; width: 200px" type="button" class="btn btn-success btn-lg" value=" ◀ Back"></div>
</div>
	
</div>

<!--==========================
    Data Analytics
  ============================-->
<div id = "tableData" style="display:none; padding-top:120px">
<div class="container">

  <center><table width="100%">
  <thead>
      <tr>
        <th width="100%"><h2>GPS Logger</h2> </th>	
      </tr>
    </thead>
  </table></center>
     
   
  <table class="table table-striped">
    <thead>
      <tr>
        <th>GPS ID</th>
		<th>Date</th>
        <th>Longitude</th>
        <th>Latitude</th>
		<th>Place</th>
      </tr> 
    </thead>
    <tbody>
    <?php
    while($gpsRow = $gpsResult->fetch_array(MYSQLI_ASSOC))
    {
    ?>
      <tr>
        <td><?php echo $gpsRow['GPSID']; ?></td>
		<th><?php echo $gpsRow['Date']; ?></th>
        <td><?php echo $gpsRow['Longitude']; ?></td>
        <td><?php echo $gpsRow['Latitude']; ?></td>
		<td><?php echo $gpsRow['Place']; ?></td>
      </tr>
    <?php
    } ?>
    </tbody>
  </table>
</div>
	<div style="padding-right: 40px; padding-top: 10px" class="float"> <input onclick="hideTables()" style="float: right; width: 200px" type="button" class="btn btn-success btn-lg" value=" ◀ Back"></div>
 <div id="container" style="width: 100%;">
		    <canvas id="canvas"></canvas>
	    </div>
 <div id="container" style="width: 100%;">
		    <canvas id="canvas1"></canvas>
	    </div>
 <div style="width:100%;">
    		<canvas id="canvas2"></canvas>
    	</div>
</div>
<?php } ?>
<!--==========================
    Contact
  ============================-->

<div id = "contactForm" style="display:none; padding-top:120px">

<div class="container">
<h2>Leave a Message</h2>
    <div class="row">
        <div class="col-md-8">
            <div class="well well-sm">
                <form>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">
                                Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter name" required="required" />
                        </div>
                        <div class="form-group">
                            <label for="email">
                                Email Address</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span>
                                </span>
                                <input type="email" class="form-control" id="email" placeholder="Enter email" required="required" /></div>
                        </div>
                        <div class="form-group">
                            <label for="subject">
                                Subject</label>
                            <select id="subject" name="subject" class="form-control" required="required">
                                <option value="na" selected="">Choose One:</option>
                                <option value="service">General Customer Service</option>
                                <option value="suggestions">Suggestions</option>
                                <option value="product">Product Support</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">
                                Message</label>
                            <textarea name="message" id="message" class="form-control" rows="9" cols="25" required="required"
                                placeholder="Message"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary pull-right" id="btnContactUs">
                            Send Message</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <form>
            <legend><span class="glyphicon glyphicon-globe"></span> Our Social Media Accounts</legend>
            <address>
                <strong>Email</strong><br>
                Inday_Device@gmail.com<br>
				<strong>Facebook</strong><br>
                https://www.facebook.com/IndayDevice<br>
				<strong>Twitter</strong><br>
                @Official_IndayDevice<br>
            </address>
      
            </form>
        </div>
    </div>
</div>
	<div style="padding-right: 40px; padding-top: 10px" class="float"> <input onclick="hideContact()" style="float: right; width: 200px" type="button" class="btn btn-success btn-lg" value=" ◀ Back"></div>

</div>

<!--==========================
    Profile
  ============================-->

<div id = "profile" style="display:none; padding-top:140px">

<center>
	<table >
	
	<tr>
	<th style="text-align: right;"> <h2> View Profile : </h2> </th>
	<th> </th>
	</tr>
	
	<tr>
	<th  height="50px" style="text-align: right;"> User ID :  </th>
	<th style="padding-left : 80px"> <?php echo $row['UserID']; ?> </th>
	</tr>
	
	<tr>
	<th height="50px" style="text-align: right"> First Name : </th>
	<th style="padding-left : 80px"> <?php echo $row['FirstName']; ?> </th>
	</tr>
	
	<tr>
	<th height="50px" style="text-align: right"> Last Name : </th>
	<th style="padding-left : 80px"> <?php echo $row['LastName']; ?> </th>
	</tr>
	
	<tr>
	<th height="50px" style="text-align: right"> Address :  </th>
	<th style="padding-left : 80px"> <?php echo $row['Address']; ?> </th>
	</tr>
	
	
	<tr>
	<th height="50px" style="text-align: right"> Contact : </th>
	<th style="padding-left : 80px"> <?php echo $row['Contact']; ?> </th>
	</tr>
	
	<tr>
	<th height="50px" style="text-align: right"> Account ID : </th>
	<th style="padding-left : 80px"> <?php echo $row['AccountID']; ?> </th>
	</tr>
	
	</table>
</center>	
	<div style="padding-right: 40px; padding-top: 10px" class="float"> <input onclick="hideProfile()" style="float: right; width: 200px" type="button" class="btn btn-success btn-lg" value=" ◀ Back"></div>


</div>




<!---------------------------------------------------->
<!--==========================
    Settings
  ============================-->

<?php

if($_SESSION['type'] == "Admin")
{
?>
<div id = "manageAccountAdmin" style="display:none; padding-top: 100px">

	<div class="container">
  
  <div class="tab-content">
  <h1>Add User Account</h1>
 <!---- <form method="POST" autocomplete="off">  ---->
   
	  
	  
	  
	  <div style=" width: 900px">
					<div class="form-group">
                            <form method="POST" autocomplete="off">
                            <div class="input-group">
                                
                                <select name="accountType" style="width: 250px; padding-left:30px; height:40px; float:right; padding-right: 30px" id="subject" name="subject" class="form-control" required="required">
                                <option value="na" selected="" disabled>Account Type</option>
								<option value="User">User</option>
                                <option value="Admin">Admin</option>
                                
                                
								
								
								</select>
                        </div>
	  
	  
                    <div style=" width: 900px">
                         <div class="form-group">
                            <label for="firstName">
                                First Name</label>
                            <div class="input-group">
                                
                                <input type="text" class="form-control" name="firstname" placeholder="First name" required="required" /></div>
                        </div>
						
						  <div class="form-group">
                            <label for="lastName">
                                Last Name</label>
                            <div class="input-group">
                                
                                <input type="text" class="form-control" name="lastname" placeholder="Last Name" required="required" /></div>
                        </div>
						
						
						<div class="form-group">
                            <label for="Contact">
                                Contact No.</label>
                            <div class="input-group">
                                
                                <input type="text" class="form-control" name="contact" placeholder="Contact" required="required" /></div>
                        </div>
						
                        
						
						<div class="form-group">
                            <label for="address">
                                Address</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="address" placeholder="Address" required="required" /></div>
                        </div>
						
						
						
						 <div class="col-md-12">
                        <button name="addAccountButton" style="width:200px" type="submit" class="btn btn-primary pull-right" id="btnContactUs">
                            Register</button>
                    </div>
					
					</div>
					</form>
    </div>
<!------	</form>    ------>
	
	  
	  
	  
    </div>
	  
	  
	  
	  
	  
	  
					
  </div>
</div>
	<div style="padding-right: 40px; padding-top: 10px" class="float"> <input onclick="hideMenuAdmin()" style="float: right; width: 200px" type="button" class="btn btn-success btn-lg" value=" ◀ Back"></div>

</div>


<div id = "databaseRecordAdmin" style="display:none; padding-top:120px">



<div class="container">
  <h2>Database Record</h2>
  <ul class="nav nav-tabs">
    <li style="padding-right: 30px" class="active"><a data-toggle="tab" href="#home">Accounts</a></li>
    <li style="padding-right: 30px"><a data-toggle="tab" href="#menu1">User</a></li>
    <li style="padding-right: 30px"><a data-toggle="tab" href="#menu2">Object Logger</a></li>
    <li style="padding-right: 30px"><a data-toggle="tab" href="#menu3">GPS Logger</a></li>
  </ul>

  <div class="tab-content">
    <div id="home" class="tab-pane fade in active">
      <div class="container">
  <h2>Account</h2>           
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Account ID</th>
        <th>Username</th>
        <th>Password</th>
		<th>Type</th>
      </tr>
    </thead>
    <tbody>
    <?php
    while($adminAccountRow = $adminAccountResult->fetch_array(MYSQLI_ASSOC))
    {
    ?>
      <tr>
        <td><?php echo $adminAccountRow['AccountID']; ?></td>
        <td><?php echo $adminAccountRow['Username']; ?></td>
        <td><?php echo $adminAccountRow['Password']; ?></td>
		<td><?php echo $adminAccountRow['Type']; ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
    </div>
	
    <div id="menu1" class="tab-pane fade">
      <div class="container">
  <h2>User</h2>           
  <table class="table table-striped">
    <thead>
      <tr>
        <th>User ID</th>
        <th>First Name</th>
        <th>Last Name</th>
		<td>Address</td>
		<th>Contact</th>
		<td>Account ID</td>
      </tr>
    </thead>
    <tbody>
    <?php
    while($adminUserRow = $adminUserResult->fetch_array(MYSQLI_ASSOC))
    {
    ?>
      <tr>
        <td><?php echo $adminUserRow['UserID']; ?></td>
        <td><?php echo $adminUserRow['FirstName']; ?></td>
        <td><?php echo $adminUserRow['LastName']; ?></td>
		<td><?php echo $adminUserRow['Address']; ?></td>
		<td><?php echo $adminUserRow['Contact']; ?></td>
        <td><?php echo $adminUserRow['AccountID']; ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
    </div>
	
    <div id="menu2" class="tab-pane fade">
     <div class="container">
  <h2>Object Logger</h2>           
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Object ID</th>
        <th>Distance</th>
        <th>Date</th>
		<th>User ID</th>
      </tr>
    </thead>
    <tbody>
    <?php
    while($adminObjectRow = $adminObjectResult->fetch_array(MYSQLI_ASSOC))
    {
    ?>
      <tr>
        <td><?php echo $adminObjectRow['ObjectID']; ?></td>
		<td><?php echo $adminObjectRow['Distance']; ?></td>
        <td><?php echo $adminObjectRow['Date']; ?></td>
		<td><?php echo $adminObjectRow['UserID']; ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
    </div>
	
    <div id="menu3" class="tab-pane fade">
      <div class="container">
  <h2>GPS Logger</h2>           
  <table class="table table-striped">
    <thead>
      <tr>
        <th>GPS ID</th>
        <th>Longitude</th>
        <th>Latitude</th>
		<th>Place</th>
      </tr>
    </thead>
    <tbody>
    <?php
    while($adminGPSRow = $adminGPSResult->fetch_array(MYSQLI_ASSOC))
    {
    ?>
      <tr>
        <td><?php echo $adminGPSRow['GPSID']; ?></td>
        <td><?php echo $adminGPSRow['Longitude']; ?></td>
        <td><?php echo $adminGPSRow['Latitude']; ?></td>
		<td><?php echo $adminGPSRow['Place']; ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
    </div>
  </div>
</div>



	<div style="padding-right: 40px; padding-top: 10px" class="float"> <input onclick="hideMenu1Admin()" style="float: right; width: 200px" type="button" class="btn btn-success btn-lg" value=" ◀ Back"></div>

</div>
<?php } ?>


  </main>

  <!--==========================
    Footer
  ============================-->
  

 <script>
 
 
function hideMenu() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("trackingMap");
	
    if (hide.style.display === "none") {
        hide.style.display = "block";
		show.style.display = "none";
    } else {
        hide.style.display = "none";
		show.style.display = "block";
    }
}

function monthChange() {
    var month = document.getElementById("subjectMonth").value;
	var date = document.getElementById("subjectDate");
	date.options.length = 0;
	
		if (month == "January"||month == "March"||month == "May"||month == "July"||month == "August"||month == "October"||month == "December"){
		for (var i = 1; i<=31; i++){
		var opt = document.createElement('option');
		opt.value = i;
		opt.innerHTML = i;
		date.appendChild(opt);
}
			
		}
		
		else if (month == "February"){
		for (var i = 1; i<=28; i++){
		var opt = document.createElement('option');
		opt.value = i;
		opt.innerHTML = i;
		date.appendChild(opt);
}
			
		}
		
		else {
		for (var i = 1; i<=30; i++){
		var opt = document.createElement('option');
		opt.value = i;
		opt.innerHTML = i;
		date.appendChild(opt);
}
			
		}
  
}

function hideMenu1() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("tableData");
	
    if (hide.style.display === "none") {
        hide.style.display = "block";
		show.style.display = "none";
    } else {
        hide.style.display = "none";
		show.style.display = "block";
    }
}

function hideMenu2() {
    var hide = document.getElementById("menu");
    var hide1 = document.getElementById("UserNameDisplay");
	var show = document.getElementById("settings");
	
    if (hide.style.display === "none") {
        hide.style.display = "block";
        hide1.style.display = "block";
		show.style.display = "none";
    } else {
        hide.style.display = "none";
        hide1.style.display = "none";
		show.style.display = "block";
    }
}

function hideSettings() {
    var hide = document.getElementById("menu");
	var hide1 = document.getElementById("UserNameDisplay");
	var show = document.getElementById("settings");
	
    if (show.style.display === "none") {
        show.style.display = "block";
		hide.style.display = "none";
		hide1.style.display = "none";
    } else {
        show.style.display = "none";
		hide.style.display = "block";
		hide1.style.display = "block";
    }
}

function hideMenu3() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("trackingMap");
	
    if (hide.style.display === "none") {
        hide.style.display = "block";
		show.style.display = "none";
    } else {
        hide.style.display = "none";
		show.style.display = "block";
    }
}


function hideMenu4() {
    var hide = document.getElementById("menu");
	var hide1 = document.getElementById("UserNameDisplay");
	var show = document.getElementById("profile");
	
    if (hide.style.display === "none") {
        hide.style.display = "block";
        hide1.style.display = "block";
		show.style.display = "none";
    } else {
        hide1.style.display = "none";
        hide.style.display = "none";
		show.style.display = "block";
    }
}


function hideProfile() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("profile");
	var hide1 = document.getElementById("UserNameDisplay");
	
    if (show.style.display === "none") {
        show.style.display = "block";
		hide.style.display = "none";
		hide1.style.display = "none";
    } else {
        show.style.display = "none";
		hide.style.display = "block";
		hide1.style.display = "block";
    }
}


function hideMap() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("trackingMap");
	
    if (show.style.display === "none") {
        show.style.display = "block";
		hide.style.display = "none";
    } else {
        show.style.display = "none";
		hide.style.display = "block";
    }
}

function hideTables() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("tableData");
	
    if (show.style.display === "none") {
        show.style.display = "block";
		hide.style.display = "none";
    } else {
        show.style.display = "none";
		hide.style.display = "block";
    }
}

function hideContact() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("contactForm");
	
    if (show.style.display === "none") {
        show.style.display = "block";
		hide.style.display = "none";
    } else {
        show.style.display = "none";
		hide.style.display = "block";
    }
}

function hideMenuAdmin() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("manageAccountAdmin");
	
    if (hide.style.display === "none") {
        hide.style.display = "block";
		show.style.display = "none";
    } else {
        hide.style.display = "none";
		show.style.display = "block";
    }
}


function hideMenu1Admin() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("databaseRecordAdmin");
	
    if (hide.style.display === "none") {
        hide.style.display = "block";
		show.style.display = "none";
    } else {
        hide.style.display = "none";
		show.style.display = "block";
    }
}


function hideContactAdmin() {
    var hide = document.getElementById("menu");
	var show = document.getElementById("settingsAdmin");
	
    if (show.style.display === "none") {
        show.style.display = "block";
		hide.style.display = "none";
    } else {
        show.style.display = "none";
		hide.style.display = "block";
    }
}
</script>

  <!-- JavaScript Libraries -->
  <script src="lib/jquery/jquery.min.js"></script>
  <script src="lib/jquery/jquery-migrate.min.js"></script>
  <script src="lib/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="lib/easing/easing.min.js"></script>
  <script src="lib/superfish/hoverIntent.js"></script>
  <script src="lib/superfish/superfish.min.js"></script>
  <script src="lib/wow/wow.min.js"></script>
  <script src="lib/waypoints/waypoints.min.js"></script>
  <script src="lib/counterup/counterup.min.js"></script>
  <script src="lib/owlcarousel/owl.carousel.min.js"></script>
  <script src="lib/isotope/isotope.pkgd.min.js"></script>
  <script src="lib/lightbox/js/lightbox.min.js"></script>
  <script src="lib/touchSwipe/jquery.touchSwipe.min.js"></script>
  <script src="lib/Chart.js/Chart.bundle.js"></script>
  <script src="lib/Chart.js/samples/utils.js"></script>
  <!-- Contact Form JavaScript File -->
  <script src="contactform/contactform.js"></script>

  <!-- Template Main Javascript File -->
  <script src="js/main.js"></script>

</body>
</html>
