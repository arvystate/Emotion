<?php
/**
 * Sample_Captcha
 *
 * Short sample controller to display Captcha library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Captcha extends EmotionController
{
	//
	// Just generates a random string and displays a basic captcha
	//
	
	public function index ()
	{
		//
		// Generate a random string of alpha-numeric characters
		// The string will always be 8 characters long
		//
		
		$text = $this->Load ('Text')->Random ('alnum', 8);
		
		//
		// Display captcha to user.
		// The source of the image needs to be replaced with correct URL to your captcha generating function.
		//
		
		echo ('Generated string: ' . $text . '<br />');
		echo ('<img src="/sample/run/controller_sample_captcha/display/' . $text . '" />');
	}
	
	public function display ($string = 'Test')
	{
		//
		// Create a basic captcha of 150x30 px in PNG format
		//
		
		$this->Load ('Captcha')->Create (150, 30, $string, 'png', 'application/font/arial.ttf');
		
		$this->Load ('Captcha')->Show ();
	}
}

?>