
API fo Finnish air quality data
===============================

This API scrapes air quality data from http://www.ilmanlaatuportaali.fi -portal, and presents it as a RESTful JSON API.

Examples:
 http://YOURSERVER/PATH/airquality-finland/?p=qualityIndex&rs=86&ss=564


Technical notes
---------------

Requires [Simple HTML DOM Parser 1.5](http://simplehtmldom.sourceforge.net); set path to this in config.php.


Android wallpaper
-----------------

You can set the background of your Android device to change colour based on the air quality data. There are few free third-party apps for this:

### Web LiveWallpaper by Chikashi Yajima

- Install [Web LiveWallpaper](https://play.google.com/store/apps/details?id=com.yaji.weblivewallpaper)
- Open browser, go to YOURDOMAIN/PATH/colour.php
- Menu > Share > Web LiveWallpaper > click Apply
-- If you can't see the apply button, turn the phone horizontal; the button is behing the ad.

### WebLiveWallpaper by Michael Haar

- Install [WebLiveWallpaper app](https://play.google.com/store/apps/details?id=com.dngames.websitelivewallpaper) for Android
- Open it from Wallpapers > Live Wallpapers
- Settings > New
 - Source YOURDOMAIN/PATH/colour.php
 - Refresh 600
- View settings
- Website snapshot: check
- Visible area: 0-0-100-100
- Save & enter name
- Start > My sites
- Select name you entered
- (wait)
- Click Set wallpaper



Data terms of use
-----------------

According to the portal, you are free to 

- Use the data for non-commercial purposes, research and teaching
- Publish the data for public communication

...provided you credit the http://www.ilmanlaatuportaali.fi as the source.


Example responses
-----------------

## Single measurement
http://YOURSERVER/PATH/airquality-finland/?p=nitrogendioxide&rs=86&ss=564

	{
	error: false,
	metadata: {
	station: "Mannerhe",
	source: "Ilmanlaatuportaali, Ilmatieteen laitos",
	sourceURL: "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=nitrogendioxide&pv=26.09.2012&j=23&et=table&tj=3600&ls=suomi",
	status: "unconfirmed measurements",
	measurement: "nitrogendioxide"
	},
	today: {
	1: "16.5",
	2: "13.1",
	3: "17.7",
	4: "16.1",
	5: "14.8",
	6: "20.5",
	7: "34.5",
	8: "43.9",
	9: "51.9",
	10: "53.9",
	11: "56.0",
	12: "47.1",
	13: "44.1",
	14: "47.7",
	15: "",
	16: "45.7",
	17: "47.2",
	18: "43.6",
	19: "29.2",
	20: "29.0",
	21: "29.8",
	22: "19.4",
	23: "13.2",
	24: ""
	},
	latest: {
	data: "13.2",
	time: 23
	}
	}

## Air quality index
http://YOURSERVER/PATH/airquality-finland/?p=qualityIndex&rs=86&ss=564

*Indices:* 
- 1 = good / hyvä
- 2 = satisfactory / tyydyttävä
- 3 = mediocre / välttävä
- 4 = bad / huono
- 5 = very bad / erittäin huono

	{
	>	metadata: {
	>	station: "Mannerhe",
	>	source: "Ilmanlaatuportaali, Ilmatieteen laitos",
	>	sourceURL: "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=nitrogendioxide&pv=26.09.2012&j=23&et=table&tj=3600&ls=suomi",
	>	status: "unconfirmed measurements",
	>	measurement: "qualityIndex"
	>	},
	latest: {
	nitrogendioxide: "13.2",
	particulateslt2.5um: "3.5",
	particulateslt10um: "11.3",
	carbonmonoxide: "164",
	ozone: "24",
	data: 1,
	time: 23
	},
	error: false
	}

## Station number which does not exist
http://YOURSERVER/PATH/airquality-finland/?p=qualityIndex&rs=86&ss=5640

	{
	latest: {
	nitrogendioxide: null,
	particulateslt2.5um: null,
	particulateslt10um: null,
	carbonmonoxide: null,
	ozone: null
	},
	error: true,
	message: "this station doesn't yet have an air quality index for today<br />"
	}

## Invalid station number
http://YOURSERVER/PATH/airquality-finland/?p=qualityIndex&rs=86&ss=XXX

	{
	error: true,
	message: "ss (station) must be a number"
	}

## Invalid city number
http://YOURSERVER/PATH/airquality-finland/?p=qualityIndex&rs=XXX&ss=564

	{
	error: true,
	message: "rs (city) must be a number"
	}

## Invalid measurement code
http://YOURSERVER/PATH/airquality-finland/?p=XXX&rs=86&ss=564

	{
	error: true,
	message: "unsupported p (measurement)<br />"
	}


Todo/Plans
----------

- Data types in JSON; string -> float
- Add marked fields to qualityIndex JSON
- Unit testing
- Index calculation: http://www.hsy.fi/seututieto/ilmanlaatu/tiedotus/indeksi/Sivut/default.aspx & http://www.ilmanlaatu.fi/ilmansaasteet/indeksi/indeksi.php
- Handle null data (at midnight)
- Documentation
- City validation
- Station validation: can be tricky because portal returns summary data if number is invalid, and scraper freezes when reading the summary table
- Scrape error handling: portal is offline or changes
- Caching?
- Additional sources? Are these duplicates to ilmanlaatuportaali.fi?
 - hel.fi
 - HSL


Misc
----

https://github.com/lllllT/MultiPictureLiveWallpaper
https://github.com/lllllT/MultiPictureLiveWallpaper-PicasaPlugin
https://play.google.com/store/apps/details?id=org.tamanegi.wallpaper.multipicture&feature=search_result#?t=W251bGwsMSwxLDEsIm9yZy50YW1hbmVnaS53YWxscGFwZXIubXVsdGlwaWN0dXJlIl0.



