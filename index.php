<?php
require_once('../login/isLoggedIn.php');

header( "Location: http://".$_SERVER['HTTP_HOST']."/pd_estimation/count.php"); 

?>