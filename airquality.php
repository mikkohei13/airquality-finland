<?php

Class airquality
{
	var $station = NULL;
	var $measurement = NULL;
	var $validMeasurements = Array();
	
	var $result = Array();

	// ------------------------------------------------------------------------
	// Constructor
	// Checks that station is number
	
	public function __construct($station, $measurement)
	{
		$this->validMeasurements['particulateslt10um'] = TRUE; 
		$this->validMeasurements['nitrogendioxide'] = TRUE; 
		$this->validMeasurements['particulateslt2.5um'] = TRUE; 
		$this->validMeasurements['carbonmonoxide'] = TRUE; 
		$this->validMeasurements['ozone'] = TRUE; 
		$this->validMeasurements['sulphurdioxide'] = TRUE; 
		$this->validMeasurements['odorsulphurcompounds'] = TRUE; 
		
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
		$this->evaluateMeasurement();
	}
		
	// ------------------------------------------------------------------------
	// 
	
	public function validMeasurement($measurement)
	{
		if ($this->validMeasurements[$measurement] || "qualityIndex" == $measurement)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	// ------------------------------------------------------------------------
	// 
	
	public function evaluateMeasurement()
	{
		if ("qualityIndex" == $this->measurement)
		{
			$this->evaluateQualityIndex($type);
		}
		else
		{
			$scraper = new scraper($this->station, $this->measurement);
			$this->result = $scraper->returnResult();
			
			$this->calculateIndex();
		}
		
	}
	
	// ------------------------------------------------------------------------
	// Calculates air quality index
	
	public function evaluateQualityIndex()
	{
		// Goes through all measurements, tries to pick the time & metadata from each, since we don't know which measurement is available. This will lead to the sourceURL will be from the last measurement. 
		
		foreach ($this->validMeasurements as $measurementName => $temp)
		{
			try
			{
				$scraper = new scraper($this->station, $measurementName);
				$partsTemp = $scraper->returnResult();
//				print_r ($partsTemp); exit("DEBUG");
				
				$this->result['latest']['parts'][$measurementName] = $partsTemp['latest']['data'];

				$this->result['latest']['time'] = $partsTemp['latest']['time'];
				$this->result['metadata'] = $partsTemp['metadata'];

			}
			catch (Exception $e)
			{
				$this->result['latest']['parts'][$measurementName] = NULL;
			}
		}
		
		$this->result['metadata']['measurement'] = "qualityIndex";
		
		// Values from http://www.hsy.fi/seututieto/ilmanlaatu/tiedotus/indeksi/Sivut/default.aspx
		// All units are micrograms/m3
		
		if (NULL === $this->result['latest']['parts']['nitrogendioxide'] && NULL === $this->result['latest']['parts']['particulateslt2.5um'] && NULL === $this->result['latest']['parts']['particulateslt10um'] && NULL === $this->result['latest']['parts']['carbonmonoxide'] && NULL === $this->result['latest']['parts']['ozone'] && NULL === $this->result['latest']['parts']['sulphurdioxide'] && NULL === $this->result['latest']['parts']['odorsulphurcompounds'])
		{
			throw new Exception("This station doesn't yet have an air quality index for today.");
		}
		elseif ($this->result['latest']['parts']['nitrogendioxide'] > 200 || $this->result['latest']['parts']['particulateslt2.5um'] > 75 || $this->result['latest']['parts']['particulateslt10um'] > 200 || $this->result['latest']['parts']['carbonmonoxide'] > 30000 || $this->result['latest']['parts']['ozone'] > 180 || $this->result['latest']['parts']['sulphurdioxide'] > 350 || $this->result['latest']['parts']['odorsulphurcompounds'] > 50)
		{
			$this->result['latest']['index'] = 5;
			$this->result['latest']['FI'] = "erittäin huono";
			$this->result['latest']['EN'] = "very bad";
		}
		elseif ($this->result['latest']['parts']['nitrogendioxide'] > 150 || $this->result['latest']['parts']['particulateslt2.5um'] > 50 || $this->result['latest']['parts']['particulateslt10um'] > 100 || $this->result['latest']['parts']['carbonmonoxide'] > 20000 || $this->result['latest']['parts']['ozone'] > 140 || $this->result['latest']['parts']['sulphurdioxide'] > 250 || $this->result['latest']['parts']['odorsulphurcompounds'] > 20)
		{
			$this->result['latest']['index'] = 4;
			$this->result['latest']['FI'] = "huono";
			$this->result['latest']['EN'] = "bad";
		}
		elseif ($this->result['latest']['parts']['nitrogendioxide'] > 70 || $this->result['latest']['parts']['particulateslt2.5um'] > 25 || $this->result['latest']['parts']['particulateslt10um'] > 50 || $this->result['latest']['parts']['carbonmonoxide'] > 8000 || $this->result['latest']['parts']['ozone'] > 100 || $this->result['latest']['parts']['sulphurdioxide'] > 80 || $this->result['latest']['parts']['odorsulphurcompounds'] > 10)
		{
			$this->result['latest']['index'] = 3;
			$this->result['latest']['FI'] = "välttävä";
			$this->result['latest']['EN'] = "mediocre";
		}
		elseif ($this->result['latest']['parts']['nitrogendioxide'] > 40 || $this->result['latest']['parts']['particulateslt2.5um'] > 10 || $this->result['latest']['parts']['particulateslt10um'] > 20 || $this->result['latest']['parts']['carbonmonoxide'] > 4000 || $this->result['latest']['parts']['ozone'] > 60 || $this->result['latest']['parts']['sulphurdioxide'] > 20 || $this->result['latest']['parts']['odorsulphurcompounds'] > 5)
		{
			$this->result['latest']['index'] = 2;
			$this->result['latest']['FI'] = "tyydyttävä";
			$this->result['latest']['EN'] = "satisfactory";
		}
		else 
		{
			$this->result['latest']['index'] = 1;
			$this->result['latest']['FI'] = "hyvä";
			$this->result['latest']['EN'] = "good";
		}
		
//		$this->debugThisArray($this->result);
	}

	// ------------------------------------------------------------------------
	// Adds index to a measurement
	
	public function calculateIndex()
	{
		// limts as micrograms/m3
	
		$indexMaxLimits['nitrogendioxide'][1] = 40;
		$indexMaxLimits['nitrogendioxide'][2] = 70;
		$indexMaxLimits['nitrogendioxide'][3] = 150;
		$indexMaxLimits['nitrogendioxide'][4] = 200;
	
		$indexMaxLimits['particulateslt2.5um'][1] = 10;
		$indexMaxLimits['particulateslt2.5um'][2] = 25;
		$indexMaxLimits['particulateslt2.5um'][3] = 50;
		$indexMaxLimits['particulateslt2.5um'][4] = 75;
	
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
		
		$indexMaxLimits['odorsulphurcompounds'][1] = 5;
		$indexMaxLimits['odorsulphurcompounds'][2] = 10;
		$indexMaxLimits['odorsulphurcompounds'][3] = 20;
		$indexMaxLimits['odorsulphurcompounds'][4] = 50;
		
		
		$data = $this->result['latest']['data'];
		$measurement = $this->result['metadata']['measurement'];
		
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
	
		$this->result['latest']['index'] = $index;
		$this->result['latest']['FI'] = $FI;
		$this->result['latest']['EN'] = $EN;
	}
	
	// ------------------------------------------------------------------------
	// 
	public function returnResultArray()
	{
		$this->result['error'] = FALSE;
		return $this->result;
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
}
?>