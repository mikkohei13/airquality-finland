<?php

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Get data

$url = "http://biomi.kapsi.fi/tools/airquality/?p=nitrogendioxide&rs=86&ss=564";

$client = curl_init($url);
curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($client);
curl_close($client);

$data = json_decode($response, TRUE);

$latest = $data['latest']['data'];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Calculate color

if ($latest < 10)
	$bgcolor = "#007900";
elseif ($latest < 20)
	$bgcolor = "#00FF00";
elseif ($latest < 30)
	$bgcolor = "#FFFF00";
elseif ($latest < 40)
	$bgcolor = "#FEB300";
elseif ($latest < 50)
	$bgcolor = "#FF7F00";
elseif ($latest < 60)
	$bgcolor = "#FF4600";
else
	$bgcolor = "#FF0000";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// HTML
?>
<html>
<head>
<style>
body {
	background-color: <?php echo $bgcolor; ?>;
}
#latest {
	color: #000;
	font-size: 300%;
	font-weight: bold;
	font-family: Arial, Helvetica, sans-serif;
	opacity:0.2;
	filter:alpha(opacity=20); /* For IE8 and earlier */
}
</style>
</head>
<body>
<p id="latest"><?php echo str_replace(".", ",", $latest); ?></p>
</body>
</html>