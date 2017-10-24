<?php
   session_start();
   unset($_SESSION["username"]);
   unset($_SESSION["password"]);
   unset($_SESSION['valid']);
?>

   <a href="app/login.php"> Login page is here </a>

