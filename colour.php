<?php
header('Content-Type: text/html; charset=utf-8');

require_once "config.php";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Get data

$url = $basePath . "?p=qualityIndex&rs=86&ss=564";

$client = curl_init($url);
curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($client);
curl_close($client);

$data = json_decode($response, TRUE);

//print_r ($data); exit("Exited debug"); // debug

$latest = $data['latest']['data'];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Calculate colour

/*
// Old colours
if ($latest == NULL)
	$bgcolor = "#000";
elseif ($latest < 10)
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
*/

if ($latest == NULL)
	$bgcolor = "#000";
elseif ($latest == 1)
	$bgcolor = "#090";
elseif ($latest == 2)
	$bgcolor = "#990";
elseif ($latest == 3)
	$bgcolor = "#f90";
elseif ($latest == 4)
	$bgcolor = "#F00";
elseif ($latest == 5)
	$bgcolor = "#d09";
else
	$bgcolor = "#fff";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// HTML
?>
<html>
<head>
<style>
body {
	background-color: <?php echo $bgcolor; ?>;
}
p {
	color: #000;
	font-family: Arial, Helvetica, sans-serif;
	opacity:0.2;
	filter:alpha(opacity=20); /* For IE8 and earlier */
	margin: 0;
}
#latest {
	font-size: 300%;
	font-weight: bold;
	margin-top: 0.5em;
}
#detail {
		
}
</style>
</head>
<body>
<p id="latest"><?php echo $data['latest']['FI']; ?></p>
<p id="detail"><?php
echo "klo " . $data['latest']['time'] . "<br />\n"
	. "typpidioksidi " . $data['latest']['nitrogendioxide'] . "<br />\n"
	. "pienhiukkaset " . $data['latest']['particulateslt2.5um'] . "<br />\n"
	. "hengitettävät hiukkaset " . $data['latest']['particulateslt10um'] . "<br />\n"
	. "hiilimonoksidi " . $data['latest']['carbonmonoxide'] . "<br />\n"
	. "otsoni " . $data['latest']['ozone']
;
?></p>
</body>
</html>