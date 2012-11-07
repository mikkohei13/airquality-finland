<pre>
<?php
header('Content-Type: text/html; charset=utf-8');

$parameters = "?p=nitrogendioxide&ss=564";

$productionJSON = file_get_contents("http://biomi.kapsi.fi/tools/airquality/" . $parameters);
$productionData = json_decode($productionJSON, TRUE);

$devJSON = file_get_contents("http://www.luomus.fi/temp/airquality-finland/" . $parameters);
$devData = json_decode($devJSON, TRUE);

if ($productionJSON == $devJSON)
{
	echo "<span style=\"color: green; font-size: 200%;\">PASS</span>\n\n";
}
else
{
	echo "<span style=\"color: red; font-size: 200%;\">FAIL</span>\n\n";
	print_r (array_diff($productionData, $devData));
}

echo "PRODUCTION:\n";
print_r ($productionData);
echo "\n";
echo "DEV:\n";
print_r ($devData);

?>