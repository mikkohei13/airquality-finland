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
		$nitrogendioxide = $nitrogendioxide['latest']['data'];
		$result['latest']['nitrogendioxide'] = $nitrogendioxide;
		
		$particulateslt2_5um = $this->measurement("particulateslt2.5um");
		$particulateslt2_5um = $particulateslt2_5um['latest']['data'];
		$result['latest']['particulateslt2.5um'] = $particulateslt2_5um;
		
		$particulateslt10um = $this->measurement("particulateslt10um");
		$particulateslt10um = $particulateslt10um['latest']['data'];
		$result['latest']['particulateslt10um'] = $particulateslt10um;
		
		$carbonmonoxide = $this->measurement("carbonmonoxide");
		$carbonmonoxide = $carbonmonoxide['latest']['data'];		
		$result['latest']['carbonmonoxide'] = $carbonmonoxide;
		
		$ozone = $this->measurement("ozone");
		$ozone = $ozone['latest']['data'];
		$result['latest']['ozone'] = $ozone;
		
//		$particulateslt10um = 21; // DEBUG

		
		// Values from http://www.hsy.fi/seututieto/ilmanlaatu/tiedotus/indeksi/Sivut/default.aspx
		// All units are micrograms/m3
		
		if (NULL == $nitrogendioxide && NULL == $particulateslt2_5um && NULL == $particulateslt10um && NULL == $carbonmonoxide && NULL == $ozone)
		{
			$result['error'] = TRUE;
			$result['message'] = "this station doesn't yet have an air quality index for today<br />";
			return $result;
		}
		elseif ($nitrogendioxide > 200 || $particulateslt2_5um > 75 || $particulateslt10um > 200 || $carbonmonoxide > 30000 || $ozone > 180)
		{
			$result['latest']['data'] = "erittäin huono";
		}
		elseif ($nitrogendioxide > 150 || $particulateslt2_5um > 50 || $particulateslt10um > 100 || $carbonmonoxide > 20000 || $ozone > 140)
		{
			$result['latest']['data'] = "huono";
		}
		elseif ($nitrogendioxide > 70 || $particulateslt2_5um > 25 || $particulateslt10um > 50 || $carbonmonoxide > 8000 || $ozone > 100)
		{
			$result['latest']['data'] = "välttävä";
		}
		elseif ($nitrogendioxide > 40 || $particulateslt2_5um > 10 || $particulateslt10um > 20 || $carbonmonoxide > 4000 || $ozone > 60)
		{
			$result['latest']['data'] = "tyydyttävä";
		}
		else 
		{
			$result['latest']['data'] = "hyvä";
		}
		
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