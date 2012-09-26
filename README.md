
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


TODO
----

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

