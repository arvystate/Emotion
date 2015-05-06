<?php
/**
 * Sample_View
 *
 * Short sample controller to display View function usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_View extends EmotionController
{
	public function index ()
	{		
		//
		// Displaying some views
		// Take a look at View samples to see how those views are designed.
		//
		
		$this->View ('xhtml_header');
		
		$this->View ('xhtml_js_function');
		
		//
		// Displaying views with parameters
		//
		
		$this->View ('list', array ('list' => array ('a', 'b', 'c', 'd', 'e') ) );
		
		$data = array ();
		
		$data[0]['name'] = 'Simon';
		$data[1]['name'] = 'David';
		$data[2]['name'] = 'Mary';
		
		$this->View ('table', array ('data' => $data) );
	}
}

?>