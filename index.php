<?php
header('Content-Type: application/json; charset=utf-8');
 
require_once "config.php";
require_once $simplehtmldomPath;
require_once "airquality.php";
require_once "scraper.php";

try
{
	$airquality = new airquality($_GET["ss"], $_GET["p"]);
	$result = $airquality->returnResultArray();
}
catch (Exception $e)
{
	$result['error'] = TRUE;
	$result['message'] = $e->getMessage();
}

if (1 == $_GET['callback'])
{
	echo "airQualityResponse(" . json_encode($result) . ");";
}
else
{
	echo json_encode($result);
}


?>