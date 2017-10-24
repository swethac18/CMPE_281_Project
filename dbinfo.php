<html>
<body><?php
define('DB_SERVER', 'cmpe281db.cvx1k3fwfe0o.us-west-1.rds.amazonaws.com');
define('DB_USERNAME', 'swetha');
define('DB_PASSWORD', 'raghavan1988!!');
define('DB_DATABASE', 'cmpe281');

$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if (mysqli_connect_errno()) echo "failed to connect to mysql:".mysql_connect_error();

$database = mysqli_select_db($connection, DB_DATABASE);


$result = mysqli_query($connection, "SELECT * FROM account");
//while ($query_data = mysqli_fetch_row($result)) {
//	echo "<br>".$query_data[0]."==".$query_data[1];
//}
?>

</body>
</html>
