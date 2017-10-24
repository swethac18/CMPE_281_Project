<?php
	use Aws\S3\S3Client;
	use Aws\CloudFront\CloudFrontClient;
	require '../vendor/autoload.php';
	$config = require('config.php');
	
	$s3 = S3Client::factory([
		'key' => $config['s3']['key'],
		'secret' => $config['s3']['secret']	
	]);

?>
