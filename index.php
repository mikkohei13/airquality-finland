<?php
header('Content-Type: application/json; charset=utf-8');
 
require_once "config.php";
require_once $simplehtmldomPath;
require_once "airquality.php";



$airquality = new airquality($_GET["rs"], $_GET["ss"]);
$result = $airquality->measurement($_GET["p"]);

if (1 == $_GET['callback'])
{
	echo "airQualityResponse(" . json_encode($result) . ");";
}
else
{
	echo json_encode($result);
}


?>