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
<body>
	
	<h4> To upload more files goto <a href=upload.php> upload.php </a><br> </h4>
	<h1> List of S3 files uploaded</h1>
	<h2> Main AZ US West(oregon) </h2>
	<table border = 1>
	<tr>
	<td><b>File </b> </td>
	<td><b>Original Description </b> </td>
	<td><b> Delete button </b> </td>
	<td> <b> Created time </b></td>
	<td> <b> Last updated time </b> </td>
	<td> <b> Created by </b> </td>
	<td> <b> Various download links:<br> </b>(Cloud Front Domain, S3 Transfer Accelerated link,Presigned S3 origin URL) <br></td>

	</tr>
	<?php foreach($objects as $object): 
	?>
	
	<tr>

		<td> <?php echo $object['Key']; ?>  </td>
		<td>
		    <?php 
			$fname = $object['Key'];
			if (array_key_exists($fname, $s3description)) {
				echo $s3description[$fname];
			} ?>
		</td>

		<td> <form action="" method="post" enctype="multipart/form-data">
		     <label>Delete button for</label>
		     <input type="submit" name="DELETE" value="<?php echo $object['Key'];?>"></form>
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
		<td> <a href="<?php 
					echo $cloudFrontDomain.$object['Key'];
			      ?>">Download from Cloud front </a> <br>
	              <a href="<?php  
					$originalURL = $s3->getObjectUrl($config['s3']['bucket'],$object['Key']);
					$source= "my-assignment-2.s3.amazonaws.com";
					$destination = "my-assignment-2.s3-accelerate.amazonaws.com";
					$acceleratedURL = str_replace($source, $destination, $originalURL);
					echo $acceleratedURL;

		?>"> S3 Transfer Accelerated Link</a>
		<br>
		 <a href="<?php echo $s3->getObjectUrl($config['s3']['bucket'],$object['Key'],'+3600 minute')?>" download = "<?php $object['Key']?>"> Pre-signed S3 origin URL</a></td>
	</tr>
	<tr></tr>

	<?php endforeach; ?>
	</table>


<br> <b><i><u><font color=red size=3>*if for some reason, main AZ US west (oregon) is down, please use the replication bucket in NORTH California to access the files</font></u></i></b> 
	<br>  
	<br> <b><i>when you have recently uploaded a file, replication takes time, You will see that replication bucket is <u> <font color=blue size=3>EVENTUALLY CONSISTENT</font></u> with a few refreshes</i></b><br>

	<h2> Replication in AZ US WEST (N California) </h2>
	<table border = 1>
	<tr>
	<td><b>File </b> </td>
	<td><b>Original Description </b> </td>
	<td><b> Delete button </b> </td>
	<td> <b> Created time </b></td>
	<td> <b> Last updated time </b> </td>
	<td> <b> Created by </b> </td>

	<td> <b> Various download links:<br> </b>(Cloud Front Domain, S3 Transfer Accelerated link,Presigned S3 origin URL) <br></td>


	</tr>
	<?php foreach($replication as $object): 
	?>

		<tr>

		<td> <?php echo $object['Key']; ?>  </td>
		<td>
		    <?php 
			$fname = $object['Key'];
			if (array_key_exists($fname, $s3description)) {
				echo $s3description[$fname];
			} ?>
		</td>

		<td> <form action="" method="post" enctype="multipart/form-data">
		     <label>Delete button for</label>
		     <input type="submit" name="DELETE" value="<?php echo $object['Key'];?>"></form>
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
		<td><a href="<?php 
					echo $cloudFrontDomain.$object['Key'];
			      ?>"> Cloud front URL </a> <br>
	             <a href="<?php  
					$originalURL = $s3->getObjectUrl($config['s3']['bucket'],$object['Key']);
					$source= "my-assignment-2.s3.amazonaws.com";
					$destination = "my-assignment-2.s3-accelerate.amazonaws.com";
					$acceleratedURL = str_replace($source, $destination, $originalURL);
					echo $acceleratedURL;

		?>"> S3 Transfer Accelerated Link</a>
		<br>
		 <a href="<?php echo $s3->getObjectUrl($config['s3']['bucket'],$object['Key'],'+3600 minute')?>" download = "<?php $object['Key']?>"> Pre-signed S3 origin URL</a></td>
	</tr>

	<tr></tr>

	<?php endforeach; ?>
	</table>

	<?php
		$objects = $s3->getIterator('ListObjects', 
	[
		'Bucket' => $config['s3']['bucket']
	]);

	$replication = $s3->getIterator('ListObjects', 
	[
		'Bucket' => $config['s3']['replication']
	]);
		if (iterator_count($replication) == iterator_count($objects)) {
			echo "<h3> Everything looks good across oregon and northern CA </h3>";
			echo "Total number of objects on S3: ";
			echo iterator_count($replication);
		} else {
		   if (iterator_count($replication) > iterator_count($objects)) {
			echo "<h3> S3 Objects <font color=red> missing in main data centre </font> </h3>";
			echo "<h3> please look into replication for the missing object </h3>";
			echo "Total number of objects on replication:";
			echo iterator_count($replication);

		   } else { 
			echo "<h3> S3 Objects missing in replication data centre and not in sync with main AZ region </h3>";
			echo "<h3> Wait for replication to complete. </h3>";
			echo "Total number of objects on replication:";
			echo iterator_count($replication);

		   }
		}
	?>
	<?php 

	mysqli_close($connection);
	?>

	<font color="brown"><b><h4> Log out <a href="logout.php">Click Here</a></h4></b></font>
</body>
</html>
