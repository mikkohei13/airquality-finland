<?php

Class scraper
{

	// ------------------------------------------------------------------------
	// Constructor
	
	public function __construct($station, $measurement)
	{
		if (is_numeric($measurement) && $measurement == (int) $measurement)
		{
			exit("WIN");
		}
		else
		{
			throw new Exception('TEST EXCEPTION2');
		}		
	}


	
}

?>