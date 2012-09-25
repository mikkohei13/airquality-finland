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
		if ("" != $this->message)
		{
			$errorArray['error'] = TRUE;
			$errorArray['message'] = $this->message;
			return $errorArray;
		}
		elseif ($type == "nitrogendioxide" || $type == "particulateslt10um" || $type == "particulateslt2.5um" || $type == "carbonmonoxide" || $type == "ozone")
		{
			$dataArray = $this->scrapeMeasurement($type);
			
			if (FALSE === $dataArray)
			{
				$this->message .= "this station doesn't have this measurement<br />";
				$errorArray['error'] = TRUE;
				$errorArray['message'] = $this->message;
				return $errorArray;
			}
			
//			echo "<pre>"; print_r ($dataArray); exit(); // Debug
			return $dataArray;
		}
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
	
	/*
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
	*/
	
	// ------------------------------------------------------------------------
	// Scrapes a measurement
	
	public function scrapeMeasurement($type)
	{
		// Form page URL
		$pv = date("d.m.Y");
		$urlHome = "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=" . $this->city . "&ss=" . $this->station . "&p=" . $type . "&pv=" . $pv . "&j=23&et=table&tj=3600&ls=suomi";

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