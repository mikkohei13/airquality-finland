<?php

Class airquality
{
	var $city = FALSE;
	var $station = FALSE;
	var $message = "";
	
	// ------------------------------------------------------------------------
	// Constructor
	// Checks that city & station are numbers
	
	public function __construct($city, $station)
	{
		if (is_numeric($city) && $city == (int) $city)
		{
			$this->city = $city;
		}
		else
		{
			$this->message .= "rs (city) must be a number";
		}

		if (is_numeric($station) && $station == (int) $station)
		{
			$this->station = $station;
		}
		else
		{
			$this->message .= "ss (station) must be a number";
		}
	}
	
	// ------------------------------------------------------------------------
	// Returns measurement or error message as an array
	// Checks that type is valid
	
	public function measurement($type)
	{
		// If error with city or station
		if ("" != $this->message)
		{
			$errorArray['error'] = TRUE;
			$errorArray['message'] = $this->message;
			return $errorArray;
		}
		elseif ($type == "qualityIndex")
		{
			$dataArray = $this->qualityIndex($type);
			return $dataArray;
		}
		elseif ($type == "nitrogendioxide" || $type == "particulateslt10um" || $type == "particulateslt2.5um" || $type == "carbonmonoxide" || $type == "ozone")
		{
			$dataArray = $this->scrapeMeasurement($type);
			
			// If this data is missing
			if (FALSE === $dataArray)
			{
				$this->message .= "this station doesn't have this measurement for today<br />";
				$errorArray['error'] = TRUE;
				$errorArray['message'] = $this->message;
				return $errorArray;
			}
			
//			echo "<pre>"; print_r ($dataArray); exit(); // Debug
			return $dataArray;
		}
		// If error with measurement name
		else
		{
			$this->message .= "unsupported p (measurement)<br />";
			$errorArray['error'] = TRUE;
			$errorArray['message'] = $this->message;
			return $errorArray;
		}
	}
	
	// ------------------------------------------------------------------------
	// Calculates air quality index
	// http://www.ilmanlaatu.fi/ilmansaasteet/indeksi/indeksi.php
	
	// DRAFT
	

	public function qualityIndex()
	{
		$nitrogendioxide = $this->measurement("nitrogendioxide");
		$result['latest']['nitrogendioxide'] = $nitrogendioxide['latest']['data'];
		if (isset($nitrogendioxide['latest']['time']))
		{
			$time = $nitrogendioxide['latest']['time'];
		}
		
		$particulateslt2_5um = $this->measurement("particulateslt2.5um");
		$result['latest']['particulateslt2.5um'] = $particulateslt2_5um['latest']['data'];
		if (isset($particulateslt2_5um['latest']['time']))
		{
			$time = $particulateslt2_5um['latest']['time'];
		}
		
		$particulateslt10um = $this->measurement("particulateslt10um");
		$result['latest']['particulateslt10um'] = $particulateslt10um['latest']['data'];
		if (isset($particulateslt10um['latest']['time']))
		{
			$time = $particulateslt10um['latest']['time'];
		}
		
		$carbonmonoxide = $this->measurement("carbonmonoxide");
		$result['latest']['carbonmonoxide'] = $carbonmonoxide['latest']['data'];		
		if (isset($carbonmonoxide['latest']['time']))
		{
			$time = $carbonmonoxide['latest']['time'];
		}
		
		$ozone = $this->measurement("ozone");
		$result['latest']['ozone'] = $ozone['latest']['data'];
		if (isset($ozone['latest']['time']))
		{
			$time = $ozone['latest']['time'];
		}
		
//		$particulateslt10um = 21; // DEBUG

		
		// Values from http://www.hsy.fi/seututieto/ilmanlaatu/tiedotus/indeksi/Sivut/default.aspx
		// All units are micrograms/m3
		
		if (NULL == $nitrogendioxide['latest']['data'] && NULL == $particulateslt2_5um['latest']['data'] && NULL == $particulateslt10um['latest']['data'] && NULL == $carbonmonoxide['latest']['data'] && NULL == $ozone['latest']['data'])
		{
			$result['error'] = TRUE;
			$result['message'] = "this station doesn't yet have an air quality index for today<br />";
			return $result;
		}
		elseif ($nitrogendioxide['latest']['data'] > 200 || $particulateslt2_5um['latest']['data'] > 75 || $particulateslt10um['latest']['data'] > 200 || $carbonmonoxide['latest']['data'] > 30000 || $ozone['latest']['data'] > 180)
		{
			$result['latest']['data'] = "5";
			$result['latest']['FI'] = "erittäin huono";
		}
		elseif ($nitrogendioxide['latest']['data'] > 150 || $particulateslt2_5um['latest']['data'] > 50 || $particulateslt10um['latest']['data'] > 100 || $carbonmonoxide['latest']['data'] > 20000 || $ozone['latest']['data'] > 140)
		{
			$result['latest']['data'] = "4";
			$result['latest']['FI'] = "huono";
		}
		elseif ($nitrogendioxide['latest']['data'] > 70 || $particulateslt2_5um['latest']['data'] > 25 || $particulateslt10um['latest']['data'] > 50 || $carbonmonoxide['latest']['data'] > 8000 || $ozone['latest']['data'] > 100)
		{
			$result['latest']['data'] = "3";
			$result['latest']['FI'] = "välttävä";
		}
		elseif ($nitrogendioxide['latest']['data'] > 40 || $particulateslt2_5um['latest']['data'] > 10 || $particulateslt10um['latest']['data'] > 20 || $carbonmonoxide['latest']['data'] > 4000 || $ozone['latest']['data'] > 60)
		{
			$result['latest']['data'] = "2";
			$result['latest']['FI'] = "tyydyttävä";
		}
		else 
		{
			$result['latest']['data'] = "1";
			$result['latest']['FI'] = "hyvä";
		}
		
		$result['latest']['time'] = $time;
		
		$result['error'] = FALSE;
		return $result;
	}

	
	// ------------------------------------------------------------------------
	// Scrapes a measurement
	
	public function scrapeMeasurement($type)
	{
		// Form page URL
		$pv = date("d.m.Y");
		$urlHome = "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=" . $this->city . "&ss=" . $this->station . "&p=" . $type . "&pv=" . $pv . "&j=23&et=table&tj=3600&ls=suomi";
//		echo $urlHome; exit(); // debug

		// Data page URL
		$time = date("YmdH");
		$url = "http://www.ilmanlaatu.fi/php/table/observationsInTable.php?step=3600&today=1&timesequence=23&time=" . $time . "&station=" . $this->station . "";

		// Create a cookie file
		$ckfile = tempnam ("/tmp", "CURLCOOKIE");
		
		// Visit form page, set a cookie
		$ch = curl_init ($urlHome);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec ($ch);
		
		// Scrape data page with the cookie
		$ch = curl_init ($url);
		curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec ($ch);

		$output = utf8_encode($output);

		// Scrape
		$html = str_get_html($output); 
		$table = $html->find('table', 0);

		foreach($table->find('tr') as $row)
		{
			$data[$row->find('td', 0)->plaintext] = $row->find('td', 1)->plaintext;
		}

		// Generate array
		// metadata fields
		$result['error'] = FALSE;
		
		$result['metadata']['station'] = $data['Tunti'];
		unset($data['Tunti']);
		
		if (NULL == $result['metadata']['station'])
		{
			// Station doesn't have this measurement
			return FALSE;
		}
		elseif (empty($data))
		{
			// Station doesn't have measurements for today
			return FALSE;
		}

		
		$result['metadata']['source'] = "Ilmanlaatuportaali, Ilmatieteen laitos";
		$result['metadata']['sourceURL'] = $urlHome;
		$result['metadata']['status'] = "unconfirmed measurements";
		$result['metadata']['measurement'] = $type;

		// Save all data as todays data
		$result['today'] = $data;

		// save latest also as latest
		$temp = array_slice($data, -1, 1, TRUE);

		// if latest is empty, take measurement before that
		if (empty($temp[0]['data']))
		{
			$temp = array_slice($data, -2, 1, TRUE);
		}

		$result['latest']['data'] = $temp[key($temp)];
		$result['latest']['time'] = key($temp);

		return $result;
	}

}


?>