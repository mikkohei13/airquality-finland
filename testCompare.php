<pre>
<?php
header('Content-Type: text/html; charset=utf-8');

$parameters = "?p=ozone&ss=564";
echo "<h1>$parameters</h1>";

$productionJSON = file_get_contents("http://biomi.kapsi.fi/tools/airquality/" . $parameters);
$productionData = json_decode($productionJSON, TRUE);

$devJSON = file_get_contents("http://www.luomus.fi/temp/airquality-finland/" . $parameters);
$devData = json_decode($devJSON, TRUE);

$diff1 = arrayRecursiveDiff($productionData, $devData);
$diff2 = arrayRecursiveDiff($devData, $productionData);

if (empty($diff1) && empty($diff2))
{
	echo "<span style=\"color: green; font-size: 200%;\">PASS</span>\n\n";
}
else
{
	echo "<span style=\"color: red; font-size: 200%;\">FAIL</span>\n\n";
}

echo "DIFF1:\n";
print_r ($diff1);
echo "DIFF2:\n";
print_r ($diff2);
echo "\n<hr />";
echo "PRODUCTION:\n";
print_r ($productionData);
echo "\n<hr />";
echo "DEV:\n";
print_r ($devData);


// firegun at terra dot com dot br / http://www.php.net/manual/en/function.array-diff.php#91756
function arrayRecursiveDiff($aArray1, $aArray2) {
  $aReturn = array();

  foreach ($aArray1 as $mKey => $mValue) {
    if (array_key_exists($mKey, $aArray2)) {
      if (is_array($mValue)) {
        $aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]);
        if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
      } else {
        if ($mValue != $aArray2[$mKey]) {
          $aReturn[$mKey] = $mValue;
        }
      }
    } else {
      $aReturn[$mKey] = $mValue;
    }
  }
  return $aReturn;
} 
?>