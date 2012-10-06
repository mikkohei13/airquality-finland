<?php

Class airquality
{
	var $station = FALSE;
	var $message = "";
	var $messageStation = "";
	
	// ------------------------------------------------------------------------
	// Constructor
	// Checks that station is number
	
	public function __construct($station)
	{
		if (is_numeric($station) && $station == (int) $station)
		{
			$this->station = $station;
		}
		else
		{
			$this->messageStation .= "ss (station) must be a number";
		}
	}
	
	// ------------------------------------------------------------------------
	// Returns measurement or error message as an array
	// Checks that type is valid
	
	public function measurement($type)
	{
//		echo "functionMEASUREMENT $type\n"; // debug
	
		// If error with station
		if ("" != $this->messageStation)
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
		elseif ($type == "nitrogendioxide" || $type == "particulateslt10um" || $type == "particulateslt2.5um" || $type == "carbonmonoxide" || $type == "ozone" || $type == "sulphurdioxide")
		{
			$dataArray = $this->scrapeMeasurement($type);
			
			// If this data is missing
			if (FALSE === $dataArray)
			{
				$this->message .= "this station doesn't have this measurement (X)<br />";
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
		// Goes through all measurements, tries to pick the time & metadata from each, since we don't know which measurement is available. This will lead to the sourceURL will be from the last measurement. 
		
		$particulateslt10um = $this->measurement("particulateslt10um");
		$result['latest']['parts']['particulateslt10um'] = $particulateslt10um['latest']['data'];
		if (isset($particulateslt10um['latest']['time']))
		{
			$time = $particulateslt10um['latest']['time'];
			$metadata = $particulateslt10um['metadata'];
		}
		
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
		
		$sulphurdioxide = $this->measurement("sulphurdioxide");
		$result['latest']['parts']['sulphurdioxide'] = $sulphurdioxide['latest']['data'];
		if (isset($sulphurdioxide['latest']['time']))
		{
			$time = $sulphurdioxide['latest']['time'];
			$metadata = $sulphurdioxide['metadata'];
		}
		
		// Values from http://www.hsy.fi/seututieto/ilmanlaatu/tiedotus/indeksi/Sivut/default.aspx
		// All units are micrograms/m3
		
		if (NULL == $nitrogendioxide['latest']['data'] && NULL == $particulateslt2_5um['latest']['data'] && NULL == $particulateslt10um['latest']['data'] && NULL == $carbonmonoxide['latest']['data'] && NULL == $ozone['latest']['data'] && NULL == $sulphurdioxide['latest']['data'])
		{
			$result['error'] = TRUE;
			$result['message'] = "this station doesn't yet have an air quality index for today<br />";
			return $result;
		}
		elseif ($nitrogendioxide['latest']['data'] > 200 || $particulateslt2_5um['latest']['data'] > 75 || $particulateslt10um['latest']['data'] > 200 || $carbonmonoxide['latest']['data'] > 30000 || $ozone['latest']['data'] > 180 || $sulphurdioxide['latest']['data'] > 350)
		{
			$result['latest']['index'] = 5;
			$result['latest']['FI'] = "erittäin huono";
			$result['latest']['EN'] = "very bad";
		}
		elseif ($nitrogendioxide['latest']['data'] > 150 || $particulateslt2_5um['latest']['data'] > 50 || $particulateslt10um['latest']['data'] > 100 || $carbonmonoxide['latest']['data'] > 20000 || $ozone['latest']['data'] > 140 || $sulphurdioxide['latest']['data'] > 250)
		{
			$result['latest']['index'] = 4;
			$result['latest']['FI'] = "huono";
			$result['latest']['EN'] = "bad";
		}
		elseif ($nitrogendioxide['latest']['data'] > 70 || $particulateslt2_5um['latest']['data'] > 25 || $particulateslt10um['latest']['data'] > 50 || $carbonmonoxide['latest']['data'] > 8000 || $ozone['latest']['data'] > 100 || $sulphurdioxide['latest']['data'] > 80)
		{
			$result['latest']['index'] = 3;
			$result['latest']['FI'] = "välttävä";
			$result['latest']['EN'] = "mediocre";
		}
		elseif ($nitrogendioxide['latest']['data'] > 40 || $particulateslt2_5um['latest']['data'] > 10 || $particulateslt10um['latest']['data'] > 20 || $carbonmonoxide['latest']['data'] > 4000 || $ozone['latest']['data'] > 60 || $sulphurdioxide['latest']['data'] > 20)
		{
			$result['latest']['index'] = 2;
			$result['latest']['FI'] = "tyydyttävä";
			$result['latest']['EN'] = "satisfactory";
		}
		else 
		{
			$result['latest']['index'] = 1;
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
//		echo "functionSCRAPEMEASUREMENT $type\n"; // debug

		// Get page
		$output = $this->fetchPageAsUTF8($type);
		
		// Scrape
		$html = str_get_html($output['html']); 
		$table = $html->find('table', 0);
		
//		echo "\nTYPE:" . $type . $html . "\n\n"; // debug; shows what the scraper sees
		

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
		$result['metadata']['sourceURL'] = $output['url'];
		$result['metadata']['status'] = "unconfirmed measurements";
		$result['metadata']['measurement'] = $type;

		// Save all data as todays data
		$result['today'] = $data;


//		$this->debugThisArray($data); // debug

		// Takes last element of array
		end($data);
		while ("" == current($data))
		{
			prev($data);
		}
		
		$result['latest']['data'] = current($data);
		$result['latest']['time'] = key($data);
		
//		print_r ($result); exit(); // debug


		// Convert scraped text to numbers
		$result = $this->convertScrapedToFloat($result);
		
		$result = $this->addIndex($result);

		return $result;
	}

	// ------------------------------------------------------------------------
	// Fetches a data page from Ilmanlaatuportaali and returns it as an UTF-8 string
	
	public function fetchPageAsUTF8($type)
	{
		// Form page URL
		$pv = date("d.m.Y");
		$urlHome = "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&ss=" . $this->station . "&p=" . $type . "&pv=" . $pv . "&j=23&et=table&tj=3600&ls=suomi";
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

		$result['html'] = utf8_encode($output);
		$result['url'] = $urlHome;
		
		return $result;
	}


	// ------------------------------------------------------------------------
	// Adds index to a measurement
	
	public function addIndex($array)
	{
		// limts as micrograms/m3
	
		$indexMaxLimits['nitrogendioxide'][1] = 40;
		$indexMaxLimits['nitrogendioxide'][2] = 70;
		$indexMaxLimits['nitrogendioxide'][3] = 150;
		$indexMaxLimits['nitrogendioxide'][4] = 200;
	
		$indexMaxLimits['particulateslt2_5um'][1] = 10;
		$indexMaxLimits['particulateslt2_5um'][2] = 25;
		$indexMaxLimits['particulateslt2_5um'][3] = 50;
		$indexMaxLimits['particulateslt2_5um'][4] = 75;
	
		$indexMaxLimits['particulateslt10um'][1] = 20;
		$indexMaxLimits['particulateslt10um'][2] = 50;
		$indexMaxLimits['particulateslt10um'][3] = 100;
		$indexMaxLimits['particulateslt10um'][4] = 200;
	
		$indexMaxLimits['carbonmonoxide'][1] = 4000;
		$indexMaxLimits['carbonmonoxide'][2] = 8000;
		$indexMaxLimits['carbonmonoxide'][3] = 20000;
		$indexMaxLimits['carbonmonoxide'][4] = 30000;
	
		$indexMaxLimits['ozone'][1] = 60;
		$indexMaxLimits['ozone'][2] = 100;
		$indexMaxLimits['ozone'][3] = 140;
		$indexMaxLimits['ozone'][4] = 180;
		
		$indexMaxLimits['sulphurdioxide'][1] = 20;
		$indexMaxLimits['sulphurdioxide'][2] = 80;
		$indexMaxLimits['sulphurdioxide'][3] = 250;
		$indexMaxLimits['sulphurdioxide'][4] = 350;
		
		
		$data = $array['latest']['data'];
		$measurement = $array['metadata']['measurement'];
		
		if ("qualityIndex" == $measurement)
		{
			// TODO: Move index calculations here
		}
		else
		{
			if ($data > $indexMaxLimits[$measurement][4])
			{
				$index = 5;
				$FI = "erittäin huono";
				$EN = "very bad";
			}
			elseif ($data > $indexMaxLimits[$measurement][3])
			{
				$index = 4;
				$FI = "huono";
				$EN = "bad";
			}
			elseif ($data > $indexMaxLimits[$measurement][2])
			{
				$index = 3;
				$FI = "välttävä";
				$EN = "mediocre";
			}
			elseif ($data > $indexMaxLimits[$measurement][1])
			{
				$index = 2;
				$FI = "tyydyttävä";
				$EN = "satisfactory";
			}
			else
			{
				$index = 1;
				$FI = "hyvä";
				$EN = "good";
			}
		}
	
		$array['latest']['index'] = $index;
		$array['latest']['FI'] = $FI;
		$array['latest']['EN'] = $EN;
	
		return $array;
	}
	
	
	// ------------------------------------------------------------------------
	// Converts scraped stings to float numbers
	
	public function convertScrapedToFloat($array)
	{
//		echo "<pre>"; print_r ($array); exit();
		
		if (empty($array['latest']['data']))
		{
			$array['latest']['data'] = NULL;
		}
		else
		{
			$array['latest']['data'] = (float) $array['latest']['data'];
		}
		
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
	// Debug: prints array as JSON and exits
	
	public function debugThisArray($array)
	{
		$array['DEBUG'] = "------------------------------ DEBUG MODE ON ------------------------------";
	
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($array);
		exit();
	}

	// ------------------------------------------------------------------------
	// Simple tests
	

	public function runTests()
	{
		echo "<pre>";
		
		/*
		$testData['latest']['parts']['nitrogendioxide'] = 13.4;
		$testData['latest']['parts']['particulateslt2_5um'] = 11.6;
		$testData['latest']['parts']['particulateslt10um'] = 18.1;
		$testData['latest']['parts']['carbonmonoxide'] = 183;
		$testData['latest']['parts']['ozone'] = 39;
		$testData['latest']['index'] = 2;
		$testData['latest']['FI'] = "tyydyttävä";
		$testData['latest']['FI'] = "satisfactory";
		$testData['latest']['time'] = 22;
		$testData['metadata']['station'] = "Mannerhe";
		$testData['metadata']['source'] = "Ilmanlaatuportaali, Ilmatieteen laitos";
		$testData['metadata']['sourceURL'] = "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=ozone&pv=04.10.2012&j=23&et=table&tj=3600&ls=suomi";
		$testData['metadata']['status'] = "unconfirmed measurements";
		$testData['metadata']['measurement'] = "qualityIndex";
		$testData['error'] = FALSE;
		*/
		
		$testDataMeasurement['latest']['data'] = "40.1";
		$testDataMeasurement['latest']['time'] = "3";
		$testDataMeasurement['today'][1] = "15";
		$testDataMeasurement['today'][2] = "20.5";
		$testDataMeasurement['today'][3] = "40.1";
		$testDataMeasurement['today'][4] = NULL;
		$testDataMeasurement['metadata']['station'] = "Mannerhe";
		$testDataMeasurement['metadata']['source'] = "Ilmanlaatuportaali, Ilmatieteen laitos";
		$testDataMeasurement['metadata']['sourceURL'] = "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&rs=86&ss=564&p=ozone&pv=04.10.2012&j=23&et=table&tj=3600&ls=suomi";
		$testDataMeasurement['metadata']['status'] = "unconfirmed measurements";
		$testDataMeasurement['metadata']['measurement'] = "nitrogendioxide";
		$testDataMeasurement['error'] = FALSE;
		
		
		$testDataMeasurement = $this->convertScrapedToFloat($testDataMeasurement);
		
		if (is_float($testDataMeasurement['latest']['data']))
		{
			echo "OK: convertScrapedToFloat\n";
		}
		else
		{
			echo "FAIL: convertScrapedToFloat\n";
		}

		if (is_float($testDataMeasurement['today'][3]))
		{
			echo "OK: convertScrapedToFloat\n";
		}
		else
		{
			echo "FAIL: convertScrapedToFloat\n";
		}
		
		
		$testDataMeasurement = $this->addIndex($testDataMeasurement);
		
		if (2 === $testDataMeasurement['latest']['index'])
		{
			echo "OK: addIndex\n";
		}
		else
		{
			echo "FAIL: addIndex\n";
		}
		

		print_r ($testDataMeasurement);
		
		exit("</pre>");
	}


}


?>