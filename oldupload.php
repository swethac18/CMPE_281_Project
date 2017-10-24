<?php
	use Aws\S3\Exception\S3Exception;
	require 'start.php';
	ini_set('upload_max_filesize', '10M');
	ini_set('memory_limit', '16M');
	ini_set('post_max_size', '16M');
	ini_set('max_input_time', 3600);
	ini_set('max_execution_time', 3600);

	if (isset($_FILES['file'])) {
		$file = $_FILES['file'];
		$userName =  $_POST['userName'];
		$password =  $_POST['password'];
		// File details
		$name = $file['name'];
		$tmp_name = $file['tmp_name'];

		$extension = explode('.', $name);

		$extension = strtolower(end($extension));

		$user_name = 'swetha_';
		$tmp_file_name = 'files/'.$user_name.$name;
	
		$result  = move_uploaded_file($tmp_name, $tmp_file_name);
		if ($userName == "swetha") {
			if ($password != 'raghavan') {
//				die ("invalid credentials or no permission to upload");
			}
		} else {
//			die ("unauthorized user");
		}
		echo $result.'move to temporary directiry';	
		if ($result == TRUE) {

		   echo "successful moved to tmp directory";
		   try {
			
	           $s3->putObject(
                        [
                          'Bucket' => $config['s3']['bucket'],
                          'Key' => $name,
                          'Body' => fopen($tmp_file_name, 'rb'),
			   'ACL' => 'public-read'
                        ]
                   );

		   echo "uploaded file to ".$tmp_file_name;
                   unlink($tmp_file_path);
                   			
		} catch(S3Exception $e) {
			echo "There was an error uploading the file to S3<br>";
			echo $e;
		}
		} else {
			echo "not successful";
			echo "<br> Check one of the following reasons:<br>";
			echo "It could be because of network issue<br>";
			echo "Reduce file size, i had imposed a file size of 10 MB<br>";
			echo "memory limit exceeded as i am using a smaller instance";
		}

	}
?>
<html>
	<head>
	<title> Swetha CMPE Cloud Assignment </title>
	</head>
	<body>
	<br>
	<form action="" method="post" enctype="multipart/form-data">
		<table>
		<tr>
		<td> userName </td> 
		<td> <input type="text" name="userName"> </td>
		</tr>
		<tr> <td> password: </td>
		<td> <input type="password" name="password">
		</td>
		</tr>
		<tr>
		<td> <input type="file" name="file"> </td>
		<td> <input type="submit" value="upload"> </td>
		<tr>
		</table>
	</form>
	<a href="list.php"> LIST uploaded S3 files </a>
	</body>

</html>

