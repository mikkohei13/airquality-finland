
API fo Finnish air quality data
===============================

This API scrapes air quality data from http://www.ilmanlaatuportaali.fi -portal, and presents it as a RESTful JSON API.

Examples (more below)
- http://biomi.kapsi.fi/tools/airquality/?p=nitrogendioxide&rs=86&ss=564
- http://biomi.kapsi.fi/tools/airquality/?p=qualityIndex&rs=420&ss=721
- http://biomi.kapsi.fi/tools/airquality/?p=nitrogendioxide&rs=86&ss=564&callback=1

*Notes*

Requires [Simple HTML DOM Parser 1.5](http://simplehtmldom.sourceforge.net); set path to this in config.php.

- Background info (in Finnish): LINK LATER
- API as a service (in Finnish): LINK LATER

*Shortcomings (to be fixed later)*

- Doesn't return any data approximately between 0-1 AM EET (because of the logic of the portal)
- Doesn't return measurement if its not available from the last three hours


*Data terms of use*

According to the portal, you are free to 

- Use the data for non-commercial purposes, research and teaching
- Publish the data for public communication

...provided you credit the http://www.ilmanlaatuportaali.fi as the source.

*License*

See LICENSE.MD

*Data as a wallpaper*

See mobile.md


Example responses
-----------------

## Single measurement
http://YOURSERVER/PATH/airquality-finland/?p=nitrogendioxide&rs=86&ss=564

All units are µg/m3.

	{
		error: false,
		metadata:
		{
			station: "Mannerhe",
			source: "Ilmanlaatuportaali, Ilmatieteen laitos",
			sourceURL: "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=nitrogendioxide&pv=26.09.2012&j=23&et=table&tj=3600&ls=suomi",
			status: "unconfirmed measurements",
			measurement: "nitrogendioxide"
		},
		today:
		{
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
		latest:
		{
			data: "13.2",
			time: 23
			index: 5,
			FI: "hyvä",
			EN: "good"
		}
	}

## Air quality index
http://YOURSERVER/PATH/airquality-finland/?p=qualityIndex&rs=86&ss=564

*Unit of the data is a class:* 
- 1 = good / hyvä
- 2 = satisfactory / tyydyttävä
- 3 = mediocre / välttävä
- 4 = bad / huono
- 5 = very bad / erittäin huono
		
	{
		latest:
		{
			parts:
			{
				nitrogendioxide: 25,
				particulateslt2.5um: 4.7,
				particulateslt10um: 17.7,
				carbonmonoxide: 319,
				ozone: 33
			},
			index: 1,
			FI: "hyvä",
			EN: "good",
			time: 8
		},
		metadata:
		{
			station: "Mannerhe",
			source: "Ilmanlaatuportaali, Ilmatieteen laitos",
			sourceURL: "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=ozone&pv=04.10.2012&j=23&et=table&tj=3600&ls=suomi",
			status: "unconfirmed measurements",
			measurement: "qualityIndex"
		},
		error: false
	}


## Station number which does not exist
http://YOURSERVER/PATH/airquality-finland/?p=qualityIndex&rs=86&ss=5640

	{
		latest:
		{
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

## Response as JSONP
If you set callback=1 in the GET parameters, the data will be wrapped in airQualityResponse function call.

Todo/Plans
----------

RELEASE

- Data as an object (instead of an array)
-- Move index calculation to its own method
-- Handle null data (at midnight)
- City validation
- Station validation: can be tricky because portal returns summary data if number is invalid, and scraper freezes when reading the summary table
- Scrape error handling: portal is offline or changes
- Caching
-- Based on get parameters, date & hour. If hourly data is available -> write cache, if not, don't. If cache is available, use that, if not, fetch new.
-- Find out when data is updated
- textual indices also to single measurements, also to qualityIndex response
- Additional sources? Are these duplicates to ilmanlaatuportaali.fi?
 - hel.fi
 - HSL

 
Misc
----

- Now input validation & error messaging is done in the sraping class; is this ok? Yes, if the point of this to be an API (object is not used directly in applications). 

- https://github.com/lllllT/MultiPictureLiveWallpaper
- https://github.com/lllllT/MultiPictureLiveWallpaper-PicasaPlugin
- https://play.google.com/store/apps/details?id=org.tamanegi.wallpaper.multipicture&feature=search_result#?t=W251bGwsMSwxLDEsIm9yZy50YW1hbmVnaS53YWxscGFwZXIubXVsdGlwaWN0dXJlIl0.

