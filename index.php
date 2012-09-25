<?php
header('Content-Type: application/json; charset=utf-8');
 
require_once "config.php";
require_once $simplehtmldomPath;
require_once "airquality.php";

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
	- mikäli kaupungin numero on epäkelpo, palautetaan edellisen kerran valitun mittauspisteen tiedot(?)
	- mikäli aseman numero on epäkeltp, palautetaan yhteenveto kaupungin tuloksista
	-> http://www.ilmanlaatu.fi/php/table/observationsInTable.php?step=3600&today=1&timesequence=23&time=2012092210&station=841
	
Koska osa taulukkosivun tarvitsemasta tiedosta tulee GET-parametrista ja osa evästeestä, on sivun käyttäminen hieman kinkkistä. Valintasivu pitää ensin hakea ja eväste tallentaa, taulukkosivun voi hakea vasta tämän jälkeen. Pelkän taulukkosivun hakeminen aiheuttaa internal server errorin.

Taulukkosivu näyttää mittaustiedot kuluvalta vuorokaudelta joka tasatunnilta. Mittaustieto tulee joskus viiveellä, jolloin viimeisimmän mittaustiedon kohdalla on tyhjä kohta. Tämä rajapinta palauttaa silloin sitä edellisen mittaustiedon.

Mansku, NO2:
http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=nitrogendioxide&pv=22.09.2012&j=23&et=table&tj=3600&ls=suomi

Lepuski, NO2:
http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=60&ss=841&p=nitrogendioxide&pv=22.09.2012&j=23&et=table&tj=3600&ls=suomi


*/

$airquality = new airquality($_GET["rs"], $_GET["ss"]);
$result = $airquality->measurement($_GET["p"]);

echo json_encode($result);

?>