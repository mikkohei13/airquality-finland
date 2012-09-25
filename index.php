<?php
header('Content-Type: text/html; charset=UTF-8'); 
require_once "config.php";
require_once "../include/simplehtmldom/simple_html_dom.php";

/*
Ilmanlaatuportaalin toimintalogiikka:
1) Käyttäjä menee ilmanlaatu nyt -sivulle, ja valitsee täällä
	a) kunnan
	b) mittauspaikan
	c) mittauksen
	d) esitystavan (tässä tapauksessa taulukko)
	-> http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=60&ss=841&p=nitrogendioxide&sc=200&pv=22.09.2012&j=23&et=table&tj=3600&ls=suomi
2) Palvelu tallentaa valinnasta tietoja evästeeseen
3) Sivulla olevaan iframeen latautuu taulukkosivu, jossa mittaustiedot ovat. Sivu hakee mittauspaikan numeron, päivämäärän ja kellonajan GET-parametristaan, sekä ainakin mittaustiedon evästeestä.
	- aseman numero tulee GET-parametristam, evästeellä ei ole vaikutusta
	-> http://www.ilmanlaatu.fi/php/table/observationsInTable.php?step=3600&today=1&timesequence=23&time=2012092210&station=841
	
Koska osa taulukkosivun tarvitsemasta tiedosta tulee GET-parametrista ja osa evästeestä, on sivun käyttäminen hieman kinkkistä. Valintasivu pitää ensin hakea ja eväste tallentaa, taulukkosivun voi hakea vasta tämän jälkeen. Pelkän taulukkosivun hakeminen aiheuttaa internal server errorin.

Taulukkosivu näyttää mittaustiedot kuluvalta vuorokaudelta joka tasatunnilta. Mittaustieto tulee joskus viiveellä, jolloin viimeisimmän mittaustiedon kohdalla on tyhjä kohta. Tämä rajapinta palauttaa silloin sitä edellisen mittaustiedon.

Mansku, NO2:
http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=nitrogendioxide&pv=22.09.2012&j=23&et=table&tj=3600&ls=suomi

Lepuski, NO2:
http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=60&ss=841&p=nitrogendioxide&pv=22.09.2012&j=23&et=table&tj=3600&ls=suomi


*/

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Get vars

if ($_GET["p"] == "nitrogendioxide" || $_GET["p"] == "particulateslt10um" || $_GET["p"] == "particulateslt2.5um" || $_GET["p"] == "carbonmonoxide" || $_GET["p"] == "ozone")
{
	$measurement = $_GET["p"];
}
else
{
	exit("unsupported p (measurement)");
}

if ($_GET["rs"] == (int) $_GET["rs"])
{
	$city = $_GET["rs"];
}
else
{
	exit("rs (city) must be a number");
}

if ($_GET["ss"] == (int) $_GET["ss"])
{
	$station = $_GET["ss"];
}
else
{
	exit("ss (station) must be a number");
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Screen scrape

$pv = date("d.m.Y");
$urlHome = "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=" . $city . "&ss=" . $station . "&p=" . $measurement . "&pv=" . $pv . "&j=23&et=table&tj=3600&ls=suomi";

$time = date("YmdH");
$url = "http://www.ilmanlaatu.fi/php/table/observationsInTable.php?step=3600&today=1&timesequence=23&time=" . $time . "&station=" . $station . "";

/* STEP 1. let’s create a cookie file */
$ckfile = tempnam ("/tmp", "CURLCOOKIE");
/* STEP 2. visit the homepage to set the cookie properly */
$ch = curl_init ($urlHome);
curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile); 
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec ($ch);
/* STEP 3. visit cookiepage.php */
$ch = curl_init ($url);
curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile); 
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec ($ch);

$output = utf8_encode($output);


$html = str_get_html($output); 
$table = $html->find('table', 0);

$rowID = 0;
foreach($table->find('tr') as $row)
{
	$data[$rowID]['time'] = $row->find('td', 0)->plaintext;
	$data[$rowID]['data'] = $row->find('td', 1)->plaintext;
	$rowID++;
}

/*
// Debug
echo "URL: <a href=\"$url\">$url</a><p>";
echo "<pre>" . str_replace("&gt;", "&gt;\n", htmlentities($output)) . "";
echo "<hr />";
print_r ($data);
*/


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Generate array & JSON

// separate metadata fields
$metadata = array_shift($data);
$result['metadata']['source'] = "Ilmanlaatuportaali, Ilmatieteen laitos";
$result['metadata']['sourceURL'] = $urlHome;
$result['metadata']['status'] = "unconfirmed measurements";
$result['metadata']['locality'] = $metadata['data'];

// save all data as data
$result['data'] = $data;

// save latest also as latest
$temp = array_slice($data, -1, 1);

// if latest is empty, take measurement before that
if (empty($temp[0]['data']))
{
	$temp = array_slice($data, -2, 1);
}
$result['latest'] = $temp[0];



echo json_encode($result);


?>