<?php
	session_start();
	require 'start.php';
	require 'dbinfo.php';
	use Aws\CloudFront\CloudFrontClient;
	
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
		$eventString = "<b>[".$Log."] ".$_SESSION['username']." is viewing objects from <u>oregon</u> S3 bucket</b><br>";
		$insert_query = "INSERT INTO events values('".$eventString."')";
		$result= mysqli_query($connection, $insert_query);

        } else {
	    echo "<br> <h1> Click here to go back to <a href=\"login.php\"> login page</a><br></h1>";
	    die ("NO PROPER AUTHENTICATION");
   header('Refresh: 2; URL = login.php');
	}

	if (isset($_POST['DELETE'])) {
		echo "initiating deletion of file:".$_POST['DELETE'];
		try {
	        $s3->deleteObject([
			'Bucket' => $config['s3']['bucket'],
			'Key' => $_POST['DELETE']
		]);
		echo "<br>Deletion complete in main bucket in oregon<br>";
		} catch(Exception $e1) {
			echo $e1;
			echo "<br> Deletion not working </br>";
		}	
		try {
	        $s3->deleteObject([
			'Bucket' => $config['s3']['replication'],
			'Key' => $_POST['DELETE']
		]);

		echo "Deletion complete in replication bucket in north california<br>";
		} catch(Exception $e1) {
			echo $e1;
			echo "<br> Deletion not working </br>";
		}

		// Now we have to delete description and created time entry in RDS
		$delete_query = "DELETE from s3object WHERE file ='".$_POST['DELETE']."'";
		echo "<br> Running Delete Query to RDS data base :".$delete_query;
		echo "<br>";
		$result = mysqli_query($connection, $delete_query);
		echo "result status of deletion:".$result." (1 indicates success 0 indicates failure)<br>";
		echo "<br><br>";
		$Log = date("D M d, Y G:i");
		$eventString = "<b>[".$Log."] ".$_SESSION['username']." is deleting the file <font color=red>".$_POST['DELETE']."</font> from S3 bucket</b><br>";
		$insert_query = "INSERT INTO events values('".$eventString."')";
		$result= mysqli_query($connection, $insert_query);

	
	}
		
	$s3description = array();
	$createdtime = array();

	$select_query = "SELECT * FROM s3object";
	$result = mysqli_query($connection, $select_query);
	while ($query_data = mysqli_fetch_row($result)) {
		$s3description[$query_data[0]] = $query_data[1];
		$createdtime[$query_data[0]] = $query_data[2];
	}

	$objects = $s3->getIterator('ListObjects', 
	[
		'Bucket' => $config['s3']['bucket']
	]);

	$replication = $s3->getIterator('ListObjects', 
	[
		'Bucket' => $config['s3']['replication']
	]);
	
	$cloudFrontDomain= "https://d3pb1fjh9d7prf.cloudfront.net/";
	try {
	$cloudfront = CloudFrontClient::factory([
		'private_key' => 'pk-APKAJSLS4KKD5BVURCGQ.pem',
		'key_pair_id' => 'APKAJSLS4KKD5BVURCGQ'
	]);
	} catch(Exception $e) {
		echo "Cloudfront issue";
	}
?>

<html>
	<head>
		<title> Listing all S3 files </title>
	</head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue.css">


<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<body>
	
	<h4> To upload more files goto <a href=upload.php> upload.php </a><br> </h4>
	<h1> List of S3 files uploaded</h1>
	
	<h2> S3 bucket in Oregon </h2>
	<table border = 5 color=blue>
	<tr>
	<td><b>File (Download) </b> </td>
	<td><b>Original Description </b> </td>
	<td><b> Delete button <i class="material-icons">delete</i> </b> </td>
	<td> <b> Created time </b></td>
	<td> <b> Last updated time </b> </td>
	<td> <b> Created by </b> </td>

	</tr>
	<?php foreach($objects as $object): 
	?>
	
	<tr>

 <b> <u>	<td>  <a href="<?php 
					echo $cloudFrontDomain.$object['Key'];
			      ?>"><?php echo $object['Key']; ?> </a> </td>
         </b></u>	
	     <font color=blue> 
		<td>
		    <?php 
			$fname = $object['Key'];
			if (array_key_exists($fname, $s3description)) {
				echo $s3description[$fname];
			} ?>
		</td>
	<font>

		<td> <form action="" method="post" enctype="multipart/form-data">
		     <input type="submit" name="DELETE" value="<?php echo $object['Key'];?>"><i class="material-icons">delete</i>
</form></input>
		</td>
			
		<td>
		    <?php 
			$fname = $object['Key'];
			if (array_key_exists($fname, $createdtime)) {
				echo $createdtime[$fname];
			} ?>
		</td>

	
		<td> <?php echo $object['LastModified']; ?> </td>
		<td> <?php echo $object['Owner']['DisplayName']; ?> </td>
	</tr>

	<?php endforeach; ?>
	</table>

	<font color="red"><b> *Download URLS are cloudfront URLS</b></font> <br><br>

	<br><br>
	<h3> To look into replication bucket (in N.California)
	<a href ="replication.php"> Click here </a> </h3>

	<font color="brown"><b><h4> Log out <a href="logout.php">Click Here</a></h4></b></font>
	<?php 

	mysqli_close($connection);
	?>
</body>
</html>
