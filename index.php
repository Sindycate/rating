<?php

Error_Reporting(E_ALL & ~E_NOTICE);

require_once('/php/config.php');

$requestURI    = explode('/', $_SERVER['REDIRECT_URL']); //['REQUEST_URI']);
$requestURI[0] = (count($requestURI)-1);

if ($requestURI[2] == 'registration') {
	require_once('/php/registration.php');
} else {
	require_once('/php/index.php');
}


?>