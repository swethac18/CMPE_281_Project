<?php
   session_start();
   unset($_SESSION["username"]);
   unset($_SESSION["password"]);
   unset($_SESSION['valid']);
   echo 'You have logged out ';
   echo '<br> refreshing to login page in 1 second';
   header('Refresh: 2; URL = login.php');
?>
