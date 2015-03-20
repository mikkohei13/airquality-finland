<?php

Class scraper
{
	var $station = NULL;
	var $measurement = NULL;
	
	var $result = Array();
	var $url = NULL;


	// ------------------------------------------------------------------------
	// Constructor
	
	// ------------------------------------------------------------------------
	// Scrapes a measurement
	
	public function __construct($station, $measurement)
	{
		$this->station = $station;
		$this->measurement = $measurement;
		
		// Get html
		$html = str_get_html($this->fetchPageAsUTF8());
		
		if ( !is_object($html) )
		{
			throw new Exception("Ilmanlaatuportaali seems to be out of order.");
		}
		else
		{
			try 
			{
				$this->scrapeDOMobject($html);
			}
			catch (Exception $e)
			{
				throw $e;
			}
		}
	}

	// ------------------------------------------------------------------------
	// Fetches a data page from Ilmanlaatuportaali and returns it as an UTF-8 string
	
	public function fetchPageAsUTF8()
	{
		// Form page URL
		$pv = date("d.m.Y");
		
		$urlHome = "http://www.ilmanlaatu.fi/ilmanyt/nyt/ilmanyt.php?as=Suomi&ss=" . $this->station . "&p=" . $this->measurement . "&pv=" . $pv . "&j=23&et=table&tj=3600&ls=suomi";
		
//		echo $urlHome; exit(); // debug

		// Data page URL
		$time = date("YmdH");
		$url = "http://www.ilmanlaatu.fi/php/table/observationsInTable.php?step=3600&today=1&timesequence=23&time=" . $time . "&station=" . $this->station . "";

		// Visit form page, set a cookie
		$cookies = $this->fetch($urlHome, null, true);		

		// Scrape data page with the cookie
		$output = $this->fetch($url, $cookies, false);	
		
		$this->url = $urlHome;

//		echo "/$output/"; echo "\n" . $urlHome . "\n" . $url . "\n"; exit("DEBUG END"); // debug

		return utf8_encode($output);
	}

	// ------------------------------------------------------------------------
	// Saves cookie data into variable instead of file; this avoids file permission problems
	// by Stephan Miller / eristoddle / https://gist.github.com/eristoddle/8740954

	public function fetch($url, $cookies = null, $returnCookie = false)
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    if($cookies){
	        curl_setopt($ch, CURLOPT_COOKIE, implode(';',$cookies));
	    }
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    $result = curl_exec($ch);
	    list($header, $body) = explode("\r\n\r\n", $result, 2);
	    $end = strpos($header, 'Content-Type');
	    $start = strpos($header, 'Set-Cookie');
	    $parts = explode('Set-Cookie:', substr($header, $start, $end - $start));
	    $cookies = array();
	    foreach ($parts as $co) {
	        $cd = explode(';', $co);
	        if (!empty($cd[0]))
	            $cookies[] = $cd[0];
	    }
	    curl_close($ch);
	    if ($returnCookie){
	        return $cookies;
	    }
    	return $body;
	}

	// ------------------------------------------------------------------------
	//
	public function scrapeDOMobject($html)
	{
//		print_r ($html); exit("??");
	
		$table = $html->find('table', 0);
		
		foreach($table->find('tr') as $row)
		{
			$data[$row->find('td', 0)->plaintext] = $row->find('td', 1)->plaintext;
		}
		
		// Metadata
		
		require "providers.php";
		
		$municipality = $municipalities[$this->station];
		if (isset($providers[$municipality]))
		{
			$provider = $providers[$municipality];
		}
		else
		{
			$provider = "tuntematon";
		}
		
		$result['metadata']['station'] = $data['Tunti'];
		$result['metadata']['municipality'] = $municipality;
		$result['metadata']['provider'] = $provider;
		$result['metadata']['source'] = "Ilmanlaatuportaali, http://www.ilmanlaatu.fi";
		$result['metadata']['sourceURL'] = $this->url;
		$result['metadata']['status'] = "unconfirmed measurements";
		$result['metadata']['measurement'] = $this->measurement;
		
		unset($data['Tunti']);
		
		if (NULL == $result['metadata']['station'])
		{
			throw new Exception("Station doesn't have this measurement " . $this->measurement);
		}
		elseif ( empty($data ))
		{
			throw new Exception("Station doesn't have measurements for today.");
		}
		else
		{
			// All measurements are missing
			$temp = FALSE;
			foreach ($data as $key => $value)
			{
				if ( !empty($value) )
				{
					$temp = TRUE;
					break;
				}
			}
			if ( !$temp )
			{
				throw new Exception("All measurements are missing for today.");
			}
		}

		// Save all data as todays data
		$result['today'] = $data;


//		print_r ($data); exit(); // debug

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
//		print_r ($result); exit(); // debug
		
//		$result = $this->addIndex($result);

//		print_r ($result); exit(); // debug
		
		
		// ABBA: tämä alunperin kutsuvassa funktiossa
		// If this data is missing
		/*
			if (FALSE === $dataArray)
			{
				$this->message .= "this station doesn't have this measurement (X)<br />";
				$errorArray['error'] = TRUE;
				$errorArray['message'] = $this->message;
				return $errorArray;
			}
		*/


		$this->result = $result;
	}
	
	// ------------------------------------------------------------------------
	// Converts scraped stings to float numbers
	
	public function convertScrapedToFloat($array)
	{
//		echo "<pre>"; print_r ($array); exit();
		
		if (! isset($array['latest']['data']))
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
	public function returnResult()
	{
		return $this->result;
	}


	
}

?>