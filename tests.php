<?php
header('Content-Type: application/json; charset=utf-8');
 
require_once "config.php";
require_once $simplehtmldomPath;
require_once "airquality.php";



$airquality = new airquality(86, 564);
$airquality->runTests();


?>