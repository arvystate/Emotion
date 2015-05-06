<?php
/**
 * Sample_Security
 *
 * Short sample controller to display Security library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Security extends EmotionController
{
	public function index ()
	{
		//
		// Securing global variables - GET, POST, COOKIE
		//
		
		$this->Load ('Security')->SecureGlobals ();
		
		echo ('Cleaning faulty user input.<br /><br />');
		
		//
		// Escaping SQL injection
		//
		
		echo ("SELECT * FROM 'users' WHERE user_id = '" . $this->Load ('Security')->RemoveSqlInjection ("1' OR 1='1") . "';<br />");
		
		//
		// Cleaning XSS exploits
		//
		
		echo ("Some XSS injection: " . $this->Load ('Security')->XssClean ("<script type=\"text/javascript\">alert ('Hello XSS!')</script>") . "<br />");
	}
}

?>