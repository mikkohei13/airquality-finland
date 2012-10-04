<?php
header('Content-Type: text/html; charset=utf-8');

require_once "config.php";

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Get data

// Mannerheimintie, Helsinki
$url = $basePath . "?p=qualityIndex&rs=86&ss=564";

$client = curl_init($url);
curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($client);
curl_close($client);

$data = json_decode($response, TRUE);

// echo "<pre>"; print_r ($data); exit("Exited debug"); // debug

$latest = $data['latest']['data'];

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Calculate colour

/*
// Old, nicer colours
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

// Official ilmanlaatu.fi colours
if ($latest == NULL)
	$bgcolor = "#000";
elseif ($latest == 1)
	$bgcolor = "#67e567";
elseif ($latest == 2)
	$bgcolor = "#fff055";
elseif ($latest == 3)
	$bgcolor = "#ffbb58";
elseif ($latest == 4)
	$bgcolor = "#fe4543";
elseif ($latest == 5)
	$bgcolor = "#b5468b";
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
echo "klo " . $data['latest']['time'] . " @ " . $data['metadata']['station'] . "<br />\n"
	. "typpidioksidi " . $data['latest']['parts']['nitrogendioxide'] . "<br />\n"
	. "pienhiukkaset " . $data['latest']['parts']['particulateslt2.5um'] . "<br />\n"
	. "hengitettävät hiukkaset " . $data['latest']['parts']['particulateslt10um'] . "<br />\n"
	. "hiilimonoksidi " . $data['latest']['parts']['carbonmonoxide'] . "<br />\n"
	. "otsoni " . $data['latest']['parts']['ozone']
;
?></p>
</body>
</html>