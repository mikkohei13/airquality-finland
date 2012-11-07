<?php

Class airquality
{
	var $station = NULL;
	var $measurement = NULL;
	var $validMeasurements = Array();

	var $message = "";
	var $messageStation = "";

	
	// ------------------------------------------------------------------------
	// Constructor
	// Checks that station is number
	
	public function __construct($station, $measurement)
	{
		$this->validMeasurements['nitrogendioxide'] = TRUE; 
		$this->validMeasurements['particulateslt10um'] = TRUE; 
		$this->validMeasurements['particulateslt2_5um'] = TRUE; 
		$this->validMeasurements['carbonmonoxide'] = TRUE; 
		$this->validMeasurements['ozone'] = TRUE; 
		$this->validMeasurements['sulphurdioxide'] = TRUE; 
		$this->validMeasurements['odorsulphurcompounds'] = TRUE; 
		$this->validMeasurements['qualityIndex '] = TRUE; 
		
		// Validate input
		if ( !( is_numeric($station) && $station==(int)$station ) )
		{
			throw new Exception("ss (station) must be a number");
		}
		
		if ( !$this->validMeasurement($measurement) )
		{
			throw new Exception("p (measurement) is invalid");
		}
		
		$this->station = $station;
		$this->measurement = $measurement;
		
		// Scraping
		$result = $this->evaluateMeasurement();
		$this->debugThisArray($result);
	}
		
	// ------------------------------------------------------------------------
	// 
	
	public function validMeasurement($measurement)
	{
		if ($this->validMeasurements[$measurement])
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	// ------------------------------------------------------------------------
	// Returns measurement or error message as an array
	
	public function evaluateMeasurement()
	{
		if ("qualityIndex" == $this->measurement)
		{
			$this->evaluateQualityIndex($type);
		}
		else
		{
			$scraper = new scraper($this->station, $this->measurement);
			return $scraper->returnResult();
		}
	}
	
	// ------------------------------------------------------------------------
	// Calculates air quality index
	
	public function evaluateQualityIndex()
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
		
		$odorsulphurcompounds = $this->measurement("odorsulphurcompounds");
		$result['latest']['parts']['odorsulphurcompounds'] = $odorsulphurcompounds['latest']['data'];
		if (isset($odorsulphurcompounds['latest']['time']))
		{
			$time = $odorsulphurcompounds['latest']['time'];
			$metadata = $odorsulphurcompounds['metadata'];
		}
		
		// Values from http://www.hsy.fi/seututieto/ilmanlaatu/tiedotus/indeksi/Sivut/default.aspx
		// All units are micrograms/m3
		
		if (NULL ===$nitrogendioxide['latest']['data'] && NULL ===$particulateslt2_5um['latest']['data'] && NULL ===$particulateslt10um['latest']['data'] && NULL ===$carbonmonoxide['latest']['data'] && NULL ===$ozone['latest']['data'] && NULL ===$sulphurdioxide['latest']['data'] && NULL ===$odorsulphurcompounds['latest']['data'])
		{
			$result['error'] = TRUE;
			$result['message'] = "this station doesn't yet have an air quality index for today<br />";
			return $result;
		}
		elseif ($nitrogendioxide['latest']['data'] > 200 || $particulateslt2_5um['latest']['data'] > 75 || $particulateslt10um['latest']['data'] > 200 || $carbonmonoxide['latest']['data'] > 30000 || $ozone['latest']['data'] > 180 || $sulphurdioxide['latest']['data'] > 350 || $odorsulphurcompounds['latest']['data'] > 50)
		{
			$result['latest']['index'] = 5;
			$result['latest']['FI'] = "erittäin huono";
			$result['latest']['EN'] = "very bad";
		}
		elseif ($nitrogendioxide['latest']['data'] > 150 || $particulateslt2_5um['latest']['data'] > 50 || $particulateslt10um['latest']['data'] > 100 || $carbonmonoxide['latest']['data'] > 20000 || $ozone['latest']['data'] > 140 || $sulphurdioxide['latest']['data'] > 250 || $odorsulphurcompounds['latest']['data'] > 20)
		{
			$result['latest']['index'] = 4;
			$result['latest']['FI'] = "huono";
			$result['latest']['EN'] = "bad";
		}
		elseif ($nitrogendioxide['latest']['data'] > 70 || $particulateslt2_5um['latest']['data'] > 25 || $particulateslt10um['latest']['data'] > 50 || $carbonmonoxide['latest']['data'] > 8000 || $ozone['latest']['data'] > 100 || $sulphurdioxide['latest']['data'] > 80 || $odorsulphurcompounds['latest']['data'] > 10)
		{
			$result['latest']['index'] = 3;
			$result['latest']['FI'] = "välttävä";
			$result['latest']['EN'] = "mediocre";
		}
		elseif ($nitrogendioxide['latest']['data'] > 40 || $particulateslt2_5um['latest']['data'] > 10 || $particulateslt10um['latest']['data'] > 20 || $carbonmonoxide['latest']['data'] > 4000 || $ozone['latest']['data'] > 60 || $sulphurdioxide['latest']['data'] > 20 || $odorsulphurcompounds['latest']['data'] > 5)
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