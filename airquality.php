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
		// If air quality index
		elseif ($type == "qualityIndex")
		{
			$dataArray = $this->qualityIndex($type);
			
			
			return $dataArray;
		}
		// If raw measurement
		elseif ($type == "nitrogendioxide" || $type == "particulateslt10um" || $type == "particulateslt2.5um" || $type == "carbonmonoxide" || $type == "ozone")
		{
			$dataArray = $this->scrapeMeasurement($type);
			
			// If this data is missing
			if (FALSE === $dataArray)
			{
				$this->message .= "this station doesn't have this measurement<br />";
				$errorArray['error'] = TRUE;
				$errorArray['message'] = $this->message;
				return $errorArray;
			}
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
	
	public function qualityIndex()
	{
		// Goes through all measurements, tries to pick the time & metadata from each, since we don't know which measurement is available
		
		$nitrogendioxide = $this->measurement("nitrogendioxide");
		$result['latest']['parts']['nitrogendioxide'] = $nitrogendioxide['latest']['data'];
		if (isset($nitrogendioxide['latest']['time']))
		{
			$time = $nitrogendioxide['latest']['time'];
			$metadata = $nitrogendioxide['metadata'];
		}
		
		$particulateslt2_5um = $this->measurement("particulateslt2.5um");
		$result['latest']['parts']['particulateslt2.5um'] = $particulateslt2_5um['latest']['data'];
		if (isset($particulateslt2_5um['latest']['time']))
		{
			$time = $particulateslt2_5um['latest']['time'];
			$metadata = $particulateslt2_5um['metadata'];
		}
		
		$particulateslt10um = $this->measurement("particulateslt10um");
		$result['latest']['parts']['particulateslt10um'] = $particulateslt10um['latest']['data'];
		if (isset($particulateslt10um['latest']['time']))
		{
			$time = $particulateslt10um['latest']['time'];
			$metadata = $particulateslt10um['metadata'];
		}
		
		$carbonmonoxide = $this->measurement("carbonmonoxide");
		$result['latest']['parts']['carbonmonoxide'] = $carbonmonoxide['latest']['data'];		
		if (isset($carbonmonoxide['latest']['time']))
		{
			$time = $carbonmonoxide['latest']['time'];
			$metadata = $carbonmonoxide['metadata'];
		}
		
		$ozone = $this->measurement("ozone");
		$result['latest']['parts']['ozone'] = $ozone['latest']['data'];
		if (isset($ozone['latest']['time']))
		{
			$time = $ozone['latest']['time'];
			$metadata = $ozone['metadata'];
		}
		
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
			$result['latest']['data'] = 5;
			$result['latest']['FI'] = "erittäin huono";
			$result['latest']['EN'] = "very bad";
		}
		elseif ($nitrogendioxide['latest']['data'] > 150 || $particulateslt2_5um['latest']['data'] > 50 || $particulateslt10um['latest']['data'] > 100 || $carbonmonoxide['latest']['data'] > 20000 || $ozone['latest']['data'] > 140)
		{
			$result['latest']['data'] = 4;
			$result['latest']['FI'] = "huono";
			$result['latest']['EN'] = "bad";
		}
		elseif ($nitrogendioxide['latest']['data'] > 70 || $particulateslt2_5um['latest']['data'] > 25 || $particulateslt10um['latest']['data'] > 50 || $carbonmonoxide['latest']['data'] > 8000 || $ozone['latest']['data'] > 100)
		{
			$result['latest']['data'] = 3;
			$result['latest']['FI'] = "välttävä";
			$result['latest']['EN'] = "mediocre";
		}
		elseif ($nitrogendioxide['latest']['data'] > 40 || $particulateslt2_5um['latest']['data'] > 10 || $particulateslt10um['latest']['data'] > 20 || $carbonmonoxide['latest']['data'] > 4000 || $ozone['latest']['data'] > 60)
		{
			$result['latest']['data'] = 2;
			$result['latest']['FI'] = "tyydyttävä";
			$result['latest']['EN'] = "satisfactory";
		}
		else 
		{
			$result['latest']['data'] = 1;
			$result['latest']['FI'] = "hyvä";
			$result['latest']['EN'] = "good";
		}
		
		$result['latest']['time'] = $time;
		$result['metadata'] = $metadata;
		$result['metadata']['measurement'] = "qualityIndex";
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

		// Convert scraped text to numbers
		$result = $this->convertScrapedToFloat($result);
		
		// save latest also as latest
		$temp = array_slice($data, -1, 1, TRUE);

		// if latest is empty, take measurement before that
		if (is_null($temp[0]['data']))
		{
			$temp = array_slice($data, -2, 1, TRUE);
		}

		// Time and data
		$result['latest']['data'] = $temp[key($temp)];
		$result['latest']['time'] = key($temp);

		return $result;
	}

	// ------------------------------------------------------------------------
	// 
	
	public function convertScrapedToFloat($array)
	{
		$array['latest']['data'] = (float) $array['latest']['data'];
		
		foreach ($array['today'] as $key => $value)
		{
			if ("" == $value)
			{
				$array['today'][$key] = NULL;
			}
			else
			{
				$array['today'][$key] = (float) $value;
			}
		}
		
		return $array;
	}
	
	// ------------------------------------------------------------------------
	// 
	
	public function debugThisArray($array)
	{
		$array['DEBUG'] = "------------------------------ DEBUG MODE ON ------------------------------";
	
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($array);
		exit();
	}

}


?>