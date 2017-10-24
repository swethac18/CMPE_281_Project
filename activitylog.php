<?php
	session_start();
	require 'start.php';
	require 'dbinfo.php';
	if (isset($_SESSION['valid'])) {
		if($_SESSION['username'] == "swetha") { 
			if($_SESSION['password'] = "cmpe281") {
			}
			else {
			    echo "<br> <h1> Click here to go back to <a href=\"login.php\"> login page</a><br></h1>";
			    die ("NO PROPER AUTHENTICATION due to incorrect password");
			
   			     header('Refresh: 2; URL = login.php');

			}
			
		} else {
			    echo "<br> <h1> Click here to go back to <a href=\"login.php\"> login page</a><br></h1>";
			    die ("NO PROPER AUTHENTICATION due to incorrect password");

   				header('Refresh: 2; URL = login.php');
		}
	
		echo "<font color=green> <h1> Logged in user:".$_SESSION['username']."</h1></font><br>";
		$Log = date("D M d, Y G:i");
		$eventString = "<b>[".$Log."] ".$_SESSION['username']." is viewing activity log of this service</b><br>";
		$insert_query = "INSERT INTO events values('".$eventString."')";
		$result= mysqli_query($connection, $insert_query);

        } else {
	    echo "<br> <h1> Click here to go back to <a href=\"login.php\"> login page</a><br></h1>";
	    die ("NO PROPER AUTHENTICATION");
   header('Refresh: 2; URL = login.php');
	}
?>
<html>
<head> <title> Activity log of CMPE 283 assignment </title> </head>
<body>

	<h1> Shows activity in reverse order (most recent event first)</h1>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<ul class="fa-ul">
	<?php 
	$result = mysqli_query($connection, "SELECT * FROM events");
	$data = array();
	
	while ($query_data = mysqli_fetch_row($result)) {
		array_push($data, $query_data[0]);	
	}
	$i = count($data) -1 ;
	while ($i >= 0)
        {
		echo "<li>";
		echo "<i class=\"fa-li fa fa-paperclip\"></i>";
		echo $data[$i];
		echo "</li>";
		$i = $i-1;
	}
	?>
	</ul>
</body>
</html>

