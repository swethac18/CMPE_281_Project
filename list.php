<?php
	require 'start.php';
	
//	$s3Client = (new Aws\Sdk)->createMultiRegionS3(['version' => 'latest']);

//	$allObjects = $s3Client->listObjects(['Bucket' => $config['s3']['bucket']);
	$objects = $s3->getIterator('ListObjects', 
	[
		'Bucket' => $config['s3']['bucket']
	]);

	$replication = $s3->getIterator('ListObjects', 
	[
		'Bucket' => $config['s3']['replication']
	]);

?>

<html>
	<head>
		<title> Listing all S3 files </title>
	</head>
<body>
	<h1> List of S3 files uploaded by various users </h1>
	<h2> Main AZ US West(oregon) </h2>
	<table border = 1>
	<tr>
	<td><b> File </b> </td>
	<td> <b> Last updated time </b> </td>
	<td> <b> Created by </b></td>
	</tr>
	<?php foreach($objects as $object): 
	?>
	
	<tr>
		<td> <?php echo $object['Key']; ?>  </td>
		<td> <?php echo $object['LastModified']; ?> </td>
		<td> <?php echo $object['Owner']['DisplayName']; ?> </td>
		<td> <a href="<?php echo $s3->getObjectUrl($config['s3']['bucket'],$object['Key'])?>" download = "<?php $object['Key']?>"> Download link</a></td>
		<td> For S3 transfer Accelerated link </td>
		<td> <a href="<?php  
					$originalURL = $s3->getObjectUrl($config['s3']['bucket'],$object['Key']);
					$source= "my-assignment-2.s3.amazonaws.com";
					$destination = "my-assignment-2.s3-accelerate.amazonaws.com";
					$acceleratedURL = str_replace($source, $destination, $originalURL);
					echo $acceleratedURL;

		?>"> Click here</td>
		

	</tr>
	<tr></tr>

	<?php endforeach; ?>
	</table>

	
	<h2> Replication in AZ US WEST (N California) </h2>
	<table border = 1>
	<tr>
	<td><b> File </b> </td>
	<td> <b> Last updated time </b> </td>
	<td> <b> Created by </b></td>
	</tr>
	<?php foreach($replication as $object): 
	?>
	
	<tr>
		<td> <?php echo $object['Key']; ?>  </td>
		<td> <?php echo $object['LastModified']; ?> </td>
		<td> <?php echo $object['Owner']['DisplayName']; ?> </td>
		<td> <a href="<?php echo $s3->getObjectUrl($config['s3']['bucket'],$object['Key'])?>" download = "<?php $object['Key']?>"> Download link</a></td>
		<td> For S3 transfer Accelerated link </td>
		<td> <a href="<?php  
					$originalURL = $s3->getObjectUrl($config['s3']['bucket'],$object['Key']);
					$source= "my-assignment-2.s3.amazonaws.com";
					$destination = "my-assignment-2.s3-accelerate.amazonaws.com";
					$acceleratedURL = str_replace($source, $destination, $originalURL);
					echo $acceleratedURL;

		?>"> Click here</td>

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

</body>
</html>	
