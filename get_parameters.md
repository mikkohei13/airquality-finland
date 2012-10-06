

Stations - ss
-------------
http://muistio.tieke.fi/mittausasemat

841        Espoo: Leppävaara 4    60.22025897    24.81118349
208        Espoo: Luukki       60.313288    24.689477
184        Harjavalta: Kaleva
179        Harjavalta: Pirkkala
513        Heinola: Heinolan keskusta
425        Helsinki: Kallio 2    60.1874667    24.9505167
564        Helsinki: Mannerheimintie    60.169643    24.939261
580        Helsinki: Smear III, Kumpula
206        Helsinki: Vallila 1    60.193665    24.963942
781        Helsinki: Vartiokylä Huivipolku    60.223886    25.102528
368        Hämeenlinna: Evo (Lammi)
853        Hämeenlinna: Niittykatu
428        Ilomantsi: Ilomantsi
424        Imatra: Mansikkala
273        Imatra: Pelkolan tulliasema, Raja
80        Imatra: Rautionkylä
433        Imatra: Teppanala
357        Inari: Raja-Jooseppi
511        Joensuu: Koskikatu 1
453        Jokioinen: Jokioinen
464        Jyväskylä: Lyseo 2
431        Jyväskylä: Palokka 2
78        Jämsä: Lääkäritalo
868        Järvenpää: Järvenpää2
569        Kaarina: Kaarina
602        Kajaani: Kajaanin keskusta 3
404        Kokkola: Keskusta, Pitkänsillankatu
227        Kokkola: Ykspihlaja
62        Kotka: Kirjastotalo
438        Kotka: Rauhala
574        Kouvola: Kouvola, Käsityöläiskatu
86        Kouvola: Kuusankoski, Urheilukentäntie
363        Kuopio: Kasarmipuisto
558        Kuopio: Maaherrankatu
294        Kuopio: Sorsasalo
839        Kuopio: Tasavallankatu
352        Kuusamo: Oulanka
66        Lahti: Kisapuisto
455        Lahti: Laune
867        Lahti: Satulakatu
74        Lahti: Tori
67        Lahti: Vesku 11
283        Lappeenranta: Ihalainen
434        Lappeenranta: Joutsenon keskusta
606        Lappeenranta: Lappeenrannan keskusta 4
378        Lappeenranta: Lauritsala
379        Lappeenranta: Pulp
280        Lappeenranta: Tirilä, Pekkasenkatu
761        Lohja: Nahkurintori 2
469        Luoto: Vikarholmen
845        Länsi-Turunmaa: Parainen
349        Länsi-Turunmaa: Utö
356        Muonio: Sammaltunturi
444        Naantali: Naantalin keskusta
446        Oulu: Oulun keskusta 2
301        Oulu: Pyykösjärvi
397        Pietarsaari: Bottenviksvägen
588        Pori: Porin keskusta
339        Porvoo: Mustijoki
846        Porvoo: Nyby
833        Raahe: Merikatu
570        Raahe: Raahen keskusta 2
821        Raisio: Kaanaan koulu
120        Raisio: Raision keskusta
510        Rauma: Hallikatu
601        Savonlinna: Olavinkatu
848        Seinäjoki: Vapaudentie 6a
838        Tampere: Epila 2
801        Tampere: Kaleva
721        Tampere: Linja-autoasema
549        Tampere: Pirkankatu
701        Turku: Oriketo
460        Turku: Ruissalo Saaronniemi
186        Turku: Turun kauppatori
553        Utsjoki: Kevo
390        Valkeakoski: Hiekkatekonurmi
392        Valkeakoski: Valkeakosken terveyskeskus
370        Vantaa: Tikkurila 3    60.289946    25.039532
557        Varkaus: Psaari 2
124        Varkaus: Pääterveysasema
463        Varkaus: Taulumäki (toripaviljonki)
372        Ähtäri: Ähtäri 2
548        Äänekoski: Äänekoski Hiski


Measurements - p
----------------
- nitrogendioxide		typpidioksidi
- particulateslt10um	hengitettävät hiukkaset
- particulateslt2_5um	pienhiukkaset
- carbonmonoxide		hiilimonoksidi
- ozone					otsoni
- sulphurdioxide		rikkidioksidi
- qualityIndex			ilmanlaatuindeksi


Cities - rs
---------
This is not needed in order to fetch the data.

- 60	Espoo
- 86	Helsinki
- 303	Oulu
- 420	Tampere
- 430	Turku

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

