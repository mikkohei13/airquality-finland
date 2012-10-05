
City - rs
---------
- 60	Espoo
- 86	Helsinki
- 303	Oulu
- 420	Tampere
- 430	Turku

Station - ss
------------

Espoo
- 841	Leppävaara 4
- 208	Luukki

Helsinki
- 564	Mannerheimintie
- 425	Kallio 2
- 580	Smear II, Kumpula
- 206	 Vallila 1
- 781	Vartiokylä, Huvipolku

Oulu
- 446	Oulun keskusta 2
- 301	Pyykösjärvi

Tampere
- 838	Epila 2
- 801	Kaleva
- 721	Linja-autoasema
- 549	Pirkankatu

Turku
- 701	Oriketo
- 460	Ruissalo Saaronniemi
- 186	Turun kauppatori


Measurements - p
----------------
- nitrogendioxide		typpidioksidi
- particulateslt10um	hengitettävät hiukkaset
- particulateslt2_5um	pienhiukkaset
- carbonmonoxide		hiilimonoksidi
- ozone					otsoni
- qualityIndex			ilmanlaatuindeksi


Ilmanlaatuportaalin toimintalogiikka:
--------------------------------------

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