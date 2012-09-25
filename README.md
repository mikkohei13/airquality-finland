
API fo Finnish air quality data
===============================

This API scrapes air quality data from http://www.ilmanlaatuportaali.fi -portal, and presents it as a RESTful JSON API.


Technical notes
---------------

Requires [Simple HTML DOM Parser 1.5](http://simplehtmldom.sourceforge.net); set path to this in config.php.


Android wallpaper
-----------------

You can set the background of your Android device to change colour based on the air quality data.

### Install WebLiveWallpaper app

- Install [WebLiveWallpaper app](https://play.google.com/store/apps/details?id=com.dngames.websitelivewallpaper&hl=en) for Android
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


TODO
----

- Index calculation: http://www.ilmanlaatu.fi/ilmansaasteet/indeksi/indeksi.php
- City/station selection
- Measurement selection
- Caching?
- Additional sources? Are these duplicates to ilmanlaatuportaali.fi?
 - hel.fi
 - HSL


Plans
-----

Indeksi-API - mittausobjekti - ilmanlaatuportaali

Drafting:

Class airquality
{
	var $city = FALSE;
	var $locality = FALSE;
	
	public function __construct($city, $locality)
	{
		$this->city = $city;
		$this->locality = $locality;
	}
	
	public function NO2()
	{
		
	}
	
	public function 03()
	{
		
	}

	public function qualityIndex()
	{
		$NO2 = NO2(); 
		$O3 = O3();
		
		$limit[5]['NO2'] = 100;	
		
		if ($NO2 > $limit[5]['NO2'] || $NO2 > $limit[5]['O3'])
		{
			$index = 5;
		}
		
	}
}


